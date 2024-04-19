<?php
/**
 * Storefront engine room
 *
 * @package storefront
 */

/**
 * Assign the Storefront version to a var
 */
$theme = wp_get_theme('storefront');
$storefront_version = $theme['Version'];

/**
 * Set the content width based on the theme's design and stylesheet.
 */
if (!isset($content_width)) {
	$content_width = 980; /* pixels */
}

$storefront = (object) array(
	'version' => $storefront_version,

	/**
	 * Initialize all the things.
	 */
	'main' => require 'inc/class-storefront.php',
	'customizer' => require 'inc/customizer/class-storefront-customizer.php',
);

require 'inc/storefront-functions.php';
require 'inc/storefront-template-hooks.php';
require 'inc/storefront-template-functions.php';
require 'inc/wordpress-shims.php';

if (class_exists('Jetpack')) {
	$storefront->jetpack = require 'inc/jetpack/class-storefront-jetpack.php';
}

if (storefront_is_woocommerce_activated()) {
	$storefront->woocommerce = require 'inc/woocommerce/class-storefront-woocommerce.php';
	$storefront->woocommerce_customizer = require 'inc/woocommerce/class-storefront-woocommerce-customizer.php';

	require 'inc/woocommerce/class-storefront-woocommerce-adjacent-products.php';

	require 'inc/woocommerce/storefront-woocommerce-template-hooks.php';
	require 'inc/woocommerce/storefront-woocommerce-template-functions.php';
	require 'inc/woocommerce/storefront-woocommerce-functions.php';
}

if (is_admin()) {
	$storefront->admin = require 'inc/admin/class-storefront-admin.php';

	require 'inc/admin/class-storefront-plugin-install.php';
}





/**
 * NUX
 * Only load if wp version is 4.7.3 or above because of this issue;
 * https://core.trac.wordpress.org/ticket/39610?cversion=1&cnum_hist=2
 */
if (version_compare(get_bloginfo('version'), '4.7.3', '>=') && (is_admin() || is_customize_preview())) {
	require 'inc/nux/class-storefront-nux-admin.php';
	require 'inc/nux/class-storefront-nux-guided-tour.php';
	require 'inc/nux/class-storefront-nux-starter-content.php';
}


// Define SMTP
define('SMTP_USER', 'huynhduyquy2001@gmail.com');     // Username to use for SMTP authentication
define('SMTP_PASS', 'obuv ganc uqaf lcou');                 // Password to use for SMTP authentication
define('SMTP_HOST', 'smtp.gmail.com');            // The hostname of the mail server
define('SMTP_PORT', '587');                  // SMTP port number - likely to be 25, 465 or 587
define('SMTP_SECURE', 'tls');                 // Encryption system to use - ssl or tls
define('SMTP_AUTH', true);                 // Use SMTP authentication (true|false)
define('SMTP_DEBUG', 0);                    // for debugging purposes only set to 1 or 2

// SMTP Function
add_action('phpmailer_init', 'custom_send_smtp_email');
function custom_send_smtp_email($phpmailer)
{
	$phpmailer->isSMTP();
	$phpmailer->Host = SMTP_HOST;
	$phpmailer->SMTPAuth = SMTP_AUTH;
	$phpmailer->Port = SMTP_PORT;
	$phpmailer->Username = SMTP_USER;
	$phpmailer->Password = SMTP_PASS;
	$phpmailer->SMTPSecure = SMTP_SECURE;
}
function send_novu_webhook_on_order_success($order_id)
{
	$order = wc_get_order($order_id);
	$novu_api_url = get_field('api_url', 'option');
	$novu_api_key = get_field('api_key', 'option');
	$novu_subcriberId = get_field('subscriber_id', 'option');
	$response = wp_remote_post(
		$novu_api_url,
		array(
			'method' => 'POST',
			'headers' => array(
				'Authorization' => 'ApiKey ' . $novu_api_key,
				'Content-Type' => 'application/json',
			),
			'body' => json_encode(
				array(
					'name' => 'test-email',
					'to' => array(
						'subscriberId' => $novu_subcriberId, // Thay đổi theo cần thiết
					),
					'payload' => array(
						'__source' => 'wordpress-order-success',
						'order_id' => $order_id,
						'order_total' => $order->get_total(),
						// Thêm bất kỳ thông tin nào khác từ đơn hàng vào payload nếu cần
					),
				)
			),
			'data_format' => 'body',
		)
	);

	if (is_wp_error($response)) {
		$error_message = $response->get_error_message();
		// Xử lý lỗi ở đây
	} else {
		// Xử lý phản hồi thành công
	}
}
add_action('woocommerce_checkout_order_processed', 'send_novu_webhook_on_order_success');

