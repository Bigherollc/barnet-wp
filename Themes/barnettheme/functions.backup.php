<?php
/*if (!isset($adminScriptManager) || !isset($wpScriptManager))
{
    include_once __DIR__ . "../../plugins/barnet-products/Common/ScriptManager.php";
}*/


/**
 * Implement the Custom Header feature.
 */
require get_template_directory() . '/inc/custom-header.php';

/**
 * Custom template tags for this theme.
 */
require get_template_directory() . '/inc/template-tags.php';

/**
 * Functions which enhance the theme by hooking into WordPress.
 */
require get_template_directory() . '/inc/template-functions.php';

/**
 * Enqueue scripts and styles.
 */
function scripts() {
	wp_enqueue_style( 'style', get_stylesheet_uri(), array(), _S_VERSION );
	wp_style_add_data( 'style', 'rtl', 'replace' );
	if ( is_singular() && comments_open() && get_option( 'thread_comments' ) ) {
		wp_enqueue_script( 'comment-reply' );
	}
}
add_action( 'wp_enqueue_scripts', 'scripts' );

if (isset($adminScriptManager) && isset($wpScriptManager)) {
    $adminScriptManager->add(
        get_template_directory_uri() . '/assets/css/admin.css',
        BarnetScriptManager::CSS_TYPE
    )->add(
        get_template_directory_uri() . '/assets/css/disable-taxonomy-root.css',
        BarnetScriptManager::CSS_TYPE
    )->add(
        get_template_directory_uri() . '/assets/css/bootstrap-duallistbox.css',
        BarnetScriptManager::CSS_TYPE,
        true
    )->add(
        get_template_directory_uri() . '/assets/js/mb-extends.js',
        BarnetScriptManager::JS_TYPE
    )->add(
        get_template_directory_uri() . '/assets/js/disable-taxonomy-root.js',
        BarnetScriptManager::JS_TYPE,
        true
    )->add(
        get_template_directory_uri() . '/assets/js/jquery.bootstrap-duallistbox.js',
        BarnetScriptManager::JS_TYPE
    )->add(
        get_template_directory_uri() . '/assets/js/apply_role_users.js',
        BarnetScriptManager::JS_TYPE,
        true
    );

    $wpScriptManager->add(
        get_template_directory_uri() . '/assets/css/styles.min.css',
        BarnetScriptManager::CSS_TYPE
    )->add(
        get_template_directory_uri() . '/assets/js/tmp.js',
        BarnetScriptManager::JS_TYPE,
        true
    )->add(
        get_template_directory_uri() . '/assets/js/scripts.min.js',
        BarnetScriptManager::JS_TYPE,
        true
    );

    $barnetUserLoggedIn = is_user_logged_in();

    $adminScriptManager->enqueue();
    $wpScriptManager->enqueue();
}

/**
 * Hook user profile update
 */
function update_extra_profile_fields($user_id)
{
    global $wpdb;
    if (current_user_can('edit_user', $user_id)) {
        $meta = get_user_meta($user_id, 'rich_editing');
        if (!empty($meta) && $meta[0] == 'false') {
            $sql = "UPDATE " . _get_meta_table('user') . " SET meta_value = '' WHERE user_id = " . $user_id . " AND meta_key like 'meta-box-order%'";
            $wpdb->query($sql);
        }
    }
}

/**
 * Hook theme option field
 */
