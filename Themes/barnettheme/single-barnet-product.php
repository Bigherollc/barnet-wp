<?php

global $post;

$product = new ProductEntity($post->ID, true, array('post' => $post));
if (!$product->checkRoleAndRegion()) {
    wp_redirect('/');
}

$digitalCodes = $product->getRelationship(array('products_to_digital-code'));
$digitalCodeStr = '';

$display_inci = 0;
$display_inci = get_post_meta($post->ID, 'product_display_inci', TRUE);

if (isset($digitalCodes['digital-code']) && is_array($digitalCodes['digital-code']) && count($digitalCodes['digital-code']) > 0) {
    $userCurrent = $product->getUser();
    $userCurrentRoles = isset($userCurrent['role']) ? $userCurrent['role'] : array();
    foreach ($userCurrentRoles as $k => $r) {
        $userCurrentRoles[$k] = strtolower($r);
    }
    if (!in_array('barnet sales', $userCurrentRoles)) {
        foreach ($digitalCodes['digital-code'] as $digitalCodeObject) {
            $digitalTitle = $digitalCodeObject['data']['post_title'];
            $digitalCode = isset($digitalCodeObject['data']['digital_code']) ? $digitalCodeObject['data']['digital_code'] : $digitalCodeObject['data']['digital_code'];

            $digitalId = intval(isset($digitalCodeObject['data']['id']) ? $digitalCodeObject['data']['id'] : $digitalCodeObject['data']['id']);
            if ($digitalId > 0) {
                $digital = new DigitalCodeEntity($digitalId);
                $digitalCodeCustomer = $digital->getRelationship(array('digitals_to_customers'), true);
                if (isset($digitalCodeCustomer['customers'][0])) {
                    $customerObject = $digitalCodeCustomer['customers'][0];
                    $customerId = intval(isset($customerObject['data']['id']) ? $customerObject['data']['id'] : $customerObject['data']['id']);
                    if ($customerId > 0) {
                        $digitalCodeStrTmp = $customerObject['data']['post_title'] . ": $digitalCode";
                        if (!empty($digitalCodeStr)) {
                            $digitalCodeStrTmp = "<br />" . $digitalCodeStrTmp;
                        }
                        $customerMeta = get_post_meta($customerId);
                        if (!empty($customerMeta) && isset($customerMeta['customer_roles']) && count($customerMeta['customer_roles']) > 0) {
                            foreach ($customerMeta['customer_roles'] as $k => $r) {
                                $customerMeta['customer_roles'][$k] = strtolower($r);
                            }
                            if (count(array_intersect($customerMeta['customer_roles'], $userCurrentRoles)) > 0) {
                                $digitalCodeStr .= $digitalCodeStrTmp;
                            }
                        }
                    }
                }

            }
        }
    }
    $digitalCodeObject = $digitalCodes['digital-code'][0];


}

$user = wp_get_current_user();
$roles = ( array )$user->roles;

// Get Product Info Meta
$productMeta = $product->getMetaData();

// Get Product Category
$productCatSelect = get_the_terms($post->ID, 'product-category');
$listProductCatSelect = array();
$listProductCatOrder = array();
$arrParentHide = array();
$catPreservative = false;
foreach ($productCatSelect as $pCat) {
    $meta = get_term_meta($pCat->term_id);
    $parentId = intval($pCat->parent);
    $catHide = empty($meta['is_hide']) ? false : $meta['is_hide'][0] == 1;
    if (!$catHide && $parentId > 0 && !isset($arrParentHide[$parentId])) {
        if (!isset($listProductCatSelect[$parentId])) {
            $parent = get_term($parentId);
            if ($parent) {
                $parentMeta = get_term_meta($parentId);
                $parentHide = empty($parentMeta['is_hide']) ? false : $parentMeta['is_hide'][0] == 1;
                $parentOrder = empty($parentMeta['order']) ? 0 : intval($parentMeta['order'][0]);
                if ($parentHide) {
                    $arrParentHide[$parentId] = $parentId;
                    continue;
                }
                $listProductCatOrder[$parentId] = $parentOrder;
                $listProductCatSelect[$parentId] = array('lb' => $parent->name, 'vl' => $pCat->name);
                if (!$catPreservative && trim(strtolower($parent->name)) == "preservatives") {
                    $catPreservative = true;
                }
            }
        } else {
            $listProductCatSelect[$parentId]['vl'] .= ', '.$pCat->name;
        }
    }
}

