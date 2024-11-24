<?php

require_once plugin_dir_path(__FILE__) . 'logging.php';

function process_telegram_webhook() {
    $options = get_option('telegram_chatgpt_bot_settings');
    $telegram_token = $options['telegram_chatgpt_bot_token'];
    $chatgpt_api_key = $options['telegram_chatgpt_bot_api_key'];

    if ($_SERVER["REQUEST_METHOD"] == "POST" && strpos($_SERVER['REQUEST_URI'], '/wp-json/telegram-chatgpt-bot/webhook') !== false) {
        $update = json_decode(file_get_contents("php://input"), true);

        if (!empty($update['message'])) {
            $message_id = $update['message']['message_id'];
            $message = $update['message']['text'];
            $chat_id = $update['message']['chat']['id'];

            error_log("Received message: $message_id");

            $last_processed_message_id = get_option('telegram_chatgpt_bot_last_processed_message_id');
            if ($message_id != $last_processed_message_id) {
                update_option('telegram_chatgpt_bot_last_processed_message_id', $message_id);

                $response = get_chatgpt_response($message, $chatgpt_api_key);

                $sendMessageUrl = "https://api.telegram.org/bot$telegram_token/sendMessage";
                $sendMessageData = array('chat_id' => $chat_id, 'text' => $response);

                $args = array(
                    'headers' => array(
                        'Content-Type' => 'application/json',
                    ),
                    'body' => wp_json_encode($sendMessageData),
                );

                $response = wp_remote_post($sendMessageUrl, $args);

                if (is_wp_error($response)) {
                    error_log("Error sending message to Telegram.");
                    telegram_chatgpt_bot_send_log("Error sending message to Telegram.");
                }
            } else {
                error_log("Duplicate message detected: $message_id");
            }
        }
    }
}
add_action('rest_api_init', function () {
    register_rest_route('telegram-chatgpt-bot', '/webhook', array(
        'methods' => 'POST',
        'callback' => 'process_telegram_webhook',
    ));
});
