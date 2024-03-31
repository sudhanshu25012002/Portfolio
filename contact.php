<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// Include the PHPMailer autoloader
require 'PHPMailer/src/PHPMailer.php';
require 'PHPMailer/src/SMTP.php';
require 'PHPMailer/src/Exception.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Retrieve form data
    $name = $_POST['name'];
    $email = $_POST['email'];
    $subject = $_POST['subject'];
    $message = $_POST['message'];

    // Instantiation and passing `true` enables exceptions
    $mail = new PHPMailer(true);

    try {
        //Server settings
        $mail->SMTPDebug = 0; // Enable verbose debug output if needed
        $mail->isSMTP(); // Send using SMTP
        $mail->Host = 'smtp.gmail.com'; // Set the SMTP server to send through
        $mail->SMTPAuth = true; // Enable SMTP authentication
        $mail->Username = 'sr25012002new@gmail.com'; // SMTP username
        $mail->Password = 'cspagvhqttursqlk'; // SMTP password
        $mail->SMTPSecure = 'tls'; // Enable TLS encryption; `PHPMailer::ENCRYPTION_STARTTLS` encouraged
        $mail->Port = 587; // TCP port to connect to, use 587 for TLS connections

        //Recipients
        $mail->setFrom($email, $name);
        $mail->addAddress('sr25012002@gmail.com'); // Add a recipient

        // Content
        $mail->isHTML(true); // Set email format to HTML
        $mail->Subject = "Mail from Portfolios";
        $mail->Body = "Name: $name, <br> Email: $email, <br> Subject: $subject, <br> $message";

        $mail->send();
        echo 'OK';
    } catch (Exception $e) {
        // echo "error: {$mail->ErrorInfo}";
        echo 'error';
    }
} else {
    echo "Method not allowed"; // Request method is not POST
}
?>
