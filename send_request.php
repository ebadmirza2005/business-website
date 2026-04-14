<?php

ob_start();
ini_set("display_errors", "0");

register_shutdown_function(static function (): void {
    $error = error_get_last();
    if ($error && in_array($error['type'], [E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR], true)) {
        if (!headers_sent()) {
            header('Content-Type: application/json');
            http_response_code(500);
        }

        if (ob_get_length()) {
            ob_clean();
        }

        echo json_encode([
            'success' => false,
            'message' => 'A server error occurred. Please try again.'
        ]);
    }
});

header("Content-Type: application/json");

$autoloadPath = __DIR__ . '/vendor/autoload.php';
$phpMailerAvailable = false;

if (is_file($autoloadPath)) {
    require $autoloadPath;
}

if (!class_exists('PHPMailer\\PHPMailer\\PHPMailer')) {
    $phpMailerSrc = __DIR__ . '/vendor/phpmailer/phpmailer/src';
    $requiredFiles = [
        $phpMailerSrc . '/Exception.php',
        $phpMailerSrc . '/PHPMailer.php',
        $phpMailerSrc . '/SMTP.php',
    ];

    foreach ($requiredFiles as $file) {
        if (!is_file($file)) {
            break;
        }

        require_once $file;
    }
}

$phpMailerAvailable = class_exists('PHPMailer\\PHPMailer\\PHPMailer');

function sendJson(array $payload): void
{
    if (ob_get_length()) {
        ob_clean();
    }

    echo json_encode($payload);
    exit;
}

function pickConfig(array $config, string $key, string $envKey = '', string $default = ''): string
{
    if (isset($config[$key]) && (string) $config[$key] !== '') {
        return (string) $config[$key];
    }

    if ($envKey !== '') {
        $envValue = getenv($envKey);
        if ($envValue !== false && $envValue !== '') {
            return (string) $envValue;
        }
    }

    return $default;
}

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    sendJson([
        "success" => false,
        "message" => "Invalid request method"
    ]);
}

$name = isset($_POST["name"]) ? trim($_POST["name"]) : "";
$email = isset($_POST["email"]) ? trim($_POST["email"]) : "";
$service = isset($_POST["service"]) ? trim($_POST["service"]) : "";
$message = isset($_POST["message"]) ? trim($_POST["message"]) : "";

if ($name === "" || $email === "" || $service === "" || $message === "") {
    sendJson([
        "success" => false,
        "message" => "Please fill in all fields"
    ]);
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    sendJson([
        "success" => false,
        "message" => "Please enter a valid email address"
    ]);
}

if (preg_match('/[\r\n]/', $email)) {
    sendJson([
        "success" => false,
        "message" => "Invalid email format"
    ]);
}

$smtpConfigPath = __DIR__ . "/smtp_config.php";
$smtpConfig = [];

if (is_file($smtpConfigPath)) {
    $loadedConfig = require $smtpConfigPath;
    if (is_array($loadedConfig)) {
        $smtpConfig = $loadedConfig;
    }
}

$smtpHost = pickConfig($smtpConfig, 'host', 'SMTP_HOST', 'smtp.gmail.com');
$smtpPortRaw = pickConfig($smtpConfig, 'port', 'SMTP_PORT', '587');
$smtpPort = (int) $smtpPortRaw;
$smtpUser = pickConfig($smtpConfig, 'username', 'SMTP_USER', '');
$smtpPass = pickConfig($smtpConfig, 'password', 'SMTP_PASS', '');
$smtpFrom = pickConfig($smtpConfig, 'from_email', 'SMTP_FROM', $smtpUser);
$smtpTo = pickConfig($smtpConfig, 'recipient_email', 'SMTP_TO', $smtpFrom);
$smtpFromName = preg_replace('/[\r\n]+/', ' ', $name) ?: pickConfig($smtpConfig, 'from_name', 'SMTP_FROM_NAME', 'Faaz Pro Tech');
$smtpEncryption = strtolower((string) ($smtpConfig["encryption"] ?? "tls"));
$smtpDebug = filter_var($smtpConfig["debug"] ?? getenv("SMTP_DEBUG") ?? false, FILTER_VALIDATE_BOOL);
$smtpDebugLog = [];

