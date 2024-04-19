<?php

/**
 * The admin-specific functionality of the plugin.
 *
 * @link       https://nujoplugins.com
 * @since      1.0.0
 *
 * @package    Nujo_Reward_Points
 * @subpackage Nujo_Reward_Points/admin
 */

/**
 * The admin-specific functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @package    Nujo_Reward_Points
 * @subpackage Nujo_Reward_Points/admin
 * @author     Nujo Plugins <test@test.com>
 */
class Nujo_Reward_Points_Admin
{

	/**
	 * The ID of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $plugin_name    The ID of this plugin.
	 */
	private $plugin_name;

	/**
	 * The version of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $version    The current version of this plugin.
	 */
	private $version;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 * @param      string    $plugin_name       The name of this plugin.
	 * @param      string    $version    The version of this plugin.
	 */
	public function __construct($plugin_name, $version)
	{

		$this->plugin_name = $plugin_name;
		$this->version = $version;
	}

	/**
	 * Register the stylesheets for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_styles()
	{
		wp_enqueue_style($this->plugin_name, plugin_dir_url(__FILE__) . 'css/nujo-reward-points-admin.css', array(), $this->version, 'all');
	}

	/**
	 * Register the JavaScript for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_scripts()
	{
		wp_enqueue_script($this->plugin_name, plugin_dir_url(__FILE__) . 'js/nujo-reward-points-admin.js', array('jquery'), $this->version, false);

		wp_localize_script($this->plugin_name, 'nrp_ajax_var', array(
			'url' => admin_url('admin-ajax.php'),
			'nonce_update_balance' => wp_create_nonce('nrp-update-balance')
		));
	}

	/**
	 * Run activation sequence if plugin was not activated correctly (e.g. if WC was not active
	 * at time of activation).
	 */
	public function check_activation_status()
	{
		if (empty(get_option('nrp_activated'))) {
			activate_nujo_reward_points();
		}

		// Upgrade settings if not on matching version
		$settings_ver = get_option('nrp_settings_version');
		if (!empty($settings_ver) && ($settings_ver !== NUJO_REWARD_POINTS_SETTINGS_VERSION)) {
			require_once plugin_dir_path(__FILE__) . '../includes/class-nujo-reward-points-activator.php';
			Nujo_Reward_Points_Activator::upgrade_settings();
		}
	}


	/**
	 * Add plugin to WooCommerce admin menu.
	 */
	public function add_plugin_admin_menu()
	{
		add_submenu_page('woocommerce', __('Points and Rewards', 'easy-loyalty-points-and-rewards-for-woocommerce'), __('Points and Rewards', 'easy-loyalty-points-and-rewards-for-woocommerce'), 'manage_woocommerce', $this->plugin_name . '-settings', array($this, 'display_plugin_admin_settings'), 3);
	}

	/**
	 * Display plugin admin settings page.
	 */
	public function display_plugin_admin_settings()
	{
		$active_tab 	= isset($_GET['tab']) ? sanitize_key($_GET['tab']) : null;
		$active_section = isset($_GET['section']) ? sanitize_key($_GET['section']) : null;

		$search_term 	= isset($_REQUEST['s']) ? sanitize_text_field($_REQUEST['s']) : null;
		$page 			= isset($_REQUEST['page']) ? intval($_REQUEST['page']) : null;

		// Check WC coupons are enabled
		if (get_option('woocommerce_enable_coupons') != 'yes') {

			// $wc_settings_url = esc_url(add_query_arg(
			// 	array(
			// 		'page' => 'wc-settings',
			// 	),
			// 	get_admin_url() . 'admin.php'
			// ));
			// $wc_settings_link = "<a href='$wc_settings_url'>" . __('WooCommerce Settings', 'easy-loyalty-points-and-rewards-for-woocommerce') . '</a>';

			$coupons_error_message = __('This plugin requires coupons to be enabled. Go to: WooCommerce > Settings > General > Enable coupons.', 'easy-loyalty-points-and-rewards-for-woocommerce'); 

			add_settings_error(esc_attr('nrp_enable_coupons_error'), esc_attr('nrp_enable_coupons_error'), esc_html($coupons_error_message));
		}

		require_once 'partials/' . $this->plugin_name . '-admin-display.php';
	}

	/**
	 * Flush rewrite rules once so My Account endpoint does not 404.
	 * 
	 * Hooked to: wp_loaded
	 */
	public function flush_rewrite_rules()
	{
		if (get_option('nrp_rewrite_rules_flushed') != 1) {
			flush_rewrite_rules();
			update_option('nrp_rewrite_rules_flushed', 1);
		}
	}

