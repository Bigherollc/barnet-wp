<?php


namespace JWTAuth;
include_once __DIR__.'/../jwt-auth/jwt-auth.php';

use Exception;
use Firebase\JWT\JWT;
use WP_Error;
use WP_REST_Request;
use WP_REST_Response;

class BarnetAuth extends Auth
{
    const JWT_AUTH_SECRET_KEY = 'JWT_AUTH_SECRET_KEY';

    const SUCCESS = 'success';

    const STATUSCODE = 'statusCode';

    const JWT_AUTH = 'jwt-auth';

    const MESSAGE = 'message';

    private $refreshTokenKey = 'jwt_refresh_token';

    /**
     * Get token by sending POST request to jwt-auth/v1/token.
     *
     * @param WP_REST_Request $request The request.
     * @return WP_REST_Response The response.
     */
    public function get_token(WP_REST_Request $request): WP_REST_Response
    {
        global $wpdb;
        $secret_key = defined(self::JWT_AUTH_SECRET_KEY) ? JWT_AUTH_SECRET_KEY : false;

        $username = $request->get_param('email');
        $password = $request->get_param('password');
        $custom_auth = $request->get_param('custom_auth');

        $username = str_replace('&', '&amp;', $username);
        $results = $wpdb->get_results("SELECT ID FROM wp_users WHERE user_email = '$username'");
        $uID = '';
        foreach ($results as $item) {
            $uID = $item->ID;
        }
        $user = get_user_by('id', $uID);

        // First thing, check the secret key if not exist return a error.
        if (! $secret_key) {
            return new WP_REST_Response(
                [
                    self::SUCCESS => false,
                    self::STATUSCODE => 403,
                    'code' => 'jwt_auth_bad_config',
                    self::MESSAGE => __('JWT is not configurated properly.', self::JWT_AUTH),
                    'data' => [],
                ]
            );
        }

        $user = $this->authenticate_user($username, $password, $custom_auth);

        // If the authentication is failed return error response.
        if (is_wp_error($user)) {
            $error_code = $user->get_error_code();

            return new WP_REST_Response(
                [
                    self::SUCCESS => false,
                    self::STATUSCODE => 403,
                    'code' => $error_code,
                    self::MESSAGE => strip_tags($user->get_error_message($error_code)),
                    'data' => [],
                ]
            );
        }

        $userAgent = $_SERVER['HTTP_USER_AGENT'];
        $roles = $user->roles;

        if (! in_array('administrator', $roles) && preg_match('/dart:io/', $userAgent, $outputArray)) {
            $isUserTablet = get_user_meta($user->ID, 'is_user_tablet', true);

            if (empty($isUserTablet)) {
                return new WP_REST_Response(
                    [
                        self::SUCCESS => false,
                        self::STATUSCODE => 403,
                        'code' => 'user_tablet_not_found',
                        self::MESSAGE => 'Permission deny',
                        'data' => [],
                    ]
                );
            }
        }

        // Valid credentials, the user exists, let's generate the token.
        return new WP_REST_Response($this->generate_token($user, false));
    }

    /**
     * Generate token
     *
     * @param WP_User $user The WP_User object.
     * @param bool $return_raw Whether or not to return as raw token string.
     *
     * @return WP_REST_Response|string Return as raw token string or as a formatted WP_REST_Response.
     */
    public function generate_token($user, $return_raw = true)
    {
        $secret_key = defined(self::JWT_AUTH_SECRET_KEY) ? JWT_AUTH_SECRET_KEY : false;
        $issued_at = time();
        $not_before = $issued_at;
        $not_before = apply_filters('jwt_auth_not_before', $not_before, $issued_at);
        $expire = $issued_at + (DAY_IN_SECONDS * 14);
        $expire = apply_filters('jwt_auth_expire', $expire, $issued_at);

        $payload = [
            'iss' => $this->get_iss(),
            'iat' => $issued_at,
            'nbf' => $not_before,
            'exp' => $expire,
            'data' => [
                'user' => [
                    'id' => $user->ID,
                ],
            ],
        ];

        $alg = $this->get_alg();

        // Let the user modify the token data before the sign.
        $token = JWT::encode(apply_filters('jwt_auth_payload', $payload, $user), $secret_key, $alg);

        $refreshToken = bin2hex(random_bytes(78));
        $cookieExpire = time() + 60 * 60 * 24 * 7;

        setcookie($this->refreshTokenKey, $refreshToken, $cookieExpire, COOKIEPATH, COOKIE_DOMAIN);
        update_user_meta($user->data->ID, $this->getRefreshCookieMetaName(), $refreshToken);

        // If return as raw token string.
        if ($return_raw) {
            return $token;
        }

        $userType = get_user_meta($user->data->ID, 'user_type');

        // The token is signed, now create object with basic info of the user.
        $response = [
            self::SUCCESS => true,
            self::STATUSCODE => 200,
            'code' => 'jwt_auth_valid_credential',
            self::MESSAGE => __('Credential is valid', self::JWT_AUTH),
            'data' => [
                'token' => $token,
                'id' => $user->ID,
                'email' => $user->user_email,
                'nicename' => $user->user_nicename,
                'firstName' => $user->first_name,
                'lastName' => $user->last_name,
                'displayName' => $user->display_name,
                'role' => $user->roles,
                'type' => count($userType) > 0 ? $userType[0] : 'global',
            ],
        ];

        // Let the user modify the data before send it back.
        return apply_filters('jwt_auth_valid_credential_response', $response, $user);
    }

