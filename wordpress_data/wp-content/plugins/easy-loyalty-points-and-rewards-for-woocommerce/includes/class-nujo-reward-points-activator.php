<?php

/**
 * Fired during plugin activation
 *
 * @link       https://nujoplugins.com
 * @since      1.0.0
 *
 * @package    Nujo_Reward_Points
 * @subpackage Nujo_Reward_Points/includes
 */

/**
 * Fired during plugin activation.
 *
 * This class defines all code necessary to run during the plugin's activation.
 *
 * @since      1.0.0
 * @package    Nujo_Reward_Points
 * @subpackage Nujo_Reward_Points/includes
 * @author     Nujo Plugins <test@test.com>
 */
class Nujo_Reward_Points_Activator
{

	/**
	 * Short Description. (use period)
	 *
	 * Long Description.
	 *
	 * @since    1.0.0
	 */
	public static function activate()
	{
		if (get_option('nrp_settings_version', 0) == 0) {
			self::set_default_settings();
		}

		if (get_option('nrp_db_version', 0) == 0) {
			self::create_db_tables();
		}

		// create customer accounts
		self::create_account_records();

		// initial settings
		update_option('nrp_rewrite_rules_flushed', 0);
		update_option('nrp_activated', 1);
	}

	private static function create_db_tables()
	{
		global $wpdb;

		if (get_option('nrp_db_version', 0) == 0) {

			require_once(ABSPATH . 'wp-admin/includes/upgrade.php');

			$charset_collate = $wpdb->get_charset_collate();
			$table_name = $wpdb->prefix . 'nrp_points_log';

			$sql = "CREATE TABLE IF NOT EXISTS $table_name (
				points_log_id BIGINT unsigned NOT NULL AUTO_INCREMENT,
				event_type VARCHAR(255) NOT NULL,
				account_id BIGINT unsigned NOT NULL,
				process TINYINT unsigned NOT NULL,
				amount INT unsigned NOT NULL,
				amount_available INT unsigned,
				account_balance INT unsigned,
				order_id BIGINT unsigned,
				data TEXT,
				deduct_map TEXT,
				expired TINYINT,
				reminder_sent TINYINT,
				date_created_gmt DATETIME NOT NULL,
				date_created_local DATETIME NOT NULL,
				date_expires_gmt DATETIME,
				date_expires_local DATETIME,
				KEY `account_id_index` (`account_id`) USING BTREE,
				PRIMARY KEY (points_log_id)
			) $charset_collate;";

			dbDelta($sql);

			$table_name = $wpdb->prefix . 'nrp_accounts';

			$sql = "CREATE TABLE IF NOT EXISTS $table_name (
				account_id BIGINT unsigned NOT NULL AUTO_INCREMENT,
				customer_id BIGINT unsigned NOT NULL,
				email VARCHAR(255),
				points_balance INT unsigned NOT NULL DEFAULT '0',
				points_pending INT unsigned NOT NULL DEFAULT '0',
				total_points_earned INT unsigned NOT NULL DEFAULT '0',
				total_points_redeemed INT unsigned NOT NULL DEFAULT '0',
				total_points_expired INT unsigned NOT NULL DEFAULT '0',
				date_created_gmt DATETIME NOT NULL,
				date_created_local DATETIME NOT NULL,
				date_last_activity_gmt DATETIME,
				date_last_activity_local DATETIME,
				date_last_reminder_gmt DATETIME,
				date_last_reminder_local DATETIME,
				KEY `customer_id_index` (`customer_id`) USING BTREE,
				PRIMARY KEY (account_id)
			) $charset_collate;";

			dbDelta($sql);

			update_option('nrp_db_version', 1);

		}

	}

	private static function create_account_records()
	{
		$users = get_users();

		foreach ($users as $user) {
			Nujo_Reward_Points_Account::create_account($user);
		}
	}

	private static function set_default_settings()
	{
		update_option('nrp_point_value', 0);
		update_option('nrp_min_redemption_points', 0);
		update_option('nrp_points_per_unit', 0);
		update_option('nrp_redemption_increment', 1);
		update_option('nrp_min_redemption_order_value', 0);
		update_option('nrp_assign_points_status', 'paid');
		update_option('nrp_debug', 0);
		update_option('nrp_message_single_product', 		Nujo_Reward_Points_Message::get_message('single_product'));
		update_option('nrp_message_variable_product', 		Nujo_Reward_Points_Message::get_message('variable_product'));
		update_option('nrp_message_cart_complete_purchase', Nujo_Reward_Points_Message::get_message('cart_complete_purchase'));
		update_option('nrp_message_cart_guest', 			Nujo_Reward_Points_Message::get_message('cart_guest'));
		update_option('nrp_message_cart_reward_min_spend',	Nujo_Reward_Points_Message::get_message('cart_reward_min_spend'));
		update_option('nrp_message_cart_reward_min_points',	Nujo_Reward_Points_Message::get_message('cart_reward_min_points'));
		update_option('nrp_message_cart_apply_discount', 	Nujo_Reward_Points_Message::get_message('cart_apply_discount'));
		update_option('nrp_message_cart_apply_discount_button', __('Click here to redeem', 'easy-loyalty-points-and-rewards-for-woocommerce'));
		update_option('nrp_tax_mode', 'incl');
		update_option('nrp_earning_ratio', array());
		update_option('nrp_rounding_mode', 'nearest');
		update_option('nrp_redemption_ratio', array());
		update_option('nrp_points_label', __('Points', 'easy-loyalty-points-and-rewards-for-woocommerce'));
		update_option('nrp_coupon_calculation_mode', 'total');
		update_option('nrp_uninstall_delete_settings', '');
		update_option('nrp_uninstall_delete_points_data', '');
		update_option('nrp_install_date', time());
		update_option('nrp_settings_version', NUJO_REWARD_POINTS_SETTINGS_VERSION);
	}

	public static function upgrade_settings()
	{
		if (get_option('nrp_settings_version') === '1') {
			self::upgrade_settings_1_to_2();
			self::upgrade_settings_2_to_3();
		}
		if (get_option('nrp_settings_version') === '2') {
			self::upgrade_settings_2_to_3();
		}
		update_option('nrp_settings_version', NUJO_REWARD_POINTS_SETTINGS_VERSION);
	}

	private static function upgrade_settings_1_to_2()
	{
		add_option('nrp_message_cart_apply_discount_button', __('Click here to redeem', 'easy-loyalty-points-and-rewards-for-woocommerce'));
	}

	private static function upgrade_settings_2_to_3()
	{
		add_option('nrp_message_cart_reward_min_points',	Nujo_Reward_Points_Message::get_message('cart_reward_min_points'));
	}
}
