<?php

require_once __DIR__ . '/helpers.php';

function sendEmail(string $to, string $subject, string $text, string $html = ''): bool
{
    if (!EMAIL_USER || !EMAIL_PASS) {
        error_log("[Email skipped] To: {$to} | Subject: {$subject}");
        return false;
    }

    // Use PHPMailer
    $composerAutoload = __DIR__ . '/../../vendor/autoload.php';
    if (file_exists($composerAutoload)) {
        require_once $composerAutoload;
        $mail = new PHPMailer\PHPMailer\PHPMailer(true);
        try {
            $mail->isSMTP();
            $mail->Host       = 'smtp.gmail.com';
            $mail->SMTPAuth   = true;
            $mail->Username   = EMAIL_USER;
            $mail->Password   = EMAIL_PASS;
            $mail->SMTPSecure = 'tls';
            $mail->Port       = 587;

            $mail->setFrom(EMAIL_USER, EMAIL_FROM_NAME);
            $mail->addAddress($to);
            $mail->Subject = $subject;
            $mail->CharSet = 'UTF-8';

            if ($html) {
                $mail->isHTML(true);
                $mail->Body    = $html;
                $mail->AltBody = $text;
            } else {
                $mail->isHTML(false);
                $mail->Body = $text;
            }

            $mail->send();
            return true;
        } catch (Exception $e) {
            error_log('[Email error] ' . $mail->ErrorInfo);
            return false;
        }
    }

    // Fallback: direct SMTP using fsockopen (no library needed)
    return sendEmailSMTP($to, $subject, $text, $html);
}

function sendEmailSMTP(string $to, string $subject, string $text, string $html = ''): bool
{
    $host    = 'smtp.gmail.com';
    $port    = 587;
    $user    = EMAIL_USER;
    $pass    = EMAIL_PASS;
    $from    = EMAIL_USER;
    $name    = EMAIL_FROM_NAME;

    $socket = @fsockopen($host, $port, $errno, $errstr, 15);
    if (!$socket) {
        error_log("[Email SMTP] Cannot connect to {$host}:{$port} - {$errstr}");
        return false;
    }

    $read = function() use ($socket) {
        $data = '';
        while ($line = fgets($socket, 515)) {
            $data .= $line;
            if (substr($line, 3, 1) === ' ') break;
        }
        return $data;
    };
    $send = function(string $cmd) use ($socket) {
        fwrite($socket, $cmd . "\r\n");
    };

    $read();
    $send('EHLO smtp.gmail.com');
    $read();
    $send('STARTTLS');
    $read();

    stream_socket_enable_crypto($socket, true, STREAM_CRYPTO_METHOD_TLS_CLIENT);

    $send('EHLO smtp.gmail.com');
    $read();
    $send('AUTH LOGIN');
    $read();
    $send(base64_encode($user));
    $read();
    $send(base64_encode($pass));
    $resp = $read();

    if (strpos($resp, '235') === false) {
        error_log('[Email SMTP] Auth failed');
        fclose($socket);
        return false;
    }

    $send("MAIL FROM:<{$from}>");
    $read();
    $send("RCPT TO:<{$to}>");
    $read();
    $send('DATA');
    $read();

    $boundary = md5((string)time());
    $headers  = "From: {$name} <{$from}>\r\n";
    $headers .= "To: {$to}\r\n";
    $headers .= "Subject: {$subject}\r\n";
    $headers .= "MIME-Version: 1.0\r\n";
    $headers .= "Content-Type: multipart/alternative; boundary=\"{$boundary}\"\r\n";

    $body  = "--{$boundary}\r\n";
    $body .= "Content-Type: text/plain; charset=UTF-8\r\n\r\n{$text}\r\n";
    if ($html) {
        $body .= "--{$boundary}\r\n";
        $body .= "Content-Type: text/html; charset=UTF-8\r\n\r\n{$html}\r\n";
    }
    $body .= "--{$boundary}--";

    $send($headers . "\r\n" . $body . "\r\n.");
    $read();
    $send('QUIT');
    fclose($socket);

    return true;
}

function sendWhatsAppMessage(string $to, string $body): bool
{
    $phone = formatPhoneForWhatsApp($to);

    if (defined('WHATSAPP_BRIDGE_URL') && WHATSAPP_BRIDGE_URL && strpos(WHATSAPP_BRIDGE_URL, 'your-node-server') === false) {
        $ch = curl_init(WHATSAPP_BRIDGE_URL);
        curl_setopt_array($ch, [
            CURLOPT_POST          => true,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER    => ['Content-Type: application/json'],
            CURLOPT_POSTFIELDS    => json_encode(['to' => $phone, 'message' => $body]),
            CURLOPT_TIMEOUT       => 15,
            CURLOPT_SSL_VERIFYPEER => true,
        ]);
        $result = curl_exec($ch);
        $code   = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        return $code >= 200 && $code < 300;
    }

    if (defined('TWILIO_ACCOUNT_SID') && TWILIO_ACCOUNT_SID && defined('TWILIO_AUTH_TOKEN') && TWILIO_AUTH_TOKEN) {
        $url = 'https://api.twilio.com/2010-04-01/Accounts/' . TWILIO_ACCOUNT_SID . '/Messages.json';
        $ch  = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_POST          => true,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_USERPWD       => TWILIO_ACCOUNT_SID . ':' . TWILIO_AUTH_TOKEN,
            CURLOPT_POSTFIELDS    => http_build_query([
                'From' => TWILIO_WHATSAPP_FROM,
                'To'   => 'whatsapp:+' . ltrim($phone, '+'),
                'Body' => $body,
            ]),
            CURLOPT_TIMEOUT       => 15,
        ]);
        curl_exec($ch);
        $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        return $code >= 200 && $code < 300;
    }

    error_log("[WhatsApp skipped] To: {$to}\n{$body}");
    return false;
}

