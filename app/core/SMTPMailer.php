<?php

class SMTPMailer {
    
    public static function sendMail($to, $subject, $message, $from_email, $from_name, $smtp_host, $smtp_port, $username, $password) {
        
        // Crear conexión socket
        $socket = fsockopen($smtp_host, $smtp_port, $errno, $errstr, 30);
        if (!$socket) {
            error_log("SMTP Error: Could not connect to $smtp_host:$smtp_port - $errstr ($errno)");
            return false;
        }
        
        // Leer respuesta del servidor
        $response = fgets($socket, 515);
        if (substr($response, 0, 3) != '220') {
            error_log("SMTP Error: Unexpected response: $response");
            fclose($socket);
            return false;
        }
        
        // Enviar EHLO
        fputs($socket, "EHLO localhost\r\n");
        
        // Leer todas las líneas de respuesta EHLO
        do {
            $response = fgets($socket, 515);
            $ehlo_lines[] = $response;
        } while (substr($response, 3, 1) == '-'); // Continuar hasta que no haya más líneas
        
        // STARTTLS
        fputs($socket, "STARTTLS\r\n");
        $response = fgets($socket, 515);
        if (substr($response, 0, 3) != '220') {
            error_log("SMTP Error: STARTTLS failed: $response");
            fclose($socket);
            return false;
        }
        
        // Cambiar a conexión segura
        if (!stream_socket_enable_crypto($socket, true, STREAM_CRYPTO_METHOD_TLS_CLIENT)) {
            error_log("SMTP Error: Failed to enable TLS");
            fclose($socket);
            return false;
        }
        
        // EHLO nuevamente después de TLS
        fputs($socket, "EHLO localhost\r\n");
        
        // Leer todas las líneas de respuesta EHLO
        do {
            $response = fgets($socket, 515);
        } while (substr($response, 3, 1) == '-');
        
        // AUTH LOGIN
        fputs($socket, "AUTH LOGIN\r\n");
        $response = fgets($socket, 515);
        if (substr($response, 0, 3) != '334') {
            error_log("SMTP Error: AUTH LOGIN failed: $response");
            fclose($socket);
            return false;
        }
        
        // Enviar username codificado
        fputs($socket, base64_encode($username) . "\r\n");
        $response = fgets($socket, 515);
        if (substr($response, 0, 3) != '334') {
            error_log("SMTP Error: Username rejected: $response");
            fclose($socket);
            return false;
        }
        
        // Enviar password codificado
        fputs($socket, base64_encode($password) . "\r\n");
        $response = fgets($socket, 515);
        if (substr($response, 0, 3) != '235') {
            error_log("SMTP Error: Authentication failed: $response");
            fclose($socket);
            return false;
        }
        
        // MAIL FROM
        fputs($socket, "MAIL FROM: <$from_email>\r\n");
        $response = fgets($socket, 515);
        if (substr($response, 0, 3) != '250') {
            error_log("SMTP Error: MAIL FROM failed: $response");
            fclose($socket);
            return false;
        }
        
        // RCPT TO
        fputs($socket, "RCPT TO: <$to>\r\n");
        $response = fgets($socket, 515);
        if (substr($response, 0, 3) != '250') {
            error_log("SMTP Error: RCPT TO failed: $response");
            fclose($socket);
            return false;
        }
        
        // DATA
        fputs($socket, "DATA\r\n");
        $response = fgets($socket, 515);
        if (substr($response, 0, 3) != '354') {
            error_log("SMTP Error: DATA command failed: $response");
            fclose($socket);
            return false;
        }
        
        // Construir headers
        $headers = "From: $from_name <$from_email>\r\n";
        $headers .= "Reply-To: $from_email\r\n";
        $headers .= "MIME-Version: 1.0\r\n";
        $headers .= "Content-Type: text/html; charset=UTF-8\r\n";
        $headers .= "Subject: $subject\r\n";
        $headers .= "\r\n";
        
        // Enviar email completo
        fputs($socket, $headers . $message . "\r\n.\r\n");
        $response = fgets($socket, 515);
        if (substr($response, 0, 3) != '250') {
            error_log("SMTP Error: Message not accepted: $response");
            fclose($socket);
            return false;
        }
        
        // QUIT
        fputs($socket, "QUIT\r\n");
        fclose($socket);
        
        return true;
    }
}