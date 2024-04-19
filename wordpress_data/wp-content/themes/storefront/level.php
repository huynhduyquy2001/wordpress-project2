<?php
/*
 Template Name: Level Page
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

<?php
add_filter('woocommerce_account_menu_items', 'custom_account_menu_items', 10, 1);

function custom_account_menu_items($menu_items)
{
    // In ra menu items
    foreach ($menu_items as $endpoint => $label) {
        echo '<li><a href="' . esc_url(wc_get_account_endpoint_url($endpoint)) . '">' . esc_html($label) . '</a></li>';
    }
}


?>

<div id="primary" class="content-area">

    <main id="main" class="site-main" role="main">

        <?php echo do_shortcode('[mycred_balance]'); ?>

    </main><!-- #main -->
</div>


<?php
do_action('storefront_sidebar');
get_footer();