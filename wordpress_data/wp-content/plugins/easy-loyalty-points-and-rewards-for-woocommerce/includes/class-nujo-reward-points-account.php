<?php

class Nujo_Reward_Points_Account
{
    const PROCESS_DEDUCT = 0;
    const PROCESS_ADD = 1;

    private $account_id;
    private $customer_id;
    private $points_balance;

    /**
     * Convert customer_id to account_id.
     */
    public static function get_account_id_from_customer_id($customer_id)
    {
        global $wpdb;

        $table_name = $wpdb->prefix . 'nrp_accounts';

        $row = $wpdb->get_row($wpdb->prepare(
            "SELECT account_id FROM $table_name WHERE customer_id = %d LIMIT 1",
            array(
                $customer_id
            )
        ));

        if (!empty($row)) {
            return $row->account_id;
        } else {
            return false;
        }
    }

    /**
     * Create points account for customer.
     */
    public static function create_account($user)
    {
        global $wpdb;

        // Check if passed a WP_User object or a user_id
        if (!is_object($user)) {
            $user = get_user_by('ID', $user);
        }

        $date_created_local = current_time('mysql');
        $date_created_gmt = current_time('mysql', 1);

        $table_name = $wpdb->prefix . 'nrp_accounts';

        // Check no account exists before creating
        if (self::get_account_id_from_customer_id($user->ID) === false) {

            $wpdb->insert(
                $table_name,
                array(
                    'customer_id'        => $user->ID,
                    'email'                => $user->get('user_email'),
                    'date_created_gmt'  => $date_created_gmt,
                    'date_created_local'  => $date_created_local,
                )
            );

            return $wpdb->insert_id;
        } else {
            self::sync_email($user->ID);
            return false;
        }
    }

    /**
     * Sync account and user email.
     */
    public static function sync_email($user_id)
    {
        global $wpdb;

        $user = get_userdata($user_id);
        $table_name = $wpdb->prefix . 'nrp_accounts';
        $wpdb->update($table_name, array('email' => $user->user_email), array('customer_id' => $user_id));
    }

    /**
     * Constructor.
     */
    public function __construct($id, $is_customer_id = true)
    {
        global $wpdb;

        $table_name = $wpdb->prefix . 'nrp_accounts';

        if ($is_customer_id) {
            $row = $wpdb->get_row($wpdb->prepare(
                "SELECT * FROM $table_name WHERE customer_id = %d LIMIT 1",
                array(
                    $id
                )
            ));
        } else {
            $row = $wpdb->get_row($wpdb->prepare(
                "SELECT * FROM $table_name WHERE account_id = %d LIMIT 1",
                array(
                    $id
                )
            ));
        }

        if (!empty($row)) {
            $this->account_id       = $row->account_id;
            $this->customer_id      = $row->customer_id;
            $this->points_balance   = null;
        } else {
            error_log("Nujo Reward Points Error: account not found");
            throw new Exception(__('Account not found', 'easy-loyalty-points-and-rewards-for-woocommerce'));
        }
    }

    /**
     * Get customer ID.
     */
    public function get_customer_id()
    {
        return $this->customer_id;
    }

    /**
     * Get points account ID.
     */
    public function get_account_id()
    {
        return $this->account_id;
    }

    /**
     * Get customer account balance.
     */
    public function get_points_balance()
    {
        if ($this->points_balance === null) {
            $this->calculate_and_update_points();
        }

        return $this->points_balance;
    }

    /**
     * Manually add/deduct points to account.
     */
    public function set_balance_manually($new_balance)
    {
        $event_type = 'manual';

        $data = array(
            'staff_id' => get_current_user_id(),
            'staff_username' => wp_get_current_user()->user_login
        );

        $current_balance = $this->get_points_balance();

        if ($new_balance > $current_balance) {
            $points_to_add = $new_balance - $current_balance;
            return $this->add_points($event_type, $points_to_add, null, $data);
        } elseif ($new_balance < $current_balance) {
            $points_to_deduct = $current_balance - $new_balance;
            return $this->deduct_points($event_type, $points_to_deduct, null, $data);
        }
    }

    /**
     * Add order points to account.
     */
    public function add_order_points($order_id, $points_to_add)
    {
        $event_type = 'order';
        $data = null;

        $this->add_points($event_type, $points_to_add, $order_id, $data);
    }

    /**
     * Add points to account.
     */
    public function add_points($event_type, $points_to_add, $order_id, $data)
    {
        global $wpdb;

        $points_to_add = absint($points_to_add);

        $date_created_local = current_time('mysql');
        $date_created_gmt = current_time('mysql', 1);

        $account_balance = $this->get_points_balance() + $points_to_add;

        if (!empty($data))
            $data = json_encode($data);

        $table_name = $wpdb->prefix . 'nrp_points_log';

        $wpdb->insert(
            $table_name,
            array(
                'event_type'            => $event_type,
                'account_id'            => $this->account_id,
                'process'               => self::PROCESS_ADD,
                'amount'                => $points_to_add,
                'amount_available'      => $points_to_add,
                'account_balance'       => $account_balance,
                'order_id'              => $order_id,
                'data'                  => $data,
                'expired'               => 0,
                'reminder_sent'         => 0,
                'date_created_gmt'      => $date_created_gmt,
                'date_created_local'    => $date_created_local,
            )
        );

        $this->calculate_and_update_points();
        $this->update_date_last_activity();
    }

