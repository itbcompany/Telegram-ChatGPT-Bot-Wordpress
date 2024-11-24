<?php

require_once plugin_dir_path(__FILE__) . 'logging.php';

function get_chatgpt_response($message, $chatgpt_api_key) {
    $options = get_option('telegram_chatgpt_bot_settings');
    $model = isset($options['telegram_chatgpt_bot_model']) ? $options['telegram_chatgpt_bot_model'] : 'gpt-3.5-turbo';
    $max_tokens = isset($options['telegram_chatgpt_bot_max_tokens']) ? $options['telegram_chatgpt_bot_max_tokens'] : 150;
    $temperature = isset($options['telegram_chatgpt_bot_temperature']) ? $options['telegram_chatgpt_bot_temperature'] : 0.7;

    $url = 'https://api.openai.com/v1/completions';
    $data = array(
        'model' => $model,
        'prompt' => $message,
        'max_tokens' => $max_tokens,
        'temperature' => $temperature
    );
    $args = array(
        'headers' => array(
            'Content-Type' => 'application/json',
            'Authorization' => 'Bearer ' . $chatgpt_api_key,
        ),
        'body' => wp_json_encode($data),
    );

    $response = wp_remote_post($url, $args);

    if (is_wp_error($response)) {
        $error_message = "Ошибка при обращении к ChatGPT API: " . print_r(wp_remote_retrieve_headers($response), true);
        error_log($error_message);
        telegram_chatgpt_bot_send_log($error_message);
        return "Ошибка при обращении к ChatGPT API.";
    }

    $body = wp_remote_retrieve_body($response);
    $decoded_body = json_decode($body, true);

    if (isset($decoded_body['error'])) {
        $error_message = "Ошибка в ответе ChatGPT: " . print_r($decoded_body, true);
        error_log($error_message);
        telegram_chatgpt_bot_send_log($error_message);

        // Проверка различных типов ошибок
        if (isset($decoded_body['error']['code'])) {
            switch ($decoded_body['error']['code']) {
                case 'insufficient_quota':
                    return "Вы превысили свою текущую квоту. Проверьте свой план и платежные данные. Для получения дополнительной информации посетите: https://platform.openai.com/docs/guides/error-codes/api-errors.";
                case 'model_not_found':
                    return "Вы выбрали недоступную модель. Пожалуйста, выберите другую модель.";
                case 'invalid_request_error':
                    return "Ваш запрос содержит ошибки. Пожалуйста, проверьте параметры запроса.";
                case 'not_allowed':
                    return "Вам не разрешено брать образцы из этой модели. Выберите другую модель.";
                default:
                    return "Произошла ошибка. Код ошибки: " . $decoded_body['error']['code'] . ". Подробности: " . $decoded_body['error']['message'];
            }
        }

        return "Ошибка в ответе ChatGPT.";
    }

    if (isset($decoded_body['choices'][0]['text'])) {
        return $decoded_body['choices'][0]['text'];
    } else {
        $error_message = "Неожиданная структура ответа: " . print_r($decoded_body, true);
        error_log($error_message);
        telegram_chatgpt_bot_send_log($error_message);
        return "Неожиданная структура ответа.";
    }
}
