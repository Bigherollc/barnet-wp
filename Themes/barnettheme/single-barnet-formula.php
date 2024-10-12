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

$formula = new FormulaEntity($post->ID, true, array('post' => $post));
if (!$formula->checkRoleAndRegion()) {
    wp_redirect('/');
}

$user = wp_get_current_user();
$roles = ( array )$user->roles;

$iconImage = '';
$image = get_stylesheet_directory_uri() . '/assets/images/default-barnet.png';
$name = "";
$code = "";
$description = "";
$ingredients = array();
$processSteps = array();
$specifications = array();
$keyAttributes = array();
$formulaBase = "";
$formulaVideoResource = null;
$formulaCollections = array();
$relatedFormulas = array();
$formulaFilePdf = array();
$formulaDescriptionFlexible = '';
$formulaShowFlexible = false;
$formulaLink = '';
$formulaKeyFeatures = array();
$formulaHowToUse = array();
$formulaKeyIngredients = array();

if (class_exists('FormulaEntity')) {
    $image = $barnet->getDefaultImage("formula_header");
    $formula = new FormulaEntity($post->ID, true, array('post' => $post));
    $name = $formula->getPostTitle();
    if (!empty($formula->getFormulaImageUrl())) {
        $image = $formula->getFormulaImageUrl();
    }
    $formulaLink = $formula->getPermalink();
    $iconImage = $formula->getFormulaIcon();
    $code = $formula->getFormulaCode();
    $description = $formula->getFormulaDescription();
    $ingredients = $formula->getFormulaIngredients();
    $formulaVideo = $formula->getFormulaVideoResource();
    $formulaBase = $formula->getFormulaBase();
    $formulaFlexible = $formula->getFormulaFlexible();
    if ($formula->isSerialized($formulaFlexible)) {
        $formulaFlexible = unserialize($formulaFlexible);
        if (!empty($formulaFlexible['formula_show_flexible']) && $formulaFlexible['formula_show_flexible'] == 1) {
            $formulaShowFlexible = true;
        }
        if (!empty($formulaFlexible['formula_description_flexible'])) {
            $formulaDescriptionFlexible = $formulaFlexible['formula_description_flexible'];
        }
    }

    if (!empty($formulaVideo) && intval($formulaVideo) > 0) {
        $formulaVideoResource = new ResourceEntity($formulaVideo);
        if (!$formulaVideoResource->checkRoleAndRegion() || !empty($formulaVideoResource->getResourcePptSource()) || !strstr($formulaVideoResource->getResourceMediaType(), 'video')) {
            $formulaVideoResource = null;
        }
    }

    if (empty($ingredients)) {
        $ingredients = array();
    } else {
        if ($formula->isSerialized($ingredients)) {
            $ingredients = unserialize($ingredients);
        }
    }
    $processSteps = $formula->getProcessSteps();
    if (empty($processSteps)) {
        $processSteps = array();
    } else {
        if ($formula->isSerialized($processSteps)) {
            $processSteps = unserialize($processSteps);
            $tmpProcessSteps = $processSteps;
            $processSteps = array();
            foreach ($tmpProcessSteps as $k => $v) {
                $processSteps[$v['p_step']] = $v['p_description'];
            }
            ksort($processSteps);
        }
    }

    $formulaKeyFeatures = $formula->getFormulaKeyFeature();
    if (empty($formulaKeyFeatures)) {
        $formulaKeyFeatures = array();
    } else {
        if ($formula->isSerialized($formulaKeyFeatures)) {
            $formulaKeyFeatures = unserialize($formulaKeyFeatures);
        }
    }

    $formulaHowToUse = $formula->getFormulaHowUse();
    if (empty($formulaHowToUse)) {
        $formulaHowToUse = array();
    } else {
        if ($formula->isSerialized($formulaHowToUse)) {
            $formulaHowToUse = unserialize($formulaHowToUse);
        }
    }


    $formulaKeyIngredients = $formula->getFormulaKeyIngredients();
    if (empty($formulaKeyIngredients)) {
        $formulaKeyIngredients = array();
    } else {
        if ($formula->isSerialized($formulaKeyIngredients)) {
            $formulaKeyIngredients = unserialize($formulaKeyIngredients);
        }
    }


    $specifications = $formula->getFormulaSpecifications();
    if (empty($specifications)) {
        $specifications = array();
    } else {
        if ($formula->isSerialized($specifications)) {
            $specifications = unserialize($specifications);
        }
    }

    $relationships = $formula->getRelationship();

    if (isset($relationships['fattributes']) && count($relationships['fattributes']) > 0) {
        $keyAttributes = $relationships['fattributes'];
    }

    if (isset($relationships['concepts']) && count($relationships['concepts']) > 0) {
        //$formulaCollections = $relationships['concepts'];

        foreach ($relationships['concepts'] as $k => $v) {
            /*if (is_array($v) && isset($v["data"])) {
                if (isset($v["data"]["concept_formula_collection"])) {
                    if (is_array($v["data"]["concept_formula_collection"]) && count($v["data"]["concept_formula_collection"]) > 0 && intval($v["data"]["concept_formula_collection"][0]) == 1) {
                        $formulaCollections[$v["data"]["id"]] = $v;
                    } else if (intval($v["data"]["concept_formula_collection"]) == 1){
                        $formulaCollections[$v["data"]["id"]] = $v;
                    }
                }
            } else {
                if (isset($v->concept_formula_collection) && is_array($v->concept_formula_collection) && count($v->concept_formula_collection) > 0 && intval($v->concept_formula_collection[0]) == 1) {
                    $formulaCollections[$v->id] = $v;
                }
            }*/
            if (is_array($v) && isset($v["data"])) {
                if (isset($v["data"]["concept_formula_collection"])) {
                    if (is_array($v["data"]["concept_formula_collection"]) && count($v["data"]["concept_formula_collection"]) > 0) {
                        $formulaCollections[$v["data"]["id"]] = $v;
                    } else {
                        $formulaCollections[$v["data"]["id"]] = $v;
                    }
                }
            } else {
                if (isset($v->concept_formula_collection) && is_array($v->concept_formula_collection) && count($v->concept_formula_collection) > 0) {
                    $formulaCollections[$v->id] = $v;
                }
            }
        }
    }

    if (isset($relationships['formulas']) && count($relationships['formulas']) > 0) {

        foreach ($relationships['formulas'] as $v) {
            if (is_array($v) && isset($v["data"])) {
                if ($v["data"]["id"] != $post->ID && !isset($relatedFormulas[$v["data"]["id"]])) {
                    $relatedFormulas[$v["data"]["id"]] = $v;
                }

            } else {
                if ($v->id != $post->ID && !isset($relatedFormulas[$v->id])) {
                    $relatedFormulas[$v->id] = $v;
                }
            }

        }
    }

    if (!empty($formula->getFormulaSheetDoc()) && intval($formula->getFormulaSheetDoc()) > 0) {
        $formulaDocResource = new ResourceEntity($formula->getFormulaSheetDoc());
        if (!$formulaDocResource->checkRoleAndRegion() || !empty($formulaDocResource->getResourcePptSource()) || !strstr($formulaDocResource->getResourceMediaType(), 'application/pdf')) {
            $formulaDocResource = null;
        }

        if (!empty($formulaDocResource)) {
            $formulaFilePdf[$formulaDocResource->getId()] = array("value" => $formulaDocResource, 'label' => $formula->getFormulaSheetDocLabel(), "icon" => "icon-formula-sheet");
        }
    }

    if (!empty($formula->getFormulaCardDoc()) && intval($formula->getFormulaCardDoc()) > 0) {
        $formulaDocResource = new ResourceEntity($formula->getFormulaCardDoc());
        if (!$formulaDocResource->checkRoleAndRegion() || !empty($formulaDocResource->getResourcePptSource()) || !strstr($formulaDocResource->getResourceMediaType(), 'application/pdf')) {
            $formulaDocResource = null;
        }

        if (!empty($formulaDocResource)) {
            $formulaFilePdf[$formulaDocResource->getId()] = array("value" => $formulaDocResource, 'label' => $formula->getFormulaCardDocLabel(), "icon" => "icon-formula-card");
        }
    }

}
?>

