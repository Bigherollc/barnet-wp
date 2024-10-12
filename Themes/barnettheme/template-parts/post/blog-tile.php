<?php
global $post;
$is_premium = get_post_meta($post->ID, '_checkbox_check', true);
$excerpt = mb_strimwidth(strip_tags($post->post_content), 0, 300, '...');
?>

<div data-post-id="<?php echo $post->ID ?>" id="post-<?php echo $post->ID ?>" class="post-item <?php echo $args['class']; ?>">
	<div class="post-item__image">
		<a href="<?php echo get_permalink($post->ID); ?>">
			<img class="img-thumbnail" src="<?php echo get_the_post_thumbnail_url($post->ID); ?>" alt="<?php echo $post->post_title; ?>">
		</a>
		<?php if(boolval($is_premium)): ?>
		<div class="post-item__locked">
			<i class="fa fa-lock"></i>
		</div>
		<?php endif; ?>
	</div>

	<?php the_title(sprintf('<h3 class="post-item__title"><a href="%s" rel="bookmark">', esc_url(get_permalink())), '</a></h3>'); ?>

	<?php if ($args['show_excerpt']) : ?>
		<div class="post-item__content">
			<?php echo $excerpt; ?>
		</div>
	<?php endif; ?>
</div>