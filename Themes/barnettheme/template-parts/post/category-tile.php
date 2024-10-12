<?php
$category = $args['category'];
$category_image_id = get_term_meta($category->term_id, 'image', true);
$excerpt = mb_strimwidth(strip_tags($category->category_description ? $category->category_description : ''), 0, 300, '...');

$image_src = '';
if ($category_image_id) {
    $image_src = wp_get_attachment_image_url($category_image_id);
}
?>

<div id="post-<?php echo $category->term_id ?>" class="post-item <?php echo $args['class']; ?>">
    <?php if ($image_src) : ?>
        <div class="post-item__image">
        <a href="<?php echo get_term_link($category->term_id); ?>">
            <img class="img-thumbnail" src="<?php echo $image_src; ?>" alt="<?php echo $category->name; ?>">
        </a>
        </div>
    <?php endif; ?>

    <h3 class="post-item__title">
        <a href="<?php echo get_term_link($category->term_id) ?>" rel="bookmark">
            <?php echo $category->name; ?>
        </a>
    </h3>

    <?php if ($args['show_excerpt']) : ?>
        <div class="post-item__content">
            <?php echo $excerpt; ?>
        </div>
    <?php endif; ?>
</div>