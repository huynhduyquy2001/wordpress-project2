<?php

class Nujo_Reward_Points_Message
{

    public static function get_message($msg)
    {
        switch ($msg) {
            case 'single_product':
                return __('Earn <strong>{points} {points_label}</strong> worth <strong>{points_value}</strong>', 'easy-loyalty-points-and-rewards-for-woocommerce');
                break;
            case 'variable_product':
                return __('Earn up to <strong>{points} {points_label}</strong> worth <strong>{points_value}</strong>', 'easy-loyalty-points-and-rewards-for-woocommerce');
                break;
            case 'cart_guest':
                return __('Login or create an account to earn {points} {points_label} worth {points_value} with this purchase.', 'easy-loyalty-points-and-rewards-for-woocommerce');
                break;
            case 'cart_reward_min_spend':
                return __('You have {points_balance} {points_label}. Spend another {min_spend_remaining} to redeem your {reward_value} discount.', 'easy-loyalty-points-and-rewards-for-woocommerce');
                break;
            case 'cart_reward_min_points':
                return __('You have {points_balance} {points_label}. Claim your reward once you reach {min_points_to_redeem}!', 'easy-loyalty-points-and-rewards-for-woocommerce');
                break;
            case 'cart_apply_discount':
                return __('You have {points_balance} {points_label}. You can redeem {points_to_redeem} for a {reward_value} discount!', 'easy-loyalty-points-and-rewards-for-woocommerce');
                break;
            case 'cart_complete_purchase':
                return __('Complete this purchase to earn {points} {points_label} worth {points_value}.', 'easy-loyalty-points-and-rewards-for-woocommerce');
                break;
            default:
                # code...
                break;
        }
    }
}
