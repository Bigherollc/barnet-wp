<?php

class UserEntity extends BarnetEntity
{
    const BARNETCUSTOMER = "barnet-customer";
    const DIGITALCODE = "digital_code";
    const POSTTYPE = "post_type";
    const RESPONSE = "response";
    const METAS = "metas";
    const PRODUCT_DESCRIPTION_LOGGED = "product_description_logged";

    protected $user;
    protected $defaultData;

    private $username;
    private $email;
    private $displayName;
    private $joinAt;
    private $role;
    private $type;
    private $userExtraInfo;
    private $userExtraType;

    public function __construct($id = null, $isPostType = false, $includeData = array())
    {
        if (!isset($id)) {
            $currentUser = wp_get_current_user();
           
            if (isset($currentUser->data->ID)) {
                $id = $currentUser->data->ID;
            }
        }

        parent::__construct($id, $isPostType, $includeData);
        $this->user = get_user_by('id', $id);

        if ($this->user) {
            $this->username = $this->user->data->user_login;
            $this->email = $this->user->data->user_email;
            $this->displayName = $this->user->data->display_name;
            $this->joinAt = $this->user->data->user_registered;
            $this->role = $this->user->roles;
            $this->type = 'global';

            $userType = get_user_meta($this->user->data->ID, 'user_type');
            if (count($userType) > 0) {
                $this->type = $userType[0];
            }
            $userExtraInfo = get_user_meta($this->user->data->ID, 'user_extra_info');
            if (!empty($userExtraInfo) && is_array($userExtraInfo) &&
                count($userExtraInfo) > 0 && $this->isSerialized($userExtraInfo[0])) {
                $this->userExtraInfo = unserialize($userExtraInfo[0]);

            }
            $userExtraType = get_user_meta($this->user->data->ID, 'user_extra_type');
            if (count($userExtraType) > 0) {
                $this->userExtraType = $userExtraType[0];
            }
        }
    }

    /**
     * @return mixed
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return mixed
     */
    public function getUsername()
    {
        return $this->username;
    }

    /**
     * @return mixed
     */
    public function getEmail()
    {
        return $this->email;
    }

    /**
     * @return mixed
     */
    public function getDisplayName()
    {
        return $this->displayName;
    }

    /**
     * @return mixed
     */
    public function getJoinAt()
    {
        return $this->joinAt;
    }

    /**
     * @return mixed
     */
    public function getRole()
    {
        return $this->role;
    }

    /**
     * @return mixed
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * @return mixed
     */
    public function getUserExtraInfo()
    {
        return $this->userExtraInfo;
    }

    /**
     * @return mixed
     */
    public function getUserExtraType()
    {
        return $this->userExtraType;
    }

    public function getCustomers()
    {

        if (!isset($this->user)) {
            return null;
        }

        $dataDefault = $this->getPosts(self::BARNETCUSTOMER);
        $result = array();
        $dataDefault['data'] = array_values(
            array_filter($dataDefault['data'], function ($e) {
                $customerRolesText = "customer_roles";
                if (!empty($e[self::METAS][$customerRolesText])) {
                    if (is_string($e[self::METAS][$customerRolesText])) {
                        $e[self::METAS][$customerRolesText] = array($e[self::METAS][$customerRolesText]);
                    }

                    if (is_array($e[self::METAS][$customerRolesText])) {
                        if (count(array_intersect($e[self::METAS][$customerRolesText], $this->role)) > 0) {
                            return true;
                        }
                    }
                }


                return false;
            })
        );

        foreach ($dataDefault['data'] as $data) {
            $customer = new CustomerEntity(
                $data[self::RESPONSE]->ID,
                true,
                array('post' => $data[self::RESPONSE])
            );
            $result[] = $customer->toArray(BarnetEntity::$PUBLIC_ALL);
        }

        return $result;
    }

    public function getProductsByCustomer($customerId)
    {
        if (!isset($this->user)) {
            return null;
        }

        global $wpdb;
        $customer = $this->getPostById($customerId, self::BARNETCUSTOMER);
        $query = "SELECT DISTINCT `from` FROM wp_mb_relationships where `to` = $customerId AND type = 'digitals_to_customers'";
        $codeListId = array_map(function ($e) {
            return $e['from'];
        }, $wpdb->get_results($query, ARRAY_A));

        $result = array(
            'customer_id' => $customerId,
            'customer_title' => $customer['data'][self::RESPONSE]->post_title,
            'customer_modify' => $customer['data'][self::RESPONSE]->post_modified
        );

        if (count($codeListId) > 0) {
   foreach ($codeListId as $codeId) {
                $digitalCode = $this->getPostById($codeId, 'barnet-digital-code');
                $d_post=$digitalCode['data'][self::RESPONSE];
     			//if( is_array($d_post))print_r($d_post);
                if(isset($d_post)&&!( is_array($d_post)))if ($d_post->post_status != null) {
                    $digitalCodeText = $digitalCode['data'][self::METAS][self::DIGITALCODE];
                    $result['data'][] = array(
                        'digital_code_id' => $codeId,
                        'digital_code_title' => $d_post->post_title,
                        self::DIGITALCODE => is_array($digitalCodeText) && count($digitalCodeText) > 0 ?
                            $digitalCodeText[0] : $digitalCodeText,
                        'products' => $this->getProductsByDigitalCode($codeId)
                    );
                }
            }
        }

        return $result;
    }

