<?php
/* Template Name: Active Listing Page */

$user = new UserEntity();
if (!$user->getId()) {
    wp_redirect('/');
}

get_header(); ?>

    <main role="main">
        <div class="product-container" data-product>
            <div class="container" id="app">
                <com-container :isdarkmode="true">
                    <com-heading-group slot="left" title="Actives" desc="Globally sourced and meticulously tested active ingredients for personal care products."></com-heading-group>
                    <com-filter slot="left"></com-filter>
                    <com-tab-list slot="right">
                        <com-tab-list-item name="active"></com-tab-list-item>
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