	/**
	 * Register plugin settings fields
	 */
	public function register_and_build_fields()
	{
		$setting_prefix = 'nrp_';
		$validator = new Nujo_Reward_Points_Form();

		/**
		 * ==================================================================
		 * GENERAL SETTINGS
		 * ==================================================================
		 */

		$page = 'nrp_general_settings_page';

		/**
		 * POINTS SECTION
		 */
		$section = 'nrp_general_settings_points_section';
		add_settings_section(
			$section,
			__('Point Settings', 'easy-loyalty-points-and-rewards-for-woocommerce'),
			// array($this, 'general_settings_points_section_output'),
			null,
			$page
		);

		// Earning ratio
		$id = $setting_prefix . 'earning_ratio';
		unset($args);
		$args = array(
			'type'      	=> 'earning_ratio',
			'subtype'   	=> '',
			'id'    		=> $id,
			'name'      	=> $id,
			'required' 		=> 'required',
			'get_options_list' => '',
			'value_type' 	=> 'normal',
			'wp_data' 		=> 'option',
		);
		add_settings_field(
			$id,
			Nujo_Reward_Points_Form::get_field_label($id),
			array($this, 'render_settings_field'),
			$page,
			$section,
			$args
		);
		register_setting($page, $id, array('sanitize_callback' => array($validator, 'validate_earning_ratio')));

		// Redemption ratio
		$id = $setting_prefix . 'redemption_ratio';
		unset($args);
		$args = array(
			'type'      	=> 'redemption_ratio',
			'subtype'   	=> '',
			'id'    		=> $id,
			'name'      	=> $id,
			'required' 		=> 'required',
			'get_options_list' => '',
			'value_type' 	=> 'normal',
			'wp_data' 		=> 'option',
		);
		add_settings_field(
			$id,
			Nujo_Reward_Points_Form::get_field_label($id),
			array($this, 'render_settings_field'),
			$page,
			$section,
			$args
		);
		register_setting($page, $id, array('sanitize_callback' => array($validator, 'validate_redemption_ratio')));

		// Minimum points to redeem
		$id = $setting_prefix . 'min_redemption_points';
		unset($args);
		$args = array(
			'type'      	=> 'input',
			'subtype'   	=> 'text',
			'id'    		=> $id,
			'name'      	=> $id,
			'required' 		=> '',
			'get_options_list' => '',
			'value_type' 	=> 'normal',
			'wp_data' 		=> 'option',
			'size'			=> 20,
			'postpend_value' => ' ' . __('points', 'easy-loyalty-points-and-rewards-for-woocommerce'),
			'help'			=> __('Only applicable if greater than "Redeeming Points" amount', 'easy-loyalty-points-and-rewards-for-woocommerce')
		);
		add_settings_field(
			$id,
			Nujo_Reward_Points_Form::get_field_label($id),
			array($this, 'render_settings_field'),
			$page,
			$section,
			$args
		);
		register_setting($page, $id, array('sanitize_callback' => array($validator, 'validate_min_redemption_points')));

		// Min order value to redeem
		$id = $setting_prefix . 'min_redemption_order_value';
		unset($args);
		$args = array(
			'type'      	=> 'input',
			'subtype'   	=> 'text',
			'id'    		=> $id,
			'name'      	=> $id,
			'required' 		=> '',
			'get_options_list' => '',
			'value_type' 	=> 'normal',
			'wp_data' 		=> 'option',
			'size' 			=> 20,
			'postpend_value' => ' ' . get_woocommerce_currency_symbol() . ' ' . get_woocommerce_currency(),
			'is_price'		=> true,
		);
		add_settings_field(
			$id,
			Nujo_Reward_Points_Form::get_field_label($id),
			array($this, 'render_settings_field'),
			$page,
			$section,
			$args
		);
		register_setting($page, $id, array('sanitize_callback' => array($validator, 'validate_min_redemption_order_value')));

		// Assign point status
		$id = $setting_prefix . 'assign_points_status';
		unset($args);
		$args = array(
			'type'      	=> 'select',
			'subtype'   	=> '',
			'id'    		=> $id,
			'name'      	=> $id,
			'required' 		=> 'required',
			'get_options_list' => array(
				'paid' => __('Order paid', 'easy-loyalty-points-and-rewards-for-woocommerce'),
				'completed' => __('Order completed', 'easy-loyalty-points-and-rewards-for-woocommerce'),
			),
			'value_type' 	=> 'normal',
			'wp_data' 		=> 'option',
		);
		add_settings_field(
			$id,
			Nujo_Reward_Points_Form::get_field_label($id),
			array($this, 'render_settings_field'),
			$page,
			$section,
			$args
		);
		register_setting($page, $id, array('sanitize_callback' => 'sanitize_key'));

		// Tax mode
		$id = $setting_prefix . 'tax_mode';
		unset($args);
		$args = array(
			'type'      	=> 'select',
			'subtype'   	=> '',
			'id'    		=> $id,
			'name'      	=> $id,
			'required' 		=> 'required',
			'get_options_list' => array(
				'incl' => __('Use tax inclusive prices for calculations', 'easy-loyalty-points-and-rewards-for-woocommerce'),
				'excl' => __('Use tax exclusive prices for calculations', 'easy-loyalty-points-and-rewards-for-woocommerce'),
			),
			'value_type' 	=> 'normal',
			'wp_data' 		=> 'option',
		);
		add_settings_field(
			$id,
			Nujo_Reward_Points_Form::get_field_label($id),
			array($this, 'render_settings_field'),
			$page,
			$section,
			$args
		);
		register_setting($page, $id, array('sanitize_callback' => 'sanitize_key'));

		// Points label
		$id = $setting_prefix . 'points_label';
		unset($args);
		$args = array(
			'type'      	=> 'input',
			'subtype'   	=> 'text',
			'id'    		=> $id,
			'name'      	=> $id,
			'required' 		=> 'required',
			'get_options_list' => '',
			'value_type' 	=> 'normal',
			'wp_data' 		=> 'option',
			'size'			=> 20,
		);
		add_settings_field(
			$id,
			Nujo_Reward_Points_Form::get_field_label($id),
			array($this, 'render_settings_field'),
			$page,
			$section,
			$args
		);
		register_setting($page, $id, array('sanitize_callback' => array($validator, 'validate_points_label')));

		/**
		 * PRODUCT MESSAGES
		 */
		$section = 'nrp_message_product_section';
		add_settings_section(
			$section,
			__('Product Page Messages', 'easy-loyalty-points-and-rewards-for-woocommerce'),
			// array($this, 'general_settings_points_section_output'),
			null,
			$page
		);

		// Single product message
		$id = $setting_prefix . 'message_single_product';
		unset($args);
		$args = array(
			'type'      	=> 'textarea',
			'subtype'   	=> '',
			'id'    		=> $id,
			'name'      	=> $id,
			'required' 		=> '',
			'get_options_list' => '',
			'value_type' 	=> 'normal',
			'wp_data' 		=> 'option',
			'help'			=> __('Available values:', 'easy-loyalty-points-and-rewards-for-woocommerce') . ' <span>{points}</span>, <span>{points_label}</span>, <span>{points_value}</span>.',
			'default_message' => Nujo_Reward_Points_Message::get_message('single_product'),
		);
		add_settings_field(
			$id,
			Nujo_Reward_Points_Form::get_field_label($id),
			array($this, 'render_settings_field'),
			$page,
			$section,
			$args
		);
		register_setting($page, $id, array('sanitize_callback' => 'sanitize_textarea_field'));

		// Variable product message
		$id = $setting_prefix . 'message_variable_product';
		unset($args);
		$args = array(
			'type'      	=> 'textarea',
			'subtype'   	=> '',
			'id'    		=> $id,
			'name'      	=> $id,
			'required' 		=> '',
			'get_options_list' => '',
			'value_type' 	=> 'normal',
			'wp_data' 		=> 'option',
			'help'			=> __('Available values:', 'easy-loyalty-points-and-rewards-for-woocommerce') . ' <span>{points}</span>, <span>{points_label}</span>, <span>{points_value}</span>.',
			'default_message' => Nujo_Reward_Points_Message::get_message('variable_product'),
		);
		add_settings_field(
			$id,
			Nujo_Reward_Points_Form::get_field_label($id),
			array($this, 'render_settings_field'),
			$page,
			$section,
			$args
		);
		register_setting($page, $id, array('sanitize_callback' => 'sanitize_textarea_field'));

		$section = 'nrp_message_cart_section';
		add_settings_section(
			$section,
			__('Cart/Checkout Messages', 'easy-loyalty-points-and-rewards-for-woocommerce'),
			// array($this, 'general_settings_points_section_output'),
			null,
			$page
		);

		// Cart complete purchase message
		$id = $setting_prefix . 'message_cart_complete_purchase';
		unset($args);
		$args = array(
			'type'      	=> 'textarea',
			'subtype'   	=> '',
			'id'    		=> $id,
			'name'      	=> $id,
			'required' 		=> '',
			'get_options_list' => '',
			'value_type' 	=> 'normal',
			'wp_data' 		=> 'option',
			'help'			=> __('Available values:', 'easy-loyalty-points-and-rewards-for-woocommerce') . ' <span>{points}</span>, <span>{points_label}</span>, <span>{points_value}</span>.',
			'default_message' => Nujo_Reward_Points_Message::get_message('cart_complete_purchase'),
		);
		add_settings_field(
			$id,
			Nujo_Reward_Points_Form::get_field_label($id),
			array($this, 'render_settings_field'),
			$page,
			$section,
			$args
		);
		register_setting($page, $id, array('sanitize_callback' => 'sanitize_textarea_field'));

		// Cart guest message
		$id = $setting_prefix . 'message_cart_guest';
		unset($args);
		$args = array(
			'type'      	=> 'textarea',
			'subtype'   	=> '',
			'id'    		=> $id,
			'name'      	=> $id,
			'required' 		=> '',
			'get_options_list' => '',
			'value_type' 	=> 'normal',
			'wp_data' 		=> 'option',
			'help'			=> __('Available values:', 'easy-loyalty-points-and-rewards-for-woocommerce') . ' <span>{points}</span>, <span>{points_label}</span>, <span>{points_value}</span>.',
			'default_message' => Nujo_Reward_Points_Message::get_message('cart_guest'),
		);
		add_settings_field(
			$id,
			Nujo_Reward_Points_Form::get_field_label($id),
			array($this, 'render_settings_field'),
			$page,
			$section,
			$args
		);
		register_setting($page, $id, array('sanitize_callback' => 'sanitize_textarea_field'));

		// Cart value under min redemption spend message
		$id = $setting_prefix . 'message_cart_reward_min_spend';
		unset($args);
		$args = array(
			'type'      	=> 'textarea',
			'subtype'   	=> '',
			'id'    		=> $id,
			'name'      	=> $id,
			'required' 		=> '',
			'get_options_list' => '',
			'value_type' 	=> 'normal',
			'wp_data' 		=> 'option',
			'help'			=> __('Available values:', 'easy-loyalty-points-and-rewards-for-woocommerce') . ' <span>{points_balance}</span>, <span>{points_label}</span>, <span>{min_spend_remaining}</span>,<br><span>{reward_value}</span>.',
			'default_message' => Nujo_Reward_Points_Message::get_message('cart_reward_min_spend'),
		);
		add_settings_field(
			$id,
			Nujo_Reward_Points_Form::get_field_label($id),
			array($this, 'render_settings_field'),
			$page,
			$section,
			$args
		);
		register_setting($page, $id, array('sanitize_callback' => 'sanitize_textarea_field'));

		// Under min points amount message
		$id = $setting_prefix . 'message_cart_reward_min_points';
		unset($args);
		$args = array(
			'type'      	=> 'textarea',
			'subtype'   	=> '',
			'id'    		=> $id,
			'name'      	=> $id,
			'required' 		=> '',
			'get_options_list' => '',
			'value_type' 	=> 'normal',
			'wp_data' 		=> 'option',
			'help'			=> __('Available values:', 'easy-loyalty-points-and-rewards-for-woocommerce') . ' <span>{points_balance}</span>, <span>{points_label}</span>, <span>{min_points_to_redeem}</span></span>.',
			'default_message' => Nujo_Reward_Points_Message::get_message('cart_reward_min_points'),
		);
		add_settings_field(
			$id,
			Nujo_Reward_Points_Form::get_field_label($id),
			array($this, 'render_settings_field'),
			$page,
			$section,
			$args
		);
		register_setting($page, $id, array('sanitize_callback' => 'sanitize_textarea_field'));

		// Cart apply discount message
		$id = $setting_prefix . 'message_cart_apply_discount';
		unset($args);
		$args = array(
			'type'      	=> 'textarea',
			'subtype'   	=> '',
			'id'    		=> $id,
			'name'      	=> $id,
			'required' 		=> '',
			'get_options_list' => '',
			'value_type' 	=> 'normal',
			'wp_data' 		=> 'option',
			'help'			=> __('Available values:', 'easy-loyalty-points-and-rewards-for-woocommerce') . ' <span>{points_balance}</span>, <span>{points_label}</span>, <span>{points_to_redeem}</span>,<br><span>{reward_value}</span>.',
			'default_message' => Nujo_Reward_Points_Message::get_message('cart_apply_discount'),
		);
		add_settings_field(
			$id,
			Nujo_Reward_Points_Form::get_field_label($id),
			array($this, 'render_settings_field'),
			$page,
			$section,
			$args
		);
		register_setting($page, $id, array('sanitize_callback' => 'sanitize_textarea_field'));

		// Cart apply discount message button
		$id = $setting_prefix . 'message_cart_apply_discount_button';
		unset($args);
		$args = array(
			'type'      	=> 'input',
			'subtype'   	=> 'text',
			'id'    		=> $id,
			'name'      	=> $id,
			'required' 		=> 'required',
			'get_options_list' => '',
			'value_type' 	=> 'normal',
			'wp_data' 		=> 'option',
			'size'			=> 20,
		);
		add_settings_field(
			$id,
			Nujo_Reward_Points_Form::get_field_label($id),
			array($this, 'render_settings_field'),
			$page,
			$section,
			$args
		);
		register_setting($page, $id, array('sanitize_callback' => array($validator, 'validate_cart_apply_discount_button')));

		/**
		 * ==================================================================
		 * ADVANCED SETTINGS
		 * ==================================================================
		 */

		$page = 'nrp_advanced_settings_page';

		/**
		 * ADVANCED SETTINGS
		 */
		$section = 'nrp_advanced_settings_section';
		add_settings_section(
			$section,
			__('Advanced Settings', 'easy-loyalty-points-and-rewards-for-woocommerce'),
			// array($this, 'advanced_settings_section_output'),
			null,
			$page
		);

		// Coupon calculation mode
		$id = $setting_prefix . 'coupon_calculation_mode';
		unset($args);
		$args = array(
			'type'      	=> 'select',
			'subtype'   	=> '',
			'id'    		=> $id,
			'name'      	=> $id,
			'required' 		=> 'required',
			'get_options_list' => array(
				'total' => __('Calculate points after discounts/coupons', 'easy-loyalty-points-and-rewards-for-woocommerce'),
				'subtotal' => __('Calculate points before discounts/coupons', 'easy-loyalty-points-and-rewards-for-woocommerce')
			),
			'value_type' 	=> 'normal',
			'wp_data' 		=> 'option',
		);
		add_settings_field(
			$id,
			Nujo_Reward_Points_Form::get_field_label($id),
			array($this, 'render_settings_field'),
			$page,
			$section,
			$args
		);
		register_setting($page, $id, array('sanitize_callback' => 'sanitize_key'));

		// Rounding mode
		$id = $setting_prefix . 'rounding_mode';
		unset($args);
		$args = array(
			'type'      	=> 'select',
			'subtype'   	=> '',
			'id'    		=> $id,
			'name'      	=> $id,
			'required' 		=> 'required',
			'get_options_list' => array(
				'nearest' => __('Round to nearest integer', 'easy-loyalty-points-and-rewards-for-woocommerce'),
				'up' => __('Round up', 'easy-loyalty-points-and-rewards-for-woocommerce'),
				'down' => __('Round down', 'easy-loyalty-points-and-rewards-for-woocommerce'),
			),
			'value_type' 	=> 'normal',
			'wp_data' 		=> 'option'
		);
		add_settings_field(
			$id,
			Nujo_Reward_Points_Form::get_field_label($id),
			array($this, 'render_settings_field'),
			$page,
			$section,
			$args
		);
		register_setting($page, $id, array('sanitize_callback' => 'sanitize_key'));

		// Delete plugin settings on uninstall
		$id = $setting_prefix . 'uninstall_delete_settings';
		unset($args);
		$args = array(
			'type'      	=> 'input',
			'subtype'   	=> 'checkbox',
			'id'    		=> $id,
			'name'      	=> $id,
			'required' 		=> '',
			'get_options_list' => array(),
			'value_type' 	=> 'normal',
			'wp_data' 		=> 'option',
			'postpend_value' => __('Delete plugin settings when plugin is uninstalled. This cannot be reversed - use carefully!', 'easy-loyalty-points-and-rewards-for-woocommerce'),
		);
		add_settings_field(
			$id,
			Nujo_Reward_Points_Form::get_field_label($id),
			array($this, 'render_settings_field'),
			$page,
			$section,
			$args
		);
		register_setting($page, $id, array('sanitize_callback' => 'sanitize_text_field'));

		// Delete points data tables on uninstall
		$id = $setting_prefix . 'uninstall_delete_points_data';
		unset($args);
		$args = array(
			'type'      	=> 'input',
			'subtype'   	=> 'checkbox',
			'id'    		=> $id,
			'name'      	=> $id,
			'required' 		=> '',
			'get_options_list' => array(),
			'value_type' 	=> 'normal',
			'wp_data' 		=> 'option',
			'postpend_value' => __('Delete points log and balances when plugin is uninstalled. This cannot be reversed - use carefully!', 'easy-loyalty-points-and-rewards-for-woocommerce'),
		);
		add_settings_field(
			$id,
			Nujo_Reward_Points_Form::get_field_label($id),
			array($this, 'render_settings_field'),
			$page,
			$section,
			$args
		);
		register_setting($page, $id, array('sanitize_callback' => 'sanitize_text_field'));

		// Debug mode
		$id = $setting_prefix . 'debug';
		unset($args);
		$args = array(
			'type'      	=> 'input',
			'subtype'   	=> 'checkbox',
			'id'    		=> $id,
			'name'      	=> $id,
			'required' 		=> '',
			'get_options_list' => array(
				'0' => __('No', 'easy-loyalty-points-and-rewards-for-woocommerce'),
				'1' => __('Yes', 'easy-loyalty-points-and-rewards-for-woocommerce')
			),
			'value_type' 	=> 'normal',
			'wp_data' 		=> 'option',
			'postpend_value' => __('Enable debug mode', 'easy-loyalty-points-and-rewards-for-woocommerce'),
		);
		add_settings_field(
			$id,
			Nujo_Reward_Points_Form::get_field_label($id),
			array($this, 'render_settings_field'),
			$page,
			$section,
			$args
		);
		register_setting($page, $id, array('sanitize_callback' => 'sanitize_text_field'));
	}

