<?php
namespace Services;

class EmailService {
    public function send(string $to, string $subject, string $message, string $from = 'no-reply@losaudaces.com'): bool {
        $headers = "From: $from\r\n";
        $headers .= "Content-Type: text/html; charset=UTF-8\r\n";
        return mail($to, $subject, $message, $headers);
    }
}
