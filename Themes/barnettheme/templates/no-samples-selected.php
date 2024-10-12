<?php
/* Template Name: No Samples Selected */

get_header(); ?>

<main role="main">
    <section class="component-sample-request --no-sample" data-sample-request data-request-step>
        <div class="container">
            <div class="component-form">
                <div class="component-form__wrapper">
                    <form class="component-form__form" data-parsley-validate data-parsley-errors-messages-disabled>
                        <div class="component-form__content" data-tab-content>
                            <div class="component-form__infor">
                                <div class="component-form__caption">
                                    <div class="component-heading-group --dark-mode">
                                        <h2 class="component-heading-group__heading --size-lg to-uppercase">
                                            <?php echo get_theme_mod('no_sample_title_text', class_exists('BarnetDefaultText') ? BarnetDefaultText::NO_SAMPLE_TITLE_TEXT : ''); ?>
                                        </h2>
                                        <div class="component-heading-group__desc">
                                            <p><?php echo get_theme_mod('no_sample_heading_line_1', class_exists('BarnetDefaultText') ? BarnetDefaultText::NO_SAMPLE_HEADING_LINE_1 : ''); ?></p>
                                            <p><?php echo get_theme_mod('no_sample_heading_line_2', class_exists('BarnetDefaultText') ? BarnetDefaultText::NO_SAMPLE_HEADING_LINE_2 : ''); ?></p>
                                        </div>
                                    </div>
                                </div>
                                <div class="component-form__field">
                                    <div class="component-form-heading">
                                        <div class="component-heading-form">
                                            <h2 class="component-heading-form__heading">
                                                <?php echo get_theme_mod('no_sample_heading_title', class_exists('BarnetDefaultText') ? BarnetDefaultText::NO_SAMPLE_HEADING_TITLE : ''); ?>
                                            </h2>
                                            <div class="component-heading-form__desc">
                                                <?php echo get_theme_mod('no_sample_heading_note', class_exists('BarnetDefaultText') ? BarnetDefaultText::NO_SAMPLE_HEADING_NOTE : ''); ?>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="component-form__group d-none">
                                        <div class="component-form__ctas">
                                            <a class="btn btn-block" href="<?php echo get_permalink(get_page_by_path('active-landing-login'));?>" title="<?php _e('All active materials');?>"><?php _e('All active materials');?></a>
                                            <a class="btn btn-block" href="<?php echo get_permalink(get_page_by_path('system-landing-login'));?>" title="<?php _e('All system formers');?>"><?php _e('All system formers');?></a>
                                            <a class="btn btn-block" href="<?php echo get_permalink(get_page_by_path('formula-listing-login'));?>" title="<?php _e('All Starting Formulas');?>"><?php _e('All Starting Formulas');?></a>
                                        </div>
                                    </div>
                                    <div class="component-form__group">
                                        <div class="component-heading-form --size-sm">
                                            <h2 class="component-heading-form__heading">
                                                <?php echo get_theme_mod('no_sample_add_a_sample', class_exists('BarnetDefaultText') ? BarnetDefaultText::NO_SAMPLE_ADD_A_SAMPLE : ''); ?>
                                            </h2>
                                        </div>
                                        <div class="component-form-group__input">
                                          <div class="component-form__item">
                                            <select class="form-control --dark-mode" name="quick_find" data-quick-find="product" style="width: 100%">
                                              <option disabled selected value=""><?php _e('Select A Product');?></option>
                                            </select>


                                          </div>
                                          <?php if(is_user_logged_in()): ?>
                                            <div class="component-form__item">
                                              <select class="form-control --dark-mode" name="quick_find" data-quick-find="formula" style="width: 100%">
                                                <option disabled selected value=""><?php _e('Select A Formula');?></option>
                                              </select>
                                            </div>
                                          <?php endif;?>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </section>
</main>

<?php get_footer(); ?>