    public function getProductsByDigitalCode($digitalCodeId)
    {
        if (!isset($this->user)) {
            return null;
        }

        global $wpdb;

        if (!isset($this->defaultData)) {
            $resultProduct = $this->getPosts("barnet-product", false);
            $resultProducts = array();
            if (isset($resultProduct['data'])) {
                foreach ($resultProduct['data'] as $data) {
                    $product = new ProductEntity(
                        $data[self::RESPONSE]->ID,
                        true,
                        array('post' => $data[self::RESPONSE])
                    );
                    $productResult = $product->toArray();
                    if (isset($productResult['taxonomies'])) {
                        unset($productResult['taxonomies']);
                    }

                    if (isset($productResult['widgets'])) {
                        unset($productResult['widgets']);
                    }
					if(isset($productResult['data']['inci_name']))$productResult['data']['inci_name'] = html_entity_decode($productResult['data']['inci_name']);

                    if(isset(($productResult['data'][self::PRODUCT_DESCRIPTION_LOGGED])))if (!is_array($productResult['data'][self::PRODUCT_DESCRIPTION_LOGGED])) {
                        $pDescArr = array($productResult['data'][self::PRODUCT_DESCRIPTION_LOGGED]);
                        $productResult['data'][self::PRODUCT_DESCRIPTION_LOGGED] = $pDescArr;
                    }
					if(isset($productResult['data'][self::PRODUCT_DESCRIPTION_LOGGED])){
						$pDescFormat = array_map(function ($e) {
							if(!$e)$e="";
							return html_entity_decode($e);
						}, $productResult['data'][self::PRODUCT_DESCRIPTION_LOGGED]);
					}

                    if(isset($pDescFormat))$productResult['data'][self::PRODUCT_DESCRIPTION_LOGGED] = $pDescFormat;
                    if (isset($productResult)) {
                        $resultProducts[] = $productResult;
                    }
                }
            }


            $this->defaultData = array_values($resultProducts);
        }

        $result = $this->defaultData;

        $query = "SELECT DISTINCT `from` FROM wp_mb_relationships where `to` = $digitalCodeId AND type = 'products_to_digitals'";

        $productFilterIds = array_map(function ($e) {
            return $e['from'];
        }, $wpdb->get_results($query, ARRAY_A));

        $result = array_filter($result, function ($e) use ($productFilterIds) {
			
            if(isset($e['data']['id']))return in_array($e['data']['id'], $productFilterIds);
			else return false;
        });

        return array_values($result);
    }

    protected function getPosts($type, $get_terms=true)
    {
        $response = array();

        $args = array(
            'numberposts' => -1,
            'orderby' => 'post_title',
            'order' => 'ASC',
            self::POSTTYPE => $type,
        );

        if (!$get_terms)
            $args['update_post_term_cache'] = false;

        $posts = get_posts($args);
        $postMetaManager = new BarnetPostMetaManager($posts);
        foreach ($posts as $post) {
            $response["data"][] = array(
                self::RESPONSE => $post,
                self::METAS => $postMetaManager->getMetaData($post->ID)
            );
        }

        return $response;
    }

    protected function getPostById($id, $type)
    {
          $args = [
            'p' => $id,
            self::POSTTYPE => $type,
        ];
        $post = get_posts($args);
        $metas = get_post_meta($id);
        $f_post=$post;
        if(isset($post[0]))$f_post=$post[0];
        return array(
            "data" => array(
                self::RESPONSE => $f_post,
                self::METAS => $metas
            )
        );
    }

    protected function getPostBySlug($slugName, $type)
    {
        $args = [
            'name' => $slugName,
            self::POSTTYPE => $type,
            'post_status' => 'publish'
        ];
        $post = get_posts($args);
        $metas = get_post_meta($post[0]->ID);

        return array(
            "data" => array(
                self::RESPONSE => $post[0],
                self::METAS => $metas
            )
        );
    }
}
