<?php
/* Template Name: All Products */


global $wp;
$user = new UserEntity();
if ($user->getId()) {
    wp_redirect('/');
}

global $post;
$pageId = $post->ID;
$pageTitle = $post->post_title;
$pageMetas = get_post_meta($pageId);
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
$first_type = $product_types[0];
$pageShortDes = '';
$postsItems=[];
$htmlFitter=[];

foreach($product_types as $product_type){
    $postsItems[$product_type]=[];
    $htmlFitter[$product_type]="";
}
/*
$postsItems['active']=[];
$postsItems['system']=[];
$postsItems['Pigments']=[];

$htmlFitter['active']="";
$htmlFitter['system']="";
$htmlFitter['Pigments']="";

$total_categories = get_terms( array(
    'taxonomy' => 'product-category',
    'hide_empty' => false, // Set to true if you only want to retrieve non-empty terms
) );
*/
$count_all_post=[];
if (isset($pageMetas["p_short_description"]) && is_array($pageMetas["p_short_description"]) && count($pageMetas["p_short_description"]) > 0) {
    $pageShortDes = $pageMetas["p_short_description"][0];
}

//$product_types=['active','system','Pigments'];
foreach($product_types as $product_type){
    $product_type_term = get_term_by('name', $product_type, 'product-type');
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
/*
//print_r($taxonomies);
$taxonomies=[];
foreach ( $total_categories as $term ) {
    // Query posts connected to the 'active' term in 'product-type' taxonomy
    $connected_posts = new WP_Query( array(
        'post_type' => 'barnet-product', // Replace 'your_post_type' with your actual post type
        'tax_query' => array(
            'relation' => 'AND',
            array(
                'taxonomy' => 'product-category',
                'field' => 'term_id',
                'terms' => $term->term_id, // Term ID from 'product-category'
            ),
            array(
                'taxonomy' => 'product-type',
                'field' => 'name',
                'terms' => $product_type, // Term to query against in 'product-type'
            ),
        ),
    ) );

    // Check if posts are found for the term
    if ( $connected_posts->have_posts() ) {
        $taxonomies[]=$term;
    }
    wp_reset_postdata();
}


// Reset post data


$formatTaxonomies = [];
foreach ($taxonomies as $taxonomy) {
    if ($taxonomy->parent == 0) {
        $formatTaxonomies[$taxonomy->term_id] = $taxonomy->to_array();
    }
}

foreach ($taxonomies as $taxonomy) {
    if ($taxonomy->parent != 0) {
        $taxArray = $taxonomy->to_array();
        $taxArray['count'] = 0;
        if(isset($formatTaxonomies[$taxonomy->parent]))$formatTaxonomies[$taxonomy->parent]['child'][] = $taxArray;
        else{
            $formatTaxonomies[$taxonomy->parent]=get_term( $taxonomy->parent )->to_array();
            $formatTaxonomies[$taxonomy->parent]['child'][] = $taxArray;
        }
    }
}
*/


/**************************************** Product List **************************************/
$limitProduct = -1;
$args = array(
    'post_type' => 'barnet-product',
    'post_status' => 'publish',
    'posts_per_page' => -1,
    'orderby' => 'post_title',
    'order' => 'ASC',
    'meta_query' => array(
        'relation' => 'AND',
        array(
            'key' => 'product_only_for_code_list',
            'value' => '0',
            'compare' => 'like',
        ),
        array(
            'key' => 'product_type_term',
            'value' =>  $product_type_term->term_id,
            'compare' => '='
        ),
    ),
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


$queryPostsItems = new WP_Query($args);

$count_all_post[$product_type] = $queryPostsItems->found_posts;
$postsItemsDefault = $queryPostsItems->posts;
wp_reset_query();
$postsItems[$product_type] = array();
$metaPostManager = new BarnetPostMetaManager($postsItemsDefault);
$relationshipManager = new BarnetRelationshipManager();
$relationshipManager->syncTerm();
foreach ($postsItemsDefault as $postItemsDefault) {
    //Remove product on list
    $product_only_for_code_list = 0;
    $product_only_for_code_list = get_post_meta(intval($postItemsDefault->ID), 'product_only_for_code_list', TRUE);
    if (intval($product_only_for_code_list) == 1) {
        
        continue;
    }
    $productEntity = new ProductEntity(
        $postItemsDefault->ID,
        true,
        array(
            'post' => $postItemsDefault,
            'meta' => $metaPostManager->getMetaData($postItemsDefault->ID)
        )
    );
    $productEntity->setRelationshipManager($relationshipManager);
    if ($productEntity->checkRoleAndRegion()) {
        $postsItems[$product_type][] = $productEntity->toArray(BarnetEntity::$PUBLIC_LANDING);
       
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
    else{
       // print_r($productEntity);
    }
}



usort($formatTaxonomies, function ($a, $b) {
    return (isset($a['order']) ? $a['order'] : '999') <=> (isset($b['order']) ? $b['order'] : '999');
});

$htmlFitter[$product_type] = '<div class="product__filter-collapse" data-filter-collapse>';
$htmlFitter[$product_type] .= '<div class="product__filter-wrap">';
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
            
            $htmlFitterChild .= '<div class="product-filter-wrapp"><input  class="--dark-mode click-filter" onclick="clickfilterByall(\''.$product_type.'\')" type="checkbox" name="'.$taxonomyChild['name'].
            '" taxonomy="'.$taxonomy['taxonomy'].'" data-slug="'.$taxonomyChild['slug'].'" value="'.$taxonomyChild['slug'].'">';
            $htmlFitterChild .= '<span class="checkMark"></span><span class="product-filter-title">' . $taxonomyChild['name'] . ' (' . $taxonomyChild['count'] . ')</span></div>';
        }
    }
   
    if (!empty($htmlFitterChild)) {
        $htmlFitter[$product_type] .= '<div class="product__boxFilter';
        $htmlFitter[$product_type] .= $tIndex < 2 ? ' Items' : '';
        $htmlFitter[$product_type] .= '" data-boxcollapse>';
        $htmlFitter[$product_type] .= '<div class="product__boxFilter-title" ';
        $htmlFitter[$product_type] .= 'data-boxcollapse-toggle>';
        $htmlFitter[$product_type] .= $taxonomy['name'];
        $htmlFitter[$product_type] .= '</div>';
        $htmlFitter[$product_type] .= '<div class="product__boxFilter-box">';
        $htmlFitter[$product_type] .= '<div class="product__boxFilter-list" data-filter-list>';
        $htmlFitter[$product_type] .= $htmlFitterChild;
        $htmlFitter[$product_type] .= '</div></div></div>';
        $tIndex++;
    }
}
$htmlFitter[$product_type] .= '</div></div>';
}