	// public function advanced_settings_section_output()
	// {
	// 	_e('You can usually ignore these settings unless instructed by support.', 'easy-loyalty-points-and-rewards-for-woocommerce');
	// }

	/**
	 * Render settings field
	 */
	public function render_settings_field($args)
	{

		if ($args['wp_data'] == 'option') {
			$wp_data_value = get_option($args['name']);
		} elseif ($args['wp_data'] == 'post_meta') {
			$wp_data_value = get_post_meta($args['post_id'], $args['name'], true);
		}

		$value = ($args['value_type'] == 'serialized') ? serialize($wp_data_value) : $wp_data_value;

		switch ($args['type']) {

			case 'input':

				if ($args['subtype'] != 'checkbox') {

					$required 			= (isset($args['required'])) ? $args['required'] : '';
					$size 				= (isset($args['size'])) ? $args['size'] : '40';
					$class 				= (isset($args['class'])) ? $args['class'] : '';
					$prepend_start 		= (isset($args['prepend_value'])) ? '<div class="input-prepend"> <span class="add-on">' . esc_html($args['prepend_value']) . '</span>' : '';
					$prepend_end 		= (isset($args['prepend_value'])) ? '</div>' : '';
					$postpend_value 	= (isset($args['postpend_value'])) ? $args['postpend_value'] : '';

					echo wp_kses($prepend_start, nrp_allowed_html());
					echo '<input type="' . esc_attr($args['subtype']) . '" id="' . esc_attr($args['id']) . '" name="' . esc_attr($args['name']) . '" value="' . esc_attr($value) . '" size="' . esc_attr($size) . '" class="' . esc_attr($class) . '" ' . esc_attr($required) . ' />' . wp_kses($postpend_value, nrp_allowed_html());
					echo wp_kses($prepend_end, nrp_allowed_html());

					if (!empty($args['help'])) {
						echo '<p class="nrp-help-text">' . wp_kses($args['help'], nrp_allowed_html()) . '</p>';
					}
				} else {

					$checked 			= ($value) ? 'checked' : '';
					$postpend_value 	= (isset($args['postpend_value'])) ? $args['postpend_value'] : '';

					echo '<input type="' . esc_attr($args['subtype']) . '" id="' . esc_attr($args['id']) . '" name="' . esc_attr($args['name']) . '" value="1" ' . esc_attr($checked) . ' /> ' . wp_kses($postpend_value, nrp_allowed_html());

					if (!empty($args['help'])) {
						echo '<p class="nrp-help-text">' . wp_kses($args['help'], nrp_allowed_html()) . '</p>';
					}
				}

				break;

			case 'textarea':

				echo '<textarea id="' . esc_attr($args['id']) . '" name="' . esc_attr($args['name']) . '" style="width: 100%; max-width: 500px;" rows="3">' . esc_textarea($value) . '</textarea>';

				if (!empty($args['help'])) {
					echo '<p class="nrp-help-text">' . wp_kses($args['help'], nrp_allowed_html()) . '</p>';
				}

				if (!empty($args['default_message'])) {
					echo '<p class="nrp-help-text"><a href="#" class="nrp-reset-message" data-id="' . esc_attr($args['id']) . '" data-message="' . esc_attr($args['default_message']) . '">' . esc_html(__('Restore to default', 'easy-loyalty-points-and-rewards-for-woocommerce')) . '</a></p>';
				}

				break;

			case 'select':

				echo '<select id="' . esc_attr($args['id']) . '" name="' . esc_attr($args['name']) . '">';
				foreach ($args['get_options_list'] as $option_key => $option_value) :
					$selected = ($value == $option_key) ? 'selected' : '';
					echo '<option value="' . esc_attr($option_key) . '" ' . esc_attr($selected) . '>' . esc_attr($option_value) . '</option>';
				endforeach;
				echo '</select>';

				if (!empty($args['help'])) {
					echo '<p class="nrp-help-text">' . wp_kses($args['help'], nrp_allowed_html()) . '</p>';
				}

				break;

			case 'earning_ratio':
				$ratio_value = (!empty($value['value'])) ? $value['value'] : '';
				$ratio_points = (!empty($value['points'])) ? $value['points'] : '';
				echo '<div style="display: inline-block; background: #fff; border: 1px solid rgb(226, 228, 231); padding: 10px; min-width: 400px;">';
				echo esc_html__('Spending', 'easy-loyalty-points-and-rewards-for-woocommerce') . ' ' . esc_html(get_woocommerce_currency_symbol()) . ' <input id="nrp_earning_ratio[value]" name="nrp_earning_ratio[value]" type="text" size="5" class="" value="' . esc_attr($ratio_value) . '" required>';
				echo ' = ';
				echo '<input id="nrp_earning_ratio[points]" name="nrp_earning_ratio[points]" type="text" size="5" value="' . esc_attr($ratio_points) . '" required> points';
				echo '</div>';
				break;

			case 'redemption_ratio':
				$ratio_value = (!empty($value['value'])) ? $value['value'] : '';
				$ratio_points = (!empty($value['points'])) ? $value['points'] : '';
				echo '<div style="display: inline-block; background: #fff; border: 1px solid rgb(226, 228, 231); padding: 10px; min-width: 400px;">';
				echo '<input id="nrp_redemption_ratio[points]" name="nrp_redemption_ratio[points]" type="text" size="5" value="' . esc_attr($ratio_points) . '" required>';
				echo ' ' . esc_html__('points') . ' = ' . esc_html(get_woocommerce_currency_symbol()) . ' ';
				echo '<input id="nrp_redemption_ratio[value]" name="nrp_redemption_ratio[value]" type="text" size="5" value="' . esc_attr($ratio_value) . '" required>' . ' ' . esc_html__('discount');
				echo '</div>';
				echo '<p class="nrp-help-text">' . esc_html(__('Points are redeemed in multiples of this amount', 'easy-loyalty-points-and-rewards-for-woocommerce')) .'</p>';
				break;

			default:
				# code...
				break;
		}
	}

