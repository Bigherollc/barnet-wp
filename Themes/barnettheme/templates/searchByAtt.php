<?php
/* Template Name: SearchByAtt */
/*
if (is_user_logged_in()) {
}
*/
$attribute="";
if(isset($_GET['q'])){
    $attribute = $_GET['q'];
}
$attributeNum = 0;
if( $attribute && $attribute!=""){
    if ( $post = get_page_by_path( $attribute, OBJECT, 'barnet-pattribute' ) ){
        $attributeNum = $post->ID;
    }
    else{
        $attributeNum = 0;
    }
}

function getProductList($productType, $pageNum, $posts_per_page, $attNum){
	$product_type_term = get_term_by('name', $productType, 'product-type');  
    $args = array(
        'posts_per_page' => $posts_per_page,
        'orderby' => "Date", 
        'order' => 'DESC',
        'post_type' =>  "barnet-product",
        'post_status' => 'publish',
        'paged' => $pageNum,
 	    'meta_query' => array(
			array(
				'key' => 'product_type_term',
				'value' =>  $product_type_term->term_id,
				'compare' => '='
			),
		),       
        'relationship' => array(
            'id' => 'products_to_pattributes',
            'from' => $attNum,
        ) 
    );
    $wp_query = new WP_Query($args);
    $count_all_post = $wp_query->found_posts;
    if ($wp_query->have_posts()) {
        while ($wp_query->have_posts()) {
            $wp_query->the_post();
            $post_id = get_the_ID();
            $post_title=get_the_title($post_id);
            $description_name="";
            if (is_user_logged_in()) {
                $description_name="product_description_logged";
            }
            else{
                
                $description_name="product_description";
            }
            $descriptions=get_post_meta($post_id, $description_name, true);  
            $product_type=get_post_meta($post_id, "product_type", true);
            $product_area=get_post_meta($post_id, "product_area", true);
            
            
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
                if (!is_user_logged_in()) {
                echo $descriptions;
                }
                else{
                    foreach($descriptions as $description){
                        ?>
                        <div>
                            <?php echo $description; ?>
                        </div>
                        <?php
                    }
                    
                }
                ?>
            </div>
        </div>
    </div>
        
    <?php 
        }
        wp_reset_query();
    }
    return $count_all_post;
    
}

get_header(); ?>

<main role="main" style="background:#fff;">
    <div class="search" >
        <input type="hidden" value="<?php echo admin_url('admin-ajax.php'); ?>" class="exi-ajax_url">
        <input type="hidden" value="<?php echo $attributeNum; ?>" class="exi-attr_num">
        <div class="container product_listing_container">
            <div class="product__listing" data-product-type="Active">
                <div class="component-list-product">
                    <?php $activeNum=getProductList("Active", 1, 20, $attributeNum);?>
                </div>
                <div class="product__loadmore">
                    <div class="product__loadmore-text">
                        Showing 
                        <span>
                            <?php if($activeNum>20){?>
                            <span class="page_product_list_num">
                            1-20
                            </span>
                            <?php } 
                            else {?>
                            <span class="page_product_list_num">
                            1-<?php echo $activeNum;?>
                            </span>                            
                            <?php } ?>
                            <span>
                            of 
                                <span class="total_item_num_attr"><?php echo $activeNum; ?>

                                </span>
                            </span>
                        </span>
                    </div> 
                    <div class="product__loadmore-btn">
                        <input type="hidden" name="page_number" id="page_number" value="1"/>
                        <a title="See More" class="btn btn-regular"  onclick="bt_seeMore_by_att('Active')">See More</a>
                    </div>
                </div>
            </div>
            <div class="product__listing " data-product-type="System" style="display:none;">
                <div class="component-list-product">
                    <?php $systemNum=getProductList("System", 1, 20, $attributeNum);?>
                </div>
                <div class="product__loadmore">
                    <div class="product__loadmore-text">
                        Showing 
                        <span>
                        <?php if($systemNum>20){?>
                            <span class="page_product_list_num">
                            1-20
                            </span>
                            <?php } 
                            else {?>
                            <span class="page_product_list_num">
                            1-<?php echo $systemNum;?>
                            </span>                            
                            <?php } ?>
                            <span>
                            of 
                                <span class="total_item_num_attr"><?php echo $systemNum; ?>

                                </span>
                            </span>
                        </span>
                    </div> 
                    <div class="product__loadmore-btn">
                        <input type="hidden" name="page_number" id="page_number" value="1"/>
                        <a title="See More" class="btn btn-regular" onclick="bt_seeMore_by_att('System')">See More</a>
                    </div>
                </div>
            </div>
            <div data-tab-list="" class="product__tabList component-tab-list product__tabList_by_attr"><!----> 
                <ul>
                    <li class="">
                        <a  data-tab-name="Active" title="active" class="active" onclick="bt_select_tab_by_attr('Active')">
                            active (<span data-tab-count=""><?php echo $activeNum; ?></span>)
                        </a>
                    </li> 
                    <li class="">
                        <a  data-tab-name="System" title="system" class="" onclick="bt_select_tab_by_attr('System')">
                            system (<span data-tab-count=""><?php echo $systemNum; ?></span>)
                        </a>
                    </li> 
                </ul>
            </div>            
        </div>
    </div>
</main>

<?php get_footer(); ?>
