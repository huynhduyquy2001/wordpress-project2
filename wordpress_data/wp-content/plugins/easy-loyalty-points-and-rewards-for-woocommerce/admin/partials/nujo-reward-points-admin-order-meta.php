<?php

/**
 * Provide a admin area view for the plugin
 *
 * This file is used to markup the admin-facing aspects of the plugin.
 *
 * @link       https://nujoplugins.com
 * @since      1.0.0
 *
 * @package    Nujo_Reward_Points
 * @subpackage Nujo_Reward_Points/admin/partials
 */

// If this file is called directly, abort.
if (!defined('WPINC')) {
    die;
}
?>

<?php if (!empty($version)) { ?>

    <?php if ($has_account) { ?>

        <table class="wc-order-total">
            <tbody>
                <tr>
                    <td class="label"><?php esc_html_e('Points Earned:', 'easy-loyalty-points-and-rewards-for-woocommerce'); ?></td>
                    <td width="1%"></td>
                    <td class="total"><strong><?php echo esc_html(nrp_format_points($points_total)); ?></strong></td>
                </tr>

                <?php if ('cancelled' == $status) { ?>
                <td class="label"><?php esc_html_e('Points Status:', 'easy-loyalty-points-and-rewards-for-woocommerce'); ?></td>
                    <td width="1%"></td>
                    <td class="total"><strong><?php esc_html_e('Cancelled', 'easy-loyalty-points-and-rewards-for-woocommerce'); ?></strong></td>
                </tr>
                <?php } elseif ('pending-paid' == $status) { ?>
                <td class="label"><?php esc_html_e('Points Status:', 'easy-loyalty-points-and-rewards-for-woocommerce'); ?></td>
                    <td width="1%"></td>
                    <td class="total"><strong><?php esc_html_e('Pending - will be assigned once payment has been received', 'easy-loyalty-points-and-rewards-for-woocommerce'); ?></strong></td>
                </tr>
                <?php } elseif ('pending-completed' == $status) { ?>
                <td class="label"><?php esc_html_e('Points Status:', 'easy-loyalty-points-and-rewards-for-woocommerce'); ?></td>
                    <td width="1%"></td>
                    <td class="total"><strong><?php esc_html_e('Pending - will be assigned once order has been completed', 'easy-loyalty-points-and-rewards-for-woocommerce'); ?></strong></td>
                </tr>
                <?php } elseif ('awarded' == $status) { ?>
                    <td class="label"><?php esc_html_e('Points Status:', 'easy-loyalty-points-and-rewards-for-woocommerce'); ?></td>
                    <td width="1%"></td>
                    <td class="total"><strong><?php esc_html_e('Assigned to customer', 'easy-loyalty-points-and-rewards-for-woocommerce'); ?></strong></td>
                </tr>
                <?php } ?>
                <?php if ($points_redeemed > 0) { ?>
                <tr>
                    <td class="label"><?php esc_html_e('Points Redeemed:', 'easy-loyalty-points-and-rewards-for-woocommerce'); ?></td>
                    <td width="1%"></td>
                    <td class="total"><strong><?php echo esc_html(nrp_format_points($points_redeemed)); ?></strong></td>
                </tr>
                <?php } ?>
                <tr>
                    <td class="label"><?php esc_html_e('Quick Links:', 'easy-loyalty-points-and-rewards-for-woocommerce'); ?></td>
                    <td width="1%"></td>
                    <td class="total">
                        <a href="<?php echo esc_url($balance_url); ?>"><?php esc_html_e('Manage customer balance', 'easy-loyalty-points-and-rewards-for-woocommerce'); ?> →</a>&nbsp;&nbsp;
                        <a href="<?php echo esc_url($points_log_url); ?>"><?php esc_html_e('View points log for order', 'easy-loyalty-points-and-rewards-for-woocommerce'); ?> →</a>
                    </td>
                </tr>
            </tbody>
        </table>

    <?php } else { ?>

        <p><?php echo esc_html(sprintf(__('No %1$s earned as the customer was not logged in. The potential number of %1$s was %2$s.', 'easy-loyalty-points-and-rewards-for-woocommerce'), nrp_points_label(true), nrp_format_points($points_total))); ?>

        <?php } ?>

    <?php } else { ?>

        <p><?php esc_html_e('Plugin was not active for this order.', 'easy-loyalty-points-and-rewards-for-woocommerce'); ?></p>

    <?php } ?>