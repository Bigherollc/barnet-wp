<?php
/* Template Name: Samples Selected */

$user = wp_get_current_user();
$userExtraInfo = array();

$firstName = '';
$lastName = '';
$userEmail = '';
$note = '';

$barnetSampleRecaptcha = new BarnetRecaptcha();
$barnetSampleRecaptchaEnable = false;
$newsletter="";
if ($user && $user->ID > 0) {
    $userInfo = $user->user_extra_info;
	$newsletter = get_user_meta($user->ID, 'newsletter', true);
    if (!empty($userInfo)) {
        $userExtraInfo = unserialize($userInfo);
    }

    $firstName = $user->first_name;
    $lastName = $user->last_name;
    $userEmail = $user->user_email;
    $userEmail = htmlspecialchars_decode($userEmail);
    //$note = $user->description;
} else  {
    if ($barnetSampleRecaptcha->isEnable()) {
        $barnetSampleRecaptchaEnable = true;
    }
}

$email = $userEmail;
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

$mess = "";
$showRequestSuccess = false;
if (isset($_REQUEST['checkemail']) && trim($_REQUEST['checkemail']) == "confirm") {
    $showRequestSuccess = true;
}

$keyStore = isset($_REQUEST["keystore"]) ? trim($_REQUEST["keystore"]) : '';

if ($_POST) {
    $firstName = isset($_POST["first_name"]) ? sanitize_text_field($_POST["first_name"]) : '';
    $lastName = isset($_POST["last_name"]) ? sanitize_text_field($_POST["last_name"]) : '';
    $email = sanitize_email($_POST['email']);
    $addNote = isset($_POST["note"]) ? esc_textarea($_POST["note"]) : '';
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
    //$newsletter = isset($_POST["newsletter"]) ? sanitize_text_field($_POST["newsletter"]) : 'off';
    $aboutUs = isset($_POST["about_us"]) ? trim($_POST["about_us"]) : '';
    $selectedSample = isset($_POST["selected_sample"]) ? trim($_POST["selected_sample"]) : '';
    $keyStore = isset($_POST["key_store"]) ? trim($_POST["key_store"]) : '';

    if (!empty($userEmail)) {
        $email = $userEmail;
    }
    $email = htmlspecialchars_decode($email);
    $error = new WP_Error();

    if ($barnetSampleRecaptchaEnable ) {
        $result = $barnetSampleRecaptcha->captchaV3Verification();
        if($result['success'] == 0 || $result['score'] < 0.5) {
            $error->add('error_captcha', __("The captcha is incorrectly configured."));
        }

    }

    if (empty($error->errors)) {
        if (class_exists('BarnetSampleRequest')) {
            $dataPost = array(
                'email' => $email,
                'firstName' => $firstName,
                'lastName' => $lastName,
                'companyName' => $companyName,
                'address' => $address,
                'addressOptional' => $addressOptional,
                'country' => $country,
                'city'  => $city,
                'province' => $province,
                'postalCode' => $postalCode,
                'phone' => $phone,
                'phoneOptional' => $phoneOptional,
                'job_title_role' => $jobTitleRole,
                'newsletter' => $newsletter,
                'aboutUs' => $aboutUs,
                'note' => $note,
                'addNote' => $addNote,
                'selectedSample' => $selectedSample
            );
            $sampleRequestSave = new BarnetSampleRequest();
            $rs = $sampleRequestSave->sampleRequestAction($dataPost, $user);
            if (is_wp_error($rs)) {
                $error = $rs;
            }
        }
    }

    if (!empty($error->errors)) {
        $arrErr = $error->get_error_messages();
        $mess = '<ul class="parsley-errors-list filled --mg-bottom-xs" data-validate-server="">';
        foreach ($arrErr as $err) {
            $mess .= '<li>' . $err . '</li>';
        }
        $mess .= '</ul>';
    } else {
        global $wp;
        $requestGet = array();

        $current_url = site_url(add_query_arg(array($requestGet), "/" . $wp->request . "/"));
        $redirect_to = add_query_arg(array("checkemail" => "confirm", 'keystore' => $keyStore), $current_url);
        wp_redirect($redirect_to);
        exit();
    }

}

get_header(); ?>

