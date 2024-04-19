<?php

// If this file is called directly, abort.
if (!defined('WPINC')) {
    die;
}

$my_points_columns = apply_filters(
    'nrp_my_account_columns',
    array(
        'points-date' => __('Date', 'easy-loyalty-points-and-rewards-for-woocommerce'),
        'points-description'  => __('Event', 'easy-loyalty-points-and-rewards-for-woocommerce'),
        'points-amount'  => sprintf(__('Amount', 'easy-loyalty-points-and-rewards-for-woocommerce'), nrp_points_label()),
        'order-id'  => __('Order', 'easy-loyalty-points-and-rewards-for-woocommerce'),

    )
);

$account_id = Nujo_Reward_Points_Account::get_account_id_from_customer_id(get_current_user_id());

$log_table_name = $wpdb->prefix . 'nrp_points_log';

$records = $wpdb->get_results(
    $wpdb->prepare(
        "SELECT * FROM $log_table_name WHERE account_id = %d ORDER BY points_log_id DESC LIMIT %d",
        array(
            $account_id,
            apply_filters('nrp_my_account_event_limit', 10),
        )
    ),
    ARRAY_A
);
?>

<?php do_action('nrp_before_my_account'); ?>

<div id="nrp_my_account_content">

    <h3><?php echo wp_kses(apply_filters('nrp_my_account_balance_title', sprintf(__('%s balance', 'easy-loyalty-points-and-rewards-for-woocommerce'), nrp_points_label())), nrp_allowed_html()); ?></h3>

    <?php
    echo wp_kses(
        apply_filters(
            'nrp_my_account_balance_text',
            '<p class="nrp_my_account_balance_text">' . sprintf(
                __('You have %1$s %2$s worth %3$s.', 'easy-loyalty-points-and-rewards-for-woocommerce'),
                nrp_format_points(nrp_get_current_user_points()),
                nrp_points_label(true),
                wc_price(nrp_get_points_value(nrp_get_current_user_points()))
            ) . '</p>',
            nrp_format_points(nrp_get_current_user_points()),
            wc_price(nrp_get_points_value(nrp_get_current_user_points()))
        ),
        nrp_allowed_html()
    );
    ?>

    <?php do_action('nrp_after_my_account_balance'); ?>

    <h3><?php echo wp_kses(apply_filters('nrp_my_account_balance_title', __('Recent events', 'easy-loyalty-points-and-rewards-for-woocommerce')), nrp_allowed_html()); ?></h3>

    <?php if ($records) : ?>

        <table class="shop_table shop_table_responsive my_account_orders nrp_my_account_points_log">

            <thead>
                <tr>
                    <?php foreach ($my_points_columns as $column_id => $column_name) : ?>
                        <th class="<?php echo esc_attr($column_id); ?>"><span class="nobr"><?php echo esc_html($column_name); ?></span></th>
                    <?php endforeach; ?>
                </tr>
            </thead>

            <tbody>
                <?php
                foreach ($records as $record) :
                ?>
                    <tr class="order">
                        <?php foreach ($my_points_columns as $column_id => $column_name) : ?>
                            <td class="<?php echo esc_attr($column_id); ?>" data-title="<?php echo esc_attr($column_name); ?>">

                                <?php if ('points-date' === $column_id) : ?>
                                    <?php echo esc_html(date_i18n('F j, Y', strtotime($record['date_created_local']))); ?>

                                <?php elseif ('points-description' === $column_id) : ?>
                                    <?php echo wp_kses(nrp_event_description($record, 'customer'), nrp_allowed_html()); ?>

                                <?php elseif ('order-id' === $column_id) : ?>

                                    <?php
                                    if (!empty($record['order_id'])) {
                                        echo esc_html(_x('#', 'hash before order number', 'woocommerce') . $record['order_id']);
                                    } else {
                                        echo '—';
                                    }
                                    ?>

                                <?php elseif ('points-amount' === $column_id) : ?>
                                    <?php echo esc_html(nrp_format_points($record['amount'], $record['process'])); ?>
                                <?php endif; ?>

                            </td>
                        <?php endforeach; ?>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php else : ?>
        <p><?php echo wp_kses(sprintf(__('You have not earned any %s yet.', 'easy-loyalty-points-and-rewards-for-woocommerce'), nrp_points_label(true)), nrp_allowed_html()); ?></p>
    <?php endif; ?>

</div>

<?php do_action('nrp_after_my_account'); ?>