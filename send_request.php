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
if (!is_file($autoloadPath)) {
    if (ob_get_length()) {
        ob_clean();
    }
    echo json_encode([
        'success' => false,
        'message' => 'Mailer dependencies are missing. Run composer install on the server.'
    ]);
    exit;
}

require $autoloadPath;

if (!class_exists('PHPMailer\\PHPMailer\\PHPMailer')) {
    $phpMailerSrc = __DIR__ . '/vendor/phpmailer/phpmailer/src';
    $requiredFiles = [
        $phpMailerSrc . '/Exception.php',
        $phpMailerSrc . '/PHPMailer.php',
        $phpMailerSrc . '/SMTP.php',
    ];

    foreach ($requiredFiles as $file) {
        if (!is_file($file)) {
            if (ob_get_length()) {
                ob_clean();
            }
            echo json_encode([
                'success' => false,
                'message' => 'PHPMailer library files are missing on server. Re-upload vendor folder or run composer install.'
            ]);
            exit;
        }

        require_once $file;
    }
}

if (!class_exists('PHPMailer\\PHPMailer\\PHPMailer')) {
    sendJson([
        'success' => false,
        'message' => 'PHPMailer could not be loaded. Run composer dump-autoload or re-install dependencies.'
    ]);
}

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
$message = isset($_POST["message"]) ? trim($_POST["message"]) : "";

if ($name === "" || $email === "" || $message === "") {
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
$smtpFromName = pickConfig($smtpConfig, 'from_name', 'SMTP_FROM_NAME', 'Faaz Pro Tech');
$smtpEncryption = strtolower((string) ($smtpConfig["encryption"] ?? "tls"));
$smtpDebug = filter_var($smtpConfig["debug"] ?? getenv("SMTP_DEBUG") ?? false, FILTER_VALIDATE_BOOL);
$smtpDebugLog = [];

// Gmail App Password is commonly copied with spaces; normalize it safely.
$smtpPass = trim(str_replace(" ", "", $smtpPass));

if ($smtpUser === "" || $smtpPass === "") {
    sendJson([
        "success" => false,
        "message" => "SMTP is not configured. Please update smtp_config.php username and password."
    ]);
}

$to = $smtpTo;
$subject = "New Contact Request - Faaz Pro Tech";

$body = "You have received a new contact request from your website.\n\n"
    . "Name: {$name}\n"
    . "Email: {$email}\n\n"
    . "Message:\n{$message}\n";

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

sendJson([
    "success" => true,
    "message" => "Thanks. Your request has been sent successfully."
]);
