<?php

/**
 * Fired when the plugin is uninstalled.
 *
 * @link       https://nujoplugins.com
 * @since      1.0.0
 *
 * @package    Nujo_Reward_Points
 */

// If uninstall not called from WordPress, then exit.
if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	exit;
}

global $wpdb;

if (get_option('nrp_uninstall_delete_settings') == '1') {

	delete_option('nrp_settings_version');
	delete_option('nrp_point_value');
	delete_option('nrp_min_redemption_points');
	delete_option('nrp_points_per_unit');
	delete_option('nrp_redemption_increment');
	delete_option('nrp_min_redemption_order_value');
	delete_option('nrp_min_redemption_order_value_display');
	delete_option('nrp_assign_points_status');
	delete_option('nrp_debug');
	delete_option('nrp_message_single_product');
	delete_option('nrp_message_variable_product');
	delete_option('nrp_message_cart_guest');
	delete_option('nrp_message_cart_reward_min_spend');
	delete_option('nrp_message_cart_reward_min_points');
	delete_option('nrp_message_cart_apply_discount');
	delete_option('nrp_message_cart_apply_discount_button');
	delete_option('nrp_message_cart_complete_purchase');
	delete_option('nrp_tax_mode');
	delete_option('nrp_earning_ratio');
	delete_option('nrp_rounding_mode');
	delete_option('nrp_redemption_ratio');
	delete_option('nrp_points_label');
	delete_option('nrp_coupon_calculation_mode');
	delete_option('nrp_uninstall_delete_settings');
	delete_option('nrp_uninstall_delete_points_data');
	delete_option('nrp_install_date');

	delete_option('nrp_rewrite_rules_flushed');
	delete_option('nrp_activated');

}

if ((get_option('nrp_uninstall_delete_points_data') == '1')
&& (get_option('nrp_pro_settings_version') === false)) { 

	delete_option('nrp_db_version');

	$table_name = $wpdb->prefix . 'nrp_points_log';
	$wpdb->query( "DROP TABLE IF EXISTS $table_name" );

	$table_name = $wpdb->prefix . 'nrp_accounts';
	$wpdb->query( "DROP TABLE IF EXISTS $table_name" );

}