function register($wp_customize)
{
    $wp_customize->add_setting(
        'site_information',
        array(
            'capability' => 'edit_theme_options',
            'default' => true,
        )
    );
    $wp_customize->add_setting(
        'company_address',
        array(
            'capability' => 'edit_theme_options',
            'default' => true,
        )
    );
    $wp_customize->add_setting(
        'company_phone',
        array(
            'capability' => 'edit_theme_options',
            'default' => true,
        )
    );
    // Add control for the "display_title_and_tagline" setting.
    $wp_customize->add_control(
        'site_information',
        array(
            'type' => 'textarea',
            'section' => 'title_tagline',
            'label' => esc_html__('Site Information', 'barnet'),
        )
    );
    $wp_customize->add_control(
        'company_address',
        array(
            'type' => 'textarea',
            'section' => 'title_tagline',
            'label' => esc_html__('Company Address', 'barnet'),
        )
    );
    $wp_customize->add_control(
        'company_phone',
        array(
            'type' => 'text',
            'section' => 'title_tagline',
            'label' => esc_html__('Company Phone', 'barnet'),
        )
    );



    $emailContactList = array();
    if (class_exists('Barnet')) {
        $emailContactList = Barnet::$EMAIL_CONTACT_LIST;
    }

    if (!empty($emailContactList)) {
        $wp_customize->add_section('email_contact_theme_section', array(
            'title' => esc_html__('Email Contacts Settings', 'barnet'),
            'priority' => 30
        ));

        foreach ($emailContactList as $k => $v) {
            $wp_customize->add_setting(
                $k,
                array(
                    'capability' => 'edit_theme_options',
                    'default' => '',
                )
            );
            $wp_customize->add_control(
                $k,
                array(
                    'type' => 'text',
                    'section' => 'email_contact_theme_section',
                    'label' => esc_html__($v, 'barnet'),
                )
            );
        }
    }

    $wp_customize->add_section('config_text_page_registration_section', array(
        'title' => esc_html__('Registration Text Settings', 'barnet'),
        'priority' => 40
    ));


    $arrTextConfig = array(
        'registration_title_text' => array(
            'label' => 'Title',
            'type' => 'text',
            'default' => class_exists('BarnetDefaultText') ? BarnetDefaultText::REGISTRATION_TITLE: ''
        ),
        'registration_thanks_title' => array(
            'label' => 'THANK YOU Title',
            'type' => 'text',
            'default' => class_exists('BarnetDefaultText') ? BarnetDefaultText::REGISTRATION_THANKS_TITLE : ''
        ),
        'registration_thanks_text' => array(
            'label' => 'THANK YOU Text',
            'type' => 'textarea',
            'default' => class_exists('BarnetDefaultText') ? BarnetDefaultText::REGISTRATION_THANKS_TEXT : ''
        ),
        'registration_thanks_ok' => array(
            'label' => 'OK Button',
            'type' => 'text',
            'default' => class_exists('BarnetDefaultText') ? BarnetDefaultText::REGISTRATION_THANKS_OK : ''
        ),
        'registration_header_text' => array(
            'label' => 'Header Text',
            'type' => 'textarea',
            'default' => class_exists('BarnetDefaultText') ? BarnetDefaultText::REGISTRATION_HEADER_TEXT : ''
        ),
        'registration_email_note' => array(
            'label' => 'Email Note',
            'type' => 'text',
            'default' => class_exists('BarnetDefaultText') ? BarnetDefaultText::REGISTRATION_EMAIL_NOTE : ''
        ),
        'registration_continue_text' => array(
            'label' => 'Continue Label',
            'type' => 'text',
            'default' => class_exists('BarnetDefaultText') ? BarnetDefaultText::REGISTRATION_CONTINUE : ''
        ),
        'registration_about_us_text' => array(
            'label' => 'About us Text',
            'type' => 'text',
            'default' => class_exists('BarnetDefaultText') ? BarnetDefaultText::REGISTRATION_ABOUT_US_TEXT : ''
        ),
        'registration_note_text' => array(
            'label' => 'Note Text Label',
            'type' => 'text',
            'default' => class_exists('BarnetDefaultText') ? BarnetDefaultText::REGISTRATION_NOTE_TEXT : ''
        ),
        'registration_newsletter_text' => array(
            'label' => 'Newsletter Label',
            'type' => 'text',
            'default' => class_exists('BarnetDefaultText') ? BarnetDefaultText::REGISTRATION_NEWSLETTER_TEXT : ''
        ),
    );
    foreach ($arrTextConfig as $k => $v) {
        $wp_customize->add_setting(
            $k,
            array(
                'capability' => 'edit_theme_options',
                'default' => $v['default'],
            )
        );
        $wp_customize->add_control(
            $k,
            array(
                'type' => $v['type'],
                'section' => 'config_text_page_registration_section',
                'label' => esc_html__($v['label'], 'barnet'),
            )
        );
    }

    $wp_customize->add_section('config_text_page_sample_section', array(
        'title' => esc_html__('Sample page Text Settings', 'barnet'),
        'priority' => 40
    ));


    $arrTextConfig = array(
        'sample_title_text' => array(
            'label' => 'Title text',
            'type' => 'text',
            'default' => class_exists('BarnetDefaultText') ? BarnetDefaultText::SAMPLE_TITLE_TEXT : ''
        ),
        'sample_thanks_text' => array(
            'label' => 'THANK YOU text',
            'type' => 'text',
            'default' => class_exists('BarnetDefaultText') ? BarnetDefaultText::SAMPLE_THANKS_TEXT : ''
        ),
        'sample_heading_line_1' => array(
            'label' => 'Heading line 1',
            'type' => 'textarea',
            'default' => class_exists('BarnetDefaultText') ? BarnetDefaultText::SAMPLE_HEADING_LINE_1 : ''
        ),
        'sample_heading_line_2' => array(
            'label' => 'Heading line 2',
            'type' => 'textarea',
            'default' => class_exists('BarnetDefaultText') ? BarnetDefaultText::SAMPLE_HEADING_LINE_2 : ''
        ),
        'sample_heading_note' => array(
            'label' => 'Note text',
            'type' => 'textarea',
            'default' => class_exists('BarnetDefaultText') ? BarnetDefaultText::SAMPLE_HEADING_NOTE : ''
        ),
        'sample_ship_to' => array(
            'label' => 'Ship To text',
            'type' => 'text',
            'default' => class_exists('BarnetDefaultText') ? BarnetDefaultText::SAMPLE_SHIP_TO : ''
        ),
        'sample_additional_note' => array(
            'label' => 'Additional Notes Title',
            'type' => 'text',
            'default' => class_exists('BarnetDefaultText') ? BarnetDefaultText::SAMPLE_ADDITIONAL_NOTE : ''
        ),
        'sample_additional_note_text' => array(
            'label' => 'Additional Notes Text',
            'type' => 'text',
            'default' => class_exists('BarnetDefaultText') ? BarnetDefaultText::SAMPLE_ADDITIONAL_NOTE_TEXT : ''
        ),
        'sample_selected_samples' => array(
            'label' => 'Selected Samples Text',
            'type' => 'text',
            'default' => class_exists('BarnetDefaultText') ? BarnetDefaultText::SAMPLE_SELECTED_SAMPLES : ''
        ),
        'sample_add_a_sample' => array(
            'label' => 'Add a Sample Text',
            'type' => 'text',
            'default' => class_exists('BarnetDefaultText') ? BarnetDefaultText::SAMPLE_ADD_A_SAMPLE : ''
        ),
        'sample_select_text' => array(
            'label' => 'Select Text',
            'type' => 'text',
            'default' => class_exists('BarnetDefaultText') ? BarnetDefaultText::SAMPLE_SELECT_TEXT : ''
        ),
    );
    foreach ($arrTextConfig as $k => $v) {
        $wp_customize->add_setting(
            $k,
            array(
                'capability' => 'edit_theme_options',
                'default' => $v['default'],
            )
        );
        $wp_customize->add_control(
            $k,
            array(
                'type' => $v['type'],
                'section' => 'config_text_page_sample_section',
                'label' => esc_html__($v['label'], 'barnet'),
            )
        );
    }

    $wp_customize->add_section('config_text_page_no_sample_section', array(
        'title' => esc_html__('No Sample page Text Settings', 'barnet'),
        'priority' => 40
    ));


    $arrTextConfig = array(
        'no_sample_title_text' => array(
            'label' => 'Title text',
            'type' => 'text',
            'default' => class_exists('BarnetDefaultText') ? BarnetDefaultText::NO_SAMPLE_TITLE_TEXT : ''
        ),
        'no_sample_heading_line_1' => array(
            'label' => 'Heading line 1',
            'type' => 'textarea',
            'default' => class_exists('BarnetDefaultText') ? BarnetDefaultText::NO_SAMPLE_HEADING_LINE_1 : ''
        ),
        'no_sample_heading_line_2' => array(
            'label' => 'Heading line 2',
            'type' => 'textarea',
            'default' => class_exists('BarnetDefaultText') ? BarnetDefaultText::NO_SAMPLE_HEADING_LINE_2 : ''
        ),
        'no_sample_heading_title' => array(
            'label' => 'Heading Title',
            'type' => 'text',
            'default' => class_exists('BarnetDefaultText') ? BarnetDefaultText::NO_SAMPLE_HEADING_TITLE: ''
        ),
        'no_sample_heading_note' => array(
            'label' => 'Heading Note',
            'type' => 'textarea',
            'default' => class_exists('BarnetDefaultText') ? BarnetDefaultText::NO_SAMPLE_HEADING_NOTE: ''
        ),
        'no_sample_add_a_sample' => array(
            'label' => 'Add a Sample Text',
            'type' => 'text',
            'default' => class_exists('BarnetDefaultText') ? BarnetDefaultText::NO_SAMPLE_ADD_A_SAMPLE : ''
        ),
    );
    foreach ($arrTextConfig as $k => $v) {
        $wp_customize->add_setting(
            $k,
            array(
                'capability' => 'edit_theme_options',
                'default' => $v['default'],
            )
        );
        $wp_customize->add_control(
            $k,
            array(
                'type' => $v['type'],
                'section' => 'config_text_page_no_sample_section',
                'label' => esc_html__($v['label'], 'barnet'),
            )
        );
    }

    $wp_customize->add_section('config_text_page_gic_section', array(
        'title' => esc_html__('GIC page Text Settings', 'barnet'),
        'priority' => 45
    ));


    $arrTextConfig = array(
        'gic_signin_title' => array(
            'label' => 'Sign In title',
            'type' => 'text',
            'default' => class_exists('BarnetDefaultText') ? BarnetDefaultText::GIC_SIGNIN_TITLE : ''
        ),
        'gic_new_customer' => array(
            'label' => 'New Customer Text',
            'type' => 'text',
            'default' => class_exists('BarnetDefaultText') ? BarnetDefaultText::GIC_NEW_CUSTOMER : ''
        ),
        'gic_registered_title' => array(
            'label' => 'Registered title',
            'type' => 'text',
            'default' => class_exists('BarnetDefaultText') ? BarnetDefaultText::GIC_REGISTER_TITLE : ''
        ),
        'gic_registered_text' => array(
            'label' => 'Registered Text',
            'type' => 'textarea',
            'default' => class_exists('BarnetDefaultText') ? BarnetDefaultText::GIC_REGISTER_TEXT : ''
        ),
    );
    foreach ($arrTextConfig as $k => $v) {
        $wp_customize->add_setting(
            $k,
            array(
                'capability' => 'edit_theme_options',
                'default' => $v['default'],
            )
        );
        $wp_customize->add_control(
            $k,
            array(
                'type' => $v['type'],
                'section' => 'config_text_page_gic_section',
                'label' => esc_html__($v['label'], 'barnet'),
            )
        );
    }

    $wp_customize->add_section('config_text_page_lab_day_section', array(
        'title' => esc_html__('Lab Day page Text Settings', 'barnet'),
        'priority' => 45
    ));


    $arrTextConfig = array(
        'lab_thanks_title' => array(
            'label' => 'THANK YOU Title',
            'type' => 'text',
            'default' => class_exists('BarnetDefaultText') ? BarnetDefaultText::LAB_THANKS_TITLE : ''
        ),
        'lab_thanks_text' => array(
            'label' => 'THANK YOU Text',
            'type' => 'textarea',
            'default' => class_exists('BarnetDefaultText') ? BarnetDefaultText::LAB_THANKS_TEXT : ''
        ),
        'lab_thanks_ok' => array(
            'label' => 'OK Button',
            'type' => 'text',
            'default' => class_exists('BarnetDefaultText') ? BarnetDefaultText::LAB_THANKS_OK : ''
        ),
        'lab_title' => array(
            'label' => 'Lab Day Request title',
            'type' => 'text',
            'default' => class_exists('BarnetDefaultText') ? BarnetDefaultText::LAB_TITLE : ''
        ),
        'lab_header_text' => array(
            'label' => 'Header Text',
            'type' => 'textarea',
            'default' => class_exists('BarnetDefaultText') ? BarnetDefaultText::LAB_HEADER_TEXT : ''
        ),
        'lab_half_day' => array(
            'label' => 'Half Day Text',
            'type' => 'text',
            'default' => class_exists('BarnetDefaultText') ? BarnetDefaultText::LAB_HALF_DAY : ''
        ),
        'lab_full_day' => array(
            'label' => 'Full Day Text',
            'type' => 'text',
            'default' => class_exists('BarnetDefaultText') ? BarnetDefaultText::LAB_FULL_DAY : ''
        ),
        'lab_desired_modules_note' => array(
            'label' => 'Desired Modules note',
            'type' => 'textarea',
            'default' => class_exists('BarnetDefaultText') ? BarnetDefaultText::LAB_DESIRED_MODULES_NOTE : ''
        ),
        'lab_location_note' => array(
            'label' => 'Location note',
            'type' => 'textarea',
            'default' => class_exists('BarnetDefaultText') ? BarnetDefaultText::LAB_LOCATION_NOTE : ''
        ),
    );
    foreach ($arrTextConfig as $k => $v) {
        $wp_customize->add_setting(
            $k,
            array(
                'capability' => 'edit_theme_options',
                'default' => $v['default'],
            )
        );
        $wp_customize->add_control(
            $k,
            array(
                'type' => $v['type'],
                'section' => 'config_text_page_lab_day_section',
                'label' => esc_html__($v['label'], 'barnet'),
            )
        );
    }

    $wp_customize->add_section('config_text_page_other_section', array(
        'title' => esc_html__('Other page Text Settings', 'barnet'),
        'priority' => 60
    ));


    $arrTextConfig = array(
        'request_training_text' => array(
            'label' => 'Request Training',
            'type' => 'text',
            'default' => class_exists('BarnetDefaultText') ? BarnetDefaultText::REQUEST_TRAINING_TEXT : ''
        ),
        'change_password_note' => array(
            'label' => 'Change Password Note',
            'type' => 'textarea',
            'default' => class_exists('BarnetDefaultText') ? BarnetDefaultText::CHANGE_PASSWORD_NOTE : ''
        ),
        'change_password_success_title' => array(
            'label' => 'Change Password Success Title',
            'type' => 'text',
            'default' => class_exists('BarnetDefaultText') ? BarnetDefaultText::CHANGE_PASSWORD_SUCCESS_TITLE : ''
        ),
        'change_password_success_text' => array(
            'label' => 'Change Password Success Text',
            'type' => 'textarea',
            'default' => class_exists('BarnetDefaultText') ? BarnetDefaultText::CHANGE_PASSWORD_SUCCESS_TEXT : ''
        ),
        'contact_success_title' => array(
            'label' => 'Contact Success Title',
            'type' => 'text',
            'default' => class_exists('BarnetDefaultText') ? BarnetDefaultText::CONTACT_SUCCESS_TITLE : ''
        ),
        'contact_success_text' => array(
            'label' => 'Contact Success Text',
            'type' => 'textarea',
            'default' => class_exists('BarnetDefaultText') ? BarnetDefaultText::CONTACT_SUCCESS_TEXT : ''
        ),
        'product_add_sample_text'=> array(
            'label' => 'Product Add Sample Text',
            'type' => 'textarea',
            'default' => class_exists('BarnetDefaultText') ? BarnetDefaultText::PRODUCT_ADD_SAMPLE_TEXT : ''
        ),
        'formula_add_sample_text'=> array(
            'label' => 'Formula Add Sample Text',
            'type' => 'textarea',
            'default' => class_exists('BarnetDefaultText') ? BarnetDefaultText::FORMULA_ADD_SAMPLE_TEXT : ''
        ),
    );
    foreach ($arrTextConfig as $k => $v) {
        $wp_customize->add_setting(
            $k,
            array(
                'capability' => 'edit_theme_options',
                'default' => $v['default'],
            )
        );
        $wp_customize->add_control(
            $k,
            array(
                'type' => $v['type'],
                'section' => 'config_text_page_other_section',
                'label' => esc_html__($v['label'], 'barnet'),
            )
        );
    }
}