    /**
     * Deduct points from redemption coupon
     */
    public function deduct_redemption_coupon($order_id, $points_to_deduct)
    {
        $event_type = 'order-redemption';
        $data = null;

        $this->deduct_points($event_type, $points_to_deduct, $order_id, $data);
    }

    /**
     * Deduct points from to account
     */
    private function deduct_points($event_type, $amount, $order_id, $data)
    {
        global $wpdb;

        $account_balance = $this->get_points_balance() - $amount;

        $log_table_name = $wpdb->prefix . 'nrp_points_log';

        $rows = $wpdb->get_results(
            $wpdb->prepare(
                "SELECT points_log_id, amount_available FROM $log_table_name WHERE process = %d AND account_id = %d AND expired = 0 AND amount_available > 0 ORDER BY points_log_id",
                array(
                    self::PROCESS_ADD,
                    $this->account_id,
                )
            ),
            ARRAY_A
        );

        // Map storing amount_redeemed against id
        $amount_to_redeem = $amount;
        $amount_redeemed_map = array();
        $amount_redeemed_map_debug = array();

        foreach ($rows as $row) {

            // Calculate the amount that can be used against a specific credit
            // It will be the minimum of available credit and amount left to redeem
            $amount_redeemed  = min($row['amount_available'], $amount_to_redeem);

            // Populate the map
            $amount_redeemed_map[$row['points_log_id']] = $row['amount_available'] - $amount_redeemed;
            $amount_redeemed_map_debug[$row['points_log_id']] = $amount_redeemed;

            // Adjust the amount_to_redeem
            $amount_to_redeem -= $amount_redeemed;

            // If no more amount_to_redeem, we can finish the loop
            if ($amount_to_redeem == 0) {
                break;
            } elseif ($amount_to_redeem < 0) {

                // This should never happen, still if it happens, throw error
                throw new Exception("Something wrong with logic!");
                exit();
            }
        }

        foreach ($amount_redeemed_map as $key => $value) {
            $wpdb->update($log_table_name, array('amount_available' => $value), array('points_log_id' => $key));
        }

        if (!empty($data))
            $data = json_encode($data);

        $date_created_local = current_time('mysql');
        $date_created_gmt = current_time('mysql', 1);

        $wpdb->insert(
            $log_table_name,
            array(
                'event_type'            => $event_type,
                'account_id'            => $this->account_id,
                'process'               => self::PROCESS_DEDUCT,
                'amount'                => $amount,
                'amount_available'      => 0,
                'account_balance'       => $account_balance,
                'order_id'              => $order_id,
                'data'                  => $data,
                'deduct_map'            => json_encode($amount_redeemed_map_debug),
                'expired'               => 0,
                'date_created_gmt'      => $date_created_gmt,
                'date_created_local'    => $date_created_local,
            )
        );

        $this->calculate_and_update_points();
        $this->update_date_last_activity();
    }

    /**
     * Calculate and update points balance.
     */
    public function calculate_and_update_points()
    {
        global $wpdb;

        $log_table_name = $wpdb->prefix . 'nrp_points_log';
        $account_table_name = $wpdb->prefix . 'nrp_accounts';

        $current_time = current_time('mysql', 1);

        $row = $wpdb->get_row($wpdb->prepare(
            "SELECT SUM(amount_available) AS total_points_available FROM $log_table_name WHERE process = %d AND account_id = %d AND expired = 0",
            array(
                self::PROCESS_ADD,
                $this->account_id,
            )
        ));

        $this->points_balance = $row->total_points_available;

        $wpdb->update($account_table_name, array('points_balance' => $this->points_balance), array('account_id' => $this->account_id));

        // Update user meta
        if (!empty($this->customer_id)) {
            update_user_meta($this->customer_id, '_nrp_points', $this->points_balance);
        }
    }

    /**
     * Update date of last points activity (not including points expiry).
     */
    public function update_date_last_activity()
    {
        global $wpdb;

        $table_name = $wpdb->prefix . 'nrp_accounts';

        $current_time_local = current_time('mysql');
        $current_time_gmt = current_time('mysql', 1);

        $wpdb->update($table_name, array('date_last_activity_gmt' => $current_time_gmt, 'date_last_activity_local' => $current_time_local), array('account_id' => $this->account_id));
    }

    /**
     * Get the current reward available to customer.
     */
    public function get_current_reward($cart_total = null)
    {
        $min_points = get_option('nrp_min_redemption_points');
        $interval = get_option('nrp_redemption_increment');

        if ($this->get_points_balance() < max($min_points, $interval))
            return false;

        if ($cart_total === null) {
            $reward_points = max($min_points, $interval);

        } else {
            $reward_points = $min_points;
            $next_reward_points = 0;
            $next_reward_points_value = 0;
            $finished = false;

            while (!$finished) {

                $next_reward_points += $interval;
                $next_reward_points_value = nrp_get_points_value($next_reward_points);

                if (($next_reward_points_value <= $cart_total) && ($next_reward_points <= $this->get_points_balance())) {
                    $reward_points = $next_reward_points;
                } else {
                    $finished = true;
                }
            }
        }

        return $reward_points;
    }

}
