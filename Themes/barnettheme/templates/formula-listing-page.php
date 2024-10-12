<?php
/* Template Name: Formula Listing Page */

$user = new UserEntity();
if (!$user->getId()) {
    wp_redirect('/');
}

get_header(); ?>

    <main role="main">
        <div class="product-container" data-product>
            <div class="container" id="app">
                <com-container :isdarkmode="true">
                    <com-heading-group slot="left" title="STARTING FORMULA" desc="These forward-looking and lab tested formula will kickstart your product development."></com-heading-group>
                    <com-filter slot="left"></com-filter>
                    <com-tab-list class="d-none" slot="right">
                        <com-tab-list-item name="formula"></com-tab-list-item>
                    </com-tab-list>
                    <com-wrapping slot="right">
                        <com-filter-mobile></com-filter-mobile>
                        <com-listing>
                            <com-list-formula data="/wp-json/barnet/v1/data?type=barnet-formula" filter="/wp-json/barnet/v1/taxonomies?type=formula-category"></com-list-formula>
                            <com-load-more></com-load-more>
                        </com-listing>
                    </com-wrapping>
                </com-container>
            </div>
        </div>
    </main>

<?php get_footer(); ?>