	/**
	 * Save points earning ratio.
	 */
	public function save_earning_ratio($old_value, $value)
	{
		$ratio_value = wc_format_decimal($value['value']);
		$ratio_points = intval($value['points']);

		if (!empty($ratio_value) && !empty($ratio_points)) {
			$points_per_unit = wc_format_decimal($ratio_points / $ratio_value);
			update_option('nrp_points_per_unit', $points_per_unit);
		} else {
			update_option('nrp_points_per_unit', 0);
		}
	}

	/**
	 * Save points redemption ratio.
	 */
	public function save_redemption_ratio($old_value, $value)
	{
		$ratio_value = wc_format_decimal($value['value']);
		$ratio_points = intval($value['points']);

		if (!empty($ratio_points) && !empty($ratio_value)) {
			$points_per_unit = wc_format_decimal($ratio_value / $ratio_points);
			$redemption_increment = max(1, round($ratio_points));

			update_option('nrp_point_value', $points_per_unit);
			update_option('nrp_redemption_increment', $redemption_increment);
		} else {
			update_option('nrp_point_value', 0);
			update_option('nrp_redemption_increment', 1);
		}
	}

	/**
	 * Process AJAX update of customer balance
	 */
	public function ajax_update_balance()
	{
		// Check for nonce security      
		if (!wp_verify_nonce($_POST['nonce'], 'nrp-update-balance')) {
			wp_send_json_error(array(
				'message' => __('Authentication Error: please refresh the page and try again', 'easy-loyalty-points-and-rewards-for-woocommerce'),
			), 401);
			die();
		}

		// Check correct user role
		if (!current_user_can('manage_woocommerce')) {
			wp_send_json_error(array(
				'message' => __('Authentication Error: user role not sufficient', 'easy-loyalty-points-and-rewards-for-woocommerce'),
			), 403);
			die();
		}

		// Parse JSON form data
		$new_balance 	= sanitize_text_field($_POST['new_balance']);
		$account_id		= intval($_POST['account_id']);

		// Check new balance is integer
		if (!is_numeric($new_balance) || (fmod($new_balance, 1) !== 0.00)) {
			wp_send_json_error(array(
				'message' => sprintf(__('Error: Balance must be a whole number with no comma or decimal', 'easy-loyalty-points-and-rewards-for-woocommerce'), nrp_points_label())
			));
		}

		// Check balance not negative
		if ($new_balance < 0) {
			wp_send_json_error(array(
				'message' => sprintf(__('Error: Balance cannot be negative', 'easy-loyalty-points-and-rewards-for-woocommerce'), nrp_points_label())
			));
		}

		// Loading account
		try {
			$account = new Nujo_Reward_Points_Account($account_id, false);
		} catch (Exception $e) {
			wp_send_json_error(array(
				'message' => __('Error:', 'easy-loyalty-points-and-rewards-for-woocommerce') . ' ' . $e->getMessage(),
			));
		}

		$account->set_balance_manually($new_balance);

		wp_send_json_success(array(
			'message' => sprintf(__('Balance updated to %2$s', 'easy-loyalty-points-and-rewards-for-woocommerce'), nrp_points_label(), nrp_format_points($account->get_points_balance())),
			'account_id' => $account->get_account_id(),
			'balance' => nrp_format_points($account->get_points_balance())
		));
	}

