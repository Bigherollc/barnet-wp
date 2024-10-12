<?php

class BarnetSampleRequest extends BarnetDataType
{
    const EMAIL = "email";
    const LASTNAME = "lastName";
    const FIRSTNAME = "firstName";
    const COMPANYNAME = "companyName";
    const ADDRESS = "address";
    const COUNTRY = "country";
    const CITY = "city";
    const PROVINCE = "province";
    const POSTALCODE = "postalCode";
    const PHONE = "phone";
    const ADDRESSOPTIONAL = "addressOptional";
    const PHONEOPTIONAL = "phoneOptional";
    const NEWSLETTER = "newsletter";
    const ABOUTUS = "aboutUs";
    const BREAKLINE = "\r\n\r\n";
    const JOBTITLEROLE = 'job_title_role';
    const ERROR_USER = "_error_user";

    public function createPostType()
    {
        register_post_type(
            $this->postType,
            $this->buildArgs(
                'Sample Request',
                'Sample Requests'
            )
        );
    }

    public function addExt()
    {
    }

    public function addRelationship()
    {
    }

    public function insertData($email, $description, $timestamp = null)
    {
        $date = isset($timestamp) ? (new DateTime("@$timestamp"))->format('d/m/Y') : date('d/m/Y');
        $postId = wp_insert_post(array(
            'post_title' => "{$email}-{$date}",
            'post_type' => 'sample-request',
            'comment_status' => 'closed',
            'ping_status' => 'closed',
            'post_content' => $description,
            'post_status' => 'publish',
            'post_author' => 1,
            'menu_order' => 0,
        ));
        if (is_wp_error($postId)) {
            return false;
        }

        return true;
    }

    protected function getListItemSampleRequest($arrPostId, $listPostID = array())
    {
        $listPosts = array();
        $listCheck = get_posts(array(
            'numberposts' => -1,
            'post_type' => array('barnet-formula', 'barnet-product'),
            'post_status' => 'publish',
            'post__in' => $arrPostId
        ));

        if ($listCheck) {
            foreach ($listCheck as $p) {
                $countPost = 1;
                if (isset($listPostID[$p->ID])) {
                    $countPost = $listPostID[$p->ID];
                }
                $listPosts[$p->ID] = array('p' => $p, 'count' => $countPost);
            }
        }

        return $listPosts;
    }

    protected function getListSampleRequest($selectedSample = '')
    {
        $listPosts = array();
        $expSelect = explode(",", trim($selectedSample, ','));
        if (count($expSelect) > 0) {
            $listPostID = array();
            $arrPostId = array();
            foreach ($expSelect as $eS) {
                $exp = explode(':', $eS);
                if (count($exp) > 0) {
                    $postId = intval($exp[0]);
                    $countPost = 1;
                    if (!empty($exp[1]) && intval($exp[1]) > 1) {
                        $countPost = intval($exp[1]);
                    }
                    $listPostID[$postId] = $countPost;
                    $arrPostId[] = $postId;
                }
            }
            if (count($arrPostId) > 0) {
                $listPosts = $this->getListItemSampleRequest($arrPostId, $listPostID);
            }
        }
        return $listPosts;
    }

    public function validateDataSampleRequestAPI($dataPost, $user = null)
    {
        $error = new WP_Error();
        if (empty($dataPost['selectedSample'])) {
            $error->add('empty_post', __("Must select at least one sample before submitting request"));
        }

        if (empty($dataPost[self::FIRSTNAME])) {
            $error->add('empty_fistname', __("The Fist Name field is empty."));
        }

        if (empty($dataPost[self::LASTNAME])) {
            $error->add('empty_lastname', __("The Last Name field is empty."));
        }

        if (empty($dataPost[self::EMAIL])) {
            $error->add('empty_email', __("The Email field is empty."));
        } elseif (!is_email($dataPost[self::EMAIL])) {
            $error->add('valid_email', __('Please enter a valid email address'));
        }

        if (empty($dataPost[self::COMPANYNAME])) {
            $error->add('empty_company', __("The Company Name field is empty."));
        }

        if (empty($dataPost[self::ADDRESS])) {
            $error->add('empty_address', __("The Address field is empty."));
        }

        if (empty($dataPost[self::COUNTRY])) {
            $error->add('empty_country', __("The Country field is empty."));
        }

        if (empty($dataPost[self::PROVINCE])) {
            $error->add('empty_province', __("The State/Province field is empty."));
        }

        if (empty($dataPost[self::POSTALCODE])) {
            $error->add('empty_postalCode', __("The Postal Code field is empty."));
        }

        if (empty($dataPost[self::PHONE])) {
            $error->add('empty_phone', __("The Work Phone field is empty."));
        }

        return $error;
    }

