<?php get_header(); ?>
<?php
	global $post;

	while ( have_posts() ) :
		the_post();
		?>
	
	<main id="content" <?php post_class( 'site-main' ); ?> role="main">
			<header class="page-header">
				<a href="<?php the_permalink(); ?>"><?php the_title('<h1 class="entry-title">', '</h1>'); ?></a>
			  
			</header>
		<div class="page-content">
			<?php the_content(); ?>
			<div class="post-tags">
				<?php the_tags( '<span class="tag-links">' . __( 'Tagged ', 'hello-elementor' ), null, '</span>' ); ?>
			</div>
			<?php wp_link_pages(); ?>
		</div>
	
		<?php comments_template(); ?>
	</main>
	
		<?php
	endwhile;
?>
<?php get_footer(); ?>