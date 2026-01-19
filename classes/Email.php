<?php
class Email {
    private $from;
    private $smtp_host;
    private $smtp_port;

    public function __construct() {
        $this->from = MAIL_FROM;
        $this->smtp_host = SMTP_HOST;
        $this->smtp_port = SMTP_PORT;
    }

    public function sendPasswordReset($to, $name, $reset_link) {
        $subject = SITE_NAME . ' - Password Reset Request';
        $message = "
        <html>
        <body style='font-family: Arial, sans-serif;'>
            <h2>Password Reset Request</h2>
            <p>Hi {$name},</p>
            <p>We received a request to reset your password. Click the link below to set a new password:</p>
            <p><a href='{$reset_link}' style='background: #007bff; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px; display: inline-block;'>Reset Password</a></p>
            <p>This link expires in 1 hour.</p>
            <p>If you didn't request this, ignore this email.</p>
            <br>
            <p>Best regards,<br>" . SITE_NAME . " Team</p>
        </body>
        </html>";

        return $this->send($to, $subject, $message);
    }

    public function sendOrderConfirmation($to, $name, $order_number, $amount) {
        $subject = SITE_NAME . ' - Order Confirmation #' . $order_number;
        $message = "
        <html>
        <body style='font-family: Arial, sans-serif;'>
            <h2>Order Confirmation</h2>
            <p>Hi {$name},</p>
            <p>Thank you for your order! Your order has been placed successfully.</p>
            <table style='width: 100%; border-collapse: collapse;'>
                <tr style='background: #f0f0f0;'>
                    <td style='padding: 10px;'><strong>Order Number:</strong></td>
                    <td style='padding: 10px;'>{$order_number}</td>
                </tr>
                <tr>
                    <td style='padding: 10px;'><strong>Amount:</strong></td>
                    <td style='padding: 10px;'>" . CURRENCY . " {$amount}</td>
                </tr>
            </table>
            <p>You can download your products from your account dashboard.</p>
            <br>
            <p>Best regards,<br>" . SITE_NAME . " Team</p>
        </body>
        </html>";

        return $this->send($to, $subject, $message);
    }

    public function sendPaymentFailed($to, $name, $order_number) {
        $subject = SITE_NAME . ' - Payment Failed for Order #' . $order_number;
        $message = "
        <html>
        <body style='font-family: Arial, sans-serif;'>
            <h2>Payment Failed</h2>
            <p>Hi {$name},</p>
            <p>Unfortunately, your payment for order <strong>{$order_number}</strong> could not be processed.</p>
            <p>Please try again or contact our support team for assistance.</p>
            <br>
            <p>Best regards,<br>" . SITE_NAME . " Team</p>
        </body>
        </html>";

        return $this->send($to, $subject, $message);
    }

    public function sendSupportReply($to, $name, $reply, $ticket_id) {
        $subject = SITE_NAME . ' - Support Ticket #' . $ticket_id . ' Reply';
        $message = "
        <html>
        <body style='font-family: Arial, sans-serif;'>
            <h2>Support Ticket Reply</h2>
            <p>Hi {$name},</p>
            <p>We have replied to your support ticket #<strong>{$ticket_id}</strong>:</p>
            <div style='background: #f0f0f0; padding: 15px; border-radius: 5px; margin: 20px 0;'>
                {$reply}
            </div>
            <p>Log in to your account to view the full conversation.</p>
            <br>
            <p>Best regards,<br>" . SITE_NAME . " Team</p>
        </body>
        </html>";

        return $this->send($to, $subject, $message);
    }

    private function send($to, $subject, $message) {
        $headers = "MIME-Version: 1.0\r\n";
        $headers .= "Content-type: text/html; charset=UTF-8\r\n";
        $headers .= "From: " . $this->from . "\r\n";

        return mail($to, $subject, $message, $headers);
    }
}
?>
