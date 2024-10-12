<?php
/* Template Name: Resource Landing Page temp*/
$starttime = microtime(true);
if (isset($_REQUEST['_debug'])) {
    print_r("Start Time: $starttime\n");
}
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
    'taxonomy' => 'resource-type',
    'hide_empty' => false
);

$fixedTime = microtime(true);
$taxonomies = get_terms($args);
if (isset($_REQUEST['_debug'])) {
    print_r("Get Terms: " . (microtime(true) - $fixedTime) . "\n");
}
$fixedTime = microtime(true);
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

if (isset($_REQUEST['_debug'])) {
    print_r("Format Taxonomy: " . (microtime(true) - $fixedTime) . "\n");
}
/**************************************** Product List **************************************/

$fixRequest = $_REQUEST;
//$fixRequest['exclude'] = 'application/pdf';
$barnetRestAPI = new BarnetRestAPI();

$fixedTime = microtime(true);
$resourceLanding = $barnetRestAPI->getResourceLanding($fixRequest);
if (isset($_REQUEST['_debug'])) {
    print_r("Get Resource Landing: " . (microtime(true) - $fixedTime) . "\n");
}
$fixedTime = microtime(true);
$resources = $barnetRestAPI->getResourcesTax($fixRequest);
if (isset($_REQUEST['_debug'])) {
    print_r("Get Resources: " . (microtime(true) - $fixedTime) . "\n");
}
$fixedTime = microtime(true);
$formatResourceLanding = array();

foreach ($resources as $resource) {
    $resourceTaxonomyIds = array_map(function ($e) {
        return is_array($e) ? $e['term_id'] : $e->term_id;
    }, $resource['taxonomies']);

    foreach ($formatTaxonomies as $k0 => $formatTaxonomy) {
        if (!isset($formatTaxonomy['child'])) {
            continue;
        }

        foreach ($formatTaxonomy['child'] as $k1 => $childTaxonomy) {
            if (in_array($childTaxonomy['term_id'], $resourceTaxonomyIds)) {
                $formatTaxonomies[$k0]['child'][$k1]['count']++;
            }
        }
    }
}
if (isset($_REQUEST['_debug'])) {
    print_r("Format Resources: " . (microtime(true) - $fixedTime) . "\n");
}
$fixedTime = microtime(true);
foreach ($resourceLanding as $resource) {
    /** @var WP_Term $resourceTaxonomy */
    foreach ($resource['taxonomies'] as $resourceTaxonomy) {
        if ($resourceTaxonomy->is_showed == 0) {
            continue;
        }

        if (!isset($formatResourceLanding[$resourceTaxonomy->term_id]['title'])) {
            $formatResourceLanding[$resourceTaxonomy->term_id]['title'] = $resourceTaxonomy->name;
        }
        $formatResourceLanding[$resourceTaxonomy->term_id]['order'] = isset($resourceTaxonomy->order) ? $resourceTaxonomy->order : null;

        if (!isset($formatResourceLanding[$resourceTaxonomy->term_id]['slug'])) {
            $formatResourceLanding[$resourceTaxonomy->term_id]['slug'] = $resourceTaxonomy->slug;
        }

        $formatResourceLanding[$resourceTaxonomy->term_id]['data'][] = $resource['data'];
    }

}
usort($formatResourceLanding, function ($a, $b) {
    return (isset($a['order']) ? $a['order'] : '99999') <=> (isset($b['order'] ) ? $b['order'] : '99999');
});

if (isset($_REQUEST['_debug'])) {
    print_r("Format Resources Landing: " . (microtime(true) - $fixedTime) . "\n");
}

$fixedTime = microtime(true);
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
        foreach ($taxonomy['child'] as $taxonomyChild) {
            $allPost = $barnetRestAPI->getResourcesByTerm($taxonomyChild['taxonomy'], $taxonomyChild['term_id'], 'barnet-resource');
            if ($taxonomyChild['count'] == 0) {
                continue;
            }

            if (!$isShowFilter) {
                $isShowFilter = true;
            }

            /*
            $_htmlFitter .= '<a class="--dark-mode filter" href="';
            $_htmlFitter .= add_query_arg("action", 'listing', add_query_arg("filter", $taxonomyChild['slug'], $current_url));
            $_htmlFitter .= '" ';
            $_htmlFitter .= '" title="' . $taxonomyChild['name'] . '">';
            $_htmlFitter .= '<span>' . $taxonomyChild['name'] . ' (' . count($allPost) . ')</span>';
            $_htmlFitter .= '</a>';
            */

            $_htmlFitter .= '<div class="product-filter-wrapp"><input  class="--dark-mode click-filter" onclick="clickResourceFilter()" type="checkbox" name="'.
            $taxonomyChild['name'].'" taxonomy="'.$taxonomy['taxonomy'].'" data-slug="'.$taxonomyChild['slug'].'" value="'.$taxonomyChild['slug'].'">';
            $_htmlFitter .= '<span class="checkMark"></span><span class="product-filter-title">' . 
            $taxonomyChild['name'] . ' (' .  count($allPost) . ')</span></div>';
        }
    }
    $_htmlFitter .= '</div></div></div>';

    if ($isShowFilter) {
        $htmlFitter .= $_htmlFitter;
        $tIndex++;
    }
}
$htmlFitter .= '</div></div>';
if (isset($_REQUEST['_debug'])) {
    print_r("HTML Taxonomy: " . (microtime(true) - $fixedTime) . "\n");
}

