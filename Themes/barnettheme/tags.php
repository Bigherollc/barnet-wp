<?php
/* Template Name: Tags */
get_header();
$tags = get_tags([
	'orderby' => 'name',
	'order' => 'ASC',
	'hide_empty' => true,
]);

?>
<main id="main" class="post">
	<div class="post-container__wrapper">
		<div id="page-wrap-blog" class="container">
			<div class="wrap-single-post">
				<div class="row blog-listing__heading">
					<div class="col-xs-12">
						<div class="post-heading text-center">
							<h1 class="post-title">Tags</h1>
							<!-- <p class="post-subtitle">Insights, tips, and ideas from Barnet's Global Innovation Center.</p> -->
						</div>
					</div>
				</div>
				<div class="row blog-listing__body">
					<div class="col-md-9 col-xs-12 post-content">
						<?php if (count($tags)) : ?>
							<section class="section section__all">
								<div class="row posts-listing" data-ajax-url="<?php echo admin_url("admin-ajax.php") ?>">
									<?php foreach ($tags as $category) : ?>
										<?php
										get_template_part('template-parts/post/category-tile', get_post_format(), [
											'class' => 'col-xs-12 col-md-4 col-sm-6',
											'show_excerpt' => true,
											'category' => $category,
										]);
										?>
									<?php endforeach; ?>
								</div>
							</section>
						<?php else : ?>
							<?php get_template_part('template-parts/content', 'none'); ?>
						<?php endif; ?>
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