function wp_custom_user_profile_fields($user)
{
    $userRegion = array();
    if (class_exists('BarnetProduct')) {
        $userRegion = BarnetProduct::$AREA_LIST;
    }
    echo '<hr><h3 class="heading">User Type (Region)</h3>';
    foreach ($userRegion as $key => $value) {
        $checked = '';
        if ($key == get_user_meta($user->ID, 'user_type', true)) {
            $checked = 'checked';
        }
        echo '<input type="radio" name="user_type" ' . $checked . ' value="' . $key . '">
        <label for="user_type">' . $value . '</label>
        ';
    }

    echo '<hr><h3 class="heading">Is User Tablet</h3>';

    $isUserTabletChecked = '';
    $valueIsUserTablet = get_user_meta($user->ID, 'is_user_tablet', true);
    if ($valueIsUserTablet) {
        $isUserTabletChecked = 'checked';
    }
    echo '<input type="checkbox" name="is_user_tablet" ' . $isUserTabletChecked . '>';

    // Newsletter
    $newsletter = "";
    $aboutUs = "";
    $userExtraInfo = get_user_meta($user->ID, 'user_extra_info', '');
    if (!empty($userExtraInfo) && is_array($userExtraInfo)) {
        $userExtraInfo = unserialize($userExtraInfo[0]);
        if (isset($userExtraInfo['newsletter'])) {
            $newsletter = $userExtraInfo['newsletter'];
        }
        if (isset($userExtraInfo['about_us'])) {
            $aboutUs = $userExtraInfo['about_us'];
        }
    }

    $company_name = isset($userExtraInfo["company_name"]) ? $userExtraInfo["company_name"] : '';
    $address = isset($userExtraInfo["address"]) ? $userExtraInfo["address"] : '';
    $addressOptional = isset($userExtraInfo["address_optional"]) ? $userExtraInfo["address_optional"] : '';
    $country = isset($userExtraInfo["country"]) ? $userExtraInfo["country"] : '';
    $province = isset($userExtraInfo["province"]) ? $userExtraInfo["province"] : '';
    $city = isset($userExtraInfo["city"]) ? $userExtraInfo["city"] : '';
    $postalCode = isset($userExtraInfo["postal_code"]) ? $userExtraInfo["postal_code"] : '';
    $phone = isset($userExtraInfo["phone"]) ? $userExtraInfo["phone"] : '';
    $phone_optional = isset($userExtraInfo["phone_optional"]) ? $userExtraInfo["phone_optional"] : '';
    $jobTitleRole = isset($userExtraInfo["job_title_role"]) ? $userExtraInfo["job_title_role"] : '';
    $note = isset($userExtraInfo["note"]) ? $userExtraInfo["note"] : '';

    //about us
    echo '<hr><h3 class="heading">Account Info:</h3>';

    echo '<table class="form-table" role="presentation"><tbody>';
    if ($company_name != '') {
        echo '<tr>';
        echo '<th><label>Company Name</label></th>';
        echo '<td><input class="regular-text" type="text" name="company_name" value="' . $company_name . '"></td>';
        echo '</tr>';
    } else {
        echo '<tr>';
        echo '<th><label>Company Name</label></th>';
        echo '<td><input class="regular-text" type="text" name="company_name"></td>';
        echo '</tr>';
    }
    if ($address != '') {
        echo '<tr>';
        echo '<th><label>Address</label></th>';
        echo '<td><input class="regular-text" type="text" name="address" value="' . $address . '"></td>';
        echo '</tr>';
    } else {
        echo '<tr>';
        echo '<th><label>Address</label></th>';
        echo '<td><input class="regular-text" type="text" name="address"></td>';
        echo '</tr>';  
    }
    if ($addressOptional != '') {
        echo '<tr>';
        echo '<th><label>Address Line 2</label></th>';
        echo '<td><input class="regular-text" type="text" name="address_optional" value="' . $addressOptional . '"></td>';
        echo '</tr>';
    } else {
        echo '<tr>';
        echo '<th><label>Address Line 2</label></th>';
        echo '<td><input class="regular-text" type="text" name="address_optional"></td>';
        echo '</tr>';
    }
    if ($province != '') {
        echo '<tr>';
        echo '<th><label>State/Province</label></th>';
        echo '<td><input class="regular-text" type="text" name="province" value="' . $province . '"></td>';
        echo '</tr>';
    } else {
        echo '<tr>';
        echo '<th><label>State/Province</label></th>';
        echo '<td><input class="regular-text" type="text" name="province"></td>';
        echo '</tr>'; 
    }
    if ($postalCode != '') {
        echo '<tr>';
        echo '<th><label>Postal Code</label></th>';
        echo '<td><input class="regular-text" type="text" name="postal_code" value="' . $postalCode . '"></td>';
        echo '</tr>';
    } else {
        echo '<tr>';
        echo '<th><label>Postal Code</label></th>';
        echo '<td><input class="regular-text" type="text" name="postal_code"></td>';
        echo '</tr>';  
    }
    if ($city != '') {
        echo '<tr>';
        echo '<th><label>City</label></th>';
        echo '<td><input class="regular-text" type="text" name="city" value="' . $city . '"></td>';
        echo '</tr>';
    } else {
        echo '<tr>';
        echo '<th><label>City</label></th>';
        echo '<td><input class="regular-text" type="text" name="city"></td>';
        echo '</tr>';
    }
    if ($country != '') {
        echo '<tr>';
        echo '<th><label>Country</label></th>';
        echo '<td>';
        echo '<select name="country">';
        $getListCountry = Barnet::getListCountries();
        foreach ($getListCountry as $k => $v) {
            printf(
                '<option value="%s"%s>%s</option>',
                esc_attr($k),
                selected($country, $k, false),
                esc_html(__($v))
            );
        }
        echo '</select>';
        echo '</td></tr>';
    } else {
        echo '<tr>';
        echo '<th><label>Country</label></th>';
        echo '<td>';
        echo '<select name="country">';
        echo '<option disabled selected value="Country">Country</option>';
        $getListCountry = Barnet::getListCountries();
        foreach ($getListCountry as $k => $v) {
            echo '<option value="' . $v . '">' . $v . '</option>';
        }
        echo '</select>';
        echo '</td></tr>';
    }
    if ($phone != '') {
        echo '<tr>';
        echo '<th><label>Work Phone</label></th>';
        echo '<td><input class="regular-text" type="text" name="phone" value="' . $phone . '"></td>';
        echo '</tr>';
    } else {
        echo '<tr>';
        echo '<th><label>Work Phone</label></th>';
        echo '<td><input class="regular-text" type="text" name="phone"></td>';
        echo '</tr>';
    }
    if ($phone_optional != '') {
        echo '<tr>';
        echo '<th><label>Mobile Phone</label></th>';
        echo '<td><input class="regular-text" type="text" name="phone_optional" value="' . $phone_optional . '"></td>';
        echo '</tr>';
    } else {
        echo '<tr>';
        echo '<th><label>Mobile Phone</label></th>';
        echo '<td><input class="regular-text" type="text" name="phone_optional"></td>';
        echo '</tr>';
    }
    if ($jobTitleRole != '') {
        echo '<tr>';
        echo '<th><label>Job Title/Role</label></th>';
        echo '<td><input class="regular-text" type="text" name="job_title_role" value="' . $jobTitleRole . '"></td>';
        echo '</tr>';
    } else {
        echo '<tr>';
        echo '<th><label>Job Title/Role</label></th>';
        echo '<td><input class="regular-text" type="text" name="job_title_role"></td>';
        echo '</tr>';
    }
    if ($aboutUs != '') {
        echo '<tr>';
        echo '<th><label>About Us</label></th>';
        echo '<td>';
        echo '<select name="about_us">';
        $valueAboutUs = class_exists('BarnetDefaultText') ? BarnetDefaultText::REGISTRATION_ABOUT_US_VALUE : array();
        foreach ($valueAboutUs as $v) {
            printf(
                '<option value="%s"%s>%s</option>',
                esc_attr($v),
                selected($aboutUs, $v, false),
                esc_html(__($v))
            );
        }
        echo '</select>';
        echo '</td></tr>';
    } else {
        echo '<tr>';
        echo '<th><label>About Us</label></th>';
        echo '<td>';
        echo '<select name="about_us">';
        echo '<option disabled selected value="">' . get_theme_mod('registration_about_us_text', class_exists('BarnetDefaultText') ? BarnetDefaultText::REGISTRATION_ABOUT_US_TEXT : '') . '</option>';
        $valueAboutUs = class_exists('BarnetDefaultText') ? BarnetDefaultText::REGISTRATION_ABOUT_US_VALUE : array();
        foreach ($valueAboutUs as $v) {
            echo '<option value="' . $v . '">' . $v . '</option>';
        }
        echo '</select>';
        echo '</td></tr>';
    }
    if ($note != '') {
        echo '<tr>';
        echo '<th><label>Optional Note</label></th>';
        echo '<td><textarea class="form-control --dark-mode" type="text" name="note">' . $note . '</textarea></td>';
        echo '</tr>';
    } else {
        echo '<tr>';
        echo '<th><label>Optional Note</label></th>';
        echo '<td><textarea class="form-control --dark-mode" type="text" name="note" placeholder="Optional Note"></textarea></td>';
        echo '</tr>';
    }
    echo '</tbody></table>';

    if ($newsletter == 'on') {
        $newsletter = 'checked';
    }
    echo '<hr><h3 class="heading">Is Newsletter</h3>';
    echo '<input type="checkbox" name="newsletter" ' . $newsletter . '>';
}

