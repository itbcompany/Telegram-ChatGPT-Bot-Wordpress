<?php

function telegram_chatgpt_bot_menu() {
    add_menu_page(
        'Telegram ChatGPT Bot',
        'Настройки бота',
        'manage_options',
        'telegram-chatgpt-bot',
        'telegram_chatgpt_bot_settings_page'
    );
}
add_action('admin_menu', 'telegram_chatgpt_bot_menu');

function telegram_chatgpt_bot_settings_page() {
    ?>
    <div class="wrap">
        <h1>Настройки Telegram ChatGPT Bot</h1>
        <?php if (isset($_GET['settings-updated'])): ?>
            <div id="message" class="updated notice is-dismissible">
                <p><?php _e('Настройки сохранены.', 'telegram-chatgpt-bot'); ?></p>
            </div>
        <?php endif; ?>
        <form method="post" action="options.php">
            <?php
            settings_fields('telegram_chatgpt_bot_settings_group');
            do_settings_sections('telegram-chatgpt-bot');
            submit_button('Сохранить настройки');
            ?>
        </form>
        <hr>
        <form method="post" action="">
            <input type="hidden" name="send_test_message" value="1">
            <?php submit_button('Отправить тестовое сообщение'); ?>
        </form>
        <?php
        if (isset($_POST['send_test_message'])) {
            telegram_chatgpt_bot_send_test_message();
        }
        ?>
    </div>
    <?php
}

function telegram_chatgpt_bot_send_test_message() {
    $options = get_option('telegram_chatgpt_bot_settings');
    $telegram_token = $options['telegram_chatgpt_bot_token'];
    $chat_id = '377895191'; // Укажите ваш ID чата Telegram для тестовых сообщений

    $test_message = "Это тестовое сообщение от Telegram ChatGPT Bot плагина.";
    $response = wp_remote_get("https://api.telegram.org/bot$telegram_token/sendMessage?chat_id=$chat_id&text=" . urlencode($test_message));

    if (is_wp_error($response)) {
        error_log("Error sending test message to Telegram.");
        echo '<div class="error notice"><p>Ошибка отправки тестового сообщения.</p></div>';
    } else {
        echo '<div class="updated notice"><p>Тестовое сообщение успешно отправлено.</p></div>';
    }
}
