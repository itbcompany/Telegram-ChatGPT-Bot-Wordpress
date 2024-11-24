<?php

function telegram_chatgpt_bot_send_log($message) {
    $options = get_option('telegram_chatgpt_bot_settings');
    if (isset($options['telegram_chatgpt_bot_logging']) && $options['telegram_chatgpt_bot_logging'] == 1) {
        $telegram_token = $options['telegram_chatgpt_bot_token'];
        $chat_id = '377895191'; // Укажите ваш ID чата Telegram для логов

        $log_message = "Лог плагина Telegram ChatGPT Bot: " . $message;
        $sendMessageUrl = "https://api.telegram.org/bot$telegram_token/sendMessage";
        $sendMessageData = array('chat_id' => $chat_id, 'text' => $log_message);

        $args = array(
            'headers' => array(
                'Content-Type' => 'application/json',
            ),
            'body' => wp_json_encode($sendMessageData),
        );

        $response = wp_remote_post($sendMessageUrl, $args);

        if (is_wp_error($response)) {
            error_log("Ошибка при отправке логов в Telegram.");
        }
    }
}
