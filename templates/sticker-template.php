<?php
if (!defined('ABSPATH'))
    exit;

global $wc4bd_order_ids;
if (!$wc4bd_order_ids) {
    wp_die('No orders selected for printing.');
}

// Get business info from settings
$business_name = get_option('wc4bd_business_name', get_bloginfo('name'));
$business_phone = get_option('wc4bd_business_phone');
?>
<!DOCTYPE html>
<html <?php language_attributes(); ?>>

<head>
    <meta charset="<?php bloginfo('charset'); ?>">
    <title><?php esc_html_e('Shipping Stickers', 'wc4bd'); ?></title>
    <?php wp_head(); ?>
</head>

<body>

    <?php foreach ($wc4bd_order_ids as $order_id):
        $order = wc_get_order($order_id);
        if (!$order)
            continue;
        $items = $order->get_items();
        $item_count = count($items);
        ?>
        <div class="sticker-wrapper">

            <div class="sticker-header flex-container">
                <div class="left-col">
                    <h3><?php echo esc_html($business_name); ?></h3>
                    <p><?php echo esc_html($business_phone); ?></p>
                </div>
                <div class="right-col">
                    <!-- **NEW**: Added Order ID here -->
                    <p><strong><?php esc_html_e('Order ID:', 'wc4bd'); ?></strong>
                        #<?php echo esc_html($order->get_order_number()); ?></p>
                    <p><?php echo esc_html(wc_format_datetime($order->get_date_created(), 'd/m/Y')); ?></p>
                </div>
            </div>

            <div class="customer-info">
                <div class="customer-info-item">
                    <div class="icon">👤</div>
                    <strong><?php echo esc_html($order->get_formatted_billing_full_name()); ?></strong>
                </div>
                <div class="customer-info-item">
                    <div class="icon">📞</div>
                    <span><?php echo esc_html($order->get_billing_phone()); ?></span>
                </div>
                <div class="customer-info-item">
                    <div class="icon">📍</div>
                    <span>
                        <?php
                        $address_parts = [$order->get_billing_address_1(), $order->get_billing_address_2(), $order->get_billing_city(), $order->get_billing_postcode()];
                        echo esc_html(implode(', ', array_filter($address_parts)));
                        ?>
                    </span>
                </div>
            </div>

            <div class="sticker-products">
                <table class="products-table">
                    <thead>
                        <tr>
                            <th class="col-sku"><?php esc_html_e('SKU', 'wc4bd'); ?></th>
                            <th class="col-variant"><?php esc_html_e('Variant', 'wc4bd'); ?></th>
                            <th class="col-qty"><?php esc_html_e('Qty', 'wc4bd'); ?></th>
                            <th class="col-price"><?php esc_html_e('Price', 'wc4bd'); ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $items_to_show = ($item_count > 5) ? array_slice($items, 0, 5, true) : $items;
                        foreach ($items_to_show as $item_id => $item):
                            $product = $item->get_product();
                            $item_price = (float) $item->get_subtotal() / (int) $item->get_quantity();

                            $meta_data = $item->get_formatted_meta_data('_', true);
                            $variant_display_parts = [];
                            foreach ($meta_data as $meta_item) {
                                $variant_display_parts[] = esc_html($meta_item->display_key) . ': ' . wp_strip_all_tags($meta_item->display_value);
                            }
                            $variant_string = implode(', ', $variant_display_parts);
                            ?>
                            <tr>
                                <td><?php echo esc_html($product ? $product->get_sku() : 'N/A'); ?></td>
                                <td><?php echo esc_html($variant_string); ?></td>
                                <td class="col-qty"><?php echo esc_html($item->get_quantity()); ?></td>
                                <td class="col-price"><?php echo wp_kses_post(wc_price($item_price)); ?></td>
                            </tr>
                        <?php endforeach; ?>

                        <?php if ($item_count > 5): ?>
                            <tr class="summary-row">
                                <td colspan="4">...and <?php echo esc_html($item_count - 5); ?> more item(s).</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

            <div class="totals-section">
                <div class="total-row">
                    <span><?php esc_html_e('Delivery Charge:', 'wc4bd'); ?></span>
                    <span><?php echo wp_kses_post(wc_price($order->get_shipping_total())); ?></span>
                </div>
                <div class="total-row grand-total">
                    <span><?php esc_html_e('Total Bill:', 'wc4bd'); ?></span>
                    <span><?php echo wp_kses_post($order->get_formatted_order_total()); ?></span>
                </div>
            </div>

        </div>
    <?php endforeach; ?>
    <?php wp_footer(); ?>
</body>

</html>