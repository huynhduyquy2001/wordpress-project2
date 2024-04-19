<?php

/**
 * Define the internationalization functionality
 *
 * Loads and defines the internationalization files for this plugin
 * so that it is ready for translation.
 *
 * @link       https://nujoplugins.com
 * @since      1.0.0
 *
 * @package    Nujo_Reward_Points
 * @subpackage Nujo_Reward_Points/includes
 */

/**
 * Define the internationalization functionality.
 *
 * Loads and defines the internationalization files for this plugin
 * so that it is ready for translation.
 *
 * @since      1.0.0
 * @package    Nujo_Reward_Points
 * @subpackage Nujo_Reward_Points/includes
 * @author     Nujo Plugins <test@test.com>
 */
class Nujo_Reward_Points_i18n {


	/**
	 * Load the plugin text domain for translation.
	 *
	 * @since    1.0.0
	 */
	public function load_plugin_textdomain() {

		load_plugin_textdomain(
			'easy-loyalty-points-and-rewards-for-woocommerce',
			false,
			dirname( dirname( plugin_basename( __FILE__ ) ) ) . '/languages/'
		);

	}



}
