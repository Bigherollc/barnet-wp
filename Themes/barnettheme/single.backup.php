<?php
/**
 * The template for displaying all single posts
 *
 * @link https://developer.wordpress.org/themes/basics/template-hierarchy/#single-post
 *
 * @package Barnet
 */

get_header();
?>

	<main id="primary" class="site-main">
		test
<div id="main">
<div id="page-wrap-blog" class="container">
<div class="wrap-single-post">
	<div class="wrap-content-sidebar">
	<div id="content" class="span12 single">
	    
		<?php
		while ( have_posts() ) :
			the_post();
			get_template_part( 'template-parts/content', get_post_type() );
					// If comments are open or we have at least one comment, load up the comment template.
			if ( comments_open() || get_comments_number() ) :
				comments_template();
			endif;

		endwhile; // End of the loop.
		?>
<div id="sidebar-primary" class="sidebar">
	<?php dynamic_sidebar( 'primary' ); ?>
</div></div></div></div></div></div></div>
	</main><!-- #main -->

<?php
get_sidebar();
get_footer();