// Gmail App Password is commonly copied with spaces; normalize it safely.
$smtpPass = trim(str_replace(" ", "", $smtpPass));

$to = $smtpTo;
$subject = "New Contact Request - Faaz Pro Tech";

$body = "You have received a new contact request from your website.\n\n"
    . "Name: {$name}\n"
    . "Email: {$email}\n"
    . "Service: {$service}\n\n"
    . "Message:\n{$message}\n";

if ($phpMailerAvailable) {
    if ($smtpUser === "" || $smtpPass === "") {
        sendJson([
            "success" => false,
            "message" => "SMTP is not configured. Please update smtp_config.php username and password."
        ]);
    }

    try {
        $mailer = new \PHPMailer\PHPMailer\PHPMailer(true);
        $mailer->isSMTP();
        $mailer->Host = $smtpHost;
        $mailer->SMTPAuth = true;
        $mailer->Username = $smtpUser;
        $mailer->Password = $smtpPass;
        $mailer->Hostname = parse_url('https://' . preg_replace('/^mailto:/', '', $smtpFrom), PHP_URL_HOST) ?: 'faazprotech.com';
        $mailer->SMTPSecure = $smtpEncryption === "ssl"
            ? \PHPMailer\PHPMailer\PHPMailer::ENCRYPTION_SMTPS
            : \PHPMailer\PHPMailer\PHPMailer::ENCRYPTION_STARTTLS;
        $mailer->Port = $smtpPort;
        $mailer->CharSet = "UTF-8";
        if ($smtpDebug) {
            $mailer->SMTPDebug = 2;
            $mailer->Debugoutput = static function ($str, $level) use (&$smtpDebugLog): void {
                $smtpDebugLog[] = trim("[{$level}] {$str}");
            };
        }
        $mailer->SMTPOptions = [
            "ssl" => [
                "verify_peer" => false,
                "verify_peer_name" => false,
                "allow_self_signed" => true,
            ],
        ];

        $mailer->Sender = $smtpFrom;
        $mailer->setFrom($smtpFrom, $smtpFromName);
        $mailer->addAddress($to);
        $mailer->addReplyTo($email, $name);

        $mailer->Subject = $subject;
        $mailer->Body = $body;
        $mailer->AltBody = $body;
        $mailer->isHTML(false);

        $mailer->send();
    } catch (Throwable $e) {
        $errorText = $e->getMessage();
        $hint = "";

        if (stripos($errorText, "authenticate") !== false) {
            $hint = " Use your Hostinger mailbox password, and make sure SMTP host, port, and encryption match Hostinger settings.";
        }

        sendJson([
            "success" => false,
            "message" => "Message could not be sent. SMTP error: " . $errorText . $hint
                . ($smtpDebug && !empty($smtpDebugLog) ? " | Debug: " . implode(" || ", $smtpDebugLog) : "")
        ]);
    }
} else {
    $headers = [
        'MIME-Version: 1.0',
        'Content-Type: text/plain; charset=UTF-8',
        'From: ' . $smtpFromName . ' <' . $smtpFrom . '>',
        'Reply-To: ' . $name . ' <' . $email . '>',
        'X-Mailer: PHP/' . PHP_VERSION,
    ];

    $mailSent = @mail($to, $subject, $body, implode("\r\n", $headers));
    if (!$mailSent) {
        sendJson([
            'success' => false,
            'message' => 'Mailer dependency is missing and PHP mail() is not available on this server. Install Composer dependencies or enable mail sending in hosting panel.'
        ]);
    }
}

sendJson([
    "success" => true,
    "message" => "Thanks. Your request has been sent successfully."
]);
