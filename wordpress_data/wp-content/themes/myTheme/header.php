<!DOCTYPE html>
<html>

<head>
      <title>
            <?php wp_title(); ?>
      </title>
      <meta charset="<?php bloginfo( 'charset' ); ?>">
      <meta name="viewport" content="width=device-width, initial-scale=1">
      <link rel="stylesheet" href="<?php echo get_stylesheet_uri(); ?>">
      <?php wp_head(); ?>
      <?php
// Get the base URL of the current page
$base_url = rtrim(dirname($_SERVER['PHP_SELF']), '/\\');

$css_files = array(
    "/wp-content/themes/myTheme/css/bootstrap.min.css",
    "/wp-content/themes/myTheme/css/normalize.css",
    "/wp-content/themes/myTheme/css/font-awesome.min.css",
    "/wp-content/themes/myTheme/css/icomoon.css",
    "/wp-content/themes/myTheme/css/jquery-ui.css",
    "/wp-content/themes/myTheme/css/owl.carousel.css",
    "/wp-content/themes/myTheme/css/transitions.css",
    "/wp-content/themes/myTheme/css/main.css",
    "/wp-content/themes/myTheme/css/color.css",
    "/wp-content/themes/myTheme/css/responsive.css"
);

foreach ($css_files as $css_file) {
    echo '<link rel="stylesheet" href="' . $base_url . $css_file . '">';
}
?>
      <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">

</head>

<body>
      <header id="tg-header" class="tg-header tg-haslayout">

            <div class="tg-navigationarea">
                  <div class="container">
                        <div class="row">
                              <div class="col-xs-12 col-sm-12 col-md-12 col-lg-12">
                                    <nav id="tg-nav" class="tg-nav">
                                          <div class="navbar-header">
                                                <button type="button" class="navbar-toggle collapsed"
                                                      data-toggle="collapse" data-target="#tg-navigation"
                                                      aria-expanded="false">
                                                      <span class="sr-only">Toggle navigation</span>
                                                      <span class="icon-bar"></span>
                                                      <span class="icon-bar"></span>
                                                      <span class="icon-bar"></span>
                                                </button>
                                          </div>
                                          <div id="tg-navigation" class="collapse navbar-collapse tg-navigation">
                                                <ul>

                                                      <li><a href="products.html">Home</a></li>
                                                      <li><a href="products.html">Best Selling</a></li>
                                                      <li><a href="products.html">Weekly Sale</a></li>
                                                      <li><a href="products.html">Lasted News</a></li>
                                                      <li><a href="contactus.html">Contact</a></li>
                                                      <li class="menu-item-has-children current-menu-item">

                                                            <ul class="sub-menu">
                                                                  <li class="menu-item-has-children">
                                                                        <a href="aboutus.html">Products</a>
                                                                        <ul class="sub-menu">
                                                                              <li><a href="products.html">Products</a>
                                                                              </li>
                                                                              <li><a href="productdetail.html">Product
                                                                                          Detail</a></li>
                                                                        </ul>
                                                                  </li>
                                                                  <li><a href="aboutus.html">About Us</a></li>
                                                                  <li><a href="404error.html">404 Error</a></li>
                                                                  <li><a href="comingsoon.html">Coming Soon</a></li>
                                                            </ul>
                                                      </li>
                                                </ul>
                                          </div>
                                    </nav>
                              </div>
                        </div>
                  </div>
            </div>
      </header>
     