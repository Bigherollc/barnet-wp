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
function scripts()
{
	wp_enqueue_style('style', get_stylesheet_uri(), array(), '1.1.1');
	wp_style_add_data('style', 'rtl', 'replace');
	if (is_singular() && comments_open() && get_option('thread_comments')) {
		wp_enqueue_script('comment-reply');
	}
}
add_action('wp_enqueue_scripts', 'scripts');

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
	)
		
		->add(
			get_template_directory_uri() . '/assets/css/cta.css',
			BarnetScriptManager::CSS_TYPE
		)
		->add(
			get_template_directory_uri() . '/assets/js/tmp.js',
			BarnetScriptManager::JS_TYPE,
			true
		)
		//Thanh N added
		->add(
			get_template_directory_uri() . '/assets/js/main.js',
			BarnetScriptManager::JS_TYPE,
			true
		)
		//Thanh N added
		->add(
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
			'default' => class_exists('BarnetDefaultText') ? BarnetDefaultText::REGISTRATION_TITLE : ''
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
			'default' => class_exists('BarnetDefaultText') ? BarnetDefaultText::NO_SAMPLE_HEADING_TITLE : ''
		),
		'no_sample_heading_note' => array(
			'label' => 'Heading Note',
			'type' => 'textarea',
			'default' => class_exists('BarnetDefaultText') ? BarnetDefaultText::NO_SAMPLE_HEADING_NOTE : ''
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
		'product_add_sample_text' => array(
			'label' => 'Product Add Sample Text',
			'type' => 'textarea',
			'default' => class_exists('BarnetDefaultText') ? BarnetDefaultText::PRODUCT_ADD_SAMPLE_TEXT : ''
		),
		'formula_add_sample_text' => array(
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
	$newsletter = get_user_meta($user->ID, 'newsletter', true);
        $aboutUs = '';
        $userExtraInfo = get_user_meta($user->ID, 'user_extra_info', '');
        if (!empty($userExtraInfo) && is_array($userExtraInfo)) {
            $userExtraInfo = unserialize($userExtraInfo[0]);
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
	$newsletter = isset($_REQUEST['newsletter']) ? $_REQUEST['newsletter'] : '';
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
		//$userExtraInfo['newsletter'] = $newsletter;
	}
	update_user_meta($userId, 'user_extra_info', serialize($userExtraInfo));
	update_user_meta($userId, 'newsletter', $newsletter);
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
add_action('personal_options_update', 'wp_update_user_profile_fields');

register_nav_menus(
	array(
		'primary' => esc_html__('main menu', 'barnet'),
		'mobile' => __('mobile menu', 'barnet'),
		'footer' => __('footer menu', 'barnet'),
		'second' => __('second menu', 'barnet'),
		'menu_topbar' => __('TopBar menu', 'barnet'),
		'menu_topbar_mobie' => __('TopBar menu mobile', 'barnet'),
		'menu_topbar_mobile' => __('TopBar menu mobile', 'barnet'),
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
add_action('admin_bar_menu', 'customize_admin_bar');
function customize_admin_bar()
{
	global $wp_admin_bar;
	$stat = stat(get_template_directory());
	$fulldate = date(' (M d Y)', $stat['mtime']);
	$month = date('m', $stat['mtime']);
	$day = date('d', $stat['mtime']);
	$wp_admin_bar->add_menu(array(
		'id' => 'version-menu',
		'title' => 'version : ' . ($month - 5) . '.' . $day . $fulldate,
		'href' => false
	));
}

function bn_disable_admin_bar()
{
	if (current_user_can('administrator')) {
		// user can view admin bar
		//show_admin_bar(true); // this line isn't essentially needed by default...
	} else {
		// hide admin bar
		show_admin_bar(false);
	}
}
add_action('after_setup_theme', 'bn_disable_admin_bar');

add_action('widgets_init', 'my_register_sidebars');
function my_register_sidebars()
{
	/* Register the 'primary' sidebar. */
	register_sidebar(
		array(
			'id'            => 'primary',
			'name'          => __('Primary Sidebar'),
			'description'   => __('A short description of the sidebar.'),
			'before_widget' => '<div id="%1$s" class="widget %2$s">',
			'after_widget'  => '</div>',
			'before_title'  => '<h3 class="widget-title">',
			'after_title'   => '</h3>',
		)
	);
	/* Repeat register_sidebar() code for additional sidebars. */
}

function themename_widgets_init()
{
	register_sidebar(array(
		'name'          => __('Primary Sidebar', 'theme_name'),
		'id'            => 'sidebar-1',
		'before_widget' => '<aside id="%1$s" class="widget %2$s">',
		'after_widget'  => '</aside>',
		'before_title'  => '<h3 class="widget-title">',
		'after_title'   => '</h3>',
	));
	register_sidebar(array(
		'name'          => __('Secondary Sidebar', 'theme_name'),
		'id'            => 'sidebar-2',
		'before_widget' => '<ul><li id="%1$s" class="widget %2$s">',
		'after_widget'  => '</li></ul>',
		'before_title'  => '<h3 class="widget-title">',
		'after_title'   => '</h3>',
	));
}
function barnetThemePasswordChangeEmail($pass_change_email, $user, $userdata)
{
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
		$pass_change_email['message'] = str_replace('###FULLNAME###', trim($userdata['first_name'] . ' ' . $userdata['last_name']), $pass_change_email['message']);
	}
	return $pass_change_email;
}
add_filter("password_change_email", "barnetThemePasswordChangeEmail", 10, 3);

//Add custom footer version Dkr
function customize_register($wp_customize)
{
	$wp_customize->add_section('footer_settings', array(
		'title'      => __('Footer Settings', 'barnet'),
		'priority'   => 90,
	));
	$wp_customize->add_setting('copyright_text', [
		'default'     => '',
		'transport'   => 'refresh',
		'type' => 'theme_mod'
	]);
	$wp_customize->add_control(new WP_Customize_Control($wp_customize, 'copyright_text', [
		'label'        => __('Copyright Text', 'barnet'),
		'section'    => 'footer_settings',
		'settings'   => 'copyright_text',
		'type'       => 'textarea',
	]));

	$wp_customize->add_setting('terms_of_use_text', [
		'default'     => '',
		'transport'   => 'refresh',
		'type' => 'theme_mod'
	]);
	$wp_customize->add_control(new WP_Customize_Control($wp_customize, 'terms_of_use_text', [
		'label'        => __('Terms Text', 'barnet'),
		'section'    => 'footer_settings',
		'settings'   => 'terms_of_use_text',
		'type'       => 'textarea',
	]));

	$wp_customize->add_setting('privacy_policy_text', [
		'default'     => '',
		'transport'   => 'refresh',
		'type' => 'theme_mod'
	]);
	$wp_customize->add_control(new WP_Customize_Control($wp_customize, 'privacy_policy_text', [
		'label'        => __('Privacy Text', 'barnet'),
		'section'    => 'footer_settings',
		'settings'   => 'privacy_policy_text',
		'type'       => 'textarea',
	]));
}
add_action('customize_register', 'customize_register');

function display_year()
{
	return date('Y');
}
add_shortcode('year', 'display_year');

function wpse_288589_add_template_to_select( $post_templates, $wp_theme, $post, $post_type ) {

    // Add custom template named template-custom.php to select dropdown 
    $post_templates['searchByAtt.php'] = __('Search By Product Attribute');
	
    return $post_templates;
}

add_filter( 'theme_page_templates', 'wpse_288589_add_template_to_select', 10, 4 );

function wpse_288589_load_plugin_template (  $template ) {
	global $searchbyatt;
	$searchbyatt="searchbyatt";
	$check_page_exist = barnet_get_page_by_slug("searchbyatt", 'OBJECT', 'page');
	// Check if the page already exists
	if(empty($check_page_exist)) {
		$page_id = wp_insert_post(
			array(
			'comment_status' => 'close',
			'ping_status'    => 'close',
			'post_author'    => 1,
			'post_title'     => ucwords($searchbyatt),
			'post_name'      => strtolower(str_replace(' ', '-', trim($searchbyatt))),
			'post_status'    => 'publish',
			'post_slug'      => $searchbyatt,
			'post_content'   => '',
			'post_type'      => 'page',
			)
		);
		update_post_meta( $page_id, '_wp_page_template', 'searchByAtt.php' );	
	}
	
	 
	
     if(  get_page_template_slug() === 'searchByAtt.php' ) {
		
		
         if ( $theme_file = locate_template( array( 'searchByAtt.php' ) ) ) {
             $template = $theme_file;
         }
		 else{
			$template = get_template_directory() . '/templates/searchByAtt.php';
		 }	 
     }
     if($template == '') {
         throw new \Exception('No template found');
     }

     return $template;
}

//add_action('after_switch_theme', 'add_searchByattr_page');

add_filter( 'template_include', 'wpse_288589_load_plugin_template' );

function barnet_get_page_by_slug( $page_slug, $output = OBJECT, $post_type = 'page' ) {
	global $wpdb;

	if ( is_array( $post_type ) ) {
		$post_type = esc_sql( $post_type );
		$post_type_in_string = "'" . implode( "','", $post_type ) . "'";
		$sql = $wpdb->prepare( "
			SELECT ID
			FROM $wpdb->posts
			WHERE post_name = %s
			AND post_type IN ($post_type_in_string)
		", $page_slug );
	} else {
		$sql = $wpdb->prepare( "
			SELECT ID
			FROM $wpdb->posts
			WHERE post_name = %s
			AND post_type = %s
		", $page_slug, $post_type );
	}

	$page = $wpdb->get_var( $sql );

	if ( $page )
		return get_post( $page, $output );

	return null;
}

add_action('wp_ajax_nopriv_search_ajax_t_attr', 'search_ajax_t_attr' );
add_action('wp_ajax_search_ajax_t_attr',   'search_ajax_t_attr' );

function search_ajax_t_attr(){
	
	$posts_per_page=20;
	if(isset($_POST['posts_per_page'])) $posts_per_page=$_POST['posts_per_page'];

	$page_number=1;
	if(isset($_POST['page_number'])) $page_number=$_POST['page_number'];

	$productType="Active";
	if(isset($_POST['productType'])) $productType=$_POST['productType'];

	$product_type_term = get_term_by('name', $productType, 'product-type');

	$attrNum=0;
	if(isset($_POST['attrNum'])) $attrNum=$_POST['attrNum'];

	$args = array(
        'posts_per_page' => $posts_per_page,
        'orderby' => "Date", 
        'order' => 'DESC',
        'post_type' =>  "barnet-product",
        'post_status' => 'publish',
        'paged' => $page_number,
	    'meta_query' => array(
			array(
				'key' => 'product_type_term',
				'value' =>  $product_type_term->term_id,
				'compare' => '='
			),
		),     
        'relationship' => array(
            'id' => 'products_to_pattributes',
            'from' => $attrNum,
        ) 
    );
    $wp_query = new WP_Query($args);
    $count_all_post = $wp_query->found_posts;
    if ($wp_query->have_posts()) {
        while ($wp_query->have_posts()) {
            $wp_query->the_post();
            $post_id = get_the_ID();
            $post_title=get_the_title($post_id);
            $description_name="";
            if (is_user_logged_in()) {
                $description_name="product_description_logged";
            }
            else{
                
                $description_name="product_description";
            }
            $descriptions=get_post_meta($post_id, $description_name, true);  
           // $product_type=get_post_meta($post_id, "product_type", true);
            $product_area=get_post_meta($post_id, "product_area", true);
            
            
    ?>

    <div class="component-list-product__item">
        <div class="component-list-product__wrap">
            <div class="component-list-product__title">
                <h3>
                    <a href="<?php echo get_permalink( $post_id );?>" title="<?php echo $post_title;?>" rel="stylesheet">
                        <?php echo $post_title;?>
                        <?php if($product_area=="global") {?>
                        <i class="icon icon-global-product"></i>
                        <?php } ?>
                    </a> <!---->
                </h3>
            </div> 
            <div class="component-list-product__desc">
                <?php 
                if (!is_user_logged_in()) {
                echo $descriptions;
                }
                else{
                    foreach($descriptions as $description){
                        ?>
                        <div>
                            <?php echo $description; ?>
                        </div>
                        <?php
                    }
                    
                }
                ?>
            </div>
        </div>
    </div>
        
    <?php 
        }
        wp_reset_query();
    }
	die();
}


add_action('wp_ajax_nopriv_search_ajax_t', 'search_ajax_t' );
add_action('wp_ajax_search_ajax_t',   'search_ajax_t' );

function search_ajax_t(){
	
	$posts_per_page=20;
	if(isset($_POST['posts_per_page'])) $posts_per_page=$_POST['posts_per_page'];

	$page_number=1;
	if(isset($_POST['page_number'])) $page_number=$_POST['page_number'];

	$productType="Active";
	if(isset($_POST['productType'])) $productType=$_POST['productType'];
	$product_type_term = get_term_by('name', $productType, 'product-type');
    $terms_array=[];
    if(isset($_POST['terms_array'])){
		$terms_array = $_POST['terms_array'];
	}

    $tax_query=[
        'relation' => 'AND',
    ];

    if($terms_array){
        $tax_query=array_merge($tax_query,$terms_array);
    }

	$args = array(
        'posts_per_page' => $posts_per_page,
        'orderby' => "title", 
        'order' => 'ASC',
        'post_type' =>  "barnet-product",
        'post_status' => 'publish',
        'paged' => $page_number,
		'tax_query' => $tax_query,
		'meta_query' => array(
			'relation' => 'AND',
			array(
				'key' => 'product_only_for_code_list',
				'value' => '0',
				'compare' => 'like',
			),
			array(
				'key' => 'product_type_term',
				'value' =>  $product_type_term->term_id,
				'compare' => '='
			),
		),     
    );
    $wp_query = new WP_Query($args);
    $count_all_post = $wp_query->found_posts;
    if ($wp_query->have_posts()) {
        while ($wp_query->have_posts()) {
            $wp_query->the_post();
            $post_id = get_the_ID();
            $post_title=get_the_title($post_id);
            $description_name="";
            if (is_user_logged_in()) {
                $description_name="product_description_logged";
            }
            else{
                
                $description_name="product_description";
            }
            $descriptions=get_post_meta($post_id, $description_name, true);  
            //$product_type=get_post_meta($post_id, "product_type", true);
            $product_area=get_post_meta($post_id, "product_area", true);
            
            
    ?>

    <div class="component-list-product__item">
        <div class="component-list-product__wrap">
            <div class="component-list-product__title">
                <h3>
                    <a href="<?php echo get_permalink( $post_id );?>" title="<?php echo $post_title;?>" rel="stylesheet">
                        <?php echo $post_title;?>
                        <?php if($product_area=="global") {?>
                        <i class="icon icon-global-product"></i>
                        <?php } ?>
                    </a> <!---->
                </h3>
            </div> 
            <div class="component-list-product__desc">
                <?php 
                if (!is_user_logged_in()) {
                echo $descriptions;
                }
                else{
                    foreach($descriptions as $description){
                        ?>
                        <div>
                            <?php echo $description; ?>
                        </div>
                        <?php
                    }
                    
                }
                ?>
            </div>
        </div>
    </div>
        
    <?php 
        }
	?>
	<div class="product__loadmore" id="see-more-producs-btn">
		<div class="product__loadmore-text">
			Showing 
			<span>
				<?php if($count_all_post>20*$page_number){?>
				<span class="page_product_list_num">
				1-<?php echo 20*$page_number;?>
				</span>
				<?php } 
				else {?>
				<span class="page_product_list_num">
				1-<?php echo $count_all_post;?>
				</span>                            
				<?php } ?>
				<span>
				of 
					<span class="total_item_num_attr"><?php echo $count_all_post; ?>

					</span>
				</span>
			</span>
		</div> 
	
		<div class="product__loadmore-btn">
			<a title="See More" class="btn btn-regular"  onclick="filteringProduct()">See More</a>
		</div>
	</div>
	<?php
        wp_reset_query();
    }
	die();
}


add_action('wp_ajax_nopriv_search_ajax_t_allPro', 'search_ajax_t_allPro' );
add_action('wp_ajax_search_ajax_t_allPro',   'search_ajax_t_allPro' );


function search_ajax_t_allPro(){
	
	$posts_per_page=20;
	if(isset($_POST['posts_per_page'])) $posts_per_page=$_POST['posts_per_page'];

	$page_number=1;
	if(isset($_POST['page_number'])) $page_number=$_POST['page_number'];

	$productType="Active";
	if(isset($_POST['productType'])) $productType=$_POST['productType'];

	$product_type_term = get_term_by('name', $productType, 'product-type');

    $terms_array=[];
    if(isset($_POST['terms_array'])){
		$terms_array = $_POST['terms_array'];
	}

    $tax_query=[
        'relation' => 'AND',
    ];

    if($terms_array){
        $tax_query=array_merge($tax_query,$terms_array);
    }

	$args = array(
        'posts_per_page' => $posts_per_page,
        'orderby' => "title", 
        'order' => 'ASC',
        'post_type' =>  "barnet-product",
        'post_status' => 'publish',
        'paged' => $page_number,
		'tax_query' => $tax_query,
		'meta_query' => array(
			'relation' => 'AND',
			array(
				'key' => 'product_only_for_code_list',
				'value' => '0',
				'compare' => 'like',
			),
			array(
				'key' => 'product_type_term',
				'value' =>  $product_type_term->term_id,
				'compare' => '='
			),
		),     
    );
    $wp_query = new WP_Query($args);
    $count_all_post = $wp_query->found_posts;
    if ($wp_query->have_posts()) {
        while ($wp_query->have_posts()) {
            $wp_query->the_post();
            $post_id = get_the_ID();
            $post_title=get_the_title($post_id);
            $description_name="";
            if (is_user_logged_in()) {
                $description_name="product_description_logged";
            }
            else{
                
                $description_name="product_description";
            }
            $descriptions=get_post_meta($post_id, $description_name, true);  
            //$product_type=get_post_meta($post_id, "product_type", true);
            $product_area=get_post_meta($post_id, "product_area", true);
            
            
    ?>

    <div class="component-list-product__item">
        <div class="component-list-product__wrap">
            <div class="component-list-product__title">
                <h3>
                    <a href="<?php echo get_permalink( $post_id );?>" title="<?php echo $post_title;?>" rel="stylesheet">
                        <?php echo $post_title;?>
                        <?php if($product_area=="global") {?>
                        <i class="icon icon-global-product"></i>
                        <?php } ?>
                    </a> <!---->
                </h3>
            </div> 
            <div class="component-list-product__desc">
                <?php 
                if (!is_user_logged_in()) {
                echo $descriptions;
                }
                else{
                    foreach($descriptions as $description){
                        ?>
                        <div>
                            <?php echo $description; ?>
                        </div>
                        <?php
                    }
                    
                }
                ?>
            </div>
        </div>
    </div>
        
    <?php 
        }
	?>
	<div class="product__loadmore" id="see-more-producs-btn">
		<div class="product__loadmore-text">
			Showing 
			<span>
				<?php if($count_all_post>20*$page_number){?>
				<span class="page_product_list_num">
				1-<?php echo 20*$page_number;?>
				</span>
				<?php } 
				else {?>
				<span class="page_product_list_num">
				1-<?php echo $count_all_post;?>
				</span>                            
				<?php } ?>
				<span>
				of 
					<span class="total_item_num_attr"><?php echo $count_all_post; ?>

					</span>
				</span>
			</span>
		</div> 
	   <?php if($page_number*20< $count_all_post ){?>
		<div class="product__loadmore-btn">
			<a title="See More" class="btn btn-regular"  onclick="filteringProductByAll('<?php echo $productType ?>')">See More</a>
		</div>
		<?php } ?>
	</div>
	<?php
        wp_reset_query();
    }
	die();
}


add_action('wp_ajax_nopriv_search_ajax_t_resource', 'search_ajax_t_resource' );
add_action('wp_ajax_search_ajax_t_resource',   'search_ajax_t_resource' );

function search_ajax_t_resource(){
	
	$posts_per_page=20;
	if(isset($_POST['posts_per_page'])) $posts_per_page=$_POST['posts_per_page'];

	$page_number=1;
	if(isset($_POST['page_number'])) $page_number=$_POST['page_number'];

    $terms_array=[];
    if(isset($_POST['terms_array'])){
		$terms_array = $_POST['terms_array'];
	}

    $tax_query=[
        'relation' => 'AND',
    ];

    if($terms_array){
        $tax_query=array_merge($tax_query,$terms_array);
    }
	$args = array(
		'taxonomy' => 'resource-type',
		'hide_empty' => false
	);
	
	$taxonomies = get_terms($args);

	$formatTaxonomies = array();
	foreach ($taxonomies as $taxonomy) {
		if ($taxonomy->parent == 0) {
			$formatTaxonomies[$taxonomy->term_id] = $taxonomy->to_array();
		}
	}
	
	foreach ($taxonomies as $taxonomy) {
		if ($taxonomy->parent != 0) {
			$taxArray = $taxonomy->to_array();
			$taxArray['count'] = 0;
			$formatTaxonomies[$taxonomy->parent]['child'][] = $taxArray;
		}
	}
	
	/**************************************** Product List **************************************/
	
	$fixRequest = $_REQUEST;
	//$fixRequest['exclude'] = 'application/pdf';
	
	$resourceLanding = getResourceLandingByfilter($tax_query);
	$barnetRestAPI = new BarnetRestAPI();
	$resources = $barnetRestAPI->getResourcesTax($fixRequest);
	$formatResourceLanding = array();
	
	foreach ($resources as $resource) {
		$resourceTaxonomyIds = array_map(function ($e) {
			return is_array($e) ? $e['term_id'] : $e->term_id;
		}, $resource['taxonomies']);
	
		foreach ($formatTaxonomies as $k0 => $formatTaxonomy) {
			if (!isset($formatTaxonomy['child'])) {
				continue;
			}
	
			foreach ($formatTaxonomy['child'] as $k1 => $childTaxonomy) {
				if (in_array($childTaxonomy['term_id'], $resourceTaxonomyIds)) {
					$formatTaxonomies[$k0]['child'][$k1]['count']++;
				}
			}
		}
	}

	foreach ($resourceLanding as $resource) {
		/** @var WP_Term $resourceTaxonomy */
		foreach ($resource['taxonomies'] as $resourceTaxonomy) {
			if ($resourceTaxonomy->is_showed == 0) {
				continue;
			}
	
			if (!isset($formatResourceLanding[$resourceTaxonomy->term_id]['title'])) {
				$formatResourceLanding[$resourceTaxonomy->term_id]['title'] = $resourceTaxonomy->name;
			}
			$formatResourceLanding[$resourceTaxonomy->term_id]['order'] = isset($resourceTaxonomy->order) ? $resourceTaxonomy->order : null;
	
			if (!isset($formatResourceLanding[$resourceTaxonomy->term_id]['slug'])) {
				$formatResourceLanding[$resourceTaxonomy->term_id]['slug'] = $resourceTaxonomy->slug;
			}
	
			$formatResourceLanding[$resourceTaxonomy->term_id]['data'][] = $resource['data'];
		}
	
	}
	usort($formatResourceLanding, function ($a, $b) {
		return (isset($a['order']) ? $a['order'] : '99999') <=> (isset($b['order'] ) ? $b['order'] : '99999');
	});
	
	$htmlFitter = '<div class="product__filter-collapse" data-filter-collapse>';
	$htmlFitter .= '<div class="product__filter-wrap">';
	$tIndex = 0;
	foreach ($formatTaxonomies as $taxonomy) {
		
		$isShowFilter = false;
		$_htmlFitter = '<div class="product__boxFilter';
		$_htmlFitter .= $tIndex < 2 ? ' active' : '';
		$_htmlFitter .= '" data-boxcollapse>';
		$_htmlFitter .= '<div class="product__boxFilter-title" ';
		$_htmlFitter .= 'data-boxcollapse-toggle>';
		$_htmlFitter .= empty($taxonomy['name']) ? '' : $taxonomy['name'];
		$_htmlFitter .= '</div>';
		$_htmlFitter .= '<div class="product__boxFilter-box">';
		$_htmlFitter .= '<div class="product__boxFilter-list" data-filter-list>';
		if (!empty($taxonomy['child'])) {
			foreach ($taxonomy['child'] as $taxonomyChild) {
				$allPost = $barnetRestAPI->getResourcesByTerm($taxonomyChild['taxonomy'], $taxonomyChild['term_id'], 'barnet-resource');
				if ($taxonomyChild['count'] == 0) {
					continue;
				}
	
				if (!$isShowFilter) {
					$isShowFilter = true;
				}
	
				$_htmlFitter .= '<div class="product-filter-wrapp"><input  class="--dark-mode click-filter" onclick="clickResourceFilter()" type="checkbox" name="'.
				$taxonomyChild['name'].'" taxonomy="'.$taxonomy['taxonomy'].'" data-slug="'.$taxonomyChild['slug'].'" value="'.$taxonomyChild['slug'].'">';
				$_htmlFitter .= '<span class="checkMark"></span><span class="product-filter-title">' . 
				$taxonomyChild['name'] . ' (' .  count($allPost) . ')</span></div>';
			}
		}
		$_htmlFitter .= '</div></div></div>';
	
		if ($isShowFilter) {
			$htmlFitter .= $_htmlFitter;
			$tIndex++;
		}
	}
	$htmlFitter .= '</div></div>';
	/*
	$args = array(
        'posts_per_page' => $posts_per_page,
        'orderby' => "title", 
        'order' => 'ASC',
        'post_type' =>  "barnet-resource",
        'post_status' => 'publish',
        'paged' => $page_number,
		'tax_query' => $tax_query, 
    );
    $wp_query = new WP_Query($args);
    $count_all_post = $wp_query->found_posts;
    if ($wp_query->have_posts()) {
        while ($wp_query->have_posts()) {
            $wp_query->the_post();
            $post_id = get_the_ID();
            $post_title=get_the_title($post_id);

            
            
    ?>

    <div class="component-list-product__item">
        <div class="component-list-product__wrap">
            <div class="component-list-product__title">
                <h3>
                    <a href="<?php echo get_permalink( $id );?>" title="<?php echo $post_title;?>" rel="stylesheet">
                        <?php echo $post_title;?>
                        <?php if($product_area=="global") {?>
                        <i class="icon icon-global-product"></i>
                        <?php } ?>
                    </a> <!---->
                </h3>
            </div> 
            <div class="component-list-product__desc">
                <?php 
                if (!is_user_logged_in()) {
                echo $descriptions;
                }
                else{
                    foreach($descriptions as $description){
                        ?>
                        <div>
                            <?php echo $description; ?>
                        </div>
                        <?php
                    }
                    
                }
                ?>
            </div>
        </div>
    </div>
        
    <?php 
        }
	?>
	<div class="product__loadmore" id="see-more-producs-btn">
		<div class="product__loadmore-text">
			Showing 
			<span>
				<?php if($count_all_post>20*$page_number){?>
				<span class="page_product_list_num">
				1-<?php echo 20*$page_number;?>
				</span>
				<?php } 
				else {?>
				<span class="page_product_list_num">
				1-<?php echo $count_all_post;?>
				</span>                            
				<?php } ?>
				<span>
				of 
					<span class="total_item_num_attr"><?php echo $count_all_post; ?>

					</span>
				</span>
			</span>
		</div> 
	
		<div class="product__loadmore-btn">
			<a title="See More" class="btn btn-regular"  onclick="filteringProduct()">See More</a>
		</div>
	</div>
	<?php
        wp_reset_query();
    }
	*/
	echo $htmlFitter;
	die();
}

function getResourceLandingByfilter($tax_query)
{
	$dataDefault = get_posts(
		array(
			'posts_per_page' => -1,
			'post_type' => 'barnet-resource',
			'tax_query' => $tax_query
		)
	);

	$result = array();
	$count = 0;
	foreach ($dataDefault as $data) {
		$resource = new ResourceEntity($data->ID, true, array('post' => $data));
		$show_resource = '';
		$show_resource = get_post_meta($data->ID, 'show_resource', TRUE);
		$mediaType = $resource->getResourceMediaType();
		if ($show_resource != 1 && $mediaType == 'application/pdf') {
			continue;
		}
		if (isset($request['exclude'])) {
			$excludeMimeType = $request['exclude'];
			if ($resource->getResourceMediaType() == $excludeMimeType) {
				continue;
			}
		}
		$result[] = $resource->toArray(BarnetEntity::$PUBLIC_LANDING, false, true, ResourceEntity::EXCEPT_PROTECTED);
		// unset($result[$count]['relationship']);
		// unset($result[$count]['widgets']);
		$count++;

	}

	return array_values(array_filter($result));
}

function barnet_retrieve_password_message( $message, $key, $user_login ) {
	$site_name  = wp_specialchars_decode( get_option( 'blogname' ), ENT_QUOTES );
	$reset_link = network_site_url( "wp-login.php?action=rp&key=$key&login=" . rawurlencode( $user_login ), 'login' );

	// Create new message
	$message = __( 'Someone has requested a password reset for the following account:' . $user_login, 'text_domain' ) . "\n";
	$message .= sprintf( __( 'Site Name: %s' ), network_home_url( '/' ) ) . "\n";
	$message .= sprintf( __( 'Username: %s', 'text_domain' ), $user_login ) . "\n";
	$message .= __( 'If this is not you, just ignore this email and nothing will happen.', 'text_domain' ) . "\n";
	$message .= __( 'To reset your password, visit the following address:', 'text_domain' ) . "\n";
	$message .= $reset_link . "\n";

	return $message;
}

add_filter( 'retrieve_password_message', 'barnet_retrieve_password_message', 50, 3 );

add_filter('pre_option_default_role', function($default_role){
    // You can also add conditional tags here and return whatever
    return 'requestor'; // This is changed
    
});


function wpdocs_logout_redirect( $redirect_to, $requested_redirect_to, $user ) {

    $user_roles = $user->roles;
    $user_has_admin_role = in_array( 'administrator', $user_roles );

	if ( $user_has_admin_role ) :
		$redirect_to = admin_url();
	else:
		$redirect_to = home_url();
	endif;

	return $redirect_to;
}         
add_filter( 'logout_redirect', 'wpdocs_logout_redirect', 9999, 3 );
