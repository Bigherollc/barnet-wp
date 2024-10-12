<?php
/* Template Name: Main Landing Page */

$user = new UserEntity();
if (!$user->getId()) {
    wp_redirect('/');
}

global $post;
$pageId = $post->ID;
$pageTitle = $post->post_title;
$pageMetas = get_post_meta($pageId);
$backgroundImage = '';
$shortDes = '';
$pageStyle = '';
$classTitle = '';
$classContent = '';
if (!empty($post->post_name)) {
    $classContent = '--'.$post->post_name;
}

if (isset($pageMetas["p_short_description"]) && is_array($pageMetas["p_short_description"]) && count($pageMetas["p_short_description"]) > 0) {
    $shortDes = $pageMetas["p_short_description"][0];
}
if (isset($pageMetas["p_style"]) && is_array($pageMetas["p_style"]) && count($pageMetas["p_style"]) > 0) {
    $pageStyle = $pageMetas["p_style"][0];
}

if ($pageStyle == "dark") {
    $classTitle = " --dark-mode";
}

get_header(); ?>

    <main role="main">
        <div class="landing <?php echo $classContent; ?>">
            <div class="container">
                <div class="landing-wrapper">
                    <div class="landing__groupTitle --mg-bottom-lg">
                        <div class="component-heading-group<?php echo $classTitle;?>">
                            <h2 class="component-heading-group__heading --size-lg to-uppercase">
                                <?php echo $pageTitle; ?>
                            </h2>
                            <div class="component-heading-group__desc"><?php echo $shortDes;?>
                            </div>
                        </div>
                    </div>
                    <?php the_content(); ?>
                </div>
            </div>
        </div>
    </main>
<?php get_footer(); ?>