function wp_update_user_register($userId)
{
    update_user_meta($userId, 'user_type', 'global');
    update_user_meta($userId, 'is_user_tablet', null);
}

function wp_update_user_profile_fields($userId)
{
    //var_dump( get_user_meta($userId, 'user_type', true) );die;
    update_user_meta($userId, 'user_type', $_REQUEST['user_type']);
    update_user_meta($userId, 'is_user_tablet', isset($_REQUEST['is_user_tablet']) ? $_REQUEST['is_user_tablet'] : '');
    $newsletter = isset($_REQUEST['newsletter']) ?$_REQUEST['newsletter'] : '';
    if ($newsletter != 'on') {
        $newsletter = "off";
    }

    $userExtraInfo = array();
    $userExtraInfoGet = get_user_meta($userId, 'user_extra_info', '');
    if (!empty($userExtraInfoGet) && is_array($userExtraInfoGet)) {
        $userExtraInfo = unserialize($userExtraInfoGet[0]);
        $userExtraInfo['company_name'] = $_REQUEST['company_name'];
        $userExtraInfo['address'] = $_REQUEST['address'];
        $userExtraInfo['address_optional'] = $_REQUEST['address_optional'];
        $userExtraInfo['country'] = $_REQUEST['country'];
        $userExtraInfo['city'] = $_REQUEST['city'];
        $userExtraInfo['province'] = $_REQUEST['province'];
        $userExtraInfo['postal_code'] = $_REQUEST['postal_code'];
        $userExtraInfo['phone'] = $_REQUEST['phone'];
        $userExtraInfo['phone_optional'] = $_REQUEST['phone_optional'];
        $userExtraInfo['job_title_role'] = $_REQUEST['job_title_role'];
        $userExtraInfo['about_us'] = $_REQUEST['about_us'];
        $userExtraInfo['note'] = $_REQUEST['note'];
        $userExtraInfo['newsletter'] = $newsletter;
    }
    update_user_meta($userId, 'user_extra_info', serialize($userExtraInfo));
}

