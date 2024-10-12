<?php
/* Template Name: Active Landing Page */
global $wp;
$current_url = trim(site_url(add_query_arg(array(array()), "/" . $wp->request . "/")), "/");
$user = new UserEntity();
if (!$user->getId()) {
    wp_redirect('/');
}

global $post;
$pageId = $post->ID;
$pageTitle = $post->post_title;
$pageMetas = get_post_meta($pageId);
$pageShortDes = '';

if (isset($pageMetas["p_short_description"]) && is_array($pageMetas["p_short_description"]) && count($pageMetas["p_short_description"]) > 0) {
    $pageShortDes = $pageMetas["p_short_description"][0];
}


$product_type_term = get_term_by('name', 'active', 'product-type');
$args = array(
    'taxonomy' => 'product-category',
    'product_type_term' => $product_type_term->term_id,
    'hide_empty' => false,
    'meta_load_field' => 1
);


$taxonomies = get_terms($args);

$formatTaxonomies = array();
foreach ($taxonomies as $taxonomy) {
    if ($taxonomy->parent == 0) {
        $formatTaxonomies[$taxonomy->term_id] = $taxonomy->to_array();
    }
}

foreach ($taxonomies as $taxonomy) {
    if ($taxonomy->parent != 0) {
        $taxArray = $taxonomy->to_array();
        $taxArray['count'] = 0;
        $formatTaxonomies[$taxonomy->parent]['child'][] = $taxArray;
    }
}

/**************************************** Product List **************************************/
$limitProduct = -1;
$args = array(
    'post_type' => 'barnet-product',
    'post_status' => 'publish',
    'posts_per_page' => -1,
    'orderby' => 'post_title',
    'order' => 'ASC',
    'meta_query' => array(
        array(
            'key' => 'product_type',
            'value' => 'active',
            'compare' => '='
        ),
    )
);

if (isset($_REQUEST['ts'])) {
    $args['tax_query'] = array(
        array(
            'taxonomy' => 'product-category',
            'field' => 'slug',
            'terms' => $_REQUEST['ts'],
            'operator' => 'IN'
        )
    );
}
$queryPostsActive = new WP_Query($args);
$postsActiveDefault = $queryPostsActive->posts;

$postsActive = array();
$metaPostManager = new BarnetPostMetaManager($postsActiveDefault);
$relationshipManager = new BarnetRelationshipManager();
$relationshipManager->syncTerm();
foreach ($postsActiveDefault as $postActiveDefault) {
    //Remove product on list
    $product_only_for_code_list = 0;
    $product_only_for_code_list = get_post_meta(intval($postActiveDefault->ID), 'product_only_for_code_list', TRUE);
    if (intval($product_only_for_code_list) == 1) {
        continue;
    }
    $productEntity = new ProductEntity(
        $postActiveDefault->ID,
        true,
        array(
            'post' => $postActiveDefault,
            'meta' => $metaPostManager->getMetaData($postActiveDefault->ID)
        )
    );
    $productEntity->setRelationshipManager($relationshipManager);
    if ($productEntity->checkRoleAndRegion()) {
        $postsActive[] = $productEntity->toArray(BarnetEntity::$PUBLIC_LANDING);
        $productTaxonomies = $productEntity->getTaxonomies();
        $productTaxonomyIds = array_map(function ($e) {
            return is_array($e) ? $e['term_id'] : $e->term_id;
        }, $productTaxonomies);

        foreach ($formatTaxonomies as $k0 => $formatTaxonomy) {
            if (!isset($formatTaxonomy['child'])) {
                continue;
            }

            foreach ($formatTaxonomy['child'] as $k1 => $childTaxonomy) {
                if (in_array($childTaxonomy['term_id'], $productTaxonomyIds)) {
                    $formatTaxonomies[$k0]['child'][$k1]['count']++;
                }
            }
        }
    }
}

usort($formatTaxonomies, function ($a, $b) {
    return (isset($a['order']) ? $a['order'] : '999') <=> (isset($b['order']) ? $b['order'] : '999');
});

$htmlFitter = '<div class="product__filter-collapse" data-filter-collapse>';
$htmlFitter .= '<div class="product__filter-wrap">';
$tIndex = 0;
foreach ($formatTaxonomies as $taxonomy) {
    $htmlFitterChild = "";
    if (isset($taxonomy['child']) && is_array($taxonomy['child'])) {
        usort($taxonomy['child'], function ($a, $b) {
            return (isset($a['order']) ? $a['order'] : '999') <=> (isset($b['order']) ? $b['order'] : '999');
        });
        foreach ($taxonomy['child'] as $taxonomyChild) {
            if ($taxonomyChild['count'] == 0) {
                continue;
            }
            $htmlFitterChild .= '<a class="--dark-mode filter" href="';
            $htmlFitterChild .=  add_query_arg("action", 'listing', add_query_arg("filter", $taxonomyChild['slug'], $current_url));
            $htmlFitterChild .= '" ';
            $htmlFitterChild .= '" title="' . $taxonomyChild['name'] . '">';
            $htmlFitterChild .= '<span>' . $taxonomyChild['name'] . ' (' . $taxonomyChild['count'] . ')</span>';
            $htmlFitterChild .= '</a>';
        }
    }
    if (!empty($htmlFitterChild)) {
        $htmlFitter .= '<div class="product__boxFilter';
        $htmlFitter .= $tIndex < 2 ? ' active' : '';
        $htmlFitter .= '" data-boxcollapse>';
        $htmlFitter .= '<div class="product__boxFilter-title" ';
        $htmlFitter .= 'data-boxcollapse-toggle>';
        $htmlFitter .= $taxonomy['name'];
        $htmlFitter .= '</div>';
        $htmlFitter .= '<div class="product__boxFilter-box">';
        $htmlFitter .= '<div class="product__boxFilter-list" data-filter-list>';
        $htmlFitter .= $htmlFitterChild;
        $htmlFitter .= '</div></div></div>';
        $tIndex++;
    }
}
$htmlFitter .= '</div></div>';

