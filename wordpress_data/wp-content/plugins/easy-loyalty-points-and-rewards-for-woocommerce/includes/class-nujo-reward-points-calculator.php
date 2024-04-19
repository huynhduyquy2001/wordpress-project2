<?php

class Nujo_Reward_Points_Calculator
{

    /**
     * Get points for variable product.
     */
	public static function get_variable_product_points($product)
	{
		$max_points = 0;

		// Get WC_Product object if product_id passed
		if (!is_object($product)) {
			$product = wc_get_product($product);

		}

		$variations = $product->get_available_variations();

		foreach ($variations as $key => $value) {

			$variation_points = self::get_product_points($product, absint($value['variation_id']));

			if ($variation_points > $max_points)
				$max_points = $variation_points;
		}

		return $max_points;
	}

    /**
     * Get points for a product.
     */
	public static function get_product_points($product, $variation, $cart_price = null)
	{
		$points_per_unit = get_option('nrp_points_per_unit');

		$tax_mode = get_option('nrp_tax_mode');

		// Get WC_Product object if product_id passed
		if (!is_object($product)) {
			$product = wc_get_product($product);
		}

		// Check product type is simple or variable
		if ((!$product->is_type('simple')) && (!$product->is_type('variable'))) {
			return 0;
		}

		if ($cart_price === null) {

			if (!empty($variation)) {

				// Get WC_Product object if variation_id passed
				if (!is_object($variation)) {
					$variation = wc_get_product($variation);
				}

				if ($tax_mode == 'excl') {
					$product_price = wc_get_price_excluding_tax($variation);
				} else {
					$product_price = wc_get_price_including_tax($variation);
				}
			} else {

				if ($tax_mode == 'excl') {
					$product_price = wc_get_price_excluding_tax($product);
				} else {
					$product_price = wc_get_price_including_tax($product);
				}
			}

			$price_for_calculation = $product_price;
		} else {
			$price_for_calculation = $cart_price;
		}

		return self::round_points($price_for_calculation * $points_per_unit);
		
	}

    /**
     * Round points according to setting.
     */
	private static function round_points($points)
	{
		switch (get_option('nrp_rounding_mode')) {
			case 'up':
				return ceil($points);
				break;

			case 'down':
				return floor($points);
				break;

			default:
				return round($points);
				break;
		}
	}
}
