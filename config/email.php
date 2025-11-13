<?php
/**
 * Email Configuration (Mailpit for local development)
 *
 * Mailpit uses:
 * - Port 1025: SMTP (where emails are sent)
 * - Port 8025: Web UI (to view emails)
 */
if (!defined('SMTP_HOST')) {
    define('SMTP_HOST', 'localhost');
}

if (!defined('SMTP_PORT')) {
    define('SMTP_PORT', 1025);
}

if (!defined('SMTP_USER')) {
    define('SMTP_USER', ''); // Not required for Mailpit
}

if (!defined('SMTP_PASS')) {
    define('SMTP_PASS', ''); // Not required for Mailpit
}

if (!defined('SMTP_FROM_EMAIL')) {
    define('SMTP_FROM_EMAIL', 'itventory@unikl.edu.my');
}

if (!defined('SMTP_FROM_NAME')) {
    define('SMTP_FROM_NAME', 'RCMP ITventory System');
}

if (!function_exists('readSMTPResponse')) {
    /**
     * Read SMTP response (handles multi-line responses)
     *
     * @param resource $smtp SMTP connection resource
     * @return string SMTP response
     */
    function readSMTPResponse($smtp) {
        $response = '';
        while ($line = fgets($smtp, 515)) {
            $response .= $line;
            if (preg_match('/^\d{3} /', $line)) {
                break;
            }
        }
        return $response;
    }
}

if (!function_exists('sendEmail')) {
    /**
     * Send email using SMTP (configured for Mailpit)
     *
     * @param string $to Recipient email address
     * @param string $subject Email subject
     * @param string $message Email message body (HTML supported)
     * @param string|null $from_email Sender email (optional, uses default if not provided)
     * @param string|null $from_name Sender name (optional, uses default if not provided)
     * @return bool True if email was sent successfully, false otherwise
     */
    function sendEmail($to, $subject, $message, $from_email = null, $from_name = null) {
        $from_email = $from_email ?? SMTP_FROM_EMAIL;
        $from_name = $from_name ?? SMTP_FROM_NAME;

        if (!filter_var($to, FILTER_VALIDATE_EMAIL)) {
            error_log("Invalid email address: $to");
            return false;
        }

        $smtp = @fsockopen(SMTP_HOST, SMTP_PORT, $errno, $errstr, 10);
        if (!$smtp) {
            error_log("Failed to connect to SMTP server at " . SMTP_HOST . ":" . SMTP_PORT . " - $errstr ($errno)");
            return false;
        }

        stream_set_timeout($smtp, 10);

        try {
            $response = readSMTPResponse($smtp);
            if (substr($response, 0, 3) !== '220') {
                error_log("SMTP server greeting error: $response");
                fclose($smtp);
                return false;
            }

            $hostname = gethostname() ?: 'localhost';
            fputs($smtp, "EHLO $hostname\r\n");
            $response = readSMTPResponse($smtp);
            if (substr($response, 0, 3) !== '250') {
                error_log("EHLO error: $response");
                fclose($smtp);
                return false;
            }

            fputs($smtp, "MAIL FROM: <$from_email>\r\n");
            $response = readSMTPResponse($smtp);
            if (substr($response, 0, 3) !== '250') {
                error_log("MAIL FROM error: $response");
                fclose($smtp);
                return false;
            }

            fputs($smtp, "RCPT TO: <$to>\r\n");
            $response = readSMTPResponse($smtp);
            if (substr($response, 0, 3) !== '250') {
                error_log("RCPT TO error: $response");
                fclose($smtp);
                return false;
            }

            fputs($smtp, "DATA\r\n");
            $response = readSMTPResponse($smtp);
            if (substr($response, 0, 3) !== '354') {
                error_log("DATA error: $response");
                fclose($smtp);
                return false;
            }

            $from_header = $from_name ? "$from_name <$from_email>" : $from_email;
            $encoded_subject = '=?UTF-8?B?' . base64_encode($subject) . '?=';

            $email_data  = "From: $from_header\r\n";
            $email_data .= "To: <$to>\r\n";
            $email_data .= "Reply-To: $from_email\r\n";
            $email_data .= "Subject: $encoded_subject\r\n";
            $email_data .= "MIME-Version: 1.0\r\n";
            $email_data .= "Content-Type: text/html; charset=UTF-8\r\n";
            $email_data .= "Content-Transfer-Encoding: 8bit\r\n";
            $email_data .= "X-Mailer: PHP/" . phpversion() . "\r\n";
            $email_data .= "Date: " . date('r') . "\r\n\r\n";
            $email_data .= $message . "\r\n.\r\n";

            fputs($smtp, $email_data);
            $response = readSMTPResponse($smtp);

            fputs($smtp, "QUIT\r\n");
            readSMTPResponse($smtp);
            fclose($smtp);

            if (substr($response, 0, 3) === '250') {
                return true;
            }

            error_log("Email sending error: $response");
            return false;
        } catch (Exception $e) {
            error_log("SMTP exception: " . $e->getMessage());
            @fclose($smtp);
            return false;
        }
    }
}
