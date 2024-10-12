<?php
/* Template Name: Terms of use */

get_header(); ?>

    <main role="main">
        <div class="term-of-use">
            <div class="container">
                <div class="term-of-use__wrapper">
                    <div class="term-of-use__header">
                        <h1 class="--mg-bottom-ml"><?php echo get_the_title(); ?></h1>
                    </div>
                    <div class="term-of-use__content">
                        <?php the_content(); ?>
                    </div>
                </div>
            </div>
        </div>
    </main>

<?php get_footer(); ?>
