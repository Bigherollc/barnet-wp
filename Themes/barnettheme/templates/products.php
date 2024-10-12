<?php
/* Template Name: Products */

get_header(); ?>

<?php
$args = [
    'orderby' => 'post_title',
    'order' => 'ASC',
    'post_type' => "barnet-product",
    'numberposts' => -1,
];

$smartfilter = array();
$taxonomies = get_object_taxonomies(array("post_type" => "barnet-product"));
$filterAllow = false;
foreach ($taxonomies as $taxonomy) {
    $exp = explode("-", $taxonomy);
    $nameTaxonomy = $exp[count($exp) - 1];
    $smartfilter[$nameTaxonomy] = array();
    if (isset($_GET[$nameTaxonomy])) {
        $tmpfilter = trim($_GET[$nameTaxonomy]);
        $exfiler = explode(",", $tmpfilter);
        foreach ($exfiler as $k) {
            $k = intval($k);
            if ($k > 0) {
                $smartfilter[$nameTaxonomy][$k] = $k;
                $filterAllow = true;
            }
        }
    }
}

if ($filterAllow) {
    $args['tax_query'] = array('relation' => 'OR');
    foreach ($smartfilter as $k => $v) {
        $args['tax_query'][] = array("taxonomy" => "product-" . $k, 'field' => 'term_id', 'terms' => $v);
    }
}

$posts = get_posts($args);

$arrCatCount = array();
foreach ($posts as $post) {
    foreach ($taxonomies as $taxonomy) {
        $terms = get_the_terms($post->ID, $taxonomy);
        foreach ($terms as $term) {
            if (!isset($arrCatCount[$term->term_id])) {
                $arrCatCount[$term->term_id] = 0;
            }
            $arrCatCount[$term->term_id] += 1;
        }
    }
}
?>
    <div class="container">
        <div class="header-wrapper products-wrapper">
            <?php get_template_part('template-parts/post/product-right-filter', "",
                array("arrCatCount" => $arrCatCount, "smartfilter" => $smartfilter)); ?>
            <?php get_template_part('template-parts/post/product-all-items', "", array("posts" => $posts)); ?>
        </div>
    </div>

<?php get_footer(); ?>