add_action('woocommerce_cart_calculate_fees', 'apply_discount_based_on_rules');
function apply_discount_based_on_rules($cart)
{
	if (function_exists('get_field') && have_rows('discount_rule', 'option')) {
		$discount_rules = get_field('discount_rule', 'option');

		if (is_array($discount_rules)) {
			$discount = 0;
			$check = false;
			foreach ($discount_rules as $discount_rule) {
				if ($discount_rule['rule'] == 'order' && is_array($discount_rule['order_rule'])) {
					foreach ($discount_rule['order_rule'] as $item) {
						if ($item['logical'] === 'AND') {
							if ($item['rule']['rule_name'] === 'Number of Ordered') {
								foreach ($cart->get_cart() as $cart_item_key => $cart_item) {
									$product_id = $cart_item['product_id'];
									$user_purchase_count = get_user_product_purchase_count($product_id);
									if ($user_purchase_count > $item['rule']['rule_value']) {
										$check = true; // Nếu một điều kiện không đạt, đặt $check là false và thoát khỏi vòng lặp
										break;
									}
								}
							} else if ($item['rule']['rule_name'] === 'Total Order') {
								$order_total = WC()->cart->subtotal;

								if ($order_total > floatval($item['rule']['rule_value'])) {
									$check = true;
								}
							}
						} else if ($item['logical'] === 'OR') {
							if ($item['rule']['rule_name'] === 'Number of Ordered') {
								foreach ($cart->get_cart() as $cart_item_key => $cart_item) {
									$product_id = $cart_item['product_id'];
									$user_purchase_count = get_user_product_purchase_count($product_id);
									if ($user_purchase_count > $item['rule']['rule_value']) {
										$check = true; // Nếu một điều kiện đạt, đặt $check là true và thoát khỏi vòng lặp
										break;
									}
								}
							} else if ($item['rule']['rule_name'] === 'Total Order') {
								$order_total = WC()->cart->subtotal;
								if ($order_total > floatval($item['rule']['rule_value'])) {

									$check = true; // Nếu một điều kiện đạt, đặt $check là true và thoát khỏi vòng lặp
									break;
								}
							}
						}
					}
				} else if ($discount_rule['rule'] == 'user' && is_array($discount_rule['user_rule'])) {
					// Xử lý khi quy tắc là user
					if ($item['logical'] === 'AND') {
						if ($item['rule']['rule_name'] === 'New Customer') {
							$current_user = wp_get_current_user();
							if ($current_user->ID !== 0) {
								// Kiểm tra nếu người dùng hiện tại đang đăng nhập
								$user_registered = strtotime($current_user->user_registered); // Chuyển đổi ngày tạo tài khoản thành dạng thời gian Unix
								$current_time = current_time('timestamp'); // Lấy thời gian hiện tại
								$seconds_diff = $current_time - $user_registered; // Tính hiệu của thời gian hiện tại và thời gian tạo tài khoản

								// Chia số giây đã trôi qua cho 86400 để tính số ngày đã đăng ký
								$days_registered = floor($seconds_diff / (60 * 60 * 24));
								if ($days_registered < $item['rule_value']) {
									$check = true;
								}

							}
						} else if ($item['rule']['rule_name'] === 'Old Customer') {
							$current_user = wp_get_current_user();
							if ($current_user->ID !== 0) {
								// Kiểm tra nếu người dùng hiện tại đang đăng nhập
								$user_registered = strtotime($current_user->user_registered); // Chuyển đổi ngày tạo tài khoản thành dạng thời gian Unix
								$current_time = current_time('timestamp'); // Lấy thời gian hiện tại
								$seconds_diff = $current_time - $user_registered; // Tính hiệu của thời gian hiện tại và thời gian tạo tài khoản

								// Chia số giây đã trôi qua cho 86400 để tính số ngày đã đăng ký
								$days_registered = floor($seconds_diff / (60 * 60 * 24));
								if ($days_registered > $item['rule_value']) {
									$check = true;
								}

							}
						}
					}
				}
				if ($check) {
					$discount += 10;

				}
			}
		} else {
			// Xử lý khi không tìm thấy trường 'discount_rule' hoặc hàm get_field không tồn tại
		}
	}
}

