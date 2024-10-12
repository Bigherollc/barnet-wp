<?php
/* Template Name: Resource Listing Page */

$user = new UserEntity();
if (!$user->getId()) {
    wp_redirect('/');
}

get_header(); ?>

    <main role="main">
        <div class="product-container" data-product>
            <div class="container" id="app">
                <com-container :isdarkmode="true">
                    <com-heading-group slot="left" title="Resources" desc="Barnet is always creating videos, presentations and lorem ipsum dolor sit ahment."></com-heading-group>
                    <com-filter slot="left"></com-filter>
                    <com-tab-list class="d-none" slot="right">
                        <com-tab-list-item name="resource"></com-tab-list-item>
                    </com-tab-list>
                    <com-wrapping slot="right">
                        <com-filter-mobile></com-filter-mobile>
                        <com-listing>
                            <com-list-resource data="/wp-json/barnet/v1/data?type=barnet-resource" filter="/wp-json/barnet/v1/taxonomies?type=resource-type"></com-list-resource>
                            <com-load-more></com-load-more>
                        </com-listing>
                    </com-wrapping>
                </com-container>
            </div>
        </div>
    </main>

<?php get_footer(); ?>