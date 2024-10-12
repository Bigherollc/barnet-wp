<?php

/**
 * The template for displaying archive pages
 *
 * @link https://developer.wordpress.org/themes/basics/template-hierarchy/
 *
 * @package Barnet
 */
get_header();
?>
<main id="main" class="post">
    <div class="post-container__wrapper">
        <div id="page-wrap-blog" class="container">
            <div class="wrap-single-post">
                <div class="row blog-listing__heading">
                    <div class="col-xs-12">
                        <div class="post-heading text-center">
                            <?php the_archive_title('<h1 class="post-title">', '</h1>'); ?>
                            <!-- <p class="post-subtitle">Insights, tips, and ideas from Barnet's Global Innovation Center.</p> -->
                        </div>
                    </div>
                </div>
                <div class="row blog-listing__body">
                    <div class="col-md-9 col-xs-12 post-content">
                        <?php if (have_posts()) : ?>
                            <section class="section section__all">
                                <div class="section-heading">
                                    <h2 class="section-title">All Posts</h2>
                                    <a href="<?php echo get_permalink(get_option('page_for_posts')); ?>" class="btn section-action">See All</a>
                                </div>
                                <div class="row posts-listing" data-ajax-url="<?php echo admin_url("admin-ajax.php") ?>">
                                    <?php if (have_posts()) : ?>
                                        <?php while (have_posts()) : the_post(); ?>
                                            <?php
                                            get_template_part('template-parts/post/blog-tile', get_post_format(), [
                                                'class' => 'col-xs-12 col-md-4',
                                                'show_excerpt' => true,
                                            ]);
                                            ?>
                                        <?php endwhile; ?>
                                    <?php endif; ?>
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