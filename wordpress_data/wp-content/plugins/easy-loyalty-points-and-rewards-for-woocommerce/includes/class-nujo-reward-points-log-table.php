<?php

class Nujo_Reward_Points_Log_Table extends Nujo_Reward_Points_WP_List_Table
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

        $table_name = $wpdb->prefix . 'nrp_points_log';

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
            // 'points_log_id' => __('Log #', 'easy-loyalty-points-and-rewards-for-woocommerce'),
            'email' => __('Customer', 'easy-loyalty-points-and-rewards-for-woocommerce'),
            'amount' => __('Points', 'easy-loyalty-points-and-rewards-for-woocommerce'),
            'description' => __('Description', 'easy-loyalty-points-and-rewards-for-woocommerce'),
            'order_id' => __('Order', 'easy-loyalty-points-and-rewards-for-woocommerce'),
            'date_created_local' => __('Date', 'easy-loyalty-points-and-rewards-for-woocommerce'),
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
        return array(
            'points_log_id' => array('points_log_id', false),
            'date_created_local' => array('date_created_local', false),
            'email' => array('email', false),
            'order_id' => array('order_id', false),

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

        $log_table_name = $wpdb->prefix . 'nrp_points_log';
        $account_table_name = $wpdb->prefix . 'nrp_accounts';
        $columns = "$log_table_name.points_log_id, $log_table_name.event_type, $log_table_name.account_id, $log_table_name.process, $log_table_name.amount, $log_table_name.amount_available, $log_table_name.order_id, $log_table_name.data, $log_table_name.date_created_gmt, $log_table_name.date_created_local, $log_table_name.date_expires_gmt, $log_table_name.date_expires_local, $account_table_name.email";

        if (!empty($_REQUEST['s'])) {
            $search_term = str_replace('#', '', sanitize_text_field($_REQUEST['s']));
            $search_query = $wpdb->prepare(' WHERE email = %s OR order_id = %s', $search_term, $search_term);
        } else {
            $search_query = '';
        }

        if (!empty($_REQUEST['orderby'])) {
            $order = (!empty($_REQUEST['order'])) ? ' ' . sanitize_key($_REQUEST['order']) : '';
            $order_by = sanitize_sql_orderby($_REQUEST['orderby'] . $order);
        } else {
            $order_by = 'points_log_id DESC';
        }

        $result = $wpdb->get_results(
            $wpdb->prepare(
                "SELECT $columns FROM $log_table_name LEFT JOIN $account_table_name ON $log_table_name.account_id = $account_table_name.account_id{$search_query} ORDER BY {$order_by} LIMIT %d OFFSET %d",
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
            case 'points_log_id':
                return $item[$column_name];
            case 'date_created_local':
                return esc_html(date_i18n('F j, Y g:i a', strtotime($item['date_created_local'])));
            case 'amount':
                return '<div style="width: 100px; display: inline; font-weight: 800;">' . (($item['process'] == Nujo_Reward_Points_Account::PROCESS_DEDUCT) ? '<span class="dashicons dashicons-minus"></span> ' : '<span class="dashicons dashicons-plus-alt2"></span> ') . '</div><strong>' . esc_html($item['amount']) . '</strong>';
            case 'description':
                return $this->description_column($item);
            case 'order_id':
                return $this->order_id_column($item['order_id']);
            default:
                return print_r($item, true);
        }
    }

    private function email_column($email)
    {
        $url = add_query_arg(
            array(
                'page' => 'nujo-reward-points-settings',
                'tab' => 'balances',
                's' => urlencode($email),
            ),
            get_admin_url() . 'admin.php'
        );

        return sprintf('<a href="%s">%s</a>', esc_url($url), esc_html($email));
    }

    private function description_column($item)
    {
        return wp_kses(nrp_event_description($item, 'admin'), nrp_allowed_html());
    }

    private function order_id_column($order_id)
    {
        if (!empty($order_id)) {

            $url = add_query_arg(
                array(
                    'post' => $order_id,
                    'action' => 'edit',
                ),
                get_admin_url() . 'post.php'
            );

            $order_id = _x('#', 'hash before order number', 'woocommerce') . $order_id;

            return sprintf('<a href="%s">%s</a>', esc_url($url), esc_html($order_id));
        } else {
            return '—';
        }
    }
}
