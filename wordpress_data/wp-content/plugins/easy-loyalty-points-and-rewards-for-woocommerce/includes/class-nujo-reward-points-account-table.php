<?php

class Nujo_Reward_Points_Account_Table extends Nujo_Reward_Points_WP_List_Table
{

    public function prepare_items()
    {
        $columns = $this->get_columns();
        $hidden = $this->get_hidden_columns();
        $sortable = $this->get_sortable_columns();

        $per_page = 20;
        $current_page = $this->get_pagenum();
        $total_items = self::record_count();

        $this->set_pagination_args([
            'total_items' => $total_items, //we have to calculate the total number of items
            'per_page' => $per_page //we have to determine how many items to show on a page
        ]);

        $this->_column_headers = array($columns, $hidden, $sortable);
        $this->items = $this->table_data($per_page, $current_page);
    }


    public static function record_count()
    {
        global $wpdb;

        $table_name = $wpdb->prefix . 'nrp_accounts';

        return $wpdb->get_var("SELECT COUNT(*) FROM {$table_name}");
    }

    /**
     * Override the parent columns method. Defines the columns to use in your listing table
     *
     * @return Array
     */
    public function get_columns()
    {
        $columns = array(
            'email' => __('Customer', 'easy-loyalty-points-and-rewards-for-woocommerce'),
            'points_balance' => __('Points Balance', 'easy-loyalty-points-and-rewards-for-woocommerce'),
            // 'points_pending' => __('Points Pending', 'easy-loyalty-points-and-rewards-for-woocommerce'),
            // 'total_points_earned' => __('Total Points Earned', 'easy-loyalty-points-and-rewards-for-woocommerce'),
            // 'total_points_redeemed' => __('Total Points Redeemed', 'easy-loyalty-points-and-rewards-for-woocommerce'),
            'date_last_activity_local' => __('Last Activity', 'easy-loyalty-points-and-rewards-for-woocommerce'),
            'view_log' => __('Points Log', 'easy-loyalty-points-and-rewards-for-woocommerce'),
            'adjust_points' => __('Update Balance', 'easy-loyalty-points-and-rewards-for-woocommerce'),
        );
        return $columns;
    }

    /**
     * Define which columns are hidden
     *
     * @return Array
     */
    public function get_hidden_columns()
    {
        return array();
    }

    /**
     * Define the sortable columns
     *
     * @return Array
     */
    public function get_sortable_columns()
    {
        // return array('title' => array('title', false));
        return array(
            'email' => array('email', false),
            'points_balance' => array('points_balance', false),
            'date_last_activity_local' => array('date_last_activity_local', false),
        );
    }

    /**
     * Get the table data
     *
     * @return Array
     */
    public static function table_data($per_page = 5, $page_number = 1)
    {
        global $wpdb;

        $table_name = $wpdb->prefix . 'nrp_accounts';

        if (!empty($_REQUEST['s'])) {
            $search_query = $wpdb->prepare(' WHERE email = %s', sanitize_text_field($_REQUEST['s']));
        } else {
            $search_query = '';
        }

        if (!empty($_REQUEST['orderby'])) {
            $order = (!empty($_REQUEST['order'])) ? ' ' . sanitize_key($_REQUEST['order']) : '';
            $order_by = sanitize_sql_orderby($_REQUEST['orderby'] . $order);
        } else {
            $order_by = 'email ASC';
        }

        $result = $wpdb->get_results(
            $wpdb->prepare(
                "SELECT * FROM {$table_name}{$search_query} ORDER BY $order_by LIMIT %d OFFSET %d",
                $per_page,
                ($page_number - 1) * $per_page,
            ),
            ARRAY_A
        );

        return $result;
    }

    /**
     * Define what data to show on each column of the table
     *
     * @param  Array $item        Data
     * @param  String $column_name - Current column name
     *
     * @return Mixed
     */
    public function column_default($item, $column_name)
    {
        switch ($column_name) {
            case 'email':
                return $this->email_column($item['email']);
            case 'points_pending':
            case 'total_points_earned':
            case 'total_points_redeemed':
                return esc_html(nrp_format_points($item[$column_name]));
            case 'date_last_activity_local':
                return empty($item['date_last_activity_local']) ? '—' : esc_html(date_i18n('F j, Y', strtotime($item['date_last_activity_local'])));
            case 'points_balance':
                return $this->points_balance_column($item['points_balance'], $item['account_id']);
            case 'adjust_points':
                return $this->adjust_points_column($item['account_id']);
            case 'view_log':
                return $this->view_log_column($item['email']);
            default:
                return print_r($item, true);
        }
    }

    private function email_column($email)
    {
        $url = add_query_arg(
            array(
                'page' => 'nujo-reward-points-settings',
                'tab' => 'log',
                's' => urlencode($email),
            ),
            get_admin_url() . 'admin.php'
        );

        return sprintf('<a href="%s">%s</a>', esc_url($url), esc_html($email));
    }

    private function view_log_column($email)
    {
        $url = add_query_arg(
            array(
                'page' => 'nujo-reward-points-settings',
                'tab' => 'log',
                's' => urlencode($email),
            ),
            get_admin_url() . 'admin.php'
        );

        return sprintf('<a href="%s">%s</a>', esc_url($url), esc_html__('View Points Log', 'easy-loyalty-points-and-rewards-for-woocommerce'));
    }

    private function points_balance_column($points_balance, $account_id)
    {
        return sprintf('<div id="nrp_points_balance_%d">%s<div>', esc_attr($account_id), esc_html($points_balance));
    }

    private function adjust_points_column($account_id)
    {
        return sprintf(
            '<form id="nrp_update_balance_form_%1$d" action="#" class="nrp_update_balance_form"><input type="hidden" name="account_id" value="%1$d"><input type="text" name="new_balance" placeholder="%2$s" size="10"> <input type="submit" name="points" name"submit" class="button" value="%3$s"></form>',
            esc_attr($account_id),
            esc_html__('New balance', 'easy-loyalty-points-and-rewards-for-woocommerce'),
            esc_html__('Update', 'easy-loyalty-points-and-rewards-for-woocommerce')
        );
    }
}