<?php get_header(); ?>

<main role="main">
    <div class="product-container product-container--detail">
        <div class="container">
            <div class="productDetail" data-product>
                <div class="productDetail__wrapping">
                    <div class="productDetail__header productDetail__header--formulaPage">
                        <div class="productDetail__header-image">
                            <?php if (!empty($iconImage)) : ?>
                                <img src="<?php echo $iconImage; ?>" alt="">
                            <?php endif; ?>
                        </div>
                        <div class="productDetail__header-groupTitle productDetail__header-groupTitle--formulaPage">
                            <h1 class="productDetail__header-title productDetail__header-title--formulaPage">
                                <?php echo $name; ?>
                            </h1>
                        </div>
                        <div class="productDetail__header-desc productDetail__header-desc--formulaPage">
                            <p><?php echo $code; ?></p>
                        </div>
                    </div>
                    <div class="productDetail__background" style="background-image: url(<?php echo $image; ?>)"></div>
                    <div class="productDetail__detail">
                        <div class="productDetail__detail-wrap">
                            <div class="productDetail__detail-right">
                                <div class="productDetail__detail-wrapper">
                                    <?php if (count($formulaFilePdf) > 0) : ?>
                                        <div class="productDetail__fileList">
                                            <ul>
                                                <?php foreach ($formulaFilePdf as $v) : ?>
                                                    <?php
                                                    $fileLink = $v["value"]->getMediaExternalURL();
                                                    $fileName = $v["value"]->getPostTitle();
                                                    if (!empty($v['label'])) {
                                                        $fileName = $v['label'];
                                                    }
                                                    $fileExt = '';
                                                    $extArr = explode('/', $v["value"]->getResourceMediaType());
                                                    if (count($extArr) > 0) {
                                                        $fileExt = $extArr[count($extArr) - 1];
                                                    }
                                                    ?>
                                                    <li>
                                                        <a  class="btn-gtm-pdf-download" href="<?php echo $fileLink; ?>"
                                                           title="<?php echo esc_html($fileName); ?>" rel="stylesheet"
                                                           target="_blank">
                                                            <i class="icon <?php echo $v["icon"];?>"></i>
                                                            <span class="text"><?php echo $fileName; ?>
                                                        <span class="extension"><?php echo !empty($fileExt) ? "($fileExt)" : ''; ?></span>
                                                    </span>
                                                        </a>
                                                    </li>
                                                <?php endforeach; ?>
                                            </ul>
                                        </div>
                                    <?php endif; ?>

                                    <div class="productDetail__information"
                                         style="background-image: url(<?php echo get_template_directory_uri(); ?>/assets/images/bg-attributes.png)">
                                        <div class="productDetail__information--content">
                                            <div class="productDetail__information--wrapper">
                                                <div class="component-heading-group --dark-mode">
                                                    <h2 class="component-heading-group__heading"><?php _e('Key Features'); ?>
                                                    </h2>
                                                </div>
                                                <?php if (!empty($formulaKeyFeatures)): ?>
                                                    <ul class="component-list">
                                                        <?php foreach ($formulaKeyFeatures['kf_info'] as $item): ?>
                                                            <li><?php echo !empty($item) ? $item : ''; ?>
                                                            </li>
                                                        <?php endforeach; ?>
                                                    </ul>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                        <div class="productDetail__information--content">
                                            <div class="productDetail__information--wrapper">
                                                <div class="component-heading-group --dark-mode">
                                                    <h2 class="component-heading-group__heading"><?php _e('How To Use'); ?>
                                                    </h2>
                                                </div>
                                                <?php if (!empty($formulaHowToUse)): ?>
                                                    <ul class="component-list">
                                                        <?php foreach ($formulaHowToUse['htu_info'] as $item): ?>
                                                            <li><?php echo !empty($item) ? $item : ''; ?>
                                                            </li>
                                                        <?php endforeach; ?>
                                                    </ul>
                                                <?php
                                                endif;
                                                ?>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="productDetail__detail-left">
                                <h2 class="productDetail__contentAttributes">
                                    <?php echo $description; ?>
                                </h2>
                            </div>
                        </div>
                    </div>
                    <div class="productDetail__request">
                        <div class="component-sample-band">
                            <div class="component-sample-band__wrap">
                                <div class="component-sample-band__content">
                                    <div class="component-sample-band__icon">
                                        <i class="icon icon-sample"></i>
                                    </div>
                                    <div class="component-sample-band__text"
                                         data-status-sample>
                                        <?php echo get_theme_mod('formula_add_sample_text', class_exists('BarnetDefaultText') ? BarnetDefaultText::FORMULA_ADD_SAMPLE_TEXT : ''); ?>
                                    </div>
                                </div>
                                <div class="component-sample-band__btn" data-group-btn-sample>
                                    <a class="btn btn-solid --dark-mode" href="#" title="<?php _e('Request Sample'); ?>"
                                       rel="stylesheet" data-add-sample
                                       data-opts-sample='{"id": "<?php echo $post->ID; ?>", "post_title": "<?php echo esc_html($name); ?>", "permalink": "<?php echo $formulaLink; ?>"}'><?php _e('Request Sample'); ?></a>
                                    <a class="btn btn-solid --dark-mode d-none" href="#"
                                       title="<?php _e('Submit Request Now'); ?>" rel="stylesheet"
                                       data-anchor-link><?php _e('Submit Request Now'); ?></a>
                                    <a class="btn d-none" href="#" title="<?php _e('Remove Sample'); ?>"
                                       rel="stylesheet" data-remove-sample
                                       data-opts-sample='{"id":"<?php echo $post->ID; ?>"}'><?php _e('Remove Sample'); ?></a>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="productDetail__areaDetail --mg-top-lg">
                        <?php if (count($ingredients) > 0) : ?>
                            <div class="component-heading-group">
                                <h2 class="component-heading-group__heading"><?php _e('Formula'); ?>
                                </h2>
                            </div>
                            <div class="component-table --mg-top-xs" data-table>
                                <table>
                                    <thead>
                                    <tr>
                                        <th><?php _e('Phase'); ?></th>
                                        <th><?php _e('Material Name'); ?></th>
                                        <th><?php _e('INCI'); ?></th>
                                        <th><?php _e('Supplier'); ?></th>
                                        <th><?php _e('Properties'); ?></th>
                                        <th><?php _e('%'); ?></th>
                                    </tr>
                                    </thead>
                                    <tbody>
                                    <?php $totalIngredients = 0;?>
                                    <?php foreach ($ingredients as $v) : ?>
                                    <?php $totalIngredients += floatval($v['f_percent']);?>
                                        <tr>
                                            <td><?php echo empty($v['phase']) ? '' : $v['phase']; ?></td>
                                            <td>
                                                <?php
                                                if (isset($v['f_linkto']) && intval($v['f_linkto']) > 0) {
                                                    echo '<a href="' . get_permalink(intval($v['f_linkto'])) . '" style="font-weight: 500; color: #0D9990">' . $v['f_material'] . '</a>';
                                                } else {
                                                    echo $v['f_material'];
                                                }
                                                ?>

                                            </td>
                                            <td><?php echo empty($v['f_inci']) ? '' : $v['f_inci']; ?></td>
                                            <td><?php echo empty($v['f_supplier']) ? '' : $v['f_supplier']; ?></td>
                                            <td><?php echo empty($v['f_properties']) ? '' : $v['f_properties']; ?></td>
                                            <td><?php echo empty($v['f_percent']) ? '' : $v['f_percent']; ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                    <?php if ($totalIngredients > 0) : ?>
                                    <?php
                                        $totalIngredients = round($totalIngredients, 2);
                                        ?>
                                    <tr>
                                        <td colspan="6" style="text-align: right">Total: <?php echo $totalIngredients; ?>%</td>
                                    </tr>
                                    <?php endif; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php endif; ?>
                        <?php if ($formulaShowFlexible) : ?>
                            <div class="productDetail__flexible"
                                 style="background-image: url(<?php echo get_template_directory_uri(); ?>/assets/images/bg-attributes-large.png">
                                <div class="productDetail__flexible--content"><i class="icon icon-transform"></i>
                                    <div class="component-heading-group --dark-mode">
                                        <h2 class="component-heading-group__heading"><?php _e('Flexible Formula'); ?></h2>
                                        <div class="component-heading-group__desc">
                                            <?php echo $formulaDescriptionFlexible; ?>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php endif; ?>
                    </div>
                    <?php if (count($processSteps) > 0) : ?>
                        <div class="productDetail__areaDetail --mg-top-lg">
                            <div class="productDetail__processing">
                                <div class="component-heading-group">
                                    <h2 class="component-heading-group__heading"><?php _e('Processing'); ?>
                                    </h2>
                                </div>
                                <ul class="component-hexagon-text --mg-top-sm">
                                    <?php foreach ($processSteps as $k => $v) : ?>
                                        <li>
                                            <span class="number"><?php echo $k; ?></span>
                                            <?php echo $v; ?>
                                        </li>
                                    <?php endforeach; ?>

                                </ul>
                            </div>
                        </div>
                    <?php endif; ?>
                    <div class="productDetail__areaDetail --mg-top-lg">
                        <div class="productDetail__analysis">
                            <?php $count1 = 0; $count2 = 0; foreach ($formulaKeyIngredients as $key):?>
                                <?php if (!empty($key['ki_product_cpt'])): ?>
                                    <?php 
                                        $product_only_for_code_list = 0;
                                        $product_only_for_code_list = get_post_meta(intval($key['ki_product_cpt']), 'product_only_for_code_list', TRUE);
                                        if (intval($product_only_for_code_list) == 1) {
                                            $count1++;
                                        }
                                    ?>
                                <?php endif;?>
                                <?php $count2++;?>
                            <?php endforeach;?>    
                            <?php if($count2 > $count1):?> 
                                <div class="component-heading-group">
                                    <h2 class="component-heading-group__heading"><?php _e('Key Ingredients'); ?>
                                    </h2>
                                </div>
                            <?php endif;?>
                            <?php
                            foreach ($formulaKeyIngredients as $key):?>
                                <?php if (!empty($key['ki_product_cpt'])): ?>
                                    <?php 
                                        //Remove product on list
                                        $product_only_for_code_list = 0;
                                        $product_only_for_code_list = get_post_meta(intval($key['ki_product_cpt']), 'product_only_for_code_list', TRUE);
                                        if (intval($product_only_for_code_list) == 1) {
                                            continue;
                                        }
                                    ?>
                                <?php endif;?>
                                <div class="component-heading-group --mg-top-md">
                                    <h2 class="component-heading-group__heading --size-md">
                                        <?php if (!empty($key['ki_product_cpt']) && !empty($key['ki_title'])): ?><a
                                            href="<?php echo get_permalink($key['ki_product_cpt']); ?>"><?php echo !empty($key['ki_title']) ? $key['ki_title'] : ''; ?></a>
                                        <?php else: echo !empty($key['ki_title']) ? $key['ki_title'] : ''; endif; ?>
                                    </h2>
                                    <div class="component-heading-group__desc"><?php echo !empty($key['ki_description']) ? $key['ki_description'] : ''; ?>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                    <?php if (count($specifications) > 0) : ?>
                        <div class="productDetail__areaDetail --mg-top-lg">
                            <div class="productDetail__specs">
                                <div class="component-heading-group">
                                    <h2 class="component-heading-group__heading"><?php _e('Formula Specifications'); ?>
                                    </h2>
                                </div>
                                <div class="productDetail__specs--wrapper"
                                     style="background-image: url(<?php echo get_template_directory_uri(); ?>/assets/images/bg-hexagon.png">
                                    <?php foreach ($specifications as $k => $v) : ?>
                                        <div class="productDetail__specs--group">
                                            <div class="productDetail__specs--name"><?php echo $v['s_label']; ?>:</div>
                                            <div class="productDetail__specs--content"><?php echo $v['s_specification']; ?></div>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                        </div>
                    <?php endif; ?>
                    <?php if (count($keyAttributes) > 0) : ?>
                        <div class="productDetail__areaDetail --mg-top-lg">
                            <div class="productDetail__certification">
                                <div class="productDetail__certification--wrapper">
                                    <?php
                                    $vMetaManager = new BarnetPostMetaManager($keyAttributes);
                                    foreach ($keyAttributes as $k => $v) : ?>
                                        <?php
                                        $keyMeta = $vMetaManager->getMetaData($v->ID);
                                        $img = '';
                                        if (isset($keyMeta["formula-attribute_media"])) {
                                            if (is_array($keyMeta["formula-attribute_media"])) {
                                                if (count($keyMeta["formula-attribute_media"]) > 0) {
                                                    $img = wp_get_attachment_url($keyMeta["formula-attribute_media"][0]);
                                                }
                                            } else if (intval($keyMeta["formula-attribute_media"]) > 0) {
                                                $img = wp_get_attachment_url(intval($keyMeta["formula-attribute_media"]));
                                            }

                                        }

                                        $img = $img ? $img : $barnet->getDefaultImage();

                                        if (!empty($img)) :
                                            ?>
                                            <div class="productDetail__certification--img">
                                                <img src="<?php echo $img; ?>" alt="img">
                                            </div>
                                        <?php endif; ?>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                        </div>
                    <?php endif; ?>
                    <?php if (!empty($formulaBase) || !empty($formulaVideoResource)) : ?>
                        <div class="productDetail__wrapping --mg-top-lg">
                            <div class="productDetail__areaDetail productDetail__areaDetail--bgGreen --pd-top-sm --pd-bottom-ml">
                                <div class="component-heading-group">
                                    <h2 class="component-heading-group__heading"><?php _e('About the Base'); ?>
                                    </h2>
                                    <div class="component-heading-group__desc">
                                        <?php echo $formulaBase; ?>
                                    </div>
                                </div>
                                <?php if (!empty($formulaVideoResource)) : ?>
                                    <?php
                                    $linkImage = $formulaVideoResource->getResourceImageURL();
                                    $nameVideo = $formulaVideoResource->getPostTitle();
                                    $linkVideo = $formulaVideoResource->getMediaExternalURL();
                                    $typeVideo = $formulaVideoResource->getResourceMediaType();
                                    $resourceMediaUrl = $formulaVideoResource->getMediaLocalURL();
                                    if (!empty($formulaVideoResource->getResourceTime())) {
                                        $nameVideo .= ' | <span class="text-icon icon-clock-sm">' . $formulaVideoResource->getResourceTime() . '</span>';
                                    }
                                    ?>
                                    <div class="component-video-control --mg-top-xs" data-gtm-view-video="<?php echo $formulaVideoResource->getPostTitle();?>" data-video>
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
                                    <p class="color-semi-dark-teal --mg-top-xs"><?php echo $nameVideo; ?></p>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endif; ?>
                    <div class="productDetail__areaDetail --mg-top-lg --pd-bottom-lg">
                        <?php if (count($formulaCollections) > 0) : ?>
                            <div class="component-heading-group">
                                <h2 class="component-heading-group__heading"><?php _e('See the Collection:'); ?>
                                </h2>
                            </div>
                            <div class="component-image-content">
                                <div class="component-image-content__wrapper slider-control --mg-top-ml">
                                    <?php foreach ($formulaCollections as $fcollection) : ?>
                                        <?php
                                        $fcollection = $fcollection['data'];
                                        $linkFCollection = get_permalink($fcollection['id']);
                                        $img = '';

                                        if (isset($fcollection['concept_thumbnail'])){
                                            if (is_array($fcollection['concept_thumbnail']) && count($fcollection['concept_thumbnail']) > 0) {
                                                $img = wp_get_attachment_url($fcollection['concept_thumbnail'][0]);
                                            } else if (intval($fcollection['concept_thumbnail']) > 0){
                                                $img = wp_get_attachment_url($fcollection['concept_thumbnail']);
                                            }

                                        }

                                        $img = $img ? $img : $barnet->getDefaultImage("concept_thumb");

                                        if (!empty($linkFCollection)) {
                                            $linkFCollection = add_query_arg("tab", "formula", $linkFCollection);
                                        }
                                        ?>
                                        <div class="component-image-content__item --dark-mode" data-slider-item>
                                            <div class="component-image-content__item--wrapper">
                                                <div class="component-image-content__item--img">
                                                    <?php if (!empty($img)) : ?>
                                                        <img src="<?php echo $img; ?>"
                                                             alt="<?php echo esc_html($fcollection['post_title']); ?>">
                                                    <?php endif; ?>
                                                </div>
                                                <div class="component-image-content__item--content">
                                                    <div class="component-image-content__item--heading"><?php echo $fcollection['post_title']; ?>
                                                    </div>
                                                </div>
                                                <?php if (!empty($linkFCollection)) : ?>
                                                    <a href="<?php echo $linkFCollection; ?>"
                                                       title="<?php echo esc_html($fcollection['post_title']); ?>"
                                                       rel="stylesheet"></a>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                        <?php endif; ?>
                    </div>

                </div>
                <?php if (count($relatedFormulas) > 0) : ?>
                    <div class="productDetail__related-fomular --mg-top-lg">
                        <div class="component-heading-group --dark-mode">
                            <h2 class="component-heading-group__heading"><?php _e('Related Formula'); ?>
                            </h2>
                        </div>
                        <div class="component-list-product --mg-top-sm">
                            <?php foreach ($relatedFormulas as $k => $v) : ?>
                                <?php
                                $fItem = new FormulaEntity($v['data']['id']);
                                $iconItem = $fItem->getFormulaIconBlack();
                                $linkItem = $fItem->getPermalink();
                                ?>
                                <div class="component-list-product__item --has-image">
                                    <?php if (!empty($iconItem)) : ?>
                                        <div class="component-list-product__image">
                                            <a href="<?php echo $linkItem; ?>"
                                               title="<?php echo esc_html($fItem->getPostTitle()); ?>" rel="stylesheet">
                                                <img src="<?php echo $iconItem; ?>" alt="">
                                            </a>
                                        </div>
                                    <?php endif; ?>
                                    <div class="component-list-product__wrap">
                                        <div class="component-list-product__title">
                                            <h3>
                                                <a href="<?php echo $linkItem; ?>"
                                                   title="<?php echo esc_html($fItem->getPostTitle()); ?>"
                                                   rel="stylesheet"><?php echo $fItem->getPostTitle(); ?></a>
                                            </h3>
                                        </div>
                                        <div class="component-list-product__desc">
                                            <?php echo $fItem->getPostExcerpt(); ?>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</main>

<?php get_footer(); ?>
