<?php

if (!is_user_logged_in()) {
    global $wp;
    $requestGet = array();
    if ($_GET) {
        $requestGet = $_GET;
    }

    $current_url = site_url(add_query_arg(array($requestGet), "/" . $wp->request . "/"));
    $redirect_to = add_query_arg("redirect_to", urlencode($current_url), wp_login_url());
    wp_redirect($redirect_to);
    exit();
}

global $post;

$concept = new ConceptEntity($post->ID, true, array('post' => $post));
if (!$concept->checkRoleAndRegion()) {
    wp_redirect('/');
}


$product_types = get_terms(array(
    'taxonomy'   => 'product-type',
    'fields'   =>  'names',
    'hide_empty' => false,
) ); 

$comTabListItems="";
foreach($product_types as $product_type){
    $comTabListItems.= '<com-tab-list-item name="'.strtolower($product_type).'" :isgroup="true"></com-tab-list-item>';
}

$comTabListItems .= '
<com-tab-list-item name="formula"></com-tab-list-item>
<com-tab-list-item name="resource"></com-tab-list-item>
';

$user = wp_get_current_user();
$roles = ( array )$user->roles;

// Get Product Info Meta
$conceptMeta = $concept->getMetaData();
$conceptDescription = '';
if (!empty($conceptMeta['concept_description'])) {
    $conceptMetaDescription = $conceptMeta['concept_description'];
    $conceptDescription = is_array($conceptMetaDescription) && isset($conceptMetaDescription[0]) ?
        $conceptMetaDescription[0] : $conceptMetaDescription;
}
$conceptInteractives = array();
$imgInteractives = '';

if (!empty($concept->getConceptInteractiveImage()) && $concept->getConceptInteractiveImage() > 0) {
    $imgInteractives = wp_get_attachment_url($concept->getConceptInteractiveImage());
    $imgInteractives = $imgInteractives ? $imgInteractives : $barnet->getDefaultImage();
}

$relationships = $concept->getRelationship();
if (isset($relationships['interactives']) && count($relationships['interactives']) > 0) {
    $conceptInteractives = $relationships['interactives'];
}

if (isset($relationships['resources']) && count($relationships['resources']) > 0) {
    $conceptResources = $relationships['resources'];
}

$conceptFiles = array();
if (!empty($concept->getConceptPresentionDocs()) && is_array($concept->getConceptPresentionDocs())) {
    foreach ($concept->getConceptPresentionDocs() as $presentionDoc) {
        if (!empty($presentionDoc['doc'])) {
            $conceptDocResource = new ResourceEntity($presentionDoc['doc']);
            $presentionDocLabel = "";
            if (!empty($presentionDoc['label'])) {
                $presentionDocLabel = $presentionDoc['label'];
            }
            if (!$conceptDocResource->checkRoleAndRegion() || empty($conceptDocResource->getResourceMediaType())) {
                $conceptDocResource = null;
            }

            if (!empty($conceptDocResource)) {
                $conceptFiles[$conceptDocResource->getId()] = array("value" => $conceptDocResource, "label" => $presentionDocLabel, "icon" => "icon-presentation");
            }
        }

    }
}

if (!empty($concept->getConceptVideosDoc()) && intval($concept->getConceptVideosDoc()) > 0) {
    $conceptDocResource = new ResourceEntity($concept->getConceptVideosDoc());
    if (!$conceptDocResource->checkRoleAndRegion() || empty($conceptDocResource->getResourceMediaType())) {
        $conceptDocResource = null;
    }

    if (!empty($conceptDocResource)) {
        $conceptFiles[$conceptDocResource->getId()] = array("value" => $conceptDocResource, "icon" => "icon-video");
    }
}

$relatedConcepts = array();
if (isset($relationships['concepts']) && count($relationships['concepts']) > 0) {
    $relatedConcepts = $relationships['concepts'];
}


$conceptImage = $concept->getConceptImage();
$conceptImageURL = is_array($conceptImage) ? (isset($conceptImage[0]) ?
    wp_get_attachment_url($conceptImage[0]) : $barnet->getDefaultImage("concept_header")) :
    (isset($conceptImage) ? wp_get_attachment_url($conceptImage) : $barnet->getDefaultImage("concept_header"));
$conceptImageURL = $conceptImageURL ? $conceptImageURL : $barnet->getDefaultImage("concept_header");
$style = get_post_meta($post->ID, 'concept_style', TRUE);
get_header(); ?>

