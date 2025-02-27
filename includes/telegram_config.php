<?php
// Telegram Bot Configuration
define('TELEGRAM_BOT_TOKEN', '5846535674:AAF0hdGkg8w0s0VI-rICf_jiQYixsAPVV0k'); // Replace with your bot token
define('TELEGRAM_API_URL', 'https://api.telegram.org/bot' . TELEGRAM_BOT_TOKEN);

// Test bot token validity
function testBotToken() {
    $url = TELEGRAM_API_URL . '/getMe';
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    // Disable SSL verification for development
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
    
    $result = curl_exec($ch);
    
    if ($result === false) {
        error_log("Curl error in testBotToken: " . curl_error($ch));
        curl_close($ch);
        return false;
    }
    
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    error_log("Bot test response: " . $result);
    error_log("Bot test HTTP code: " . $httpCode);
    
    return $httpCode === 200;
}

/**
 * Send document via Telegram
 * @param string $chat_id Telegram chat ID
 * @param string $file_path Local path to the file
 * @param string $caption Optional caption for the document
 * @return bool Success status
 */
function sendTelegramDocument($chat_id, $file_path, $caption = '') {
    // First test if bot token is valid
    if (!testBotToken()) {
        error_log("Bot token is invalid");
        return false;
    }

    error_log("Attempting to send Telegram document");
    error_log("Chat ID: " . $chat_id);
    error_log("File path: " . $file_path);
    error_log("File exists: " . (file_exists($file_path) ? 'yes' : 'no'));
    error_log("File size: " . (file_exists($file_path) ? filesize($file_path) : 'N/A'));
    
    if (!file_exists($file_path)) {
        error_log("File does not exist: " . $file_path);
        return false;
    }

    $url = TELEGRAM_API_URL . '/sendDocument';
    
    // Create CURLFile object
    $document = new CURLFile($file_path);
    error_log("CURLFile created for: " . $document->getFilename());
    
    $data = [
        'chat_id' => $chat_id,
        'document' => $document,
        'caption' => $caption
    ];
    
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    // Disable SSL verification for development
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
    
    // Add verbose debugging
    curl_setopt($ch, CURLOPT_VERBOSE, true);
    $verbose = fopen('php://temp', 'w+');
    curl_setopt($ch, CURLOPT_STDERR, $verbose);
    
    $result = curl_exec($ch);
    
    if ($result === false) {
        error_log("Curl error: " . curl_error($ch));
        rewind($verbose);
        $verboseLog = stream_get_contents($verbose);
        error_log("Verbose curl log: " . $verboseLog);
    } else {
        error_log("Telegram API response: " . $result);
    }
    
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    error_log("HTTP response code: " . $httpCode);
    
    curl_close($ch);
    fclose($verbose);
    
    return $httpCode === 200 && !empty($result);
}
