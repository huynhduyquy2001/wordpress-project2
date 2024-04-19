<?php

class Nujo_Reward_Points_Form
{

    /**
	 * Get form field label for id
	 */
    public static function get_field_label($id)
    {
        // Form field labels
        $fields = array(
            'nrp_earning_ratio'             => __('Earning Points', 'easy-loyalty-points-and-rewards-for-woocommerce'),
            'nrp_redemption_ratio'          => __('Redeeming Points', 'easy-loyalty-points-and-rewards-for-woocommerce'),
            'nrp_points_label'              => __('Points Label', 'easy-loyalty-points-and-rewards-for-woocommerce'),
            'nrp_min_redemption_order_value' => __('Min Order Value to Redeem', 'easy-loyalty-points-and-rewards-for-woocommerce'),
            'nrp_min_redemption_points'     => __('Min Points to Redeem', 'easy-loyalty-points-and-rewards-for-woocommerce'),
            'nrp_assign_points_status'      => __('Assign Points When', 'easy-loyalty-points-and-rewards-for-woocommerce'),
            'nrp_tax_mode'                  => __('Tax Mode', 'easy-loyalty-points-and-rewards-for-woocommerce'),
            'nrp_message_single_product'    => __('Single Product Message', 'easy-loyalty-points-and-rewards-for-woocommerce'),
            'nrp_message_variable_product'  => __('Variable Product Message', 'easy-loyalty-points-and-rewards-for-woocommerce'),
            'nrp_message_cart_guest'        => __('Default Message (Guest)', 'easy-loyalty-points-and-rewards-for-woocommerce'),
            'nrp_message_cart_reward_min_spend' => __('Under Min Spend for Reward', 'easy-loyalty-points-and-rewards-for-woocommerce'),
            'nrp_message_cart_reward_min_points' => __('Under Min Points for Reward', 'easy-loyalty-points-and-rewards-for-woocommerce'),
            'nrp_message_cart_apply_discount' => __('Redeem Points for Reward', 'easy-loyalty-points-and-rewards-for-woocommerce'),
            'nrp_message_cart_apply_discount_button' => __('Redeem Button Text', 'easy-loyalty-points-and-rewards-for-woocommerce'),
            'nrp_message_cart_complete_purchase' => __('Default Message (Logged In)', 'easy-loyalty-points-and-rewards-for-woocommerce'),
            'nrp_coupon_calculation_mode'   => __('Calculation Mode', 'easy-loyalty-points-and-rewards-for-woocommerce'),
            'nrp_rounding_mode'             => __('Point Rounding Mode', 'easy-loyalty-points-and-rewards-for-woocommerce'),
            'nrp_uninstall_delete_settings' => __('Delete Settings On Uninstall?', 'easy-loyalty-points-and-rewards-for-woocommerce'),
            'nrp_uninstall_delete_points_data' => __('Delete Points Data On Uninstall?', 'easy-loyalty-points-and-rewards-for-woocommerce'),
            'nrp_debug'                     => __('Debug Mode?', 'easy-loyalty-points-and-rewards-for-woocommerce'),
            'nrp_pro_expires'                   => __('Points Expire After', 'easy-loyalty-points-and-rewards-for-woocommerce'),
            'nrp_pro_action_reward_account_signup' => __('Account Signup Reward', 'easy-loyalty-points-and-rewards-for-woocommerce'),
            'nrp_pro_action_reward_first_order' => __('First Order Reward', 'easy-loyalty-points-and-rewards-for-woocommerce'),
            'nrp_pro_license_key'               => __('License Key', 'easy-loyalty-points-and-rewards-for-woocommerce'),
        );

        return $fields[$id];
    }

    /**
	 * Add WordPress settings error
	 */
    private static function add_settings_error($id, $error)
    {
        add_settings_error(esc_attr($id), esc_attr($id), esc_html($error));
    }

    /**
	 * Return if value is whole number
	 */
    private static function is_integer($value)
    {
        if (!is_numeric($value) || (fmod($value, 1) !== 0.00) || ($value < 0)) {
            return false;
        } else {
            return true;
        }
    }

