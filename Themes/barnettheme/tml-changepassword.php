<?php
$user = wp_get_current_user();

$mess = "";
$urlProfile = get_site_url();
if (function_exists("tml_get_action_url")) {
    $urlProfile = tml_get_action_url('dashboard' );
}

$showRequestSuccess = false;
if (isset($_REQUEST['changepass']) && trim($_REQUEST['changepass']) == "true") {
    $showRequestSuccess = true;
}

if (!empty($_POST['temporary_password'])) {
    $error = new WP_Error();
    $temporaryPassword = $_POST["temporary_password"];
    $newPassword = $_POST["new_password"];
    $reNewPassword = $_POST['re_new_password'];

    if (!wp_check_password($temporaryPassword, $user->user_pass, $user->ID)) {
        $error->add(
            'incorrect_password',
            __('The Old Password you entered for your account is incorrect.')
        );
    } else {
        if (strlen($newPassword) < 7) {
            $error->add( 'valid_password', __('Your password must be at least seven characters'));
        } else if ($newPassword != $reNewPassword) {
            $error->add('password_reset_mismatch', __('The passwords do not match.'));
        } else if ($temporaryPassword == $newPassword) {
            $error->add( 'valid_password', __('New password cannot match old password'));
        }


        if (!$error->has_errors()) {
            wp_set_password($newPassword, $user->ID);

            $userId = $user->ID;

            $userData['ID'] = $userId; //user ID
            $userData['user_pass'] = $newPassword;
            wp_update_user($userData);
        }
    }

    if (!empty($error->errors)) {
        $arrErr = $error->get_error_messages();
        $mess = '<ul class="parsley-errors-list filled" data-validate-server="">';
        foreach ($arrErr as $err) {
            $mess .= '<li>' . $err . '</li>';
        }
        $mess .= '</ul>';
    } else {
        global $wp;
        $requestGet = array();
        if ($_GET) {
            $requestGet = $_GET;
        }

        $current_url = site_url(add_query_arg(array($requestGet), "/" . $wp->request . "/"));
        $redirect_to = add_query_arg("changepass", "true", $current_url);
        wp_redirect($redirect_to);
        exit();
    }
}
?>
<?php get_header(); ?>

<main role="main">
    <section class="component-form-request" data-request-step>
        <div class="container">
            <div class="component-form-request__wrapper" style="background-image: url(<?php echo get_template_directory_uri(); ?>/assets/images/bg-hexagon.png)">
                <div class="component-form --center">
                    <div class="component-form__wrapper">
                        <?php if (!$showRequestSuccess) :?>
                        <div class="component-heading-group --dark-mode --mg-bottom-md text-center" data-heading>
                            <h2 class="component-heading-group__heading --size-lg to-uppercase">
                                <?php _e('Change Password'); ?>
                            </h2>
                        </div>
                        <?php
                        if (!empty($mess)) :
                            ?>
                            <?php echo $mess;?>
                        <?php
                        endif;
                        ?>
                        <form class="component-form__form" method="post" data-parsley-validate data-parsley-errors-messages-disabled>
                            <div class="component-form__content" data-tab-content>
                                <div class="component-form__group">
                                    <div class="component-form__group__input">
                                        <div class="component-form__item">
                                            <input class="form-control --dark-mode" type="password" name="temporary_password" minlength="7" placeholder="<?php _e('Old Password');?>" autocomplete="off" data-parsley-required>
                                        </div>
                                    </div>
                                </div>
                                <div class="component-form__group">
                                    <div class="component-heading-form">
                                        <div class="component-heading-form__desc">
                                            <?php echo get_theme_mod('change_password_note', class_exists('BarnetDefaultText') ? BarnetDefaultText::CHANGE_PASSWORD_NOTE : ''); ?>
                                        </div>
                                    </div>
                                    <div class="component-form__group__input">
                                        <div class="component-form__item">
                                            <input class="form-control --dark-mode" type="password" name="new_password" placeholder="<?php _e('New Password');?>" minlength="7" id="new_password" autocomplete="off" data-parsley-required>
                                        </div>
                                        <div class="component-form__item">
                                            <input class="form-control --dark-mode" type="password" name="re_new_password" placeholder="<?php _e('Re-Enter New Password');?>" minlength="7" id="re_new_password" data-parsley-equalto="#new_password" autocomplete="off" data-parsley-required>
                                        </div>
                                    </div>
                                </div>
                                <div class="component-form__group text-center">
                                    <button class="btn btn-solid btn-large --dark-mode" data-next-btn type="submit"><?php _e('Save');?></button>
                                </div>
                            </div>
                        </form>
                        <?php else : ?>
                            <div class="component-form__group text-center">
                                <div class="component-heading-group --dark-mode text-center">
                                    <h2 class="component-heading-group__heading --size-md">
                                        <?php echo get_theme_mod('change_password_success_title', class_exists('BarnetDefaultText') ? BarnetDefaultText::CHANGE_PASSWORD_SUCCESS_TITLE : ''); ?>
                                    </h2>
                                    <div class="component-heading-group__desc">
                                        <?php echo get_theme_mod('change_password_success_text', class_exists('BarnetDefaultText') ? BarnetDefaultText::CHANGE_PASSWORD_SUCCESS_TEXT : ''); ?>
                                    </div>
                                </div>
                                <div class="component-form__btn"><a class="btn btn-solid --dark-mode" href="/" title="Ok">Ok</a>
                                </div>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </section>
</main>

<?php get_footer(); ?>