<main role="main">
    <div class="concepts-container">
        <div class="container">
            <div class="concepts-detail">
                <div class="concepts-detail__wrapping">
                    <div class="concepts-detail__header">
                        <div class="concepts-detail__header-groupTitle">
                            <div class="concepts-detail__header-title">
                                <h1><?php echo $post->post_title; ?></h1>
                            </div>
                            <div class="concepts-detail__header-attr">concept</div>
                        </div>
                        <?php if (isset($conceptFiles)) { ?>
                        <div class="concepts-detail__fileList">
                            <ul<?php echo count($conceptFiles) >= 3 ? ' class="--more-than-three"' : '';?>>
                                <?php
                                foreach ($conceptFiles as $v) :
                                    $fileLink = $v["value"]->getMediaExternalURL();
                                    if (!empty($v["value"]->getResourcePptSource()) || strstr($v["value"]->getResourceMediaType(), 'video')) {
                                        $fileLink = $v["value"]->getPermalink();
                                    }
                                    $fileName = $v["value"]->getPostTitle();
                                    if (!empty($v["label"])) {
                                        $fileName = $v["label"];
                                    }
                                    $fileExt = '';
                                    $extArr = explode('/', $v["value"]->getResourceMediaType());
                                    if (count($extArr) > 0) {
                                        $fileExt = $extArr[count($extArr) - 1];
                                    }
                                    ?>
                                <li>
                                    <a <?php echo $v["icon"] == 'icon-presentation' ? ' class="btn-gtm-pdf-download"' : '';?> <?php echo $v["icon"] == 'icon-video' ? ' class="btn-gtm-view-video"' : '';?> href="<?php echo $fileLink; ?>"
                                       title="<?php echo esc_html($fileName); ?>" target="_blank" rel="stylesheet">
                                        <i class="icon <?php echo $v["icon"];?>"></i>
                                        <span class="text"><?php echo $fileName; ?></span>
                                    </a>
                                </li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                        <?php } ?>
                    </div>
                    <div class="concepts-detail__imageContent">
                        <div class="component-slider-hero slider-control --size-wrap-lg">
                            <div class="component-slider-hero__item dark-mode" style="background-image: url(<?php echo $conceptImageURL; ?>)" data-slider-item>
                                <div class="component-slider-hero__wrap">
                                    <div class="component-heading-group <?php echo $style == 'light' ? '' : '--dark-mode'; ?>">
                                        <h2 class="component-heading-group__heading">
                                        </h2>
                                        <div class="component-heading-group__desc"><?php echo $conceptDescription; ?>
                                        </div>
                                    </div>
                                    <div class="component-slider-hero__btn"><a class="btn btn-regular <?php echo $style == 'light' ? '' : '--dark-mode'; ?>"
                                                                               href="#" title="See Details">See
                                            Details</a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="concepts-detail__areaDetail" data-interactive-diagram>
                        <?php if (!empty($imgInteractives) || count($conceptInteractives) > 0) : ?>
                            <div class="concepts-detail__bannerContent --mg-top-ml">
                                <div class="concepts-detail__banner">
                                    <?php if (!empty($imgInteractives)) : ?>
                                        <img src="<?php echo $imgInteractives; ?>"
                                             alt="<?php echo esc_html($post->post_title); ?>">
                                    <?php endif; ?>
                                    <?php if (count($conceptInteractives) > 0) : ?>
                                        <?php
                                        $cIndex = 0;
                                        foreach ($conceptInteractives as $ci) :
                                            $stylePos = '';
                                            if (!empty($ci->ia_coordinates)) {
                                                $iaCoordinates = $ci->ia_coordinates;
                                                if (!empty($iaCoordinates)) {
                                                    $exp = explode(",", $iaCoordinates);
                                                    if (count($exp) > 1) {
                                                        $stylePos = 'top: ' . $exp[1] . '%; left: ' . $exp[0] . '%';
                                                    }
                                                }
                                            }
                                            ?>
                                            <div class="concepts-detail__banner--dot<?php echo $cIndex == 0 ? ' active' : '' ?>"
                                                 data-dot-diagram data-index="<?php echo $cIndex; ?>"
                                                 style="<?php echo $stylePos; ?>"><span></span></div>
                                            <?php
                                            $cIndex++;
                                        endforeach; ?>
                                    <?php endif; ?>
                                </div>
                                <?php if (count($conceptInteractives) > 0) : ?>
                                    <div class="concepts-detail__slideContent">
                                        <div class="concepts-detail__slide slider-control" data-diagram data-slider-main
                                             data-opts-slider='{"slickContainer": ".concepts-detail__bannerContent","optsSlick": {"slide": "[data-slider-item]", "dots": true, "rows": 0, "slidesToShow": 1}}'>
                                            <?php foreach ($conceptInteractives as $ci) : ?>
                                                <?php
                                                $iaSubtitle = '';
                                                if (!empty($ci->ia_subtitle)) {
                                                    $iaSubtitle = $ci->ia_subtitle;
                                                }

                                                $iaImage = '';
                                                if (!empty($ci->ia_image)) {
                                                    $iaImage = wp_get_attachment_url($ci->ia_image);
                                                    $iaImage = $iaImage ? $iaImage : $barnet->getDefaultImage();
                                                }

                                                $iaLink = '#';
                                                $ciSubConcept = get_the_terms($ci->id, 'sub-concept-category');
                                                if (!empty($ciSubConcept) && is_array($ciSubConcept) && count($ciSubConcept) > 0) {
                                                    $iaLink .= $ciSubConcept[0]->slug;
                                                }

                                                $iaLinkLabel = '';
                                                if (!empty($ci->ia_link_label)) {
                                                    $iaLinkLabel = $ci->ia_link_label;
                                                }

                                                $iaHtml = '';
                                                if (!empty($ci->ia_html)) {
                                                    $iaHtml = $ci->ia_html;
                                                }
                                                ?>
                                                <div class="item" data-slider-item>
                                                    <div class="component-heading-group">
                                                        <h2 class="component-heading-group__heading">
                                                            <?php echo $ci->post_title; ?>
                                                        </h2>
                                                        <div class="component-heading-group__desc">
                                                            <?php echo $iaSubtitle; ?>
                                                        </div>
                                                    </div>
                                                    <div class="component-media-group --mg-top-lg">
                                                        <div class="component-media-group__image">
                                                            <a href="<?php echo $iaLink; ?>"
                                                               title="<?php echo esc_html($iaLinkLabel); ?>"
                                                               rel="stylesheet" data-anchor-link-diagram>
                                                                <?php if (!empty($iaImage)) : ?>
                                                                    <img src="<?php echo $iaImage; ?>" alt="">
                                                                <?php endif; ?>
                                                            </a>
                                                        </div>
                                                        <div class="component-media-group__caption">
                                                            <div class="component-media-group__title">
                                                                <a href="<?php echo $iaLink; ?>"
                                                                   title="<?php echo esc_html($iaLinkLabel); ?>"
                                                                   rel="stylesheet" data-anchor-link-diagram>
                                                                    <span><?php echo $iaLinkLabel; ?></span>
                                                                </a>
                                                            </div>
                                                            <div class="component-media-group__content">
                                                                <?php echo $iaHtml; ?>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            <?php endforeach; ?>
                                        </div>
                                    </div>
                                <?php endif; ?>
                            </div>
                        <?php endif; ?>
                        <div class="concepts-detail__app --mg-top-lg">
                            <div id="app">
                                <com-container>
                                    <com-filter slot="left">
                                        <com-filter-swiper></com-filter-swiper>
                                    </com-filter>
                                    <com-tab-list slot="right" :hastoggle="true">
                                        <!--
                                        <com-tab-list-item name="active" :isgroup="true"></com-tab-list-item>
                                        <com-tab-list-item name="system" :isgroup="true"></com-tab-list-item>
                                        <com-tab-list-item name="formula"></com-tab-list-item>
                                        <com-tab-list-item name="resource"></com-tab-list-item>
                                        -->
                                        <?php echo $comTabListItems;?>
                                    </com-tab-list>
                                    <com-wrapping slot="right">
                                        <com-filter-mobile></com-filter-mobile>
                                        <com-listing>
                                            <com-list-concept
                                                    filter="/wp-json/barnet/v1/taxonomies"
                                                    data="/wp-json/barnet/v1/concept/<?php echo $post->ID; ?>"></com-list-concept>
                                            <com-load-more></com-load-more>
                                        </com-listing>
                                    </com-wrapping>
                                </com-container>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <?php //the_content(); ?>
            <?php if (count($relatedConcepts) > 0) :?>
            <section class="component-fashtion-concepts --mg-top-lg">
                <div class="component-fashtion-concepts__wrapper">
                    <div class="component-heading-group text-center <?php echo $style == 'light' ? '' : '--dark-mode'; ?>">
                        <h2 class="component-heading-group__heading">Related Concepts
                        </h2>
                    </div>
                    <div class="component-fashtion-concepts__slider --mg-top-sm">
                        <div class="component-image-concepts">
                            <div class="component-image-concepts__wrapper slider-control" data-slider-main data-opts-slider='{"slickContainer": ".component-fashtion-concepts","optsSlick": {"slide": "[data-slider-item]","dots": true, "rows": 0, "slidesToShow": 4, "slidesToScroll": 4, "responsive":[{"breakpoint": 991, "settings": {"slidesToShow": 3, "slidesToScroll": 3}}, {"breakpoint": 768, "settings": {"slidesToShow": 2, "slidesToScroll": 2}}]}}'>
                                <?php foreach ($relatedConcepts as $rc) : ?>
                                    <?php
                                    $rcItem = new ConceptEntity($rc['data']['id']);
                                    $imageItem = '';
                                    if (!empty($rcItem->getConceptThumbnail())) {
                                        $imageItem = wp_get_attachment_url($rcItem->getConceptThumbnail());
                                    }
                                    $imageItem = $imageItem ? $imageItem : $barnet->getDefaultImage("concept_thumb");
                                    $linkItem = $rcItem->getPermalink();
                                    $darkMode = "";
                                    if ($style == 'light') {
                                        $darkMode = " --dark-mode";
                                    }
                                    ?>
                                    <div class="component-image-concepts__item<?php echo $darkMode;?>" data-slider-item>
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
                                <?php endforeach; ?>
                        </div>
                    </div>
                </div>
            </section>
            <?php endif;?>
        </div>
    </div>
</main>

<?php get_footer(); ?>
