<?php

/**
 * Fired during plugin deactivation
 *
 * @link       https://nujoplugins.com
 * @since      1.0.0
 *
 * @package    Nujo_Reward_Points
 * @subpackage Nujo_Reward_Points/includes
 */

/**
 * Fired during plugin deactivation.
 *
 * This class defines all code necessary to run during the plugin's deactivation.
 *
 * @since      1.0.0
 * @package    Nujo_Reward_Points
 * @subpackage Nujo_Reward_Points/includes
 * @author     Nujo Plugins <test@test.com>
 */
class Nujo_Reward_Points_Deactivator {

	/**
	 * Short Description. (use period)
	 *
	 * Long Description.
	 *
	 * @since    1.0.0
	 */
	public static function deactivate() {
		flush_rewrite_rules();
		update_option('nrp_activated', 0);
	}

}