// Thêm một hàm vào hook
add_action('woocommerce_order_status_completed', 'check_next_rank', 10, 1);

function check_next_rank($order_id)
{

	// Lấy thông tin đơn hàng từ ID đơn hàng
	$order = wc_get_order($order_id);

	// Kiểm tra xem đơn hàng có tồn tại không
	if (!$order) {
		return;
	}

	// Lấy UserID từ đơn hàng
	$user_id = $order->get_user_id();

	// Kiểm tra xem UserID có tồn tại không
	if (!$user_id) {
		return;
	}

	$members = get_field('member', 'option');
	$rank = get_field('rank', 'user_' . $user_id);
	$check = false;
	send_notification_by_novu($user_id, $order_id);
	foreach ($members as $member) {
		if ($member['id'] == $rank + 1) {
			$rules = $member['rules'];
			foreach ($rules as $rule) {
				if ($rule['rule_name'] === 'total_price') {

					$rule_value = $rule['rule_value'];

					// Lấy tổng giá trị của đơn hàng
					$order_total = calculate_total_purchase($user_id); // Lấy tổng giá trị của đơn hàng

					error_log('rule_value' . $rule_value);
					// So sánh tổng giá trị của đơn hàng với giá trị của quy tắc
					if ($order_total > $rule_value) {
						//Viết hàm
						$check = true;

					} else {
						$check = false;
						break;
					}
				} else if ($rule['rule_name'] === 'total_amount_purchased') {
					$rule_value = $rule['rule_value'];
					$total_purchase = calculate_total_amount_purchase($user_id);
					if ($total_purchase > $rule_value) {
						$check = true;
					} else {
						$check = false;
						break;
					}
				}
			}
			if ($check == true) {
				update_field('rank', $member['id'], 'user_' . $user_id);
				send_notification_by_novu($user_id, $order_id);
			}
		}


	}
}

function send_notification_by_novu($user_id, $order_id)
{
	$order = wc_get_order($order_id);
	$novu_api_url = get_field('api_url', 'option');
	$novu_api_key = get_field('api_key', 'option');
	//$novu_subcriberId = get_field('subscriber_id', 'option');
	$response = wp_remote_post(
		$novu_api_url,
		array(
			'method' => 'POST',
			'headers' => array(
				'Authorization' => 'ApiKey ' . $novu_api_key,
				'Content-Type' => 'application/json',
			),
			'body' => json_encode(
				array(
					'name' => 'test-email',
					'to' => array(
						'subscriberId' => $user_id, // Thay đổi theo cần thiết
					),
					'payload' => array(
						'__source' => 'wordpress-order-success',
						'order_id' => $order_id,
						'order_total' => $order->get_total(),
						// Thêm bất kỳ thông tin nào khác từ đơn hàng vào payload nếu cần
					),
				)
			),
			'data_format' => 'body',
		)
	);
}


function calculate_total_purchase($current_user_id)
{
	$total_price = 0;
	// Kiểm tra xem người dùng có đăng nhập không
	if ($current_user_id) {
		// Lấy danh sách các đơn hàng đã hoàn thành của người dùng
		$orders = wc_get_orders(
			array(
				'customer' => $current_user_id, // Lọc theo ID của người dùng
				'status' => 'completed' // Chỉ lấy các đơn hàng đã hoàn thành
			)
		);
		// Kiểm tra xem có đơn hàng nào không
		if ($orders) {
			foreach ($orders as $order) {
				$total_price += $order->get_total();
			}
			return $total_price;
		} else {
			return 0;
		}
	} else {
		return 0;
	}

}


