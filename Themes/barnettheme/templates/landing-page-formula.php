<?php
/* Template Name: Formula Landing Page */
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

$args = array(
    'taxonomy' => 'formula-category',
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

/**************************************** Formula List **************************************/
$limitFormula = -1;
$args = array(
    'post_type' => 'barnet-formula',
    'post_status' => 'publish',
    'posts_per_page' => -1,
    'orderby' => 'post_title',
    'order' => 'ASC',
);

if (isset($_REQUEST['ts'])) {
    $args['tax_query'] = array(
        array(
            'taxonomy' => 'formula-category',
            'field' => 'slug',
            'terms' => $_REQUEST['ts'],
            'operator' => 'IN'
        )
    );
}

$queryPostsFormula = new WP_Query($args);
$postsFormulaDefault = $queryPostsFormula->posts;
$postsFormula = array();
$countIndex = 0;

$metaPostManager = new BarnetPostMetaManager($postsFormulaDefault);
$relationshipManager = new BarnetRelationshipManager();
$relationshipManager->syncTerm();
foreach ($postsFormulaDefault as $postFormulaDefault) {
    $formulaItem = new FormulaEntity(
        $postFormulaDefault->ID,
        true,
        array(
            'post' => $postFormulaDefault,
            'meta' => $metaPostManager->getMetaData($postFormulaDefault->ID)
        )
    );
    $formulaItem->setRelationshipManager($relationshipManager);
    if ($formulaItem->checkRoleAndRegion()) {
        $postsFormula[] = $formulaItem;
        $countIndex++;
        $formulaTaxonomies = $formulaItem->getTaxonomies();
        $formulaTaxonomyIds = array_map(function ($e) {
            return is_array($e) ? $e['term_id'] : $e->term_id;
        }, $formulaTaxonomies);

        foreach ($formatTaxonomies as $k0 => $formatTaxonomy) {
            if (!isset($formatTaxonomy['child'])) {
                continue;
            }

            foreach ($formatTaxonomy['child'] as $k1 => $childTaxonomy) {
                if (in_array($childTaxonomy['term_id'], $formulaTaxonomyIds)) {
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
    $isShowFilter = false;
    $_htmlFitter = '<div class="product__boxFilter';
    $_htmlFitter .= $tIndex < 2 ? ' active' : '';
    $_htmlFitter .= '" data-boxcollapse>';
    $_htmlFitter .= '<div class="product__boxFilter-title" ';
    $_htmlFitter .= 'data-boxcollapse-toggle>';
    $_htmlFitter .= empty($taxonomy['name']) ? '' : $taxonomy['name'];
    $_htmlFitter .= '</div>';
    $_htmlFitter .= '<div class="product__boxFilter-box">';
    $_htmlFitter .= '<div class="product__boxFilter-list" data-filter-list>';
    if (!empty($taxonomy['child'])) {
        usort($taxonomy['child'], function ($a, $b) {
            return (isset($a['order']) ? $a['order'] : '999') <=> (isset($b['order']) ? $b['order'] : '999');
        });
        foreach ($taxonomy['child'] as $taxonomyChild) {
            if ($taxonomyChild['count'] == 0) {
                continue;
            }

            if (!$isShowFilter) {
                $isShowFilter = true;
            }

            $_htmlFitter .= '<a class="--dark-mode filter" href="';
            $_htmlFitter .=  add_query_arg("action", 'listing', add_query_arg("filter", $taxonomyChild['slug'], $current_url));
            $_htmlFitter .= '" ';
            $_htmlFitter .= '" title="' . $taxonomyChild['name'] . '">';
            $_htmlFitter .= '<span>' . $taxonomyChild['name'] . ' (' . $taxonomyChild['count'] . ')</span>';
            $_htmlFitter .= '</a>';
        }
    }
    $_htmlFitter .= '</div></div></div>';

    if ($isShowFilter) {
        $htmlFitter .= $_htmlFitter;
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
                                    <?php _e('Starting Formulas');?>
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
                        $listPostFormula = array();
                        $index = 0;
                        foreach ($postsFormula as $postFormula) {
                            if ($postFormula->getFormulaFeatured() != 1) {
                                continue;
                            }

                            if ($limitFormula > 0 && $index++ >= $limitFormula) {
                                break;
                            }

                            $listPostFormula[] = $postFormula;
                        }
                        ?>
                        <?php if (count($listPostFormula) > 0) : ?>
                        <div class="product__title-widget --mg-top-lg"><?php _e('Featured Formulas');?></div>
                        <?php endif;?>
                        <div class="product__wrapping --mg-top-sm">
                            <?php if (count($listPostFormula) > 0) : ?>
                            <div class="product__filter product__filter--mobile" data-filter>
                                <div class="product__filter-toggle" data-filter-toggle><span
                                            class="open">Hide Filters</span><span
                                            class="close">Show all Categories & Filters</span></div>
                                <?php echo $htmlFitter; ?>
                            </div>
                            <div class="component-list-product">
                                <?php
                                $index = 0;
                                /** @var FormulaEntity $postFormula */
                                foreach ($listPostFormula as $postFormula) {

                                    $iconItem = $postFormula->getFormulaIconBlack();
                                    $linkItem = $postFormula->getPermalink();
                                    ?>
                                    <div class="component-list-product__item --has-image">
                                        <?php if (!empty($iconItem)) : ?>
                                            <div class="component-list-product__image">
                                                <a href="<?php echo $linkItem; ?>"
                                                   title="<?php echo esc_html($postFormula->getPostTitle()); ?>"
                                                   rel="stylesheet">
                                                    <img src="<?php echo $iconItem; ?>"
                                                         title="<?php echo esc_html($postFormula->getPostTitle()); ?>">
                                                </a>
                                            </div>
                                        <?php endif; ?>
                                        <div class="component-list-product__wrap">
                                            <div class="component-list-product__title">
                                                <h3>
                                                    <a href="<?php echo $linkItem; ?>"
                                                       title="<?php echo esc_html($postFormula->getPostTitle()); ?>"
                                                       rel="stylesheet"><?php echo $postFormula->getPostTitle(); ?></a>
                                                </h3>
                                            </div>
                                            <div class="component-list-product__desc">
                                                <?php echo $postFormula->getPostExcerpt(); ?>
                                            </div>
                                        </div>
                                    </div>
                                    <?php
                                }
                                ?>
                            </div>
                            <?php endif;?>
                            <div class="product__loadmore --mg-top-md">
                                <a class="btn btn-regular --dark-mode" href="<?php echo add_query_arg("action", 'listing', $current_url); ?>" title="See All"
                                   rel="stylesheet" data-see-more>See All Formulas</a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="product-container" style="display: none" data-listing-page>
        <div class="container" id="app">
            <com-container :isdarkmode="true">
                <com-heading-group slot="left" title="<?php _e('Starting Formulas');?>"
                                   desc="<?php echo esc_html($pageShortDes); ?>"></com-heading-group>
                <com-filter slot="left"></com-filter>
                <com-tab-list class="d-none" slot="right">
                    <com-tab-list-item name="formula"></com-tab-list-item>
                </com-tab-list>
                <com-wrapping slot="right">
                    <com-filter-mobile></com-filter-mobile>
                    <com-listing>
                        <com-list-formula data="/wp-json/barnet/v1/data?type=barnet-formula&sort=post_title&sort_none=1"
                                          filter="/wp-json/barnet/v1/taxonomies?type=formula-category&meta_load_field=1"></com-list-formula>
                        <com-load-more></com-load-more>
                    </com-listing>
                </com-wrapping>
            </com-container>
        </div>
    </div>
</main>

<?php get_footer(); ?>
