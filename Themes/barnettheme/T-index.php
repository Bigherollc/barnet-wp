<?php

// query the featured ones first
$featured_posts = new WP_Query([
	'post_type' => 'post',
	'posts_per_page' => 1,
	'orderby' => 'post_date',
	'order' => 'DESC',
	'meta_query' => [
		[
			'key' => 'is_featured',
			'value' => '1',
			'compare' => '=',
		],
	]
]);

$featured_posts_id = array_map(function ($post) {
	return $post->ID;
}, $featured_posts->posts);

// get featured category using the featured post.
$featured_categories = [];
if (count($featured_posts_id)) {
	$featured_categories = wp_get_post_categories($featured_posts_id[0], [
		'fields' => 'all',
		'order' => 'DESC',
		'post_per_page' => 2,
	]);
}

// get terms for the featured category
// foreach($featured_categories as $category) {
// 	$featured_categories[$category->term_id] = get_term($category->term_id);
// }
// echo '<pre>';
// var_dump($featured_categories);
// echo '</pre>';
// die;

// then query for the rest of the posts
$recent_posts = new WP_Query([
	'post_type' => 'post',
	'posts_per_page' => 6,
	'orderby' => 'post_date',
	'order' => 'DESC',
	'post__not_in' => $featured_posts_id,
]);
?>

<?php get_header(); ?>

<main id="main" class="post">
	<div class="post-container__wrapper">
		<div id="page-wrap-blog" class="container">
			<div class="wrap-single-post">
				<div class="row blog-listing__heading">
					<div class="col-xs-12">
						<div class="post-heading text-center">
							<h1 class="post-title"><?php single_post_title(); ?></h1>
							<p class="post-subtitle">Insights, tips, and ideas from Barnet's Global Innovation Center.</p>
						</div>
					</div>
				</div>
				<div class="row blog-listing__body">
					<div class="col-md-9 col-xs-12 post-content">
						<section class="section section__featured">
							<?php if ($featured_posts->have_posts()) : ?>
								<div class="row">
									<?php
									$index = 0;
									while ($featured_posts->have_posts()) {
										$featured_posts->the_post();
										get_template_part('template-parts/post/blog-tile', get_post_format(), [
											'class' => $index == 0 ? 'col-xs-12 post-item--featured' : 'col-xs-12 col-md-6',
											'show_excerpt' => true,
										]);
										$index++;
									}
									?>
								</div>
							<?php endif; ?>
						</section>
						<?php if (count($featured_categories) > 0) : ?>
							<section class="section section__featured-category">
								<div class="section-heading">
									<h2 class="section-title">Featured Category Name</h2>
									<a href="<?php echo get_permalink(get_page_by_path('categories')) ?>" class="btn section-action">See All</a>
								</div>
								<div class="row">
									<?php foreach ($featured_categories as $category) {
										get_template_part('template-parts/post/category-tile', get_post_format(), [
											'class' => 'col-xs-12 col-md-6',
											'show_excerpt' => true,
											'category' => $category,
										]);
									} ?>
								</div>
							</section>
						<?php endif; ?>
						<section class="section section__all">
							<div class="section-heading">
								<h2 class="section-title">Recent Posts</h2>
								<a href="<?php echo get_permalink(get_option('page_for_posts')); ?>" class="btn section-action">See All</a>
							</div>
							<div class="row posts-listing" data-ajax-url="<?php echo admin_url("admin-ajax.php") ?>">
								<?php if ($recent_posts->have_posts()) : ?>
									<?php while ($recent_posts->have_posts()) : $recent_posts->the_post(); ?>
										<?php
										get_template_part('template-parts/post/blog-tile', get_post_format(), [
											'class' => 'col-xs-12 col-md-4',
											'show_excerpt' => false,
										]);
										?>
									<?php endwhile; ?>
								<?php endif; ?>
							</div>

							<!-- TODO: add pagination here -->
							<?php if ($recent_posts->post_count < $recent_posts->found_posts) : ?>
								<div class="div pagination text-center">
									<div class="pagination__btn-wrapper">
										<a href="#" class="btn pagination__btn" data-page="2">
											See all
										</a>
										<div class="pagination__loader text-center" style="display: none; margin-top: 10px;">
											<i class="fa fa-spinner fa-spin fa-3x fa-fw" aria-hidden="true"></i>
											<span class="sr-only">Loading...</span>
										</div>
									</div>
								</div>
							<?php endif; ?>
						</section>
					</div>
					<div class="col-md-3 col-xs-12 post-sidebar__wrapper">
						<div class="post-sidebar">
							<?php dynamic_sidebar('post-sidebar'); ?>
						</div>
					</div>
				</div>
			</div>
		</div>

		<div class="post-footer">
			<div class="container">
				<div class="row blog-listing__cta">
					<div class="col-xs-12">
						<?php get_template_part('template-parts/common/block-cta'); ?>
					</div>
				</div>
			</div>
		</div>
	</div>
</main>
<?php get_footer(); ?>