get_header(); ?>

    <main role="main">
        <div class="product-container" data-product>
            <div class="container">
                <div class="product__pageListing">
                    <div class="product__pageListing--row">
                        <div class="product__pageListing--colLeft">
                            <div class="product__groupTitle">
                                <div class="component-heading-group --dark-mode">
                                    <h2 class="component-heading-group__heading --size-lg">
                                        <?php _e('Actives');?>
                                    </h2>
                                    <div class="component-heading-group__desc">
                                        <?php echo $pageShortDes; ?>
                                    </div>
                                </div>
                            </div>
                            <div class="product__filter" data-filter>
                                <?php echo $htmlFitter; ?>
                            </div>
                        </div>
                        <div class="product__pageListing--colRight">
                            <?php the_content(); ?>
                            <?php
                            $listPostActive = array();
                            $index = 0;
                            foreach ($postsActive as $postActive) {
                                if ($postActive['data']['product_featured'] != 1) {
                                    continue;
                                }

                                if ($limitProduct > 0 && $index++ >= $limitProduct) {
                                    break;
                                }

                                $listPostActive[] = $postActive;
                            }
                            ?>
                            <?php if (count($listPostActive) > 0) : ?>
                            <div class="product__title-widget --mg-top-lg">Featured Actives</div>
                            <?php endif;?>
                            <div class="product__wrapping --mg-top-sm">
                                <?php if (count($listPostActive) > 0) : ?>
                                <div class="product__filter product__filter--mobile" data-filter>
                                    <div class="product__filter-toggle" data-filter-toggle><span
                                                class="open">Hide Filters</span><span class="close">Show all Categories & Filters</span>
                                    </div>
                                    <?php echo $htmlFitter; ?>
                                </div>
                                <div class="component-list-product">
                                    <?php
                                    foreach ($listPostActive as $postActive) {
                                        ?>
                                        <div class="component-list-product__item">
                                            <div class="component-list-product__wrap">
                                                <div class="component-list-product__title">
                                                    <h3><a href="<?php echo get_permalink($postActive['data']['id']) ?>"
                                                           title="<?php echo $postActive['data']['post_title']; ?>"
                                                           rel="stylesheet">
                                                            <?php echo $postActive['data']['post_title']; ?>
                                                            <?php if (!empty($postActive['data']['product_area']) && $postActive['data']['product_area'] == "global") :?>
                                                                <i class="icon icon-global-product"></i>
                                                            <?php endif;?>
                                                        </a>
                                                    </h3>
                                                </div>
                                                <div class="component-list-product__desc"><?php echo $postActive['data']['post_excerpt']; ?></div>
                                            </div>
                                        </div>
                                        <?php
                                    }
                                    ?>
                                </div>
                                <?php endif;?>
                            </div>
                            <div class="product__loadmore --mg-top-md"><a class="btn btn-regular --dark-mode"
                                                                          href="<?php echo add_query_arg("action", 'listing', $current_url); ?>"
                                                                          title="See All Actives"
                                                                          rel="stylesheet" data-see-more>See All
                                    Actives</a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="product-container" style="display: none" data-listing-page>
            <div class="container" id="app">
                <com-container :isdarkmode="true">
                    <com-heading-group slot="left" title="<?php _e('Actives');?>"
                                       desc="<?php echo esc_html($pageShortDes); ?>"></com-heading-group>
                    <com-filter slot="left"></com-filter>
                    <com-wrapping slot="right">
                        <?php the_content(); ?>
                    </com-wrapping>
                    <com-tab-list slot="right">                        
                        <com-tab-list-item name="active"></com-tab-list-item>
                    </com-tab-list>
                    <com-wrapping slot="right">
                        <com-filter-mobile></com-filter-mobile>
                       
                        <com-listing>
                            <com-list-product :hasborder="true" data="/wp-json/barnet/v1/data?type=barnet-product&sort=post_title&sort_none=1"
                                              filter="/wp-json/barnet/v1/taxonomies?type=product-category&meta_load_field=1"></com-list-product>
                            <com-load-more></com-load-more>
                        </com-listing>
                    </com-wrapping>
                   
                </com-container>
            </div>
        </div>
    </main>

<?php get_footer(); ?>