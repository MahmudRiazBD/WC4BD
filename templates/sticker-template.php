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
    <style>
        @page {
            size: 75mm 100mm;
            margin: 3mm;
        }

        body,
        html {
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
            font-size: 8pt;
            line-height: 1.3;
            color: #000000;
            margin: 0;
            padding: 0;
            background: #fff;
        }

        .sticker-wrapper {
            width: 69mm;
            height: 94mm;
            border: 1.5px solid #000;
            border-radius: 4px;
            padding: 10px;
            box-sizing: border-box;
            display: flex;
            flex-direction: column;
            page-break-inside: avoid;
            page-break-before: always;
        }

        .sticker-wrapper:first-of-type {
            page-break-before: auto;
        }

        .sticker-header,
        .customer-info,
        .sticker-products,
        .totals-section {
            width: 100%;
        }

        .sticker-header,
        .customer-info,
        .sticker-products {
            border-bottom: 1.5px solid #000;
        }

        .flex-container {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
        }

        .sticker-header {
            padding-bottom: 5px;
        }

        .sticker-header .left-col h3 {
            margin: 0;
            font-size: 11pt;
        }

        .sticker-header .left-col p {
            margin: 0;
            font-size: 8pt;
        }

        .sticker-header .right-col {
            text-align: right;
            font-size: 9pt;
        }

        .sticker-header .right-col p {
            margin: 0;
        }

        .customer-info {
            padding: 8px 0;
        }

        .customer-info-item {
            display: flex;
            align-items: center;
            margin-bottom: 4px;
        }

        .customer-info-item:last-child {
            margin-bottom: 0;
        }

        .customer-info-item .icon {
            width: 16px;
            height: 16px;
            margin-right: 8px;
            flex-shrink: 0;
            font-size: 12pt;
            display: flex;
            align-items: center;
            justify-content: center;
            line-height: 1;
            color: #000;
            filter: grayscale(100%);
        }

        .customer-info-item strong {
            font-size: 9pt;
        }

        .sticker-products {
            padding: 8px 0;
            flex-grow: 1;
            overflow: hidden;
        }

        .products-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 7.5pt;
        }

        .products-table th,
        .products-table td {
            text-align: left;
            padding: 2px 0;
            vertical-align: top;
        }

        .products-table th {
            font-weight: bold;
            border-bottom: 1px solid #ccc;
        }

        .products-table .col-sku {
            width: 25%;
        }

        .products-table .col-variant {
            width: 45%;
        }

        .products-table .col-qty {
            width: 10%;
            text-align: center;
        }

        .products-table .col-price {
            width: 20%;
            text-align: right;
        }

        .summary-row td {
            font-style: italic;
            color: #555;
            border-top: 1px dashed #ccc;
            padding-top: 3px;
        }

        .totals-section {
            margin-top: auto;
            padding-top: 5px;
            font-size: 9pt;
        }

        .totals-section .total-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 2px;
        }

        .totals-section .grand-total {
            font-weight: bold;
            font-size: 11pt;
        }

        @media print {
            body {
                background: none;
            }
        }
    </style>
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
</body>

</html>