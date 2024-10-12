<?php
global $post;
$postMetas = get_post_meta($post->ID);
$nameMeta = $post->title;
$catName = "";

$cats = get_the_category($post->ID);
if (count($cats) > 0) {
    $catName = $cats[0]->name;
}

?>
<div class="product-detail-header">
    <h2 class="name"><?php echo $nameMeta; ?></h2>
    <p class="category"><?php echo $catName; ?></p>
</div>
<div class="product-detail-image">
    <img width="100%" src="<?php echo get_stylesheet_directory_uri() . '/assets/images/banner.png' ?>">
</div>