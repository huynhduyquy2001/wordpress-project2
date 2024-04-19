<?php
/*
 Template Name: Gift
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
get_header();
?>

<!-- HTML -->

<notification-center-component style="display: inline-flex" application-identifier="QyF7ZmqJ4ToE"
    subscriber-id="660f6ef191bd69a79e877b30"></notification-center-component>
<script>
    // here you can attach any callbacks, interact with the Notification Center Web Component API
    let nc = document.getElementsByTagName("notification-center-component")[0];
    nc.onLoad = () => console.log("Notification Center session loaded!");
</script>


<?php echo do_shortcode('[gift_products]'); ?>

<!-- <?php echo do_shortcode('[mycred_history]'); ?> -->

<script>
    jQuery(document).ready(function ($) {
        var ajaxurl = '<?php echo esc_js(admin_url('admin-ajax.php', 'relative')); ?>';
        $(".redeem-gift").on("click", function (e) {
            e.preventDefault();
            var current_point = $('#current_point').text();
            if (current_point < productPrice) {
                alert("Bạn chưa đủ điểm thưởng, vui lòng tích lũy thêm");
                return;
            }
            var productID = $(this).data("product-id");
            var productPrice = $(this).data("product-price");
            var userID = $(this).data("user-id");
            var data = {
                action: "redeem_gift_request",
                product_id: productID,
                product_price: productPrice,
                user_id: userID
            };
            $.post(ajaxurl, data, function (response) {
                if (response === "success") {
                    alert("Yêu cầu đổi quà đã được gửi.");
                } else {
                    alert("Có lỗi xảy ra. Vui lòng thử lại sau.");
                }
            });
        });
    });
</script>


<?php
do_action('storefront_sidebar');
get_footer();