asort($listProductCatOrder);

$terms = "";
foreach ($listProductCatOrder as $k => $v) {
    $v = $listProductCatSelect[$k];
    $terms .= '<p><strong>' . $v['lb'] . ':</strong> ' . $v['vl'] . '</p>';
    if (!$catPreservative) {
        $terms .= '<p><strong>Preservatives:</strong> None</p>';
        $catPreservative = true;
    }
}

if (!$catPreservative) {
    $terms .= '<p><strong>Preservatives:</strong> None</p>';
}


// Get Header Background
$backgroundImageURL = isset($productMeta['product_header_image']) && isset($productMeta['product_header_image'][0]) ? wp_get_attachment_url($productMeta['product_header_image'][0]) : get_stylesheet_directory_uri() . '/assets/images/default-barnet.png';
// Get Product Attribute List
$productAttributes = (new WP_Query(
    array(
        'post_type' => 'barnet-pattribute',
        'posts_per_page' => -1,
        'relationship' => array(
            'id' => 'products_to_pattributes',
            'from' => $post->ID
        )
    )
))->posts;

// Get Global Compliance List
$globalCompliance = isset($productMeta['product_global_compliance'][0]) ? unserialize($productMeta['product_global_compliance'][0]) : null;

// Get Description
$descriptionDefault = is_user_logged_in() ? ($productMeta['product_description_logged'][0] ?? null) : ($productMeta['product_description'][0] ?? null);
$description = isset($descriptionDefault) ? (@unserialize($descriptionDefault) ? unserialize($descriptionDefault) : $descriptionDefault) : null;
if (is_array($description) && count($description) > 0) {
    $tmpDes = $description;
    $description = "";
    foreach ($tmpDes as $des) {
        $description .= $des;
    }
}

$docIds = array();
// Get document

$docIds[] = isset($productMeta['product_kiss_doc'][0]) ? array("id" => $productMeta['product_kiss_doc'][0], "icon" => "icon-kiss-sheet", "label" => $product->getProductKissDocLabel()) : null;
$docIds[] = isset($productMeta['product_presentation_doc'][0]) ? array("id" => $productMeta['product_presentation_doc'][0], "icon" => "icon-presentation", "label" => $product->getProductPresentationDocLabel()) : null;
$docIds[] = isset($productMeta['product_msds_doc'][0]) ? array("id" => $productMeta['product_msds_doc'][0], "icon" => "icon-sds", "label" => $product->getProductMsdsDocLabel()) : null;
$docIds[] = isset($productMeta['product_spec_doc'][0]) ? array("id" => $productMeta['product_spec_doc'][0], "icon" => "icon-spec", "label" => $product->getProductSpecDocLabel()) : null;
$docIds[] = isset($productMeta['product_snapshots_doc'][0]) ? array("id" => $productMeta['product_snapshots_doc'][0], "icon" => "icon-summary", "label" => $product->getProductSnapshotsDocLabel()) : null;
$docIds[] = isset($productMeta['product_dossier_doc'][0]) ? array("id" => $productMeta['product_dossier_doc'][0], "icon" => "icon-summary", "label" => $product->getProductDossierDocLabel()) : null;
$docIds[] = isset($productMeta['product_formula_doc'][0]) ? array("id" => $productMeta['product_formula_doc'][0], "icon" => "icon-formula-sheet", "label" => $product->getProductFormulaDocLabel()) : null;
if (!empty($product->getProductOtherDocs()) && is_array($product->getProductOtherDocs())) {
    foreach ($product->getProductOtherDocs() as $otherDoc) {
        if (!empty($otherDoc['doc'])) {
            $otherDocLabel = "";
            if (!empty($otherDoc['label'])) {
                $otherDocLabel = $otherDoc['label'];
            }
            $docIds[] = array("id" => $otherDoc['doc'], "icon" => $otherDoc['other_icon'], "label" => $otherDocLabel);
        }

    }
}