	/**
	 * Add product data tab.
	 * 
	 * Hooked to: woocommerce_product_data_tabs
	 */
	public function add_product_data_tab($original_tabs)
	{

		$new_tab['nrp_tab'] = array(
			'label' => __('Reward Points', 'easy-loyalty-points-and-rewards-for-woocommerce'),
			'target' => 'nrp_product_data',
			'class'		=> array('show_if_simple', 'show_if_variable'),
		);

		$insert_at_position = -1; // This can be changed
		$tabs = array_slice($original_tabs, 0, $insert_at_position, true); // First part of original tabs
		$tabs = array_merge($tabs, $new_tab); // Add new
		$tabs = array_merge($tabs, array_slice($original_tabs, $insert_at_position, null, true)); // Glue the second part of original

		return $tabs;
	}

	/**
	 * Register plugin meta box on order page.
	 * 
	 * Hooked to: add_meta_boxes
	 */
	public function add_order_meta_box()
	{
		add_meta_box(
			'nrp_order_meta_box',                 // Unique ID
			nrp_points_label(),      // Box title
			array($this, 'display_order_meta_box'),  // Content callback, must be of type callable
			'shop_order'                            // Post type
		);
	}

	/**
	 * Render plugin meta box on order page.
	 */
	public function display_order_meta_box($post)
	{
		$order = wc_get_order($post);

		$version 			= $order->get_meta('_nrp_version', true);
		$points_total 		= $order->get_meta('_nrp_points_total', true);
		$points_cancelled 	= $order->get_meta('_nrp_points_cancelled', true);
		$points_added 		= $order->get_meta('_nrp_points_added', true);
		$points_redeemed 	= $order->get_meta('_nrp_points_redeemed', true);
		$is_processed 		= $order->get_meta('_nrp_is_processed', true);
		$has_account 		= $order->get_meta('_nrp_has_account', true);

		if (empty($points_total))
			$points_total = 0;

		if (empty($points_cancelled))
			$points_cancelled = 0;

		if (empty($points_added))
			$points_added = 0;

		if (empty($points_redeemed))
			$points_redeemed = 0;

		if ($points_total == 0) {
			$status = 'none';
		} elseif ($points_cancelled == $points_total) {
			$status = 'cancelled';
		} elseif ($points_total > $points_added) {
			if (get_option('nrp_assign_points_status') == 'completed') {
				$status = 'pending-completed';
			} else {
				$status = 'pending-paid';
			}
		} elseif ($points_total <= $points_total) {
			$status = 'awarded';
		} else {
			$status = 'unknown';
		}

		if ($has_account == true) {
			$user = get_userdata($order->get_customer_id());

			$balance_url = esc_url(add_query_arg(
				array(
					'page' => 'nujo-reward-points-settings',
					'tab' => 'balances',
					's' => urlencode($user->user_email),
				),
				get_admin_url() . 'admin.php'
			));

			$points_log_url = esc_url(add_query_arg(
				array(
					'page' => 'nujo-reward-points-settings',
					'tab' => 'log',
					's' => urlencode($order->get_id()),
				),
				get_admin_url() . 'admin.php'
			));
		}

		require_once 'partials/' . $this->plugin_name . '-admin-order-meta.php';
	}