function calculate_total_amount_purchase($current_user_id)
{
	// Kiểm tra xem người dùng có đăng nhập không
	if ($current_user_id) {
		// Lấy danh sách các đơn hàng đã hoàn thành của người dùng
		$orders = wc_get_orders(
			array(
				'customer' => $current_user_id, // Lọc theo ID của người dùng
				'status' => 'completed' // Chỉ lấy các đơn hàng đã hoàn thành
			)
		);

		// Đếm số lượng đơn hàng
		$order_count = count($orders);

		// Trả về tổng số lượng đơn hàng
		return $order_count;
	} else {
		return 0;
	}
}


// Hàm lấy số ngày đã đăng ký của người dùng
function get_user_registered_days($user_id)
{
	// Lấy ngày đăng ký của người dùng
	$registration_date = get_user_meta($user_id, 'user_registered', true);
	error_log('registration_date' . $registration_date);
	if (!empty($registration_date)) {
		// Tính toán số ngày từ ngày đăng ký đến ngày hiện tại
		$registration_timestamp = strtotime($registration_date);
		$current_timestamp = current_time('timestamp');
		$days_registered = floor(($current_timestamp - $registration_timestamp) / (60 * 60 * 24));

		return $days_registered;
	} else {
		return 0; // Trả về 0 nếu không có thông tin về ngày đăng ký
	}
}


function get_user_product_purchase_count($product_id, $user_id = null)
{
	if (!$user_id)
		$user_id = get_current_user_id();

	if (!$user_id)
		return 0;

	// Lấy tất cả các đơn hàng đã hoàn thành của người dùng
	$completed_orders = wc_get_orders(
		array(
			'customer' => $user_id,
			'status' => 'completed',
			'limit' => -1,
		)
	);
	$purchase_count = 0;
	// Lặp qua từng đơn hàng
	foreach ($completed_orders as $order) {
		// Kiểm tra xem sản phẩm có tồn tại trong đơn hàng không
		$order_items = $order->get_items();
		foreach ($order_items as $item) {
			if ($item->get_product_id() == $product_id) {
				// Nếu sản phẩm tồn tại trong đơn hàng, tăng biến đếm số lần mua lên 1
				$purchase_count++;
				break;
			}
		}
	}
	return $purchase_count;
}


//lấy số đơn hàng đã mua
function get_user_order_count()
{
	// Kiểm tra nếu người dùng đã đăng nhập
	if (is_user_logged_in()) {
		// Lấy ID của người dùng hiện tại
		$user_id = get_current_user_id();

		// Đếm số lượng đơn hàng của người dùng
		$order_count = count(
			wc_get_orders(
				array(
					'customer' => $user_id,
					'status' => array('completed', 'processing') // Chỉ tính các đơn hàng đã hoàn thành hoặc đang xử lý
				)
			)
		);

		return $order_count;
	}

	return 0;
}

// Hàm để tạo chuỗi ngẫu nhiên
// Hook vào user_register
add_action('user_register', 'create_novu_subscriber_after_register', 10, 1);

function create_novu_subscriber_after_register($user_id)
{
	// Lấy thông tin của người dùng vừa được tạo
	$user_info = get_userdata($user_id);

	// Kiểm tra xem người dùng có tồn tại không
	if ($user_info) {
		// Gọi hàm create_novu_subscriber và truyền thông tin người dùng
		$result = create_novu_subscriber($user_info->ID, $user_info->user_email, $user_info->first_name, $user_info->last_name);
		// Kiểm tra kết quả
		if (is_string($result)) {
			// Ghi log nếu có lỗi
			error_log($result);
		} else {
			// Xử lý phản hồi từ API nếu cần
			// Ví dụ: lưu thông tin người đăng ký vào meta dữ liệu của người dùng
			update_user_meta($user_id, 'novu_subscriber_id', $result->subscriberId);
		}
	}
}