/**
 * Hook list define
 */
add_action('profile_update', 'update_extra_profile_fields');
add_action('customize_register', 'register');
add_action('user_register', 'wp_update_user_register');
add_action('show_user_profile', 'wp_custom_user_profile_fields');
add_action('edit_user_profile', 'wp_custom_user_profile_fields');
add_action('edit_user_profile_update', 'wp_update_user_profile_fields');
add_action('personal_options_update', 'wp_update_user_profile_fields' );

register_nav_menus(
    array(
        'primary' => esc_html__('main menu', 'barnet'),
        'second' => __('second menu', 'barnet'),
    )
);

if (function_exists("tml_register_action")) {
    tml_register_action('updateprofile', array(
        'title' => __('Update Profile'),
        'slug' => 'update-profile',
        'callback' => 'tml_dashboard_handler',
        'show_on_forms' => false,
        'show_nav_menu_item' => false,
    ));

    tml_register_action('changepassword', array(
        'title' => __('Change Password'),
        'slug' => 'change-password',
        'callback' => 'tml_dashboard_handler',
        'show_on_forms' => false,
        'show_nav_menu_item' => false,
    ));

    /*tml_register_action('setpassword', array(
        'title' => __('Set Password'),
        'slug' => 'set-password',
        'show_on_forms' => false,
        'show_nav_menu_item' => false,
    ));*/
}

