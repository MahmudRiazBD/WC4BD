<?php
if (!defined('ABSPATH'))
    exit;

global $wc4bd_order_ids;
if (!$wc4bd_order_ids) {
    wp_die('No orders specified for invoice generation.');
}

// Get business info from settings
$business_logo = get_option('wc4bd_business_logo');
$business_name = get_option('wc4bd_business_name', get_bloginfo('name'));
$business_address = get_option('wc4bd_business_address');
$business_phone = get_option('wc4bd_business_phone');
$terms = get_option('wc4bd_terms');
?>
<!DOCTYPE html>
<html <?php language_attributes(); ?>>

<head>
    <meta charset="<?php bloginfo('charset'); ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?php esc_html_e('Invoices', 'wc4bd'); ?></title>
    <?php wp_head(); ?>
</head>

<body class="wc4bd-invoice-body">

    <div class="wc4bd-print-button-container">
        <button class="wc4bd-print-button"
            onclick="window.print();"><?php esc_html_e('Print Invoices', 'wc4bd'); ?></button>
    </div>

    <?php foreach ($wc4bd_order_ids as $order_id):
        $order = wc_get_order($order_id);
        if (!$order)
            continue; ?>

        <div class="wc4bd-invoice-container">
            <div class="wc4bd-invoice-wrapper">
                <header class="wc4bd-invoice-header">
                    <div class="wc4bd-header-left">
                        <?php if ($business_logo): ?>
                            <img src="<?php echo esc_url($business_logo); ?>" alt="<?php echo esc_attr($business_name); ?>"
                                class="wc4bd-logo">
                        <?php else: ?>
                            <h2><?php echo esc_html($business_name); ?></h2>
                        <?php endif; ?>
                    </div>
                    <div class="wc4bd-header-right">
                        <h1><?php esc_html_e('INVOICE', 'wc4bd'); ?></h1>
                        <p><strong><?php esc_html_e('Invoice #:', 'wc4bd'); ?></strong>
                            <?php echo esc_html($order->get_order_number()); ?></p>
                        <p><strong><?php esc_html_e('Date:', 'wc4bd'); ?></strong>
                            <?php echo esc_html(wc_format_datetime($order->get_date_created(), 'F j, Y')); ?></p>
                    </div>
                </header>

                <section class="wc4bd-invoice-details">
                    <div class="wc4bd-details-box">
                        <h3><?php esc_html_e('From', 'wc4bd'); ?></h3>
                        <address>
                            <strong><?php echo esc_html($business_name); ?></strong><br>
                            <?php echo nl2br(esc_html($business_address)); ?>
                            <?php if (!empty($business_phone)): ?>
                                <br><?php echo '<strong>' . esc_html__('Phone:', 'wc4bd') . '</strong> ' . esc_html($business_phone); ?>
                            <?php endif; ?>
                        </address>
                    </div>
                    <div class="wc4bd-details-box wc4bd-company-details">
                        <h3><?php esc_html_e('Billed To', 'wc4bd'); ?></h3>
                        <address>
                            <strong><?php echo esc_html($order->get_formatted_billing_full_name()); ?></strong><br>
                            <?php
                            // **FIXED LOGIC**: This is a much safer way to build the address without causing a fatal error.
                            $address_parts = [
                                $order->get_billing_address_1(),
                                $order->get_billing_address_2(),
                                $order->get_billing_city(),
                                $order->get_billing_state(),
                                $order->get_billing_postcode(),
                                $order->get_billing_country() ? WC()->countries->get_countries()[$order->get_billing_country()] : ''
                            ];
                            echo implode('<br>', array_filter($address_parts));
                            ?>
                            <?php if ($order->get_billing_phone()): ?>
                                <br><?php echo '<strong>' . esc_html__('Phone:', 'wc4bd') . '</strong> ' . esc_html($order->get_billing_phone()); ?>
                            <?php endif; ?>
                            <?php if ($order->get_billing_email()): ?>
                                <br><?php echo '<strong>' . esc_html__('Email:', 'wc4bd') . '</strong> ' . esc_html($order->get_billing_email()); ?>
                            <?php endif; ?>
                        </address>
                    </div>
                </section>

                <table class="wc4bd-items-table">
                    <thead>
                        <tr>
                            <th class="wc4bd-item-thumb"></th>
                            <th><?php esc_html_e('Product', 'wc4bd'); ?></th>
                            <th class="wc4bd-align-right"><?php esc_html_e('SKU', 'wc4bd'); ?></th>
                            <th class="wc4bd-align-right"><?php esc_html_e('Qty', 'wc4bd'); ?></th>
                            <th class="wc4bd-align-right"><?php esc_html_e('Total', 'wc4bd'); ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($order->get_items() as $item):
                            $product = $item->get_product(); ?>
                            <tr>
                                <td class="wc4bd-item-thumb"><?php echo $product ? $product->get_image([60, 60]) : ''; ?>
                                </td>
                                <td class="wc4bd-item-details">
                                    <span class="wc4bd-item-name"><?php echo esc_html($item->get_name()); ?></span>
                                    <div class="wc4bd-item-meta"><?php wc_display_item_meta($item, ['echo' => true]); ?></div>
                                </td>
                                <td class="wc4bd-align-right"><?php echo $product ? esc_html($product->get_sku()) : 'N/A'; ?>
                                </td>
                                <td class="wc4bd-align-right"><?php echo esc_html($item->get_quantity()); ?></td>
                                <td class="wc4bd-align-right">
                                    <?php echo wp_kses_post($order->get_formatted_line_subtotal($item)); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>

                <section class="wc4bd-invoice-totals">
                    <table>
                        <?php foreach ($order->get_order_item_totals() as $key => $total): ?>
                            <tr class="<?php echo esc_attr(str_replace('_', '-', $key)); ?>">
                                <td><?php echo esc_html($total['label']); ?></td>
                                <td class="wc4bd-align-right"><?php echo wp_kses_post($total['value']); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </table>
                </section>

                <?php if ($terms): ?>
                    <footer class="wc4bd-invoice-footer">
                        <h4><?php esc_html_e('Terms & Conditions', 'wc4bd'); ?></h4>
                        <p><?php echo nl2br(esc_textarea($terms)); ?></p>
                    </footer>
                <?php endif; ?>

            </div>
        </div>
    <?php endforeach; ?>
    <?php wp_footer(); ?>
</body>

</html>