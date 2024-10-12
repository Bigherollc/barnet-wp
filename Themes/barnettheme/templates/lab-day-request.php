<?php
/* Template Name: Lab Day Request */

global $post;
$pageId = $post->ID;

$pageTitle = $post->post_title;
$backgroundImage = get_post_meta($pageId, 'p_background_image', true);

$mess = "";
$showRequestSuccess = false;

$current_user = wp_get_current_user();
$userExtraInfo = array();

$firstName = '';
$lastName = '';
$userEmail = '';
$note = '';

if ($current_user && $current_user->ID > 0) {
    $userInfo = $current_user->user_extra_info;
    if (!empty($userInfo)) {
        $userExtraInfo = unserialize($userInfo);
    }

    $firstName = $current_user->first_name;
    $lastName = $current_user->last_name;
    $userEmail = $current_user->user_email;
    $note = $current_user->description;

}

$companyName = isset($userExtraInfo["company_name"]) ? $userExtraInfo["company_name"] : '';
$address = isset($userExtraInfo["address"]) ? $userExtraInfo["address"] : '';
$addressOptional = isset($userExtraInfo["address_optional"]) ? $userExtraInfo["address_optional"] : '';
$country = isset($userExtraInfo["country"]) ? $userExtraInfo["country"] : '';
$province = isset($userExtraInfo["province"]) ? $userExtraInfo["province"] : '';
$postalCode = isset($userExtraInfo["postal_code"]) ? $userExtraInfo["postal_code"] : '';
$phone = isset($userExtraInfo["phone"]) ? $userExtraInfo["phone"] : '';
$phoneOptional = isset($userExtraInfo["phone_optional"]) ? $userExtraInfo["phone_optional"] : '';

if (isset($_REQUEST['checkemail']) && trim($_REQUEST['checkemail']) == "confirm") {
    $showRequestSuccess = true;
}

if (isset($_POST["duration"])) {

    $duration = isset($_POST['duration']) ? $_POST['duration'] : '';
    $desired_date = isset($_POST['desired_date']) ? $_POST['desired_date'] : '';
    $location = isset($_POST['location']) ? $_POST['location'] : '';
    $desired_module_1 = isset($_POST['select_day_1']) ? ($_POST['select_day_1']) : '';
    $desired_module_2 = isset($_POST['select_day_2']) ? ($_POST['select_day_2']) : '';
    $location_text = '';

    if ($location == 'location_1') {
        $location_text = 'At the Global Innovation Center';
    } elseif ($location == 'location_2') {
        $location_text = 'Off-site';
    } else {
        $location_text = 'Virtual';
    }

    if (empty($mess)) {
        $emailAdmin = get_option('admin_email');
        $wpMailOption = get_option('wp_mail_smtp');

        if (is_array($wpMailOption) && isset($wpMailOption["mail"]) && isset($wpMailOption["mail"]["from_email"])) {
            $emailAdmin = trim($wpMailOption["mail"]["from_email"]);
        }

        if ($emailAdmin != "") {
            $message = __('Lab Day Request:') . "\r\n\r\n";
            $message .= sprintf(__('Duration: %s'),
                    $duration == 'duration_1' ? __('Half Day') : __('Full Day')) . "\r\n\r\n";
            $message .= sprintf(__('Desired Date: %s'), $desired_date) . "\r\n\r\n";
            $message .= sprintf(__('Desired Module 1: %s'),
                    (!empty($desired_module_1)) ? $desired_module_1 : '') . "\r\n\r\n";
            $message .= sprintf(__('Desired Module 2: %s'),
                    (!empty($desired_module_2)) ? $desired_module_2 : '') . "\r\n\r\n";
            $message .= sprintf(__('Location: %s'), __($location_text)) . "\r\n\r\n";
            $title = sprintf(__('Customer Request Lab Day'));

            if (is_user_logged_in()) {
                if ($message && !wp_mail($emailAdmin, wp_specialchars_decode($title), $message)) {
                    //error sendmail
                    $mess = "Your request is not completed";
                }

                $message .= '------------------------------------------------------------------------------------' . "\r\n\r\n";
                $message .= __('Account Info:') . "\r\n\r\n";
                $message .= sprintf(__('Email: %s'), $userEmail) . "\r\n\r\n";
                $message .= sprintf(__('First Name: %s'), $firstName) . "\r\n\r\n";
                $message .= sprintf(__('Last Name: %s'), $lastName) . "\r\n\r\n";
                $message .= sprintf(__('Phone: %s'), $phone) . "\r\n\r\n";
                $message .= sprintf(__('Phone Optional: %s'), $phoneOptional) . "\r\n\r\n";
                $message .= sprintf(__('Address: %s'), $address) . "\r\n\r\n";
                $message .= sprintf(__('Address Optional: %s'), $addressOptional) . "\r\n\r\n";
                $message .= sprintf(__('Country: %s'), $country) . "\r\n\r\n";
                $message .= sprintf(__('Province: %s'), $province) . "\r\n\r\n";
                $message .= sprintf(__('Postal Code: %s'), $postalCode) . "\r\n\r\n";

                if (class_exists('BarnetLabRequest')) {
                    $LabDayRequest = new BarnetLabRequest();
                    $LabDayRequest->insertData($userEmail, $message);
                }
            } else {
                $mess = 'Please login before sending request';
            }
        }
    }

    if (empty($mess)) {
        global $wp;
        $requestGet = array();

        $current_url = site_url(add_query_arg(array(), "/" . $wp->request . "/"));
        $redirect_to = add_query_arg("checkemail", "confirm", $current_url);

        $message = sprintf(__('Hi: %s'), $userEmail) . "\r\n\r\n";
        $message .= __('Your request is successfully completed. We will be contacting you soon') . "\r\n\r\n";
        $message .= __('Thanks') . "\r\n\r\n";
        $message .= __('Barnet Team') . "\r\n\r\n";


        $title = sprintf(__('Lab Day Request'));
        if ($message && !wp_mail($userEmail, wp_specialchars_decode($title), $message)) {
            //error sendmail
        }
        wp_redirect($redirect_to);
        exit();
    }
}