    public function validateDataSampleRequest($dataPost, $user = null)
    {
        $error = new WP_Error();
        if (empty($dataPost['selectedSample'])) {
            $error->add('empty_post', __("Must select at least one sample before submitting request"));
        }

        if (empty($dataPost[self::FIRSTNAME])) {
            $error->add('empty_fistname', __("The Fist Name field is empty."));
        }

        if (empty($dataPost[self::LASTNAME])) {
            $error->add('empty_lastname', __("The Last Name field is empty."));
        }

        if (empty($dataPost[self::EMAIL])) {
            $error->add('empty_email', __("The Email field is empty."));
        } elseif (!is_email($dataPost[self::EMAIL])) {
            $error->add('valid_email', __('Please enter a valid email address'));
        }

        if (empty($dataPost[self::COMPANYNAME])) {
            $error->add('empty_company', __("The Company Name field is empty."));
        }

        if (empty($dataPost[self::ADDRESS])) {
            $error->add('empty_address', __("The Address field is empty."));
        }

        if (empty($dataPost[self::COUNTRY])) {
            $error->add('empty_country', __("The Country field is empty."));
        }

        if (empty($dataPost[self::CITY])) {
            $error->add('empty_city', __("The City field is empty."));
        }

        if (empty($dataPost[self::JOBTITLEROLE])) {
            $error->add('empty_job_title_role', __("The Job Title Role field is empty."));
        }

        if (empty($dataPost[self::PROVINCE])) {
            $error->add('empty_province', __("The State/Province field is empty."));
        }

        if (empty($dataPost[self::POSTALCODE])) {
            $error->add('empty_postalCode', __("The Postal Code field is empty."));
        }

        if (empty($dataPost[self::PHONE])) {
            $error->add('empty_phone', __("The Work Phone field is empty."));
        }

        return $error;
    }

