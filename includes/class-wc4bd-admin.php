<?php
if (!defined('ABSPATH'))
    exit;

class WC4BD_Admin
{
    public function __construct()
    {
        // --- Settings Page ---
        add_action('admin_menu', [$this, 'add_settings_page']);
        add_action('admin_init', [$this, 'register_settings']);
        add_action('admin_enqueue_scripts', [$this, 'enqueue_settings_scripts']);

        // --- Order List "Actions" Column ---
        add_filter('manage_edit-shop_order_columns', [$this, 'add_actions_column'], 20);
        add_action('manage_shop_order_posts_custom_column', [$this, 'render_actions_column'], 10, 2);
        add_filter('woocommerce_shop_order_list_table_columns', [$this, 'add_actions_column'], 20);
        add_action('woocommerce_shop_order_list_table_custom_column', [$this, 'render_actions_column_hpos'], 10, 2);

        // --- Single Order Edit Page Button ---
        add_action('add_meta_boxes', [$this, 'add_invoice_meta_box']);

        // --- Custom Bulk Print Button ---
        add_action('admin_footer', [$this, 'add_custom_bulk_print_button_js']);
    }

    // --- Feature 1: "Actions" Column ---
    public function add_actions_column($columns)
    {
        $reordered_columns = [];
        foreach ($columns as $key => $column) {
            $reordered_columns[$key] = $column;
            if ('order_total' === $key) {
                $reordered_columns['wc4bd_actions'] = esc_html__('Actions', 'wc4bd');
            }
        }
        return $reordered_columns;
    }

    public function render_actions_column($column, $order_id)
    {
        if ('wc4bd_actions' === $column) {
            $this->print_action_icons($order_id);
        }
    }

    public function render_actions_column_hpos($column, $order)
    {
        if ('wc4bd_actions' === $column) {
            $this->print_action_icons($order->get_id());
        }
    }

    private function print_action_icons($order_id)
    {
        $nonce = wp_create_nonce('wc4bd_print_invoices');
        $invoice_url = add_query_arg(['print_wc4bd_invoices' => 'true', 'order_ids' => $order_id, '_wpnonce' => $nonce], home_url('/'));
        $sticker_url = add_query_arg(['print_wc4bd_stickers' => 'true', 'order_ids' => $order_id, '_wpnonce' => $nonce], home_url('/'));

        echo '<div class="wc4bd-actions-container" style="display:flex; align-items:center;">';
        // Invoice Icon
        printf(
            '<a href="%s" target="_blank" class="button wc-action-button" title="%s" style="margin-right:5px; padding: 5px 6px; height: auto; line-height: 1;"><span class="dashicons dashicons-printer" style="font-size: 18px; vertical-align: middle;"></span></a>',
            esc_url($invoice_url),
            esc_attr__('Print Invoice', 'wc4bd')
        );
        // Sticker Icon
        printf(
            '<a href="%s" target="_blank" class="button wc-action-button" title="%s" style="padding: 5px 6px; height: auto; line-height: 1;"><span class="dashicons dashicons-tag" style="font-size: 18px; vertical-align: middle;"></span></a>',
            esc_url($sticker_url),
            esc_attr__('Print Sticker', 'wc4bd')
        );
        echo '</div>';
    }

    // --- Feature 2: Single Order Page Button ---
    public function add_invoice_meta_box()
    {
        add_meta_box('wc4bd_invoice_meta_box', esc_html__('WC4BD Actions', 'wc4bd'), [$this, 'render_invoice_meta_box_content'], 'shop_order', 'side', 'default');
    }

    public function render_invoice_meta_box_content($post)
    {
        $nonce = wp_create_nonce('wc4bd_print_invoices');
        $invoice_url = add_query_arg(['print_wc4bd_invoices' => 'true', 'order_ids' => $post->ID, '_wpnonce' => $nonce], home_url('/'));
        $sticker_url = add_query_arg(['print_wc4bd_stickers' => 'true', 'order_ids' => $post->ID, '_wpnonce' => $nonce], home_url('/'));

        echo '<p style="display: flex; justify-content: space-between; gap: 10px;">';
        printf('<a href="%s" class="button button-primary" target="_blank" style="flex: 1; text-align:center;">%s</a>', esc_url($invoice_url), esc_html__('Print Invoice', 'wc4bd'));
        printf('<a href="%s" class="button" target="_blank" style="flex: 1; text-align:center;">%s</a>', esc_url($sticker_url), esc_html__('Print Sticker', 'wc4bd'));
        echo '</p>';
    }

