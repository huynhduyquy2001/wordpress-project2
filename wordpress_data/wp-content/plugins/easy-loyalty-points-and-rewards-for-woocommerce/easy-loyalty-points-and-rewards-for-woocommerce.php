<?php

/**
 * The plugin bootstrap file
 *
 * @link              https://nujoplugins.com
 * @since             1.0.0
 * @package           Nujo_Reward_Points
 *
 * @wordpress-plugin
 * Plugin Name:       Easy Loyalty Points and Rewards for WooCommerce
 * Plugin URI:        https://wordpress.org/plugins/easy-loyalty-points-and-rewards-for-woocommerce
 * Description:       Increase WooCommerce customer loyalty with a points and rewards system.
 * Version:           1.4.0
 * Author:            Nujo Plugins
 * Author URI:        https://nujoplugins.com
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       easy-loyalty-points-and-rewards-for-woocommerce
 * Domain Path:       /languages
 * Requires at least: 5.8
 * Requires PHP:	  7.3
 * 
 * WC requires at least: 5.6
 * WC tested up to: 6.3
 * 
 * Copyright 2022 Nujo Plugins
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA 02110-1301 USA
 */

// If this file is called directly, abort.
if (!defined('WPINC')) {
	die;
}

/**
 * Current plugin version.
 */
define('NUJO_REWARD_POINTS_VERSION', '1.4.0');
define('NUJO_REWARD_POINTS_SETTINGS_VERSION', '3');

/**
 * Check WooCommerce is active.
 * Check pro version is not active.
 */
if (in_array('woocommerce/woocommerce.php', apply_filters('active_plugins', get_option('active_plugins')))
&& !in_array('nujo-points-and-rewards-pro/nujo-points-and-rewards-pro.php', apply_filters('active_plugins', get_option('active_plugins')))) {

	/**
	 * The code that runs during plugin activation.
	 * This action is documented in includes/class-nujo-reward-points-activator.php
	 */
	function activate_nujo_reward_points()
	{
		require_once plugin_dir_path(__FILE__) . 'includes/class-nujo-reward-points-activator.php';
		Nujo_Reward_Points_Activator::activate();
	}

	/**
	 * The code that runs during plugin deactivation.
	 * This action is documented in includes/class-nujo-reward-points-deactivator.php
	 */
	function deactivate_nujo_reward_points()
	{
		require_once plugin_dir_path(__FILE__) . 'includes/class-nujo-reward-points-deactivator.php';
		Nujo_Reward_Points_Deactivator::deactivate();
	}

	register_activation_hook(__FILE__, 'activate_nujo_reward_points');
	register_deactivation_hook(__FILE__, 'deactivate_nujo_reward_points');

	/**
	 * The core plugin class that is used to define internationalization,
	 * admin-specific hooks, and public-facing site hooks.
	 */
	require plugin_dir_path(__FILE__) . 'includes/class-nujo-reward-points.php';

	/**
	 * Begins execution of the plugin.
	 *
	 * Since everything within the plugin is registered via hooks,
	 * then kicking off the plugin from this point in the file does
	 * not affect the page life cycle.
	 *
	 * @since    1.0.0
	 */
	function run_nujo_reward_points()
	{

		$plugin = new Nujo_Reward_Points();
		$plugin->run();
	}
	run_nujo_reward_points();

}