function add_initial_coins($user_id)
{
	$initial_points = 50000;  // Số coins ban đầu bạn muốn cộng

	// Cập nhật điểm cho người dùng mới
	update_user_meta($user_id, 'coins', $initial_points);

	// Tùy chọn: Ghi log để kiểm tra
	error_log("Added initial 50000 coins to user #{$user_id}");
}

// Gắn hàm vào hook user_register
add_action('user_register', 'add_initial_coins', 10, 1);


// Hàm create_novu_subscriber được cập nhật để nhận ID người dùng và sử dụng nó làm subscriberId

function create_novu_subscriber($user_id, $email, $firstName, $lastName)
{
	$url = 'https://api.novu.co/v1/subscribers';
	$api_key = '2a9988093a133fe6aa7b34f0b1bf86fe'; // Thay thế bằng khóa API thực tế của bạn
	$data = array(
		'subscriberId' => strval($user_id), // Sử dụng ID người dùng làm subscriberId
		'firstName' => $firstName,
		'lastName' => $lastName,
		'email' => $email,
		'phone' => '',
		'avatar' => '',
		'locale' => 'en-US',
		'data' => array(
			'isDeveloper' => true,
			'customKey' => 'customValue'
		)
	);

	$args = array(
		'headers' => array(
			'Content-Type' => 'application/json',
			'Accept' => 'application/json',
			'Authorization' => 'ApiKey ' . $api_key
		),
		'body' => json_encode($data),
	);

	$response = wp_remote_post($url, $args);

	if (is_wp_error($response)) {
		$error_message = $response->get_error_message();
		return "Something went wrong: $error_message";
	} else {
		$body = wp_remote_retrieve_body($response);
		$data = json_decode($body);
		return $data; // Trả về dữ liệu từ API
	}
}

add_action('acf/input/admin_enqueue_scripts', 'my_acf_admin_enqueue_scripts');
function my_acf_admin_enqueue_scripts()
{
	add_action('admin_print_scripts', 'my_acf_admin_print_scripts');
	function my_acf_admin_print_scripts()
	{
		?>
		<script>
			document.addEventListener('DOMContentLoaded', function () {
				acf.addAction('acfe/fields/button/success/name=accept', function (response, $el, data) {
					location.reload();

				});
				acf.addAction('acfe/fields/button/success/name=reject', function (response, $el, data) {
					location.reload();

				});
			});

		</script>
		<?php
	}
}











function add_custom_account_menu_item($items)
{
	// Thêm tab "Level" vào menu tài khoản
	$items['level'] = __('Level', 'text-domain');

	return $items;
}
add_filter('woocommerce_account_menu_items', 'add_custom_account_menu_item');


// Đăng ký shortcode
add_shortcode('mycred_balance', 'mycred_balance_shortcode');

