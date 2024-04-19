<?php

/**
 * The file that defines the core plugin class
 *
 * @link       https://nujoplugins.com
 * @since      1.0.0
 *
 * @package    Nujo_Reward_Points
 * @subpackage Nujo_Reward_Points/includes
 */

/**
 * The core plugin class.
 *
 * This is used to define internationalization, admin-specific hooks, and
 * public-facing site hooks.
 *
 * Also maintains the unique identifier of this plugin as well as the current
 * version of the plugin.
 *
 * @since      1.0.0
 * @package    Nujo_Reward_Points
 * @subpackage Nujo_Reward_Points/includes
 * @author     Nujo Plugins <test@test.com>
 */
class Nujo_Reward_Points
{

	/**
	 * The loader that's responsible for maintaining and registering all hooks that power
	 * the plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      Nujo_Reward_Points_Loader    $loader    Maintains and registers all hooks for the plugin.
	 */
	protected $loader;

	/**
	 * The unique identifier of this plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      string    $plugin_name    The string used to uniquely identify this plugin.
	 */
	protected $plugin_name;

	/**
	 * The current version of the plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      string    $version    The current version of the plugin.
	 */
	protected $version;

	/**
	 * Define the core functionality of the plugin.
	 *
	 * Set the plugin name and the plugin version that can be used throughout the plugin.
	 * Load the dependencies, define the locale, and set the hooks for the admin area and
	 * the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function __construct()
	{
		if (defined('NUJO_REWARD_POINTS_VERSION')) {
			$this->version = NUJO_REWARD_POINTS_VERSION;
		} else {
			$this->version = '1.0.0';
		}
		$this->plugin_name = 'nujo-reward-points';

		$this->load_dependencies();
		$this->set_locale();
		$this->define_admin_hooks();
		$this->define_public_hooks();
	}

	/**
	 * Load the required dependencies for this plugin.
	 *
	 * Include the following files that make up the plugin:
	 *
	 * - Nujo_Reward_Points_Loader. Orchestrates the hooks of the plugin.
	 * - Nujo_Reward_Points_i18n. Defines internationalization functionality.
	 * - Nujo_Reward_Points_Admin. Defines all hooks for the admin area.
	 * - Nujo_Reward_Points_Public. Defines all hooks for the public side of the site.
	 *
	 * Create an instance of the loader which will be used to register the hooks
	 * with WordPress.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function load_dependencies()
	{

		/**
		 * The class responsible for orchestrating the actions and filters of the
		 * core plugin.
		 */
		require_once plugin_dir_path(dirname(__FILE__)) . 'includes/class-nujo-reward-points-loader.php';

		/**
		 * The class responsible for defining internationalization functionality
		 * of the plugin.
		 */
		require_once plugin_dir_path(dirname(__FILE__)) . 'includes/class-nujo-reward-points-i18n.php';

		/**
		 * The class responsible for defining all actions that occur in the admin area.
		 */
		require_once plugin_dir_path(dirname(__FILE__)) . 'admin/class-nujo-reward-points-admin.php';

		/**
		 * The class responsible for defining all actions that occur in the public-facing
		 * side of the site.
		 */
		require_once plugin_dir_path(dirname(__FILE__)) . 'public/class-nujo-reward-points-public.php';

		require_once plugin_dir_path(dirname(__FILE__)) . 'includes/class-nujo-reward-points-account.php';
		require_once plugin_dir_path(dirname(__FILE__)) . 'includes/class-nujo-reward-points-wp-list-table.php';
		require_once plugin_dir_path(dirname(__FILE__)) . 'includes/class-nujo-reward-points-account-table.php';
		require_once plugin_dir_path(dirname(__FILE__)) . 'includes/class-nujo-reward-points-log-table.php';
		require_once plugin_dir_path(dirname(__FILE__)) . 'includes/class-nujo-reward-points-redemption-coupon.php';
		require_once plugin_dir_path(dirname(__FILE__)) . 'includes/class-nujo-reward-points-calculator.php';
		require_once plugin_dir_path(dirname(__FILE__)) . 'includes/class-nujo-reward-points-message.php';
		require_once plugin_dir_path(dirname(__FILE__)) . 'includes/class-nujo-reward-points-form.php';

		require_once plugin_dir_path(dirname(__FILE__)) . 'includes/functions.php';

