<?php
/**
 * Plugin Name: Manage Customer Reward Points
 * Description: A plugin to manage customer reward points.
 * Version: 1.0
 * Author: Your Name
 */

// Thêm một trang vào menu admin
add_action('admin_menu', 'mcrp_add_menu');

function mcrp_add_menu()
{
    add_menu_page(
        'Manage Reward Points', // Tiêu đề của trang
        'Reward Points', // Tên trên menu
        'manage_options', // Quyền truy cập cần thiết để xem trang
        'mcrp_reward_points', // ID của trang
        'mcrp_render_page' // Hàm để hiển thị nội dung của trang
    );
}
// Hàm để hiển thị nội dung của trang
// Make sure the WP_List_Table is available
if (!class_exists('WP_List_Table')) {
    require_once (ABSPATH . 'wp-admin/includes/class-wp-list-table.php');
}

require 'vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class MyCRED_Points_History_Table extends WP_List_Table
{
    private $data;

    public function __construct($data)
    {
        parent::__construct([
            'singular' => 'Transaction',
            'plural' => 'Transactions',
            'ajax' => false,
        ]);
        $this->data = $data;
    }

    public function no_items()
    {
        echo 'No transactions found.';
    }

    function get_columns()
    {
        $columns = array(
            'user_id' => 'User ID',
            'points_added' => 'Points Added',
            'points_deducted' => 'Points Deducted',
            'history_balance' => 'History Balance',
            'description' => 'Description',
            'date' => 'Date'
        );
        return $columns;
    }


    function get_sortable_columns()
    {
        $sortable_columns = array(
            'user_id' => array('user_id', true),
            'points_added' => array('points_added', false),
            'points_deducted' => array('points_deducted', false),
            'history_balance' => array('history_balance', false),
            'date' => array('date', true) // Cột ngày có thể sắp xếp
        );
        return $sortable_columns;
    }




    protected function column_default($item, $column_name)
    {
        return isset($item[$column_name]) ? $item[$column_name] : 'No data';
    }

    public function prepare_items()
    {
        // Get search and date filter values
        $search = isset($_REQUEST['s']) ? sanitize_text_field($_REQUEST['s']) : '';
        $start_date = isset($_REQUEST['start_date']) ? sanitize_text_field($_REQUEST['start_date']) : '';
        $end_date = isset($_REQUEST['end_date']) ? sanitize_text_field($_REQUEST['end_date']) : '';

        // Filter data based on search and date
        $filtered_data = $this->filter_data($search, $start_date, $end_date);

        // Sorting
        $sortable_columns = $this->get_sortable_columns();
        $orderby = isset($_GET['orderby']) && isset($sortable_columns[$_GET['orderby']]) ? $sortable_columns[$_GET['orderby']][0] : 'date';
        $order = isset($_GET['order']) && in_array($_GET['order'], ['asc', 'desc']) ? $_GET['order'] : 'desc';

        usort($filtered_data, function ($a, $b) use ($orderby, $order) {
            // Convert date/time values to compare
            $date_a = strtotime($a[$orderby]);
            $date_b = strtotime($b[$orderby]);

            if ($date_a === false || $date_b === false) {
                // If cannot convert to date/time, use string comparison
                $result = strcmp($a[$orderby], $b[$orderby]);
            } else {
                // Compare by date/time
                $result = $date_a <=> $date_b;
            }

            return ($order === 'asc') ? $result : -$result;
        });

        // Pagination
        $total_items = count($filtered_data);
        $per_page = 10;
        $current_page = $this->get_pagenum();
        $this->set_pagination_args([
            'total_items' => $total_items,
            'per_page' => $per_page
        ]);
        $this->items = array_slice($filtered_data, ($current_page - 1) * $per_page, $per_page);

        // Set column headers
        $this->_column_headers = [
            $this->get_columns(),
            [],
            $sortable_columns
        ];
    }



    // Phương thức để lọc dữ liệu dựa trên các giá trị từ bộ lọc
    public function filter_data($search, $start_date, $end_date)
    {
        $filtered_data = array_filter($this->data, function ($item) use ($search, $start_date, $end_date) {
            $user_match = empty ($search) || stripos($item['user_id'], $search) !== false;

            // Check if the item's date is within the specified range
            $date_match = true;
            if (!empty ($start_date) && !empty ($end_date)) {
                $item_date = strtotime($item['date']);
                $start_date_timestamp = strtotime($start_date);
                $end_date_timestamp = strtotime($end_date . ' 23:59:59'); // Set end date to end of day
                $date_match = $item_date >= $start_date_timestamp && $item_date <= $end_date_timestamp;
            }

            return $user_match && $date_match;
        });

        return $filtered_data;
    }

    /**
     * Export filtered data to Excel file
     *
     * @param string $filename Filename for the Excel file
     * @param array $data Filtered data to be exported
     */
    function export_to_excel($filename, $data)
    {
        // Create new Spreadsheet object
        $spreadsheet = new Spreadsheet();

        // Set properties for the Excel file
        $spreadsheet->getProperties()->setCreator("Your Name")
            ->setLastModifiedBy("Your Name")
            ->setTitle("Reward History")
            ->setSubject("Reward History")
            ->setDescription("Reward history exported from website")
            ->setKeywords("reward history excel")
            ->setCategory("Export");

        // Add data to the Excel file
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setCellValue('A1', 'user_name');
        $sheet->setCellValue('B1', 'points_added');
        $sheet->setCellValue('C1', 'points_deducted');
        $sheet->setCellValue('D1', 'history_balance');
        $sheet->setCellValue('E1', 'description');
        $sheet->setCellValue('F1', 'date');

        $row = 2; // Start from row 2
        foreach ($data as $item) {
            $sheet->setCellValue('A' . $row, $item['user_id']);
            $sheet->setCellValue('B' . $row, $item['points_added']);
            $sheet->setCellValue('C' . $row, $item['points_deducted']);
            $sheet->setCellValue('D' . $row, $item['history_balance']);
            $sheet->setCellValue('E' . $row, $item['description']);
            $sheet->setCellValue('F' . $row, $item['date']);
            $row++;
        }

        // Create a writer for XLSX format
        $writer = new Xlsx($spreadsheet);

        // Save Excel file to the server
        $writer->save($filename);
    }



    public function search_box($text, $input_id)
    {
        $input_id = esc_attr($input_id . '-search-input');
        $value = isset($_REQUEST['s']) ? esc_attr($_REQUEST['s']) : '';
        $start_date_value = isset($_REQUEST['start_date']) ? esc_attr($_REQUEST['start_date']) : '';
        $end_date_value = isset($_REQUEST['end_date']) ? esc_attr($_REQUEST['end_date']) : '';

        echo '<form method="get">';
        echo '<input type="hidden" name="page" value="' . esc_attr($_REQUEST['page']) . '" />';
        echo '<p class="search-box">';
        echo '<label class="screen-reader-text" for="' . $input_id . '">' . $text . ':</label>';
        echo '<input type="search" id="' . $input_id . '" name="s" value="' . $value . '" placeholder="Search by name">';
        echo '<input type="date" id="' . $input_id . '-start-date" name="start_date" value="' . $start_date_value . '" style="margin-left: 10px;" placeholder="Start Date">';
        echo '<input type="date" id="' . $input_id . '-end-date" name="end_date" value="' . $end_date_value . '" style="margin-left: 10px;" placeholder="End Date">';
        echo '<input type="submit" id="search-submit" class="button" value="' . $text . '">';
        echo '</p>';
        echo '</form>';
    }




}


