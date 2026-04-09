<?php

ob_start();
ini_set("display_errors", "0");

header("Content-Type: application/json");

require __DIR__ . "/vendor/autoload.php";

use PHPMailer\PHPMailer\Exception;
use PHPMailer\PHPMailer\PHPMailer;

function sendJson(array $payload): void
{
    if (ob_get_length()) {
        ob_clean();
    }

    echo json_encode($payload);
    exit;
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

$smtpHost = (string) ($smtpConfig["host"] ?? getenv("SMTP_HOST") ?: "smtp.gmail.com");
$smtpPort = (int) ($smtpConfig["port"] ?? getenv("SMTP_PORT") ?: 587);
$smtpUser = (string) ($smtpConfig["username"] ?? getenv("SMTP_USER") ?: "");
$smtpPass = (string) ($smtpConfig["password"] ?? getenv("SMTP_PASS") ?: "");
$smtpFrom = (string) ($smtpConfig["from_email"] ?? getenv("SMTP_FROM") ?: $smtpUser);
$smtpFromName = (string) ($smtpConfig["from_name"] ?? getenv("SMTP_FROM_NAME") ?: "Faaz Pro Tech");
$smtpEncryption = strtolower((string) ($smtpConfig["encryption"] ?? "tls"));

// Gmail App Password is commonly copied with spaces; normalize it safely.
$smtpPass = trim(str_replace(" ", "", $smtpPass));

if ($smtpUser === "" || $smtpPass === "") {
    sendJson([
        "success" => false,
        "message" => "SMTP is not configured. Please update smtp_config.php username and password."
    ]);
}

$to = "ebadmirza.2005@gmail.com";
$subject = "New Contact Request - Faaz Pro Tech";

$body = "You have received a new contact request from your website.\n\n"
    . "Name: {$name}\n"
    . "Email: {$email}\n\n"
    . "Message:\n{$message}\n";

try {
    $mailer = new PHPMailer(true);
    $mailer->isSMTP();
    $mailer->Host = $smtpHost;
    $mailer->SMTPAuth = true;
    $mailer->Username = $smtpUser;
    $mailer->Password = $smtpPass;
    $mailer->SMTPSecure = $smtpEncryption === "ssl"
        ? PHPMailer::ENCRYPTION_SMTPS
        : PHPMailer::ENCRYPTION_STARTTLS;
    $mailer->Port = $smtpPort;
    $mailer->CharSet = "UTF-8";

    $mailer->setFrom($smtpFrom, $smtpFromName);
    $mailer->addAddress($to);
    $mailer->addReplyTo($email, $name);

    $mailer->Subject = $subject;
    $mailer->Body = $body;

    $mailer->send();
} catch (Exception $e) {
    $errorText = $e->getMessage();
    $hint = "";

    if (stripos($errorText, "authenticate") !== false) {
        $hint = " Use Gmail App Password (16 chars), not your normal Gmail password.";
    }

    sendJson([
        "success" => false,
        "message" => "Message could not be sent. SMTP error: " . $errorText . $hint
    ]);
}

sendJson([
    "success" => true,
    "message" => "Thanks. Your request has been sent successfully."
]);
