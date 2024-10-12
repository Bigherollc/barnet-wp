<?php

$redirect_to = get_site_url();
$wpforms_settings = get_option( 'wpforms_settings', [] );

if (isset($_REQUEST['redirect_to'])) {
    $redirect_to = $_REQUEST['redirect_to'];
}

//$barnetRecaptcha = new BarnetRecaptcha();
$mess = "";
$showRequestSuccess = false;
if (isset($_REQUEST['checkemail']) && trim($_REQUEST['checkemail']) == "confirm") {
    $showRequestSuccess = true;
}
if (!empty($_POST['email'])) {
    //register
    $firstName = isset($_POST["first_name"]) ? sanitize_text_field($_POST["first_name"]) : '';
    $lastName = isset($_POST["last_name"]) ? sanitize_text_field($_POST["last_name"]) : '';
    $email = sanitize_email($_POST['email']);
    $note = isset($_POST["note"]) ? esc_textarea($_POST["note"]) : '';
    $companyName = isset($_POST["company_name"]) ? sanitize_text_field($_POST["company_name"]) : '';
    $address = isset($_POST["address"]) ? sanitize_text_field($_POST["address"]) : '';
    $addressOptional = isset($_POST["address_optional"]) ? sanitize_text_field($_POST["address_optional"]) : '';
    $country = isset($_POST["country"]) ? trim($_POST["country"]) : '';
    $city = isset($_POST["city"]) ? sanitize_text_field($_POST["city"]) : '';
    $province = isset($_POST["province"]) ? sanitize_text_field($_POST["province"]) : '';
    $postalCode = isset($_POST["postal_code"]) ? sanitize_text_field($_POST["postal_code"]) : '';
    $phone = isset($_POST["phone"]) ? sanitize_text_field($_POST["phone"]) : '';
    $phoneOptional = isset($_POST["phone_optional"]) ? sanitize_text_field($_POST["phone_optional"]) : '';
    $jobTitleRole = isset($_POST["job_title_role"]) ? sanitize_text_field($_POST["job_title_role"]) : '';
    $newsletter = isset($_POST["newsletter"]) ? sanitize_text_field($_POST["newsletter"]) : 'off';
    $aboutUs = isset($_POST["about_us"]) ? trim($_POST["about_us"]) : '';
    $username = "";
    $nickname = "";

    $utm_source = isset($_POST["utm_source"]) ? sanitize_text_field($_POST["utm_source"]) : '';
    $utm_medium = isset($_POST["utm_medium"]) ? sanitize_text_field($_POST["utm_medium"]) : '';
    $Utm_Content = isset($_POST["Utm-Content"]) ? sanitize_text_field($_POST["Utm-Content"]) : '';
    $Utm_Id = isset($_POST["Utm-Id"]) ? sanitize_text_field($_POST["Utm-Id"]) : '';
    $Utm_Term = isset($_POST["Utm-Term"]) ? sanitize_text_field($_POST["Utm-Term"]) : '';
    $Referrer_URL = isset($_POST["Referrer-URL"]) ? sanitize_text_field($_POST["Referrer-URL"]) : '';
    $attributer_channel = isset($_POST["attributer-channel"]) ? sanitize_text_field($_POST["attributer-channel"]) : 'xxx';

    $BPNJ = isset($_POST["BPNJ"]) ? sanitize_text_field($_POST["BPNJ"]) : '';
    $attributer_channeldrilldown1 = isset($_POST["attributer-channeldrilldown1"]) ? sanitize_text_field($_POST["attributer-channeldrilldown1"]) : '';
    $attributer_channeldrilldown2 = isset($_POST["attributer-channeldrilldown2"]) ? sanitize_text_field($_POST["attributer-channeldrilldown2"]) : '';
    $attributer_channeldrilldown3 = isset($_POST["attributer-channeldrilldown3"]) ? sanitize_text_field($_POST["attributer-channeldrilldown3"]) : '';
    $attributer_channeldrilldown4 = isset($_POST["attributer-channeldrilldown4"]) ? sanitize_text_field($_POST["attributer-channeldrilldown4"]) : '';
    $attributer_landingpage = isset($_POST["attributer-landingpage"]) ? sanitize_text_field($_POST["attributer-landingpage"]) : '';
    $attributer_landingpagegroup = isset($_POST["attributer-landingpagegroup"]) ? sanitize_text_field($_POST["attributer-landingpagegroup"]) : '';
    
    $error = new WP_Error();
    if (!is_email($email)) {
        $error->add('valid_email', __('Please enter a valid email address'));
    } else {
        $userCheck = get_user_by('email', htmlentities($email));
        if ($userCheck) {
            $error->add('exist_email', __('This email is already registered. Please choose another one.'));
        } else {
            $username =  str_replace(array(".", "_", "@"), array("", "", ""), strtolower($email));
            $userCheck = get_user_by('login', $username);
            if ($userCheck) {
                $username .= time();
                $userCheck = get_user_by('login', $username);
                if ($userCheck) {
                    $error->add('exist_username', __('Error! An error occurred. Please try again later'));
                }
            }
        }
    }

    if (empty($firstName) || empty($lastName)) {
        if (empty($firstName)) {
            $error->add('empty_fistname', __("The Fist Name field is empty."));
        }

        if (empty($lastName)) {
            $error->add('empty_lastname', __("The Last Name field is empty."));
        }
    } else {
        $nickname = $firstName . " " . $lastName;
    }

    if (empty($companyName)) {
        $error->add('empty_company', __("The Company Name field is empty."));
    }

    if (empty($address)) {
        $error->add('empty_address', __("The Address field is empty."));
    }

    if (empty($country)) {
        $error->add('empty_country', __("The Country field is empty."));
    }

    if (empty($city)) {
        $error->add('empty_city', __("The City field is empty."));
    }

    if (empty($province)) {
        $error->add('empty_province', __("The State/Province field is empty."));
    }

    if (empty($postalCode)) {
        $error->add('empty_postalCode', __("The Postal Code field is empty."));
    }

    if (empty($phone)) {
        $error->add('empty_phone', __("The Work Phone field is empty."));
    }

    if (empty($jobTitleRole)) {
        $error->add('empty_job_title_role', __("The Job Title/Role field is empty."));
    }

  /*
    // verify recaptcha response
    $barnetRecaptchaEnable = $barnetRecaptcha->isEnable();
    if ($barnetRecaptchaEnable) {
        $result = $barnetRecaptcha->captchaV3Verification();
        if($result['success'] == 0 || $result['score'] < 0.3) {
            $error->add('recaptcha_incorrect', __("The captcha is incorrect."));
        }
    }
  */
  
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
        $userPass = wp_generate_password();
        $userData = array(
            'user_login' => $username,
            'user_email' => $email,
            'user_pass' => $userPass,
            'nickname' => $nickname,
            'description' => $note,
            'first_name' => $firstName,
            'last_name' => $lastName
        );

        $userDataOther = array(
            'company_name' => $companyName,
            'address' => $address,
            'address_optional' => $addressOptional,
            'country' => $country,
            'city' => $city,
            'province' => $province,
            'postal_code' => $postalCode,
            'phone' => $phone,
            'phone_optional' => $phoneOptional,
            'job_title_role' => $jobTitleRole,
            //'newsletter' => $newsletter,
            'about_us' => $aboutUs,
            'note' => $note,
        );

        

        $userId = wp_insert_user($userData);
		$nuser = new WP_User($userId);
		$nuser->set_role('requestor');
      
        if (is_wp_error($userId)) {
            $error->add('_error_user', $userId->get_error_message());
        } else {
            add_user_meta( $userId, 'user_extra_type', 'barnet', true );
            add_user_meta( $userId, 'newsletter', $newsletter, true );
            add_user_meta( $userId, 'user_extra_info', serialize($userDataOther), true );
            add_user_meta( $userId, 'utm_source', $utm_source, true );
            add_user_meta( $userId, 'utm_medium', $utm_medium, true );
            add_user_meta( $userId, 'Utm-Content', $Utm_Content, true );
            add_user_meta( $userId, 'Utm-Id', $Utm_Id, true );
            add_user_meta( $userId, 'Utm-Term', $Utm_Term, true );
            add_user_meta( $userId, 'Referrer-URL', $Referrer_URL, true );
            add_user_meta( $userId, 'Channel', $attributer_channel, true );
            add_user_meta( $userId, 'BPNJ', $BPNJ, true );
            add_user_meta( $userId, 'Channel-Drill-Down-1', $attributer_channeldrilldown1, true );
            add_user_meta( $userId, 'Channel-Drill-Down-2', $attributer_channeldrilldown2, true );
            add_user_meta( $userId, 'Channel-Drill-Down-3', $attributer_channeldrilldown3, true );
            add_user_meta( $userId, 'Channel-Drill-Down-4', $attributer_channeldrilldown4, true );
            add_user_meta( $userId, 'landingpage', $attributer_landingpage, true );
            add_user_meta( $userId, 'landingpagegroup', $attributer_landingpagegroup, true );

            //sendmail user
            if (is_multisite()) {
                $siteName = get_network()->site_name;
            } else {
                $siteName = wp_specialchars_decode(get_option('blogname'), ENT_QUOTES);
            }
            $message = sprintf(__('Hello %s,'), $firstName) . "\r\n\r\n";
            $message .= __("Thank you for your interest in Barnet Products! We have received your request for registration to our website. All requests are reviewed and approved by a Barnet Products administrator. An email notification will be sent to the corporate email address you provided when your registration has been approved.") . "\r\n\r\n";
            $message .= __("Kind Regards,") . "\r\n\r\n";
            $message .= __("The Barnet Products Team") . "\r\n\r\n";

            $title = "Barnet Products Website Access Request";
            if ($message && !wp_mail($email, wp_specialchars_decode($title), $message)) {
                error_log("Unable to send the requet access confirmation email." . print_r([
                    "email" => $email,
                    "first" => $firstName,
                    "last"  => $lastName,
                    "company" => $companyName
                ], true));
            }

            //sendmail admin
            $emailAdmin = get_option('admin_email');
            $wpMailOption = get_option('wp_mail_smtp');

            if (is_array($wpMailOption) && isset($wpMailOption["mail"]) && isset($wpMailOption["mail"]["from_email"])) {
                $emailAdmin = trim($wpMailOption["mail"]["from_email"]);
            }

            if ($emailAdmin != "") {
                $getListCountryName = Barnet::getListCountries();
                $countryName = isset($getListCountryName[$country]) ? $getListCountryName[$country] : '';
                $message = __('Account Info:') . "\r\n\r\n";
                $message .= sprintf(__('Email: %s'), $email) . "\r\n\r\n";
                $message .= sprintf(__('First Name: %s'), $firstName) . "\r\n\r\n";
                $message .= sprintf(__('Last Name: %s'), $lastName) . "\r\n\r\n";
                $message .= sprintf(__('Company Name: %s'), $companyName) . "\r\n\r\n";
                $message .= sprintf(__('Job Title/Role: %s'), $jobTitleRole) . "\r\n\r\n";
                $message .= sprintf(__('Address: %s'), $address) . "\r\n\r\n";
                $message .= sprintf(__('Address Optional: %s'), $addressOptional) . "\r\n\r\n";
                $message .= sprintf(__('Country: %s'), $countryName) . "\r\n\r\n";
                $message .= sprintf(__('City: %s'), $city) . "\r\n\r\n";
                $message .= sprintf(__('Province: %s'), $province) . "\r\n\r\n";
                $message .= sprintf(__('Postal Code: %s'), $postalCode) . "\r\n\r\n";
                $message .= sprintf(__('Phone: %s'), $phone) . "\r\n\r\n";
                $message .= sprintf(__('Phone Optional: %s'), $phoneOptional) . "\r\n\r\n";
                $message .= sprintf(__('About Us: %s'), $aboutUs) . "\r\n\r\n";
                $message .= sprintf(__('Note: %s'), $note) . "\r\n\r\n";
                $message .= __('Visit the following address: ') . network_site_url("wp-admin/users.php") . "\r\n\r\n";

                $title = sprintf(__('[%s] Customer Request Access'), $siteName);
                if ($message && !wp_mail($emailAdmin, wp_specialchars_decode($title), $message)) {
                    error_log("Unable to send the requet access confirmation email." . print_r([
                            "email" => $email,
                            "first" => $firstName,
                            "last"  => $lastName,
                            "company" => $companyName
                        ], true));
                    $error->add('_error_user', "We are experiencing a network issue at the moment. Please try again later.");
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
<script type="text/javascript" 
    src="https://www.google.com/recaptcha/api.js?onload=wpcaptcha_captcha&render=<?php echo $wpforms_settings['recaptcha-site-key'];?>"
    id="recaptcha3-js"
    defer="defer"
    data-wp-strategy="defer">
</script>   
    <main role="main">
        <section class="component-form-hero" style="background-image: url('<?php echo get_template_directory_uri(); ?>/assets/images/bg-sign-in.png')" data-url="/wp-json/barnet/v1/checkemail/" data-method="POST" data-request-step>
            <div class="component-form --right">
                <?php if (!$showRequestSuccess) :?>
                    <div class="component-form__wrapper">
                        <div class="component-heading-group --dark-mode" data-heading>
                            <h2 class="component-heading-group__heading --size-lg to-uppercase">
                                <?php echo get_theme_mod('registration_title_text', class_exists('BarnetDefaultText') ? BarnetDefaultText::REGISTRATION_TITLE : ''); ?>
                            </h2>
                        </div>
                        <?php
                        if (!empty($mess)) :
                            ?>
                            <?php echo $mess;?>
                        <?php
                        endif;
                        ?>
                        <form id="risgister-form" class="component-form__form" method="POST" data-parsley-errors-messages-disabled data-parsley-validate>
                            <div class="component-form__content" data-tab-content>
                                <div class="component-form__group">
                                    <div class="component-heading-form">
                                        <div class="component-heading-form__desc">
                                            <?php echo get_theme_mod('registration_header_text', class_exists('BarnetDefaultText') ? BarnetDefaultText::REGISTRATION_HEADER_TEXT : ''); ?>
                                        </div>
                                    </div>
                                    <div class="component-form__group__input">
                                        <div class="component-form__item">
                                            <input class="form-control --dark-mode" type="text" name="first_name" value="<?php echo (isset($_POST['first_name']) ? $_POST['first_name'] : '' );?>" placeholder="<?php _e('First Name');?> *" autocomplete="off" data-parsley-required>
                                        </div>
                                        <div class="component-form__item">
                                            <input class="form-control --dark-mode" type="text" name="last_name" value="<?php echo (isset($_POST['last_name']) ? $_POST['last_name'] : '' );?>" placeholder="<?php _e('Last Name');?> *" autocomplete="off" data-parsley-required>
                                        </div>
                                        <div class="component-form__item">
                                            <input class="form-control --dark-mode" type="email" name="email" value="<?php echo (isset($_POST['email']) && !$showRequestSuccess ? $_POST['email'] : '' );?>" placeholder="<?php _e('Work Email Address');?> *" autocomplete="off" data-parsley-required data-parsley-type="email">
                                            <label class="component-form__lbl" for="email">
                                                <?php echo get_theme_mod('registration_email_note', class_exists('BarnetDefaultText') ? BarnetDefaultText::REGISTRATION_EMAIL_NOTE : ''); ?>
                                            </label>
                                        </div>
                                         <div class="component-form__item">
                                         <input type="hidden" id="BPNJ" name="BPNJ" value="BPNJ" />
                                         <input type="hidden" type="text" class="text-field-utm w-input" maxlength="256" name="utm_source" data-name="utm_source" placeholder="UTM Source" id="utm_source" />
                                         <input type="hidden" type="text" class="text-field-utm w-input" maxlength="256" name="utm_medium" data-name="utm_medium" placeholder="UTM Medium" id="utm_medium-2" />
                                         <input type="hidden" type="text" class="text-field-utm w-input" maxlength="256" name="Utm-Content" data-name="Utm_Content" placeholder="UTM Content" id="Utm-Content-2" />
                                         <input type="hidden" type="text" class="text-field-utm w-input" maxlength="256" name="Utm-Id" data-name="Utm_ID" placeholder="UTM ID" id="Utm-ID" />
                                         <input type="hidden" type="text" class="text-field-utm w-input" maxlength="256" name="Utm-Term" data-name="Utm Term" placeholder="UTM Term" id="Utm-Term-3" />

                                         <input type="hidden" type="text" class="text-field-utm w-input" maxlength="256" name="Referrer-URL" data-name="Referrer URL" id="Referrer-URL" placeholder="e.g., www.google.com" aria-label="Referrer URL" value="">
     									<input type="hidden" id="[attributer-channel]" name="attributer-channel" value="[channel]">
     									<input type="hidden" id="[attributer-channeldrilldown1]" name="attributer-channeldrilldown1" value="[channeldrilldown1]">
     									<input type="hidden" id="[attributer-channeldrilldown2]" name="attributer-channeldrilldown2" value="[channeldrilldown2]">
     									<input type="hidden" id="[attributer-channeldrilldown3]" name="attributer-channeldrilldown3" value="[channeldrilldown3]">
     									<input type="hidden" id="[attributer-channeldrilldown4]" name="attributer-channeldrilldown4" value="[channeldrilldown4]">
     									<input type="hidden" id="[attributer-landingpage]" name="attributer-landingpage" value="[landingpage]">
     									<input type="hidden" id="[attributer-landingpagegroup]" name="attributer-landingpagegroup" value="[landingpagegroup]">
                                          </div>
                                    </div>
                                </div>
                                <div class="component-form__group text-center">
                                    <button class="btn btn-solid --dark-mode" onclick="capcha()" data-next-btn type="button">
                                        <?php echo get_theme_mod('registration_continue_text', class_exists('BarnetDefaultText') ? BarnetDefaultText::REGISTRATION_CONTINUE : ''); ?>
                                    </button>
                                </div>
                            </div>
                            <div class="component-form__content d-none" data-tab-content>
                                <div class="component-form__group">
                                    <div class="component-form__group__input">
                                        <div class="component-form__item">
                                            <input class="form-control --dark-mode" type="text" name="company_name" value="<?php echo (isset($_POST['company_name']) ? $_POST['company_name'] : '' );?>" placeholder="<?php _e('Company Name');?> *" autocomplete="off" data-parsley-required>
                                        </div>
                                        <div class="component-form__item">
                                            <input class="form-control --dark-mode" type="text" name="address" value="<?php echo (isset($_POST['address']) ? $_POST['address'] : '' );?>" placeholder="<?php _e('Address');?> *" autocomplete="off" data-parsley-required>
                                        </div>
                                        <div class="component-form__item">
                                            <input class="form-control --dark-mode" type="text" name="address_optional" value="<?php echo (isset($_POST['address_optional']) ? $_POST['address_optional'] : '' );?>" placeholder="<?php _e('Address Line 2');?>" autocomplete="off">
                                        </div>
                                        <div class="component-form__item">
                                            <select class="form-control --dark-mode" name="country" required data-parsley-required>
                                                <option value=""><?php _e('Country *');?></option>
                                                <?php
                                                $getListCountry = Barnet::getListCountries();
                                                $valueCountry = isset($_POST['country']) ? $_POST['country'] : '';
                                                foreach ($getListCountry as $k => $v) {
                                                    printf(
                                                        '<option value="%s"%s>%s</option>',
                                                        esc_attr($k),
                                                        selected($valueCountry, $k, false),
                                                        esc_html(__($v))
                                                    );
                                                }
                                                ?>
                                            </select>
                                        </div>

                                        <div class="component-form__item">
                                            <input class="form-control --dark-mode" type="text" name="city" value="<?php echo (isset($_POST['city']) ? $_POST['city'] : '' );?>" placeholder="<?php _e('City');?> *" autocomplete="off" data-parsley-required>
                                        </div>
                                        <div class="component-form__two-col">
                                            <div class="col-6 component-form__item">
                                                <input class="form-control --dark-mode" type="text" name="province" value="<?php echo (isset($_POST['province']) ? $_POST['province'] : '' );?>" placeholder="<?php _e('State/Province');?> *" autocomplete="off" data-parsley-required>
                                            </div>
                                            <div class="col-6 component-form__item">
                                                <input class="form-control --dark-mode" type="text" name="postal_code" value="<?php echo (isset($_POST['postal_code']) ? $_POST['postal_code'] : '' );?>" placeholder="<?php _e('Postal Code');?> *" autocomplete="off" data-parsley-required>
                                            </div>
                                        </div>
                                        <div class="component-form__item">
                                            <input class="form-control --dark-mode" type="number" name="phone" value="<?php echo (isset($_POST['phone']) ? $_POST['phone'] : '' );?>" placeholder="<?php _e('Best Contact Phone');?> *" autocomplete="off" data-parsley-required>
                                        </div>
                                        <div class="component-form__item">
                                            <input class="form-control --dark-mode" type="number" name="phone_optional" value="<?php echo (isset($_POST['phone_optional']) ? $_POST['phone_optional'] : '' );?>" placeholder="<?php _e('Mobile Phone');?>" autocomplete="off">
                                        </div>
                                        <div class="component-form__item">
                                            <input class="form-control --dark-mode" type="text" name="job_title_role" value="<?php echo (isset($_POST['job_title_role']) ? $_POST['job_title_role'] : '' );?>" placeholder="<?php _e('Job Title/Role');?> *" autocomplete="off" data-parsley-required>
                                        </div>
                                    </div>
                                </div>
                                <div class="component-form__group">
                                    <div class="component-form-group__input">
                                        <div class="component-form__item">
                                            <?php
                                            $valueAboutUs = class_exists('BarnetDefaultText') ? BarnetDefaultText::REGISTRATION_ABOUT_US_VALUE : array();
                                            ?>
                                            <select class="form-control --dark-mode" name="about_us" data-parsley-required>
                                                <option disabled selected value=""><?php echo get_theme_mod('registration_about_us_text', class_exists('BarnetDefaultText') ? BarnetDefaultText::REGISTRATION_ABOUT_US_TEXT : ''); ?></option>
                                                <?php
                                                $valueAboutUsSelect = isset($_POST['about_us']) ? $_POST['about_us'] : '';
                                                foreach ($valueAboutUs as $v) {
                                                    printf(
                                                        '<option value="%s"%s>%s</option>',
                                                        esc_attr($v),
                                                        selected($valueAboutUsSelect, $v, false),
                                                        esc_html(__($v))
                                                    );
                                                }
                                                ?>
                                            </select>
                                        </div>
                                    </div>
                                </div>
                                <div class="component-form__group">
                                    <div class="component-heading-form">
                                        <div class="component-heading-form__desc">
                                            <?php echo get_theme_mod('registration_note_text', class_exists('BarnetDefaultText') ? BarnetDefaultText::REGISTRATION_NOTE_TEXT : ''); ?>
                                        </div>
                                    </div>
                                    <div class="component-form-group__input">
                                        <div class="component-form__item">
                                            <textarea class="form-control --dark-mode" type="text" name="note" placeholder="<?php _e('Optional Note');?>"><?php echo (isset($_POST['note']) ? $_POST['note'] : '' );?></textarea>
                                        </div>
                                    </div>
                                </div>
                                <div class="component-form__group">
                                    <div class="component-form-group__input">
                                        <div class="component-form__item">
                                            <div class="form-checkbox --dark-mode">
                                                <input type="checkbox" id="checkbox1" name="newsletter" <?php checked(isset($_POST['newsletter']) ? $_POST['newsletter'] : 'on', 'on'); ?> data-parsley-checkmin="1">
                                                <label for="checkbox1">
                                                    <?php echo get_theme_mod('registration_newsletter_text', class_exists('BarnetDefaultText') ? BarnetDefaultText::REGISTRATION_NEWSLETTER_TEXT : ''); ?>
                                                    <span></span>
                                                </label>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <?php
									
                                    if(get_option("recapcha_type_woo")=="v2"){
                                    do_action( 'register_form' );
                                    }
                                    else{
                                        echo '<input type="hidden" id="gtoken" name="g_token">';
                                    }
                                    ?>

                                
                                <div class="component-form__group">
                                    <div class="component-heading-form">
                                        <div class="component-heading-form__desc">
                                            By signing up as a customer and providing us certain information, you are agreeing to our<a target="_blank" href="/privacy-policy"> Privacy Policy</a>                                        
                                        </div>
                                    </div>
                                </div>
                                <div class="component-form__group text-center">
                                    <?php if(get_option("recapcha_type_woo")=="v2"){?>
                                    <button class="btn btn-solid --dark-mode" data-next-btn type="submit"><?php _e('Submit Request');?></button>
                                    <?php }
                                    else  {?>
                                        <input type="hidden" name="action" value="validate_captcha">
                                        <button class="g-recaptcha" 
                                            data-sitekey="<?php echo $wpforms_settings['recaptcha-site-key'];?>" 
                                            data-callback='onSubmit' 
                                            data-action='submit'><?php _e('Submit Request');?></button>
                                    <?php }?>

                                </div>
                            </div>
                        </form>
                    </div>
                <?php else :?>
                    <div class="component-form__wrapper">
                        <div class="component-form__content" data-tab-content>
                            <div class="component-heading-group --dark-mode text-center">
                                <h2 class="component-heading-group__heading --size-lg to-uppercase text-left">
                                    <?php echo get_theme_mod('registration_thanks_title', class_exists('BarnetDefaultText') ? BarnetDefaultText::REGISTRATION_THANKS_TITLE : ''); ?>
                                </h2>
                                <div class="component-heading-group__desc">
                                    <?php echo get_theme_mod('registration_thanks_text', class_exists('BarnetDefaultText') ? BarnetDefaultText::REGISTRATION_THANKS_TEXT : ''); ?>
                                </div>
                            </div>
                            <?php
                            $valueOk = get_theme_mod('registration_thanks_ok', class_exists('BarnetDefaultText') ? BarnetDefaultText::REGISTRATION_THANKS_OK : '');
                            ?>
                            <div class="component-form__btn"><a class="btn btn-solid --dark-mode" href="/" title="<?php echo esc_html($valueOk);?>"><?php echo $valueOk;?></a>
                            </div>
                        </div>
                    </div>
                <?php endif;?>
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
        document.getElementById("risgister-form").submit();
    }
</script>

<style type="text/css">
    .grecaptcha-badge {
        z-index: 1000;
    }
</style>