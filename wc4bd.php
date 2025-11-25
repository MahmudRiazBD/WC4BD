<?php
/**
 * Plugin Name:       WC4BD - Ecommerce Toolkit for Bangladesh
 * Plugin URI:        https://GitHub.com/mahmudriaz.bd/wc4wp
 * Description:       The essential WooCommerce toolkit for Bangladeshi e-commerce. Currently features Invoice & Shipping Label printing with Bulk Actions.
 * Version:           1.0.0
 * Author:            Mahmudul Hasan Riaz
 * Author URI:        https://Riaz.com.bd
 * License:           GPL-2.0+
 * Text Domain:       wc4bd
 */

if (!defined('ABSPATH'))
    exit;

final class WC4BD_Plugin
{
    const VERSION = '1.0.0';
    private static $_instance = null;

    public static function instance()
    {
        if (is_null(self::$_instance))
            self::$_instance = new self();
        return self::$_instance;
    }

    private function __construct()
    {
        // Check if WooCommerce is active before proceeding.
        if (!class_exists('WooCommerce')) {
            add_action('admin_notices', [$this, 'notice_woocommerce_not_active']);
            return;
        }
        $this->define_constants();
        $this->includes();
    }

    public function notice_woocommerce_not_active()
    {
        echo '<div class="error"><p>' . esc_html__('WC4BD Invoice Generator requires WooCommerce to be activated to function.', 'wc4bd') . '</p></div>';
    }

    private function define_constants()
    {
        define('WC4BD_PLUGIN_FILE', __FILE__);
        define('WC4BD_PLUGIN_PATH', plugin_dir_path(__FILE__));
        define('WC4BD_PLUGIN_URL', plugin_dir_url(__FILE__));
    }

    private function includes()
    {
        require_once WC4BD_PLUGIN_PATH . 'includes/class-wc4bd-admin.php';
        require_once WC4BD_PLUGIN_PATH . 'includes/class-wc4bd-frontend.php';
        new WC4BD_Admin();
        new WC4BD_Frontend();
    }
}

// Ensures the plugin is loaded at the right time.
add_action('plugins_loaded', function () {
    WC4BD_Plugin::instance();
});