$resources = array();
if (class_exists('ResourceEntity')) {
    foreach ($docIds as $docId) {
        if (!isset($docId)) {
            continue;
        }

        $docResource = new ResourceEntity($docId["id"]);
        if (!$docResource->checkRoleAndRegion() || !empty($docResource->getResourcePptSource()) || !strstr($docResource->getResourceMediaType(), 'application/pdf')) {
            continue;
        }

        $resourceExt = '';
        $extArr = explode('/', $docResource->getResourceMediaType());
        if (count($extArr) > 0) {
            $resourceExt = $extArr[count($extArr) - 1];
        }
        if (empty($docId['label'])) {
            $docId['label'] = $docResource->getPostTitle();
        }

        $resources[] = array(
            'title' =>  $docId['label'],
            'url' => $docResource->getMediaExternalURL(),
            'ext' => $resourceExt,
            'icon' => $docId['icon']
        );
    }
}

// Get Starter Formula List
$formulas = (new WP_Query(
    array(
        'post_type' => 'barnet-formula',
        'posts_per_page' => -1,
        'relationship' => array(
            'id' => 'products_to_formulas',
            'from' => $post->ID
        )
    )
))->posts;

// Get Related Concepts
$concepts = (new WP_Query(
    array(
        'post_type' => 'barnet-concept',
        'posts_per_page' => -1,
        'relationship' => array(
            'id' => 'products_to_concepts',
            'from' => $post->ID
        )
    )
))->posts;

//Get Alternate Product
$alternateProduct = $product->getProductAlternateGroup();
if (empty($alternateProduct)) {
    $alternateProduct = array();
} else {
    if ($product->isSerialized($alternateProduct)) {
        $alternateProduct = unserialize($alternateProduct);
    }
}

//get Architecture/Technology product
$architectureTechnology = $product->getProductArchitectureTechnology();
if (empty($architectureTechnology)) {
    $architectureTechnology = array();
} else {
    if ($product->isSerialized($architectureTechnology)) {
        $architectureTechnology = unserialize($architectureTechnology);
    }
}

$productVideoResource = null;

$productVideo = empty($productMeta['product_video_resource']) ? 0 : $productMeta['product_video_resource'];

if (is_array($productVideo) && count($productVideo) > 0) {
    $productVideo = intval($productVideo[0]);
} else {
    $productVideo = intval($productVideo);
}
if ($productVideo < 0) {
    $productVideo = 0;
}

if ($productVideo > 0) {
    if (class_exists('ResourceEntity')) {
        $productVideoResource = new ResourceEntity($productVideo);
        if (!$productVideoResource->checkRoleAndRegion() || !empty($productVideoResource->getResourcePptSource()) || !strstr($productVideoResource->getResourceMediaType(), 'video')) {
            $productVideoResource = null;
        }
    }
}

get_header(); ?>

