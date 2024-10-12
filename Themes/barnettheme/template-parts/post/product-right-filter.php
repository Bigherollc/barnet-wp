<?php
global $wp;

$arrCatCount = $args["arrCatCount"];
$smartfilter = $args["smartfilter"];
if (!$arrCatCount) {
    $arrCatCount = array();
}
$requestGet = array();
if ($_GET) {
    $requestGet = $_GET;
}

$current_url = site_url(add_query_arg(array($requestGet), "/" . $wp->request . "/"));
$nameFilter = "smartfilter";
$taxonomies = get_object_taxonomies(array("post_type" => "barnet-product"));

?>

<div class="service-list-item">
    <p class="left-menu-header">ALL PRODUCTS</p>
    <p class="left-menu-script">We specialize in Active Ingredients for the use in personal care products and unique
        System Formers inspired by East Asian trends</p>

    <?php
    foreach ($taxonomies as $taxonomy) :
        $terms = get_terms($taxonomy, array('hide_empty' => false));
        $exp = explode("-", $taxonomy);
        $nameTaxonomy = $exp[count($exp) - 1];
        if (count($terms) > 0) {

            ?>
            <p class="group-category"><?php echo $nameTaxonomy; ?></p>
            <ol class="list">
                <?php
                foreach ($terms as $term):
                    $urlFilter = $current_url;
                    $nameFilter = $nameTaxonomy;
                    $curFilter = false;
                    $numberPosts = 0;
                    if (isset($arrCatCount[$term->term_id])) {
                        $numberPosts = $arrCatCount[$term->term_id];
                    }

                    if (!isset($smartfilter[$nameFilter])) {
                        $smartfilter[$nameFilter] = array();
                    }

                    if (isset($smartfilter[$nameFilter][$term->term_id])) {
                        unset($smartfilter[$nameFilter][$term->term_id]);
                        $curFilter = true;

                    } else {
                        $smartfilter[$nameFilter][$term->term_id] = $term->term_id;
                    }


                    $urlFilter = add_query_arg($nameFilter, implode(",", $smartfilter[$nameFilter]), $current_url);
                    if ($curFilter) {
                        $smartfilter[$nameFilter][$term->term_id] = $term->term_id;
                    } else {
                        unset($smartfilter[$nameFilter][$term->term_id]);
                    }
                    $inputChecked = $curFilter ? ' checked="checked"' : '';;


                    ?>
                    <li>
                        <a href="<?php echo $urlFilter; ?>" title="<?php echo $term->name; ?>">
                            <input class="inp-cbx" id="cbx<?php echo $term->term_id; ?>" type="checkbox"
                                   style="display: none"
                                   name="<?php echo $nameFilter; ?>[]"
                                   value="<?php echo $term->term_id; ?>"<?php echo  $inputChecked;?>/>
                            <label class="cbx" sfor="cbx<?php echo $term->term_id; ?>">
                                <span>
                                    <svg width="12px" height="10px" viewbox="0 0 12 10">
                                        <polyline points="1.5 6 4.5 9 10.5 1"></polyline>
                                    </svg>
                                </span>
                                <span><?php echo $term->name; ?> (<?php echo $numberPosts; ?>)</span>
                            </label>
                        </a>
                    </li>
                <?php endforeach; ?>
            </ol>
            <?php
        }

    endforeach;
    ?>
</div>