		$this->loader = new Nujo_Reward_Points_Loader();
	}

	/**
	 * Define the locale for this plugin for internationalization.
	 *
	 * Uses the Nujo_Reward_Points_i18n class in order to set the domain and to register the hook
	 * with WordPress.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function set_locale()
	{

		$plugin_i18n = new Nujo_Reward_Points_i18n();

		$this->loader->add_action('plugins_loaded', $plugin_i18n, 'load_plugin_textdomain');
	}

	/**
	 * Register all of the hooks related to the admin area functionality
	 * of the plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function define_admin_hooks()
	{

		$plugin_admin = new Nujo_Reward_Points_Admin($this->get_plugin_name(), $this->get_version());

		// Flush custom endpoints
		$this->loader->add_action('wp_loaded', $plugin_admin, 'flush_rewrite_rules');

		// Check plugin activated correctly
		$this->loader->add_action('wp_loaded', $plugin_admin, 'check_activation_status');

		// Enqueue css/js
		$this->loader->add_action('admin_enqueue_scripts', $plugin_admin, 'enqueue_styles');
		$this->loader->add_action('admin_enqueue_scripts', $plugin_admin, 'enqueue_scripts');

		// Add plugin action links
		$this->loader->add_filter( 'plugin_action_links_easy-loyalty-points-and-rewards-for-woocommerce/easy-loyalty-points-and-rewards-for-woocommerce.php', $plugin_admin, 'plugin_action_links' );

		// Add settings page
		$this->loader->add_action('admin_menu', $plugin_admin, 'add_plugin_admin_menu', 50);
		$this->loader->add_action('admin_init', $plugin_admin, 'register_and_build_fields');

		// Process ajax
		$this->loader->add_action('wp_ajax_nrp_update_balance', $plugin_admin, 'ajax_update_balance');

		// Order meta box
		$this->loader->add_action('add_meta_boxes', $plugin_admin, 'add_order_meta_box');

		// Process points ratio form values
		$this->loader->add_action('update_option_nrp_earning_ratio', $plugin_admin, 'save_earning_ratio', 10, 2);
		$this->loader->add_action('update_option_nrp_redemption_ratio', $plugin_admin, 'save_redemption_ratio', 10, 2);

		// Hide order item meta
		$this->loader->add_filter('woocommerce_hidden_order_itemmeta', $plugin_admin, 'hidden_order_item_meta', 10, 1);
	}

	/**
	 * Register all of the hooks related to the public-facing functionality
	 * of the plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function define_public_hooks()
	{

		$plugin_public = new Nujo_Reward_Points_Public($this->get_plugin_name(), $this->get_version());

		// Enqueue css/js
		$this->loader->add_action('wp_enqueue_scripts', $plugin_public, 'enqueue_styles');
		$this->loader->add_action('wp_enqueue_scripts', $plugin_public, 'enqueue_scripts');

		// Points and orders
		$this->loader->add_action('woocommerce_before_add_to_cart_button', $plugin_public, 'product_display_potential_points');
		$this->loader->add_filter('woocommerce_get_item_data', $plugin_public, 'cart_display_item_points', 10, 2);
		$this->loader->add_action('woocommerce_checkout_create_order_line_item', $plugin_public, 'order_add_item_points_meta', 10, 4);
		$this->loader->add_action('woocommerce_checkout_create_order', $plugin_public, 'order_add_total_points_meta', 20, 2);
		$this->loader->add_action('woocommerce_order_status_changed', $plugin_public, 'order_add_points_to_customer', 10, 4);

		// Redeem points
		$this->loader->add_filter('woocommerce_get_shop_coupon_data', $plugin_public, 'cart_redeem_set_wc_coupon_data', 10, 3);
		$this->loader->add_filter('woocommerce_before_calculate_totals', $plugin_public, 'cart_redeem_prevent_multiple_coupons', 10, 1);
		$this->loader->add_filter('woocommerce_cart_totals_coupon_label', $plugin_public, 'cart_redeem_coupon_label', 10, 2);
		$this->loader->add_action('woocommerce_checkout_order_processed', $plugin_public, 'order_deduct_redemption_coupon_points', 10, 1);

		// Cart notices and calculation
		$this->loader->add_filter('woocommerce_before_calculate_totals', $plugin_public, 'cart_update_points_data', 10); // TODO - only call if page not cart or checkout. Need this if customer bypasses these pages
		$this->loader->add_action('woocommerce_before_cart', $plugin_public, 'cart_update_points_data', 10);
		$this->loader->add_action('woocommerce_before_checkout_form', $plugin_public, 'cart_update_points_data', 10);
		$this->loader->add_action('woocommerce_before_cart', $plugin_public, 'print_cart_notice', 10);
		$this->loader->add_action('woocommerce_before_checkout_form', $plugin_public, 'print_cart_notice', 10);

		// Sync points accounts and emails
		$this->loader->add_action('user_register', $plugin_public, 'user_create_points_account', 10, 2);
		$this->loader->add_action('profile_update', $plugin_public, 'user_update_points_account_email', 10, 2);

		// My Account page
		$this->loader->add_action('init', $plugin_public, 'my_account_add_endpoint');
		$this->loader->add_filter('query_vars', $plugin_public, 'my_account_query_vars');
		$this->loader->add_filter('woocommerce_account_menu_items', $plugin_public, 'my_account_add_link');
		$this->loader->add_action('woocommerce_account_nrp-points_endpoint', $plugin_public, 'my_account_display_content');

	}

	/**
	 * Run the loader to execute all of the hooks with WordPress.
	 *
	 * @since    1.0.0
	 */
	public function run()
	{
		$this->loader->run();
	}

	/**
	 * The name of the plugin used to uniquely identify it within the context of
	 * WordPress and to define internationalization functionality.
	 *
	 * @since     1.0.0
	 * @return    string    The name of the plugin.
	 */
	public function get_plugin_name()
	{
		return $this->plugin_name;
	}

	/**
	 * The reference to the class that orchestrates the hooks with the plugin.
	 *
	 * @since     1.0.0
	 * @return    Nujo_Reward_Points_Loader    Orchestrates the hooks of the plugin.
	 */
	public function get_loader()
	{
		return $this->loader;
	}

	/**
	 * Retrieve the version number of the plugin.
	 *
	 * @since     1.0.0
	 * @return    string    The version number of the plugin.
	 */
	public function get_version()
	{
		return $this->version;
	}
}
