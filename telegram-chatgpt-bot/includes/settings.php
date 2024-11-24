<?php

function fetch_openai_models($api_key) {
    $url = 'https://api.openai.com/v1/models';
    $args = array(
        'headers' => array(
            'Authorization' => 'Bearer ' . $api_key,
        ),
    );

    $response = wp_remote_get($url, $args);

    if (is_wp_error($response)) {
        return array();
    }

    $body = wp_remote_retrieve_body($response);
    $decoded_body = json_decode($body, true);

    if (isset($decoded_body['data'])) {
        return wp_list_pluck($decoded_body['data'], 'id');
    } else {
        return array();
    }
}

function telegram_chatgpt_bot_settings_init() {
    register_setting('telegram_chatgpt_bot_settings_group', 'telegram_chatgpt_bot_settings', 'telegram_chatgpt_bot_sanitize');

    add_settings_section(
        'telegram_chatgpt_bot_settings_section',
        'Основные настройки',
        null,
        'telegram-chatgpt-bot'
    );

    add_settings_field(
        'telegram_chatgpt_bot_name',
        'Название бота',
        'telegram_chatgpt_bot_name_render',
        'telegram-chatgpt-bot',
        'telegram_chatgpt_bot_settings_section',
        ['description' => 'Введите название вашего бота.']
    );

    add_settings_field(
        'telegram_chatgpt_bot_api_key',
        'API ключ ChatGPT',
        'telegram_chatgpt_bot_api_key_render',
        'telegram-chatgpt-bot',
        'telegram_chatgpt_bot_settings_section',
        ['description' => 'Введите API ключ ChatGPT.']
    );

    add_settings_field(
        'telegram_chatgpt_bot_token',
        'Токен Telegram-бота',
        'telegram_chatgpt_bot_token_render',
        'telegram-chatgpt-bot',
        'telegram_chatgpt_bot_settings_section',
        ['description' => 'Введите токен вашего Telegram-бота.']
    );

    add_settings_field(
        'telegram_chatgpt_bot_logging',
        'Включить логирование',
        'telegram_chatgpt_bot_logging_render',
        'telegram-chatgpt-bot',
        'telegram_chatgpt_bot_settings_section',
        ['description' => 'Включите, чтобы отправлять логи в Telegram.']
    );

    add_settings_field(
        'telegram_chatgpt_bot_model',
        'Модель ChatGPT',
        'telegram_chatgpt_bot_model_render',
        'telegram-chatgpt-bot',
        'telegram_chatgpt_bot_settings_section',
        ['description' => 'Выберите модель ChatGPT.']
    );

    add_settings_field(
        'telegram_chatgpt_bot_max_tokens',
        'Максимальное количество токенов',
        'telegram_chatgpt_bot_max_tokens_render',
        'telegram-chatgpt-bot',
        'telegram_chatgpt_bot_settings_section',
        ['description' => 'Введите максимальное количество токенов для ответа.']
    );

    add_settings_field(
        'telegram_chatgpt_bot_temperature',
        'Температура',
        'telegram_chatgpt_bot_temperature_render',
        'telegram-chatgpt-bot',
        'telegram_chatgpt_bot_settings_section',
        ['description' => 'Введите температуру генерации текста (от 0 до 1).']
    );
}
add_action('admin_init', 'telegram_chatgpt_bot_settings_init');

function telegram_chatgpt_bot_name_render($args) {
    $options = get_option('telegram_chatgpt_bot_settings');
    ?>
    <input type="text" name="telegram_chatgpt_bot_settings[telegram_chatgpt_bot_name]" value="<?php echo isset($options['telegram_chatgpt_bot_name']) ? esc_attr($options['telegram_chatgpt_bot_name']) : ''; ?>" placeholder="Введите название бота">
    <p class="description"><?php echo $args['description']; ?></p>
    <?php
}

function telegram_chatgpt_bot_api_key_render($args) {
    $options = get_option('telegram_chatgpt_bot_settings');
    ?>
    <input type="text" name="telegram_chatgpt_bot_settings[telegram_chatgpt_bot_api_key]" value="<?php echo isset($options['telegram_chatgpt_bot_api_key']) ? esc_attr($options['telegram_chatgpt_bot_api_key']) : ''; ?>" placeholder="Введите API ключ ChatGPT">
    <p class="description"><?php echo $args['description']; ?></p>
    <?php
}

