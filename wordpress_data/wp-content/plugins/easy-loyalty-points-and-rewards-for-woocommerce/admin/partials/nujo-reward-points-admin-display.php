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

$tab = $active_tab;
$section = $active_section;

if ($tab === null) {
    $tab = 'balances';
}
?>

<div id="nrp-header">
    <a href="https://nujoplugins.com/?utm_source=nrp_free&utm_medium=wordpress_plugin&utm_content=logo&utm_campaign=nrp_pro" target="_blank"><img src="<?php echo str_replace('partials/', '', plugin_dir_url(__FILE__)) . 'img/nujoplugins_logo_dark_plugin.png'; ?>"  height="15"></a>
    <h1>Easy Loyalty Points and Rewards for WooCommerce</h1>
</div>

<div id="nrp-support">
    <?php echo wp_kses(sprintf(__('<strong>Any issues?</strong> Please let us know on our <a href="%s" target="_blank">support forum</a>!', 'easy-loyalty-points-and-rewards-for-woocommerce'), "https://wordpress.org/support/plugin/easy-loyalty-points-and-rewards-for-woocommerce/"), nrp_allowed_html()); ?>
</div>

<div class="wrap">

    <h2 class="nrp-top-h2"></h2>
    <!-- <div id="icon-themes" class="icon32"></div> -->
    
    <?php settings_errors(); ?>
    <nav class="nav-tab-wrapper">

        <!-- <a href="?page=nujo-reward-points-settings&tab=welcome" class="nav-tab <?php if ($tab === 'welcome') : ?>nav-tab-active<?php endif; ?>"><?php esc_html_e('Welcome', 'easy-loyalty-points-and-rewards-for-woocommerce'); ?></a> -->
        <a href="?page=nujo-reward-points-settings&tab=balances" class="nav-tab <?php if ($tab === 'balances') : ?>nav-tab-active<?php endif; ?>"><?php esc_html_e('Point Balances', 'easy-loyalty-points-and-rewards-for-woocommerce'); ?></a>
        <a href="?page=nujo-reward-points-settings&tab=log" class="nav-tab <?php if ($tab === 'log') : ?>nav-tab-active<?php endif; ?>"><?php esc_html_e('Points Log', 'easy-loyalty-points-and-rewards-for-woocommerce'); ?></a>
        <a href="?page=nujo-reward-points-settings&tab=settings" class="nav-tab <?php if ($tab === 'settings') : ?>nav-tab-active<?php endif; ?>"><?php esc_html_e('Settings', 'easy-loyalty-points-and-rewards-for-woocommerce'); ?></a>
        <a href="https://nujoplugins.com/woocommerce-points-and-rewards/?utm_source=nrp_free&utm_medium=wordpress_plugin&utm_content=upgrade_tab&utm_campaign=nrp_pro" target="_blank" class="nav-tab <?php if ($tab === 'upgrade') : ?>nav-tab-active<?php endif; ?>" style="background-color: #F6D70E; border: 1px solid #f8bb02; border-bottom: 0; color: #000; font-weight: 600;"><?php esc_html_e('Upgrade to Pro', 'easy-loyalty-points-and-rewards-for-woocommerce'); ?></a>
    </nav>

    <?php if ($tab === 'welcome') {  ?>

        <h2>Welcome</h2>
        <div class="tab-content">
            <p>Thanks for using our plugin!</p>
        </div>

    <?php } ?>

    <?php if ($tab === 'settings') {  ?>

        <ul class="subsubsub">
            <li><a href="?page=nujo-reward-points-settings&tab=settings" class="<?php if ($section === null) : ?>current<?php endif; ?>"><?php esc_html_e('General Settings', 'easy-loyalty-points-and-rewards-for-woocommerce'); ?></a> | </li>
            <li><a href="?page=nujo-reward-points-settings&tab=settings&section=advanced" class="<?php if ($section === 'advanced') : ?>current<?php endif; ?>"><?php esc_html_e('Advanced', 'easy-loyalty-points-and-rewards-for-woocommerce'); ?></a></li>
        </ul><br class="clear" />

        <?php if ($section === null) {  ?>
            <!-- <h2>General</h2> -->
            <div class="tab-content">

            <div class="nrp-upgrade-box">
                <p>
                    <strong><?php echo wp_kses(sprintf(__('Upgrade to our <a href="%s" target="_blank">Pro version</a> for even more features:', 'easy-loyalty-points-and-rewards-for-woocommerce'), "https://nujoplugins.com/woocommerce-points-and-rewards/?utm_source=nrp_free&utm_medium=wordpress_plugin&utm_content=upgrade_box&utm_campaign=nrp_pro"), nrp_allowed_html()); ?></strong>
                </p>
                <p>
                    <span class="dashicons dashicons-calendar-alt"></span> <?php echo wp_kses(__('<strong>Points multiplier</strong> - schedule events such as <strong>double points promotions</strong>', 'easy-loyalty-points-and-rewards-for-woocommerce'), array('strong' => array())); ?>
                    <br>
                    <span class="dashicons dashicons-clock"></span> <?php echo wp_kses(__('<strong>Points expiration</strong> - expire points after a time period', 'easy-loyalty-points-and-rewards-for-woocommerce'), array('strong' => array())); ?>
                    <br>
                    <span class="dashicons dashicons-tag"></span> <?php echo wp_kses(__('<strong>Product/category point settings</strong> - set bonus points or exclusions at a product/category level', 'easy-loyalty-points-and-rewards-for-woocommerce'), array('strong' => array())); ?>
                    <br>
                    <span class="dashicons dashicons-carrot"></span> <?php echo wp_kses(__('<strong>Action rewards</strong> - earn bonus points for new accounts or first orders', 'easy-loyalty-points-and-rewards-for-woocommerce'), array('strong' => array())); ?>
                    <br>
                    <span class="dashicons dashicons-cart"></span> <?php echo wp_kses(__('<strong>Product bundles support</strong> - earn points for bundle type products', 'easy-loyalty-points-and-rewards-for-woocommerce'), array('strong' => array())); ?>
                    <br>
                    <span class="dashicons dashicons-sos"></span> <?php echo wp_kses(__('<strong>Technical support</strong> - get expert help from our friendly team', 'easy-loyalty-points-and-rewards-for-woocommerce'), array('strong' => array())); ?>
                </p>
                <p class="nrp-upgrade-link">
                    <strong><?php echo wp_kses(sprintf(__('<a href="%s" target="_blank">Upgrade to Pro</a>', 'easy-loyalty-points-and-rewards-for-woocommerce'), "https://nujoplugins.com/woocommerce-points-and-rewards/?utm_source=nrp_free&utm_medium=wordpress_plugin&utm_content=upgrade_box&utm_campaign=nrp_pro"), nrp_allowed_html()); ?></strong>
                </p>
            </div>

                <form method="POST" action="options.php">
                    <?php
                    settings_fields('nrp_general_settings_page');
                    do_settings_sections('nrp_general_settings_page');
                    ?>
                    <?php submit_button(); ?>
                </form>
            </div>

        <?php } ?>

        <?php if ($section === 'advanced') {  ?>
            <!-- <h2>General</h2> -->
            <div class="tab-content">
                <form method="POST" action="options.php">
                    <?php
                    settings_fields('nrp_advanced_settings_page');
                    do_settings_sections('nrp_advanced_settings_page');
                    ?>
                    <?php submit_button(); ?>
                </form>
            </div>

        <?php } ?>

    <?php } ?>

    <?php if ($tab === 'balances') {  ?>

        <?php
        $log_table = new Nujo_Reward_Points_Account_Table();
        $log_table->prepare_items();
        ?>
        <form method="post" class="nrp_list_table">

            <input type="hidden" name="page" value="<?php echo esc_attr($page); ?>">
            <?php $log_table->search_box(__('Search Customers', 'easy-loyalty-points-and-rewards-for-woocommerce'), 'nrp_search_account_table'); ?>
        </form>
        <div class="wrap">
            <h2 class="wp-heading-inline">

                <?php esc_html_e('Point Balances', 'easy-loyalty-points-and-rewards-for-woocommerce'); ?>

                <?php if (!empty($search_term)) { ?>

                    <span class="subtitle">

                    <?php echo wp_kses(sprintf(__('Search results for: "%s"', 'easy-loyalty-points-and-rewards-for-woocommerce'), '<strong>' . esc_html($search_term) . '</strong>'), array('strong' => array())); ?>

                    </span>

                <?php } ?>

            </h2>
        </div>
        <?php
        $log_table->display();
        ?>

    <?php } ?>

    <?php if ($tab === 'log') {  ?>

        <?php
        $log_table = new Nujo_Reward_Points_Log_Table();
        $log_table->prepare_items();
        ?>
        <form method="post" class="nrp_list_table">
            <input type="hidden" name="page" value="<?php echo esc_attr($page); ?>">
            <?php $log_table->search_box(__('Search Log', 'easy-loyalty-points-and-rewards-for-woocommerce'), 'nrp_search_log_table'); ?>
        </form>
        <div class="wrap">
            <h2 class="wp-heading-inline">

                <?php esc_html_e('Points Log', 'easy-loyalty-points-and-rewards-for-woocommerce'); ?>

                <?php if (!empty($search_term)) { ?>

                    <span class="subtitle">

                    <?php echo wp_kses(sprintf(__('Search results for: "%s"', 'easy-loyalty-points-and-rewards-for-woocommerce'), '<strong>' . esc_html($search_term) . '</strong>'), array('strong' => array())); ?>

                    </span>

                <?php } ?>


            </h2>
        </div>

        <?php
        $log_table->display();
        ?>

    <?php } ?>

</div>