$endtime = microtime(true);
if (isset($_REQUEST['_debug'])) {
    print_r("PreHeader: " . ($endtime - $starttime) . "\n");
}
get_header(); ?>

<main role="main">
    <div class="product-container" data-product>
        <input type="hidden" value="<?php echo admin_url('admin-ajax.php'); ?>" class="exi-ajax_url">
        <input type="hidden" value="resource" id="barnet-login-landing-page">
        <div  id="loadmre_loading_img" ><img src="<?php echo get_template_directory_uri()?>/assets/images/loadmore.gif" alt="Loading"/></div>
        <div class="container">
            <div class="product__pageListing">
                <div class="product__pageListing--row">
                    <div class="product__pageListing--colLeft">
                        <div class="product__groupTitle">
                            <div class="component-heading-group --dark-mode">
                                <h2 class="component-heading-group__heading --size-lg">
                                    <?php _e('Resources');?>
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
                        <div class="product__wrapping --mg-top-lg">
                            <div class="product__filter product__filter--mobile" data-filter>
                                <div class="product__filter-toggle" data-filter-toggle><span
                                            class="open">Hide Filters</span><span
                                            class="close">Show all Categories & Filters</span></div>
                                <?php echo $htmlFitter; ?>
                            </div>
                            <div class="product__resourceLanding">
                                <?php
                                $isFirstItem = true;
                                foreach ($formatResourceLanding as $resource) {
                                    $resourceCount = 0;
                                    ?>
                                    <div class="component-related-video --bg-green <?php echo $isFirstItem ? "" : "--mg-top-ml"; ?>">
                                        <div class="component-related-video__wrapper">
                                            <div class="component-related-video__group-title">
                                                <div class="component-heading-group">
                                                    <h2 class="component-heading-group__heading"><?php echo $resource['title']; ?>
                                                    </h2>
                                                </div>
                                                <div class="component-related-video__cta"><a class="btn btn-regular"
                                                                                             href="<?php echo add_query_arg("action", 'listing', add_query_arg("filter",$resource['slug'], $current_url));?>"
                                                                                             title="See More"
                                                                                             data-see-more>See
                                                        More</a>
                                                </div>                                        
                                            </div>
                                            <div class="component-related-video__item slider-control"
                                                 data-slider-main
                                                 data-opts-slider='{"slickContainer": ".component-related-video","optsSlick": {"slide": "[data-slider-item]","dots": true, "rows": 0, "slidesToShow": 4,  "slidesToScroll": 4, "responsive":[{"breakpoint": 991, "settings": {"slidesToShow": 3,  "slidesToScroll": 3}},{"breakpoint": 768, "settings": {"slidesToShow": 2,  "slidesToScroll": 2}},{"breakpoint": 376, "settings": {"slidesToShow": 1,  "slidesToScroll": 1}}]}}'>
                                                <?php
                                               $resourcefiltered = array_filter($resource['data'], function ($var) {
                                               return ($var['resource_show_see_more_before'] == '1');
                                                });
                                                //sort by order
                                                usort($resourcefiltered, function($a, $b) {
                                                    return (isset($a['resource_order']) ? $a['resource_order'] : '99999') <=> (isset($b['resource_order'] ) ? $b['resource_order'] : '99999');
                                                });
                                                //sort alphabetically if order same
                                                usort($resourcefiltered, function($a, $b) {
                                                   if ($a['resource_order']==$b['resource_order']) {
                                                       return strcmp($a['post_title'],$b['post_title']);
                                                    }
                                               });
                                                foreach($resourcefiltered as $resourceData) {
                                                    if ($resourceCount++ > 2) {
                                                        break;
                                                    }
                                                    $timeMedia = $resourceData['resource_other_attribute']['length_formatted'];
                                                    ?>
                                                    <div class="component-related-video__col" data-slider-item><a
                                                                class="component-related-video__img<?php if (strpos($resourceData['resource_media_type'], 'video') !== false) {echo ' btn-gtm-view-video';} else {echo '';}
                                                                ?>"
                                                                target="_blank"
                                                                <?php if($resourceData['resource_media_type']=='application/pdf'){?>href="<?php echo $resourceData['media_external_url']; ?>" <?php }else{?>href="barnet-resource/<?php echo $resourceData['post_name']; ?>"<?php } ?>
                                                                title="<?php echo $resourceData['post_title']; ?>">
                                                                <img
                                                                    src="<?php echo $resourceData['resource_image_url']; ?>"
    
                                                                    alt="<?php echo $resourceData['post_title']; ?>"></a>
                                                        <div class="component-related-video__content"><a
                                                                    class="component-related-video__title<?php if (strpos($resourceData['resource_media_type'], 'video') !== false) {echo ' btn-gtm-view-video';} else {echo '';}
                                                                ?>"
                                                                    href="barnet-resource/<?php echo $resourceData['post_name']; ?>"
                                                                    title="<?php echo $resourceData['post_title']; ?>"><?php echo $resourceData['post_title']; ?></a>
                                                            <?php if ($resourceData['resource_media_type'] == 'video/mp4') {
                                                                ?>
                                                                <div class="component-related-video__time">
                                                                    <i class="icon icon-clock-sm"></i><?php echo $timeMedia; ?>
                                                                </div>
                                                                <?php 
                                                            } ?>
                                                        </div>
                                                    </div>
                                                 <?php
                                                }
                                                ?>
                                            </div>
                                        </div>
                                    </div>
                                    <?php
                                    $isFirstItem = false;
                                }
                                ?>
                            </div>
                            <div class="product__listing" style="display:none;">
                                <input type="hidden" name="page_number" id="page_number" value="0"/>
                                <div class="component-list-resource">
                                    <div class="component-related-video row">
                                        <div class="component-related-video__col col-12 col-sm-6 col-md-4">
                                            <a href="http://localhost/barnet-resource/abermat-pa-sustainable-snapshot" title="Abermat PA Sustainable Snapshot" class="component-related-video__img">
                                                <img src="http://localhost/wp-content/themes/barnettheme/assets/images/default.png" title="Abermat PA Sustainable Snapshot" alt="image">
                                            </a> 
                                            <div class="component-related-video__content">
                                                <a href="http://localhost/barnet-resource/abermat-pa-sustainable-snapshot" title="Abermat PA Sustainable Snapshot" class="component-related-video__title">Abermat PA Sustainable Snapshot</a> <!---->
                                            </div>
                                        </div>
                                    </div>
                                    <div class="product__loadmore">
                                        <div class="product__loadmore-text">Showing 
                                            <span class="resource-showing-numbers">1-20
                                                <span> of 
                                                    <span class="resource-total-numbers">142</span>
                                                </span>
                                            </span>
                                        </div> 
                                        <div class="product__loadmore-btn">
                                            <a title="See More" class="btn btn-regular">See More</a>
                                        </div>
                                    </div>
                                </div>
                            </div>                           
                            <div id="see-all-products-btn" class="product__loadmore --mg-top-md"><a class="btn btn-regular --dark-mode"
                                    onclick="seeAllResource()" 
                                   title="<?php _e('See All Resources'); ?>" rel="stylesheet"
                                   ><?php _e('See All Resources'); ?></a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!--
    <div class="product-container" style="display: none" data-listing-page>
        <div class="container" id="app">
            <com-container :isdarkmode="true">
                <com-heading-group slot="left" title="<?php _e('Resources');?>"
                                   desc="<?php echo esc_html($pageShortDes); ?>"></com-heading-group>
                <com-filter slot="left"></com-filter>
                <com-tab-list class="d-none" slot="right">
                    <com-tab-list-item name="resource"></com-tab-list-item>
                </com-tab-list>
                <com-wrapping slot="right">
                    <com-filter-mobile></com-filter-mobile>
                    <com-listing>
                        <com-list-resource data="/wp-json/barnet/v1/resources"
                                           filter="/wp-json/barnet/v1/taxonomies?type=resource-type"></com-list-resource>
                        <com-load-more></com-load-more>
                    </com-listing>
                </com-wrapping>
            </com-container>
        </div>
    </div>-->
</main>

<?php get_footer(); ?>
