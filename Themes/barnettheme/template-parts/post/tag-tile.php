<?php
$tag = $args['tag'];
$tag_image_id = get_term_meta($tag->term_id, 'image', true);
$excerpt = mb_strimwidth(strip_tags($tag->tag_description ? $tag->tag_description : ''), 0, 300, '...');

$image_src = '';
if ($tag_image_id) {
    $image_src = wp_get_attachment_image_url($ag_image_id);
}
?>

<div id="post-<?php echo $tag->term_id ?>" class="post-item <?php echo $args['class']; ?>">
    <?php if ($image_src) : ?>
        <div class="post-item__image">
        <a href="<?php echo get_term_link($tag->term_id); ?>">
            <img class="img-thumbnail" src="<?php echo $image_src; ?>" alt="<?php echo $tag->name; ?>">
        </a>
        </div>
    <?php endif; ?>

    <h3 class="post-item__title">
        <a href="<?php echo get_term_link($tag->term_id) ?>" rel="bookmark">
            <?php echo $tag->name; ?>
        </a>
    </h3>

    <?php if ($args['show_excerpt']) : ?>
        <div class="post-item__content">
            <?php echo $excerpt; ?>
        </div>
    <?php endif; ?>
</div>