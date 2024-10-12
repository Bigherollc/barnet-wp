<?php
/**
 * Template part for displaying page content in page.php
 *
 * @link https://developer.wordpress.org/themes/basics/template-hierarchy/
 *
 * @package Barnet
 */

?>

<article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
	<header class="entry-header">
<?php if(is_home() || is_front_page() || is_page("any_additional_page")){
   // do nothing
} else {
   the_title( '<h1 class="entry-title">', '</h1>' );
} ?>
		
	</header><!-- .entry-header -->

	<?php post_thumbnail(); ?>

	<div class="entry-content">
		<?php	the_content();		?>
	</div><!-- .entry-content -->

</article><!-- #post-<?php the_ID(); ?> -->