if (is_admin()) {
    // Hide Admin Color Scheme option
    remove_action("admin_color_scheme_picker", "admin_color_scheme_picker");
    // Hide Profile Picture option
    add_filter('option_show_avatars', '__return_false');
    // Hide Biographical Info, Keyboard Shortcuts option
    function remove_extra_field_profile()
    {
        $current_file_url = preg_replace("#\?.*#", "", basename($_SERVER['REQUEST_URI']));

        if ($current_file_url == "profile.php") {
            add_action('wp_loaded', function () {
                ob_start("profile_callback");
            });
            add_action('shutdown', function () {
                @ob_end_flush();
            });
        }
    }

    add_action('init', 'remove_extra_field_profile');

    function profile_callback($html)
    {
        // remove the headline
        $headline = __(IS_PROFILE_PAGE ? 'About Yourself' : 'About the user');
        $replacement = 'About The Author Box';
        $html = str_replace('<h3>' . $headline . '</h3>', '<h3>' . $replacement . '</h3>', $html);

        // remove the table row
        $html = preg_replace('~<tr class="user-comment-shortcuts-wrap">\s*<th scope="row">Keyboard Shortcuts</th>\s*<td>\s*<label for="comment_shortcuts".*</tr>~imsUu', '', $html);
        $html = preg_replace('~<tr class="user-description-wrap">\s*<th><label for="description".*</tr>~imsUu', '', $html);
        $html = str_replace('<h2>About Yourself</h2>', '', $html);
        return $html;
    }
}

