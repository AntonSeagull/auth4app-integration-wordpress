<?php
/*
Plugin Name: Auth4App Integration
Description: Интеграция сервиса Auth4App для авторизации через мессенджеры.
Version: 1.1
Author: Your Name
Text Domain: auth4app
Domain Path: /languages
*/

// Загрузка текстовых доменов для перевода
function auth4app_load_textdomain()
{
    load_plugin_textdomain('auth4app', false, dirname(plugin_basename(__FILE__)) . '/languages');
}
add_action('plugins_loaded', 'auth4app_load_textdomain');

// Добавляем страницу настроек
function auth4app_add_admin_menu()
{
    add_options_page(
        __('Auth4App Integration Settings', 'auth4app'),
        'Auth4App',
        'manage_options',
        'auth4app',
        'auth4app_options_page'
    );
}
add_action('admin_menu', 'auth4app_add_admin_menu');

// Регистрируем настройки
function auth4app_settings_init()
{
    register_setting('auth4app', 'auth4app_settings');

    add_settings_section(
        'auth4app_section',
        __('API Key Settings', 'auth4app'),
        'auth4app_settings_section_callback',
        'auth4app'
    );

    add_settings_field(
        'auth4app_apikey',
        __('API Key', 'auth4app'),
        'auth4app_apikey_render',
        'auth4app',
        'auth4app_section'
    );

    add_settings_field(
        'auth4app_phone_mask',
        __('Phone Mask', 'auth4app'),
        'auth4app_phone_mask_render',
        'auth4app',
        'auth4app_section'
    );

    add_settings_field(
        'auth4app_check_interval',
        __('Check Interval (seconds)', 'auth4app'),
        'auth4app_check_interval_render',
        'auth4app',
        'auth4app_section'
    );
}
add_action('admin_init', 'auth4app_settings_init');

function auth4app_apikey_render()
{
    $options = get_option('auth4app_settings');
?>
    <input type='text' name='auth4app_settings[auth4app_apikey]' value='<?php echo $options['auth4app_apikey']; ?>'>
<?php
}

function auth4app_phone_mask_render()
{
    $options = get_option('auth4app_settings');
    $phone_mask = isset($options['auth4app_phone_mask']) ? $options['auth4app_phone_mask'] : '+0 (000) 000-0000';
?>
    <input type='text' name='auth4app_settings[auth4app_phone_mask]' value='<?php echo $phone_mask; ?>'>
<?php
}

function auth4app_check_interval_render()
{
    $options = get_option('auth4app_settings');
    $check_interval = isset($options['auth4app_check_interval']) ? $options['auth4app_check_interval'] : 5;
?>
    <input type='number' name='auth4app_settings[auth4app_check_interval]' value='<?php echo $check_interval; ?>' min='1'>
<?php
}

function auth4app_settings_section_callback()
{
    echo __('Enter your Auth4App API key and other settings here.', 'auth4app');
}

function auth4app_options_page()
{
?>
    <form action='options.php' method='post'>
        <h2><?php _e('Auth4App Integration Settings', 'auth4app'); ?></h2>
        <?php
        settings_fields('auth4app');
        do_settings_sections('auth4app');
        submit_button();
        ?>
    </form>
<?php
}

