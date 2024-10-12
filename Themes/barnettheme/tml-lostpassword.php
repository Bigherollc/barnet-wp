<?php
$redirect_to = get_site_url();
if (isset($_REQUEST['redirect_to'])) {
    $redirect_to = $_REQUEST['redirect_to'];
}

$mess = "";
$showForgotSuccess = false;
if (isset($_REQUEST['checkemail']) && trim($_REQUEST['checkemail']) == "confirm") {
    $showForgotSuccess = true;
}

if (!empty($_POST['email'])) {
    $email = sanitize_email($_POST['email']);
    $errors = new WP_Error();


    $userData = false;

    if (!is_email($email)) {
        $errors->add('valid_email', __('Please enter a valid email address'));
    } else {
        $userData = get_user_by('email', htmlentities($email));
        if (empty($userData)) {
            $errors->add('invalid_email', __('Unknown email address. Check again or try your email.'));
        }
    }

    do_action('lostpassword_post', $errors, $userData);
    $errors = apply_filters('lostpassword_errors', $errors, $userData);


    if (!$errors->has_errors()) {
        if (!$userData) {
            $errors->add( 'invalid_email', __('Unknown email address. Check again or try your email.'));
        }
    }

    if (!$errors->has_errors()) {
        $userLogin = $userData->user_login;
        $userEmail = $userData->user_email;
        $key = get_password_reset_key($userData);

        if (is_wp_error($key)) {
            $errors->add('error_key', $key->get_error_message());
        }

        if (!$errors->has_errors()) {
            if (is_multisite()) {
                $siteName = get_network()->site_name;
            } else {
                $siteName = wp_specialchars_decode(get_option('blogname'), ENT_QUOTES);
            }

            $message = __('Someone has requested a password reset for the following account:') . "\r\n\r\n";
            $message .= sprintf(__('Site Name: %s'), $siteName) . "\r\n\r\n";
            $message .= sprintf(__('Username: %s'), $userLogin) . "\r\n\r\n";
            $message .= __('If this was a mistake, ignore this email and nothing will happen.') . "\r\n\r\n";
            $message .= __('To reset your password, visit the following address:') . "\r\n\r\n";
            $message .= network_site_url("wp-login.php?action=rp&key=$key&login=" . rawurlencode($userLogin), 'login') . "\r\n\r\n";

            $requester_ip = $_SERVER['REMOTE_ADDR'];
            if ($requester_ip) {
                $message .= sprintf(
                        __('This password reset request originated from the IP address %s.'),
                        $requester_ip
                    ) . "\r\n";
            }

            $title = sprintf(__('[%s] Password Reset  Request'), $siteName);

            $title = apply_filters('retrieve_password_title', $title, $userLogin, $userData);

            $message = apply_filters('retrieve_password_message', $message, $key, $userLogin, $userData);

            if ($message && !wp_mail($userEmail, wp_specialchars_decode($title), $message)) {
                $errors->add(
                    'retrieve_password_email_failure',
                    sprintf(
                        __('<strong>Error</strong>: The email could not be sent. Your site may not be correctly configured to send emails. <a href="%s">Get support for resetting your password</a>.'),
                        esc_url(__('https://wordpress.org/support/article/resetting-your-password/'))
                    )
                );
            }
          
		//retrieve_password($userLogin);

        }
    }

    if (!empty($errors->errors)) {
        $arrErr = $errors->get_error_messages();
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
        $redirect_to = add_query_arg("checkemail", "confirm", $current_url);
        wp_redirect($redirect_to);
        exit();
    }
}

if (is_user_logged_in()) {
    wp_redirect($redirect_to);
    exit();
}

?>
<?php get_header(); ?>
    <main role="main">
        <?php
        if (!$showForgotSuccess) :
        ?>
        <section class="component-form-request" data-request-step>
            <div class="container">
                <div class="component-form-request__wrapper" style="background-image: url(<?php echo get_template_directory_uri(); ?>/assets/images/bg-hexagon.png)">
                    <div class="component-form --center">
                        <div class="component-form__wrapper">

                            <div class="component-heading-group --dark-mode --mg-bottom-md text-center" data-heading>
                                <h2 class="component-heading-group__heading --size-lg to-uppercase"><?php _e('Forgot Password');?>
                                </h2>
                            </div>
                            <?php
                            if (!empty($mess)) :
                                ?>
                                <?php echo $mess;?>
                            <?php
                            endif;
                            ?>
                            <form class="component-form__form" method="POST" data-parsley-validate data-parsley-errors-messages-disabled>

                                <div class="component-form__content" data-tab-content>
                                    <div class="component-form__group">
                                        <div class="component-heading-form">
                                            <div class="component-heading-form__desc"><?php _e('We will send the password reset instructions to the email address associated with your account.');?>
                                            </div>
                                        </div>
                                        <div class="component-form__group__input">
                                            <div class="component-form__item">
                                                <input class="form-control --dark-mode" type="email" name="email" placeholder="<?php _e('Email Address');?>" autocomplete="off" data-parsley-required data-parsley-type="email">
                                            </div>
                                        </div>
                                    </div>
                                    <div class="component-form__group text-center">
                                        <button class="btn btn-solid btn-large --dark-mode" data-next-btn type="submit"><?php _e('Send Mail');?></button>
                                    </div>
                                </div>


                            </form>

                        </div>
                    </div>
                </div>
            </div>
        </section>
        <?php
        else :
            ?>
            <section class="component-form-request --confirmation">
                <div class="container">
                    <div class="component-form-request__wrapper" style="background-image: url(<?php echo get_template_directory_uri(); ?>/assets/images/bg-hexagon.png)">
                        <div class="component-form --center">
                            <div class="component-form__wrapper">
                                <div class="component-form__content">
                                    <div class="component-form__group text-center">
                                        <div class="component-form__inbox"><img src="<?php echo get_template_directory_uri(); ?>/assets/images/mail.svg" alt="Mail">
                                        </div>
                                        <div class="component-heading-group --dark-mode">
                                            <h2 class="component-heading-group__heading --size-md"><?php _e('Check Your Inbox');?>
                                            </h2>
                                            <div class="component-heading-group__desc"><?php _e('We just sent you an email. Follow the link in the email to reset your password.');?>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </section>
        <?php endif; ?>
    </main>

<?php get_footer(); ?>