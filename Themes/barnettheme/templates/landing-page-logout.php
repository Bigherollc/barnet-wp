<?php
/* Template Name: Logout Landing Page */

$user = new UserEntity();
if ($user->getId()) {
    wp_redirect('/');
}
global $post;
$pageId = $post->ID;
$pageMetas = get_post_meta($pageId);
$pageShortDes = '';
$pageTitle = '';

if (isset($pageMetas["p_title"]) && is_array($pageMetas["p_title"]) && count($pageMetas["p_title"]) > 0) {
    $pageTitle = $pageMetas["p_title"][0];
}
if (isset($pageMetas["p_short_description"]) && is_array($pageMetas["p_short_description"]) && count($pageMetas["p_short_description"]) > 0) {
    $pageShortDes = $pageMetas["p_short_description"][0];
}

$style_page = get_post_meta($post->ID, 'p_style', TRUE);
get_header(); ?>

    <div class="landing-logout">
        <div class="landing-logout__groupTitle">
            <div class="container">
                <div class="component-heading-group <?php echo $style_page == 'light' ? '' : '--dark-mode'; ?>">
                    <h2 class="component-heading-group__heading --size-lg to-uppercase"><?php
                        if (!empty($pageTitle)) {
                            echo $pageTitle;
                        }
                        ?>
                    </h2>
                    <div class="component-heading-group__desc"><?php
                        if (!empty($pageShortDes)) {
                            echo $pageShortDes;
                        }
                        ?>
                    </div>
                </div>
            </div>
        </div>
        <?php the_content();?>
        <div class="landing-logout__signin-box --mg-top-xl">
            <div class="container">
                <div class="component-signin-box" style="background-image: url(<?php echo get_template_directory_uri(); ?>/assets/images/product/bg-signin.png)">
                    <div class="component-signin-box__title">Sign In for Details</div>
                    <div class="component-signin-box__box">
                        <div class="component-signin-box__signin">
                            <div class="component-signin-box__text">Registered users get access to full product information and related resources:</div>
                            <div class="component-signin-box__list">
                                <ul class="component-list">
                                    <li>Specifications & Data Sheets</li>
                                    <li>Videos & Presentations</li>
                                    <li>Starting Formulas</li>
                                </ul>
                            </div>
                            <div class="component-signin-box__link"><a href="/login" class="btn btn-normal <?php echo $style_page == 'light' ? '' : '--dark-mode'; ?>">Sign in</a></div>
                        </div>
                        <div class="component-signin-box__request">
                            <div class="component-signin-box__titleRequest">New Customer?</div><a class="btn btn-solid <?php echo $style_page == 'light' ? '' : '--dark-mode'; ?> btn-gtm-request-access" href="/register" title="Request Access" rel="stylesheet">Request Access</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

<?php get_footer(); ?>
