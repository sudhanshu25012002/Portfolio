<?php
/**
 * ============================================================================
 * SUDHANSHU RANJAN — PORTFOLIO DIRECT CONTACT DISPATCH
 * Secure SMTP Mailer with .env support, input validation, and fallback logging
 * ============================================================================
 */

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// Include PHPMailer autoloader files
require_once __DIR__ . '/PHPMailer/src/PHPMailer.php';
require_once __DIR__ . '/PHPMailer/src/SMTP.php';
require_once __DIR__ . '/PHPMailer/src/Exception.php';

header('Content-Type: application/json; charset=utf-8');

// Lightweight .env Loader
function loadEnv($path) {
    if (!file_exists($path)) return;
    $lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        $line = trim($line);
        if (empty($line) || str_starts_with($line, '#')) continue;
        if (strpos($line, '=') !== false) {
            list($key, $value) = explode('=', $line, 2);
            $key = trim($key);
            $value = trim($value);
            $value = trim($value, '"\'');
            if (!array_key_exists($key, $_ENV)) {
                putenv("{$key}={$value}");
                $_ENV[$key] = $value;
            }
        }
    }
}

// Load .env if present
loadEnv(__DIR__ . '/.env');

// Check for optional mail_config.php override
if (file_exists(__DIR__ . '/mail_config.php')) {
    include_once __DIR__ . '/mail_config.php';
}

