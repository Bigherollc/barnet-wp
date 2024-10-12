<?php
/* Template Name: Product Type Listing Page */

$user = new UserEntity();
if (!$user->getId()) {
    wp_redirect('/');
}
global $post;
$pageId = $post->ID;
$pageMetas = get_post_meta($pageId);
$product_type=get_term( $pageMetas["page-product-type"][0] )->name;
$pageShortDes = '';
if (isset($pageMetas["p_short_description"]) && is_array($pageMetas["p_short_description"]) && count($pageMetas["p_short_description"]) > 0) {
    $pageShortDes = $pageMetas["p_short_description"][0];
}
get_header(); ?>

    <main role="main">
        <div class="product-container" data-product>
            <div class="container" id="app">
                <com-container :isdarkmode="true">
                    <com-heading-group slot="left" title="<?php echo $product_type; ?>" desc="<?php echo $pageShortDes;?>"></com-heading-group>
                    <com-filter slot="left"></com-filter>
                    <com-tab-list slot="right">
                        <com-tab-list-item name="<?php echo strtolower($product_type);?>"></com-tab-list-item>
                    </com-tab-list>
                    <com-wrapping slot="right">
                        <com-filter-mobile></com-filter-mobile>
                        <com-listing>
                            <com-list-product :hasborder="true" data="/wp-json/barnet/v1/data?type=barnet-product" filter="/wp-json/barnet/v1/taxonomies?type=product-category"></com-list-product>
                            <com-load-more></com-load-more>
                        </com-listing>
                    </com-wrapping>
                </com-container>
            </div>
        </div>
    </main>

<?php get_footer(); ?>