<main role="main">
    <?php if (!$showRequestSuccess) : ?>
        <section class="component-sample-request" data-sample-request>
            <div class="container">
                <div class="component-form">
                    <div class="component-form__wrapper">
                        <form class="component-form__form" method="post" data-parsley-validate data-parsley-errors-messages-disabled>
                            <div class="component-form__content" data-tab-content>
                                <div class="component-form__infor">
                                    <div class="component-form__caption">
                                        <div class="component-heading-group --dark-mode">
                                            <h2 class="component-heading-group__heading --size-lg to-uppercase">
                                                <?php echo get_theme_mod('sample_title_text', class_exists('BarnetDefaultText') ? BarnetDefaultText::SAMPLE_TITLE_TEXT : ''); ?>
                                            </h2>
                                            <div class="component-heading-group__desc">
                                                <p>
                                                    <?php echo get_theme_mod('sample_heading_line_1', class_exists('BarnetDefaultText') ? BarnetDefaultText::SAMPLE_HEADING_LINE_1 : ''); ?>
                                                </p>
                                                <p>
                                                    <?php echo get_theme_mod('sample_heading_line_2', class_exists('BarnetDefaultText') ? BarnetDefaultText::SAMPLE_HEADING_LINE_2 : ''); ?>
                                                </p>
                                            </div>
                                        </div>
                                        <div class="component-form__note">
                                            <?php echo get_theme_mod('sample_heading_note', class_exists('BarnetDefaultText') ? BarnetDefaultText::SAMPLE_HEADING_NOTE : ''); ?>
                                        </div>
                                    </div>
                                    <div class="component-form__field">
                                        <?php
                                        if (!empty($mess)) :
                                            ?>
                                            <?php echo $mess;?>
                                        <?php
                                        endif;
                                        ?>
                                        <?php
                                        $classBlockUser = '';
                                        $disableEmail = false;
                                        if (is_user_logged_in()) :
                                            $classBlockUser = ' d-none';
                                            $disableEmail = true;
                                            ?>
                                            <div class="component-form__infor__confirm" data-infor-confirm>
                                                <div class="component-form__infor__confirm--wrapper">
                                                    <div class="component-form__infor__confirm--heading">
                                                        <div class="component-heading-form --size-sm">
                                                            <h2 class="component-heading-form__heading">
                                                                <?php echo get_theme_mod('sample_ship_to', class_exists('BarnetDefaultText') ? BarnetDefaultText::SAMPLE_SHIP_TO : ''); ?>
                                                            </h2>
                                                        </div>
                                                        <button class="btn" type="button" data-edit-btn>
                                                            <?php _e('Edit');?>
                                                        </button>
                                                    </div>
                                                    <div class="component-form__infor__confirm--content">
                                                        <div class="component-form__infor__confirm--user">
                                                            <p><?php echo $firstName . ' ' . $lastName;?></p>
                                                            <p><?php echo $userEmail;?></p>
                                                            <p><?php echo !empty($phone) ? 'W: ' . $phone : '';?></p>
                                                            <p>
                                                                <?php echo !empty($phoneOptional) ? 'M: ' .$phoneOptional : '';?>
                                                            </p>
                                                        </div>
                                                        <div class="component-form__infor__confirm--company">
                                                            <p><?php echo $companyName;?></p>
                                                            <p><?php echo $address; ?></p>
                                                            <p><?php echo $addressOptional; ?></p>
                                                            <p>
                                                                <?php echo isset($province) ? $province: '';?> <?php echo isset($postalCode) ? $postalCode : '';?>
                                                            </p>
                                                            <p><?php echo isset($country) ? $country : '';?></p>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        <?php endif;?>
                                        <div class="comnponent-form__infor__edit<?php echo $classBlockUser;?>" data-infor-edit>
                                            <div class="component-form__group">
                                                <div class="component-heading-form --size-sm">
                                                    <h2 class="component-heading-form__heading"><?php _e('Ship To:');?>
                                                    </h2>
                                                    <div class="component-heading-form__desc">
                                                        <?php _e('All fields are required to complete your request.');?>
                                                    </div>
                                                </div>
                                                <div class="component-form__group__input">
                                                    <div class="component-form__item">
                                                        <input class="form-control --dark-mode" type="text" name="first_name" placeholder="<?php _e('First Name');?> *" value="<?php echo $firstName;?>" autocomplete="off" data-parsley-required>
                                                    </div>
                                                    <div class="component-form__item">
                                                        <input class="form-control --dark-mode" type="text" name="last_name" placeholder="<?php _e('Last Name');?> *" value="<?php echo $lastName;?>" autocomplete="off" data-parsley-required>
                                                    </div>
                                                    <div class="component-form__item">
                                                        <input class="form-control --dark-mode" type="email" name="email" placeholder="<?php _e('Work Email Address');?> *" value="<?php echo $email;?>" autocomplete="off" data-parsley-required<?php $disableEmail ? ' disabled' : ''; ?>>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="component-form__group">
                                                <div class="component-heading-form --size-sm">
                                                    <h2 class="component-heading-form__heading">
                                                        <?php _e('Company Details:');?>
                                                    </h2>
                                                </div>
                                                <div class="component-form__group__input">
                                                    <div class="component-form__item">
                                                        <input class="form-control --dark-mode" type="text" name="company_name" placeholder="<?php _e('Company Name');?> *" value="<?php echo $companyName;?>" autocomplete="off" data-parsley-required>
                                                    </div>
                                                    <div class="component-form__item">
                                                        <input class="form-control --dark-mode" type="text" name="address" placeholder="<?php _e('Address');?> *" value="<?php echo $address; ?>" autocomplete="off" data-parsley-required>
                                                    </div>
                                                    <div class="component-form__item">
                                                        <input class="form-control --dark-mode" type="text" name="address_optional" placeholder="<?php _e('Address Line 2');?>" value="<?php echo $addressOptional;?>" autocomplete="off">
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
                                                        <input class="form-control --dark-mode" type="text" name="city" placeholder="<?php _e('City');?>  *" value="<?php echo $city;?>" autocomplete="off" data-parsley-required>
                                                    </div>
                                                    <div class="component-form__two-col">
                                                        <div class="col-6 component-form__item">
                                                            <input class="form-control --dark-mode" type="text" name="<?php _e('province');?>" placeholder="<?php _e('State/Province');?> *" value="<?php echo $province;?>" autocomplete="off" data-parsley-required>
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
                                                        <input class="form-control --dark-mode" type="text" name="job_title_role" value="<?php echo $jobTitleRole;?>" placeholder="<?php _e('Job Title/Role');?> *" autocomplete="off" data-parsley-required>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="component-form__group">
                                            <div class="component-heading-form --size-sm">
                                                <h2 class="component-heading-form__heading">
                                                    <?php echo get_theme_mod('sample_additional_note', class_exists('BarnetDefaultText') ? BarnetDefaultText::SAMPLE_ADDITIONAL_NOTE : ''); ?>
                                                </h2>
                                                <div class="component-heading-form__desc">
                                                    <?php echo get_theme_mod('sample_additional_note_text', class_exists('BarnetDefaultText') ? BarnetDefaultText::SAMPLE_ADDITIONAL_NOTE_TEXT : ''); ?>
                                                </div>
                                            </div>
                                            <div class="component-form-group__input">
                                                <div class="component-form__item">
                                                    <textarea class="form-control --dark-mode" type="text" name="note" placeholder="<?php _e('Optional Note');?>"><?php echo $addNote;?></textarea>
                                                </div>
                                            </div>
                                        </div>
                                        <!--<div class="component-form__group">
                                            <div class="component-form-group__input">
                                                <div class="component-form__item">
                                                    <div class="form-checkbox --dark-mode">
                                                        <input type="checkbox" id="checkbox1" name="newsletter" <?php checked(isset($newsletter) ? $newsletter : 0, 'on'); ?> data-parsley-checkmin="1" data-parsley-required>
                                                        <label for="checkbox1"> <?php _e('Signup for our newsletter to recieve updates about new prodcuts and whatever else is in the newsletter');?>
                                                            <span></span>
                                                        </label>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>-->
                                        <div class="component-form__group">
                                            <div class="component-heading-form --size-sm">
                                                <div class="component-heading-form__desc">
                                                    <?php echo get_theme_mod('registration_about_us_text', class_exists('BarnetDefaultText') ? BarnetDefaultText::REGISTRATION_ABOUT_US_TEXT : ''); ?>
                                                </div>
                                            </div>
                                            <div class="component-form-group__input">
                                                <div class="component-form__item">
                                                    <?php
                                                    $valueAboutUs = class_exists('BarnetDefaultText') ? BarnetDefaultText::REGISTRATION_ABOUT_US_VALUE : array();
                                                    ?>
                                                    <select class="form-control --dark-mode" name="about_us" data-parsley-required>
                                                        <option disabled selected value="">
                                                            <?php echo get_theme_mod('sample_select_text', class_exists('BarnetDefaultText') ? BarnetDefaultText::SAMPLE_SELECT_TEXT : ''); ?>
                                                        </option>
                                                        <?php
                                                        $valueAboutUsSelect = isset($aboutUs) ? $aboutUs : '';
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
                                        <?php if ($barnetSampleRecaptchaEnable) :?>
                                            <?php $barnetSampleRecaptcha->displayCaptcha();?>
                                        <?php endif;?>
                                        <div class="component-form__group">
                                            <div class="component-heading-form">
                                                <div class="component-heading-form__desc">
                                                    <a href="/privacy-policy">
                                                        <?php if (is_user_logged_in()):?>
                                                            <?php _e('By providing information for this sample request, you are agreeing to our Privacy Policy');?>
                                                        <?php else:?>
                                                            <?php _e('By providing us information, you are agreeing to our Privacy Policy');?>
                                                        <?php endif;?>
                                                    </a>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="component-form__group text-center d-none d-sm-block">
                                            <button class="btn btn-solid --dark-mode" data-next-btn type="submit"><?php _e('Submit Request');?></button>
                                        </div>
                                    </div>
                                </div>
                                <div class="component-form__product">
                                    <div class="component-form__group --mg-bottom-lg">
                                        <div class="component-form__product__selected">
                                            <div class="component-heading-group">
                                                <h2 class="component-heading-group__heading --size-md">
                                                    <?php echo get_theme_mod('sample_selected_samples', class_exists('BarnetDefaultText') ? BarnetDefaultText::SAMPLE_SELECTED_SAMPLES : ''); ?>
                                                    <span class="count-wrapper">(<span class="count-number" data-count-sample>0</span>)</span></h2>
                                            </div>
                                            <div class="component-form__product__list" data-wrap-sample></div>
                                        </div>
                                    </div>
                                    <div class="component-form__group d-none">
                                        <div class="component-heading-group">
                                            <h2 class="component-heading-group__heading --size-md"><?php _e('Find a Product');?>
                                            </h2>
                                            <div class="component-heading-group__desc"><?php _e('You can request samples of individual products and starting formulas.');?>
                                            </div>
                                        </div>
                                        <div class="component-form__ctas text-center">
                                            <a class="btn btn-regular btn-block" href="<?php echo get_permalink(get_page_by_path('active-landing-login'));?>" title="<?php _e('All active materials');?>"><?php _e('All active materials');?></a>
                                            <a class="btn btn-regular btn-block" href="<?php echo get_permalink(get_page_by_path('system-landing-login'));?>" title="<?php _e('All system formers');?>"><?php _e('All system formers');?></a>
                                            <a class="btn btn-regular btn-block" href="<?php echo get_permalink(get_page_by_path('formula-listing-login'));?>" title="<?php _e('All Starting Formulas');?>"><?php _e('All Starting Formulas');?></a>
                                        </div>
                                    </div>
                                    <div class="component-form__group">
                                        <div class="component-heading-group">
                                            <h2 class="component-heading-group__heading --size-md">
                                                <?php echo get_theme_mod('sample_add_a_sample', class_exists('BarnetDefaultText') ? BarnetDefaultText::SAMPLE_ADD_A_SAMPLE : ''); ?>
                                            </h2>
                                        </div>
                                        <div class="component-form-group__input">
                                            <div class="component-form__item">
                                                <select class="form-control" name="quick_find" data-quick-find="product">
                                                    <option disabled selected value=""><?php _e('Select A Product');?></option>
                                                </select>
                                            </div>
                                            <?php if(is_user_logged_in()): ?>
                                            <div class="component-form__item">
                                              <select class="form-control" name="quick_find" data-quick-find="formula">
                                                <option disabled selected value=""><?php _e('Select A Formula');?></option>
                                              </select>
                                            </div>
                                            <?php endif;?>
                                        </div>
                                    </div>
                                    <div class="component-form__group text-center d-sm-none">
                                        <button class="btn btn-solid" data-next-btn type="submit"><?php _e('Submit Request');?></button>
                                    </div>
                                </div>
                            </div>
                            <input type="hidden" name="selected_sample" value="" data-selected-sample>
                            <input type="hidden" name="key_store" value="keyStore" data-key-store>
                        </form>
                    </div>
                </div>
            </div>
        </section>
    <?php else :?>
        <script type="text/javascript">
            if (typeof window.localStorage == 'object') {
                window.localStorage.removeItem('<?php echo $keyStore; ?>');
            }
        </script>
        <section class="component-form-request --confirmation --bg-transparent">
            <div class="container">
                <div class="component-form-request__wrapper" style="background-image: url(<?php echo get_template_directory_uri(); ?>/assets/images/bg-hexagon.png)">
                    <div class="component-form --center">
                        <div class="component-form__wrapper">
                            <div class="component-form__content">
                                <div class="component-form__group text-center">
                                    <div class="component-heading-group --dark-mode text-center">
                                        <h2 class="component-heading-group__heading --size-md"><?php _e('Thank You');?>
                                        </h2>
                                        <div class="component-heading-group__desc">
                                            <?php echo get_theme_mod('sample_thanks_text', class_exists('BarnetDefaultText') ? BarnetDefaultText::SAMPLE_THANKS_TEXT : ''); ?>
                                        </div>
                                    </div>
                                    <div class="component-form__btn"><a class="btn btn-solid --dark-mode" href="/" title="Ok">Ok</a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    <?php endif;?>
</main>
<?php get_footer(); ?>
<script>
    grecaptcha.ready(() => {
        grecaptcha.execute('<?php echo get_option('recaptcha_api_site_key');?>', { action: 'action' }).then(token => {
            const gtoken = document.querySelector('#gtoken');
            if(typeof gtoken !== null && gtoken ) {
                gtoken.value = token;
            }
        });
    });
</script>
<style type="text/css">
    .grecaptcha-badge {
        z-index: 1000;
    }
</style>
