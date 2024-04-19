<?php

/**
 * Convert points into a monetary value.
 */
function nrp_get_points_value($points)
{
    return (float) round($points * (float) get_option('nrp_point_value'), 2);
}

/**
 * Get points balance of current user.
 */
function nrp_get_current_user_points()
{
    if (is_user_logged_in()) {
        $points = get_user_meta(get_current_user_id(), '_nrp_points', true);
        if (empty($points)) {
            $points = 0;
        }
    } else {
        $points = 0;
    }
    return $points;
}

/**
 * Get points label.
 */
function nrp_points_label($lowercase = false)
{
    $label = get_option('nrp_points_label', __('Points'));

    // Lowercase label if default label for language
    if (($lowercase) && ($label == __('Points'))) {
        return strtolower($label);
    } else {
        return $label;
    }
}

/**
 * Format points using localisation.
 */
function nrp_format_points($points, $process = null)
{
    $points = number_format_i18n($points);

    if ($process == null)
        $process = Nujo_Reward_Points_Account::PROCESS_ADD;

    if ($process == Nujo_Reward_Points_Account::PROCESS_DEDUCT) {
        return '-' . $points;
    } else {
        return $points;
    }
}

/**
 * Return friendly description of points event.
 */
function nrp_event_description($event, $view = 'customer')
{
    $data = json_decode($event['data'], true);
    $order_hash = _x('#', 'hash before order number', 'woocommerce');

    if (empty($data['staff_username']))
        $data['staff_username'] = '';

    switch ($event['event_type']) {
        case 'manual':
            if ($view == 'admin') {
                $event_description = sprintf(__('%1$s adjusted by %2$s', 'easy-loyalty-points-and-rewards-for-woocommerce'), nrp_points_label(), $data['staff_username']);
            } else {
                $event_description = sprintf(__('%1$s adjusted by staff', 'easy-loyalty-points-and-rewards-for-woocommerce'), nrp_points_label());
            }
            break;
        case 'order':
            $event_description = sprintf(__('%1$s earned for purchase', 'easy-loyalty-points-and-rewards-for-woocommerce'), nrp_points_label(), $order_hash . $event['order_id']);
            break;
        case 'order-redemption':
            $event_description = sprintf(__('%1$s redeemed towards purchase', 'easy-loyalty-points-and-rewards-for-woocommerce'), nrp_points_label(), $order_hash . $event['order_id']);
            break;
        case 'order-cancelled':
            $event_description = sprintf(__('%1$s adjusted for order refund', 'easy-loyalty-points-and-rewards-for-woocommerce'), nrp_points_label(), $order_hash . $event['order_id']);
            break;
        case 'account-signup':
            $event_description = sprintf(__('%1$s bonus for creating account', 'easy-loyalty-points-and-rewards-for-woocommerce'), nrp_points_label());
            break;
        case 'first-order':
            $event_description = sprintf(__('%1$s bonus for first order', 'easy-loyalty-points-and-rewards-for-woocommerce'), nrp_points_label());
            break;
        default:
            $event_description = __('Event description unavailable', 'easy-loyalty-points-and-rewards-for-woocommerce');
    }

    return $event_description;
}

/**
 * Replace message placeholders with array values.
 */
function nrp_message($message, $values = array())
{
    foreach ($values as $key => $value) :
        $message = str_replace('{' . $key . '}', $value, $message);
    endforeach;

    return $message;
}

/**
 * Get cart total.
 */
function nrp_get_cart_total()
{
    if (get_option('nrp_tax_mode') == 'excl') {
        return WC()->cart->get_cart_contents_total();
    } else {
        return WC()->cart->get_cart_contents_total() + WC()->cart->get_cart_contents_tax();
    }
}

/**
 * Get cart total including value of any points redemption.
 */
