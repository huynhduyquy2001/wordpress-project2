<?php
/*
Plugin Name: Custom API Plugin
Description: A custom API plugin for Test Api.
Version: 1.0
Author: Your Name
*/

add_action('rest_api_init', 'custom_api_register_custom_post_type_endpoint');

function custom_api_register_custom_post_type_endpoint()
{
    register_rest_route(
        'custom/v1',
        '/gift-post-type',
        array(
            'methods' => 'GET',
            'callback' => 'custom_api_get_gift_post_type',
        )
    );

    register_rest_route(
        'custom/v1',
        '/gift-post-type/create',
        array(
            'methods' => 'POST',
            'callback' => 'custom_api_create_gift_post',
            // 'permission_callback' => function () {
            //     return current_user_can('edit_posts');
            // },
        )
    );
}

function custom_api_get_gift_post_type($request)
{
    // Lấy tham số truy vấn (nếu có)
    $params = $request->get_params();
    error_log(json_encode($params));

    // Thiết lập các tham số mặc định
    $defaults = array(
        'post_type' => 'gift-post-type',
        'posts_per_page' => -1,
    );

    // Kết hợp tham số truy vấn với các tham số mặc định
    $args = wp_parse_args($params, $defaults);

    // Truy vấn dữ liệu từ Custom Post Type
    $custom_posts = new WP_Query($args);

    // Xử lý dữ liệu trước khi trả về (nếu cần)
    $formatted_posts = array();
    if ($custom_posts->have_posts()) {
        while ($custom_posts->have_posts()) {
            $custom_posts->the_post();

            // Lấy ID của bài đăng
            $post_id = get_the_ID();

            // Lấy ID của sản phẩm
            $product_id = get_post_meta($post_id, 'product_id', true);

            // Lấy giá của sản phẩm
            $product_price = get_post_meta($post_id, 'product_price', true);

            // Lấy ID của người dùng
            $user_id = get_post_meta($post_id, 'user_id', true);

            // Lấy trạng thái của quà tặng
            $gift_status = get_post_meta($post_id, 'gift_status', true);

            // Thêm dữ liệu vào mảng
            $formatted_posts[] = array(
                'id' => $post_id,
                'title' => get_the_title(),
                'product_id' => $product_id,
                'product_price' => $product_price,
                'user_id' => $user_id,
                'gift_status' => $gift_status,
                // Thêm các trường dữ liệu khác nếu cần
            );
        }
    }


    // Trả về dữ liệu dưới dạng JSON
    return rest_ensure_response($formatted_posts);
}


function custom_api_create_gift_post($request)
{
    $params = $request->get_params();

    // Kiểm tra xem các tham số cần thiết đã được gửi đến hay chưa
    if (empty($params['title']) || empty($params['product_id']) || empty($params['product_price']) || empty($params['user_id']) || empty($params['gift_status'])) {
        return new WP_Error('invalid_parameters', 'Missing required parameters', array('status' => 400));
    }

    // Tạo một bài đăng mới
    $post_data = array(
        'post_title' => sanitize_text_field($params['title']),
        'post_type' => 'gift-post-type',
        'post_status' => 'Pending',
    );

    $post_id = wp_insert_post($post_data, true);

    if (is_wp_error($post_id)) {
        return new WP_Error('create_post_error', 'Failed to create new post', array('status' => 500));
    }

    // Lưu thông tin bổ sung vào meta data của bài đăng
    update_post_meta($post_id, 'product_id', sanitize_text_field($params['product_id']));
    update_post_meta($post_id, 'product_price', sanitize_text_field($params['product_price']));
    update_post_meta($post_id, 'user_id', sanitize_text_field($params['user_id']));
    update_post_meta($post_id, 'gift_status', sanitize_text_field($params['gift_status']));

    return new WP_REST_Response(array('message' => 'Gift post created successfully', 'post_id' => $post_id), 200);
}
