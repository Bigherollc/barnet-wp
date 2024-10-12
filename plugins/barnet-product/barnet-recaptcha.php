<?php

class BarnetRecaptcha
{
    const ENABLE_DEFAULT = "1";
    const SITE_KEY = "6Lc_mswcAAAAAJOOQiOf2W5vZeiz5sYzKtcWBRG6";
    const SECRET_KEY = "6Lc_mswcAAAAAHzk72khYOec_V1PKou-6mSsSG1Q";
    public function __construct()
    {
    }

    public function init()
    {
        $this->addSettingField('recaptcha_enable', 'ReCaptcha Enable', self::ENABLE_DEFAULT, 'select_enable');
        $this->addSettingField(
            'recaptcha_api_site_key',
            'ReCaptcha Site Key',
            get_option('recaptcha_api_site_key', self::SITE_KEY)
        );
        $this->addSettingField(
            'recaptcha_api_secret_key',
            'ReCaptcha Secret Key',
            get_option('recaptcha_api_secret_key', self::SECRET_KEY)
        );
        $this->addSettingField(
            'search_featured_item',
            'Search featured items',
            get_option('search_featured_item', 1),
			'check_status'
        );
        if ($this->isEnable()) {
            $currentUrl = home_url($_SERVER['REQUEST_URI']);
            if ((strpos($currentUrl, '/register') !== false) || (strpos($currentUrl, '/samples-selected') !== false) || (strpos($currentUrl, '/contact-us') !== false)) {    
                add_action('wp_enqueue_scripts', array($this, 'enqueueScript'));
            }
        }
    }

    public function enqueueScript()
    {
        $src = 'https://www.google.com/recaptcha/api.js?render=' .get_option('recaptcha_api_site_key');
        wp_enqueue_script('barnet-recaptcha', $src, false, false, true);
    }

    public function isEnable()
    {
        if (intval(get_option("recaptcha_enable", self::ENABLE_DEFAULT)) == 1) {
            return true;
        }
        return false;
    }

    public function displayCaptcha()
    {

        if (isset($_GET['captcha']) && $_GET['captcha'] == 'failed') {
            echo __('<strong>ERROR</strong>: Please retry CAPTCHA');
        }

        //echo '<div class="g-recaptcha" data-sitekey="' . get_option('recaptcha_api_site_key') . '" data-theme="light"></div>';
        echo '<input type="hidden" id="gtoken" name="g_token">';
    }

    public function captchaVerification()
    {

        //$remote_ip = $_SERVER["REMOTE_ADDR"]; // . '&remoteip=' . $remote_ip
        // make a GET request to the Google reCAPTCHA Server
        $uri = 'https://www.google.com/recaptcha/api/siteverify?secret=' . get_option('recaptcha_api_secret_key') . '&response=' . $_POST['g-recaptcha-response'];

        $request = wp_remote_get(
            $uri
        );

        // get the request response body
        $response_body = wp_remote_retrieve_body($request);
        $result = json_decode($response_body, true);
        return $result;
    }

    public function captchaV3Verification() {
        // Build POST request:
        $recaptcha_url = 'https://www.google.com/recaptcha/api/siteverify';
        $recaptcha_secret = get_option('recaptcha_api_secret_key');
        $recaptcha_response = $_POST['g_token'];
        // Make and decode POST request:
        $recaptcha = file_get_contents($recaptcha_url . '?secret=' . $recaptcha_secret . '&response=' . $recaptcha_response);
        $result = json_decode($recaptcha, true);
        return $result;
    }

    public function addSettingField(
        $id,
        $title,
        $defaultValue = false,
        $fieldType = 'text',
        $group = 'general',
        $isReadOnly = false
    ) {

        add_filter('admin_init', function () use ($id, $title, $fieldType, $group, $defaultValue, $isReadOnly) {
            add_settings_field($id, $title, function () use ($id, $fieldType, $defaultValue, $isReadOnly) {
                if ($fieldType == 'select_enable') {
                    $values = array(0 => 'Disable', 1 => "Enable");
                    $option = '';
                    foreach ($values as $k => $v) {
                        $option .= '<option value="' . $k . '"' .
                            selected(get_option($id, $defaultValue), $k, false) .
                            '>' . $v . '</option>';
                    }
                    $tmp = "<select id='$id' name='$id'>" . $option . "</select>";
                    echo $tmp;
                }
				else if($fieldType == 'check_status'){
						/*
						$checked="";
						if(get_option($id, $defaultValue)){
							$checked ="checked";
						}
						*/
						echo "<input type='checkbox' name='$id' " .
											(get_option($id, $defaultValue) ? "checked" : "") . " " . ($isReadOnly ? "readonly" : "") .
											" />";				
				}
				else {
                    echo "<input type='$fieldType' name='$id' value='" .
                        get_option($id, $defaultValue) . "' " . ($isReadOnly ? "readonly" : "") .
                        " />";
                }
            },
                $group,
                'default',
                array('label_for' => $id));

            register_setting($group, $id, 'esc_attr');
        });
    }
}