function mcrp_render_page()
{
    // Mặc định khi load trang là 'mycred_default'
    $ctype = isset($_GET['ctype']) ? $_GET['ctype'] : 'mycred_default';
    error_log('$ctype: ' . $ctype);

    // Kiểm tra giá trị của ctype, nếu là coins thì hiển thị lịch sử giao dịch của loại tiền coins
    if ($ctype == 'coins') {
        $args = [
            'number' => -1,
            'ctype' => 'coins'
        ];
    } else {
        // Nếu không, hiển thị lịch sử giao dịch của 'mycred_default'
        $args = [
            'number' => -1,
            'ctype' => 'mycred_default'
        ];
    }

    $log = new myCRED_Query_Log($args);
    $data = [];
    $running_balance = get_user_meta(get_current_user_id(), $ctype, true); // Sử dụng biến $ctype để xác định loại tiền

    foreach ($log->results as $entry) {
        $points_added = $entry->creds > 0 ? $entry->creds : '';
        $points_deducted = $entry->creds < 0 ? abs($entry->creds) : '';
        $temp = $running_balance;
        $running_balance -= $entry->creds;

        $user_id = $entry->user_id;
        $total_points = get_user_meta($user_id, 'mycred_total_points', true);
        // Lấy ID người dùng từ dữ liệu giao dịch

        // Lấy thông tin người dùng từ ID người dùng
        $user_data = get_userdata($user_id);

        // Kiểm tra xem liệu có thông tin người dùng hay không
        if ($user_data) {
            // Nếu có, lấy tên người dùng
            $user_name = $user_data->display_name;
        } else {
            // Nếu không, gán giá trị mặc định
            $user_name = 'Unknown';
        }

        // Gán giá trị của mycred_total_points cho $temp nếu tồn tại
        if ($total_points > 0) {
            $temp = $total_points;
        } else {
            $total_points = $temp;
        }

        $data[] = [
            'user_id' => $user_name,
            'points_added' => $points_added ? '+' . number_format($points_added, 0, ',', '.') : '',
            'points_deducted' => $points_deducted ? '-' . number_format($points_deducted, 0, ',', '.') : '',
            'history_balance' => number_format($total_points, 0, ',', '.'),
            'description' => $entry->entry,
            'date' => date("H:i:s Y-m-d", $entry->time)
        ];
    }

    $log->reset_query();

    // Display the table
    $table = new MyCRED_Points_History_Table($data);
    $table->prepare_items();
    ?>
    <div class="wrap">
        <h2>Bảng quản lý lịch sử giao dịch</h2>
        <div class="nav-tab-wrapper">
            <a href="<?php echo add_query_arg('ctype', 'mycred_default'); ?>"
                class="nav-tab <?php echo ($ctype == 'mycred_default') ? 'nav-tab-active' : ''; ?>">Hiện Points</a>
            <a href="<?php echo add_query_arg('ctype', 'coins'); ?>"
                class="nav-tab <?php echo ($ctype == 'coins') ? 'nav-tab-active' : ''; ?>">Hiện Coins</a>
        </div>
        Số dư hiện tại: <?php echo number_format(get_user_meta(get_current_user_id(), $ctype, true), 0, ',', '.'); ?><br>
        <?php $table->search_box('Tìm kiếm', 'user_id'); ?>
        <form method="post" action="">
            <input type="hidden" name="export_excel" value="1">
            <button type="submit" class="button">Xuất Excel</button>
        </form>
        <?php $table->display(); ?>

    </div>
    <?php
}

function add_points_for_product_review($comment_id, $comment_approved)
{
    if ($comment_approved === 1) {
        $comment = get_comment($comment_id);
        $user_id = $comment->user_id;
        $product_id = $comment->comment_post_ID;

        // Kiểm tra xem đây có phải là bình luận cho sản phẩm không
        if ('product' === get_post_type($product_id)) {
            // Số điểm bạn muốn cộng
            $points_to_add = 10000;

            // Lấy điểm hiện tại của người dùng
            $current_points = (int) get_user_meta($user_id, 'coins', true);
            // Tính toán điểm mới
            $new_points = $current_points + $points_to_add;

            // Cập nhật điểm cho người dùng
            update_user_meta($user_id, 'coins', $new_points);
        }
    }
}

add_action('comment_post', 'add_points_for_product_review', 10, 2);




