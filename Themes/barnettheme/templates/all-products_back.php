<?php
/* Template Name: All Products back */

$user = new UserEntity();
if ($user->getId()) {
    wp_redirect('/');
}
/*
$orign_product_types = get_terms(array(
    'taxonomy'   => 'product-type',
    'hide_empty' => false,
) );  

$product_types=[];
usort($orign_product_types, function($a, $b) {
    return get_field('order', $a) - get_field('order', $b);
});

foreach($orign_product_types as $orign_product_type){
    
    $product_types[] = $orign_product_type->name;
}

$comTabListItems="";
foreach($product_types as $product_type){
    $comTabListItems.= '<com-tab-list-item name="'.strtolower($product_type).'"></com-tab-list-item>';
}
*/
get_header(); ?>
    <main role="main">
        <div class="product-container">
            <div class="container" id="app">
                <com-container :isdarkmode="true">
                    <com-heading-group slot="left" title="All Products" desc="We specialize in Active Ingredients for the use in personal care products and unique System Formers inspired by East Asian trends."></com-heading-group>
                    <com-filter slot="left"></com-filter>
                    <!--<com-tab-list slot="right">
                        <?php //echo $comTabListItems; ?>
                    </com-tab-list>-->
                    <com-wrapping slot="right">
                        <!-- <com-sign-in background-image="<?php echo get_template_directory_uri(); ?>/assets/images/product/bg-signin.png" url-sign-in="/login" url-request-access="/register"></com-sign-in> -->
                         <?php the_content(); ?>
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