    /**
     * @param $request
     * @return mixed|void|WP_Error
     */
    public function refresh_token($request)
    {
        $err = '';
        $token = $request->get_param('token');
		$tks = [];
        if($token)$tks = explode('.', $token);
        if (count($tks) != 3) {
            $err = __('Wrong number of segments.', self::JWT_AUTH);
        }
        if (empty($err)) {
            list($headb64, $bodyb64, $cryptob64) = $tks;
            try {
                $payload = JWT::jsonDecode(JWT::urlsafeB64Decode($bodyb64));

                // The Token is decoded now validate the iss.
                if ($payload->iss !== $this->get_iss()) {
                    // The iss do not match, return error.
                    return new WP_REST_Response(
                        [
                            self::SUCCESS    => false,
                            self::STATUSCODE => 403,
                            'code'       => 'jwt_auth_bad_iss',
                            self::MESSAGE    => __('The iss do not match with this server.', self::JWT_AUTH),
                            'data'       => [],
                        ]
                    );
                }

                // Check the user id existence in the token.
                if (! isset($payload->data->user->id)) {
                    // No user id in the token, abort!!
                    return new WP_REST_Response(
                        [
                            self::SUCCESS    => false,
                            self::STATUSCODE => 403,
                            'code'       => 'jwt_auth_bad_request',
                            self::MESSAGE    => __('User ID not found in the token.', self::JWT_AUTH),
                            'data'       => [],
                        ]
                    );
                }

                // So far so good, check if the given user id exists in db.
                $user = get_user_by('id', $payload->data->user->id);

                if (! $user) {
                    // No user id in the token, abort!!
                    return new WP_REST_Response(
                        [
                            self::SUCCESS    => false,
                            self::STATUSCODE => 403,
                            'code'       => 'jwt_auth_user_not_found',
                            self::MESSAGE    => __("User doesn't exist", self::JWT_AUTH),
                            'data'       => [],
                        ]
                    );
                }
                $token = $this->generate_token($user, false);

                //setcookie('utk', $token, time() + $this->getLoginExpired(), COOKIEPATH, COOKIE_DOMAIN);
                return $token;
            } catch (Exception $e) {
                // Something is wrong when trying to decode the token, return error response.
                return new WP_REST_Response(
                    [
                        self::SUCCESS    => false,
                        self::STATUSCODE => 403,
                        'code'       => 'jwt_auth_invalid_token',
                        self::MESSAGE    => $e->getMessage(),
                        'data'       => [],
                    ]
                );
            }
        }

        if (! empty($err)) {
            return new WP_REST_Response(
                [
                    self::SUCCESS    => false,
                    self::STATUSCODE => 500,
                    'code'       => 'jwt_auth_invalid_token',
                    self::MESSAGE    => $err,
                    'data'       => [],
                ]
            );
        }
    }

    public function validate_auth_token($token)
    {
        if (! $token) {
            return new WP_REST_Response(
                [
                    self::SUCCESS    => false,
                    self::STATUSCODE => 403,
                    'code'       => 'jwt_auth_bad_auth_header',
                    self::MESSAGE    => __('Authorization header not found.', self::JWT_AUTH),
                    'data'       => [],
                ]
            );
        }

        // Get the Secret Key.
        $secret_key = defined(self::JWT_AUTH_SECRET_KEY) ? JWT_AUTH_SECRET_KEY : false;

        if (! $secret_key) {
            return new WP_REST_Response(
                [
                    self::SUCCESS    => false,
                    self::STATUSCODE => 403,
                    'code'       => 'jwt_auth_bad_config',
                    self::MESSAGE    => __('JWT is not configurated properly.', self::JWT_AUTH),
                    'data'       => [],
                ]
            );
        }

        // Try to decode the token.
        try {
            $alg = $this->get_alg();
            $payload = JWT::decode($token, new \Firebase\JWT\Key($secret_key, $alg));

            // The Token is decoded now validate the iss.
            if ($payload->iss !== $this->get_iss()) {
                // The iss do not match, return error.
                return new WP_REST_Response(
                    [
                        self::SUCCESS    => false,
                        self::STATUSCODE => 403,
                        'code'       => 'jwt_auth_bad_iss',
                        self::MESSAGE    => __('The iss do not match with this server.', self::JWT_AUTH),
                        'data'       => [],
                    ]
                );
            }

            // Check the user id existence in the token.
            if (! isset($payload->data->user->id)) {
                // No user id in the token, abort!!
                return new WP_REST_Response(
                    [
                        self::SUCCESS    => false,
                        self::STATUSCODE => 403,
                        'code'       => 'jwt_auth_bad_request',
                        self::MESSAGE    => __('User ID not found in the token.', self::JWT_AUTH),
                        'data'       => [],
                    ]
                );
            }

            // So far so good, check if the given user id exists in db.
            $user = get_user_by('id', $payload->data->user->id);

            if (! $user) {
                // No user id in the token, abort!!
                return new WP_REST_Response(
                    [
                        self::SUCCESS    => false,
                        self::STATUSCODE => 403,
                        'code'       => 'jwt_auth_user_not_found',
                        self::MESSAGE    => __("User doesn't exist", self::JWT_AUTH),
                        'data'       => [],
                    ]
                );
            }

            // Everything looks good return the token if $output is set to false.
            return $payload;
        } catch (Exception $e) {
            // Something is wrong when trying to decode the token, return error response.
            return new WP_REST_Response(
                [
                    self::SUCCESS    => false,
                    self::STATUSCODE => 403,
                    'code'       => 'jwt_auth_invalid_token',
                    self::MESSAGE    => $e->getMessage(),
                    'data'       => [],
                ]
            );
        }
    }