get_header(); ?>

    <main role="main">
        
        <div class="product-container" data-product>
            <div class="container">
                <input type="hidden" value="<?php echo admin_url('admin-ajax.php'); ?>" class="exi-ajax_url">
                <div  id="loadmre_loading_img" ><img src="<?php echo get_template_directory_uri()?>/assets/images/loadmore.gif" alt="Loading"/></div>
                <div class="product__pageListing">
                    <div class="product__pageListing--row">
                        <div class="product__pageListing--colLeft">
                            <div class="product__groupTitle">
                                <div class="component-heading-group --dark-mode">
                                    <h2 class="component-heading-group__heading --size-lg">
                                        <?php _e('All Products');?>
                                    </h2>
                                    <div class="component-heading-group__desc">
                                        <?php echo $pageShortDes; ?>
                                    </div>
                                </div>
                            </div>
                            <?php foreach($product_types as $product_type){ ?>
                            <div class="product__filter" data-filter>
                               <div class="prduct_fliter_box_wrapper_" <?php if($product_type!=$first_type){?> style="display: none;"<?php }?>
                               data-product-type="<?php echo $product_type;?>">
                                 <?php echo $htmlFitter[$product_type]; ?>
                                </div>
                            </div>
                            <?php }?>
                        </div>
                        <div class="product__pageListing--colRight">
                            <?php the_content(); ?>
                            <?php
                            $listPostItems = array();
                            foreach($product_types as $product_type){
                                $listPostItems[$product_type]=[];
                            }
                            //$listPostItems['active']=[];
                            //$listPostItems['system']=[];
                            //$listPostItems['Pigments']=[];
                            foreach($postsItems as $key => $postsItems_pt)
                            {
                                $index = 0;
                                foreach ($postsItems_pt as $postItems) {
                                    if ($limitProduct > 0 && $index++ >= $limitProduct) {
                                        break;
                                    }

                                    $listPostItems[$key][] = $postItems;
                                }
                             }
                            ?>

                            <div class="product__wrapping --mg-top-sm">
                                <div data-tab-list="" class="product__tabList component-tab-list product__tabList_by_allProduct"><!----> 
                                    <ul>
                                        <?php  foreach($product_types as $product_type) { ?>
                                        <li class="">
                                            <a  data-tab-name="<?php echo $product_type;?>" title="<?php echo $product_type;?>" class="<?php if($product_type==$first_type){ ?>active<?php }?>"
                                             onclick="bt_select_tab_by_prduct_type('<?php echo $product_type;?>')">
                                            <?php echo $product_type;?> (<span data-tab-count=""><?php echo $count_all_post[$product_type]; ?></span>)
                                            </a>
                                        </li> 
                                        <?php } ?>
                                    </ul>
                                </div>

                                
                                <div class="product__filter product__filter--mobile" data-filter >
                                    <div class="product__filter-toggle" data-filter-toggle><span
                                                class="open">Hide Filters</span><span class="close">Show all Categories & Filters</span>
                                    </div>
                                    <?php
                                    foreach($product_types as $product_type){ 
                                     if (count($listPostItems) > 0) :
                                     ?>                                  
                                    <div class="prduct_fliter_box_wrapper_" <?php if($product_type!=$first_type){?> style="display: none;"<?php }?> 
                                    data-product-type="<?php echo $product_type;?>">
                                        <?php echo $htmlFitter[$product_type]; ?>
                                     </div>
                                     <?php endif;?>
                                     <?php } ?>                                    
                                </div>

                                <div class="product__signIn">
                                    <div class="component-signin-box" style="background-image: url(&quot;<?php echo get_template_directory_uri()?>/assets/images/product/bg-signin.png&quot;);">
                                    <div class="component-signin-box__title">Sign In for Details</div> 
                                    <div class="component-signin-box__box">
                                        <div class="component-signin-box__signin">
                                            <div class="component-signin-box__text">Registered users get access to full product information and related resources:
                                            </div> 
                                            <div class="component-signin-box__list">
                                                <ul class="component-list">
                                                    <li>Specifications &amp; Data Sheets</li> 
                                                    <li>Videos &amp; Presentations</li> 
                                                    <li>Starting Formulas</li>
                                                </ul>
                                            </div> 
                                            <div class="component-signin-box__link">
                                                <a href="/login" title="Sign in" class="btn btn-normal --dark-mode">Sign in</a>
                                            </div>
                                        </div> 
                                        <div class="component-signin-box__request">
                                            <div class="component-signin-box__titleRequest">New Customer?</div>
                                            <a title="Request Access" href="/register" class="btn btn-solid --dark-mode btn-gtm-request-access">Request Access</a>
                                        </div>
                                    </div>
                                </div>
                            
                                <?php
                                foreach($product_types as $product_type){ 
                                     if (count($listPostItems) > 0) :
                                     ?>
                                <div class="product__listing" data-product-type="<?php echo $product_type;?>" <?php if($product_type!=$first_type){?> style="display: none;"<?php }?>>
                                    <input type="hidden" name="page_number" id="page_number" value="1"/>
                                    <div class="component-list-product">
                                        <?php
                                        $i=1;
                                        foreach ($listPostItems[$product_type] as $postItems) {
                                           if($i>20) break;
                                           $i++;
                                            $id=$postItems['data']['id'];
                                            $post_title=$postItems['data']['post_title'];
                                            $product_area=$postItems['data']['product_area'];
                                            $descriptions=$postItems['data']['product_description'];
                                            
                                            ?>
                                            <div class="component-list-product__item">
                                                <div class="component-list-product__wrap">
                                                    <div class="component-list-product__title">
                                                        <h3>
                                                            <a href="<?php echo get_permalink( $id );?>" title="<?php echo $post_title;?>" rel="stylesheet">
                                                                <?php echo $post_title;?>
                                                                <?php if($product_area=="global") {?>
                                                                <i class="icon icon-global-product"></i>
                                                                <?php } ?>
                                                            </a> <!---->
                                                        </h3>
                                                    </div> 
                                                    <div class="component-list-product__desc">
                                                        <?php 
                                                        echo $descriptions;
                                                        ?>
                                                    </div>
                                                </div>
                                            </div>
                                            <?php
                                        }
                                        ?>
                                            <div class="product__loadmore" id="see-more-producs-btn">
                                                <div class="product__loadmore-text">
                                                    Showing 
                                                    <span>
                                                        <?php if($count_all_post[$product_type]>20){?>
                                                        <span class="page_product_list_num">
                                                        1-20
                                                        </span>
                                                        <?php } 
                                                        else {?>
                                                        <span class="page_product_list_num">
                                                        1-<?php echo $count_all_post[$product_type];?>
                                                        </span>                            
                                                        <?php } ?>
                                                        <span>
                                                        of 
                                                            <span class="total_item_num_attr"><?php echo $count_all_post[$product_type]; ?>

                                                            </span>
                                                        </span>
                                                    </span>
                                                </div> 
                                                <?php if($count_all_post > 20){?>
                                                <div class="product__loadmore-btn">
                                                    <a title="See More" class="btn btn-regular"  onclick="filteringProductByAll('<?php echo $product_type ?>')">See More</a>
                                                </div>
                                                <?php }?>
                                            </div>
                                    </div>
                                </div>
                              
                                <?php endif;?>
                                <?php } ?>

  
                             
                            </div>


                        </div>

                    </div>
                </div>
            </div>
        </div>		
    </main>

<?php get_footer(); ?>