//check version
add_action( 'admin_bar_menu', 'customize_admin_bar' );
function customize_admin_bar()
{
    global $wp_admin_bar;
    $stat = stat(get_template_directory());
    $fulldate = date(' (M d Y)', $stat['mtime']);
    $month = date('m', $stat['mtime']);
    $day = date('d', $stat['mtime']);
    $wp_admin_bar->add_menu( array(
        'id' => 'version-menu',
        'title' => 'version : '.($month-5).'.'.$day.$fulldate,
        'href' => false
    ) );
}

function bn_disable_admin_bar() {
    if (current_user_can('administrator')) {
        // user can view admin bar
        //show_admin_bar(true); // this line isn't essentially needed by default...
    } else {
        // hide admin bar
        show_admin_bar(false);
    }
}
add_action('after_setup_theme', 'bn_disable_admin_bar');

add_action( 'widgets_init', 'my_register_sidebars' );
function my_register_sidebars() {
	/* Register the 'primary' sidebar. */
	register_sidebar(
		array(
			'id'            => 'primary',
			'name'          => __( 'Primary Sidebar' ),
			'description'   => __( 'A short description of the sidebar.' ),
			'before_widget' => '<div id="%1$s" class="widget %2$s">',
			'after_widget'  => '</div>',
			'before_title'  => '<h3 class="widget-title">',
			'after_title'   => '</h3>',
		)
	);
	/* Repeat register_sidebar() code for additional sidebars. */
}