function nrp_get_cart_total_excl_redemption()
{
    $redemption_discount_points = Nujo_Reward_Points_Redemption_Coupon::get_cart_coupon_points_amount(WC()->cart);
    $redemption_discount_value = nrp_get_points_value($redemption_discount_points);

    if (get_option('nrp_tax_mode') == 'excl') {
        return WC()->cart->get_cart_contents_total() + $redemption_discount_value;
    } else {
        return WC()->cart->get_cart_contents_total() + WC()->cart->get_cart_contents_tax() + $redemption_discount_value;
    }
}

/**
 * Get total of all points in cart.
 */
function nrp_get_cart_points()
{
    $points = 0;
    $cart = WC()->cart->cart_contents;

    foreach ($cart as $cart_item_id => $cart_item) :

        if (!empty($cart_item['nrp_points']))
            $points += $cart_item['nrp_points'];

    endforeach;

    return $points;
}

/**
 * Get minimum amount of points that can be redeemed.
 */
function nrp_get_min_points_to_redeem()
{
    $min_points = get_option('nrp_min_redemption_points');
    $interval = get_option('nrp_redemption_increment');

    return max($min_points, $interval);
}

/**
 * An array of safe html elements.
 */
function nrp_allowed_html()
{
    $allowed_tags = array(
        'a' => array(
            'id'    => array(),
            'class' => array(),
            'href'  => array(),
            'rel'   => array(),
            'title' => array(),
            'target' => array(),
            'data-coupon' => array(),
        ),
        'abbr' => array(
            'title' => array(),
        ),
        'b' => array(),
        'blockquote' => array(
            'cite'  => array(),
        ),
        'cite' => array(
            'title' => array(),
        ),
        'code' => array(),
        'del' => array(
            'datetime' => array(),
            'title' => array(),
        ),
        'dd' => array(),
        'div' => array(
            'id'    => array(),
            'class' => array(),
            'title' => array(),
            'style' => array(),
        ),
        'dl' => array(),
        'dt' => array(),
        'em' => array(),
        'h1' => array(),
        'h2' => array(),
        'h3' => array(),
        'h4' => array(),
        'h5' => array(),
        'h6' => array(),
        'i' => array(),
        'img' => array(
            'alt'    => array(),
            'class'  => array(),
            'height' => array(),
            'src'    => array(),
            'width'  => array(),
        ),
        'li' => array(
            'class' => array(),
        ),
        'ol' => array(
            'class' => array(),
        ),
        'p' => array(
            'id'    => array(),
            'class' => array(),
        ),
        'q' => array(
            'cite' => array(),
            'title' => array(),
        ),
        'span' => array(
            'id'    => array(),
            'class' => array(),
            'title' => array(),
            'style' => array(),
        ),
        'strike' => array(),
        'strong' => array(),
        'ul' => array(
            'class' => array(),
        ),
    );

    return apply_filters('nrp_allowed_html', $allowed_tags);
}

/**
 * Additional strings for translation compatibility with Pro version
 */
function nrp_additional_strings() {
    $arr = array(
        __('Days', 'easy-loyalty-points-and-rewards-for-woocommerce'),
        __('Months', 'easy-loyalty-points-and-rewards-for-woocommerce'),
        __('Years', 'easy-loyalty-points-and-rewards-for-woocommerce'),
        __('Leave empty if points do not expire.', 'easy-loyalty-points-and-rewards-for-woocommerce'),
        __('Apply to existing points', 'easy-loyalty-points-and-rewards-for-woocommerce'),
        __('Update expiry date of all existing points', 'easy-loyalty-points-and-rewards-for-woocommerce'),
        __('License Key', 'easy-loyalty-points-and-rewards-for-woocommerce'),
        __('A valid license key is required for access to automatic plugin upgrades and product support.', 'easy-loyalty-points-and-rewards-for-woocommerce'),
        __('Please enter your <a href="%s">license key</a> to receive access to automatic upgrades and support.', 'easy-loyalty-points-and-rewards-for-woocommerce'),
        __('%1$s bonus for first order', 'easy-loyalty-points-and-rewards-for-woocommerce'),
        __('%1$s bonus for creating account', 'easy-loyalty-points-and-rewards-for-woocommerce'),
    );
    return $arr;
}