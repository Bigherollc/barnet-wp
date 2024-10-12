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

$resource = new ResourceEntity($post->ID, true, array('post' => $post));
if (!$resource->checkRoleAndRegion()) {
    wp_redirect('/');
}

$user = wp_get_current_user();
$roles = ( array ) $user->roles;

$name = '';
$description = '';
$relatedProducts = array();
$relatedFormulas = array();
$relatedConcepts = array();
$relatedResource = array();
$resourceMediaType = '';
$resourceMediaLink = '';
$thumbVideo = '';
$resourceMediaUrl = '';

$postMetas = get_post_meta($post->ID);
if (isset($postMetas['resource_media']) && isset($postMetas['resource_media'][0])) {
    $videoID = $postMetas['resource_media'][0];
    $postMetasVideo = get_post_meta($videoID);
    $resourceMediaUrl = wp_get_attachment_url($videoID);
    if (isset($postMetas['resource_image']) && isset($postMetas['resource_image'][0])) {
        $thumbVideo = wp_get_attachment_url($postMetas['resource_image'][0]);
    } elseif (isset($postMetasVideo['_thumbnail_id']) && isset($postMetasVideo['_thumbnail_id'][0])) {
        $thumbVideo = wp_get_attachment_url($postMetasVideo['_thumbnail_id'][0]);
    }
}

$name = $resource->getPostTitle();
$description = $resource->getResourceDescription();
$resourceMediaType = $resource->getResourceMediaType();
if (!empty($resource->getResourcePptSource())) {
    $resourceMediaLink = $resource->getResourcePptSource();
} elseif (!empty($resource->getResourceMedia())) {
    $resourceMediaLink = $resource->getMediaExternalURL();
}

if ($resourceMediaUrl == '') {
    $resourceMediaUrl = $resourceMediaLink;
}

$relationships = $resource->getRelationship();

if (isset($relationships['products']) && count($relationships['products']) > 0) {
    $relatedProducts = $relationships['products'];
}

if (isset($relationships['formulas']) && count($relationships['formulas']) > 0) {
    $relatedFormulas = $relationships['formulas'];
}

if (isset($relationships['concepts']) && count($relationships['concepts']) > 0) {
    $relatedConcepts = $relationships['concepts'];
}

if (isset($relationships['resources']) && count($relationships['resources']) > 0) {
    $relatedResource = $relationships['resources'];
}

?>

<?php get_header();?>

