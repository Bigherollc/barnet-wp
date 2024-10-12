<?php

/**
 * The template for displaying all single posts
 *
 * @link https://developer.wordpress.org/themes/basics/template-hierarchy/#single-post
 *
 * @package Barnet
 */

get_header();
global $post;

$post_categories = get_the_category($post->ID);
$wp_query = new WP_Query([
	'post_type' => 'post',
	'posts_per_page' => 3,
	'orderby' => 'post_date',
	'order' => 'DESC',
	'cat__in' => array_map(function ($category) {
		return $category->term_id;
	}, $post_categories),
]);

$is_premium = get_post_meta($post->ID, '_checkbox_check', true);
$is_content_locked = false;
if(boolval($is_premium)) {
	$user = wp_get_current_user();
	$allowed_roles = get_option('post_roles_restriction', []);
	$user_roles = is_user_logged_in() ? (array) $user->roles : [];
	$intersect_roles = array_intersect($user_roles, $allowed_roles);

	$is_content_locked = !is_user_logged_in() || count($intersect_roles) <= 0;
}
// echo '<pre>';
// var_dump(boolval($is_premium));
// echo '</pre>';
// die;
?>

<main id="main" class="post post-single">
	<div class="post-container__wrapper">
		<div id="page-wrap-blog" class="container">
			<div class="wrap-single-post">
				<div class="row">
					<div class="col-xs-12">
						<?php # display breadcrums 
						?>
					</div>
				</div>
				<div class="row post-main">
					<div class="col-xs-12 col-md-9 post-single__article">
						<?php get_template_part('template-parts/content', get_post_type(), [
							'is_locked' => $is_premium && $is_content_locked
						]); ?>
					</div>
					<div class="col-md-3 col-xs-12 post-single__sidebar">
						<div class="post-sidebar">
							<?php dynamic_sidebar('post-sidebar'); ?>
						</div>
					</div>
				</div>
			</div>
		</div>
		<div class="post-footer">
			<div class="container">

				<?php if ($wp_query->have_posts()) : ?>
					<div class="row post-related">
						<div class="col-xs-12">
							<div class="row">
								<?php
								while ($wp_query->have_posts()) {
									$wp_query->the_post();
									get_template_part('template-parts/post/blog-tile', get_post_type(), [
										'class' => 'col-xs-12 col-md-4',
										'show_excerpt' => true
									]);
								}
								?>
							</div>
						</div>
					</div>
				<?php endif; ?>
				<div class="row post-cta">
					<div class="col-xs-12">
						<?php get_template_part('template-parts/common/block-cta'); ?>
					</div>
				</div>
			</div>
		</div>
	</div>
</main><!-- #main -->

<?php
get_footer();