    private function getRefreshCookieMetaName()
    {
        $computerId = $_SERVER['HTTP_USER_AGENT'].($_SERVER['SERVER_ADDR'] ?? $_SERVER['LOCAL_ADDR'] ?? '127.0.0.1').($_SERVER['SERVER_PORT'] ?? $_SERVER['LOCAL_PORT'] ?? '80').$_SERVER['REMOTE_ADDR'];

        return $this->refreshTokenKey.'_'.md5($computerId);
    }

    /**
     * Get User from cookie token.
     *
     * @return bool|WP_Error|WP_User
     */
    private function getUserByUserCookieToken()
    {
        if (empty($_COOKIE[$this->refreshTokenKey])) {
            return new WP_Error(
                'jwt_auth_refresh_token_not_found',
                'The Refresh Token not found.',
                [
                    'status' => 404,
                ]
            );
        }

        $users = get_users(
            [
                'meta_key' => $this->getRefreshCookieMetaName(),
                'meta_value' => $_COOKIE[$this->refreshTokenKey],
                'number' => 1,
                'count_total' => false,
            ]
        );

        if (! count($users)) {
            return new WP_Error(
                'jwt_auth_user_not_found',
                'The user not found.',
                [
                    'status' => 404,
                ]
            );
        }

        $user = reset($users);

        return get_user_by('ID', $user->ID);
    }

    protected function validateDataPassword($password, $newPassword, $reNewPassword)
    {
        $error = new WP_Error();
        if (empty($password)) {
            $error->add('empty_password', __('The Password field is empty.'));
        } elseif (empty($newPassword)) {
            $error->add('empty_new_password', __('The New Password field is empty.'));
        } elseif (strlen($newPassword) < 7) {
            $error->add('valid_password', __('Your password must be at least seven characters'));
        } elseif ($newPassword != $reNewPassword) {
            $error->add('password_reset_mismatch', __('The passwords do not match.'));
        } elseif ($password == $newPassword) {
            $error->add('valid_password', __('New password cannot match old password'));
        }

        return $error;
    }

    /**
     * @param $request
     * @return mixed|void|WP_Error
     */
    public function changePassword($request)
    {
        $password = $request->get_param('password');
        $newPassword = $request->get_param('new_password');
        $reNewPassword = $request->get_param('re_new_password');
        $user = wp_get_current_user();
        $error = new WP_Error();

        if ($user) {
            $error = $this->validateDataPassword($password, $newPassword, $reNewPassword);

            if (! $error->has_errors() && ! wp_check_password($password, $user->user_pass, $user->ID)) {
                $error->add(
                    'incorrect_password',
                    __('The Password you entered for your account is incorrect.')
                );
            }

            if (! $error->has_errors()) {
                wp_set_password($newPassword, $user->ID);

                $userId = $user->ID;

                $userData['ID'] = $userId; //user ID
                $userData['user_pass'] = $newPassword;
                wp_update_user($userData);
            }
        } else {
            $error->add('valid_user', __('Not get data user current'));
        }

        if (! empty($error->errors)) {
            return new WP_REST_Response(
                [
                    self::SUCCESS    => false,
                    self::STATUSCODE => 500,
                    'code'       => $error->get_error_code(),
                    self::MESSAGE    => $error->get_error_message(),
                    'data'       => [],
                ]
            );
        }

        return new WP_REST_Response(
            [
                self::SUCCESS    => true,
                self::STATUSCODE => 200,
                'code'       => self::SUCCESS,
                self::MESSAGE    => self::SUCCESS,
                'data'       => [],
            ]
        );
    }

    protected function getLoginExpired()
    {
        $expiredValue = get_option('login_expired_time');
        if (empty($expiredValue) && defined('LOGIN_EXPIRED_TIME')) {
            $expiredValue = LOGIN_EXPIRED_TIME;
        } elseif (! defined('LOGIN_EXPIRED_TIME')) {
            $expiredValue = 604800;
        }

        return $expiredValue;
    }
}

$barnetAuth = new BarnetAuth();
