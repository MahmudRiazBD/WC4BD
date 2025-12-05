<?php
if (!defined('ABSPATH'))
    exit;

class WC4BD_Frontend
{
    public function __construct()
    {
        add_filter('query_vars', [$this, 'register_query_vars']);
        add_action('template_redirect', [$this, 'load_print_template']);
    }

    public function register_query_vars($vars)
    {
        $vars[] = 'print_wc4bd_invoices';
        $vars[] = 'print_wc4bd_stickers';
        $vars[] = 'order_ids';
        $vars[] = '_wpnonce';
        return $vars;
    }

    public function load_print_template()
    {
        if (isset($_GET['_wpnonce']) && wp_verify_nonce(sanitize_key($_GET['_wpnonce']), 'wc4bd_print_invoices')) {

            $order_ids_str = get_query_var('order_ids');
            if (!$order_ids_str)
                return;

            global $wc4bd_order_ids;
            $wc4bd_order_ids = array_map('absint', explode(',', sanitize_text_field($order_ids_str)));

            $template_name = '';
            if (get_query_var('print_wc4bd_invoices')) {
                $template_name = 'invoice-template.php';
            } elseif (get_query_var('print_wc4bd_stickers')) {
                $template_name = 'sticker-template.php';
            } else {
                return;
            }

            // **UPDATED**: Changed from wp_enqueue_scripts to wp_print_styles to ensure it loads later
            add_action('wp_print_styles', [$this, 'enqueue_print_assets']);

            $template = locate_template(['wc4bd/' . $template_name]) ?: WC4BD_PLUGIN_PATH . 'templates/' . $template_name;
            if (file_exists($template)) {
                include $template;
                exit;
            }
        }
    }

    /**
     * Enqueues styles and adds inline CSS to hide conflicting elements like Chaty.
     */
    public function enqueue_print_assets()
    {
        // Enqueue the main stylesheet for our templates
        $handle = 'wc4bd-print-style';

        if (get_query_var('print_wc4bd_stickers')) {
            $handle = 'wc4bd-sticker-style';
            wp_enqueue_style($handle, WC4BD_PLUGIN_URL . 'assets/css/sticker-style.css', [], WC4BD_Plugin::VERSION);
        } else {
            wp_enqueue_style($handle, WC4BD_PLUGIN_URL . 'assets/css/invoice-style.css', [], WC4BD_Plugin::VERSION);
        }

        // **NEW**: Dequeue all other styles and scripts to prevent theme interference
        global $wp_styles, $wp_scripts;

        // Dequeue Styles
        if (isset($wp_styles->queue)) {
            foreach ($wp_styles->queue as $style_handle) {
                if ($style_handle !== 'wc4bd-print-style' && $style_handle !== 'wc4bd-sticker-style' && $style_handle !== 'admin-bar') {
                    wp_dequeue_style($style_handle);
                }
            }
        }

        // Dequeue Scripts (except essential ones if needed, but usually print templates don't need theme scripts)
        if (isset($wp_scripts->queue)) {
            foreach ($wp_scripts->queue as $script_handle) {
                if ($script_handle !== 'admin-bar') { // Keep admin bar script if needed, or remove it too
                    wp_dequeue_script($script_handle);
                }
            }
        }

        // Add inline CSS to hide third-party plugins in both screen and print
        $custom_css = "
            /* Hide Chaty Plugin and other sticky elements */
            .chaty-widget-i,
            .chaty-main-widget,
            #chaty-btn,
            .chaty-widget,
            .sticky,
            .fixed,
            #scroll-top,
            #wpadminbar {
                display: none !important;
                visibility: hidden !important;
            }
            
            /* Ensure clean print layout */
            @media print {
                .chaty-widget-i,
                .chaty-main-widget,
                #chaty-btn,
                .chaty-widget,
                .sticky,
                .fixed,
                #scroll-top,
                #wpadminbar,
                header:not(.wc4bd-invoice-header),
                footer:not(.wc4bd-invoice-footer),
                nav,
                aside {
                    display: none !important;
                    visibility: hidden !important;
                }
            }
        ";
        wp_add_inline_style($handle, $custom_css);
    }
}