get_header();
?>

<main role="main">
    <?php if (!$showRequestSuccess) : ?>
        <section
                class="component-lab-day-request component-form-hero" <?php if (!empty($backgroundImage)): ?> style="background-image:  url('<?php echo wp_get_attachment_url($backgroundImage); ?>')" <?php else: ?> style="background-image:  url('<?php echo get_stylesheet_directory_uri() . '/assets/images/bg-sign-in.png'; ?>')"  <?php endif; ?>
                data-url="" data-method="POST" data-request-step>
            <div class="component-form --right">

                <div class="component-form__wrapper">
                    <div class="component-heading-group --dark-mode text-center" data-heading>
                        <h2 class="component-heading-group__heading --size-lg to-uppercase">
                            <?php echo get_theme_mod('lab_title', class_exists('BarnetDefaultText') ? BarnetDefaultText::LAB_TITLE : ''); ?>
                        </h2>
                    </div>
                    <ul class="component-progress-step" data-progress-step="">
                        <li class="active">
                        </li>
                        <li>
                        </li>
                    </ul>
                    <form class="component-form__form" method="POST" data-parsley-errors-messages-disabled
                          data-parsley-validate>
                        <div class="component-form__content" data-tab-content>
                            <div class="component-form__group">
                                <div class="component-form__note">
                                    <?php echo get_theme_mod('lab_header_text', class_exists('BarnetDefaultText') ? BarnetDefaultText::LAB_HEADER_TEXT : ''); ?>
                                </div>
                                <?php if (!empty($mess)) : ?>
                                    <ul class="parsley-errors-list filled" data-validate-server="">
                                        <li>
                                            <?php echo $mess; ?>
                                        </li>
                                    </ul>
                                <?php endif; ?>
                                <div class="component-heading-form --size-sm">
                                    <h2 class="component-heading-form__heading"><?php _e('Duration'); ?>:
                                    </h2>
                                </div>
                                <div class="component-form-group__input checkbox-row">
                                    <div class="component-form__item">
                                        <div class="form-radio --dark-mode">
                                            <input type="radio" id="duration_1" value="duration_1" name="duration"
                                                   checked>
                                            <label for="duration_1">
                                                <?php echo get_theme_mod('lab_half_day', class_exists('BarnetDefaultText') ? BarnetDefaultText::LAB_HALF_DAY : ''); ?>
                                                <span></span>
                                            </label>
                                        </div>
                                    </div>
                                    <div class="component-form__item">
                                        <div class="form-radio --dark-mode">
                                            <input type="radio" id="duration_2" value="duration_2" name="duration">
                                            <label for="duration_2">
                                                <?php echo get_theme_mod('lab_full_day', class_exists('BarnetDefaultText') ? BarnetDefaultText::LAB_FULL_DAY : ''); ?>
                                                <span></span>
                                            </label>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="component-form__group">
                                <div class="component-heading-form --size-sm">
                                    <h2 class="component-heading-form__heading"><?php _e('Desired Date:'); ?>
                                    </h2>
                                </div>
                                <div class="custom-datepicker" data-calendar>
                                    <input type="hidden" name="desired_date" id="desired_date">
                                </div>
                            </div>
                            <div class="component-form__group text-center">
                                <button class="btn btn-solid --dark-mode" data-next-btn data-step-valid
                                        type="button"><?php _e('Continue'); ?></button>
                            </div>
                        </div>
                        <div class="component-form__content d-none" data-tab-content>
                            <div class="component-form__group">
                                <div class="component-heading-form --size-sm">
                                    <h2 class="component-heading-form__heading"><?php _e('Desired Modules:'); ?>
                                    </h2>
                                </div>
                                <div class="component-form__note">
                                    <?php echo get_theme_mod('lab_desired_modules_note', class_exists('BarnetDefaultText') ? BarnetDefaultText::LAB_DESIRED_MODULES_NOTE : ''); ?>
                                </div>
                                <div class="component-form-group__input">
                                    <?php the_content(); ?>
                                </div>
                            </div>
                            <div class="component-form__group">
                                <div class="component-heading-form --size-sm">
                                    <h2 class="component-heading-form__heading"><?php _e('Location: '); ?>
                                    </h2>
                                </div>
                                <div class="alert alert-warning">
                                    <?php echo get_theme_mod('lab_location_note', class_exists('BarnetDefaultText') ? BarnetDefaultText::LAB_LOCATION_NOTE : ''); ?>
                                </div>
                                <div class="component-form-group__input --mg-left-sm">
                                    <div class="component-form__item">
                                        <div class="form-radio --dark-mode --glow-mode">
                                            <input type="radio" id="location_1" value="location_1" name="location">
                                            <label for="location_1"><?php _e('At the Global Innovation Center'); ?>
                                                <span></span>
                                            </label>
                                        </div>
                                    </div>
                                    <div class="component-form__item">
                                        <div class="form-radio --dark-mode --glow-mode">
                                            <input type="radio" id="location_2" value="location_2" name="location">
                                            <label for="location_2"><?php _e('Off-site'); ?>
                                                <span></span>
                                            </label>
                                        </div>
                                    </div>
                                    <div class="component-form__item">
                                        <div class="form-radio --dark-mode --glow-mode">
                                            <input type="radio" id="location_3" value="location_3" name="location"
                                                   checked>
                                            <label for="location_3"><?php _e('Virtual'); ?>
                                                <span></span>
                                            </label>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="component-form__group text-center">
                                <button class="btn btn-solid --dark-mode" data-next-btn
                                        type="submit"><?php _e('Submit Request'); ?></button>
                            </div>
                        </div>
                    </form>
                </div>
        </section>
    <?php else : ?>
        <section class="component-form-request --confirmation --bg-transparent">
            <div class="container">
                <div class="component-form-request__wrapper"
                     style="background-image: url('<?php echo get_stylesheet_directory_uri() . '/assets/images/bg-hexagon.png'; ?>')">
                    <div class="component-form --center">
                        <div class="component-form__wrapper">
                            <div class="component-form__content">
                                <div class="component-form__group text-center">
                                    <div class="component-heading-group --dark-mode text-center">
                                        <h2 class="component-heading-group__heading --size-md">
                                            <?php echo get_theme_mod('lab_thanks_title', class_exists('BarnetDefaultText') ? BarnetDefaultText::LAB_THANKS_TITLE : ''); ?>
                                        </h2>
                                        <div class="component-heading-group__desc">
                                            <?php echo get_theme_mod('lab_thanks_text', class_exists('BarnetDefaultText') ? BarnetDefaultText::LAB_THANKS_TEXT : ''); ?>
                                        </div>
                                    </div>
                                    <?php
                                    $valueOk = get_theme_mod('lab_thanks_ok', class_exists('BarnetDefaultText') ? BarnetDefaultText::LAB_THANKS_OK : '');
                                    ?>
                                    <div class="component-form__btn">
                                        <a class="btn btn-solid --dark-mode" href="lab-day-request" title="<?php echo esc_html($valueOk) ?>">
                                            <?php echo $valueOk; ?>
                                        </a>
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
