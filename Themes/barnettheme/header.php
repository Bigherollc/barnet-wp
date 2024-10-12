<?php
/**
 * The header for our theme
 *
 * This is the template that displays all of the <head> section and everything up until <div id="content">
 *
 * @link https://developer.wordpress.org/themes/basics/template-files/#template-partials
 *
 * @package WordPress
 * @subpackage Taxback
 * @since 1.0
 * @version 1.0
 */

$isSearch = is_page_template('templates/search.php');
$isBlogPages = is_single() || is_tag() || is_category() || get_post_type() === 'post' || is_page_template('blog.php') || is_page_template('categories.php') || is_page_template('tags.php');

$bodyClass = $isSearch ? 'search-page' : '--bg-gradient';

if($isBlogPages) {
    $bodyClass = 'blog-page';
}

$postId = get_the_ID();
$backgroundImageId = get_post_meta($postId, 'p_background_image');
if (!empty($backgroundImageId) && count($backgroundImageId) > 0) {
    $backgroundImageURL = wp_get_attachment_url($backgroundImageId[0]);
}

$htmlBackGroundURL = isset($backgroundImageURL) && $backgroundImageURL ?
    'data-parallax style="background-image:url(' . $backgroundImageURL . ');"' : '';
$strAddSample =  get_theme_mod('product_add_sample_text',
    class_exists('BarnetDefaultText') ? BarnetDefaultText::PRODUCT_ADD_SAMPLE_TEXT : '');
if (is_singular()) {
    $postCurent = get_post();
    if ($postCurent && isset($postCurent->post_type) && $postCurent->post_type == "barnet-formula") {
        $strAddSample =  get_theme_mod('formula_add_sample_text',
            class_exists('BarnetDefaultText') ? BarnetDefaultText::FORMULA_ADD_SAMPLE_TEXT : '');
    }
}
?>
<html <?php global $wp;
language_attributes(); ?> class="no-js" lang="en" dir="ltr">
<head>
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta charset="<?php bloginfo('charset'); ?>">
    <meta name="viewport"
          content="width=device-width,initial-scale=1,shrink-to-fit=no, maximum-scale=1.0, user-scalable=no">
    <title><?php wp_title(''); ?></title>
    <?php
    wp_head();
    $page = class_exists('BarnetProduct') ? BarnetProduct::$PAGE : [];
    $page_slug = add_query_arg(array(), $wp->request);
    ?>
    <!-- Start: Script GTM-->
    <script>window.dataLayer = window.dataLayer || [];</script>
    <!-- End: Script GTM-->
</head>
<body class="<?php echo $bodyClass;?> <?php echo isset($page[$page_slug]) ? $page[$page_slug] : '' ?>"
      data-site-id="<?php echo get_current_blog_id(); ?>"
      data-cart-sample data-opts-sample='{"successPage": "/samples-selected", "failPage": "/no-samples-selected", "addText": "<?php _e('Samples Added');?>", "defaultText": "<?php echo $strAddSample;?>"}'
      data-url='{"product": "/wp-json/barnet/v1/data?type=barnet-product&sort=post_title&sort_none=1", "formula": "/wp-json/barnet/v1/data?type=barnet-formula&sort=post_title&sort_none=1"}'
      <?php echo $htmlBackGroundURL; ?>>
<header class="header" data-header>
    <div class="header__sticky --bg-gradient" data-sticky>
        <?php
        if (is_user_logged_in()) {
            get_template_part('template-parts/header/header-sites');
        } else {
            get_template_part('template-parts/header/header-guest');
        }

        if (is_user_logged_in() || is_plugin_active("user-menus/user-menus.php")) {
            get_template_part('template-parts/header/header-menus');
        }
        ?>
        <div class="header__search-dropdown" data-search-dropdown="">
            <div class="header__search-form" data-search-form data-action="/search">
                <button data-search-btn><i class="icon icon-search"></i></button>
                <input class="form-control" name="s" type="text" autocomplete="off" data-search-input data-api="/wp-json/barnet/v1/data?type=barnet-product,barnet-formula,barnet-resource,barnet-concept&sort=post_title&pdf_none=1">
                <button data-search-clear-btn=""><i class="icon icon-close"></i></button>
            </div>
        </div>
    </div>
</header>