<main role="main">
    <div class="product-container product-container--detail">
        <div class="container">
            <div class="productDetail">
                <div class="productDetail__wrapping">
                    <div class="productDetail__header">
                        <div class="productDetail__header-groupTitle">
                            <h1 class="productDetail__header-title">
                                <?php echo $post->post_title; ?>
                            </h1>
                            <div class="productDetail__header-attr"><?php echo strtoupper($digitalCodeStr); ?></div>
                        </div>
                        <div class="productDetail__header-desc--productPage">
                            <?php
                            if (is_user_logged_in() && isset($productMeta['inci_name'][0])) {
                                echo "<span>INCI:</span><p>{$productMeta['inci_name'][0]}</p>";
                            } elseif (!is_user_logged_in() && ($display_inci == 1) && isset($productMeta['inci_name'][0])) {
                                echo "<span>INCI:</span><p>{$productMeta['inci_name'][0]}</p>";
                            }
                            ?>
                        </div>
                    </div>
                    <div class="productDetail__background"
                         style="<?php echo $backgroundImageURL ? "background-image: url($backgroundImageURL)" : ''; ?>"></div>
                    <div class="productDetail__detail">
                        <div class="productDetail__detail-wrap">
                            <div class="productDetail__detail-right">
                                <?php if (is_user_logged_in()) { ?>
                                <div class="productDetail__fileList">
                                    <ul>
                                        <?php
                                        foreach ($resources as $resource) {
                                            ?>
                                            <li><a class="btn-gtm-pdf-download" href="<?php echo $resource['url']; ?>"
                                                   title="<?php echo $resource['title']; ?>" target="_blank" rel="stylesheet"><i
                                                            class="icon <?php echo $resource['icon']; ?>"></i><span
                                                            class="text"><?php echo $resource['title']; ?><span
                                                                class="extension"><?php echo isset($resource['ext']) ? "({$resource['ext']})" : ''; ?></span></span></a>
                                            </li>
                                            <?php
                                        }
                                        ?>
                                    </ul>
                                </div>
                                <?php } ?>
                                <div class="productDetail__attributes ">
                                    <?php if (is_user_logged_in()) : ?>
                                    <div class="productDetail__attributes-properties --mg-bottom-sm">
                                        <?php
                                        $termsPrefix = "";
                                        if (isset($productMeta['product_usage'][0])) {
                                            $termsPrefix = "<p><strong>Use Level:</strong> {$productMeta['product_usage'][0]}</p>";
                                        }

                                        echo $termsPrefix;
                                        echo $terms;

                                        if (isset($productMeta['product_iso'][0])) {
                                            echo "<p><strong>ISO Natural Origin Rating:</strong> {$productMeta['product_iso'][0]}</p>";
                                        }
                                        ?>
                                    </div>
                                    <?php endif; ?>
                                    <h3 class="productDetail__attributes-title">Key Attributes & Certifications:
                                    </h3>
                                    <div class="productDetail__attributes-list">
                                        <?php
                                        $productAttributeMetaManager = new BarnetPostMetaManager($productAttributes);
                                       
                                        $productAttributesList = array();

                                        foreach ($productAttributes as $productAttribute) {
                                            $productAttributeMeta = $productAttributeMetaManager->getMetaData($productAttribute->ID);
                                            
                                            $productAttributeImageURL = "";
                                            if (isset($productAttributeMeta['product-attribute_media']) && is_array($productAttributeMeta['product-attribute_media'])) {
                                                if (count($productAttributeMeta['product-attribute_media']) > 0) {
                                                    $productAttributeImageURL = wp_get_attachment_url($productAttributeMeta['product-attribute_media'][0]);
                                                }
                                            } elseif (isset($productAttributeMeta['product-attribute_media'])) {
                                                $productAttributeImageURL = wp_get_attachment_url($productAttributeMeta['product-attribute_media']);
                                            }
                                            
                                            $productAttributeImageURL = $productAttributeImageURL ? $productAttributeImageURL : $barnet->getDefaultImage();

                                            $productAttributeOrder = 999;
                                            if (isset($productAttributeMeta['product-attribute_order']) &&
                                                is_array($productAttributeMeta['product-attribute_order']) &&
                                                count($productAttributeMeta['product-attribute_order']) > 0) {
                                                $productAttributeOrder = intval($productAttributeMeta['product-attribute_order'][0]);
                                            } elseif (isset($productAttributeMeta['product-attribute_order'])) {
                                                $productAttributeOrder = intval($productAttributeMeta['product-attribute_order']);
                                            }

                                            $attrId=$productAttribute->ID;
                                            $_concept_slug=get_post_meta($attrId, "_concept_slug", true);
                                            $concept_slug= $productAttribute->post_name;
                                            if($_concept_slug && $_concept_slug!='defualt'){
                                                $concept_slug= $_concept_slug;
                                            }
                                           
                                            $productAttributes_arr = array(
                                                'image' => $productAttributeImageURL,
                                                'title' => $productAttribute->post_title,
                                                'name' => $productAttribute->post_name,
                                                'order' => $productAttributeOrder,
                                                'concept_slug' => $concept_slug,
                                            );
                                            $productAttributesList[] = $productAttributes_arr;
                                        }
                                        usort($productAttributesList, function ($a, $b) {
                                            return (isset($a['order']) ? $a['order'] : '999') <=> (isset($b['order']) ? $b['order'] : '999');
                                        });

                                        foreach ($productAttributesList as $productAttribute) {
                                            
                                            ?>
                                            <div class="productDetail__attributes-item">
                                                <div class="productDetail__attributes-image">  <a 
                                          		target="_blank"
                                                   href="<?php echo get_site_url()."/barnet-concept/".$productAttribute['concept_slug']; ?>"
                                                   title="<?php echo esc_html($productAttribute['title']); ?>"
                                                   rel="stylesheet">
                                                           <img
                                                            src="<?php echo $productAttribute['image']; ?>"
                                                            alt="<?php echo esc_html($productAttribute['title']); ?>"></a>
                                                </div>
                                                <div class="productDetail__attributes-tooltip">  <a 
                                          		target="_blank"
                                                   href="<?php echo get_site_url()."/barnet-concept/".$productAttribute['concept_slug']; ?>"
                                                   title="<?php echo esc_html($productAttribute['title']); ?>"
                                                   rel="stylesheet">
                                                            <img
                                                            src="<?php echo $productAttribute['image']; ?>"
                                                            alt="<?php echo esc_html($productAttribute['title']); ?>"></a>
                                                </div>
                                                <h3 class="productDetail__attributes-text">
                                                    <?php echo $productAttribute['title']; ?>
                                                </h3>
                                                <a class="productDetail__attributes-link"
                                          		target="_blank"
                                                   href="<?php echo get_site_url()."/barnet-concept/".$productAttribute['concept_slug']; ?>"
                                                   title="<?php echo esc_html($productAttribute['title']); ?>"
                                                   rel="stylesheet"></a>
                                            </div>
                                            <?php
                                        }
                                        ?>
                                    </div>
                                    <?php if (is_user_logged_in()) : ?>
                                    <h3 class="productDetail__attributes-title">Global Compliance:</h3>
                                    <div class="productDetail__attributes-compliance">
                                        <ul>
                                            <?php
                                            if (isset($globalCompliance)) {
                                                foreach ($globalCompliance as $globalComplianceItem) {
                                                    ?>
                                                    <li>
                                                        <span class="country"><?php echo $globalComplianceItem['t_label']; ?></span>
                                                        <span class="content"><?php echo $globalComplianceItem['t_text']; ?></span>
                                                    </li>
                                                    <?php
                                                }
                                            }
                                            ?>
                                        </ul>
                                    </div>
                                    <?php endif; ?>
                                </div>
                                <?php
                                if (!empty($alternateProduct)):
                                    foreach ($alternateProduct as $item):
                                        ?>
                                        <?php if (!empty($item['product_alternate_product'])): ?>
                                            <?php 
                                                //Remove product on list
                                                $product_only_for_code_list = 0;
                                                $product_only_for_code_list = get_post_meta(intval($item['product_alternate_product']), 'product_only_for_code_list', TRUE);
                                                if (intval($product_only_for_code_list) == 1) {
                                                    continue;
                                                }
                                            ?>
                                        <?php endif;?>
                                        <div class="productDetail__linkVersion --mg-top-sm">
                                            <?php if (!empty($item['product_alternate_description'])): ?>
                                                <?php if (!empty($item['product_alternate_product'])): ?>
                                                    <a href="<?php echo get_permalink($item['product_alternate_product']); ?>"
                                                       title="<?php echo $item['product_alternate_description']; ?>">
                                                        <span><?php echo $item['product_alternate_description']; ?></span>
                                                    </a>
                                                <?php else: ?>
                                                    <span><?php echo $item['product_alternate_description']; ?></span>
                                                <?php endif; ?>
                                            <?php endif; ?>
                                        </div>
                                    <?php
                                    endforeach;
                                endif;
                                ?>
                            </div>
                            <div class="productDetail__detail-left">
                                <h2 class="productDetail__contentAttributes">
                                    <?php
                                    echo $description ?? "";
                                    ?>
                                </h2>
                            </div>
                        </div>
                    </div>
                    <?php if(!is_user_logged_in()):?>
                        <div class="productDetail__signIn">
                            <div class="component-signin-box" style="background-image: url(<?php echo get_template_directory_uri(); ?>/assets/images/product/bg-signin.png)">
                                <div class="component-signin-box__title">Sign In for Details</div>
                                <div class="component-signin-box__box">
                                    <div class="component-signin-box__signin">
                                        <div class="component-signin-box__text">Registered users get access to full product information and related resources:</div>
                                        <div class="component-signin-box__list">
                                            <ul class="component-list">
                                                <li>Specifications & Data Sheets</li>
                                                <li>Videos & Presentations</li>
                                                <li>Starting Formulas</li>
                                            </ul>
                                        </div>
                                        <div class="component-signin-box__link"><a href="/login" class="btn btn-normal --dark-mode">Sign in</a></div>
                                    </div>
                                    <div class="component-signin-box__request">
                                        <div class="component-signin-box__titleRequest">New Customer?</div>
                                        <a class="btn btn-solid --dark-mode btn-gtm-request-access" href="/register" title="Request Access" rel="stylesheet">Request Access</a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endif;?>  
                    <div class="productDetail__request">
                        <div class="component-sample-band">
                            <div class="component-sample-band__wrap">
                                <div class="component-sample-band__content">
                                    <div class="component-sample-band__icon">
                                        <i class="icon icon-sample"></i>
                                    </div>
                                    <div class="component-sample-band__text"
                                         data-status-sample>
                                        <?php echo get_theme_mod('product_add_sample_text', class_exists('BarnetDefaultText') ? BarnetDefaultText::PRODUCT_ADD_SAMPLE_TEXT : ''); ?>
                                    </div>
                                </div>
                                <div class="component-sample-band__btn" data-group-btn-sample>
                                    <a class="btn btn-solid --dark-mode" href="#" title="<?php _e('Request Sample'); ?>"
                                       rel="stylesheet" data-add-sample
                                       data-opts-sample='{"id": "<?php echo $product->getId(); ?>", "post_title": "<?php echo esc_html($product->getPostTitle()); ?>", "permalink": "<?php echo $product->getPermalink(); ?>"}'><?php _e('Request Sample'); ?></a>
                                    <a class="btn btn-solid --dark-mode d-none" href="#"
                                       title="<?php _e('Submit Request Now'); ?>" rel="stylesheet"
                                       data-anchor-link><?php _e('Submit Request Now'); ?></a>
                                    <a class="btn d-none" href="#" title="<?php _e('Remove Sample'); ?>"
                                       rel="stylesheet" data-remove-sample
                                       data-opts-sample='{"id":"<?php echo $product->getId(); ?>"}'><?php _e('Remove Sample'); ?></a>
                                </div>
                            </div>
                        </div>
                    </div>
                    <?php if (!empty($productVideoResource) > 0) : ?>
                        <?php
                        $linkImage = $productVideoResource->getResourceImageURL();
                        $nameVideo = $productVideoResource->getPostTitle();
                        $linkVideo = $productVideoResource->getMediaExternalURL();
                        $typeVideo = $productVideoResource->getResourceMediaType();
                        $linkResource = $productVideoResource->getPermalink();
                        $resourceMediaUrl = $productVideoResource->getMediaLocalURL();
                        ?>

                        <div class="productDetail__areaDetail --mg-top-lg">
                            <div class="productDetail__headingGroup">
                                <div class="component-heading-group">
                                    <h3 class="component-heading-group__heading --size-h2"><?php echo $nameVideo; ?>
                                    </h3>
                                </div>
                                <div class="productDetail__headingGroup-link"><a href="<?php echo $linkResource; ?>"
                                                                                 title="<?php _e('See Standalone Video'); ?>"
                                                                                 target="_blank"
                                                                                 rel="stylesheet"><span><?php _e('See Standalone Video'); ?></span></a>
                                </div>
                            </div>
                            <div class="component-video-control --mg-top-xs" data-gtm-view-video="<?php echo $nameVideo;?>" data-video>
                                <?php if (isset($linkImage) && $linkImage != get_template_directory_uri() . "/assets/images/default.png") : ?>
                                    <video poster="<?php echo $linkImage;?>">
                                        <source src="<?php echo $linkVideo; ?>" type="<?php echo $typeVideo; ?>">
                                    </video>
                                <?php else:?>
                                    <video>
                                        <source src="<?php echo $resourceMediaUrl; ?>#t=0.5" type="<?php echo $typeVideo; ?>">
                                    </video>
                                <?php endif;?>
                            </div>
                        </div>
                    <?php endif; ?>
                    <?php if (!empty($architectureTechnology)) :?>
                        <div class="productDetail__areaDetail --mg-top-lg">
                            <div class="row">
                                <div class="col-12 col-sm-6">
                                    <div class="component-heading-group">
                                        <?php if (!empty($architectureTechnology["at_title"])) :?>
                                            <h3 class="component-heading-group__heading --size-h2">
                                                <?php echo $architectureTechnology["at_title"];?>
                                            </h3>
                                        <?php endif;?>
                                        <?php if (!empty($architectureTechnology["at_description"])) :?>
                                            <div class="component-heading-group__desc">
                                                <?php echo $architectureTechnology["at_description"];?>
                                            </div>
                                        <?php endif;?>
                                    </div>
                                </div>
                                <div class="col-12 col-sm-6">
                                    <?php if (!empty($architectureTechnology["at_image"])) :?>
                                        <?php
                                        $imgUrl = 0;
                                        if (is_array($architectureTechnology["at_image"]) && count($architectureTechnology["at_image"]) > 0) {
                                            $imgUrl = intval($architectureTechnology["at_image"][0]);
                                        } else {
                                            $imgUrl = intval($architectureTechnology["at_image"]);
                                        }
                                        if ($imgUrl > 0) :
                                            $imgUrl = wp_get_attachment_url($imgUrl);
                                            $imgText = "";
                                            if (!empty($architectureTechnology["at_image_text"])) {
                                                $imgText = $architectureTechnology["at_image_text"];
                                            }

                                            ?>
                                            <div class="productDetail__contentImage-image --mg-top-ml"><img src="<?php echo $imgUrl;?>" alt="<?php echo esc_html($imgText);?>">
                                            </div>
                                            <div class="productDetail__contentImage-imageText --mg-top-xs"><?php echo $imgText;?></div>
                                        <?php endif; ?>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    <?php endif;?>
                    <?php
                    if (count($formulas) > 0) {
                        ?>
                        <div class="productDetail__areaDetail --mg-top-lg">
                            <div class="component-heading-group">
                                <h3 class="component-heading-group__heading --size-h2">Starting Formulas
                                    with <?php echo $post->post_title; ?>
                                </h3>
                            </div>
                            <div class="component-list-product --mg-top-sm --pd-bottom-sm">
                                <?php
                                foreach ($formulas as $formula) {
                                    $formulaItem = new FormulaEntity($formula->ID, true, array('post' => $formula));
                                    $iconItem = $formulaItem->getFormulaIconBlack();
                                    $linkItem = $formulaItem->getPermalink();
                                    ?>
                                    <div class="component-list-product__item --has-image">
                                        <div class="component-list-product__image"><a href=""
                                                                                      title="<?php echo $formula->post_title; ?>"
                                                                                      rel="stylesheet"><img
                                                        src="<?php echo !empty($iconItem) ? $iconItem : "#"; ?>" alt=""></a>
                                        </div>
                                        <div class="component-list-product__wrap">
                                            <div class="component-list-product__title">
                                                <h3><a href="<?php echo $linkItem; ?>"
                                                       title="<?php echo $formula->post_title; ?>"
                                                       rel="stylesheet"><?php echo $formula->post_title; ?></a>
                                                </h3>
                                            </div>
                                            <div class="component-list-product__desc"><?php echo strip_tags($formulaItem->getFormulaDescription()); ?></div>
                                        </div>
                                    </div>
                                    <?php
                                }
                                ?>
                            </div>
                        </div>
                        <?php
                    }
                    ?>
                </div>
                <?php
                if (count($concepts) > 0) {
                    ?>
                    <section class="component-fashtion-concepts --mg-top-lg">
                        <div class="component-fashtion-concepts__wrapper">
                            <div class="component-heading-group text-center --dark-mode">
                                <h3 class="component-heading-group__heading --size-h2">Related Concepts
                                </h3>
                            </div>
                            <div class="component-fashtion-concepts__slider --mg-top-sm">
                                <div class="component-image-concepts">
                                    <div class="component-image-concepts__wrapper slider-control" data-slider-main
                                         data-opts-slider='{"slickContainer": ".component-fashtion-concepts","optsSlick": {"slide": "[data-slider-item]","dots": true, "rows": 0, "slidesToShow": 4, "slidesToScroll": 4, "responsive":[{"breakpoint": 991, "settings": {"slidesToShow": 3, "slidesToScroll": 3}}, {"breakpoint": 768, "settings": {"slidesToShow": 2, "slidesToScroll": 2}}]}}'>
                                        <?php
                                        foreach ($concepts as $concept) {
                                            $rcItem = new ConceptEntity($concept->ID);
                                            $imageItem = '';
                                            if(!empty($rcItem->getConceptThumbnail())) {
                                                $imageItem = wp_get_attachment_url($rcItem->getConceptThumbnail());
                                            }
                                            $imageItem = $imageItem ? $imageItem : $barnet->getDefaultImage("concept_thumb");
                                            $linkItem = $rcItem->getPermalink();
                                            ?>
                                            <div class="component-image-concepts__item" data-slider-item>
                                                <div class="component-image-concepts__item--wrapper">
                                                    <div class="component-image-concepts__item--img">
                                                        <?php if (!empty($imageItem)) : ?>
                                                            <img src="<?php echo $imageItem; ?>" alt="<?php echo esc_html($rcItem->getPostTitle()); ?>">
                                                        <?php endif;?>
                                                    </div>
                                                    <div class="component-image-concepts__item--content">
                                                        <div class="component-image-concepts__item--heading">
                                                            <?php echo $rcItem->getPostTitle(); ?>
                                                        </div>
                                                        <div class="component-image-concepts__item--desc">
                                                            <?php echo $rcItem->getPostExcerpt(); ?>
                                                        </div>
                                                    </div>
                                                    <a href="<?php echo $linkItem; ?>" title="<?php echo esc_html($rcItem->getPostTitle()); ?>" rel="stylesheet"></a>
                                                </div>
                                            </div>
                                            <?php
                                        }
                                        ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </section>
                    <?php
                }
                ?>
            </div>
        </div>
    </div>
</main>

<?php get_footer(); ?>
<style type="text/css">
    .productDetail__signIn::before {
        background-color: #ffffff !important;
    }
</style>