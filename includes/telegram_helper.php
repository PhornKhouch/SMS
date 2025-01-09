<?php

function sendTelegramMessage($message) {
    $botToken = '5846535674:AAF0hdGkg8w0s0VI-rICf_jiQYixsAPVV0k'; // Telegram bot token
    $chatId = '-675980742';     //  group chat ID
    
    $url = "https://api.telegram.org/bot{$botToken}/sendMessage";
    
    $data = [
        'chat_id' => $chatId,
        'text' => $message,
        'parse_mode' => 'HTML'
    ];
    
    $options = [
        'http' => [
            'method' => 'POST',
            'header' => "Content-Type: application/x-www-form-urlencoded\r\n",
            'content' => http_build_query($data)
        ]
    ];
    
    $context = stream_context_create($options);
    $result = file_get_contents($url, false, $context);
    
    return $result;
}

function formatPaymentMessage($studentName, $amount, $paymentDate, $paymentMethod, $paymentStatus, $payType) {
    $message = "<b>ការបង់ប្រាក់ថ្មី</b>\n\n";
    $message .= "<b>សិស្ស:</b> {$studentName}\n";
    $message .= "<b>ចំនួនទឹកប្រាក់:</b> {$amount}$\n";
    $message .= "<b>កាលបរិច្ឆេទ:</b> {$paymentDate}\n";
    $message .= "<b>វិធីសាស្រ្តបង់ប្រាក់:</b> {$paymentMethod}\n";
    $message .= "<b>ស្ថានភាព:</b> {$paymentStatus}\n";
    $message .= "<b>ប្រភេទ:</b> {$payType}\n";
    
    return $message;
}
