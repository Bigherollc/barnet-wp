<?php
$user = wp_get_current_user();
$userInfo = $user->user_extra_info;
$userExtraInfo = array();
if (!empty($userInfo)) {
    $userExtraInfo = unserialize($userInfo); 
}

$urlProfile = get_site_url();
if (function_exists("tml_get_action_url")) {
    $urlProfile = tml_get_action_url('dashboard' );
}

$mess = "";

$firstName = $user->first_name;
$lastName = $user->last_name;
$userEmail = $user->user_email;
$userEmail = htmlspecialchars_decode($userEmail);

$companyName = isset($userExtraInfo["company_name"]) ? $userExtraInfo["company_name"] : '';
$address = isset($userExtraInfo["address"]) ? $userExtraInfo["address"] : '';
$addressOptional = isset($userExtraInfo["address_optional"]) ? $userExtraInfo["address_optional"] : '';
$country = isset($userExtraInfo["country"]) ? $userExtraInfo["country"] : '';
$city = isset($userExtraInfo["city"]) ? $userExtraInfo["city"] : '';
$province = isset($userExtraInfo["province"]) ? $userExtraInfo["province"] : '';
$postalCode = isset($userExtraInfo["postal_code"]) ? $userExtraInfo["postal_code"] : '';
$phone = isset($userExtraInfo["phone"]) ? $userExtraInfo["phone"] : '';
$phoneOptional = isset($userExtraInfo["phone_optional"]) ? $userExtraInfo["phone_optional"] : '';
$jobTitleRole = isset($userExtraInfo["job_title_role"]) ? $userExtraInfo["job_title_role"] : '';
$aboutUs = isset($userExtraInfo["about_us"]) ? trim($userExtraInfo["about_us"]) : '';
//$newsletter = isset($userExtraInfo["newsletter"]) ? $userExtraInfo["newsletter"] : 'off';
$newsletter = get_user_meta($user->ID, 'newsletter', true);
$note = isset($userExtraInfo["note"]) ? $userExtraInfo["note"] : '';

