<?php
/**
 * Plugin Name: Telegram ChatGPT Bot
 * Description: Плагин для настройки бота Telegram и API ChatGPT.
 * Version: 1.5
 * Author: DimasBaz
 */

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

// Подключение необходимых файлов
require_once plugin_dir_path(__FILE__) . 'includes/settings.php';
require_once plugin_dir_path(__FILE__) . 'includes/webhook.php';
require_once plugin_dir_path(__FILE__) . 'includes/chatgpt-api.php';
require_once plugin_dir_path(__FILE__) . 'includes/logging.php';
require_once plugin_dir_path(__FILE__) . 'admin/admin-page.php';

// Регистрируем REST API маршрут
add_action('rest_api_init', function () {
    register_rest_route('telegram-chatgpt-bot', '/send-message', array(
        'methods' => 'POST',
        'callback' => 'send_message_to_chatgpt',
    ));
});

function send_message_to_chatgpt(WP_REST_Request $request) {
    $message = $request->get_param('message');
    $api_key = get_option('telegram_chatgpt_bot_settings')['telegram_chatgpt_bot_api_key'];
    
    // Получаем ответ от ChatGPT
    $response = get_chatgpt_response($message, $api_key);

    return rest_ensure_response(array(
        'success' => true,
        'response' => $response,
    ));
}

// Подключение стилей и скриптов консультанта
function telegram_chatgpt_bot_enqueue_scripts() {
    wp_enqueue_style('consultant-css', plugin_dir_url(__FILE__) . 'assets/css/consultant.css');
    wp_enqueue_script('consultant-js', plugin_dir_url(__FILE__) . 'assets/js/consultant.js', array('jquery'), null, true);
}
add_action('wp_enqueue_scripts', 'telegram_chatgpt_bot_enqueue_scripts');