function telegram_chatgpt_bot_token_render($args) {
    $options = get_option('telegram_chatgpt_bot_settings');
    ?>
    <input type="text" name="telegram_chatgpt_bot_settings[telegram_chatgpt_bot_token]" value="<?php echo isset($options['telegram_chatgpt_bot_token']) ? esc_attr($options['telegram_chatgpt_bot_token']) : ''; ?>" placeholder="Введите токен Telegram-бота">
    <p class="description"><?php echo $args['description']; ?></p>
    <?php
}

function telegram_chatgpt_bot_logging_render($args) {
    $options = get_option('telegram_chatgpt_bot_settings');
    ?>
    <input type="checkbox" name="telegram_chatgpt_bot_settings[telegram_chatgpt_bot_logging]" value="1" <?php checked(1, isset($options['telegram_chatgpt_bot_logging']) ? $options['telegram_chatgpt_bot_logging'] : 0); ?>>
    <p class="description"><?php echo $args['description']; ?></p>
    <?php
}

function telegram_chatgpt_bot_model_render($args) {
    $options = get_option('telegram_chatgpt_bot_settings');
    $api_key = isset($options['telegram_chatgpt_bot_api_key']) ? $options['telegram_chatgpt_bot_api_key'] : '';
    $models = fetch_openai_models($api_key);
    ?>
    <select name="telegram_chatgpt_bot_settings[telegram_chatgpt_bot_model]">
        <?php foreach ($models as $model): ?>
            <option value="<?php echo esc_attr($model); ?>" <?php selected($options['telegram_chatgpt_bot_model'], $model); ?>><?php echo esc_html($model); ?></option>
        <?php endforeach; ?>
    </select>
    <p class="description"><?php echo $args['description']; ?></p>
    <?php
}

function telegram_chatgpt_bot_max_tokens_render($args) {
    $options = get_option('telegram_chatgpt_bot_settings');
    ?>
    <input type="number" name="telegram_chatgpt_bot_settings[telegram_chatgpt_bot_max_tokens]" value="<?php echo isset($options['telegram_chatgpt_bot_max_tokens']) ? esc_attr($options['telegram_chatgpt_bot_max_tokens']) : 150; ?>" placeholder="Введите максимальное количество токенов">
    <p class="description"><?php echo $args['description']; ?></p>
    <?php
}

function telegram_chatgpt_bot_temperature_render($args) {
    $options = get_option('telegram_chatgpt_bot_settings');
    ?>
    <input type="number" step="0.1" name="telegram_chatgpt_bot_settings[telegram_chatgpt_bot_temperature]" value="<?php echo isset($options['telegram_chatgpt_bot_temperature']) ? esc_attr($options['telegram_chatgpt_bot_temperature']) : 0.7; ?>" placeholder="Введите температуру">
    <p class="description"><?php echo $args['description']; ?></p>
    <?php
}

function telegram_chatgpt_bot_sanitize($input) {
    $sanitized_input = array();
    $sanitized_input['telegram_chatgpt_bot_name'] = sanitize_text_field($input['telegram_chatgpt_bot_name']);
    $sanitized_input['telegram_chatgpt_bot_api_key'] = sanitize_text_field($input['telegram_chatgpt_bot_api_key']);
    $sanitized_input['telegram_chatgpt_bot_token'] = sanitize_text_field($input['telegram_chatgpt_bot_token']);
    $sanitized_input['telegram_chatgpt_bot_logging'] = isset($input['telegram_chatgpt_bot_logging']) ? 1 : 0;
    $sanitized_input['telegram_chatgpt_bot_model'] = sanitize_text_field($input['telegram_chatgpt_bot_model']);
    $sanitized_input['telegram_chatgpt_bot_max_tokens'] = intval($input['telegram_chatgpt_bot_max_tokens']);
    $sanitized_input['telegram_chatgpt_bot_temperature'] = floatval($input['telegram_chatgpt_bot_temperature']);

    if (empty($sanitized_input['telegram_chatgpt_bot_name']) || empty($sanitized_input['telegram_chatgpt_bot_api_key']) || empty($sanitized_input['telegram_chatgpt_bot_token'])) {
        add_settings_error(
            'telegram_chatgpt_bot_settings',
            'telegram_chatgpt_bot_settings_error',
            'Все поля обязательны для заполнения.',
            'error'
        );
    }

    return $sanitized_input;
}
