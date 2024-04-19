<?php
/*
 Template Name: show pd
 */
?>


<head>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-9ndCyUaIbzAi2FUVXJi0CjmCapSmO7SnpJef0486qhLnuZ2cdeRhO02iuK6FUUVM" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css"
        integrity="sha512-iecdLmaskl7CVkqkXNQ/ZH/XLlvWZOJyj7Yy7tcenmpD1ypASozpmT/E0iPtmFIB46ZmdtAc9eNBvH0H/ZpiBw=="
        crossorigin="anonymous" referrerpolicy="no-referrer" />
    <script src="https://web-component.novu.co/index.js"></script>
    <style>
        select {
            padding: .6180469716em;
            /* Thiết lập padding cho toàn bộ select box */
        }

        /* Các option trong select box */
        select option {
            padding: .6180469716em;
            /* Thiết lập padding cho mỗi option */
        }

        table:not(.has-background) tbody tr:nth-child(2n) td,
        fieldset,
        fieldset legend {
            background-color: white !important;
        }

        fieldset {
            padding: 0 !important;
            margin: 0 !important;
        }

        fieldset legend {
            font-weight: 600 !important;
            padding: 0 !important;
            margin-left: 0 !important;
        }
    </style>
</head>

<?php
get_header(); ?>

<!-- HTML -->
<notification-center-component style="display: inline-flex" application-identifier="QyF7ZmqJ4ToE"
    subscriber-id="660f6ef191bd69a79e877b30"></notification-center-component>
<script>
    // here you can attach any callbacks, interact with the Notification Center Web Component API
    let nc = document.getElementsByTagName("notification-center-component")[0];
    nc.onLoad = () => console.log("Notification Center session loaded!");
</script>


<div id="primary" class="content-area">

    <main id="main" class="site-main" role="main">

        <form id="sales-report-form">
            <label for="start_price">Start Price:</label>
            <input type="number" id="start_price" name="start_date">
            <label for="end_price">End Price:</label>
            <input type="number" id="end_price" name="end_date">
            <br><br><label for="search_keyword">Search by Name:</label>
            <input type="text" id="search_keyword" name="search_keyword">
            <?php
            // Assuming that '[list_categories]' is a shortcode to display categories
            echo do_shortcode('[list_categories]');
            ?>
            <?php
            // Assuming that '[all_product_attributes_dropdowns]' is a shortcode to display product attributes dropdowns
            echo do_shortcode('[all_product_attributes_dropdowns]');
            ?>
            <!-- Thêm ô tìm kiếm theo tên -->
            <!-- /Thêm ô tìm kiếm theo tên -->
            <input type="submit" value="Lọc">
        </form>



        <div id="show">
            <?php
            echo do_shortcode('[custom_product_content]');
            ?>

        </div>

    </main><!-- #main -->
