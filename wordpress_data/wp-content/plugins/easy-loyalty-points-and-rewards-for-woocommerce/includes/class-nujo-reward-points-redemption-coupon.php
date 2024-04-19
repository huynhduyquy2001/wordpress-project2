<?php

class Nujo_Reward_Points_Redemption_Coupon
{
	private static $format_regex = '/^nrp_redeem_(\d+)_points$/';

    /**
     * Check if coupon is in redemption coupon format.
     */
	public static function is_format($data)
	{
		return preg_match(self::$format_regex, $data);
	}

    /**
     * Check if coupon is valid to be used.
     */
	public static function is_valid($data)
	{
		$is_valid = false;

		// Check user is logged in
		if (is_user_logged_in()) {

			// Check is correct format
			if (self::is_format($data)) {

				// Check user has sufficient points in account
				if (nrp_get_current_user_points() >= self::get_points_amount($data)) {

					// Check minimum point redemption
					if (nrp_get_min_points_to_redeem() <= self::get_points_amount($data)) {

						$is_valid = true;

					}
				}
			}
		}
		return $is_valid;
	}

    /**
     * Get points amount from coupon.
     */
	public static function get_points_amount($data)
	{
		preg_match(self::$format_regex, $data, $matches);
		return $matches[1];
	}

    /**
     * Override WC coupon data if valid coupon.
     */
	public static function set_wc_coupon_data($false, $data, $coupon)
	{
		if (self::is_valid($data)) {

			$points_amount = self::get_points_amount($data);
			$discount_amount = nrp_get_points_value($points_amount);

			$coupon->set_virtual(true);
			$coupon->set_discount_type('fixed_cart');
			$coupon->set_amount($discount_amount);
			// Set min spend to higher of min order value or the discount amount to prevent wasted points
			$coupon->set_minimum_amount(max(get_option('nrp_min_redemption_order_value', 0), $discount_amount));

			return $coupon;
		}
	}

    /**
     * Remove redemption coupons if more than one present.
     */
	public static function remove_multiple_coupons(WC_Cart $cart)
	{
		$redemption_count = 0;
		$safe_coupons = array();

		foreach ($cart->applied_coupons as $coupon) {
			if (self::is_format($coupon)) {
				$redemption_count++;
			} else {
				$safe_coupons[] = $coupon;
			}
		}

		if ($redemption_count > 1) {
			$cart->applied_coupons = $safe_coupons;
		}
	}

    /**
     * Get value of redemption coupon in cart.
     */
	public static function get_cart_coupon_points_amount(WC_Cart $cart)
	{
		foreach ($cart->applied_coupons as $coupon) {
			if (self::is_format($coupon)) {
				return self::get_points_amount($coupon);
			}
		}

		return false;
	}
}