	/**
	 * Hide plugin meta fields on order items unless debug mode.
	 * 
	 * Hooked to: woocommerce_hidden_order_itemmeta
	 */
	function hidden_order_item_meta($arr)
	{
		if (empty(get_option('nrp_debug'))) {
			$arr[] = '_nrp_points';
			$arr[] = '_nrp_quantity';
		}
		return $arr;
	}

	/**
	 * Add plugin list action links.
	 */
	function plugin_action_links($links)
	{
		// Build and escape the URL.
		$url = esc_url(add_query_arg(
			array(
				'page' => 'nujo-reward-points-settings',
				'tab' => 'settings',
			),
			get_admin_url() . 'admin.php'
		));
		$settings_link = "<a href='$url'>" . __('Settings', 'easy-loyalty-points-and-rewards-for-woocommerce') . '</a>';
		array_unshift($links, $settings_link);
		$url = "https://nujoplugins.com/woocommerce-points-and-rewards/?utm_source=nrp_free&utm_medium=wordpress_plugin&utm_content=plugins_page&utm_campaign=nrp_pro";
		$settings_link = "<a href='$url' target='_blank'><strong style='display: inline; color: #11967A'>" . __('Upgrade to Pro', 'easy-loyalty-points-and-rewards-for-woocommerce') . '</strong></a>';
		array_unshift($links, $settings_link);
		return $links;
	}
}