// Hàm callback cho shortcode
function mycred_balance_shortcode($atts)
{
	echo '<link rel="stylesheet" href="https://www.w3schools.com/w3css/4/w3.css">';

	// Lấy ID của người dùng hiện tại
	$user_id = get_current_user_id();
	error_log('user_id: ' . $user_id);
	// Kiểm tra xem người dùng có tồn tại hay không
	if ($user_id) {
		$current_total_price = calculate_total_purchase($user_id);
		$current_total_amount = calculate_total_amount_purchase($user_id);

		$coins = get_user_meta($user_id, 'coins', true);
		$points = get_user_meta($user_id, 'mycred_default', true);

		$rank = get_user_meta($user_id, 'rank', true);
		$nextRank = '';
		$temp = $rank;
		$members = get_field('member', 'option');
		if ($rank == 1) {
			$rank = 'Đồng';
			$nextRank = 'Bạc';
		} else if ($rank == 2) {
			$rank = 'Bạc';
			$nextRank = 'Vàng';
		} else if ($rank == 3) {
			$rank = 'Vàng';
			$nextRank = 'Kim Cương';
		}

		?>
		<div>
			<p><b>Số dư đồng Points hiện tại:</b> <?php echo $points; ?></p>
			<p><b>Số dư đồng Coins hiện tại:</b> <?php echo $coins; ?></p>
			<p><b>Cấp độ người dùng hiện tại:</b> <?php echo $rank; ?></p>

			<p><b>Tổng tiền đã sử dụng:</b> <?php echo $current_total_price; ?></p>
			<p><b>Số đơn hàng đã mua:</b> <?php echo $current_total_amount; ?></p>
			<p><b style="color: red;">
					<b>Cấp độ tiếp theo:</b> <?php echo $nextRank; ?>, điều kiện nâng cấp:
				</b></p>
		</div>
		<?php
		foreach ($members as $member) {
			if ($member['id'] == $temp + 1) {
				$rules = $member['rules'];
				foreach ($rules as $rule) {
					if ($rule['rule_name'] === 'total_price') {
						$rule_value = $rule['rule_value'];
						// Lấy tổng giá trị của đơn hàng
						$order_total = calculate_total_purchase($user_id);

						if ($order_total < $rule_value) {
							// Tính toán phần trăm
							$process = $order_total * 100 / $rule_value;
							?>
							<p><b>Mức chi phí cần đạt:</b> <?php echo $rule_value; ?></p>

							<div class="w3-light-grey">
								<div class="w3-container w3-green w3-center" style="width:<?php echo $process; ?>%"> <?php echo $process; ?>%</div>
							</div><br>
							<?php
							// Thoát khỏi vòng lặp sau khi tìm thấy điều kiện nâng cấp
						}

					} else if ($rule['rule_name'] === 'total_amount_purchased') {
						$rule_value = $rule['rule_value'];
						$total_purchase = calculate_total_amount_purchase($user_id);
						if ($total_purchase < $rule_value) {
							// Viết hàm xử lý trong trường hợp này
							$process = $total_purchase * 100 / $rule_value;
							?>
								<p><b>Số lượng đơn hàng cần đạt:</b> <?php echo $rule_value; ?></p>

								<div class="w3-light-grey">
									<div class="w3-container w3-red w3-center" style="width:<?php echo $process; ?>%"> <?php echo $process; ?>%</div>
								</div><br>
							<?php
						}
					}
				}
			}
		}
	}
}


add_action('wp', 'save_data_to_cookie');

function save_data_to_cookie()
{
	// Lấy ID của người dùng hiện tại
	$current_user_id = get_current_user_id();

	// Kiểm tra nếu có tham số 'ref' trong URL
	if (isset($_GET['ref']) && !isset($_COOKIE['check_ref_cookie'])) {
		$referrer_id = intval($_GET['ref']);
		setcookie('ref_cookie_name', $referrer_id, time() + 3600, COOKIEPATH, COOKIE_DOMAIN);
	}

	// Tạo URL chia sẻ với ref=user_id của người dùng hiện tại
	$share_url = add_query_arg('ref', $current_user_id, home_url('/'));

	// In ra liên kết chia sẻ
	echo '<a href="' . esc_url($share_url) . '">Chia sẻ trang web</a>';
}


add_action('user_register', 'after_user_register');

function after_user_register($user_id)
{
	$cookieValue = $_COOKIE['ref_cookie_name'];
	// Kiểm tra xem người dùng hiện tại có trong session hay không và cookie 'check_ref_cookie' chưa được thiết lập
	if (isset($user_id) && isset($cookieValue)) {
		$current_coins = (int) get_user_meta($cookieValue, 'coins', true);
		// Cập nhật số điểm coins cho người dùng mới đăng ký
		update_user_meta($cookieValue, 'coins', $current_coins + 10000);
		setcookie('check_ref_cookie', 1, time() + 3600, COOKIEPATH, COOKIE_DOMAIN);
	}
}




/**
 * Note: Do not add any custom code here. Please use a custom plugin so that your customizations aren't lost during updates.
 * https://github.com/woocommerce/theme-customisations
 */