function getMailConfig($key, $default = '') {
    return $_ENV[$key] ?? getenv($key) ?: $default;
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    // Collect & sanitize inputs
    $name  = trim($_POST['name'] ?? '');
    $email = filter_var(trim($_POST['email'] ?? ''), FILTER_SANITIZE_EMAIL);
    $phone = trim($_POST['phone'] ?? '');
    $brief = trim($_POST['brief'] ?? $_POST['message'] ?? '');

    $errors = [];

    // Validation
    if (empty($name)) {
        $errors[] = "Full name is required.";
    } elseif (mb_strlen($name) < 2 || mb_strlen($name) > 100) {
        $errors[] = "Name must be between 2 and 100 characters.";
    }

    if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "A valid email address is required.";
    }

    if (empty($phone)) {
        $errors[] = "Phone number is required.";
    } elseif (!preg_match('/^[0-9+\s\-()]{7,20}$/', $phone)) {
        $errors[] = "Please provide a valid phone number.";
    }

    if (empty($brief)) {
        $errors[] = "Project brief or message is required.";
    } elseif (mb_strlen($brief) < 5 || mb_strlen($brief) > 5000) {
        $errors[] = "Project brief must be between 5 and 5,000 characters.";
    }

    if (!empty($errors)) {
        http_response_code(400);
        echo json_encode([
            'status'  => 'error',
            'message' => implode(' ', $errors)
        ]);
        exit;
    }

    $timestamp = date('d M Y, H:i:s T');
    $clientIp  = $_SERVER['HTTP_X_FORWARDED_FOR'] ?? $_SERVER['REMOTE_ADDR'] ?? 'UNKNOWN';

    // Safe lead logging to inquiries.json (protected by .gitignore)
    $logFile = __DIR__ . '/inquiries.json';
    $existing = [];
    if (file_exists($logFile)) {
        $existing = json_decode(@file_get_contents($logFile), true) ?: [];
    }
    $existing[] = [
        'timestamp' => $timestamp,
        'name'      => $name,
        'email'     => $email,
        'phone'     => $phone,
        'brief'     => $brief,
        'ip'        => $clientIp
    ];
    @file_put_contents($logFile, json_encode($existing, JSON_PRETTY_PRINT));

    // SMTP Configuration
    $smtpHost     = getMailConfig('SMTP_HOST', 'smtp.gmail.com');
    $smtpPort     = (int) getMailConfig('SMTP_PORT', 587);
    $smtpSecure   = getMailConfig('SMTP_ENCRYPTION', 'tls');
    $smtpUsername = getMailConfig('SMTP_USERNAME', 'sr25012002new@gmail.com');
    $smtpPassword = getMailConfig('SMTP_PASSWORD', base64_decode('cm5idmlobmFlanJ0bW1icg=='));
    $fromName     = getMailConfig('MAIL_FROM_NAME', 'Portfolio Direct Handshake');
    $toEmail      = getMailConfig('MAIL_TO_ADDRESS', 'sr25012002@gmail.com');
    $toName       = getMailConfig('MAIL_TO_NAME', 'Sudhanshu Ranjan');

    $mail = new PHPMailer(true);

    try {
        $mail->SMTPDebug  = 0;
        $mail->isSMTP();
        $mail->Host       = $smtpHost;
        $mail->SMTPAuth   = !empty($smtpPassword);
        $mail->Username   = $smtpUsername;
        $mail->Password   = $smtpPassword;
        
        if (strtolower($smtpSecure) === 'ssl') {
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
        } elseif (strtolower($smtpSecure) === 'tls') {
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        } else {
            $mail->SMTPAutoTLS = false;
        }
        
        $mail->Port       = $smtpPort;
        $mail->Timeout    = 8;

        // Set sender & direct reply-to
        $mail->setFrom($smtpUsername ?: 'noreply@helioslabs.in', $fromName);
        $mail->addReplyTo($email, $name);
        $mail->addAddress($toEmail, $toName);

        // Content
        $mail->isHTML(true);
        $mail->Subject = "New Direct Connection Inquiry: " . htmlspecialchars($name, ENT_QUOTES, 'UTF-8');

        $safeName  = htmlspecialchars($name, ENT_QUOTES, 'UTF-8');
        $safeEmail = htmlspecialchars($email, ENT_QUOTES, 'UTF-8');
        $safePhone = htmlspecialchars($phone, ENT_QUOTES, 'UTF-8');
        $safeBrief = nl2br(htmlspecialchars($brief, ENT_QUOTES, 'UTF-8'));

        $body = "
        <div style='background-color: #f0f2f5; padding: 24px; font-family: monospace, Arial, sans-serif; color: #111111;'>
          <div style='max-width: 600px; margin: 0 auto; background: #ffffff; border: 3px solid #000080; border-radius: 4px; box-shadow: 4px 4px 0px rgba(0,0,0,0.15); overflow: hidden;'>
            
            <div style='background: linear-gradient(90deg, #000080 0%, #1084d0 100%); color: #ffffff; padding: 14px 20px; font-weight: bold; font-size: 15px; letter-spacing: 0.5px;'>
              ⚡ NEW DIRECT CONNECTION INQUIRY
            </div>

            <div style='padding: 24px;'>
              <table style='width: 100%; border-collapse: collapse; margin-bottom: 20px; font-size: 14px;'>
                <tr>
                  <td style='padding: 8px 0; color: #555555; width: 130px; font-weight: bold;'>SENDER NAME:</td>
                  <td style='padding: 8px 0; font-weight: bold; color: #000000;'>{$safeName}</td>
                </tr>
                <tr>
                  <td style='padding: 8px 0; color: #555555; font-weight: bold;'>EMAIL:</td>
                  <td style='padding: 8px 0;'><a href='mailto:{$safeEmail}' style='color: #000080; text-decoration: underline; font-weight: bold;'>{$safeEmail}</a></td>
                </tr>
                <tr>
                  <td style='padding: 8px 0; color: #555555; font-weight: bold;'>PHONE:</td>
                  <td style='padding: 8px 0;'><a href='tel:{$safePhone}' style='color: #000080; text-decoration: underline; font-weight: bold;'>{$safePhone}</a></td>
                </tr>
                <tr>
                  <td style='padding: 8px 0; color: #555555; font-weight: bold;'>RECEIVED ON:</td>
                  <td style='padding: 8px 0; color: #333333;'>{$timestamp}</td>
                </tr>
              </table>

              <div style='background: #f7f8fa; border: 2px solid #d6d4ce; border-left: 4px solid #000080; padding: 16px; margin: 16px 0; border-radius: 2px;'>
                <div style='font-size: 11px; font-weight: bold; color: #000080; margin-bottom: 8px; letter-spacing: 0.5px;'>PROJECT BRIEF / MESSAGE:</div>
                <div style='font-family: Arial, sans-serif; font-size: 14px; line-height: 1.6; color: #222222; white-space: pre-wrap;'>{$safeBrief}</div>
              </div>

              <div style='margin-top: 24px; padding-top: 14px; border-top: 1px dashed #d6d4ce; font-size: 11px; color: #777777;'>
                <span>Tip: Hitting <strong>Reply</strong> in your email client will reply directly to <strong>{$safeEmail}</strong>.</span>
              </div>
            </div>

          </div>
        </div>";

        $mail->Body    = $body;
        $mail->AltBody = "NEW PORTFOLIO INQUIRY\n\nName: $name\nEmail: $email\nPhone: $phone\nTimestamp: $timestamp\n\nProject Brief / Message:\n$brief\n";

        $mail->send();

        echo json_encode([
            'status'  => 'success',
            'message' => 'Your message has been transmitted successfully! Sudhanshu will get back to you shortly.'
        ]);

    } catch (Exception $e) {
        // Fallback to PHP native mail() if available
        $mailSent = false;
        $headers  = "From: {$fromName} <{$smtpUsername}>\r\n" .
                    "Reply-To: {$email}\r\n" .
                    "MIME-Version: 1.0\r\n" .
                    "Content-Type: text/html; charset=UTF-8\r\n";

        if (@mail($toEmail, "New Direct Connection Inquiry: " . $name, $body, $headers)) {
            $mailSent = true;
        }

        if ($mailSent) {
            echo json_encode([
                'status'  => 'success',
                'message' => 'Your message has been transmitted successfully! Sudhanshu will get back to you shortly.'
            ]);
        } else {
            // Log error privately for debugging
            @file_put_contents(
                __DIR__ . '/inquiries_error.log',
                "[" . date('Y-m-d H:i:s') . "] Mail Error: " . $mail->ErrorInfo . "\n",
                FILE_APPEND
            );

            // If SMTP password is not set yet, provide informative message
            if (empty($smtpPassword)) {
                echo json_encode([
                    'status'  => 'error',
                    'message' => 'SMTP credentials not configured yet. Inquiry has been safely logged.'
                ]);
            } else {
                echo json_encode([
                    'status'  => 'error',
                    'message' => 'Transmission failed. Your inquiry was saved; please try again or email directly.'
                ]);
            }
        }
    }
} else {
    http_response_code(405);
    echo json_encode([
        'status'  => 'error',
        'message' => 'Method Not Allowed'
    ]);
}
