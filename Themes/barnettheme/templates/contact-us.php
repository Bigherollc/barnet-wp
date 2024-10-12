<?php
/* Template Name: Contact Us */
?>
<?php
$wpforms_settings = get_option( 'wpforms_settings', [] );
$mess = "";
$barnetRecaptcha = new BarnetRecaptcha();
$showRequestSuccess = false;
if (isset($_REQUEST['checkemail']) && trim($_REQUEST['checkemail']) == "confirm") {
    $showRequestSuccess = true;
}

$emailContactList = array();
if (class_exists('Barnet')) {
    $emailContactList = Barnet::$EMAIL_CONTACT_LIST;
}

if (!empty($_POST['email_contact'])) {
    $name = isset($_POST["name_contact"]) ? sanitize_text_field($_POST["name_contact"]) : '';
    $email = sanitize_email($_POST['email_contact']);
    $message = isset($_POST["message_contact"]) ? esc_textarea($_POST["message_contact"]) : '';
    $department = isset($_POST["department"]) ? trim($_POST["department"]) : '';

    $error = new WP_Error();
    if (!is_email($email) || strlen($email) > 100) {
        $error->add('valid_email', __('Please enter a valid email address'));
    }

    if (empty($name)) {
        $error->add('empty_name', __('The Name field is empty.'));
    } else if (strlen($name) >= 50) {
        $error->add('max_name', __('The Name field is maximum of 50 characters.'));
    }

    if (!empty($emailContactList)) {
        if (!isset($emailContactList[$department])) {
            $error->add('message_name', __('The Department field is empty.'));
        }
    } else {
        // if the department field is empty, we consider that an error
        $error->add('message_name', __('The Department field is empty.'));
        // $department = "";
    }

     // verify recaptcha response
    $barnetRecaptchaEnable = $barnetRecaptcha->isEnable();
    if ($barnetRecaptchaEnable) {
        $result = $barnetRecaptcha->captchaV3Verification();
        if($result['success'] == 0 || $result['score'] < 0.5) {
            $error->add('recaptcha_incorrect', __("The captcha is incorrect."));
        }
    }
 
    if (empty($message)) {
        $error->add('message_name', __('The Message field is empty.'));
    }


    if($wpforms_settings['recaptcha-type']=="v2"){
        if(!isset($_POST['g-recaptcha-response'])||!(isset($_POST['g-recaptcha-response'])&&$_POST['g-recaptcha-response'])){
            $error->add('recaptcha_incorrect', __("The captcha is incorrect."));
        } 
      
    }
    else if($wpforms_settings['recaptcha-type']=="invisible"){
    
    }
    else{
        if(isset($_POST['g_token'])){
            $secret_key = $wpforms_settings['recaptcha-secret-key'];           
            $response=$_POST['g_token'];
            $siteverify_url='https://www.google.com/recaptcha/api/siteverify?secret='.$secret_key.'&response='.$response;
            $verify_captcha = file_get_contents($siteverify_url);
            $verify_response = json_decode($verify_captcha);
            if ($verify_response->success && $verify_response->score >= $wpforms_settings['recaptcha-v3-threshold'] ) {
    
            }
            else {
                $error->add('recaptcha_verification_faild', __($wpforms_settings['recaptcha-fail-msg']));
            }
        }
        else{
            $error->add('recaptcha_incorrect', __("The captcha is incorrect."));
        }
    }

    if (empty($error->errors)) {
        $dataFields = array(
            'email' => $email,
            'name' => $name
        );

        $description = __('Contact Infor') . "\r\n\r\n";
        $description .= sprintf('Name: %s', $name) . "\r\n\r\n";
        $description .= sprintf('Email: %s', $email) . "\r\n\r\n";
        $emailDepartment = "";
        if (!empty($department)) {
            $emailDepartment = get_theme_mod($department, '');
            $departmentTxt = "";
            // filter our list of comma-seperated emails through is_email
            $emailDepartment = implode(',',
                    array_map("trim",
                    array_filter(explode(',', $emailDepartment),
                        function ($e) { return is_email(trim($e)); })));
            //  check that our list of emails isn't empty
            if (!empty($emailDepartment)) {
                $departmentTxt = " (" . $emailDepartment . ")";
            } else {
                $emailDepartment = "";
            }
            $dataFields['department'] = $emailContactList[$department] . $departmentTxt;

            $description .= sprintf('Department: %s', $emailContactList[$department] . $departmentTxt) . "\r\n\r\n";
        }
        $description .= sprintf('Message: %s', $message) . "\r\n\r\n";

        $dataFields['message'] = $message;

        //save info data
        if (class_exists('BarnetContact')) {
            $barnetContact = new BarnetContact();
            $barnetContact->insertData($description);
        }

        $emailAdmin = get_option('admin_email');

        // don't send to support@... anymore
        // $wpMailOption = get_option('wp_mail_smtp');
        // if (is_array($wpMailOption) && isset($wpMailOption["mail"]) && isset($wpMailOption["mail"]["from_email"])) {
        //     $emailAdmin = trim($wpMailOption["mail"]["from_email"]);
        // }

        if (!empty($emailDepartment)) {
            $emailAdmin = $emailDepartment;
        }

        if ($emailAdmin != "") {
            if (is_multisite()) {
                $siteName = get_network()->site_name;
            } else {
                $siteName = wp_specialchars_decode(get_option('blogname'), ENT_QUOTES);
            }

            $message = __('Contact Info:') . "\r\n\r\n";
            foreach ($dataFields as $k => $v) {
                $message .= sprintf(__(ucfirst($k) . ': %s'), $v) . "\r\n\r\n";
            }

            $title = sprintf(__('[%s] Contact Us'), $siteName);
            if ($message && !wp_mail($emailAdmin, wp_specialchars_decode($title), $message)) {
                //error sendmail
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

?>
<?php get_header(); ?>
    <script type="text/javascript" 
        src="https://www.google.com/recaptcha/api.js?onload=wpcaptcha_captcha&render=<?php echo $wpforms_settings['recaptcha-site-key'];?>"
        id="recaptcha3-js"
        defer="defer"
        data-wp-strategy="defer">
    </script> 
    <main role="main">
        <section class="component-form-hero contact-us" style="background-image: url('<?php echo get_template_directory_uri(); ?>/assets/images/bg-contact-us.png')">
            <div class="component-form --right">
                <div class="component-form__wrapper">
                    <div class="component-heading-group --dark-mode text-center" data-heading>
                        <h2 class="component-heading-group__heading --size-lg to-uppercase">Contact us
                        </h2>
                    </div>
                    <?php
                    if (!empty($mess)) :
                        ?>
                        <?php echo $mess;?>
                    <?php
                    endif;
                    ?>
                    <?php if (!$showRequestSuccess) :?>
                    <form class="component-form__form" id="barnet-contact-form" data-parsley-validate data-parsley-errors-messages-disabled method="post">
                        <div class="component-form__content" data-tab-content>
                            <div class="component-form__group">
                                <div class="component-form__group__input">
                                    <div class="component-form__item">
                                        <input class="form-control --dark-mode" type="text" name="name_contact" placeholder="<?php _e('Name');?> *" maxlength="50" autocomplete="off" required>
                                    </div>
                                    <div class="component-form__item">
                                        <input class="form-control --dark-mode" type="email" name="email_contact" placeholder="<?php _e('Email');?> *" autocomplete="off" maxlength="100" required>
                                    </div>
                                    <?php if (!empty($emailContactList)) :?>
                                    <div class="component-form__item">
                                        <select class="form-control --dark-mode" name="department" required data-parsley-required>
                                            <option value=""><?php _e('Department');?> *</option>
                                            <?php
                                            $valueDepartment = isset($_POST['department']) ? $_POST['department'] : '';
                                            foreach ($emailContactList as $k => $v) {
                                                printf(
                                                    '<option value="%s"%s>%s</option>',
                                                    esc_attr($k),
                                                    selected($valueDepartment, $k, false),
                                                    esc_html(__($v))
                                                );
                                            }
                                            ?>
                                        </select>
                                    </div>
                                    <?php endif; ?>
                                    <div class="component-form__item">
                                        <textarea class="form-control --dark-mode" type="text" name="message_contact" placeholder="<?php _e('Your Message');?> *" autocomplete="off" required></textarea>
                                    </div>
                                </div>
                            </div>
                            <div class="component-form__group">
                                <?php if ($barnetRecaptcha->isEnable()) :?>
                                    <?php $barnetRecaptcha->displayCaptcha();  ?>
                                <?php endif;?>
                            </div>
                            <input type="hidden" id="gtoken" name="g_token">
                            <div class="component-form__group">
                                <div class="component-heading-form">
                                    <div class="component-heading-form__desc">
                                        <a href="/privacy-policy"><?php _e('By providing us information, you are agreeing to our Privacy Policy');?></a>                                        
                                    </div>
                                </div>
                            </div>
                            <div class="component-form__group">
                                <button class="btn btn-solid btn-block btn-large --dark-mode" data-next-btn type="submit">Submit</button>
                            </div>
                        </div>

                    </form>
                    <?php else : ?>
                        <div class="component-form__content" data-tab-content>
                            <div class="alert-successful">
                                <div class="alert-successful__title">
                                    <?php echo get_theme_mod('contact_success_title', class_exists('BarnetDefaultText') ? BarnetDefaultText::CONTACT_SUCCESS_TITLE : ''); ?>
                                </div>
                                <div class="alert-successful__description">
                                    <?php echo get_theme_mod('contact_success_text', class_exists('BarnetDefaultText') ? BarnetDefaultText::CONTACT_SUCCESS_TEXT : ''); ?>
                                </div>
                            </div>
                        </div>
                    <?php endif;?>
                </div>
                <div class="contact-us__information">
                    <div class="contact-us__information--content">
                        <?php echo get_theme_mod( 'company_address',''); ?>
                        <?php $com_phone = get_theme_mod( 'company_phone','');?>
                        <p> <a href="tel:#<?php echo $com_phone; ?>" title="phone"><?php echo $com_phone; ?></a>
                        </p>
                    </div>
                </div>
            </div>
        </section>
    </main>

<?php get_footer(); ?>
<script>
    function wpcaptcha_captcha() {
        grecaptcha.execute('<?php echo $wpforms_settings['recaptcha-site-key'];?>', { action: 'action' }).then(token => {
            const gtoken = document.querySelector('#gtoken');
            if(typeof gtoken !== null && gtoken ) {
                gtoken.value = token;
            }
        });
    };
    function onSubmit(token) {
        document.getElementById("barnet-contact-form").submit();
    }
</script>

<style type="text/css">
    .grecaptcha-badge {
        z-index: 1000;
    }
</style>