    protected function sendEmailSampleRequest($dataPost, $listPosts)
    {
        if (is_multisite()) {
            $siteName = get_network()->site_name;
        } else {
            $siteName = wp_specialchars_decode(get_option('blogname'), ENT_QUOTES);
        }

        $emailAdmin = get_option('admin_email');
        $wpMailOption = get_option('wp_mail_smtp');

        if (is_array($wpMailOption) && isset($wpMailOption["mail"]) && isset($wpMailOption["mail"]["from_email"])) {
            $emailAdmin = trim($wpMailOption["mail"]["from_email"]);
        }

        $emailSamples = get_theme_mod('email_contact_samples', '');

        // filter our list of comma-seperated emails through is_email and trim
        $emailSamples = implode(',',
                    array_map('trim',
                    array_filter(explode(',', $emailSamples),
                        function ($e) { return is_email(trim($e)); })));
        if (!empty($emailSamples)) {
            $emailAdmin = $emailSamples;
        }

        if ($emailAdmin != "") {
            $getListCountryName = Barnet::getListCountries();
            $countryName = '';
            if (isset($getListCountryName[$dataPost[self::COUNTRY]])) {
                $countryName = $getListCountryName[$dataPost[self::COUNTRY]];
            }

            $timestamp = isset($dataPost['timestamp']) ? $dataPost['timestamp'] : time();
            $dateTime = (new DateTime("@$timestamp"))->format("m/d/Y, H:i:s");
            $message = __('Account Info:') . self::BREAKLINE;
            if (!empty($dataPost[self::EMAIL])) $message .= sprintf(__('Email: %s'), $dataPost[self::EMAIL]) . self::BREAKLINE;
            if (!empty($dataPost[self::FIRSTNAME]))$message .= sprintf(__('First Name: %s'), $dataPost[self::FIRSTNAME]) . self::BREAKLINE;
            if (!empty($dataPost[self::LASTNAME]))$message .= sprintf(__('Last Name: %s'), $dataPost[self::LASTNAME]) . self::BREAKLINE;
            if (!empty($dataPost[self::COMPANYNAME]))$message .= sprintf(__('Company Name: %s'), $dataPost[self::COMPANYNAME]) . self::BREAKLINE;
            if (!empty($dataPost[self::ADDRESS]))$message .= sprintf(__('Address: %s'), $dataPost[self::ADDRESS]) . self::BREAKLINE;
            if (!empty($dataPost[self::ADDRESSOPTIONAL]))$message .= sprintf(__('Address Optional: %s'), $dataPost[self::ADDRESSOPTIONAL]) . self::BREAKLINE;
             $message .= sprintf(__('Country: %s'), $countryName) . self::BREAKLINE;
            if (!empty($dataPost[self::CITY]))$message .= sprintf(__('City: %s'), $dataPost[self::CITY]) . self::BREAKLINE;
            if (!empty($dataPost[self::PROVINCE]))$message .= sprintf(__('Province: %s'), $dataPost[self::PROVINCE]) . self::BREAKLINE;
            if (!empty($dataPost[self::POSTALCODE]))$message .= sprintf(__('Postal Code: %s'), $dataPost[self::POSTALCODE]) . self::BREAKLINE;
            if (!empty($dataPost[self::PHONE]))$message .= sprintf(__('Phone: %s'), $dataPost[self::PHONE]) . self::BREAKLINE;
            if (!empty($dataPost[self::PHONEOPTIONAL]))$message .= sprintf(__('Phone Optional: %s'), $dataPost[self::PHONEOPTIONAL]) . self::BREAKLINE;
            if (!empty($dataPost[self::JOBTITLEROLE]))$message .= sprintf(__('Job Title/Role: %s'), $dataPost[self::JOBTITLEROLE]) . self::BREAKLINE;
            if (!empty($dataPost[self::ABOUTUS]))$message .= sprintf(__('About Us: %s'), $dataPost[self::ABOUTUS]) . self::BREAKLINE;
            if (!empty($dataPost['addNote']))$message .= sprintf(__('Note: %s'), $dataPost['addNote']) . self::BREAKLINE;
            $message .= sprintf(__('Sent At: %s'), $dateTime) . self::BREAKLINE;

            $message .= __('Sample Info:') . self::BREAKLINE;
            $index = 1;
            foreach ($listPosts as $v) {
                $message .= sprintf(__('item %s : %s --- count : %s'), $index, $v['p']->post_title, $v['count']);
                $message .= self::BREAKLINE;
                $index++;
            }

            //save info data
            $this->insertData($dataPost[self::EMAIL], $message, $timestamp);

            //send mail
            $title = sprintf(__('[%s]  Sample Request'), $siteName);

            //send email to user
            if ($message && !wp_mail($dataPost[self::EMAIL], wp_specialchars_decode($title), $message)) {

            }
            if ($message && !wp_mail($emailAdmin, wp_specialchars_decode($title), $message)) {
                return false;
            }
        }
        return true;
    }

    protected function createUserSampleRequestAPI(
        $dataPost = array(),
        $createUser = false,
        $username = '',
        $user = null
    )
    {
        $error = new WP_Error();
        $nickname = $dataPost[self::FIRSTNAME] . " " . $dataPost[self::LASTNAME];
        if (empty($dataPost[self::ADDRESSOPTIONAL])) {
            $dataPost[self::ADDRESSOPTIONAL] = '';
        }
        if (empty($dataPost[self::PHONEOPTIONAL])) {
            $dataPost[self::PHONEOPTIONAL] = '';
        }
        if (empty($dataPost[self::NEWSLETTER])) {
            $dataPost[self::NEWSLETTER] = 'off';
        }
        if (empty($dataPost[self::ABOUTUS])) {
            $dataPost[self::ABOUTUS] = '';
        }

        if (empty($dataPost[self::CITY])) {
            $dataPost[self::CITY] = '';
        }



        $userDataOther = array(
            'company_name' => $dataPost[self::COMPANYNAME],
            self::ADDRESS => $dataPost[self::ADDRESS],
            'address_optional' => $dataPost[self::ADDRESSOPTIONAL],
            self::COUNTRY => $dataPost[self::COUNTRY],
            self::PROVINCE => $dataPost[self::PROVINCE],
            'postal_code' => $dataPost[self::POSTALCODE],
            self::PHONE => $dataPost[self::PHONE],
            'phone_optional' => $dataPost[self::PHONEOPTIONAL],
           // self::NEWSLETTER => $dataPost[self::NEWSLETTER],
            'about_us' => $dataPost[self::ABOUTUS],
            'city'  => $dataPost[self::CITY],
            //'note'  => $dataPost['addNote'],
        );

        if ($createUser) {
            $userPass = wp_generate_password();
            $userData = array(
                'user_login' => $username,
                'user_email' => $dataPost[self::EMAIL],
                'user_pass' => $userPass,
                'nickname' => $nickname,
                'description' => $dataPost['addNote'],
                'first_name' => $dataPost[self::FIRSTNAME],
                'last_name' => $dataPost[self::LASTNAME]
            );

            $userId = wp_insert_user($userData);
            if (is_wp_error($userId)) {
                $error->add(self::ERROR_USER, $userId->get_error_message());
            } else {
                add_user_meta($userId, 'user_extra_type', 'barnet', true);
				add_user_meta( $userId, 'newsletter', $dataPost[self::NEWSLETTER], true );
                add_user_meta($userId, 'user_extra_info', serialize($userDataOther), true);
            }
        } elseif ($user && $user->ID > 0) {
            //update data user
            $userData = array(
                'ID' => $user->ID,
                'user_email' => $dataPost[self::EMAIL],
                'first_name' => $dataPost[self::FIRSTNAME],
                'last_name' => $dataPost[self::LASTNAME],
                //'description' => $dataPost['addNote'],
                'nickname' => $nickname
            );

            $userExtraInfo = get_user_meta($user->ID, 'user_extra_info', '');
            if (!empty($userExtraInfo) && is_array($userExtraInfo)) {
                $userExtraInfo = unserialize($userExtraInfo[0]);
                $note = isset($userExtraInfo["note"]) ? $userExtraInfo["note"] : '';
                $userDataOther['note'] = $note;
            }
            $userId = wp_update_user($userData);
            if (is_wp_error($userId)) {
                $error->add('_error_update_user', $user->get_error_message());
            } else {
                unset($userDataOther['about_us']);
                update_user_meta($userId, 'user_extra_info', serialize($userDataOther));
				update_user_meta($userId, 'newsletter', $dataPost[self::NEWSLETTER]);
            }
        }

        return $error;
    }