function themename_widgets_init() {
	register_sidebar( array(
		'name'          => __( 'Primary Sidebar', 'theme_name' ),
		'id'            => 'sidebar-1',
		'before_widget' => '<aside id="%1$s" class="widget %2$s">',
		'after_widget'  => '</aside>',
		'before_title'  => '<h3 class="widget-title">',
		'after_title'   => '</h3>',
	) );
	register_sidebar( array(
		'name'          => __( 'Secondary Sidebar', 'theme_name' ),
		'id'            => 'sidebar-2',
		'before_widget' => '<ul><li id="%1$s" class="widget %2$s">',
		'after_widget'  => '</li></ul>',
		'before_title'  => '<h3 class="widget-title">',
		'after_title'   => '</h3>',
	) );
}
function barnetThemePasswordChangeEmail($pass_change_email, $user, $userdata){
    //do your changes here
    if (isset($_POST['pass1']) && trim($_POST['pass1']) != "") {
        $pass_change_email['subject'] = __('[%s] Welcome to Barnet Products');
        $pass_change_email['message'] = __(
            '<p>Hello ###FULLNAME###,</p>
<p>Thank you for your interest in Barnet Products. We are pleased to welcome you to our website!</p>
<p>Please log in today to access and explore:</p>
<ul>
<li>Product Literature</li>
<li>Concepts and Collections</li>
<li>Starting Formulas</li>
<li>Submission of Sample Requests</li>
</ul>
<p>Please use the information below to access the site:</p>
<p>Email: ###EMAIL###<br/>Password:  ###PASSWORD###
</p>
<p>Click <a href="###SITEURL###/login/">here</a> to login.</p>
<p>Barnetproducts.com is intended to supplement the visits and information you receive from your Barnet Sales Representative. As always, we encourage you to contact your Sales Representative with any questions that you may have.</p>
<p>Please take a moment to update your profile to make sure that the contact information we have on file for you is accurate.</p>
<p>Thank you for your continued interest in Barnet Products!</p>
<p>Kind Regards,</p>
<p>Hillary A. Phillis<br/>
Marketing Director</p>'
        );
        $pass_change_email['message'] = str_replace('###PASSWORD###', trim($_POST['pass1']), $pass_change_email['message']);
        $pass_change_email['message'] = str_replace('###FULLNAME###', trim($userdata['first_name'] . ' '. $userdata['last_name']), $pass_change_email['message']);
    }
    return $pass_change_email;
}
add_filter("password_change_email", "barnetThemePasswordChangeEmail", 10, 3);
