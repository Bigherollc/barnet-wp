<?php

/**
 * Template part for displaying posts
 *
 * @link https://developer.wordpress.org/themes/basics/template-hierarchy/
 *
 * @package Barnet
 */
// var_dump(get_the_tags($post->ID));
// die;

$tags = get_the_tags($post->ID);
?>

<article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
	<div class="post-single__header entry-header">
<?php if(is_home() || is_front_page() || is_page("any_additional_page")){
   // do nothing
} else {
   the_title( '<h1 class="entry-title">', '</h1>' );
} ?>
	</div>

	<div class="row post-single__image">
		<div class="col-xs-12">
			<div class="entry-image">
				<?php the_post_thumbnail('full', ['class' => 'img-fluid']); ?>
			</div>
		</div>
	</div>

	<div class="row post-single__content <?php echo $args['is_locked'] ? '--locked' : '' ?>">
		<div class="col-xs-12 post-content">
			<div class="entry-content">
				<?php if ($args['is_locked']) {
					echo wp_trim_words(get_the_content(), 100);
				} else {
					the_content();
				}
				?>
			</div>
		</div>
		<?php if ($args['is_locked']) : ?>
			<div class="col-xs-12 paywall-container">
				<div class="paywall">
					<h2 class="paywall-heading"><i class="fa fa-lock"></i> Member Exclusive</h2>
					<p class="paywall-content">This article requires membership for access. Full access is usually granted within 24 hours. Once approved, you will have access to insights, product informations, starting formulas and more.</p>
					<a href="<?php echo '/register'; ?>" class="paywall-signup btn btn-primary btn-xl">Request Access</a>
					<p class="paywall-login">Already have an account? <a href="/login">Sign in</a></p>
				</div>
			</div>
		<?php endif; ?>
	</div>
	<?php if (!$args['is_locked']) : ?>
	<?php if (is_array($tags) && count($tags) > 0) : ?>
		<section class="section">
			<div class="row">
				<div class="col-xs-12">
					<h4 class="section-title">Related Topics</h4>
					<div class="tags">
						<?php foreach ($tags as $tag) : ?>
							<a class="tag" href="<?php echo get_tag_link($tag->term_id); ?>">
								<span><?php echo $tag->name; ?></span>
							</a>
						<?php endforeach; ?>
					</div>
				</div>
			</div>
		</section>
	<?php endif; ?>
	<?php endif; ?>
</article>