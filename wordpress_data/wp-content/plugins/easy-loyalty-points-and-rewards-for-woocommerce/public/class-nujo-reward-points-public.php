<?php

/**
 * The public-facing functionality of the plugin.
 *
 * @link       https://nujoplugins.com
 * @since      1.0.0
 *
 * @package    Nujo_Reward_Points
 * @subpackage Nujo_Reward_Points/public
 */

/**
 * The public-facing functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the public-facing stylesheet and JavaScript.
 *
 * @package    Nujo_Reward_Points
 * @subpackage Nujo_Reward_Points/public
 * @author     Nujo Plugins <test@test.com>
 */
class Nujo_Reward_Points_Public
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
	 * @param      string    $plugin_name       The name of the plugin.
	 * @param      string    $version    The version of this plugin.
	 */
	public function __construct($plugin_name, $version)
	{

		$this->plugin_name = $plugin_name;
		$this->version = $version;
	}

	/**
	 * Register the stylesheets for the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_styles()
	{
		wp_enqueue_style($this->plugin_name, plugin_dir_url(__FILE__) . 'css/nujo-reward-points-public.css', array(), $this->version, 'all');
	}

	/**
	 * Register the JavaScript for the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_scripts()
	{
		wp_enqueue_script($this->plugin_name, plugin_dir_url(__FILE__) . 'js/nujo-reward-points-public.js', array('jquery'), $this->version, false);

		wp_localize_script($this->plugin_name, 'nrp_ajax_var', array(
			'url' => admin_url('admin-ajax.php'),
			'site_url' => get_site_url(),
			'nonce_apply_coupon' => wp_create_nonce('apply-coupon')
		));
	}

	/**
	 * Display points message on product pages.
	 * 
	 * Hooked to: woocommerce_before_add_to_cart_button
	 */
	public function product_display_potential_points()
	{
		global $product;

		// Only show notice if plugin has been configured
		if (!empty(get_option('nrp_point_value'))) {

			if ($product->is_type('simple') && !empty(get_option('nrp_message_single_product'))) {

				$points = Nujo_Reward_Points_Calculator::get_product_points($product, null);

				if (!empty($points)) {

					$values = array(
						'points_balance' => nrp_format_points(nrp_get_current_user_points()),
						'points_balance_value' => wc_price(nrp_get_current_user_points()),
						'points' => nrp_format_points($points),
						'points_value' => wc_price(nrp_get_points_value($points)),
						'points_label' => nrp_points_label(true)
					);

					$values = apply_filters('nrp_message_single_product_values', $values);

					echo '<div class="nrp-product-message">';
					echo wp_kses(nrp_message(get_option('nrp_message_single_product'), $values), nrp_allowed_html());
					echo '</div>';
				}
			} elseif ($product->is_type('variable') && !empty(get_option('nrp_message_variable_product'))) {

				$points = Nujo_Reward_Points_Calculator::get_variable_product_points($product);

				if (!empty($points)) {

					$values = array(
						'points_balance' => nrp_format_points(nrp_get_current_user_points()),
						'points_balance_value' => wc_price(nrp_get_current_user_points()),
						'points' => nrp_format_points($points),
						'points_value' => wc_price(nrp_get_points_value($points)),
						'points_label' => nrp_points_label(true)
					);

					$values = apply_filters('nrp_message_variable_product_values', $values);

					echo '<div class="nrp-product-variable-message">';
					echo wp_kses(nrp_message(get_option('nrp_message_variable_product'), $values), nrp_allowed_html());
					echo '</div>';
				}
			}
		}
	}

	/**
	 * Calculate and add points data to cart.
	 * 
	 * Hooked to: woocommerce_before_cart
	 */
	public function cart_update_points_data()
	{
		$tax_mode = get_option('nrp_tax_mode');
		$calculaton_mode = get_option('nrp_coupon_calculation_mode'); // total = discounted price, subtotal = non-discounted price

		$cart = WC()->cart->cart_contents;

		foreach ($cart as $cart_item_id => $cart_item) {

			if (isset($cart_item['line_total']) && isset($cart_item['line_subtotal']) && isset($cart_item['line_tax']) && isset($cart_item['line_subtotal_tax'])) {

				if ($tax_mode == 'excl') {
					if ($calculaton_mode == 'total') {
						$cart_item_price = $cart_item['line_total'] / $cart_item['quantity'];
					} else {
						$cart_item_price = $cart_item['line_subtotal'] / $cart_item['quantity'];
					}
				} else {
					if ($calculaton_mode == 'total') {
						$cart_item_price = ($cart_item['line_total'] + $cart_item['line_tax']) / $cart_item['quantity'];
					} else {
						$cart_item_price = ($cart_item['line_subtotal'] + $cart_item['line_subtotal_tax']) / $cart_item['quantity'];
					}
				}
			} else {
				$cart_item_price = $cart_item['data']->get_price();
			}

			$item_points = Nujo_Reward_Points_Calculator::get_product_points(
				$cart_item['product_id'],
				$cart_item['variation_id'],
				$cart_item_price,
			);

			$cart_item['nrp_points'] = $item_points * $cart_item['quantity'];
			WC()->cart->cart_contents[$cart_item_id] = $cart_item;
		}
		WC()->cart->set_session();
	}

	/**
	 * Display points below cart item for debugging.
	 * 
	 * Hooked to: woocommerce_add_cart_item_data
	 */
	public function cart_display_item_points($item_data, $cart_item)
	{
		if ((get_option('nrp_debug') == 1) && (isset($cart_item['nrp_points']))) {
			$item_data[] = array(
				'key'   => esc_html(nrp_points_label()),
				'value' => esc_html(nrp_format_points($cart_item['nrp_points']))
			);
		}

		return $item_data;
	}

	/**
	 * Add points meta to order item.
	 * 
	 * Hooked to: woocommerce_checkout_create_order_line_item
	 */
	public function order_add_item_points_meta($item, $cart_item_key, $values, $order)
	{
		if (empty($values['nrp_points']))
			return;

		$item->add_meta_data('_nrp_points', $values['nrp_points']);
		$item->add_meta_data('_nrp_quantity', $values['quantity']);
	}

	/**
	 * Add points data to order.
	 * 
	 * Hooked to: woocommerce_checkout_create_order
	 */
	public function order_add_total_points_meta($order, $data)
	{
		$total_points_earned = 0;

		foreach ($order->get_items() as $item_id => $item) {
			if (!empty($item->get_meta('_nrp_points', true))) {
				$total_points_earned += $item->get_meta('_nrp_points', true);
			}
		}

		$order->update_meta_data('_nrp_version', array('pro' => false, 'version' => NUJO_REWARD_POINTS_VERSION));
		$order->update_meta_data('_nrp_points_total', $total_points_earned);
		$order->update_meta_data('_nrp_points_cancelled', 0);

		if ($order->get_user() === false) {
			$order->update_meta_data('_nrp_has_account', false);
		} else {
			$order->update_meta_data('_nrp_has_account', true);
		}
	}

	/**
	 * Add points to customer on order status change.
	 * 
	 * Hooked to: woocommerce_order_status_changed
	 */
	public function order_add_points_to_customer($order_id, $status_from, $status_to, $instance)
	{
		if (get_option('nrp_assign_points_status') == 'completed') {
			// Only proceed if changed to completed status
			if ($status_to != 'completed')
				return;
		} else {
			// Only proceed if changed to paid status
			if (!in_array($status_to, wc_get_is_paid_statuses()))
				return;
		}

		$order = wc_get_order($order_id);
		$points_to_add = $order->get_meta('_nrp_points_total', true);
		$is_processed = $order->get_meta('_nrp_is_processed', true);
		$has_account = $order->get_meta('_nrp_has_account', true);

		if (($is_processed == false) && ($has_account == true)) {

			$account = new Nujo_Reward_Points_Account($order->get_customer_id());

			if ($points_to_add > 0) {

				$account->add_order_points($order_id, $points_to_add);

				$order->update_meta_data('_nrp_is_processed', true);

				if (absint($order->get_meta('_nrp_points_added', true)) > 0) {
					$total_points_added = $order->get_meta('_nrp_points_added', true) + $points_to_add;
					$order->update_meta_data('_nrp_points_added', $total_points_added);
				} else {
					$order->update_meta_data('_nrp_points_added', $points_to_add);
				}

				$order->save();

				$note = sprintf(__('Customer awarded %1$s %2$s for this order.', 'easy-loyalty-points-and-rewards-for-woocommerce'), nrp_format_points($points_to_add), nrp_points_label(true));
				$order->add_order_note($note);
			}
		}
	}

	/**
	 * Deduct redeemed points from customer balance once order is processed.
	 * 
	 * Hooked to: woocommerce_checkout_order_processed
	 */
	public function order_deduct_redemption_coupon_points($order_id)
	{
		$order = wc_get_order($order_id);
		$coupons = $order->get_coupon_codes();

		$total_pointed_redeemed = 0;

		foreach ($coupons as $coupon) {

			if (Nujo_Reward_Points_Redemption_Coupon::is_format($coupon)) {

				$points_to_deduct = Nujo_Reward_Points_Redemption_Coupon::get_points_amount($coupon);

				try {
					$account = new Nujo_Reward_Points_Account($order->get_customer_id());
					$account->deduct_redemption_coupon($order_id, $points_to_deduct);

					$note = sprintf(__('Customer redeemed %1$s %2$s for a %3$s discount.', 'easy-loyalty-points-and-rewards-for-woocommerce'), nrp_format_points($points_to_deduct), nrp_points_label(true), wc_price(nrp_get_points_value($points_to_deduct)));
					$order->add_order_note($note);

					$total_pointed_redeemed += $points_to_deduct;
				} catch (Exception $e) {
					error_log("Nujo Rewards Points Error: could not deduct redemption coupon points from account (Order ID: $order_id)");
				}
			}
		}

		$order->update_meta_data('_nrp_points_redeemed', $total_pointed_redeemed);
		$order->save();
	}

	/**
	 * Create points account for customer. Accounts are created upon plugin activation
	 * and when new WP users subsequently register.
	 * 
	 * Hooked to: user_register
	 */
	public function user_create_points_account($user_id, $userdata)
	{
		Nujo_Reward_Points_Account::create_account($user_id);
	}

	/**
	 * Create points account email address synced with WP user.
	 * 
	 * Hooked to: profile_update
	 */
	public function user_update_points_account_email($user_id, $old_user_data)
	{
		$old_user_email = $old_user_data->data->user_email;

		$user = get_userdata($user_id);
		$new_user_email = $user->user_email;

		if ($new_user_email !== $old_user_email) {
			Nujo_Reward_Points_Account::sync_email($user_id);
		}
	}

	/**
	 * Set redemption coupon data.
	 * 
	 * Hooked to: woocommerce_get_shop_coupon_data
	 */
	public function cart_redeem_set_wc_coupon_data($false, $data, $coupon)
	{
		return Nujo_Reward_Points_Redemption_Coupon::set_wc_coupon_data($false, $data, $coupon);
	}

	/**
	 * Prevent more than one redemption coupon being used at a time.
	 * 
	 * Hooked to: woocommerce_before_calculate_totals
	 */
	public function cart_redeem_prevent_multiple_coupons(WC_Cart $cart)
	{
		Nujo_Reward_Points_Redemption_Coupon::remove_multiple_coupons($cart);
	}

	/**
	 * Customise redemption coupon label.
	 * 
	 * Hooked to: woocommerce_cart_totals_coupon_label
	 */
	public function cart_redeem_coupon_label($label, $coupon)
	{
		if (Nujo_Reward_Points_Redemption_Coupon::is_format($coupon->get_code())) {
			return esc_html(sprintf(__('Redeem %s', 'easy-loyalty-points-and-rewards-for-woocommerce'), nrp_points_label(true)));
		} else {
			return $label;
		}
	}

	/**
	 * Return array of notices for cart/checkout page.
	 */
	public function get_cart_notices()
	{
		// No notice if cart empty
		if (count(WC()->cart->cart_contents) == 0) {
			return;
		}

		$cart_points = nrp_get_cart_points();

		// Guest message
		if (!is_user_logged_in()) {

			$values = array(
				'points' => nrp_format_points($cart_points),
				'points_value' => strip_tags(wc_price(nrp_get_points_value($cart_points))),
				'points_label' => nrp_points_label(true)
			);

			$values = apply_filters('nrp_message_cart_guest_values', $values);

			return array(
				array(
					nrp_message(get_option('nrp_message_cart_guest'), $values),
					'notice'
				)
			);
		}

		$account = new Nujo_Reward_Points_Account(get_current_user_id(), true);
		$current_reward = $account->get_current_reward(nrp_get_cart_total());
		$currently_redeemed = Nujo_Reward_Points_Redemption_Coupon::get_cart_coupon_points_amount(WC()->cart);

		// Default notice
		$values = array(
			'points_balance' => nrp_format_points(nrp_get_current_user_points()),
			'points_balance_value' => strip_tags(wc_price(nrp_get_current_user_points())),
			'points' => nrp_format_points($cart_points),
			'points_value' => strip_tags(wc_price(nrp_get_points_value($cart_points))),
			'points_label' => nrp_points_label(true),
			'min_points_to_redeem' => nrp_format_points(nrp_get_min_points_to_redeem()),
		);

		$values = apply_filters('nrp_message_cart_complete_purchase_values', $values);

		$default_notice = array(
			nrp_message(get_option('nrp_message_cart_complete_purchase'), $values),
			'notice'
		);

		// Customer has already redeemed points
		if ($currently_redeemed !== false) {
			return array(
				$default_notice,
				array(sprintf(
					__('You have redeemed %1$s %2$s.', 'easy-loyalty-points-and-rewards-for-woocommerce'),
					nrp_format_points($currently_redeemed),
					nrp_points_label(true),
				), 'success'),
			);
		}

		// Customer has available reward
		if ($current_reward !== false) {

			$current_reward_value = strip_tags(wc_price(nrp_get_points_value($current_reward)));

			// Customer is under min spend
			if (nrp_get_cart_total() < get_option('nrp_min_redemption_order_value')) {

				$min_spend_remaining = strip_tags(wc_price((float) get_option('nrp_min_redemption_order_value') - nrp_get_cart_total()));

				$values = array(
					'points_balance' => nrp_format_points(nrp_get_current_user_points()),
					'points_balance_value' => strip_tags(wc_price(nrp_get_current_user_points())),
					'points' => nrp_format_points($cart_points),
					'points_value' => strip_tags(wc_price(nrp_get_points_value($cart_points))),
					'points_label' => nrp_points_label(true),
					'min_spend' => strip_tags(wc_price(get_option('nrp_min_redemption_order_value'))),
					'min_spend_remaining' => $min_spend_remaining,
					'reward_value' => $current_reward_value,
				);

				$values = apply_filters('nrp_message_cart_reward_min_spend_values', $values);

				return array(
					$default_notice,
					array(
						nrp_message(get_option('nrp_message_cart_reward_min_spend'), $values),
						'notice'
					)
				);
			} else { // Customer meets min spend

				$apply_discount_text = apply_filters('nrp_message_cart_apply_discount_button_text', get_option('nrp_message_cart_apply_discount_button', __('Click here to redeem', 'easy-loyalty-points-and-rewards-for-woocommerce')));
				$apply_discount_class = apply_filters('nrp_message_cart_apply_discount_button_class', '');
				$apply_discount_html = "<a href='#' class='nrp_apply_redemption_coupon $apply_discount_class' data-coupon='nrp_redeem_{$current_reward}_points'>$apply_discount_text</a>";

				$values = array(
					'points_balance' => nrp_format_points(nrp_get_current_user_points()),
					'points_balance_value' => strip_tags(wc_price(nrp_get_current_user_points())),
					// 'points' => nrp_format_points($cart_points),
					// 'points_value' => strip_tags(wc_price(nrp_get_points_value($cart_points))),
					'points_label' => nrp_points_label(true),
					'points_to_redeem' => nrp_format_points($current_reward),
					'reward_value' => $current_reward_value,
				);

				$values = apply_filters('nrp_message_cart_apply_discount_values', $values);

				return array(
					$default_notice,
					array(
						nrp_message(get_option('nrp_message_cart_apply_discount'), $values)  . ' ' . $apply_discount_html,
						'notice'
					)
				);
			}
		} else { // Customer under min points to redeem

			if ((nrp_get_min_points_to_redeem() > 1) && (nrp_get_current_user_points() > 0)) {

				$values = array(
					'points_balance' => nrp_format_points(nrp_get_current_user_points()),
					'points_balance_value' => strip_tags(wc_price(nrp_get_current_user_points())),
					'points' => nrp_format_points($cart_points),
					'points_value' => strip_tags(wc_price(nrp_get_points_value($cart_points))),
					'points_label' => nrp_points_label(true),
					'min_points_to_redeem' => nrp_format_points(nrp_get_min_points_to_redeem()),
				);

				$values = apply_filters('nrp_message_cart_reward_min_points_values', $values);

				return array(
					$default_notice,
					array(
						nrp_message(get_option('nrp_message_cart_reward_min_points'), $values),
						'notice'
					)
				);
			}
		}

		// Return just the default notice if no other notices
		return array($default_notice);
	}

	/**
	 * Print cart/checkout notices.
	 * 
	 * Hooked to: woocommerce_before_checkout_form, woocommerce_before_cart
	 */
	public function print_cart_notice()
	{
		$allowed_html = nrp_allowed_html();

		// Only show notice if plugin has been configured
		if (!empty(get_option('nrp_point_value'))) {

			$cart_notices = $this->get_cart_notices();

			if (!empty($cart_notices)) {
				foreach ($cart_notices as $cart_notice) :
					wc_add_notice(
						wp_kses($cart_notice[0], $allowed_html),
						esc_attr($cart_notice[1])
					);
				endforeach;
			}
		}
	}

	/**
	 * Register new endpoint URL for My Account page.
	 * 
	 * Hooked to: woocommerce_add_cart_item_data
	 */
	public function my_account_add_endpoint()
	{
		add_rewrite_endpoint('nrp-points', EP_ROOT | EP_PAGES);
	}

	/**
	 * Add new query var for My Account Page.
	 * 
	 * Hooked to: query_vars
	 */
	function my_account_query_vars($vars)
	{
		$vars[] = 'nrp-points';
		return $vars;
	}

	/**
	 * Add link for My Account page.
	 * 
	 * Hooked to: woocommerce_account_menu_items
	 */
	function my_account_add_link($original_tabs)
	{
		$new_tab['nrp-points'] = apply_filters('nrp_my_account_menu_item', nrp_points_label());

		$insert_at_position = apply_filters('nrp_my_account_menu_position', 2); // This can be changed
		$tabs = array_slice($original_tabs, 0, $insert_at_position, true); // First part of original tabs
		$tabs = array_merge($tabs, $new_tab); // Add new
		$tabs = array_merge($tabs, array_slice($original_tabs, $insert_at_position, null, true)); // Glue the second part of original

		return $tabs;
	}

	/**
	 * Display My Account page.
	 * 
	 * Hooked to: woocommerce_account_menu_items
	 */
	function my_account_display_content()
	{
		global $wpdb;
		$account = new Nujo_Reward_Points_Account(get_current_user_id());
		$account->calculate_and_update_points();
		require_once 'partials/' . $this->plugin_name . '-public-my-account.php';
	}
}
