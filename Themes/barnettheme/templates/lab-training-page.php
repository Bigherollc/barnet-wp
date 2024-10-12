<?php
/* Template Name: Lab Training Page*/

global $post;
$page_id = $post->ID;
$page_title = $post->post_title;

$description = get_post_meta($page_id, 'p_short_description', true);
$pageTitleMeta = get_post_meta($page_id, 'p_title', true);
$bgimage_id = get_post_meta($page_id, 'p_background_image', true);
if (!empty($pageTitleMeta)) {
    $page_title = $pageTitleMeta;
}

$args = [
    'orderby' => 'post_title',
    'order' => 'ASC',
    'post_type' => 'lab-training',
    'numberposts' => -1,
];

$posts = get_posts($args);

$arr_tmp = array();
foreach ($posts as $p) {
    $number = get_post_meta($p->ID, 'lab_number', true);
    $lab_title = get_post_meta($p->ID, 'lab_title', true);
    $lab_code = get_post_meta($p->ID, 'lab_code', true);
    $lab_description = get_post_meta($p->ID, 'lab_description', true);
    $lesson = get_post_meta($p->ID, 'group_lessions', true);
    $arr_lab = array(
        'number' => $number,
        'lab_title' => $lab_title,
        'lab_code' => $lab_code,
        'lab_description' => $lab_description,
        'lesson' => $lesson
    );
    $arr_tmp[$number] = $arr_lab;
}
ksort($arr_tmp, SORT_NUMERIC);

get_header();
?>

<main role="main">
    <div class="lab-training">
        <div class="lab-training__heading"
             style="background-image: url('<?php echo get_stylesheet_directory_uri() . '/assets/images/bg-hexagon.png'; ?>')">
            <div class="container">
                <div class="component-heading-group --dark-mode">
                    <h2 class="component-heading-group__heading --size-lg"><?php echo !empty($page_title) ? $page_title : ''; ?>
                    </h2>
                    <div class="component-heading-group__desc">
                        <?php if (empty($description)): ?>
                            <p><?php _e('The Global Innovation Center team offers collaborative hands-on training customized to meet the unique needs of our customers.'); ?></p>
                            <p><?php _e('Courses range from basic emulsion theory, applied training on concepts and trends, to personalized training.'); ?></p>
                        <?php else: ?>
                            <p><?php
                                $description = preg_replace("/[\r\n]/","</p><p>",$description);
                                echo str_replace("<p></p>", "", $description); ?>
                            </p>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
        <div class="lab-training__accrodion --mg-top-lg">
            <div class="container">
                <div class="lab-training__accrodion-wrap">
                    <div class="component-hexagon-content">
                        <?php
                        if (!empty($arr_tmp)):
                            foreach ($arr_tmp as $key => $value):
                                ?>
                                <div class="component-hexagon-content__package"
                                     style="background-image: url('<?php echo get_stylesheet_directory_uri() . '/assets/images/bg-polygon-fill-content.svg'; ?>'); background-repeat: no-repeat;">
                                    <div class="component-hexagon-content__title"><?php echo !empty($value['lab_title']) ? $value['lab_title'] : ''; ?></div>
                                    <div class="component-hexagon-content__code"><?php echo !empty($value['lab_code']) ? $value['lab_code'] : ''; ?></div>
                                    <div class="component-hexagon-content__desc"><?php echo !empty($value['lab_description']) ? $value['lab_description'] : ''; ?></div>
                                    <div class="component-accrodion" data-accrodion>
                                        <?php if (isset($value['lesson'])): ?>
                                            <?php foreach ($value['lesson'] as $key): ?>
                                                <div class="component-accrodion__item" data-item>
                                                    <div class="component-accrodion__toggle" data-toggle>
                                                        <span><?php if (!empty($key['lession_title'])) {
                                                                echo $key['lession_title'];
                                                            } ?></span></div>
                                                    <?php if (!empty($key['lession_content'])): ?>
                                                        <div class="component-accrodion__dropdown" data-dropdown>
                                                            <?php echo $key['lession_content']; ?>
                                                        </div>
                                                    <?php endif; ?>
                                                </div>
                                            <?php endforeach; ?>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            <?php
                            endforeach;
                        endif;
                        ?>
                    </div>
                </div>
            </div>
        </div>
        <?php the_content();?>
        <div class="lab-training__btn --mg-top-lg">
            <div class="container">
                <div class="lab-training__btn-wrap"
                     style="background-image:  url('<?php echo get_stylesheet_directory_uri() . '/assets/images/bg-hexagon.png'; ?>')">
                    <a class="btn btn-solid --dark-mode" href="/lab-day-request" title="Request Training"
                       rel="stylesheet">
                        <?php echo get_theme_mod('request_training_text', class_exists('BarnetDefaultText') ? BarnetDefaultText::REQUEST_TRAINING_TEXT : ''); ?>
                    </a>
                </div>
            </div>
        </div>
    </div>
</main>

<?php get_footer(); ?>
