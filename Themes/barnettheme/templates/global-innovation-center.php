<?php
/* Template Name: Global Innovation Center */

global $post;
$pageId = $post->ID;
$pageTitle = $post->post_title;
$pageMetas = get_post_meta($pageId);
$backgroundImage = '';
$shortDes = '';

if (isset($pageMetas["p_short_description"]) && is_array($pageMetas["p_short_description"]) && count($pageMetas["p_short_description"]) > 0) {
    $shortDes = $pageMetas["p_short_description"][0];
}

get_header(); ?>

    <main role="main">
        <div class="global-innovation-center">
            <div class="container">
                <div class="global__groupTitle --mg-bottom-lg --mg-top-lg">
                    <div class="component-heading-group --dark-mode">
                        <h2 class="component-heading-group__heading --size-lg to-uppercase">
                            <?php echo $pageTitle; ?>
                        </h2>
                        <div class="component-heading-group__desc">
                            <?php echo $shortDes;?>
                        </div>
                    </div>
                </div>
            </div>
            <?php the_content(); ?>
            <?php if (!is_user_logged_in()) : ?>
            <div class="container">
                <div class="product__signIn --mg-top-lg">
                    <div class="component-signin-box" style="background-image: url(<?php echo get_template_directory_uri(); ?>/assets/images/product/bg-signin.png);">
                        <div class="component-signin-box__title">
                            <?php echo get_theme_mod('gic_signin_title', class_exists('BarnetDefaultText') ? BarnetDefaultText::GIC_SIGNIN_TITLE : ''); ?>
                        </div>
                        <div class="component-signin-box__box">
                            <div class="component-signin-box__signin">
                                <div class="component-signin-box__text">
                                    <?php echo get_theme_mod('gic_registered_title', class_exists('BarnetDefaultText') ? BarnetDefaultText::GIC_REGISTER_TITLE : ''); ?>
                                </div>
                                <div class="component-signin-box__list">
                                    <div class="component-list">
                                        <?php echo get_theme_mod('gic_registered_text', class_exists('BarnetDefaultText') ? BarnetDefaultText::GIC_REGISTER_TEXT : ''); ?>
                                    </div>
                                </div>
                                <div class="component-signin-box__link">
                                    <a href="<?php echo wp_login_url();?>" title="<?php _e('Sign in');?>" class="btn btn-normal --dark-mode"><?php _e('Sign in');?></a>
                                </div>
                            </div>
                            <div class="component-signin-box__request">
                                <div class="component-signin-box__titleRequest">
                                    <?php echo get_theme_mod('gic_new_customer', class_exists('BarnetDefaultText') ? BarnetDefaultText::GIC_NEW_CUSTOMER : ''); ?>
                                </div>
                                <a title="<?php _e('Request Access');?>" href="<?php echo wp_registration_url();?>" class="btn btn-solid --dark-mode btn-gtm-request-access"><?php _e('Request Access');?></a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <?php endif;?>

        </div>
    </main>

<?php get_footer(); ?>
