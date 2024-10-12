<?php
/* Template Name: Search */

$comTabListToogle = '';
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

$comSignIn = '<com-sign-in background-image="' . get_template_directory_uri() . '/assets/images/product/bg-signin.png" url-sign-in="/login" url-request-access="/register"></com-sign-in>';
$comListSearchData = '/wp-json/barnet/v1/search?type=barnet-product';

if (is_user_logged_in()) {
//    $comTabListToogle = ':hastoggle="true"';
/*
    $comTabListItems .= '
        <com-tab-list-item name="formula"></com-tab-list-item>
        <com-tab-list-item name="resource"></com-tab-list-item>
    ';
*/
    $comSignIn = '';
    $comListSearchData = '/wp-json/barnet/v1/search?type=barnet-product,barnet-formula,barnet-resource,barnet-concept';
}

get_header(); ?>

<main role="main">
    <div class="search" data-search>
        
        <div class="container" id="app">
            <com-container>
                <com-heading-search slot="left"></com-heading-search>
                <com-filter slot="left"></com-filter>
                <com-no-result-sidebar slot="left" image="<?php echo get_template_directory_uri(); ?>/assets/images/search/sidebarArtboard.png"></com-no-result-sidebar>
                <!--<com-tab-list slot="right" <?php// echo $comTabListToogle; ?>>
                    <?php// echo $comTabListItems; ?>
                </com-tab-list>-->
                <!-- <com-image-concepts slot="right" options="{&quot;slide&quot;: &quot;[data-slider-item]&quot;, &quot;dots&quot;: true, &quot;rows&quot;: 0, &quot;slidesToShow&quot;: 3, &quot;slidesToScroll&quot;: 3, &quot;responsive&quot;:[{&quot;breakpoint&quot;: 768, &quot;settings&quot;: {&quot;slidesToShow&quot;: 2, &quot;slidesToScroll&quot;: 2}}]}"></com-image-concepts> -->
               
                
                <com-wrapping slot="right">  
                    <?php the_content();?>
                    <com-image-concepts options='{"slide": "[data-slider-item]", "dots": true, "rows": 0, "slidesToShow": 3, "slidesToScroll": 3, "responsive":[{"breakpoint": 768, "settings": {"slidesToShow": 2, "slidesToScroll": 2}}]}'></com-image-concepts>                 
                    <com-filter-mobile></com-filter-mobile>
                    <?php echo $comSignIn;?>
                    <com-listing>
                        <com-list-search :hasborder="true" data="<?php echo $comListSearchData; ?>" filter="/wp-json/barnet/v1/taxonomies"></com-list-search>
                        <com-load-more></com-load-more>
                    </com-listing>
                    <com-no-result image="<?php echo get_template_directory_uri(); ?>/assets/images/search/microscope_lg.svg" text="No matching results could be found. Modify your keywords and try again."></com-no-result>
                </com-wrapping>
            </com-container>
        </div>
    </div>
</main>

<?php get_footer(); ?>