<main role="main">
    <div class="resource-detail">
        <div class="container">
            <div class="component-heading-group">
                <h2 class="component-heading-group__heading --size-lg">
                    <?php echo $name;?>
                    <?php if ($resourceMediaType == "application/pdf") :?>
                        <a class="resource-detail__iconPDF" href="<?php echo $resourceMediaLink; ?>" target="_blank">
                            <span class="icon icon-presentation"></span>
                        </a>
                    <?php endif; ?>
                </h2>
                <div class="component-heading-group__desc">
                    <?php echo $description; ?>
                </div>
            </div>
            <div class="resource-detail__detail">
                <div class="resource-detail__detail--row">
                    <?php if (count($relatedProducts) > 0 || count($relatedFormulas) > 0) : ?>
                    <div class="resource-detail__detail--colRight">
                        <div class="resource-detail__detail--content">
                            <?php if (count($relatedProducts) > 0) :?>
                            <div class="resource-detail__detail--part">
                                <div class="resource-detail__detail--title">Related Products</div>
                                <div class="resource-detail__detail--listRelated">
                                    <?php foreach($relatedProducts as $rp) :?>
                                    <?php
                                        //Remove product on list
                                        $product_only_for_code_list = 0;
                                        $product_only_for_code_list = get_post_meta(intval($rp['data']['id']), 'product_only_for_code_list', TRUE);
                                        if (intval($product_only_for_code_list) == 1) {
                                            continue;
                                        }
                                    ?>
                                    <?php
                                        $rpLink = get_permalink($rp['data']['id']);
                                        ?>
                                    <div class="component-item-related">
                                        <div class="component-item-related__title">
                                            <a href="<?php echo $rpLink;?>" title="<?php echo esc_html($rp['data']['post_title']); ?>" rel="stylesheet"><?php echo $rp['data']['post_title']; ?></a>
                                        </div>
                                    </div>
                                    <?php endforeach;?>
                                </div>
                            </div>
                            <?php endif; ?>
                            <?php if (count($relatedFormulas) > 0) :?>
                            <div class="resource-detail__detail--part">
                                <div class="resource-detail__detail--title">Related Formula</div>
                                <div class="resource-detail__detail--listRelated">
                                    <?php foreach ($relatedFormulas as $rf) :?>
                                        <?php
                                        $fItem= new FormulaEntity($rf['data']['id']);
                                        $iconItem = $fItem->getFormulaIcon();
                                        $linkItem = $fItem->getPermalink();
                                        ?>
                                    <div class="component-item-related">
                                        <?php if (!empty($iconItem)) :?>
                                        <div class="component-item-related__image">
                                            <a href="<?php echo $linkItem;?>" title="<?php echo esc_html($fItem->getPostTitle()); ?>" rel="stylesheet">
                                                <img src="<?php echo $iconItem;?>" alt="<?php echo esc_html($fItem->getPostTitle()); ?>">
                                            </a>
                                        </div>
                                        <?php endif;?>
                                        <div class="component-item-related__title">
                                            <a href="<?php echo $linkItem;?>" title="<?php echo esc_html($fItem->getPostTitle()); ?>" rel="stylesheet"><?php echo $fItem->getPostTitle(); ?></a>
                                        </div>
                                    </div>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                            <?php endif;?>
                        </div>
                    </div>
                    <?php endif;?>
                    <div class="resource-detail__detail--colLeft">
                        <?php if (!empty($resourceMediaLink) && !empty($resourceMediaType)) : ?>
                            <?php if (strstr($resourceMediaType, 'video')) :?>
                                <div class="resource-detail__detail--VideoFile">
                                    <div class="component-video-control" data-video id="video">
                                        <?php if (isset($thumbVideo) && $thumbVideo != '') : ?>
                                            <video poster="<?php echo $thumbVideo;?>">
                                                <source src="<?php echo $resourceMediaLink; ?>" type="<?php echo $resourceMediaType; ?>">
                                            </video>
                                        <?php else:?>
                                            <video>
                                                <source src="<?php echo $resourceMediaUrl; ?>#t=0.5" type="<?php echo $resourceMediaType; ?>">
                                            </video>
                                        <?php endif;?>
                                    </div>
                                </div>
                            <?php else :?>
                                <div class="resource-detail__detail--pdfFile">
                                    <iframe src="<?php echo $resourceMediaLink; ?>" frameborder="0"></iframe>
                                </div>
                            <?php endif; ?>
                        <?php endif;?>
                    </div>
                </div>
            </div>
            <?php if (count($relatedResource) > 0) :?>
            <div class="resource-detail__other">
                <div class="product__title-widget">Related Resources</div>
                <div class="component-related-video --bg-green">
                    <div class="component-related-video__wrapper">
                        <div class="component-related-video__item slider-control" data-slider-main data-opts-slider='{"slickContainer": ".component-related-video","optsSlick": {"slide": "[data-slider-item]","dots": true, "rows": 0, "slidesToShow": 4,  "slidesToScroll": 4, "responsive":[{"breakpoint": 991, "settings": {"slidesToShow": 3,  "slidesToScroll": 3}},{"breakpoint": 768, "settings": {"slidesToShow": 2,  "slidesToScroll": 2}},{"breakpoint": 376, "settings": {"slidesToShow": 1,  "slidesToScroll": 1}}]}}'>
                            <?php foreach ($relatedResource as $rr) :?>
                                <?php
                                $rItem= new ResourceEntity($rr['data']['id']);
                                //get media type
                                $postContent = get_post_meta($rr['data']['id']);
                                $mediaID = $postContent['resource_media'][0];
                                $mediaType = get_post_mime_type($mediaID);
                                $imageItem = $rItem->getResourceImageURL();
                                $linkItem = $rItem->getPermalink();
                                $timeMedia = $rItem->getResourceTime();
                                ?>
                                <div class="component-related-video__col" data-slider-item>
                                    <?php if(!empty($imageItem)) :?>
                                    <a class="component-related-video__img<?php if (strpos($mediaType, 'video') !== false) {echo ' btn-gtm-view-video';} else {echo '';}
                                                                ?>" href="<?php echo $linkItem;?>" title="<?php echo esc_html($rItem->getPostTitle()); ?>">
                                        <img src="<?php echo $imageItem; ?>" alt="image">
                                    </a>
                                    <?php endif; ?>
                                    <div class="component-related-video__content">
                                        <a class="component-related-video__title<?php if (strpos($mediaType, 'video') !== false) {echo ' btn-gtm-view-video';} else {echo '';}
                                                                ?>" href="<?php echo $linkItem;?>" title="<?php echo esc_html($rItem->getPostTitle()); ?>"><?php echo $rItem->getPostTitle(); ?></a>
                                        <?php if (!empty($timeMedia)) :?>
                                        <div class="component-related-video__time">
                                            <i class="icon icon-clock-sm"></i>
                                            <?php echo $timeMedia;?>
                                        </div>
                                        <?php endif;?>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
            </div>
            <?php endif;?>
            <?php if (count($relatedConcepts) > 0) :?>
            <section class="component-fashtion-concepts --mg-top-lg">
                <div class="component-fashtion-concepts__wrapper">
                    <div class="component-heading-group text-center --dark-mode">
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
                                    $imageItem = $imageItem ? $imageItem : $barnet->getDefaultImage();
                                    $linkItem = $rcItem->getPermalink();
                                    $darkMode = "";
                                    if ($rcItem->getConceptStyle() == "dark") {
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
                </div>
            </section>
            <?php endif;?>
        </div>
    </div>
</main>

<?php get_footer(); ?>