// Регистрируем шорткод для отображения формы авторизации
function auth4app_form_shortcode()
{
    // Проверяем, авторизован ли пользователь
    if (is_user_logged_in()) {
        return '';
    }

    // Включаем JavaScript и CSS
    wp_enqueue_script('jquery-mask', 'https://cdnjs.cloudflare.com/ajax/libs/jquery.mask/1.14.16/jquery.mask.min.js', array('jquery'), null, true);
    wp_enqueue_script('auth4app-script', plugins_url('/js/auth4app.js', __FILE__), array('jquery', 'jquery-mask'), null, true);
    wp_enqueue_style('auth4app-style', plugins_url('/css/auth4app.css', __FILE__));

    // Получаем настройки плагина
    $options = get_option('auth4app_settings');
    $phone_mask = isset($options['auth4app_phone_mask']) ? $options['auth4app_phone_mask'] : '+0 (000) 000-0000';

    // Передаем настройки в JavaScript
    wp_localize_script('auth4app-script', 'auth4app_settings', array(
        'phone_mask' => $phone_mask,
        'check_interval' => isset($options['auth4app_check_interval']) ? $options['auth4app_check_interval'] : 5,
        'phone_label' => __('Ваш код', 'auth4app'),
        'copy_code_button' => __('Скопировать код', 'auth4app'),
        'copy_instructions' => __('Скопируйте код и вставьте его в любом удобном мессенджере:', 'auth4app'),
        'code_copied' => __('Код скопирован', 'auth4app')
    ));

    // HTML форма для ввода номера телефона
    return '
    <div id="auth4app-container">
        <form id="auth4app-form">
            <label for="auth4app-phone">' . __('Введите номер телефона:', 'auth4app') . '</label>
            <input type="tel" id="auth4app-phone" name="phone" required>
            <button type="submit">' . __('Получить код', 'auth4app') . '</button>
        </form>
        <div id="auth4app-links"></div>
    </div>';
}
add_shortcode('auth4app_form', 'auth4app_form_shortcode');

// Обрабатываем AJAX запрос для получения кода авторизации
function auth4app_get_code()
{
    $phone = sanitize_text_field($_POST['phone']);
    $options = get_option('auth4app_settings');
    $apikey = $options['auth4app_apikey'];

    // Делаем запрос к API Auth4App
    $response = wp_remote_post('https://api.auth4app.com/code/get', array(
        'method' => 'POST',
        'body' => json_encode(array('phone' => $phone, 'api_key' => $apikey)),
        'headers' => array(
            'Content-Type' => 'application/json'
        )
    ));

    if (is_wp_error($response)) {
        wp_send_json_error(__('Ошибка при запросе к API', 'auth4app'));
    } else {
        $body = wp_remote_retrieve_body($response);
        $data = json_decode($body, true);
        wp_send_json_success($data);
    }

    wp_die();
}
add_action('wp_ajax_auth4app_get_code', 'auth4app_get_code');
add_action('wp_ajax_nopriv_auth4app_get_code', 'auth4app_get_code');

// Обрабатываем AJAX запрос для проверки статуса авторизации
function auth4app_check_status()
{
    $code_id = sanitize_text_field($_POST['code_id']);
    $options = get_option('auth4app_settings');
    $apikey = $options['auth4app_apikey'];

    // Делаем запрос к API Auth4App
    $response = wp_remote_post('https://api.auth4app.com/code/result', array(
        'method' => 'POST',
        'body' => json_encode(array('code_id' => $code_id, 'api_key' => $apikey)),
        'headers' => array(
            'Content-Type' => 'application/json'
        )
    ));

    if (is_wp_error($response)) {
        wp_send_json_error(__('Ошибка при запросе к API', 'auth4app'));
    } else {
        $body = wp_remote_retrieve_body($response);
        $data = json_decode($body, true);

        if ($data['auth']) {
            $phone = $data['phone'];
            $user = get_user_by('email', $phone . '@auth4app.local');

            if (!$user) {
                // Создаем нового пользователя, если он не существует
                $user_id = wp_create_user($phone, wp_generate_password(), $phone . '@auth4app.local');
                if (is_wp_error($user_id)) {
                    wp_send_json_error(__('Не удалось создать пользователя.', 'auth4app'));
                }
                $user = get_user_by('id', $user_id);
            }

            // Авторизуем пользователя
            wp_set_current_user($user->ID);
            wp_set_auth_cookie($user->ID);
            wp_send_json_success(array('message' => __('Вы успешно авторизованы.', 'auth4app'), 'redirect' => home_url()));
        } else {
            wp_send_json_error(__('Авторизация не удалась.', 'auth4app'));
        }
    }

    wp_die();
}
add_action('wp_ajax_auth4app_check_status', 'auth4app_check_status');
add_action('wp_ajax_nopriv_auth4app_check_status', 'auth4app_check_status');

function auth4app_localize_script()
{
    wp_localize_script('auth4app-script', 'auth4app_ajax', array(
        'url' => admin_url('admin-ajax.php')
    ));
}
add_action('wp_enqueue_scripts', 'auth4app_localize_script');
?>