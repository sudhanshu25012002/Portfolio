<?php
/**
 * ============================================================================
 * SUDHANSHU RANJAN — PORTFOLIO DIRECT CONTACT DISPATCH
 * Security Hardened: Rate Limiting, Throttling, Honeypot, CRLF Injection Shield,
 * Strict Character Guards & SMTP Dispatch
 * ============================================================================
 */

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// Include PHPMailer autoloader files
require_once __DIR__ . '/PHPMailer/src/PHPMailer.php';
require_once __DIR__ . '/PHPMailer/src/SMTP.php';
require_once __DIR__ . '/PHPMailer/src/Exception.php';

header('Content-Type: application/json; charset=utf-8');

// Security Response Headers
header('X-Content-Type-Options: nosniff');
header('X-Frame-Options: SAMEORIGIN');
header('X-XSS-Protection: 1; mode=block');

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

    $now = time();
    $clientIp = $_SERVER['HTTP_CF_CONNECTING_IP'] ?? $_SERVER['HTTP_X_FORWARDED_FOR'] ?? $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';
    if (strpos($clientIp, ',') !== false) {
        $clientIp = trim(explode(',', $clientIp)[0]);
    }

    // ========================================================================
    // SECURITY GUARD 1: HONEYPOT BOT TRAP
    // ========================================================================
    if (!empty($_POST['website_url'])) {
        // Automated bot filled hidden honeypot. Fake success response to poison bot.
        usleep(400000);
        echo json_encode([
            'status'  => 'success',
            'message' => 'Your message has been transmitted successfully!'
        ]);
        exit;
    }

    // ========================================================================
    // SECURITY GUARD 2: RATE LIMITING & THROTTLING
    // ========================================================================
    $rateLimitFile = __DIR__ . '/rate_limits.json';
    $maxAttemptsPerWindow = 4;        // Max 4 submissions
    $windowSeconds = 600;             // Per 10-minute sliding window
    $minIntervalSeconds = 12;         // Must wait 12s between consecutive requests

    $rateData = [];
    if (file_exists($rateLimitFile)) {
        $rateData = json_decode(@file_get_contents($rateLimitFile), true) ?: [];
    }

    // Prune stale records older than window
    foreach ($rateData as $ip => $records) {
        $rateData[$ip] = array_filter($records, function($ts) use ($now, $windowSeconds) {
            return ($now - $ts) < $windowSeconds;
        });
        if (empty($rateData[$ip])) {
            unset($rateData[$ip]);
        }
    }

    $ipRecords = $rateData[$clientIp] ?? [];

    // Throttling check (burst protection)
    if (!empty($ipRecords)) {
        $lastAttempt = max($ipRecords);
        if (($now - $lastAttempt) < $minIntervalSeconds) {
            $waitSeconds = $minIntervalSeconds - ($now - $lastAttempt);
            http_response_code(429);
            echo json_encode([
                'status'  => 'error',
                'message' => "Throttled: Please wait {$waitSeconds} seconds before transmitting another message."
            ]);
            exit;
        }
    }

    // Volume rate-limit check
    if (count($ipRecords) >= $maxAttemptsPerWindow) {
        http_response_code(429);
        echo json_encode([
            'status'  => 'error',
            'message' => 'Rate limit reached: Maximum 4 transmissions per 10 minutes. Please wait before retrying.'
        ]);
        exit;
    }

    // ========================================================================
    // SECURITY GUARD 3: TIME-LOCK BOT DEFENSE (Minimum human fill time)
    // ========================================================================
    $formTime = (float) ($_POST['form_time'] ?? 0);
    if ($formTime > 0) {
        $elapsedSec = ($now * 1000 - $formTime) / 1000;
        if ($elapsedSec < 1.5) {
            http_response_code(400);
            echo json_encode([
                'status'  => 'error',
                'message' => 'Form submitted too rapidly. Please review your details and submit again.'
            ]);
            exit;
        }
    }

    // ========================================================================
    // SECURITY GUARD 4: EMAIL HEADER INJECTION & INPUT SANITIZATION
    // ========================================================================
    $name  = preg_replace("/[\r\n\t]/", ' ', trim($_POST['name'] ?? ''));
    $email = preg_replace("/[\r\n\t]/", '', trim($_POST['email'] ?? ''));
    $phone = preg_replace("/[\r\n\t]/", ' ', trim($_POST['phone'] ?? ''));
    $brief = trim($_POST['brief'] ?? $_POST['message'] ?? '');

    $name  = strip_tags($name);
    $email = filter_var($email, FILTER_SANITIZE_EMAIL);
    $phone = strip_tags($phone);
    $brief = strip_tags($brief);

    $errors = [];

    // Name Validation
    if (empty($name)) {
        $errors[] = "Full name is required.";
    } elseif (mb_strlen($name) < 2 || mb_strlen($name) > 80) {
        $errors[] = "Name must be between 2 and 80 characters.";
    } elseif (!preg_match("/^[a-zA-Z\s\.\-'\p{L}]+$/u", $name)) {
        $errors[] = "Name contains invalid characters.";
    }

    // Email Validation
    if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL) || mb_strlen($email) > 120) {
        $errors[] = "A valid email address is required.";
    }

    // Phone Validation
    if (empty($phone)) {
        $errors[] = "Phone number is required.";
    } elseif (!preg_match('/^[0-9+\s\-()]{7,20}$/', $phone)) {
        $errors[] = "Please provide a valid phone number (digits, +, -).";
    }

    // Brief Validation
    if (empty($brief)) {
        $errors[] = "Project brief or message is required.";
    } elseif (mb_strlen($brief) < 5 || mb_strlen($brief) > 4000) {
        $errors[] = "Project brief must be between 5 and 4,000 characters.";
    }

    // URL Spam Flooding Guard: Reject messages with more than 3 hyperlinks
    if (preg_match_all('/https?:\/\//i', $brief) > 3) {
        $errors[] = "Excessive links detected. Please remove hyperlinks from your brief.";
    }

    if (!empty($errors)) {
        http_response_code(400);
        echo json_encode([
            'status'  => 'error',
            'message' => implode(' ', $errors)
        ]);
        exit;
    }

    // Passed all guards — log rate limit attempt
    $rateData[$clientIp][] = $now;
    @file_put_contents($rateLimitFile, json_encode($rateData, JSON_PRETTY_PRINT));

    $timestamp = date('d M Y, H:i:s T');

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
        $mail->Subject = "New Direct Connection Inquiry: " . htmlspecialchars($name, ENT_QUOTES | ENT_HTML5, 'UTF-8');

        $safeName  = htmlspecialchars($name, ENT_QUOTES | ENT_HTML5, 'UTF-8');
        $safeEmail = htmlspecialchars($email, ENT_QUOTES | ENT_HTML5, 'UTF-8');
        $safePhone = htmlspecialchars($phone, ENT_QUOTES | ENT_HTML5, 'UTF-8');
        $safeBrief = nl2br(htmlspecialchars($brief, ENT_QUOTES | ENT_HTML5, 'UTF-8'));
        $safeIp    = htmlspecialchars($clientIp, ENT_QUOTES | ENT_HTML5, 'UTF-8');

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
                <tr>
                  <td style='padding: 8px 0; color: #555555; font-weight: bold;'>CLIENT IP:</td>
                  <td style='padding: 8px 0; color: #666666; font-size: 12px;'>{$safeIp}</td>
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
        $mail->AltBody = "NEW PORTFOLIO INQUIRY\n\nName: $name\nEmail: $email\nPhone: $phone\nTimestamp: $timestamp\nClient IP: $clientIp\n\nProject Brief / Message:\n$brief\n";

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
            @file_put_contents(
                __DIR__ . '/inquiries_error.log',
                "[" . date('Y-m-d H:i:s') . "] Mail Error: " . $mail->ErrorInfo . "\n",
                FILE_APPEND
            );

            echo json_encode([
                'status'  => 'error',
                'message' => 'Transmission failed. Your inquiry was saved; please try again later.'
            ]);
        }
    }
} else {
    http_response_code(405);
    echo json_encode([
        'status'  => 'error',
        'message' => 'Method Not Allowed'
    ]);
}