    // --- Feature 3: Custom Bulk Print Button ---
    public function add_custom_bulk_print_button_js()
    {
        $screen = get_current_screen();
        if ($screen && in_array($screen->id, ['edit-shop_order', 'woocommerce_page_wc-orders'], true)) {
            $nonce = wp_create_nonce('wc4bd_print_invoices');
            ?>
            <script type="text/javascript">
                jQuery(document).ready(function ($) {
                    var $bulkActions = $('.bulkactions');
                    if ($bulkActions.length === 0) return;

                    var invoiceBtnHtml = '<a href="#" id="wc4bd-bulk-print-invoices" class="button" style="margin-left: 5px; display: none;"><?php esc_html_e('Bulk Print Invoices', 'wc4bd'); ?></a>';
                    var stickerBtnHtml = '<a href="#" id="wc4bd-bulk-print-stickers" class="button" style="margin-left: 5px; display: none;"><?php esc_html_e('Bulk Print Stickers', 'wc4bd'); ?></a>';

                    $bulkActions.append(invoiceBtnHtml).append(stickerBtnHtml);

                    var $invoiceBtn = $('#wc4bd-bulk-print-invoices');
                    var $stickerBtn = $('#wc4bd-bulk-print-stickers');

                    function toggleButtonsVisibility() {
                        var checkedCount = $('input[name="post[]"]:checked, input[name="id[]"]:checked').length;
                        $invoiceBtn.toggle(checkedCount > 0);
                        $stickerBtn.toggle(checkedCount > 0);
                    }

                    toggleButtonsVisibility();
                    $(document).on('change', 'th.check-column input[type="checkbox"], td.check-column input[type="checkbox"]', toggleButtonsVisibility);

                    function handleBulkPrint(e, type) {
                        e.preventDefault();
                        var order_ids = [];
                        $('input[name="post[]"]:checked, input[name="id[]"]:checked').each(function () {
                            order_ids.push($(this).val());
                        });
                        if (order_ids.length > 0) {
                            var url = "<?php echo home_url('/'); ?>?print_wc4bd_" + type + "=true&_wpnonce=<?php echo $nonce; ?>&order_ids=" + order_ids.join(',');
                            window.open(url, '_blank');
                        }
                    }

                    $(document).on('click', '#wc4bd-bulk-print-invoices', function (e) { handleBulkPrint(e, 'invoices'); });
                    $(document).on('click', '#wc4bd-bulk-print-stickers', function (e) { handleBulkPrint(e, 'stickers'); });
                });
            </script>
            <?php
        }
    }

    // --- Settings Page Code ---
    public function add_settings_page()
    {
        // **CHANGED**: The menu title and page title are now "WC4BD Settings"
        add_submenu_page(
            'woocommerce',
            esc_html__('WC4BD Settings', 'wc4bd'),
            esc_html__('WC4BD Settings', 'wc4bd'),
            'manage_woocommerce',
            'wc4bd-settings',
            [$this, 'settings_page_html']
        );
    }
    public function settings_page_html()
    {
        ?>
        <div class="wrap">
            <!-- **CHANGED**: The h1 tag now shows "WC4BD Settings" -->
            <h1><?php esc_html_e('WC4BD Settings', 'wc4bd'); ?></h1>
            <form method="post" action="options.php">
                <?php
                settings_fields('wc4bd_settings_group');
                do_settings_sections('wc4bd-settings');
                submit_button();
                ?>
            </form>
        </div>
    <?php
    }
    public function register_settings()
    {
        register_setting('wc4bd_settings_group', 'wc4bd_business_logo', [
            'sanitize_callback' => 'esc_url_raw'
        ]);
        register_setting('wc4bd_settings_group', 'wc4bd_business_name', [
            'sanitize_callback' => 'sanitize_text_field'
        ]);
        register_setting('wc4bd_settings_group', 'wc4bd_business_address', [
            'sanitize_callback' => 'sanitize_textarea_field'
        ]);
        register_setting('wc4bd_settings_group', 'wc4bd_business_phone', [
            'sanitize_callback' => 'sanitize_text_field'
        ]);
        register_setting('wc4bd_settings_group', 'wc4bd_terms', [
            'sanitize_callback' => 'sanitize_textarea_field'
        ]);

        add_settings_section('wc4bd_general_section', 'Business Information', null, 'wc4bd-settings');
        add_settings_field('wc4bd_business_logo', 'Business Logo', [$this, 'logo_field_html'], 'wc4bd-settings', 'wc4bd_general_section');
        add_settings_field('wc4bd_business_name', 'Business Name', [$this, 'text_field_html'], 'wc4bd-settings', 'wc4bd_general_section', ['id' => 'wc4bd_business_name']);
        add_settings_field('wc4bd_business_address', 'Business Address', [$this, 'textarea_field_html'], 'wc4bd-settings', 'wc4bd_general_section', ['id' => 'wc4bd_business_address']);
        add_settings_field('wc4bd_business_phone', 'Business Phone', [$this, 'text_field_html'], 'wc4bd-settings', 'wc4bd_general_section', ['id' => 'wc4bd_business_phone']);
        add_settings_field('wc4bd_terms', 'Terms & Conditions', [$this, 'textarea_field_html'], 'wc4bd-settings', 'wc4bd_general_section', ['id' => 'wc4bd_terms']);
    }
    public function enqueue_settings_scripts($hook)
    {
        if ('woocommerce_page_wc4bd-settings' === $hook)
            wp_enqueue_media();
    }
    public function logo_field_html()
    {
        $logo_url = get_option('wc4bd_business_logo');
        echo '<input type="text" name="wc4bd_business_logo" id="wc4bd_business_logo" value="' . esc_attr($logo_url) . '" class="regular-text"><input type="button" id="upload-btn" class="button-secondary" value="Upload Image"><script>jQuery(document).ready(function($){$("#upload-btn").click(function(e){e.preventDefault();var i=wp.media({title:"Upload Logo",multiple:!1}).open().on("select",function(){$("#wc4bd_business_logo").val(i.state().get("selection").first().toJSON().url)})})});</script>';
    }
    public function text_field_html($args)
    {
        printf('<input type="text" id="%s" name="%s" value="%s" class="regular-text" />', esc_attr($args['id']), esc_attr($args['id']), esc_attr(get_option($args['id'])));
    }
    public function textarea_field_html($args)
    {
        printf('<textarea id="%s" name="%s" rows="5" class="large-text">%s</textarea>', esc_attr($args['id']), esc_attr($args['id']), esc_textarea(get_option($args['id'])));
    }
}