    protected function createUserSampleRequest(
        $dataPost = array(),
        $createUser = false,
        $username = '',
        $user = null
    )
    {
        $error = new WP_Error();
        $nickname = $dataPost[self::FIRSTNAME] . " " . $dataPost[self::LASTNAME];
        if (empty($dataPost[self::ADDRESSOPTIONAL])) {
            $dataPost[self::ADDRESSOPTIONAL] = '';
        }
        if (empty($dataPost[self::PHONEOPTIONAL])) {
            $dataPost[self::PHONEOPTIONAL] = '';
        }
        if (empty($dataPost[self::NEWSLETTER])) {
            $dataPost[self::NEWSLETTER] = 'off';
        }
        if (empty($dataPost[self::ABOUTUS])) {
            $dataPost[self::ABOUTUS] = '';
        }

        $userDataOther = array(
            'company_name' => $dataPost[self::COMPANYNAME],
            self::ADDRESS => $dataPost[self::ADDRESS],
            'address_optional' => $dataPost[self::ADDRESSOPTIONAL],
            self::COUNTRY => $dataPost[self::COUNTRY],
            self::CITY => $dataPost[self::CITY],
            self::PROVINCE => $dataPost[self::PROVINCE],
            'postal_code' => $dataPost[self::POSTALCODE],
            self::PHONE => $dataPost[self::PHONE],
            'phone_optional' => $dataPost[self::PHONEOPTIONAL],
           // self::NEWSLETTER => $dataPost[self::NEWSLETTER],
            'about_us' => $dataPost[self::ABOUTUS],
            'job_title_role'  => $dataPost[self::JOBTITLEROLE]
        );

        if ($createUser) {
            $userPass = wp_generate_password();
            $userData = array(
                'user_login' => $username,
                'user_email' => $dataPost[self::EMAIL],
                'user_pass' => $userPass,
                'nickname' => $nickname,
                'description' => $dataPost['note'],
                'first_name' => $dataPost[self::FIRSTNAME],
                'last_name' => $dataPost[self::LASTNAME]
            );

            $userId = wp_insert_user($userData);
            if (is_wp_error($userId)) {
                $error->add(self::ERROR_USER, $userId->get_error_message());
            } else {
                add_user_meta($userId, 'user_extra_type', 'barnet', true);
                add_user_meta($userId, 'user_extra_info', serialize($userDataOther), true);
				add_user_meta( $userId, 'newsletter', $dataPost[self::NEWSLETTER], true );
            }
        } elseif ($user && $user->ID > 0) {
            //update data user
            $userData = array(
                'ID' => $user->ID,
                'user_email' => $dataPost[self::EMAIL],
                'first_name' => $dataPost[self::FIRSTNAME],
                'last_name' => $dataPost[self::LASTNAME],
                //'description' => $dataPost['note'],
                'nickname' => $nickname
            );
            
            $userExtraInfo = get_user_meta($user->ID, 'user_extra_info', '');
            if (!empty($userExtraInfo) && is_array($userExtraInfo)) {
                $userExtraInfo = unserialize($userExtraInfo[0]);
                $note = isset($userExtraInfo["note"]) ? $userExtraInfo["note"] : '';
                $userDataOther['note'] = $note;
            }
            $userId = wp_update_user($userData);
            if (is_wp_error($userId)) {
                $error->add('_error_update_user', $user->get_error_message());
            } else {
                //unset($userDataOther['about_us']);
                update_user_meta($userId, 'user_extra_info', serialize($userDataOther));
				update_user_meta($userId, 'newsletter', $dataPost[self::NEWSLETTER]);
            }
        }

        return $error;
    }