    /**
	 * Return if value is monetary
	 */
    private static function is_monetary($value)
    {
        $value = str_replace(wc_get_price_decimal_separator(), '.', $value);

        if (!is_numeric($value) || ($value < 0)) {
            return false;
        } else {
            return true;
        }
    }

    /**
	 * Validation rule for ratio fields
	 */
    private static function rule_ratio($id, $value)
    {
        $points             = sanitize_text_field($value['points']);
        $monetary_amount    = sanitize_text_field($value['value']);

        $is_valid = true;

        if ((strlen($points) == 0) || (strlen($monetary_amount) == 0)) {

            $is_valid = false;
            self::add_settings_error($id, sprintf(__('"%s" is required.', 'easy-loyalty-points-and-rewards-for-woocommerce', self::get_field_label($id))));
        } else {

            if (self::is_integer($points) == false) {
                $is_valid = false;
                self::add_settings_error($id, sprintf(__('"%s" points amount must be a whole number with no comma or decimal.', 'easy-loyalty-points-and-rewards-for-woocommerce'), self::get_field_label($id)));
            }

            if (self::is_monetary($monetary_amount) == false) {
                $is_valid = false;
                self::add_settings_error($id, sprintf(__('"%1$s" monetary value must be entered with one monetary decimal point (%2$s) and no currency symbol or thousands seperator.', 'easy-loyalty-points-and-rewards-for-woocommerce'), self::get_field_label($id), wc_get_price_decimal_separator()));
            }
        }

        if ($is_valid) {

            if (empty($points))
                $points = '0';

            if (empty($monetary_amount))
                $monetary_amount = '0';

            return array('points' => (int) $points, 'value' => $monetary_amount);
        } else {

            return get_option($id);
        }
    }

    /**
	 * Validation rule for text fields
	 */
    private static function rule_textfield($id, $value, $required = false)
    {
        $value = sanitize_text_field($value);

        if ($required && empty($value)) {
            $value = get_option($id);
            self::add_settings_error($id, sprintf(__('"%s" is required.', 'easy-loyalty-points-and-rewards-for-woocommerce'), self::get_field_label($id)));
        }

        return $value;
    }

    /**
	 * Validation rule for monetary fields
	 */
    private static function rule_monetary($id, $value)
    {
        $value = sanitize_text_field($value);

        if (empty($value)) {
            $value = 0;
        } elseif (self::is_monetary($value) == false) {
            $value = get_option($id);
            self::add_settings_error($id, sprintf(__('"%1$s" must be entered with one monetary decimal point (%2$s) and no currency symbol or thousands seperator.', 'easy-loyalty-points-and-rewards-for-woocommerce'), self::get_field_label($id), wc_get_price_decimal_separator()));
        }

        return $value;
    }

    /**
	 * Validation rule for whole number fields
	 */
    private static function rule_integer($id, $value)
    {
        $value = sanitize_text_field($value);

        if (empty($value)) {
            $value = 0;
        } elseif (self::is_integer($value) == false) {
            $value = get_option($id);
            self::add_settings_error($id, sprintf(__('"%s" must be a whole number with no comma or decimal.', 'easy-loyalty-points-and-rewards-for-woocommerce'), self::get_field_label($id)));
        }

        return intval($value);
    }

    public function validate_earning_ratio($value)
    {
        return self::rule_ratio('nrp_earning_ratio', $value);
    }

    public function validate_redemption_ratio($value)
    {
        return self::rule_ratio('nrp_redemption_ratio', $value);
    }

    public function validate_points_label($value)
    {
        return self::rule_textfield('nrp_points_label', $value, true);
    }

    public function validate_min_redemption_order_value($value)
    {
        return self::rule_monetary('nrp_min_redemption_order_value', $value);
    }

    public function validate_min_redemption_points($value)
    {
        return self::rule_integer('nrp_min_redemption_points', $value);
    }

    public function validate_cart_apply_discount_button($value)
    {
        return self::rule_textfield('nrp_message_cart_apply_discount_button', $value, true);
    }
}
