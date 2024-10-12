<?php
$redirect_to = get_site_url();
if (isset($_REQUEST['redirect_to'])) {
    $redirect_to = $_REQUEST['redirect_to'];
}

$mess = "";
if (!empty($_POST['email']) && !empty($_POST['password'])) {
    $email = sanitize_email($_POST['email']);
    $password = $_POST["password"];
    $error = new WP_Error();

    if (empty($error->errors)) {
        if (!is_email($email)) {
            $error->add('valid_email', __('Please enter a valid email address'));
        } else if (strlen($password) < 7) {
            $error->add( 'valid_password', __( 'Your password must be at least seven characters'));
        } else {
            $email = str_replace("&","&amp;",$email);
            $results = $wpdb->get_results("SELECT ID FROM wp_users WHERE user_email = '$email'");
            $uID = '';
            foreach ($results as $item) {
                $uID = $item->ID;
            }
            $user = get_user_by('id', $uID);
            if (!$user) {
                $error->add('invalid_email', __('Unknown email address. Check again or try your email.'));
            } else {
                if (!wp_check_password($password, $user->user_pass, $user->ID)) {
                    $error->add(
                        'incorrect_password',
                        sprintf(
                            __( 'The password you entered for the email %s is incorrect.' ),
                            '<strong>' . $email . '</strong>'
                        ) .
                        ' <a href="' . wp_lostpassword_url() . '">' .
                        __( 'Lost your password?' ) .
                        '</a>'
                    );
                } else {
                    $secure_cookie   = '';
                    if (get_user_option( 'use_ssl', $user->ID)) {
                        $secure_cookie = true;
                        force_ssl_admin( true );
                    }

                    $user = wp_signon(array('user_login' => $user->data->user_login, 'user_password' => $password), $secure_cookie );
                    if (empty($_COOKIE[LOGGED_IN_COOKIE])) {
                        if (headers_sent()) {
                            $user = new WP_Error( 'test_cookie', sprintf(
                                    __( '<strong>Error</strong>: Cookies are blocked due to unexpected output. For help, please see <a href="%1$s">this documentation</a> or try the <a href="%2$s">support forums</a>.' ),
                                    __( 'https://wordpress.org/support/article/cookies/' ),
                                    __( 'https://wordpress.org/support/forums/' )
                                )
                            );
                        } elseif ( isset( $_POST['testcookie'] ) && empty( $_COOKIE[ TEST_COOKIE ])) {
                            // If cookies are disabled we can't log in even with a valid user+pass
                            $user = new WP_Error( 'test_cookie', sprintf(
                                    __( '<strong>Error</strong>: Cookies are blocked or not supported by your browser. You must <a href="%s">enable cookies</a> to use WordPress.'),
                                    __( 'https://wordpress.org/support/article/cookies#enable-cookies-your-browser' )
                                )
                            );
                        }
                    }

                    if (!is_wp_error($user)) {
                        if ((empty( $redirect_to ) || 'wp-admin/' == $redirect_to || admin_url() == $redirect_to ) ) {

                            // If the user doesn't belong to a blog, send them to user admin. If the user can't edit posts, send them to their profile.
                            if ( is_multisite() && ! get_active_blog_for_user( $user->ID ) && ! is_super_admin( $user->ID ) ) {
                                $redirect_to = user_admin_url();

                            } elseif ( is_multisite() && ! $user->has_cap( 'read' ) ) {
                                $redirect_to = get_dashboard_url( $user->ID );

                            } elseif ( ! $user->has_cap( 'edit_posts' ) ) {
                                if ( tml_action_exists( 'dashboard' ) ) {
                                    $redirect_to = tml_get_action_url( 'dashboard' );
                                } else {
                                    $redirect_to = $user->has_cap( 'read' ) ? admin_url( 'profile.php' ) : home_url();
                                }
                            }
                            wp_redirect( $redirect_to );
                            exit;
                        }
                        wp_safe_redirect( $redirect_to );
                        exit;
                    } else {
                        $error->add('error_user', $user->get_error_message());
                    }
                }
            }
        }
    }
    if (!empty($error->errors)) {
        $arrErr = $error->get_error_messages();
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


if ( isset($_SESSION['referer_url']) ) {

    $login_redirect = str_replace("logout","login", $_SESSION['referer_url']);

    $redirect_to =  $login_redirect;

} else {

    $redirect_to = get_site_url();

}


?>
<html <?php language_attributes(); ?> class="no-js" lang="en" dir="ltr">
<head>
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta charset="<?php bloginfo('charset'); ?>">
    <meta name="viewport" content="width=device-width,initial-scale=1,shrink-to-fit=no, maximum-scale=1.0, user-scalable=no"">
    <meta name="robots" content="index, nofollow">
    <?php wp_head(); ?>
</head>
<body class="--fit-vh --bg-gradient loginbg" data-site-id="<?php echo get_current_blog_id(); ?>">

<body>

<noscript>JavaScript is off. Please enable to view full site.</noscript>
<main role="main">

    <section class="component-form-account">
        <div class="component-form --right">
            <div class="component-form__wrapper"><a class="component-form-account__logo" href="/" title="Logo">
                    <div class="component-form-account__logo--img"><img src="<?php echo get_template_directory_uri(); ?>/assets/images/logo-barnet-large.svg" alt="Logo">
                    </div></a>
                <div class="component-heading-group --dark-mode">
                    <h2 class="component-heading-group__heading to-uppercase">where science meets fashion
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
                    <input name="redirect_to" type="hidden" value="<?php echo $redirect_to;?>">
                    <div class="component-form__group">
                        <div class="component-form__group__input">
                            <div class="component-form__item">
                                <input class="form-control --dark-mode" type="email" name="email" placeholder="Email" autocomplete="off" data-parsley-required data-parsley-type="email">
                            </div>
                            <div class="component-form__item">
                                <input class="form-control --dark-mode" type="password" name="password" placeholder="Password" autocomplete="off" minlength="7" data-parsley-required>
                            </div>
                        </div><a class="component-form__link" href="<?php echo wp_lostpassword_url();?>" title="<?php _e('Forgot Password?');?>"><?php _e('Forgot Password?');?></a>
                        <button class="btn btn-solid btn-large btn-block --dark-mode" type="submit"><?php _e('Sign In');?></button>
                    </div>
                    <div class="component-form__group">
                        <div class="component-heading-form">
                            <h2 class="component-heading-form__heading"><?php _e('New Customer?');?>
                            </h2>
                        </div><a class="btn btn-block btn-gtm-request-access" href="<?php echo wp_registration_url();?>" title="<?php _e('Request Access');?>"><?php _e('Request Access');?></a>
                    </div>

                    <input name="redirect_to" type="hidden" value="<?php echo $redirect_to;?>">
                </form>
            </div>
        </div>
    </section>
</main>
<!-- <div class="component-loading-overlay d-none" data-loading>
    <div class="component-loading-wrapper"></div> 
</div> -->
<?php wp_footer(); ?>
</body>
</html>