    protected function getUserNameCreateUser($dataPost)
    {
        $error = new WP_Error();
        $username = str_replace(
            array(".", "_", "@"),
            array("", "", ""),
            strtolower($dataPost[self::EMAIL])
        );
        $userCheck = get_user_by('login', $username);
        if ($userCheck) {
            $username .= time();
            $userCheck = get_user_by('login', $username);
            if ($userCheck) {
                $error->add('exist_username', __('Error! An error occurred. Please try again later'));
            }
        }
        if (!empty($error->errors)) {
            return $error;
        }
        return $username;
    }

    public function sampleRequestActionAPI($dataPost = array(), $user = null)
    {
        $listPosts = array();
        $username = "";
        $createUser = false;

        $error = $this->validateDataSampleRequestAPI($dataPost, $user);

        if (empty($error->errors)) {
            $listPosts = $this->getListSampleRequest($dataPost['selectedSample']);
            if (empty($listPosts)) {
                $error->add('empty_post', __("Not get List product 2"));
            }
        }

        if (!empty($error->errors)) {
            return $error;
        }

        if (empty($user) || $user->ID <= 0) {
            //checkemail create account
            $createUser = true;
            $userCheck = get_user_by(self::EMAIL, $dataPost[self::EMAIL]);
            if ($userCheck) {
                $createUser = false;
            } else {
                $username = $this->getUserNameCreateUser($dataPost);
            }
        }

        if (is_wp_error($username)) {
            $error->add('_error_username', $user->get_error_message());
        }

        if (empty($error->errors)) {
            $error = $this->createUserSampleRequestAPI($dataPost, $createUser, $username, $user);
        }

        if (empty($error->errors)) {
            //send email and save data
            $this->sendEmailSampleRequest($dataPost, $listPosts);
        }

        if (!empty($error->errors)) {
            return $error;
        }
        
        return true;
    }

    public function sampleRequestAction($dataPost = array(), $user = null)
    {
        $listPosts = array();
        $username = "";
        $createUser = false;

        $error = $this->validateDataSampleRequest($dataPost, $user);

        if (empty($error->errors)) {
            $listPosts = $this->getListSampleRequest($dataPost['selectedSample']);
            if (empty($listPosts)) {
                $error->add('empty_post', __("Not get List product 2"));
            }
        }

        if (!empty($error->errors)) {
            return $error;
        }

        if (empty($user) || $user->ID <= 0) {
            //checkemail create account
            $createUser = true;
            $userCheck = get_user_by(self::EMAIL, $dataPost[self::EMAIL]);
            if ($userCheck) {
                $createUser = false;
            } else {
                $username = $this->getUserNameCreateUser($dataPost);
            }
        }

        if (is_wp_error($username)) {
            $error->add('_error_username', $user->get_error_message());
        }

        if (empty($error->errors)) {
            $error = $this->createUserSampleRequest($dataPost, $createUser, $username, $user);
        }

        if (empty($error->errors)) {
            //send email and save data
            $this->sendEmailSampleRequest($dataPost, $listPosts);
        }

        if (!empty($error->errors)) {
            return $error;
        }

        return true;
    }
}

$barnetSampleRequest = new BarnetSampleRequest('sr_', 'sample-request');
$barnetSampleRequest->addAdminColumn(__('Content'), function ($post_id) {
    $content = get_post_field('post_content', $post_id);
    $content = preg_replace("/[\r\n]/", "</p><p>", $content);
    echo "<p>" . $content . "</p>";
});