</div><!-- #primary -->
<script>
    jQuery(document).ready(function ($) {
        // Phân tích query param từ URL và thiết lập giá trị của input khi trang được tải
        var urlParams = new URLSearchParams(window.location.search);
        var startPriceParam = urlParams.get('start_price');
        var endPriceParam = urlParams.get('end_price');
        var productCategoryParam = urlParams.get('product_category');

        $('#start_price').val(startPriceParam);
        $('#end_price').val(endPriceParam);
        $('#product_category').val(productCategoryParam);

        if (startPriceParam && endPriceParam) {
            // Thực hiện AJAX để lọc sản phẩm và hiển thị kết quả
            $.ajax({
                url: '<?php echo admin_url('admin-ajax.php'); ?>', // Sử dụng ajaxurl được định nghĩa
                type: 'POST',
                data: {
                    action: 'filter_products', // Hành động cần thực hiện trong file PHP
                    start_price: startPriceParam,
                    end_price: endPriceParam,
                    product_category: productCategoryParam // Truyền giá trị product_category trong dữ liệu AJAX
                },
                success: function (response) {
                    $('#show').html(response); // Thay thế nội dung của vùng hiển thị sản phẩm với dữ liệu trả về từ máy chủ
                },
                error: function (xhr, status, error) {
                    console.log(xhr.responseText);
                }
            });
        }
        $('input[type="checkbox"], input[type="radio"]').change(function () {
            // Xử lý khi có sự thay đổi trên checkbox hoặc radio button
            search_and_filter();
        });

        $('input[type="text"]').on('change keydown ', function () {
            // Xử lý khi có sự thay đổi, keydown hoặc keyup trên trường input văn bản

            search_and_filter();
        });

        $('input[type="number"]').on('change keydown ', function () {
            // Xử lý khi có sự thay đổi, keydown hoặc keyup trên trường input số
            search_and_filter();
        });


        $('select').change(function () {
            // Xử lý khi có sự thay đổi trên dropdown select
            search_and_filter();
        });

        function search_and_filter() {
            var startPrice = $('#start_price').val();
            var endPrice = $('#end_price').val();
            var productCategory = $('#product_category').val(); // Lấy giá trị của trường product_category
            var size = [];
            var origin = [];
            var search_keyword = $('#search_keyword').val();

            // Lấy giá trị của các checkbox size đã chọn
            $('input[name="size[]"]:checked').each(function () {
                size.push($(this).val());
            });

            // Lấy giá trị của các checkbox color đã chọn
            $('input[name="origin[]"]:checked').each(function () {
                origin.push($(this).val());
            });
            console.log(size);
            console.log(origin);
            // Cập nhật query param vào URL
            var newUrl = updateQueryStringParameter(window.location.href, 'start_price', startPrice);
            newUrl = updateQueryStringParameter(newUrl, 'end_price', endPrice);
            newUrl = updateQueryStringParameter(newUrl, 'product_category', productCategory); // Cập nhật query param cho product_category
            window.history.pushState({ path: newUrl }, '', newUrl);
            // Thực hiện AJAX để lọc sản phẩm và hiển thị kết quả
            $.ajax({
                url: '<?php echo admin_url('admin-ajax.php'); ?>', // Sử dụng ajaxurl được định nghĩa
                type: 'POST',
                data: {
                    action: 'filter_products', // Hành động cần thực hiện trong file PHP
                    start_price: startPrice,
                    end_price: endPrice,
                    product_category: productCategory, // Truyền giá trị product_category trong dữ liệu AJAX
                    size: size.join(','), // Chuyển mảng size thành chuỗi ngăn cách bởi dấu phẩy
                    origin: origin.join(','), // Chuyển mảng colors thành chuỗi ngăn cách bởi dấu phẩy
                    search_keyword: search_keyword
                },
                success: function (response) {
                    $('#show').html(response); // Thay thế nội dung của vùng hiển thị sản phẩm với dữ liệu trả về từ máy chủ
                },
                error: function (xhr, status, error) {
                    console.log(xhr.responseText);
                }
            });
        }


        $('#sales-report-form').on('submit', function (e) {
            e.preventDefault(); // Ngăn chặn biểu mẫu gửi thông tin một cách thông thường
            var startPrice = $('#start_price').val();
            var endPrice = $('#end_price').val();
            var productCategory = $('#product_category').val(); // Lấy giá trị của trường product_category
            var size = [];
            var origin = [];
            var search_keyword = $('#search_keyword').val();

            // Lấy giá trị của các checkbox size đã chọn
            $('input[name="size[]"]:checked').each(function () {
                size.push($(this).val());
            });

            // Lấy giá trị của các checkbox color đã chọn
            $('input[name="origin[]"]:checked').each(function () {
                origin.push($(this).val());
            });
            console.log(size);
            console.log(origin);
            // Cập nhật query param vào URL
            var newUrl = updateQueryStringParameter(window.location.href, 'start_price', startPrice);
            newUrl = updateQueryStringParameter(newUrl, 'end_price', endPrice);
            newUrl = updateQueryStringParameter(newUrl, 'product_category', productCategory); // Cập nhật query param cho product_category
            window.history.pushState({ path: newUrl }, '', newUrl);
            // Thực hiện AJAX để lọc sản phẩm và hiển thị kết quả
            $.ajax({
                url: '<?php echo admin_url('admin-ajax.php'); ?>', // Sử dụng ajaxurl được định nghĩa
                type: 'POST',
                data: {
                    action: 'filter_products', // Hành động cần thực hiện trong file PHP
                    start_price: startPrice,
                    end_price: endPrice,
                    product_category: productCategory, // Truyền giá trị product_category trong dữ liệu AJAX
                    size: size.join(','), // Chuyển mảng size thành chuỗi ngăn cách bởi dấu phẩy
                    origin: origin.join(','), // Chuyển mảng colors thành chuỗi ngăn cách bởi dấu phẩy
                    search_keyword: search_keyword
                },
                success: function (response) {
                    $('#show').html(response); // Thay thế nội dung của vùng hiển thị sản phẩm với dữ liệu trả về từ máy chủ
                },
                error: function (xhr, status, error) {
                    console.log(xhr.responseText);
                }
            });
        });

        // Hàm cập nhật query param vào URL
        function updateQueryStringParameter(uri, key, value) {
            var re = new RegExp("([?&])" + key + "=.*?(&|$)", "i");
            var separator = uri.indexOf('?') !== -1 ? "&" : "?";
            if (uri.match(re)) {
                return uri.replace(re, '$1' + key + "=" + value + '$2');
            }
            else {
                return uri + separator + key + "=" + value;
            }
        }

    });
</script>

<?php
do_action('storefront_sidebar');
get_footer();