<?php
$redirect_to = get_site_url();
if (isset($_REQUEST['redirect_to'])) {
    $redirect_to = $_REQUEST['redirect_to'];
}

$mess = "";
if (!empty($_POST['new_password']) && isset($_POST['re_new_password'])) {
    $newPassword = $_POST['new_password'];
    $reNewPassword = $_POST['re_new_password'];

    $errors = new WP_Error;
    if (strlen($newPassword) < 7) {
        $errors->add( 'valid_password', __( 'Your password must be at least seven characters'));
    } else if ($newPassword != $reNewPassword) {
        $errors->add( 'password_reset_mismatch', __( 'The passwords do not match.' ) );
    }
    if (!$errors->has_errors()) {
        list($rp_path) = explode('?', wp_unslash($_SERVER['REQUEST_URI']));
        $rp_cookie = 'wp-resetpass-' . COOKIEHASH;
        if (isset($_COOKIE[$rp_cookie]) && 0 < strpos($_COOKIE[$rp_cookie], ':')) {
            list($rp_login, $rp_key) = explode(':', wp_unslash($_COOKIE[ $rp_cookie]), 2);
            $user = check_password_reset_key($rp_key, $rp_login);
            if (!hash_equals($rp_key, $_POST['rp_key'])) {
                $user = false;
            }
        } else {
            $user = false;
        }

        if (!$user || is_wp_error($user)) {
            setcookie($rp_cookie, ' ', time() - YEAR_IN_SECONDS, $rp_path, COOKIE_DOMAIN, is_ssl(), true);
            if ($user && $user->get_error_code() === 'expired_key') {
                wp_redirect( site_url( 'wp-login.php?action=lostpassword&error=expiredkey' ) );
            } else {
                wp_redirect( site_url( 'wp-login.php?action=lostpassword&error=invalidkey' ) );
            }
            exit;
        }

        do_action( 'validate_password_reset', $errors, $user);
        if ((!$errors->get_error_code())) {
            reset_password( $user,$newPassword);
            setcookie($rp_cookie, ' ', time() - YEAR_IN_SECONDS, $rp_path, COOKIE_DOMAIN, is_ssl(), true);
            wp_redirect( site_url( 'wp-login.php?resetpass=complete' ) );
            exit;
        }
    }

    if (!empty($errors->errors)) {
        $arrErr = $errors->get_error_messages();
        $mess = '<ul class="parsley-errors-list filled" data-validate-server="">';
        foreach ($arrErr as $err) {
            $mess .= '<li>' . $err . '</li>';
        }
        $mess .= '</ul>';
    }
}

if (is_user_logged_in()) {
    wp_redirect($redirect_to);
    exit();
}

?>
<?php get_header(); ?>

<main role="main">
    <section class="component-form-request" data-request-step>
        <div class="container">
            <div class="component-form-request__wrapper" style="background-image: url(<?php echo get_template_directory_uri(); ?>/assets/images/bg-hexagon.png)">
                <div class="component-form --center">
                    <div class="component-form__wrapper">
                        <div class="component-heading-group --dark-mode --mg-bottom-md text-center" data-heading>
                            <h2 class="component-heading-group__heading --size-lg to-uppercase"><?php _e('Add New Password');?>
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
                            <?php
                            $rp_cookie = 'wp-resetpass-' . COOKIEHASH;
                            if (isset($_COOKIE[ $rp_cookie ]) && 0 < strpos($_COOKIE[$rp_cookie], ':')) {
                                list($rp_login, $rp_key) = explode(':', wp_unslash($_COOKIE[$rp_cookie]), 2);
                                echo '<input name="rp_key" type="hidden" value="' . $rp_key . '">';
                            }

                            ?>

                            <div class="component-form__content" data-tab-content>
                                <div class="component-form__group">
                                    <div class="component-heading-form">
                                        <div class="component-heading-form__desc"><?php _e('Your password must be at least seven characters. For added security we recommend using upper and lower case letters as well as special characters.');?>
                                        </div>
                                    </div>
                                    <div class="component-form__group__input">
                                        <div class="component-form__item">
                                            <input class="form-control --dark-mode" type="password" name="new_password" placeholder="<?php _e('New Password');?>" autocomplete="off" minlength="7" id="new_password" data-parsley-required>
                                        </div>
                                        <div class="component-form__item">
                                            <input class="form-control --dark-mode" type="password" name="re_new_password" placeholder="<?php _e('Re-Enter New Password');?>" autocomplete="off" minlength="7" id="re_new_password" data-parsley-equalto="#new_password" data-parsley-required>
                                        </div>
                                    </div>
                                </div>
                                <div class="component-form__group text-center">
                                    <button class="btn btn-solid btn-large --dark-mode" data-next-btn type="submit"><?php _e('Set Password');?></button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </section>
</main>

<?php get_footer(); ?>