if (!empty($_POST['first_name'])) {
    $firstName = isset($_POST["first_name"]) ? sanitize_text_field($_POST["first_name"]) : '';
    $lastName = isset($_POST["last_name"]) ? sanitize_text_field($_POST["last_name"]) : '';

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

    $error = new WP_Error();

    if (empty($firstName) || empty($lastName)) {
        if (empty($firstName)) {
            $error->add('empty_fistname',  __("The Fist Name field is empty."));
        }

        if (empty($lastName)) {
            $error->add('empty_lastname',  __("The Last Name field is empty."));
        }
    }

    if(empty($companyName)) {
        $error->add('empty_company',  __("The Company Name field is empty."));
    }

    if(empty($address)) {
        $error->add('empty_address',  __("The Address field is empty."));
    }

    if(empty($country)) {
        $error->add('empty_country',  __("The Country field is empty."));
    }

    if(empty($city)) {
        $error->add('empty_city',  __("The City field is empty."));
    }

    if(empty($province)) {
        $error->add('empty_province',  __("The State/Province field is empty."));
    }

    if(empty($postalCode)) {
        $error->add('empty_postalCode',  __("The Postal Code field is empty."));
    }

    if(empty($phone)) {
        $error->add('empty_phone',  __("The Work Phone field is empty."));
    }

    if (empty($jobTitleRole)) {
        $error->add('empty_job_title_role', __("The Job Title/Role field is empty."));
    }
    if (empty($error->errors)) {
        $userData = array(
            'ID' => $user->ID,
            'user_email' => $userEmail,
            'first_name' => $firstName,
            'last_name' => $lastName,
            'nickname' => $firstName . " " . $lastName
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
            'note' => $note
        );

        $userId = wp_update_user($userData);
        if (is_wp_error($userId)) {
            $error->add('_error_user', $user->get_error_message());
        } else {
            update_user_meta( $userId, 'user_extra_info', serialize($userDataOther));
			update_user_meta($userId, 'newsletter', $newsletter);
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
        $urlProfile = add_query_arg("updateprofile", "true", $urlProfile);
        wp_redirect($urlProfile);
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
                            <div class="component-heading-group --dark-mode --mg-bottom-sm text-center" data-heading>
                                <h2 class="component-heading-group__heading --size-lg to-uppercase"><?php _e('Update Details');?>
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
                                                <input class="form-control --dark-mode" type="text" name="first_name" placeholder="<?php _e('First Name');?> *" value="<?php echo $firstName;?>" autocomplete="off" data-parsley-required>
                                            </div>
                                            <div class="component-form__item">
                                                <input class="form-control --dark-mode" type="text" name="last_name" placeholder="<?php _e('Last Name');?> *" value="<?php echo $lastName;?>" autocomplete="off" data-parsley-required>
                                            </div>
                                            <div class="component-form__item">
                                                <input class="form-control --dark-mode" type="email" name="email" placeholder="<?php _e('Work Email Address');?> *" value="<?php echo $user->user_email;?>" autocomplete="off" data-parsley-required disabled>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="component-form__group">
                                        <div class="component-heading-form --size-sm">
                                            <h2 class="component-heading-form__heading">Company Details:
                                            </h2>
                                        </div>
                                        <div class="component-form__group__input">
                                            <div class="component-form__item">
                                                <input class="form-control --dark-mode" type="text" name="company_name" placeholder="<?php _e('Company Name');?> *" value="<?php echo $companyName;?>" autocomplete="off" data-parsley-required>
                                            </div>
                                            <div class="component-form__item">
                                                <input class="form-control --dark-mode" type="text" name="address" placeholder="<?php _e('Address');?> *" value="<?php echo $address;?>" autocomplete="off" data-parsley-required>
                                            </div>
                                            <div class="component-form__item">
                                                <input class="form-control --dark-mode" type="text" name="address_optional" placeholder="<?php _e('Address Line 2 (Optional)');?>" value="<?php echo $addressOptional;?>" autocomplete="off">
                                            </div>
                                            <div class="component-form__item">
                                                <select class="form-control --dark-mode" name="country" required data-parsley-required>
                                                    <option value=""><?php _e('Country');?> *</option>
                                                    <?php
                                                    $getListCountry = Barnet::getListCountries();
                                                    foreach ($getListCountry as $k => $v) {
                                                        printf(
                                                            '<option value="%s"%s>%s</option>',
                                                            esc_attr($k),
                                                            selected($country, $k, false),
                                                            esc_html(__($v))
                                                        );
                                                    }
                                                    ?>
                                                </select>
                                            </div>
                                            <div class="component-form__item">
                                                <input class="form-control --dark-mode" type="text" name="city" placeholder="<?php _e('City');?> *" value="<?php echo $city;?>" autocomplete="off" data-parsley-required>
                                            </div>
                                            <div class="component-form__two-col">
                                                <div class="col-6 component-form__item">
                                                    <input class="form-control --dark-mode" type="text" name="province" placeholder="<?php _e('State/Province');?> *" value="<?php echo $province;?>" autocomplete="off" data-parsley-required>
                                                </div>
                                                <div class="col-6 component-form__item">
                                                    <input class="form-control --dark-mode" type="text" name="postal_code" placeholder="<?php _e('Postal Code');?> *" value="<?php echo $postalCode;?>" autocomplete="off" data-parsley-required>
                                                </div>
                                            </div>
                                            <div class="component-form__item">
                                                <input class="form-control --dark-mode" type="text" name="phone" placeholder="<?php _e('Work Phone');?> *" value="<?php echo $phone;?>" autocomplete="off" data-parsley-required>
                                            </div>
                                            <div class="component-form__item">
                                                <input class="form-control --dark-mode" type="text" name="phone_optional" placeholder="<?php _e('Mobile Phone (Optional)');?>" value="<?php echo $phoneOptional;?>" autocomplete="off">
                                            </div>
                                            <div class="component-form__item">
                                                <input class="form-control --dark-mode" type="text" name="job_title_role" placeholder="<?php _e('Job Title/Role');?> *" value="<?php echo $jobTitleRole;?>" autocomplete="off" data-parsley-required>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="component-form__group">
                                        <div class="component-heading-form">
                                            <div class="component-heading-form__desc">
                                                <a href="/privacy-policy"><?php _e('By providing information for this sample request, you are agreeing to our Privacy Policy');?></a>                                        
                                            </div>
                                        </div>
                                    </div>
                                    <div class="component-form__group text-center">
                                        <button class="btn btn-solid btn-large --dark-mode" data-next-btn type="submit"><?php _e('Save');?></button>
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
