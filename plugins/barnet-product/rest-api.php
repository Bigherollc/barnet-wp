<?php
//use WP_REST_Response;
class BarnetRestAPI
{
    const BARNET_PRODUCT = "barnet-product";
    const BARNET_RESOURCE = "barnet-resource";
    const ACTIVE = "active";
    const CATEGORY = "category";
    const PRODUCT_TYPE = "product_type";
    const SYSTEM = "system";
    const RESOURCE_TYPE = "resource-type";
    const RESPONSE = "response";
    const EMAIL = "email";
    const SUCCESS = "success";
    const NEWSLETTER = "newsletter";
    const ORDER = "order";
    const NUMBERPOSTS = "numberposts";
    const POST_TYPE = "post_type";
    const POST_STATUS = "post_status";
    const PUBLISH = "publish";
    const VALUE = "value";
    const COMPARE = "compare";
    const TAXONOMY = "taxonomy";
    const METAS = "metas";
    const DOMAIN = "domain";
    const STATUSCODE = "statusCode";
    const JWT_AUTH_BAD_AUTH_HEADER = "jwt_auth_bad_auth_header";
    const JWT_AUTH = "jwt-auth";
    const MESSAGE = "message";
    const BAD_REQUEST = "bad_request";
    const RELATIONSHIP = "relationship";
    const ADDRESS = "address";
    const COUNTRY = "country";
    const PROVINCE = "province";
    const CITY = "city";
    const PHONE = "phone";
    const TOKEN = "token";

    protected $secure;
    protected $auth;
    protected $relationshipManager;
    protected $medias;
    protected $mediaAttachmentMetadata;
    protected $sessionCacheManager;

    const POST_PER_PAGE = -1;
    protected static $RELATIONSHIP_ID = array(
        'products_to_formulas',
        'products_to_concepts',
        'products_to_pattributes',
        'products_to_products',
        'products_to_digital-code',
    );

    protected static $SYNC_POST_TYPE = array(
        self::BARNET_PRODUCT,
        'barnet-concept-book',
        'barnet-formula',
        'barnet-concept',
        'barnet-customer',
        'barnet-digital-code',
        'barnet-pattribute',
        'barnet-pconcept',
        'barnet-resource',
        'attachment'
    );

    public function __construct()
    {
        $this->secure = new BarnetSimpleSecure();
        $this->auth = new \JWTAuth\BarnetAuth();
        $this->relationshipManager = new BarnetRelationshipManager();
        $this->sessionCacheManager = new BarnetSessionCacheManager();
    }

    public function postDebug($request)
    {
        $debugLog = __DIR__ . "/../../uploads/wp-rest-log-" . date("Ymd") . ".log";
        $dateLog = date("[Y-m-d H:i:s] ");

        if ($_SERVER['REQUEST_METHOD'] != 'POST') {
            return new WP_REST_Response(
                array(
                    self::SUCCESS => false,
                    self::STATUSCODE => 405,
                    'code' => 'method_not_allow',
                    self::MESSAGE => "Allow method POST"
                )
            );
        }

        $requestBody = $request->get_body();
        $requestArray = json_decode($requestBody, true);
        if (!isset($requestArray['body'])) {
            return new WP_REST_Response(
                array(
                    self::SUCCESS => false,
                    self::STATUSCODE => 400,
                    'code' => 'bad_request',
                    self::MESSAGE => "body must be required"
                )
            );
        }

        $body = $requestArray['body'];
        file_put_contents($debugLog, "$dateLog$body\n", FILE_APPEND);
        return new WP_REST_Response(
            array(
                self::SUCCESS => true,
                self::STATUSCODE => 200
            )
        );
    }

    public function getDebug($request)
    {
        if (!isset($request['id'])) {
            return new WP_REST_Response(
                array(
                    self::SUCCESS => false,
                    self::STATUSCODE => 400,
                    'code' => 'bad_request',
                    self::MESSAGE => "id must be required"
                )
            );
        }

        $file = __DIR__ . "/../../uploads/wp-rest-log-{$request['id']}.log";

        if (!file_exists($file)) {
            return new WP_REST_Response(
                array(
                    self::SUCCESS => false,
                    self::STATUSCODE => 404,
                    'code' => 'file_not_found',
                    self::MESSAGE => "Not found rest log"
                )
            );
        }

        echo file_get_contents($file);
        exit;
    }

    public function getConceptBooks($request)
    {
        $dataDefault = $this->getPosts($request, "barnet-concept-book");
        $result = array();
        if(isset($dataDefault['data'])){
            foreach ($dataDefault['data'] as $data) {
                $conceptBook = new ConceptBookEntity(
                    $data[self::RESPONSE]['ID'],
                    true,
                    array(
                        'post' => $data[self::RESPONSE],
                        'meta' => $data[self::METAS]
                    )
                );
                $result[] = $conceptBook->toArrayPublic(BarnetEntity::$PUBLIC_ALL);
            }
            return array_values(array_filter($result));
        }
    }

   
    public function getConceptLanding_cache($request)
    {
		
	/*
		$activeProducts= $this->getProducts(
                    array(
                        'type' => self::ACTIVE
                    )
                );
		
		
		
		
					
		$activeTaxonomies=$this->getTaxonomies(
                    array(
                        'type' => 'product-category',
                        self::PRODUCT_TYPE => self::ACTIVE
                    )
                );
		
		$systemProduct= $this->getProducts(
                    array(
                        'type' => self::SYSTEM
                    )
                );
		
		$systemCategory= self::CATEGORY => $this->getTaxonomies(
                    array(
                        'type' => 'product-category',
                        self::PRODUCT_TYPE => self::SYSTEM
                    )
                );
		
		$formula= $this->getFormulas($request);
		$formula_taxonomy=$this->getTaxonomies(
                    array(
                        'type' => 'formula-category'
                    )
                );
		$resource=$this->getResources($request);
		$resouce_folder_taxnomy=$this->getTaxonomies(
                    array(
                        'type' => 'formula-category'
                    )
                );
			
		$resouce_folder_taxnomy=$this->getTaxonomies(
                    array(
                        'type' => 'formula-category'
                    )
                );	
		$resouce_taxonomy=$this->getTaxonomies(
                        array(
                            'type' => self::RESOURCE_TYPE
                        )
                    );
		print_r($resouce_taxonomy);
		//ettro();
		*/

        //print_r($request);
        $result=array(
		

            'formula' => array(
                'data' => $this->getFormulas($request),
                
                self::CATEGORY => $this->getTaxonomies(
                    array(
                        'type' => 'formula-category'
                    )
                )
                
            ),
            'resource' => array(
                'data' => $this->getResources($request),
                
                self::CATEGORY => array(
                    'resource-folder' => $this->getTaxonomies(
                        array(
                            'type' => 'resource-folder'
                        )
                    ),
                    self::RESOURCE_TYPE => $this->getTaxonomies(
                        array(
                            'type' => self::RESOURCE_TYPE
                        )
                    )
                )
                
            ),
        );
        
        
        $product_types = get_terms(array(
            'taxonomy'   => 'product-type',
            'fields'   =>  'names',
            'hide_empty' => false,
        ) );
        foreach($product_types as $product_type){
            $result[$product_type] = array(
			
                'data' => $this->getProducts(
                    array(
                        'type' => $product_type
                    )
                ),
                
                self::CATEGORY => $this->getTaxonomies(
                    array(
                        'type' => 'product-category',
                        self::PRODUCT_TYPE => $product_type
                    )
                )
                
                
            );
        }	
        return $result;
		
    }
    

    public function getConceptLanding($request)
    {	
        global $wpdb;
        $caching_setting_table = $wpdb->prefix . 'caching_setting_table';
        $api_name = 'landing_concept_internal_cache';  
        $query = $wpdb->prepare("SELECT data FROM $caching_setting_table WHERE api_name = %s", $api_name);
        $response_data = $wpdb->get_var($query);   
        if($response_data)$response_array = maybe_unserialize($response_data);    
        else $response_array = $this->getConceptLanding_cache($request);   
        return $response_array;
		
    }


    public function sync($request)
    {
        if (!isset($request['from'])) {
            return null;
        }
        
        $result = array();

        $from = $request['from'];
        $dateTime = new DateTime("@$from");
        //Convert to local time
		
        $localTime = get_option('timezone_string');
        $dateTime->setTimezone(new DateTimeZone($localTime)); 
        $dateTimeLocal = $dateTime->format("Y-m-d H:i:s");
        $dateT = strtotime($dateTimeLocal);
		//$dtimestringg=gmdate("Y-m-d\TH:i:s\Z", $dateT);
        //$dTime = new DateTime($dtimestringg);
		$dTime = new DateTime("@$dateT");
        $posts = array();
        foreach (static::$SYNC_POST_TYPE as $postType) {
            $posts[$postType] = $this->getPostsByCreatedTime($postType, $dTime);
        }
		
		
        $result["deleted"] = array();

        $resourcesDeleted = $this->getDeletedResourceByRole();
        if (!empty($resourcesDeleted)&& $resourcesDeleted ) {
            foreach ($resourcesDeleted as $resourcesDeleteds) {
                $result["deleted"][] = (object)$resourcesDeleteds;
            }
        }

        $conceptsDeleted = $this->getDeletedConceptByRole();
        if (!empty($conceptsDeleted)&&$conceptsDeleted) {
            foreach ($conceptsDeleted as $conceptsDeleteds) {
                $result["deleted"][] = (object)$conceptsDeleteds;
            }
        }

        $productsDeleted = $this->getDeletedProductByRole();
        if (!empty($productsDeleted)&& $productsDeleted) {
            foreach ($productsDeleted as $productsDeleteds) {
                $result["deleted"][] = (object)$productsDeleteds;
            }
        }

        $formulasDeleted = $this->getDeletedFormulaByRole();
        if (!empty($formulasDeleted) && $formulasDeleted) {
            foreach ($formulasDeleted as $formulasDeleteds) {
                $result["deleted"][] = (object)$formulasDeleteds;
            }
        }

        $getDeletedConceptBook = $this->getDeletedConceptBook();
        if (!empty($getDeletedConceptBook) && $getDeletedConceptBook) {
            foreach ($getDeletedConceptBook as $conceptBDeleteds) {
                $result["deleted"][] = (object)$conceptBDeleteds;
            }
        }
        global $wpdb;
        foreach ($posts as $postType => $dataCollection) {
            if ($postType == "attachment") {
                if (!isset($dataCollection['data'])) {
                    continue;
                }
                foreach ($dataCollection['data'] as $data) {
                    $result["medias"][] = $data[self::RESPONSE];
                }
            } else if ($postType == "barnet-pconcept") {
                if (!isset($dataCollection['data'])) {
                    continue;
                }
                foreach ($dataCollection['data'] as $data) {
                    $id = $data[self::RESPONSE]->ID;
                    $queryC = "SELECT `to` FROM wp_mb_relationships WHERE `from` = {$id} AND `type` = 'pconcepts_to_concepts'";
                    $resultListObjConcept = $wpdb->get_results($queryC, ARRAY_A);
                    $listObjId = array_map(function ($e) {
                        return $e['to'];
                    }, $resultListObjConcept);
        
                    $queryC = "SELECT `to` FROM wp_mb_relationships WHERE `from` = {$id} AND `type` = 'pconcepts_to_products'";
                    $resultListObjProduct = $wpdb->get_results($queryC, ARRAY_A);
                    $listObjId = array_map(function ($e) {
                        return $e['to'];
                    }, $resultListObjProduct);
        
                    $product_concept_description = '';
                    $product_concept_description = get_post_meta(intval($id), 'product_concept_description', TRUE);
        
                    $product_concept_right_text = '';
                    $product_concept_right_text = get_post_meta(intval($id), 'product_concept_right_text', TRUE);
        
                    $product_concept_order = 0;
                    $product_concept_order = get_post_meta(intval($id), 'product_concept_order', TRUE);            
        
                    //Optimize pconcept
					if($resultListObjConcept!=[]&&isset($resultListObjProduct[0])){
						$arrayNewData = array();
						$arrayNewData['data']['id']= intval($id);
						$arrayNewData['data']['product_id'] = intval($resultListObjProduct[0]['to']);
						$arrayNewData['data']['concept_id'] = intval($resultListObjConcept[0]['to']);
						$arrayNewData['data']['product_concept_description'] = $product_concept_description;
						$arrayNewData['data']['product_concept_right_text'] = $product_concept_right_text;
						$arrayNewData['data']['product_concept_order'] = intval($product_concept_order);
						$get_terms = get_the_terms( $id, 'sub-concept-category' );
						$array_term = array();
						if($get_terms)foreach ($get_terms as $term) {
							$array_term[] = $term->term_id;
						}
						$arrayNewData['data']['taxonomy_subconcept'] = $array_term;
						$result[$postType][] = $arrayNewData;
					}
                }
            } else {
                $camelPostType = DataHelper::snake2CamelCase($postType);
                $entityClass = substr($camelPostType, 6, strlen($camelPostType) - 6) . "Entity";
                if ($entityClass == "PconceptEntity") {
                    $entityClass = "ProductConceptEntity";
                } else if ($entityClass == "PattributeEntity") {
                    $entityClass = "ProductAttributeEntity";
                }

                if (!isset($dataCollection['data'])) {
                    continue;
                }

                foreach ($dataCollection['data'] as $data) {
                    if (!class_exists($entityClass)) {
                        continue;
                    }

                    $entity = new $entityClass($data[self::RESPONSE]->ID);
                    $data_check = $entity->toArray(BarnetEntity::$PUBLIC_ALL);
                    if ($data_check != null) {
                        $result[$postType][] = $entity->toArray(BarnetEntity::$PUBLIC_ALL);
                    } else {
                        $roleDeletedResource = array("post_id" => (string)$data[self::RESPONSE]->ID, "post_type" => $data[self::RESPONSE]->post_type, "timestamp" => (string)strtotime($data[self::RESPONSE]->post_modified));
                        //$result["deleted"][] = (object)$roleDeletedResource;
                    }
                }
            }

        }

        // get list tax update
        $result["update-tax"] = array();
        $barnetTaxUpdate = $wpdb->get_results("SELECT tax_id FROM $wpdb->barnet_tax_update WHERE `timestamp` >= $from");
        $arrTaxUpdate = array();
        foreach ($barnetTaxUpdate as $k => $v) {
            $taxId = intval($v->tax_id);
            if ($taxId > 0) {
                $arrTaxUpdate[$taxId] = $taxId;
            }
        }
        if (count($arrTaxUpdate) > 0) {
            $terms = get_terms(array(
                'include' => $arrTaxUpdate,
                'hide_empty' => false,
                'meta_load_field' => 1
            ));
            if (!empty($terms)) {
                $result["update-tax"] = $terms;
            }
        }

        $barnetDetete = $wpdb->get_results("SELECT * FROM $wpdb->barnet_deleted WHERE `timestamp` >= $from");
        $result["deleted-tax"] = array();
        $arrayNotAvaiable = $this->getNotAvaiableTerm('resource-folder', 'barnet-resource');
        $user = wp_get_current_user();
        foreach ($arrayNotAvaiable as $termId) {
            $tmp = get_term( $termId, 'resource-folder' );
            if ($tmp->term_id != 1353) {
                $folderNotAvaiable = array("term_id" => (string)$tmp->term_id, "taxonomy" => (string)$tmp->taxonomy, "timestamp" => '1635750000');
                $result["deleted-tax"][] = (object)$folderNotAvaiable;
            } else {
                $userCustomer = array();
                if (class_exists('UserEntity')) {
                    $userEntity = new UserEntity($user->ID);
                    $userCustomer = $userEntity->getCustomers();
                }
                if (empty($userCustomer)) {
                    $folderNotAvaiable = array("term_id" => (string)$tmp->term_id, "taxonomy" => (string)$tmp->taxonomy, "timestamp" => '1635750000');
                    $result["deleted-tax"][] = (object)$folderNotAvaiable;
                } else {
                    $countDigitals = 0;
                    foreach ($userCustomer as $u) {
                        if (!empty($u['data']['post_title']) && !empty($u['relationship']['digitals'])) {
                            $countDigitals++;
                        }
                    }
                    if ($countDigitals == 0) {
                        $folderNotAvaiable = array("term_id" => (string)$tmp->term_id, "taxonomy" => (string)$tmp->taxonomy, "timestamp" => '1635750000');
                        $result["deleted-tax"][] = (object)$folderNotAvaiable;
                    }
                }
            }
        }

        foreach ($barnetDetete as $k => $v) {
            if (isset($v->post_type) && (in_array($v->post_type, array('resource-type', 'resource-folder', 'category')) || strpos($v->post_type, '-category') !== false)) {
                $result["deleted-tax"][] = array ('term_id' => $v->post_id, 'taxonomy' => $v->post_type, 'timestamp' => $v->timestamp);
            } 
            else {
                $result["deleted"][] = $v;
            }
        }

        $userExtraInfo = get_user_meta($user->ID, 'user_extra_info', '');
        if (!empty($userExtraInfo) && is_array($userExtraInfo)) {
            $userExtraInfo = unserialize($userExtraInfo[0]);
            if (isset($userExtraInfo['flag_user_update'])) {
                if ($from <= strtotime($userExtraInfo['flag_user_update'])) {
                    $result["flag_user_update"] = true;
                } else {
                    $result["flag_user_update"] = false;
                }
            }
        }
		
        return $result;
    }

    public function getAllTerm($termName) {
        $terms = get_terms([
            'taxonomy' => $termName,
            'hide_empty' => false,
        ]);
        $termsSendApp = array();
        foreach ($terms as $term) {
            $term_vals = get_term_meta($term->term_id);
            foreach ($term_vals as $key=>$val) {
                if($key == 'show_app' && $val[0] == 1) {
                    array_push($termsSendApp, $term);
                }
            }
        }
        return $termsSendApp;
    }

    public function getAllPostByTerm($termName, $termId, $postType) {
        $allPost = array();
        $allPost = get_posts(array(
            'post_type' => $postType,
            'tax_query' => array(
                array(
                'taxonomy' => $termName,
                'field' => 'term_id',
                'terms' => $termId)
            ))
        );
        $termchildren = get_terms( $termName, array( 'parent' => $termId, 'orderby' => 'slug', 'hide_empty' => false ) );
        if (!empty($termchildren)) {
            foreach ($termchildren as $child) {
                $term_vals = get_term_meta($child->term_id);
                foreach ($term_vals as $key=>$val) {
                    if($key == 'show_app' && $val[0] == 1) {
                        $allPostChild = get_posts(array(
                            'post_type' => $postType,
                            'tax_query' => array(
                                array(
                                'taxonomy' => $termName,
                                'field' => 'term_id',
                                'terms' => $child->term_id)
                            ))
                        );
                        foreach ($allPostChild as $post) {
                            array_push($allPost, $post);
                        }
                    }
                }
            }
        }

        return $allPost;
    }

    public function getResourcesByTerm($termName, $termId, $postType) {
        $allPost = array();
        $args = array(
            'post_type' => $postType,
            'posts_per_page'=> -1,
            'tax_query' => array(
            array(
                'taxonomy' => $termName,
                'field' => 'term_id',
                'terms' => $termId
             )
          )
        );
        $query = new WP_Query( $args );
        $allPost = $query->posts;
        foreach ($allPost as $key => $post) {
            $resource = new ResourceEntity($post->ID, true, array('post' => $post));
            $show_resource = '';
            $show_resource = get_post_meta($post->ID, 'show_resource', TRUE);
            if ($show_resource != 1 && $resource->getResourceMediaType() == 'application/pdf') {
                unset($allPost[$key]);
            }
        }

        return $allPost;
    }

    public function getNotAvaiableTerm($termName, $postType) {
        $termsSendApp = array();
        $termsSendApp = $this->getAllTerm($termName);
        $arrayNotAvaiable = array();
        $arrayAvaiable = array();
        $userEntity = new UserEntity();
        $role = $userEntity->getRole();
        $globalText = "global";
        $usrType = $userEntity->getType();
        $userType = isset($usrType) ? $usrType : $globalText;
        foreach ($termsSendApp as $tmp) {
            $allPost = $this->getAllPostByTerm($termName, $tmp->term_id, $postType);
            if (empty($allPost)) {
                array_push($arrayNotAvaiable, $tmp->term_id);
            } else {
                foreach ($allPost as $post) {
                    $resource = new ResourceEntity($post->ID, true, array('post' => $post));
                    $resourceArea = $resource->getResourceArea();
                    $countMedia = 0;
                    $countRole = 0;
                    $countRole2 = 0;
    
                    $postContent = get_post_meta($post->ID);
                    $mediaID = $postContent['resource_media'][0];
                    $type = get_post_mime_type($mediaID);
                    $arrayType = array('application/pdf', 'diagram', 'video/mp4');
    
                    if (isset($postContent['resource_roles']) && !empty($postContent['resource_roles'])) {
                        foreach ($postContent['resource_roles'] as $item) {
                            if ( in_array($item, $role) ) {
                                $countRole++;
                            }
                        }
                    } else {
                        if ( !in_array($type, $arrayType) ) {
                            $countRole2 = 0;
                        } else {
                            $countRole2++;
                        }
                    }
    
                    if ( in_array($type, $arrayType) ) {
                        $countMedia++;
                    }
    
                    if ($countMedia == 0 || ($countRole2 == 0 && $countRole == 0 && !in_array('administrator', $role)) || (!in_array($resourceArea, array($userType, $globalText)) && $userType != $globalText && !in_array('administrator', $role))) {
                        array_push($arrayNotAvaiable, $tmp->term_id);
                    //} else if (!in_array('administrator', $role)) {
                    } else if(in_array($resourceArea, array($userType, $globalText)) || $userType == $globalText || in_array('administrator', $role)) {
                        array_push($arrayAvaiable, $tmp->term_id);
                    }
                }
            }
        }
        
        $arrayNotAvaiable = array_unique($arrayNotAvaiable);
        $arrayAvaiable = array_unique($arrayAvaiable);
        foreach ($arrayNotAvaiable as $k => $v) {
            if (in_array($v, $arrayAvaiable)) {
                unset($arrayNotAvaiable[$k]);
            }
        }
         return $arrayNotAvaiable;
    }

    public function getDeletedConceptBook() {
        $userEntity = new UserEntity();
        $usrType = $userEntity->getType();
        $conceptBookDeleted = array();
    
        $query_args = array(
            'post_type' => 'barnet-concept-book',
            'posts_per_page' => -1,
            'fields' => 'ids, post_type, post_modified',
            'post_status' => null,
        );
        $query = new WP_Query($query_args);
        $administrator = "administrator";
        $globalText = "global";
        $userType = isset($usrType) ? $usrType : $globalText;
    
        foreach($query->posts as $post) {
            $conceptBook = new ConceptBookEntity($post->ID);
            $conceptBookAre = $conceptBook->getConceptBookArea();
    
            if (!in_array($conceptBookAre, array($userType, $globalText)) && $userType != $globalText) {
                $roleDeletedConceptB = array("post_id" => (string)$post->ID, "post_type" => $post->post_type, "timestamp" => (string)strtotime($post->post_modified));
                $conceptBookDeleted[] = $roleDeletedConceptB;
            }
    
        }
        return $conceptBookDeleted;
    }

    public function getDeletedConceptByRole() {
        $userEntity = new UserEntity();
        $userRoles = $userEntity->getRole();
        $usrType = $userEntity->getType();
        $conceptsDeleted = array();

        $query_args = array(
            'post_type' => 'barnet-concept',
            'posts_per_page' => -1,
            'fields' => 'ids, post_type, post_modified',
            'post_status' => null,
        );
        $query = new WP_Query($query_args);
        $roleText = "roles";
        $administrator = "administrator";
        $globalText = "global";
        $userType = isset($usrType) ? $usrType : $globalText;

        foreach($query->posts as $post) {
            $concept = new ConceptEntity($post->ID);
            $conceptArea = $concept->getConceptArea();
            $roleText = "roles";
            $roles = $concept->getRelationship(array('concepts_to_roles'));
            if (isset($roles[$roleText]) && count($roles[$roleText]) > 0) {
                $roles[$roleText] = array_map(function ($e) {
                    if (is_array($e)) {
                        return strtolower($e['data']['post_title']);
                    }

                    return strtolower($e->post_title);
                }, $roles[$roleText]);
                $roles = array_values($roles[$roleText]);
            } else {
                $roles = array();
            }
            $count = 0;
            if (!in_array($administrator, $userRoles)) {
                if (!empty($roles)) {
                    foreach ($roles as $role) {
                        if (in_array($role, $userRoles)) {
                            $count++;
                        }
                    }
                    if ($count == 0 || (!in_array($conceptArea, array($userType, $globalText)) && $userType != $globalText)) {
                        $roleDeletedConcept = array("post_id" => (string)$post->ID, "post_type" => $post->post_type, "timestamp" => (string)strtotime($post->post_modified));
                        $conceptsDeleted[] = $roleDeletedConcept;
                    }
                } else {
                    if (!in_array($conceptArea, array($userType, $globalText)) && $userType != $globalText) {
                        $roleDeletedConcept = array("post_id" => (string)$post->ID, "post_type" => $post->post_type, "timestamp" => (string)strtotime($post->post_modified));
                        $conceptsDeleted[] = $roleDeletedConcept;
                    }
                }
            }
        }
        return $conceptsDeleted;
    }

    public function getDeletedProductByRole() {
        $userEntity = new UserEntity();
        $userRoles = $userEntity->getRole();
        $usrType = $userEntity->getType();
        $productsDeleted = array();
        $query_args = array(
            'post_type' => 'barnet-product',
            'posts_per_page' => -1,
            'fields' => 'ids, post_type, post_modified',
            'post_status' => null,
        );
        $query = new WP_Query($query_args);
        $roleText = "roles";
        $administrator = "administrator";
        $globalText = "global";
        $userType = isset($usrType) ? $usrType : $globalText;

        foreach($query->posts as $post) {
            $product = new ProductEntity($post->ID);
            $productArea = $product->getProductArea();
            $roleText = "roles";
            $roles = $product->getRelationship(array('products_to_roles'));
            if (isset($roles[$roleText]) && count($roles[$roleText]) > 0) {
                $roles[$roleText] = array_map(function ($e) {
                    if (is_array($e)) {
                        return strtolower($e['data']['post_title']);
                    }

                    return strtolower($e->post_title);
                }, $roles[$roleText]);
                $roles = array_values($roles[$roleText]);
            } else {
                $roles = array();
            }
            $count = 0;
            if (!in_array($administrator, $userRoles)) {
                if (!empty($roles)) {
                    foreach ($roles as $role) {
                        if (in_array($role, $userRoles)) {
                            $count++;
                        }
                    }
                    if ($count == 0 || (!in_array($productArea, array($userType, $globalText)) && $userType != $globalText)) {
                        $roleDeletedProduct = array("post_id" => (string)$post->ID, "post_type" => $post->post_type, "timestamp" => (string)strtotime($post->post_modified));
                        $productsDeleted[] = $roleDeletedProduct;
                    }
                } else {
                    if (!in_array($productArea, array($userType, $globalText)) && $userType != $globalText) {
                        $roleDeletedProduct = array("post_id" => (string)$post->ID, "post_type" => $post->post_type, "timestamp" => (string)strtotime($post->post_modified));
                        $productsDeleted[] = $roleDeletedProduct;
                    }
                }
            }
        }
        return $productsDeleted;
    }

    public function getDeletedFormulaByRole() {
        $userEntity = new UserEntity();
        $userRoles = $userEntity->getRole();
        $usrType = $userEntity->getType();
        $formulasDeleted = array();
        $query_args = array(
            'post_type' => 'barnet-formula',
            'posts_per_page' => -1,
            'fields' => 'ids, post_type, post_modified',
            'post_status' => null,
        );
        $query = new WP_Query($query_args);
        $roleText = "roles";
        $administrator = "administrator";
        $globalText = "global";
        $userType = isset($usrType) ? $usrType : $globalText;

        foreach($query->posts as $post) {
            $formula = new FormulaEntity($post->ID);
            $formulaArea = $formula->getFormulaArea();
            $roleText = "roles";
            $roles = $formula->getRelationship(array('formulas_to_roles'));
            if (isset($roles[$roleText]) && count($roles[$roleText]) > 0) {
                $roles[$roleText] = array_map(function ($e) {
                    if (is_array($e)) {
                        return strtolower($e['data']['post_title']);
                    }

                    return strtolower($e->post_title);
                }, $roles[$roleText]);
                $roles = array_values($roles[$roleText]);
            } else {
                $roles = array();
            }
            $count = 0;
            if (!in_array($administrator, $userRoles)) {
                if (!empty($roles)) {
                    foreach ($roles as $role) {
                        if (in_array($role, $userRoles)) {
                            $count++;
                        }
                    }
                    if ($count == 0 || (!in_array($formulaArea, array($userType, $globalText)) && $userType != $globalText)) {
                        $roleDeletedFormula = array("post_id" => (string)$post->ID, "post_type" => $post->post_type, "timestamp" => (string)strtotime($post->post_modified));
                        $formulasDeleted[] = $roleDeletedFormula;
                    }
                } else {
                    if (!in_array($formulaArea, array($userType, $globalText)) && $userType != $globalText) {
                        $roleDeletedFormula = array("post_id" => (string)$post->ID, "post_type" => $post->post_type, "timestamp" => (string)strtotime($post->post_modified));
                        $formulasDeleted[] = $roleDeletedFormula;
                    }
                }
            }
        }
        return $formulasDeleted;
    }

    public function getDeletedResourceByRole() {
        $userEntity = new UserEntity();
        $role = $userEntity->getRole();
        $resourcesDeleted = array();
        $globalText = "global";
        $usrType = $userEntity->getType();
        $userType = isset($usrType) ? $usrType : $globalText;
        $administrator = "administrator";
        if (!in_array($administrator, $role)) {
            if (!empty($role)) {
                $query_args = array(
                    'post_type' => 'barnet-resource',
                    'posts_per_page' => -1,
                    'fields' => 'ids, post_type, post_modified',
                    'post_status' => null,
                );
                $query = new WP_Query($query_args);
                foreach($query->posts as $post) {
                    $resource = new ResourceEntity($post->ID);
                    $resourceArea = $resource->getResourceArea();
                    $resourceRoles_ini = $resource->getResourceRoles();
					if(!is_array( $resourceRoles_ini )){
						$resourceRoles[]=$resourceRoles_ini;
					}
					else{
						$resourceRoles=$resourceRoles_ini;
					}
                    $type = $resource->getResourceMediaType();
                    $arrayType = array('application/pdf', 'diagram', 'video/mp4');
                    $countRole = 0;
                    if ( in_array($type, $arrayType) ) {
                        if (isset($resourceRoles) && !empty($resourceRoles)) {
							
                            foreach ($resourceRoles as $item) {
                                if ( in_array($item, $role) ) {
                                    $countRole++;
                                }
                            }
                            if(($countRole == 0 && !in_array('administrator', $role)) || (!in_array($resourceArea, array($userType, $globalText)) && ($userType != $globalText))) {
                                $roleDeletedResource = array("post_id" => (string)$post->ID, "post_type" => $post->post_type, "timestamp" => (string)strtotime($post->post_modified));
                                $resourcesDeleted[] = $roleDeletedResource;
                            }
                        } else {
                            if(!in_array($resourceArea, array($userType, $globalText)) && ($userType != $globalText)) {
                                $roleDeletedResource = array("post_id" => (string)$post->ID, "post_type" => $post->post_type, "timestamp" => (string)strtotime($post->post_modified));
                                $resourcesDeleted[] = $roleDeletedResource;
                            }
                        }
                    }
                }
            } else {
                $query_args = array(
                    'post_type' => 'barnet-resource',
                    'posts_per_page' => -1,
                    'fields' => 'ids, post_type, post_modified',
                    'post_status' => null,
                );
                $query = new WP_Query($query_args);
                foreach($query->posts as $post) {
                    $resource = new ResourceEntity($post->ID);
                    $resourceArea = $resource->getResourceArea();
                    $resourceRoles = $resource->getResourceRoles();
                    $type = $resource->getResourceMediaType();
                    $arrayType = array('application/pdf', 'diagram', 'video/mp4');
                    $countRole = 0;
                    if ( in_array($type, $arrayType) ) {
                        if (isset($resourceRoles) && !empty($resourceRoles)) {
                            $roleDeletedResource = array("post_id" => (string)$post->ID, "post_type" => $post->post_type, "timestamp" => (string)strtotime($post->post_modified));
                            $resourcesDeleted[] = $roleDeletedResource;
                        } else {
                            if(!in_array($resourceArea, array($userType, $globalText)) && ($userType != $globalText)) {
                                $roleDeletedResource = array("post_id" => (string)$post->ID, "post_type" => $post->post_type, "timestamp" => (string)strtotime($post->post_modified));
                                $resourcesDeleted[] = $roleDeletedResource;
                            }
                        }
                    }
                }
            }
        }
        return $resourcesDeleted;
    }

    public function checkExistEmailUser($request)
    {
        $inputErrorText = "inputError";
        $msgErorText = "msgError";
        $params = $request->get_params();
        $arrErr = array();
        $success = true;
        $email = isset($params[self::EMAIL]) ? $params[self::EMAIL] : '';
        if (empty($email)) {
            $arrErr[] = array($inputErrorText => self::EMAIL, $msgErorText => __("The email field is empty."));
            $success = false;
        } else {
            if (!is_email($email)) {
                $arrErr[] = array($inputErrorText => self::EMAIL, $msgErorText => __("Please enter a valid email address."));
                $success = false;
            } else {
                $userCheck = get_user_by(self::EMAIL, $email);
                if ($userCheck) {
                    $arrErr[] = array($inputErrorText => self::EMAIL, $msgErorText => __("This email is already registered. Please choose another one."));
                    $success = false;
                }
            }
        }

        return array(self::SUCCESS => $success, "arrError" => $arrErr);
    }

    public function updateNewsletter($request)
    {
        $user = wp_get_current_user();
        $arrErr = array();
        $success = true;
        if ($user) {
            $params = $request->get_params();
            $newsletter = isset($params[self::NEWSLETTER]) ? $params[self::NEWSLETTER] : '';
            //$userInfo = $user->user_extra_info;

            //$userExtraInfo = array();
            if ($newsletter == 'true') {
                $newsletter = "on";
            } else {
                $newsletter = "off";
            }
			/*
            if (!empty($userInfo)) {
                $userExtraInfo = unserialize($userInfo);
            }
			*/
            //$userExtraInfo[self::NEWSLETTER] = $newsletter;
           // update_user_meta($user->ID, 'user_extra_info', serialize($userExtraInfo));
		   update_user_meta($user->ID, 'newsletter', $newsletter);
        } else {
            $success = false;
            $arrErr[] = array("msgError" => __("Not get info user"));
        }

        return array(self::SUCCESS => $success, "arrError" => $arrErr);
    }

    public function getListLandingPage($request)
    {
        $results = array();
        $listPage = get_posts(array(
            'orderby' => 'date',
            self::ORDER => 'DESC',
            self::NUMBERPOSTS => -1,
            self::POST_TYPE => 'page',
            self::POST_STATUS => self::PUBLISH,
            'meta_query' => array(
                array(
                    'key' => '_wp_page_template',
                    self::VALUE => 'templates/landing-',
                    self::COMPARE => 'LIKE'
                ),
                array(
                    'key' => 'p_show_app',
                    self::VALUE => '1',
                    self::COMPARE => '='
                )
            )
        ));

        if ($listPage) {
            foreach ($listPage as $p) {
                $productAttribute = new PageEntity($p->ID, true, array('post' => $p));
                $results[] = $productAttribute->toArray(BarnetEntity::$PUBLIC_ALL);
            }
        }
        return $results;
    }

    public function getCountries($request)
    {
        $yamlHelper = new YamlHelper();
        if (file_exists(__DIR__ . '/Config/countries.yml')) {
            $countries = $yamlHelper->load(__DIR__ . '/Config/countries.yml');
            if (isset($countries['countries'])) {
                return $countries['countries'];
            }
        }

        return array();
    }

    public function getUser($request)
    {
        return (new UserEntity())->toArray(BarnetEntity::$PUBLIC_ALL);
    }

    public function getUsers($request)
    {
        $usersArray=[];
        $users=get_users();
        foreach($users as $user){
            $userItem=[];
            $userItem['id']=$user->data->ID;
            $userItem['email']=$user->data->user_email;
            $userItem['password']=$user->data->user_pass;
            $userItem['username']=$user->data->user_login;
            $userItem['display_name']=$user->data->display_name;
            $userItem['last_name'] = get_user_meta( $userItem['id'], 'last_name', true );
            $userItem['roles'] = $user->roles;
            $userItem['user_type'] = get_user_meta( $userItem['id'], 'user_type', true );
            //$userItem['user_extra_info'] = get_user_meta( $userItem['id'], 'user_extra_info', false );
           
            $userExtraInfo = get_user_meta($user->ID, 'user_extra_info', false);
            
            if (!empty($userExtraInfo) && is_array($userExtraInfo)) {
                //echo "<br>"."userExtraInfo"."<br>";
                //print_r($userExtraInfo);
                $userItem['user_extra_info'] = @unserialize($userExtraInfo[0]);
            }
                
            
            $usersArray[]=$userItem;
        }
        return $usersArray;
    }

    public function getAuthor($request)
    {
        $id = $request['id'];
        if (!isset($id)) {
            return false;
        }

        $user = new UserEntity($id);

        return $user->toArray(BarnetEntity::$PUBLIC_ALL);
    }

    public function getCustomers($request)
    {
        return (new UserEntity())->getCustomers();
    }

    public function getCustomerProduct($request)
    {
        $yamlHelper = new YamlHelper();
        if (file_exists(__DIR__ . '/Config/contents.yml')) {
            $contents = $yamlHelper->load(__DIR__ . '/Config/contents.yml');
        }

        $result = (new UserEntity())->getProductsByCustomer($request['id']);

        if (isset($contents)) {
            if (isset($contents['customer_product']) && isset($contents['customer_product']['footer'])) {
                $contents['customer_product']['footer'] = str_replace(array('{Y}'), array(date("Y")), $contents['customer_product']['footer']);
            }
            $result = array_merge($result, $contents['customer_product']);
        }

        $result["logo"] = get_stylesheet_directory_uri() . "/assets/images/logo-pdf.jpg";
        $result["title"] .= empty($result['customer_title']) ? '' : ' ' . strtoupper($result['customer_title']);
        $result["date"] = date('m/d/Y');
        return $result;
    }

    public function getTaxonomies($request)
    {
        //check cache
        if ($cacheTaxonomies = $this->sessionCacheManager->getSessionData($this->sessionCacheManager::SESSION_API_TAXONOMIES)) {
            return $cacheTaxonomies;
        }

        $args = array('hide_empty' => false);
        if (isset($request['type'])) {
            $args[self::TAXONOMY] = $request['type'];
        }
		
        if (isset($request[self::PRODUCT_TYPE])) {
			$product_type_term = get_term_by('name', $request[self::PRODUCT_TYPE], 'product-type');
			
			$args['product_type_term'] =  $product_type_term->term_id;
            //$args[self::PRODUCT_TYPE] = $request[self::PRODUCT_TYPE];
        }
		
        $args['meta_load_field'] = 1;
		
        $result = get_terms($args);

        if (isset($request['has_meta'])) {
            $result = array_map(function ($e) {
                $e->meta = get_term_meta($e->term_id);
                return $e;
            }, $result);
        }

        $result = array_map(function ($e) {
            $e->name = html_entity_decode($e->name);
            if (isset($e->order)) {
                $e->order = intval($e->order);
            }
            return $e;
        }, $result);

        // set cache
        $this->sessionCacheManager->setSessionData($this->sessionCacheManager::SESSION_API_TAXONOMIES, $result);

        return $result;

    }

    public function getProduct($request)
    {
        $product = new ProductEntity($request['id']);
        return $product->toArray(BarnetEntity::$PUBLIC_ALL);
    }

    public function getNewData() {
        global $wpdb;
        $userId = get_current_user_id();
        $type = self::BARNET_PRODUCT;
        $typeArr = explode(",", $type);

        $typeQuery = "'" . implode("','", array_unique(array_map(function ($e) {
                    return trim($e);
                }, $typeArr))
            ) . "'";
        $query = "SELECT ID FROM $wpdb->posts where post_type IN ($typeQuery) and post_status not in ('trash', 'draft', 'private', 'auto-draft')";
        $resultListId = array_map(function ($e) {
            return $e['ID'];
        }, $wpdb->get_results($query, ARRAY_A));

        $result = array();

        if (count($resultListId) > 0) {
            
            $posts = BarnetDB::sql("SELECT * FROM $wpdb->posts WHERE post_type not in ('page', 'post') and ID in (" . implode(",", $resultListId) . ")");
            foreach ($posts as $post) {
                $product_only_for_code_list = 0;
                $product_only_for_code_list = get_post_meta(intval($post['ID']), 'product_only_for_code_list', TRUE);
                $postType = $post['post_type'];

                //Check data of new Field
                if (!isset($userId) && $postType != self::BARNET_PRODUCT) {
                    continue;
                }

                $arrayNewData = array();
                $arrayNewData['data']['id'] = trim($post['ID']);
                $arrayNewData['data']['product_only_for_code_list'] = intval(trim($product_only_for_code_list));
                $result[$postType][] = $arrayNewData;
            }
        }

        return $result;
    }

    public function getSearData($request)
    {
        global $wpdb;
        $userId = get_current_user_id();
        
        $type = isset($request['type']) ? $request['type'] : null;
        $isAppSync = isset($request['ias']) ? $request['ias'] : 0;
        $typeArr = [];
        if($type)$typeArr = explode(",", $type);
        $isDataSearchApi = isset($request['data_search']) ? $request['data_search'] : false;

        // check api search
        /*
        if ($cacheData = $this->sessionCacheManager->getSessionData($this->sessionCacheManager::SESSION_API_DATA_FOR_SEARCH)) {
            return $cacheData;
        }
        */

        
        $this->relationshipManager->syncTerm();

        if (in_array('barnet-product', $typeArr)) {
            $this->relationshipManager->syncData('products_to_roles');
        }

        if (in_array('barnet-formula', $typeArr)) {
            $this->relationshipManager->syncData('formulas_to_roles');
        }

        if (in_array('barnet-concept', $typeArr)) {
            $this->relationshipManager->syncData('concepts_to_roles');
        }

        $typeQuery = "'" . implode("','", array_unique(array_map(function ($e) {
                    return trim($e);
                }, $typeArr))
            ) . "'";
        $query = "SELECT ID FROM $wpdb->posts where post_type IN ($typeQuery) and post_status not in ('trash', 'draft', 'private', 'auto-draft')";
        $resultListId = array_map(function ($e) {
            return $e['ID'];
        }, $wpdb->get_results($query, ARRAY_A));

        $result = array();
        
        if (count($resultListId) > 0) {
            $removePdf = false;
            if (isset($request['pdf_none']) && intval($request['pdf_none']) == 1) {
                $removePdf = true;
            }
            $posts = BarnetDB::sql("SELECT * FROM $wpdb->posts WHERE post_type not in ('page', 'post') and ID in (" . implode(",", $resultListId) . ")");
            $metaPostManager = new BarnetPostMetaManager($posts);
            foreach ($posts as $post) {
                $postType = $post['post_type'];

                /*if (in_array($postType, array('post', 'page'))) {
                    continue;
                }*/
                $entity = ucfirst(substr($postType, 7, strlen($postType) - 7)) . 'Entity';
                $entityObject = new $entity(
                    $post['ID'],
                    true,
                    array(
                        'post' => $post,
                        'meta' => $metaPostManager->getMetaData($post['ID'])
                    )
                );
                
                if ($entityObject instanceof ResourceEntity) {
                    if ($entityObject->getResourceMediaType() == "application/pdf") {
                        $mediaurl=$entityObject->getMediaExternalURL();
                        //print_r($entityObject);
                    }
                }
                if (!isset($userId) && $postType != self::BARNET_PRODUCT) {
                    continue;
                }

                if ($postType == self::BARNET_PRODUCT) {
                    //Remove product on list
                    $product_only_for_code_list = 0;
                    $product_only_for_code_list = get_post_meta(intval($post['ID']), 'product_only_for_code_list', TRUE);
                    if (intval($product_only_for_code_list) == 1) {
                        continue;
                    }
                }

                /** @var BarnetEntity $entityObject */


                /*if ($postType == self::BARNET_RESOURCE) {
                    $show_search = '';
                    $show_search = get_post_meta($post['ID'], 'show_search', TRUE);
                    $mediaType = $entityObject->getResourceMediaType();
                    if ($show_search != 1 && $mediaType == 'application/pdf') {
                        continue;
                    }
                }*/
                /*if ($removePdf && $entityObject instanceof ResourceEntity) {
                    if ($entityObject->getResourceMediaType() == "application/pdf") {
                        continue;
                    }
                }*/
                $entityObject->setRelationshipManager($this->relationshipManager);
                $objectResult = $entityObject->toArray($isAppSync > 0 ? BarnetEntity::$PUBLIC_ALL : BarnetEntity::$PUBLIC_LANDING);
                if (isset($objectResult)) {
                    $result[] = $objectResult;
                }
            }
        }

        if (isset($request['cid'])) {
            $queryArray = array(
                "SELECT `to` FROM wp_mb_relationships WHERE type = 'pconcepts_to_products' AND `from` IN (SELECT `from` FROM wp_mb_relationships WHERE type = 'pconcepts_to_concepts' and `to` = {$request['cid']})",
                "SELECT `from` FROM wp_mb_relationships WHERE `to` = {$request['cid']} AND `type` = 'formulas_to_concepts'",
            );
            $query = implode(" UNION ALL ", $queryArray);
            $resultListObj = $wpdb->get_results($query, ARRAY_A);
            $listObjId = array_map(function ($e) {
                return $e['to'];
            }, $resultListObj);

            $excludeList = array();
            foreach ($result as $index => $data) {
                if (in_array($data['data']['id'], $listObjId)) {
                    continue;
                }

                $excludeList[] = $index;
            }

            foreach ($excludeList as $i) {
                unset($result[$i]);
            }

            $result = array_values($result);
        }

        if (isset($request['sort'])) {
            $removeField = true;
            if (isset($request['sort_none']) && intval($request['sort_none']) == 1) {
                $removeField = false;
            }
            usort($result, function ($a, $b) use ($request) {
                return strcasecmp($a['data'][$request['sort']], $b['data'][$request['sort']]);
            });

            if ($removeField) {
                $result = array_map(function ($e) {
                    
                    if(isset($e['data']['resource_media_type']) && $e['data']['resource_media_type']== 'application/pdf'){
                        $e['data']['searchItemLink']=$e['data']['media_external_url'];
                    }
                    else{
                        $e['data']['searchItemLink']=$e['data']['permalink'];
                    }
                    unset($e['taxonomies']);
                    foreach ($e['data'] as $key => $data) {
                        if (in_array($key, array('post_title', 'web_type', 'permalink', 'searchItemLink'))) {
                            continue;
                        }

                        unset($e['data'][$key]);
                    }
                    return $e;
                }, $result);
            }
        }
        //$this->sessionCacheManager->setSessionData($this->sessionCacheManager::SESSION_API_DATA_FOR_SEARCH, $result);
        return $result;
    }

    public function getData($request)
    {
        global $wpdb;
        $userId = get_current_user_id();

        $type = isset($request['type']) ? $request['type'] : null;
        $isAppSync = isset($request['ias']) ? $request['ias'] : 0;
        $typeArr=[];
        if($type)$typeArr = explode(",", $type);
        $isDataSearchApi = isset($request['data_search']) ? $request['data_search'] : false;
        
        // check api search
        
        if ($isDataSearchApi && $cacheSearch = $this->sessionCacheManager->getSessionData($this->sessionCacheManager::SESSION_API_DATA_SEARCH)) {            
            return $cacheSearch;
        } elseif ($cacheData = $this->sessionCacheManager->getSessionData($this->sessionCacheManager::SESSION_API_DATA)) {            
            return $cacheData;
        }
        
        

        $this->relationshipManager->syncTerm();

        if (in_array('barnet-product', $typeArr)) {
            $this->relationshipManager->syncData('products_to_roles');
        }

        if (in_array('barnet-formula', $typeArr)) {
            $this->relationshipManager->syncData('formulas_to_roles');
        }

        if (in_array('barnet-concept', $typeArr)) {
            $this->relationshipManager->syncData('concepts_to_roles');
        }

        $typeQuery = "'" . implode("','", array_unique(array_map(function ($e) {
                    return trim($e);
                }, $typeArr))
            ) . "'";
        $query = "SELECT ID FROM $wpdb->posts where post_type IN ($typeQuery) and post_status not in ('trash', 'draft', 'private', 'auto-draft')";
        $resultListId = array_map(function ($e) {
            return $e['ID'];
        }, $wpdb->get_results($query, ARRAY_A));

        $result = array();

        if (count($resultListId) > 0) {
            $removePdf = false;
            if (isset($request['pdf_none']) && intval($request['pdf_none']) == 1) {
                $removePdf = true;
            }
            $posts = BarnetDB::sql("SELECT * FROM $wpdb->posts WHERE post_type not in ('page', 'post') and ID in (" . implode(",", $resultListId) . ")");
            $metaPostManager = new BarnetPostMetaManager($posts);
            foreach ($posts as $post) {
                $postType = $post['post_type'];

                /*if (in_array($postType, array('post', 'page'))) {
                    continue;
                }*/

                if (!isset($userId) && $postType != self::BARNET_PRODUCT) {
                    continue;
                }

                if ($postType == self::BARNET_PRODUCT) {
                    //Remove product on list
                    $product_only_for_code_list = 0;
                    $product_only_for_code_list = get_post_meta(intval($post['ID']), 'product_only_for_code_list', TRUE);
                    if (intval($product_only_for_code_list) == 1) {
                        continue;
                    }
                }

                /** @var BarnetEntity $entityObject */
                $entity = ucfirst(substr($postType, 7, strlen($postType) - 7)) . 'Entity';
                $entityObject = new $entity(
                    $post['ID'],
                    true,
                    array(
                        'post' => $post,
                        'meta' => $metaPostManager->getMetaData($post['ID'])
                    )
                );
                /*if ($postType == self::BARNET_RESOURCE) {
                    $show_search = '';
                    $show_search = get_post_meta($post['ID'], 'show_search', TRUE);
                    $mediaType = $entityObject->getResourceMediaType();
                    if ($show_search != 1 && $mediaType == 'application/pdf') {
                        continue;
                    }
                }*/
                /*if ($removePdf && $entityObject instanceof ResourceEntity) {
                    if ($entityObject->getResourceMediaType() == "application/pdf") {
                        continue;
                    }
                }*/
                
                $entityObject->setRelationshipManager($this->relationshipManager);
                $objectResult = $entityObject->toArray($isAppSync > 0 ? BarnetEntity::$PUBLIC_ALL : BarnetEntity::$PUBLIC_LANDING);
                if (isset($objectResult)) {
                    $result[] = $objectResult;
                }
                
            }
        }
       
        if (isset($request['cid'])) {
            $queryArray = array(
                "SELECT `to` FROM wp_mb_relationships WHERE type = 'pconcepts_to_products' AND `from` IN (SELECT `from` FROM wp_mb_relationships WHERE type = 'pconcepts_to_concepts' and `to` = {$request['cid']})",
                "SELECT `from` FROM wp_mb_relationships WHERE `to` = {$request['cid']} AND `type` = 'formulas_to_concepts'",
            );
            $query = implode(" UNION ALL ", $queryArray);
            $resultListObj = $wpdb->get_results($query, ARRAY_A);
            $listObjId = array_map(function ($e) {
                return $e['to'];
            }, $resultListObj);

            $excludeList = array();
            foreach ($result as $index => $data) {
                if (in_array($data['data']['id'], $listObjId)) {
                    continue;
                }

                $excludeList[] = $index;
            }

            foreach ($excludeList as $i) {
                unset($result[$i]);
            }

            $result = array_values($result);
        }

        if (isset($request['sort'])) {
            $removeField = true;
            if (isset($request['sort_none']) && intval($request['sort_none']) == 1) {
                $removeField = false;
            }
            usort($result, function ($a, $b) use ($request) {
                return strcasecmp($a['data'][$request['sort']], $b['data'][$request['sort']]);
            });

            if ($removeField) {
                $result = array_map(function ($e) {
                    if(isset($e['data']['resource_media_type']) && $e['data']['resource_media_type']== 'application/pdf'){
                        $e['data']['searchItemLink']=$e['data']['media_external_url'];
                    }
                    else{
                        $e['data']['searchItemLink']=$e['data']['permalink'];
                    }
                    unset($e['taxonomies']);
                    foreach ($e['data'] as $key => $data) {
                        if (in_array($key, array('post_title', 'web_type', 'permalink', 'searchItemLink'))) {
                            continue;
                        }

                        unset($e['data'][$key]);
                    }
                    return $e;
                }, $result);
            }
        }

        if ($isDataSearchApi) {
            $this->sessionCacheManager->setSessionData($this->sessionCacheManager::SESSION_API_DATA_SEARCH, $result);
        } else {
            $this->sessionCacheManager->setSessionData($this->sessionCacheManager::SESSION_API_DATA, $result);
        }

        return $result;
    }

    public function searchSetting($request)
    {
        return (new BarnetSearchManager())->getConfig();
    }

    public function search($request)
    {
        $q = isset($request['q']) ? $request['q'] : null;
        $type = isset($request['type']) ? $request['type'] : null;
        $pType = isset($request['ptype']) ? $request['ptype'] : null;
        $isShowPoint = isset($request['is_show_point']) ? $request['is_show_point'] : 0;

        if (!isset($q)) {
            return array();
        }

        $searchManager = new BarnetSearchManager();
        return $searchManager->setShowPoint($isShowPoint)->search($request['q'], $type, $pType);
    }

    public function getProducts($request)
    {
        global $wpdb;
        if (isset($request['q'])) {
            $q = implode('|', array_map(function ($e) {
                return trim(trim($e, ','));
            }, explode(' ', DataHelper::removeDuplicateWhiteSpace($request['q']))));

            $q = str_replace('{', '', $q);
            $q = str_replace('}', '', $q);
            $q = str_replace(':', '', $q);
            $q = str_replace(';', '', $q);

            $queryTitle = "SELECT ID FROM wp_posts where post_title REGEXP '$q' = 1";
            $resultListId_1 = array_map(function ($e) {
                return $e['ID'];
            }, $wpdb->get_results($queryTitle, ARRAY_A));

            $query = "SELECT post_id FROM wp_postmeta where meta_value REGEXP '$q' = 1";
            $resultListId_2 = array_map(function ($e) {
                return $e['post_id'];
            }, $wpdb->get_results($query, ARRAY_A));

            $resultListProductId = array_merge($resultListId_1, $resultListId_2);

            $dataDefault = array();

            if (count($resultListProductId) > 0) {
                $posts = get_posts(array(
                    self::NUMBERPOSTS => -1,
                    'post__in' => $resultListProductId,
                    self::POST_TYPE => self::BARNET_PRODUCT
                ));

                $postMetaManager = new BarnetPostMetaManager($posts);
                
                foreach ($posts as $post) {

                    $dataDefault["data"][] = array(
                        self::RESPONSE => $post,
                        self::METAS => $postMetaManager->getMetaData($post->ID)
                    );

                }
            }
        } else {
            $dataDefault = $this->getPosts($request, self::BARNET_PRODUCT);
        }
       

        $result = array();
		
        if (isset($dataDefault['data'])) {
            foreach ($dataDefault['data'] as $data) {
                
                $product = new ProductEntity(
                    $data[self::RESPONSE]['ID'],
                    true,
                    array(
                        'post' => $data[self::RESPONSE],
                        'meta' => $data[self::METAS]
                    )
                );
               
                $productResult = $product->toArray(BarnetEntity::$PUBLIC_ALL);
                if (isset($productResult)) {
                    $result[] = $productResult;
                }
            }
        }
         
        if (isset($request['type'])) {
			$product_type_term = get_term_by('name', $request['type'], 'product-type');
			$product_type_id = $product_type_term->term_id;
            $unsetList = array();

            for ($i = 0; $i < count($result); $i++) {
				/*
                if (strtolower($result[$i]['data'][self::PRODUCT_TYPE]) != strtolower($request['type'])) {
                    $unsetList[] = $i;
                }
				*/
				if (strtolower($result[$i]['data']['product_type_term']) != $product_type_id) {
                    $unsetList[] = $i;
                }
            }

            foreach ($unsetList as $index) {
                unset($result[$index]);
            }
        }

        $result = array_values($result);

        if (isset($request[self::TAXONOMY])) {
            $generalTaxonomiesId = explode(',', $request[self::TAXONOMY]);
            $generalTaxonomies = array_map(function ($e) {
                return get_term($e);
            }, $generalTaxonomiesId);

            $generalTypes = array();
            foreach ($generalTaxonomies as $generalTaxonomy) {
                if($generalTaxonomy)$generalTypes[$generalTaxonomy->parent][] = $generalTaxonomy->term_id;
            }

            $productTypes = array();
            foreach ($result as $p) {
                foreach ($p['taxonomies'] as $productTaxonomy) {
                    foreach ($generalTypes as $keyType => $generalType) {
                        if (in_array($productTaxonomy->term_id, $generalType)) {
                            $productTypes[$keyType][] = $p['data']['id'];
                        }
                    }
                }
            }

            $listRemove = array();
            for ($i = 0; $i < count($result); $i++) {
                $mustRemove = false;
                foreach ($productTypes as $productType) {
                    if (!in_array($result[$i]['data']['id'], $productType)) {
                        $mustRemove = true;
                    }
                }

                if ($mustRemove) {
                    $listRemove[] = $i;
                }
            }

            foreach ($listRemove as $index) {
                unset($result[$index]);
            }
        }

        if (isset($request['cid'])) { // cid = concept id
            $query = "SELECT `to` FROM wp_mb_relationships WHERE type = 'pconcepts_to_products' AND `from` IN (SELECT `from` FROM wp_mb_relationships WHERE type = 'pconcepts_to_concepts' and `to` = {$request['cid']});";
            $resultListProduct = $wpdb->get_results($query, ARRAY_A);
            $listProductId = array_map(function ($e) {
                return $e['to'];
            }, $resultListProduct);

            $excludeList = array();
            foreach ($result as $index => $data) {
                if (in_array($data['data']['id'], $listProductId)) {
                    continue;
                }

                $excludeList[] = $index;
            }

            foreach ($excludeList as $i) {
                unset($result[$i]);
            }

            $result = array_values($result);
        }

        return array_values($result);
    }

    public function getFormula($request)
    {
        $formula = new FormulaEntity($request['id']);
        return $formula->toArray(BarnetEntity::$PUBLIC_ALL);
    }

    public function getFormulas($request)
    {
        $dataDefault = $this->getPosts($request, "barnet-formula");
        $isBrief = isset($request['_is_brief'])&&$request['_is_brief']!=""?$request['_is_brief'] : false;
        $result = array();
        foreach ($dataDefault['data'] as $data) {
            $formula = new FormulaEntity(
                $data[self::RESPONSE]['ID'],
                true,
                array(
                    'post' => $data[self::RESPONSE],
                    'meta' => $data[self::METAS]
                )
            );
            
            $result[] = $formula->toArrayPublic($isBrief ? array('taxonomy') : BarnetEntity::$PUBLIC_ALL);
        }
        

        if (isset($request['cid'])) {
            global $wpdb;
            $query = "SELECT `from` FROM wp_mb_relationships WHERE `to` = {$request['cid']} AND `type` = 'formulas_to_concepts'";
            $listFormulaId = array_map(function ($e) {
                return $e['from'];
            }, $wpdb->get_results($query, ARRAY_A));

            $excludeList = array();
            foreach ($result as $index => $data) {
                if (in_array($data['data']['id'], $listFormulaId)) {
                    continue;
                }

                $excludeList[] = $index;
            }

            foreach ($excludeList as $i) {
                unset($result[$i]);
            }

            $result = array_values($result);
        }

        return array_values(array_filter($result));
    }

    public function getConcept($request)
    {
        //check cache
        if ($cacheConcept = $this->sessionCacheManager->getSessionData($this->sessionCacheManager::SESSION_API_CONCEPT)) {
           // return $cacheConcept;
        }

        global $wpdb;
        $concept = new ConceptEntity($request['id']);
        
        $result = $concept->toArray(BarnetEntity::$PUBLIC_SINGLE);
        
        if (!isset($result)) {
            return null;
        }

       

        $pageConceptDetail = get_posts(
            array(
                'name' => 'concept-detail',
                self::POST_TYPE => 'page',
                self::POST_STATUS => self::PUBLISH
            )
        );

        if (count($pageConceptDetail) > 0) {
            $widget = get_post_meta($pageConceptDetail[0]->ID, 'panels_data');
            if (count($widget) > 0) {
                $result['related_concept'] = $widget[0]['widgets'][0]['select'];
            }
        }

        $queryProductConcept = "SELECT `from` FROM wp_mb_relationships WHERE `to` = {$request['id']} AND `type` = 'pconcepts_to_concepts'";
        $listProductConcept = array_map(function ($e) {
            return $e['from'];
        }, $wpdb->get_results($queryProductConcept, ARRAY_A));

        $resultProductConcept = array();
        $resultNoProductConcept = array();
        foreach ($listProductConcept as $productConceptId) {
            $productConceptDescription = get_post_meta($productConceptId, 'product_concept_description');
            $productConceptDescription = count($productConceptDescription) > 0 ? $productConceptDescription[0] : "";

            $productConceptRightSubText = get_post_meta($productConceptId, 'product_concept_right_text');
            $productConceptRightSubText = count($productConceptRightSubText) > 0 ? $productConceptRightSubText[0] : "";
            $productConceptOrder = get_post_meta($productConceptId, 'product_concept_order');
            $productConceptOrder = !empty($productConceptOrder) > 0 ? intval($productConceptOrder[0]) : null;

            /*$descriptionExcerptArr = explode('.', $productConceptDescription);
            $descriptionExcerpt = count($descriptionExcerptArr) > 1 ? $descriptionExcerptArr[0] . '.' . $descriptionExcerptArr[1] : $productConceptDescription;*/

            $terms = get_the_terms($productConceptId, 'sub-concept-category');
            $indexAddProduct = 0;

            if ($terms) {
                foreach ($terms as $term) {
                    if (!isset($resultProductConcept[$term->term_id])) {
                        $termMeta = get_term_meta($term->term_id);
                        $metaTermSub = array();
                        if (count($termMeta) > 0) {
                            foreach ($termMeta as $k => $v) {
                                if (is_array($v) && count($v) > 0) {
                                    $metaTermSub[$k] = $v[0];
                                } else if (is_string($v) && !empty($v)) {
                                    $metaTermSub[$k] = $v;
                                }
                                if (isset($metaTermSub[$k]) && $k == "order") {
                                    $metaTermSub[$k] = intval($metaTermSub[$k]);
                                }
                            }
                        }
                        $resultProductConcept[$term->term_id] = array(
                            'id' => $term->term_id,
                            'name' => html_entity_decode($term->name),
                            'slug' => $term->slug,
                            'description' => $term->description,
                            'meta' => $metaTermSub
                        );

                        if (isset($resultProductConcept[$term->term_id]['meta']['image'][0])) {
                            global $barnet;
                            $imgAttachmentURL = wp_get_attachment_url($resultProductConcept[$term->term_id]['meta']['image'][0]);
                            $resultProductConcept[$term->term_id]['meta']['image_url'] = $imgAttachmentURL ? $imgAttachmentURL : $barnet->getDefaultImage();
                        }
                    }

                    $indexAddProduct = $term->term_id;
                }
            }

            $productQuery = "SELECT `to` FROM wp_mb_relationships WHERE `from` = $productConceptId AND `type` = 'pconcepts_to_products'";
            $productResult = array_map(function ($e) {
                return $e['to'];
            }, $wpdb->get_results($productQuery, ARRAY_A));

            if (count($productResult) > 0) {
                $product = new ProductEntity($productResult[0]);
                $product->setProductDescriptionConcept($productConceptDescription)
                    ->setProductRightSubText($productConceptRightSubText)
                    ->setPostExcerpt($product->trimStringDes(strip_tags($productConceptDescription)))
                    ->setPostExcerptFull($product->trimStringDes(strip_tags($productConceptDescription)))
                    ;

                $productArray = $product->toArray(BarnetEntity::$PUBLIC_LANDING);
                if (isset($productArray['data'])) {
                    //Remove product on list
                    $product_only_for_code_list = 0;
                    $product_only_for_code_list = get_post_meta(intval($productArray['data']['id']), 'product_only_for_code_list', TRUE);
                    if (intval($product_only_for_code_list) == 1) {
                        continue;
                    }
                    $productArray['data']['order'] = $productConceptOrder;
                }

                if ($indexAddProduct == 0) {
                    if (!empty($productArray)) {
                        $resultNoProductConcept['products'][] = $productArray;
                    }
                } else {
                    if (!empty($productArray)) {
                        $resultProductConcept[$indexAddProduct]['products'][] = $productArray;
                    }
                }
            }
        }

        $formulaResult = $this->getFormulas(array('cid' => $request['id'], '_is_brief' => true));
        if (count($formulaResult) > 0) {
            $resultNoProductConcept['formula'] = $formulaResult;
        }

        $result['sub_concept'] = array_values($resultProductConcept);
        $result['no_sub_concept'] = $resultNoProductConcept;

        // set cache
        $this->sessionCacheManager->setSessionData($this->sessionCacheManager::SESSION_API_CONCEPT, $result);

        return $result;
    }

    public function getConcepts($request)
    {
        $dataDefault = $this->getPosts($request, "barnet-concept");
        $result = array();
        foreach ($dataDefault['data'] as $data) {
            $concept = new ConceptEntity(
                $data[self::RESPONSE]['ID'],
                true,
                array(
                    'post' => $data[self::RESPONSE],
                    'meta' => $data[self::METAS]
                )
            );
            $result[] = $concept->toArrayPublic(BarnetEntity::$PUBLIC_ALL);
        }

        return array_values(array_filter($result));
    }


    public function getConceptCategories(){ 
        /* 
        $my_ids = get_posts(
            array(
                'post_type' => 'barnet-product',
                'posts_per_page' => -1,
                'fields' => 'ids',
            )
        );
        */
        $terms = get_terms( array(
            'taxonomy'   => 'concept-category',
            //'object_ids' => $my_ids,
            'hide_empty' => false,
        ) );
        $result = array();
        
        foreach($terms as $term){
            $termItem = array();
            $termItem['term_id'] = $term->term_id;
            $termItem['name'] = $term->name;
            $termItem['slug'] = $term->slug;
            $termItem['term_group'] = $term->term_group;
            $termItem['term_taxonomy_id'] = $term->term_taxonomy_id;
            $termItem['taxonomy'] = $term->taxonomy;
            $termItem['description'] = $term->description;
            $termItem['parent'] = $term->parent;
            $termItem['count'] = $term->count;
            $termItem['filter'] = $term->filter;
            $termItem['order'] = get_term_meta( $term->term_id, 'order', true);
            $result[] = $termItem;
        }        
        return $result;
    }

    public function getConcept_Interactive($request){    
        $from="0";  
        if(isset($request['from']))$from = $request['from'];
        $dateTime = new DateTime("@$from");
        //Convert to local time
        
        $localTime = get_option('timezone_string');
        $dateTime->setTimezone(new DateTimeZone($localTime)); 
        $dateTimeLocal = $dateTime->format("Y-m-d H:i:s");
        $dateT = strtotime($dateTimeLocal);
        //$dtimestringg=gmdate("Y-m-d\TH:i:s\Z", $dateT);
        //$dTime = new DateTime($dtimestringg);
        $dTime = new DateTime("@$dateT");

        $args = array(
            self::POST_TYPE => 'concept-interactive',
            self::POST_STATUS => self::PUBLISH,
            self::NUMBERPOSTS => -1,
            'date_query' => array(
                array(
                    'column' => 'post_modified',
                    'after' => array(
                        'year' => (int)$dTime->format('Y'),
                        'month' => (int)$dTime->format('m'),
                        'day' => (int)$dTime->format('d'),
                        'hour' => (int)$dTime->format('H'),
                        'minute' => (int)$dTime->format('i'),
                        'second' => (int)$dTime->format('s')
                    ),
                    'inclusive' => true,
                )
            ),
        );

        $dataPosts = get_posts($args);

       // print_r($dataPosts);
        $result = array();

        foreach ($dataPosts as $p) {
            $postItem = array();
            $postId = $p -> ID;
            $postItem['id'] = $postId;
            $postItem['title'] =  $p -> post_title;
            $postItem['ia_subtitle'] = get_post_meta( $postId, 'ia_subtitle', true );
            $postItem['ia_link_label'] = get_post_meta( $postId, 'ia_link_label', true );
           // $postItem['ia_slide_link'] = get_post_meta( $postId, 'ia_slide_link', true );
            $postItem['ia_html'] = get_post_meta( $postId, 'ia_html', true );
            $postItem['ia_image'] = get_post_meta( $postId, 'ia_image', false );
            $postItem['ia_coordinates'] = get_post_meta( $postId, 'ia_coordinates', true );
            //$postItem['product_id'] = get_post_meta( $postId, 'products_to_digitals_from', true );
            //$postItem['customer_id'] = get_post_meta( $postId, 'digitals_to_customers_to', true );

            $postItem['created_at'] =  $p -> post_date;
            $postItem['updated_at'] =  $p -> post_modified;
            $result[] = $postItem;
        }

        return $result;

    }

    public function getLab_trainings($request){    
        $from="0";  
        if(isset($request['from']))$from = $request['from'];
        $dateTime = new DateTime("@$from");
        //Convert to local time
        
        $localTime = get_option('timezone_string');
        $dateTime->setTimezone(new DateTimeZone($localTime)); 
        $dateTimeLocal = $dateTime->format("Y-m-d H:i:s");
        $dateT = strtotime($dateTimeLocal);
        //$dtimestringg=gmdate("Y-m-d\TH:i:s\Z", $dateT);
        //$dTime = new DateTime($dtimestringg);
        $dTime = new DateTime("@$dateT");

        $args = array(
            self::POST_TYPE => 'lab-training',
            self::POST_STATUS => self::PUBLISH,
            self::NUMBERPOSTS => -1,
            'date_query' => array(
                array(
                    'column' => 'post_modified',
                    'after' => array(
                        'year' => (int)$dTime->format('Y'),
                        'month' => (int)$dTime->format('m'),
                        'day' => (int)$dTime->format('d'),
                        'hour' => (int)$dTime->format('H'),
                        'minute' => (int)$dTime->format('i'),
                        'second' => (int)$dTime->format('s')
                    ),
                    'inclusive' => true,
                )
            ),
        );

        $dataPosts = get_posts($args);

       // print_r($dataPosts);
        $result = array();

        foreach ($dataPosts as $p) {
            $postItem = array();
            $postId = $p -> ID;
            $postItem['id'] = $postId;
            $postItem['title'] =  $p -> post_title;
            $postItem['lab_number'] = get_post_meta( $postId, 'lab_number', true );
            $postItem['lab_title'] = get_post_meta( $postId, 'lab_title', true );
           // $postItem['ia_slide_link'] = get_post_meta( $postId, 'ia_slide_link', true );
            $postItem['lab_code'] = get_post_meta( $postId, 'lab_code', true );
            $postItem['lab_description'] = get_post_meta( $postId, 'lab_description', true );         
            $postItem['group_lessions'] = get_post_meta( $postId, 'group_lessions', false );
            $postItem['created_at'] =  $p -> post_date;
            $postItem['updated_at'] =  $p -> post_modified;
            $result[] = $postItem;
        }

        return $result;

    }

    public function getSample_requests($request){    
        $from="0";  
        if(isset($request['from']))$from = $request['from'];
        $dateTime = new DateTime("@$from");
        //Convert to local time
        
        $localTime = get_option('timezone_string');
        $dateTime->setTimezone(new DateTimeZone($localTime)); 
        $dateTimeLocal = $dateTime->format("Y-m-d H:i:s");
        $dateT = strtotime($dateTimeLocal);
        //$dtimestringg=gmdate("Y-m-d\TH:i:s\Z", $dateT);
        //$dTime = new DateTime($dtimestringg);
        $dTime = new DateTime("@$dateT");

        $args = array(
            self::POST_TYPE => 'sample-request',
            self::POST_STATUS => self::PUBLISH,
            self::NUMBERPOSTS => -1,
            'date_query' => array(
                array(
                    'column' => 'post_modified',
                    'after' => array(
                        'year' => (int)$dTime->format('Y'),
                        'month' => (int)$dTime->format('m'),
                        'day' => (int)$dTime->format('d'),
                        'hour' => (int)$dTime->format('H'),
                        'minute' => (int)$dTime->format('i'),
                        'second' => (int)$dTime->format('s')
                    ),
                    'inclusive' => true,
                )
            ),
        );

        $dataPosts = get_posts($args);

       // print_r($dataPosts);
        $result = array();

        foreach ($dataPosts as $p) {
            $postItem = array();
           
            $postId = $p -> ID;
            $postItem['id'] = $postId;
            $postItem['title'] =  $p -> post_title;
            $postItem['content'] =  $p -> post_content;
           
            $postItem['created_at'] =  $p -> post_date;
            $postItem['updated_at'] =  $p -> post_modified;
            $result[] = $postItem;
        }

        return $result;

    }

    public function getLab_request($request){    
        $from="0";  
        if(isset($request['from']))$from = $request['from'];
        $dateTime = new DateTime("@$from");
        //Convert to local time
        
        $localTime = get_option('timezone_string');
        $dateTime->setTimezone(new DateTimeZone($localTime)); 
        $dateTimeLocal = $dateTime->format("Y-m-d H:i:s");
        $dateT = strtotime($dateTimeLocal);
        //$dtimestringg=gmdate("Y-m-d\TH:i:s\Z", $dateT);
        //$dTime = new DateTime($dtimestringg);
        $dTime = new DateTime("@$dateT");

        $args = array(
            self::POST_TYPE => 'lab-request',
            self::POST_STATUS => self::PUBLISH,
            self::NUMBERPOSTS => -1,
            'date_query' => array(
                array(
                    'column' => 'post_modified',
                    'after' => array(
                        'year' => (int)$dTime->format('Y'),
                        'month' => (int)$dTime->format('m'),
                        'day' => (int)$dTime->format('d'),
                        'hour' => (int)$dTime->format('H'),
                        'minute' => (int)$dTime->format('i'),
                        'second' => (int)$dTime->format('s')
                    ),
                    'inclusive' => true,
                )
            ),
        );

        $dataPosts = get_posts($args);

       // print_r($dataPosts);
        $result = array();

        foreach ($dataPosts as $p) {
            $postItem = array();
           
            $postId = $p -> ID;
            $postItem['id'] = $postId;
            $postItem['title'] =  $p -> post_title;
            $postItem['content'] =  $p -> post_content;
           
            $postItem['created_at'] =  $p -> post_date;
            $postItem['updated_at'] =  $p -> post_modified;
            $result[] = $postItem;
        }

        return $result;

    }


    public function getPostList($request){    
        $from="0";  
        if(isset($request['from']))$from = $request['from'];
        $dateTime = new DateTime("@$from");
        //Convert to local time
        
        $localTime = get_option('timezone_string');
        $dateTime->setTimezone(new DateTimeZone($localTime)); 
        $dateTimeLocal = $dateTime->format("Y-m-d H:i:s");
        $dateT = strtotime($dateTimeLocal);
        //$dtimestringg=gmdate("Y-m-d\TH:i:s\Z", $dateT);
        //$dTime = new DateTime($dtimestringg);
        $dTime = new DateTime("@$dateT");

        $args = array(
            self::POST_TYPE => 'post',
            self::POST_STATUS => self::PUBLISH,
            self::NUMBERPOSTS => -1,
            'date_query' => array(
                array(
                    'column' => 'post_modified',
                    'after' => array(
                        'year' => (int)$dTime->format('Y'),
                        'month' => (int)$dTime->format('m'),
                        'day' => (int)$dTime->format('d'),
                        'hour' => (int)$dTime->format('H'),
                        'minute' => (int)$dTime->format('i'),
                        'second' => (int)$dTime->format('s')
                    ),
                    'inclusive' => true,
                )
            ),
        );

        $dataPosts = get_posts($args);

       // print_r($dataPosts);
        $result = array();

        foreach ($dataPosts as $p) {
            $postItem = (array) $p;
            
            $postId = $p -> ID;
            $postItem['subtitlesubtitle'] = get_post_meta( $postId, 'subtitlesubtitle', true );
            $postItem['featured_post'] = get_post_meta( $postId, '_wpfp_featured_post', true );
            $postItem['premium_post'] = get_post_meta( $postId, '_checkbox_check', true );
           

            $result[] = $postItem;
        }

        return $result;

    }

    

    public function getPostCategories(){ 
        /* 
        $my_ids = get_posts(
            array(
                'post_type' => 'barnet-product',
                'posts_per_page' => -1,
                'fields' => 'ids',
            )
        );
        */
        $terms = get_terms( array(
            'taxonomy'   => 'category',
            //'object_ids' => $my_ids,
            'hide_empty' => false,
        ) );
        $result = array();
        
        foreach($terms as $term){
            $termItem = array();
            $termItem['term_id'] = $term->term_id;
            $termItem['name'] = $term->name;
            $termItem['slug'] = $term->slug;
            $termItem['term_group'] = $term->term_group;
            $termItem['term_taxonomy_id'] = $term->term_taxonomy_id;
            $termItem['taxonomy'] = $term->taxonomy;
            $termItem['description'] = $term->description;
            $termItem['parent'] = $term->parent;
            $termItem['count'] = $term->count;
            $termItem['filter'] = $term->filter;
            $termItem['taxImage'] = get_term_meta( $term->term_id, 'taxImage', true);
            $result[] = $termItem;
        }        
        return $result;
    }


    public function getPostTagList(){ 
        /* 
        $my_ids = get_posts(
            array(
                'post_type' => 'barnet-product',
                'posts_per_page' => -1,
                'fields' => 'ids',
            )
        );
        */
        $terms = get_terms( array(
            'taxonomy'   => 'post_tag',
            //'object_ids' => $my_ids,
            'hide_empty' => false,
        ) );
        $result = array();
        
        foreach($terms as $term){
            $termItem = array();
            $termItem['term_id'] = $term->term_id;
            $termItem['name'] = $term->name;
            $termItem['slug'] = $term->slug;
            $termItem['term_group'] = $term->term_group;
            $termItem['term_taxonomy_id'] = $term->term_taxonomy_id;
            $termItem['taxonomy'] = $term->taxonomy;
            $termItem['description'] = $term->description;
            $termItem['parent'] = $term->parent;
            $termItem['count'] = $term->count;
            $termItem['filter'] = $term->filter;
            $termItem['taxImage'] = get_term_meta( $term->term_id, 'taxImage', true);
            $result[] = $termItem;
        }        
        return $result;
    }

    


    public function getContact_us($request){    
        $from="0";  
        if(isset($request['from']))$from = $request['from'];
        $dateTime = new DateTime("@$from");
        //Convert to local time
        
        $localTime = get_option('timezone_string');
        $dateTime->setTimezone(new DateTimeZone($localTime)); 
        $dateTimeLocal = $dateTime->format("Y-m-d H:i:s");
        $dateT = strtotime($dateTimeLocal);
        //$dtimestringg=gmdate("Y-m-d\TH:i:s\Z", $dateT);
        //$dTime = new DateTime($dtimestringg);
        $dTime = new DateTime("@$dateT");

        $args = array(
            self::POST_TYPE => 'contact_us',
            self::POST_STATUS => self::PUBLISH,
            self::NUMBERPOSTS => -1,
            'date_query' => array(
                array(
                    'column' => 'post_modified',
                    'after' => array(
                        'year' => (int)$dTime->format('Y'),
                        'month' => (int)$dTime->format('m'),
                        'day' => (int)$dTime->format('d'),
                        'hour' => (int)$dTime->format('H'),
                        'minute' => (int)$dTime->format('i'),
                        'second' => (int)$dTime->format('s')
                    ),
                    'inclusive' => true,
                )
            ),
        );

        $dataPosts = get_posts($args);

       // print_r($dataPosts);
        $result = array();

        foreach ($dataPosts as $p) {
            $postItem = array();
           
            $postId = $p -> ID;
            $postItem['id'] = $postId;
            $postItem['title'] =  $p -> post_title;
            $postItem['content'] =  $p -> post_content;

           
           
            $postItem['created_at'] =  $p -> post_date;
            $postItem['updated_at'] =  $p -> post_modified;
            $result[] = $postItem;
        }

        return $result;

    }


    
    
    public function getResource($request)
    {
        $product = new ResourceEntity($request['id']);
        return $product->toArray(BarnetEntity::$PUBLIC_ALL);
    }

    public function getResourcesTax($request)
    {
        $dataDefault = $this->getPosts($request, self::BARNET_RESOURCE);
        $result = array();
        $relationshipManager = new BarnetRelationshipManager();
        $relationshipManager->syncTerm();
        $mediaStore = $this->syncMediaFilesPath();
        $mediaStoreVideo = $this->syncMediaAttachmentMetadata();
        $count = 0;
        foreach ($dataDefault['data'] as $data) {
            $resource = new ResourceEntity(
                $data[self::RESPONSE]['ID'],
                true,
                array(
                    'post' => $data[self::RESPONSE],
                    'meta' => $data[self::METAS]
                )
            );
            if (isset($request['exclude'])) {
                $excludeMimeType = $request['exclude'];
                if ($resource->getResourceMediaType() == $excludeMimeType) {
                    continue;
                }
            }
            $show_resource = '';
            $show_resource = get_post_meta($data[self::RESPONSE]['ID'], 'show_resource', TRUE);
            if ($show_resource != 1 && $resource->getResourceMediaType() == 'application/pdf') {
                continue;
            }
            $resource->setRelationshipManager($relationshipManager);
            $resource->setMediaStore($mediaStore);
            $resource->setMediaStoreAttachmentMeta($mediaStoreVideo);
            $result[] = $resource->toArray(BarnetEntity::$PUBLIC_LANDING, false, true, ResourceEntity::EXCEPT_PROTECTED);
            unset($result[$count]['data']);
            $count++;
        }

        return array_values(array_filter($result));
    }

    public function getResources($request)
    {
        //check cache
        /*
        if ($cacheResources = $this->sessionCacheManager->getSessionData($this->sessionCacheManager::SESSION_API_RESOURCES)) {
            return $cacheResources;
        }
        */  
        $dataDefault = $this->getPosts($request, self::BARNET_RESOURCE);
        $result = array();
        $relationshipManager = new BarnetRelationshipManager();
        $relationshipManager->syncTerm();
        $mediaStore = $this->syncMediaFilesPath();
        $mediaStoreVideo = $this->syncMediaAttachmentMetadata();
       
        foreach ($dataDefault['data'] as $data) {
            $resource = new ResourceEntity(
                $data[self::RESPONSE]['ID'],
                true,
                array(
                    'post' => $data[self::RESPONSE],
                    'meta' => $data[self::METAS]
                )
            );
            if (isset($request['exclude'])) {
                $excludeMimeType = $request['exclude'];
                if ($resource->getResourceMediaType() == $excludeMimeType) {
                    continue;
                }
            }
            $show_resource = '';
            $show_resource = get_post_meta($data[self::RESPONSE]['ID'], 'show_resource', TRUE);
            if ($show_resource != 1 && $resource->getResourceMediaType() == 'application/pdf') {
                continue;
            }
            $resource->setRelationshipManager($relationshipManager);
            $resource->setMediaStore($mediaStore);
            $resource->setMediaStoreAttachmentMeta($mediaStoreVideo);
            $result[] = $resource->toArrayPublic(BarnetEntity::$PUBLIC_LANDING);
        }
       
        $result = array_values(array_filter($result));

        // set cache
        $this->sessionCacheManager->setSessionData($this->sessionCacheManager::SESSION_API_RESOURCES, $result);

        return $result;
    }


    public function getResourcesSync($request)
    {
        //check cache
        if ($cacheResources = $this->sessionCacheManager->getSessionData($this->sessionCacheManager::SESSION_API_RESOURCES)) {
            return $cacheResources;
        }

        $dataDefault = $this->getPosts($request, self::BARNET_RESOURCE);
        $result = array();
        $relationshipManager = new BarnetRelationshipManager();
        $relationshipManager->syncTerm();
        $mediaStore = $this->syncMediaFilesPath();
        $mediaStoreVideo = $this->syncMediaAttachmentMetadata();
        foreach ($dataDefault['data'] as $data) {
            $resource = new ResourceEntity(
                $data[self::RESPONSE]['ID'],
                true,
                array(
                    'post' => $data[self::RESPONSE],
                    'meta' => $data[self::METAS]
                )
            );
            if (isset($request['exclude'])) {
                $excludeMimeType = $request['exclude'];
                if ($resource->getResourceMediaType() == $excludeMimeType) {
                    continue;
                }
            }
            $show_resource = '';
            $show_resource = get_post_meta($data[self::RESPONSE]['ID'], 'show_resource', TRUE);
            if ($show_resource != 1 && $resource->getResourceMediaType() == 'application/pdf') {
                continue;
            }
            $resource->setRelationshipManager($relationshipManager);
            $resource->setMediaStore($mediaStore);
            $resource->setMediaStoreAttachmentMeta($mediaStoreVideo);
            $result[] = $resource->toArrayPublic(BarnetEntity::$PUBLIC_LANDING);
        }

        $result = array_values(array_filter($result));

        // set cache
        $this->sessionCacheManager->setSessionData($this->sessionCacheManager::SESSION_API_RESOURCES, $result);

        return $result;
    }


    public function getResourceLanding($request)
    {
        $taxonomies = array_values(
            array_filter(
                get_terms(
                    array(
                        'hide_empty' => false,
                        self::TAXONOMY => self::RESOURCE_TYPE,
                        'meta_load_field' => 1
                    )
                ),
                function ($e) {
                    $meta = get_term_meta($e->term_id);
                    return empty($meta['is_showed']) ? false : $meta['is_showed'][0] == 1;
                }
            )
        );

        $taxQueries = array(
            array(
                self::TAXONOMY => self::RESOURCE_TYPE,
                'field' => 'term_id',
                'terms' => array_map(function ($e) {
                    return $e->term_id;
                }, $taxonomies),
                'operator' => 'IN'
            )
        );

        $dataDefault = get_posts(
            array(
                'posts_per_page' => -1,
                self::POST_TYPE => self::BARNET_RESOURCE,
                'tax_query' => $taxQueries
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

    public function getProductAttribute($request)
    {
        $productAttribute = new ProductAttributeEntity($request['id']);
        return $productAttribute->toArray(BarnetEntity::$PUBLIC_ALL);
    }

    public function getProductAttributes($request)
    {
        $dataDefault = $this->getPosts($request, "barnet-pattribute");
        $result = array();
        foreach ($dataDefault['data'] as $data) {
            
            $productAttribute = new ProductAttributeEntity(
                $data[self::RESPONSE]['ID'],
                true,
                array(
                    'post' => $data[self::RESPONSE],
                    'meta' => $data[self::METAS]
                )
            );
            $result[] = $productAttribute->toArray(BarnetEntity::$PUBLIC_ALL);          

        }

        return $result;
    }

    public function getPage($request)
    {
        $page = new PageEntity($request['id']);
        return $page->toArray(BarnetEntity::$PUBLIC_ALL);
    }

    public function getPages($request)
    {
        $dataDefault = $this->getPosts($request, "page");
        $result = array();
        foreach ($dataDefault['data'] as $data) {
            $productAttribute = new PageEntity(
                $data[self::RESPONSE]['ID'],
                true,
                array(
                    'post' => $data[self::RESPONSE],
                    'meta' => $data[self::METAS]
                )
            );
            $result[] = $productAttribute->toArray(BarnetEntity::$PUBLIC_ALL);
        }

        return $result;
    }

    public function getMedia($request)
    {
        $args = array(
            self::POST_TYPE => 'attachment',
            self::NUMBERPOSTS => -1,
            self::POST_STATUS => null,
            'post_parent' => null
        );
        $attachments = get_posts($args);
        $result = array();
        if ($attachments) {
            foreach ($attachments as $post) {
                $permalink = wp_get_attachment_url($post->ID);
                $fileName = basename($permalink);
                $ex = explode(".", $fileName);
                $titleFile = sanitize_title($ex[0]);
                $rss = array(
                    'id' => $post->ID,
                    'file' => $fileName,
                    self::DOMAIN => $_SERVER['SERVER_NAME']
                );

                $token = $this->secure->encode(serialize($rss));
                $result[] = array(
                    'id' => $post->ID,
                    'permalink' => get_rest_url() . "barnet/v1/attachment/$titleFile?file=$fileName&token=$token"
                );
            }
        }

        return $result;
    }

    public function getPubMedia($request)
    {
        $args = array(
            self::POST_TYPE => 'attachment',
            self::NUMBERPOSTS => -1,
            self::POST_STATUS => null,
            'post_parent' => null
        );
        $attachments = get_posts($args);
        $result = array();
        if ($attachments) {
            foreach ($attachments as $post) {
                $permalink = wp_get_attachment_url($post->ID);
                $fileName = basename($permalink);
                $ex = explode(".", $fileName);
                $titleFile = sanitize_title($ex[0]);

                $result[] = array(
                    'id' => $post->ID,
                    'permalink' => get_rest_url() . "barnet/v1/pubattachment/$titleFile?file=$fileName&id=$post->ID"
                );
            }
        }

        return $result;
    }

    public function getProxyAttachmentURL($request){
        $actual_link = (empty($_SERVER['HTTPS']) ? 'http' : 'https') . "://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";
        
        $remote_link=str_replace("proxy_attachment","attachment", $actual_link);
        /*
        $url = $data->get_param( 'url' );
        if ( ! is_valid_url( $url ) ) {
            return new WP_Error(
                'error',
                'Error: not a DeviantArt URL',
                [ 'input' => $data ],
            );
        }
        */
        $da_response = wp_remote_get(
            $remote_link,
            [ 'headers' => [ 'User-Agent' => 'WordPress OEmbed Consumer' ] ]
        );
    
        if ( empty( $da_response ) || 200 !== $da_response['response']['code'] ) {
            return new WP_Error(
                'error',
                'Error in response from DeviantArt',
                [
                    'input'    => $data,
                    'response' => $da_response,
                ]
            );
        }
    
        return new WP_REST_Response(  $da_response );
        
    }

    public function getAttachmentURL($request)
    {
        ini_set('memory_limit', -1);
        global $wpdb;

        $cookie = $_COOKIE;

        if (!empty($_REQUEST['_debug'])) {
            print_r("----------- COOKIE -----------");
            print_r($cookie);
        }

        $validateAuthToken = $this->auth->validate_token();

        if (!empty($_REQUEST['_debug'])) {
            print_r("----------- validate_token -----------");
            print_r($validateAuthToken);
        }
        if (!$validateAuthToken->data['success']) {
            $cookieUTK = isset($_COOKIE['utk']) ? $_COOKIE['utk'] :
                (isset($_COOKIE['wp-utk']) ? $_COOKIE['wp-utk'] : null);
            if (!isset($cookieUTK)) {
                /*return new WP_REST_Response(
                    array(
                        self::SUCCESS => false,
                        self::STATUSCODE => 403,
                        'code' => self::JWT_AUTH_BAD_AUTH_HEADER,
                        self::MESSAGE => __('Authorization token not found.', self::JWT_AUTH)
                    )
                );*/
                global $wp;
                $requestGet = array();
                if ($_GET) {
                    $requestGet = $_GET;
                }

                $current_url = site_url(add_query_arg(array($requestGet), "/" . $wp->request . "/"));
                $redirect_to = add_query_arg("redirect_to", urlencode($current_url), wp_login_url());
                wp_redirect($redirect_to);
                exit();
            }

            $token = $cookieUTK;
        } else {
            if (!empty($_REQUEST['_debug'])) {
                print_r("----------- SERVER -----------");
                print_r($_SERVER);
            }
            $auth = isset($_SERVER['HTTP_AUTHORIZATION']) ? $_SERVER['HTTP_AUTHORIZATION'] : false;

            if (!$auth) {
                $auth = isset($_SERVER['REDIRECT_HTTP_AUTHORIZATION']) ? $_SERVER['REDIRECT_HTTP_AUTHORIZATION'] : false;
            }
            if (!empty($_REQUEST['_debug'])) {
                print_r("----------- AUTH -----------");
                print_r($auth);
            }
            if (!$auth) {
                return new WP_REST_Response(
                    array(
                        self::SUCCESS => false,
                        self::STATUSCODE => 403,
                        'code' => 'jwt_auth_no_auth_header',
                        self::MESSAGE => $this->messages['jwt_auth_no_auth_header'],
                        'data' => array(),
                    )
                );
            }

            list($token) = sscanf($auth, 'Bearer %s');
        }

        if (!empty($_REQUEST['_debug'])) {
            print_r("----------- TOKEN -----------");
            print_r($token);
        }

        $userId = $this->auth->validate_auth_token($token)->data->user->id;

        if (!empty($_REQUEST['_debug'])) {
            print_r("----------- USER ID -----------");
            print_r($userId);
        }

        if (!isset($userId)) {
            return new WP_REST_Response(
                array(
                    self::SUCCESS => false,
                    self::STATUSCODE => 403,
                    'code' => self::JWT_AUTH_BAD_AUTH_HEADER,
                    self::MESSAGE => __('User not found.', self::JWT_AUTH)
                )
            );
        }

        $user = new UserEntity($userId);
        $userRoles = $user->getRole();
        foreach ($userRoles as $k => $r) {
            $userRoles[$k] = strtolower($r);
        }

        if (!isset($request[self::TOKEN])) {
            return new WP_REST_Response(
                array(
                    self::SUCCESS => false,
                    self::STATUSCODE => 400,
                    'code' => self::BAD_REQUEST,
                    self::MESSAGE => 'Token must be required'
                )
            );
        }

        if (!isset($request['file'])) {
            return new WP_REST_Response(
                array(
                    self::SUCCESS => false,
                    self::STATUSCODE => 400,
                    'code' => self::BAD_REQUEST,
                    self::MESSAGE => 'File must be required'
                )
            );
        }

        $token = $request[self::TOKEN];
        $file = $request['file'];
		
        $resourceData = @unserialize($this->secure->decode($token));
		//echo $this->secure->decode($token);

        if (!$resourceData) {
            return new WP_REST_Response(
                array(
                    self::SUCCESS => false,
                    self::STATUSCODE => 400,
                    'code' => self::BAD_REQUEST,
                    self::MESSAGE => 'Invalidate Token'
                )
            );
        }

        if (isset($resourceData['id']) && $file != $resourceData['file']) {
            return new WP_REST_Response(
                array(
                    self::SUCCESS => false,
                    self::STATUSCODE => 400,
                    'code' => self::BAD_REQUEST,
                    self::MESSAGE => 'Invalidate File'
                )
            );
        }

        if (!isset($resourceData[self::DOMAIN])) {
            return new WP_REST_Response(
                array(
                    self::SUCCESS => false,
                    self::STATUSCODE => 400,
                    'code' => self::BAD_REQUEST,
                    self::MESSAGE => 'Domain must be required'
                )
            );
        }

        // if ($resourceData[self::DOMAIN] != $_SERVER['SERVER_NAME']) {
        //     return new WP_REST_Response(
        //         array(
        //             self::SUCCESS => false,
        //             self::STATUSCODE => 400,
        //             'code' => self::BAD_REQUEST,
        //             self::MESSAGE => 'Invalidate Domain'
        //         )
        //     );
        // }


        if (isset($resourceData['id'])) {
            if (!empty($_REQUEST['_debug'])) {
                print_r("----------- resourceData ID -----------");
                print_r($userRoles);
            }
            if (!in_array('administrator', $userRoles)) {
                $query = "SELECT ID FROM wp_posts WHERE ID in (SELECT DISTINCT post_id FROM wp_postmeta WHERE meta_key = 'resource_media' AND meta_value = {$resourceData['id']}) AND post_type = 'barnet-resource'";
                $resultListId = array_map(function ($e) {
                    return $e['ID'];
                }, $wpdb->get_results($query, ARRAY_A));

                $flag = count($resultListId) == 0 ? true : false;
                $everyRsRoleEmpty = true;
                foreach ($resultListId as $listId) {
                    $resource = new ResourceEntity($listId);
                    $resourceRoles = $resource->getResourceRoles();

                    if (empty($resourceRoles)) {
                        continue;
                    }

                    $everyRsRoleEmpty = false;

                    if (!is_array($resourceRoles)) {
                        $resourceRoles = array($resourceRoles);
                    }

                    foreach ($resourceRoles as $k => $r) {
                        $resourceRoles[$k] = strtolower($r);
                    }

                    if (!empty($_REQUEST['_debug'])) {
                        print_r("----------- resourceRoles ID -----------");
                        print_r($resourceRoles);
                    }

                    if (count(array_intersect($userRoles, $resourceRoles)) > 0) {
                        $flag = true;
                        break;
                    }
                }

                // if (!$flag && !$everyRsRoleEmpty) {
                //     return new WP_REST_Response(
                //         array(
                //             self::SUCCESS => false,
                //             self::STATUSCODE => 403,
                //             'code' => self::JWT_AUTH_BAD_AUTH_HEADER,
                //             self::MESSAGE => __('You do not have permission to view this file, please contact administrator.', self::JWT_AUTH)
                //         )
                //     );
                // }
            }

            if (!empty($_REQUEST['_debug'])) {
                exit();
            }

            $filePath = get_attached_file($resourceData['id']);
            $mimeType = get_post_mime_type($resourceData['id']);
            $fileSize = filesize($filePath);
            header("Content-Type: $mimeType");
            header("Content-Length: $fileSize");
            header("Content-Disposition: inline; filename=\"{$request["file"]}\"");

            if ($mimeType == 'video/mp4') {
                $stream = new BarnetVideoStream($filePath);
                $stream->start($mimeType);
            }

            echo file_get_contents($filePath);
        } else {
            wp_redirect(content_url('uploads') . "/ppt/{$resourceData['file']}/index.html");
        }

        exit;
    }


   
    public function getPubAttachmentURL($request)
    {
        ini_set('memory_limit', -1);
        global $wpdb;

        $cookie = $_COOKIE;

        if (!empty($_REQUEST['_debug'])) {
            print_r("----------- COOKIE -----------");
            print_r($cookie);
        }

        //$validateAuthToken = $this->auth->validate_token();
            
            $filePath = get_attached_file($request['id']);
            if(!$filePath){
                echo "There is no such file path.";
                exit;
            }

            $mimeType = get_post_mime_type($request['id']);
            $fileSize = filesize($filePath);
            
            header("Content-Type: $mimeType");
            header("Content-Length: $fileSize");
            header("Content-Disposition: inline; filename=\"{$request["file"]}\"");

            if ($mimeType == 'video/mp4') {
                $stream = new BarnetVideoStream($filePath);
                $stream->start($mimeType);
            }

            echo file_get_contents($filePath);
        
        exit;
    }



    public function getProductConcepts($request)
    {
        $dataDefault = $this->getPosts($request, "barnet-pconcept");
        $this->relationshipManager->syncTerm();
        $result = array();
        foreach ($dataDefault['data'] as $data) {
            $productConcept = new ProductConceptEntity(
                $data[self::RESPONSE]['ID'],
                true,
                array(
                    'post' => $data[self::RESPONSE],
                    'meta' => $data[self::METAS]
                )
            );
            $productConcept->setRelationshipManager($this->relationshipManager);

            global $wpdb;
            $id = $data[self::RESPONSE]['ID'];

            $queryC = "SELECT `to` FROM wp_mb_relationships WHERE `from` = {$id} AND `type` = 'pconcepts_to_concepts'";
            $resultListObjConcept = $wpdb->get_results($queryC, ARRAY_A);
            $listObjId = array_map(function ($e) {
                return $e['to'];
            }, $resultListObjConcept);

            $queryC = "SELECT `to` FROM wp_mb_relationships WHERE `from` = {$id} AND `type` = 'pconcepts_to_products'";
            $resultListObjProduct = $wpdb->get_results($queryC, ARRAY_A);
            $listObjId = array_map(function ($e) {
                return $e['to'];
            }, $resultListObjProduct);

            $product_concept_description = '';
            $product_concept_description = get_post_meta(intval($id), 'product_concept_description', TRUE);

            $product_concept_right_text = '';
            $product_concept_right_text = get_post_meta(intval($id), 'product_concept_right_text', TRUE);

            $product_concept_order = 0;
            $product_concept_order = get_post_meta(intval($id), 'product_concept_order', TRUE);            

            //Optimize pconcept
            
			if($resultListObjConcept!=[]&&isset($resultListObjProduct[0])){
				$arrayNewData = array($data);
				/*
				$arrayNewData['data']->id = intval($id);
				$arrayNewData['data']->product_id = intval($resultListObjProduct[0]['to']);
				$arrayNewData['data']->concept_id = intval($resultListObjConcept[0]['to']);
				$arrayNewData['data']->product_concept_description = $product_concept_description;
				$arrayNewData['data']->product_concept_right_text = $product_concept_right_text;
				$arrayNewData['data']->product_concept_order = intval($product_concept_order);
				*/
				
				$arrayNewData['data']['id'] = intval($id);
				$arrayNewData['data']['post_title'] = $data[self::RESPONSE]['post_title'];
				$arrayNewData['data']['product_id'] = intval($resultListObjProduct[0]['to']);
				$arrayNewData['data']['concept_id'] = intval($resultListObjConcept[0]['to']);
				$arrayNewData['data']['product_concept_description'] = $product_concept_description;
				$arrayNewData['data']['product_concept_right_text'] = $product_concept_right_text;
				$arrayNewData['data']['product_concept_order'] = intval($product_concept_order);
				
				$get_terms = get_the_terms( $id, 'sub-concept-category' );
				//print_r($get_terms);
				$array_term = array();
				if($get_terms){
					foreach ($get_terms as $term) {
						$array_term[] = $term->term_id;
					}
				}
				///$arrayNewData['data']->taxonomy_subconcept = $array_term;
				$arrayNewData['data']['taxonomy_subconcept'] = $array_term;
				///$result[] = (object)$arrayNewData;
				$result[] = $arrayNewData;

            //$result[] = $productConcept->toArray(BarnetEntity::$PUBLIC_ALL, false, false);
			}
        }

        return $result;

    }

    protected function getPost($request, $type)
    {
        $args = [
            'p' => $request['id'],
            self::POST_TYPE => $type,
        ];
        $post = get_posts($args);
        $metas = get_post_meta($request['id'], '');

        $objectName = explode('-', $type)[1];
        $relationships = preg_grep("~{$objectName}~", self::$RELATIONSHIP_ID);
        $relationshipResult = array();
        foreach ($relationships as $relationship) {
            $foreignObjectSpl = explode('_', $relationship);
            $foreignObject = strpos($foreignObjectSpl[0], $objectName) === false ? $foreignObjectSpl[0] : $foreignObjectSpl[2];
            $postType = 'barnet-' . ($foreignObject[-1] == 's' ? substr($foreignObject, 0, strlen($foreignObject) - 1) : $foreignObject);
            $relationshipResult[$foreignObject] = $this->getRelationship(
                $relationship,
                $postType,
                $foreignObject == $foreignObjectSpl[2] ? $request['id'] : null,
                $foreignObject == $foreignObjectSpl[0] ? $request['id'] : null);
        }

        return array(
            "data" => array(
                self::RESPONSE => $post[0],
                self::METAS => $metas,
                self::RELATIONSHIP => $relationshipResult
            )
        );
    }

    protected function getPosts($request, $type)
    {
        global $wpdb;
        $response = array();

        $query = "SELECT * FROM $wpdb->posts WHERE post_type = '$type' and post_status not in ('trash', 'draft', 'private', 'auto-draft') ORDER BY ";
        $query .= isset($request['order_by']) ? $request['order_by'] : 'post_title ';
        $query .= isset($request[self::ORDER]) ? $request[self::ORDER] : 'ASC';
        $query .= isset($request['limit']) && $request['limit'] > 0 ?
            " LIMIT {$request['limit']}" : "";

        $posts = BarnetDB::sql($query);
        $postMetaManager = new BarnetPostMetaManager($posts);
        foreach ($posts as $post) {
            $response["data"][] = array(
                self::RESPONSE => $post,
                self::METAS => $postMetaManager->getMetaData($post['ID'])
            );
        }

        return $response;
    }

    protected function getRelationship($id, $postType, $from = null, $to = null)
    {
        $relationship = array('id' => $id);

        if (isset($from)) {
            $relationship['from'] = $from;
        }

        if (isset($to)) {
            $relationship['to'] = $to;
        }

        $args = array(
            self::POST_TYPE => $postType,
            self::RELATIONSHIP => $relationship,
        );

        if ($postType == "barnet-digital-code") {
            $args[self::RELATIONSHIP]['id'] = 'products_to_digitals';
        }

        $result = new WP_Query($args);
        return $result->posts;
    }

    protected function getPostsByCreatedTime($postType, $dateTime)
    {
        $response = array();
        if ($postType == "attachment") {
            $args = array(
                self::POST_TYPE => 'attachment',
                self::NUMBERPOSTS => -1,
                self::POST_STATUS => null,
                'date_query' => array(
                    array(
                        'column' => 'post_modified',
                        'after' => array(
                            'year' => (int)$dateTime->format('Y'),
                            'month' => (int)$dateTime->format('m'),
                            'day' => (int)$dateTime->format('d'),
                            'hour' => (int)$dateTime->format('H'),
                            'minute' => (int)$dateTime->format('i'),
                            'second' => (int)$dateTime->format('s')
                        ),
                        'inclusive' => true,
                    )
                ),
                'post_parent' => null
            );
            $attachments = get_posts($args);
            if ($attachments) {
                foreach ($attachments as $post) {
                    $permalink = wp_get_attachment_url($post->ID);
                    $fileName = basename($permalink);
                    $ex = explode(".", $fileName);
                    $titleFile = sanitize_title($ex[0]);
                    $rss = array(
                        'id' => $post->ID,
                        'file' => $fileName,
                        self::DOMAIN => $_SERVER['SERVER_NAME']
                    );

                    $token = $this->secure->encode(serialize($rss));
                    $response["data"][] = array(
                        self::RESPONSE => array(
                            'id' => $post->ID,
                            'permalink' => get_rest_url() . "barnet/v1/attachment/$titleFile?file=$fileName&token=$token"
                        )

                    );
                }
            }
        } else {
            $posts = (new WP_Query(
                array(
                    self::POST_TYPE => array($postType),
                    'date_query' => array(
                        array(
                            'column' => 'post_modified',
                            'after' => array(
                                'year' => (int)$dateTime->format('Y'),
                                'month' => (int)$dateTime->format('m'),
                                'day' => (int)$dateTime->format('d'),
                                'hour' => (int)$dateTime->format('H'),
                                'minute' => (int)$dateTime->format('i'),
                                'second' => (int)$dateTime->format('s')
                            ),
                            'inclusive' => true,
                        )
                    ),
                    'posts_per_page' => -1,
                )
            ))->get_posts();
            $postMetaManager = new BarnetPostMetaManager($posts);
            foreach ($posts as $post) {
                $response["data"][] = array(
                    self::RESPONSE => $post,
                    self::METAS => $postMetaManager->getMetaData($post->ID)
                );
            }
        }

        return $response;
    }

    protected function syncMediaFilesPath()
    {
        if (!isset($this->medias)) {
            global $wpdb;
            $medias = $wpdb->get_results("SELECT post_id, meta_value FROM $wpdb->postmeta
                                                  WHERE meta_key = '_wp_attached_file';", ARRAY_A);
            if (count($medias) > 0) {
                $this->medias = array();
                foreach ($medias as $media) {
                    $this->medias[$media['post_id']] = $media['meta_value'];
                }
            }
        }

        return $this->medias;
    }

    protected function syncMediaAttachmentMetadata()
    {
        if (!isset($this->mediaAttachmentMetadata)) {
            global $wpdb;
            $medias = $wpdb->get_results("SELECT post_id, meta_value FROM $wpdb->postmeta
                                                  WHERE meta_key = '_wp_attachment_metadata';", ARRAY_A);
            if (count($medias) > 0) {
                $this->mediaAttachmentMetadata = array();
                foreach ($medias as $media) {
                    $this->mediaAttachmentMetadata[$media['post_id']] = $media['meta_value'];
                }
            }
        }
        return $this->mediaAttachmentMetadata;
    }

    public function getAnnoucements($request)
    {

        $args = array(
            self::POST_TYPE => 'barnet-annoucement',
            self::POST_STATUS => self::PUBLISH,
            self::NUMBERPOSTS => -1,
            'meta_query' => array(
                array(
                    'key' => 'an_device',
                    self::VALUE => array("both", "app"),
                    self::COMPARE => 'IN'
                ),
            )
        );

        $dataPosts = get_posts($args);
        $result = array();
        foreach ($dataPosts as $p) {
            $concept = new AnnoucementEntity($p->ID, true, array('post' => $p));
            $result[] = $concept->toArray(BarnetEntity::$PUBLIC_ALL);
        }

        return $result;
    }

    public function sampleRequest($request)
    {
        $firstName = empty($request->get_param("first_name")) ? '' : $request->get_param("first_name");
        $lastName = empty($request->get_param("last_name")) ? '' : $request->get_param("last_name");
        $email = empty($request->get_param(self::EMAIL)) ? '' : $request->get_param(self::EMAIL);
        $timestamp = empty($request->get_param("timestamp")) ? time() : $request->get_param("timestamp");
        $note = empty($request->get_param("addNote")) ? '' : $request->get_param("addNote");
        $companyName = empty($request->get_param("company_name")) ? '' : $request->get_param("company_name");
        $address = empty($request->get_param(self::ADDRESS)) ? '' : $request->get_param(self::ADDRESS);
        $addressOptional = $request->get_param("address_optional");
        $country = empty($request->get_param(self::COUNTRY)) ? '' : $request->get_param(self::COUNTRY);
        $province = empty($request->get_param(self::PROVINCE)) ? '' : $request->get_param(self::PROVINCE);
        $city = empty($request->get_param(self::CITY)) ? '' : $request->get_param(self::CITY);
        $postalCode = empty($request->get_param("postal_code")) ? '' : $request->get_param("postal_code");
        $phone = empty($request->get_param(self::PHONE)) ? '' : $request->get_param(self::PHONE);
        $phoneOptional = $request->get_param("phone_optional");
        $newsletter = empty($request->get_param(self::NEWSLETTER)) ? '' : $request->get_param(self::NEWSLETTER);
        $aboutUs = $request->get_param("about_us");
        $selectedSample = empty($request->get_param("selected_sample")) ? '' : $request->get_param("selected_sample");


        $dataPost = array(
            self::EMAIL => $email,
            'timestamp' => $timestamp,
            'firstName' => $firstName,
            'lastName' => $lastName,
            'companyName' => $companyName,
            self::ADDRESS => $address,
            'addressOptional' => $addressOptional,
            self::COUNTRY => $country,
            self::PROVINCE => $province,
            self::CITY => $city,
            'postalCode' => $postalCode,
            self::PHONE => $phone,
            'phoneOptional' => $phoneOptional,
            self::NEWSLETTER => $newsletter,
            'aboutUs' => $aboutUs,
            'addNote' => $note,
            'selectedSample' => $selectedSample
        );
        $sampleRequestSave = new BarnetSampleRequest();
        $rs = $sampleRequestSave->sampleRequestActionAPI($dataPost);
        if (is_wp_error($rs) && !empty($rs->errors)) {
            return new WP_REST_Response(
                array(
                    self::SUCCESS => false,
                    self::STATUSCODE => 500,
                    'code' => $rs->get_error_code(),
                    self::MESSAGE => $rs->get_error_message(),
                    'data' => array(),
                )
            );
        }

        return new WP_REST_Response(
            array(
                self::SUCCESS => true,
                self::STATUSCODE => 200,
                'code' => self::SUCCESS,
                self::MESSAGE => self::SUCCESS,
                'data' => $dataPost,
            )
        );
    }


    public function getProductCategories(){ 
        /* 
        $my_ids = get_posts(
            array(
                'post_type' => 'barnet-product',
                'posts_per_page' => -1,
                'fields' => 'ids',
            )
        );
        */
        $terms = get_terms( array(
            'taxonomy'   => 'product-category',
            //'object_ids' => $my_ids,
            'hide_empty' => false,
        ) );
        $result = array();
        
        foreach($terms as $term){
            $termItem = array();
            $termItem['term_id'] = $term->term_id;
            $termItem['name'] = $term->name;
            $termItem['slug'] = $term->slug;
            $termItem['term_group'] = $term->term_group;
            $termItem['term_taxonomy_id'] = $term->term_taxonomy_id;
            $termItem['taxonomy'] = $term->taxonomy;
            $termItem['description'] = $term->description;
            $termItem['parent'] = $term->parent;
            $termItem['count'] = $term->count;
            $termItem['filter'] = $term->filter;
            $termItem['is_hide'] = get_term_meta( $term->term_id, 'is_hide', true);
            $termItem['order'] = get_term_meta( $term->term_id, 'order', true);
            $result[] = $termItem;
        }        
        return $result;
    }

    public function getFormulaCategories(){ 
        /* 
        $my_ids = get_posts(
            array(
                'post_type' => 'barnet-product',
                'posts_per_page' => -1,
                'fields' => 'ids',
            )
        );
        */
        $terms = get_terms( array(
            'taxonomy'   => 'formula-category',
            //'object_ids' => $my_ids,
            'hide_empty' => false,
        ) );
        $result = array();
        
        foreach($terms as $term){
            $termItem = array();
            $termItem['term_id'] = $term->term_id;
            $termItem['name'] = $term->name;
            $termItem['slug'] = $term->slug;
            $termItem['term_group'] = $term->term_group;
            $termItem['term_taxonomy_id'] = $term->term_taxonomy_id;
            $termItem['taxonomy'] = $term->taxonomy;
            $termItem['description'] = $term->description;
            $termItem['parent'] = $term->parent;
            $termItem['count'] = $term->count;
            $termItem['filter'] = $term->filter;
            $termItem['image'] = get_term_meta( $term->term_id, 'image', true);
            $termItem['image_black'] = get_term_meta( $term->term_id, 'image_black', true);
            $termItem['order'] = get_term_meta( $term->term_id, 'order', true);
            $result[] = $termItem;
        }        
        return $result;
    }

    
    
    public function getFormulaAttributeSets(){  
        /*
        $my_ids = get_posts(
            array(
                'post_type' => 'barnet-product',
                'posts_per_page' => -1,
                'fields' => 'ids',
            )
        );
        */
        $terms = get_terms( array(
            'taxonomy'   => 'fattribute-set',
           // 'object_ids' => $my_ids,
            'hide_empty' => false,
        ) );
       
        $result = array();
        
        foreach($terms as $term){
            $termItem = array();
            $termItem['term_id'] = $term->term_id;
            $termItem['name'] = $term->name;
            $termItem['slug'] = $term->slug;
            $termItem['term_group'] = $term->term_group;
            $termItem['term_taxonomy_id'] = $term->term_taxonomy_id;
            $termItem['taxonomy'] = $term->taxonomy;
            $termItem['description'] = $term->description;
            $termItem['parent'] = $term->parent;
            $termItem['count'] = $term->count;
            $termItem['filter'] = $term->filter;
            $termItem['order'] = get_term_meta( $term->term_id, 'order', true);
            $result[] = $termItem;
        }        
        return $result;
    }

    

    public function getFormulaAttributes($request)
    {
        $dataDefault = $this->getPosts($request, "barnet-fattribute");
        $result = array();
        foreach ($dataDefault['data'] as $data) {
            $formulaAttribute = new FormulaAttributeEntity(
                $data[self::RESPONSE]['ID'],
                true,
                array(
                    'post' => $data[self::RESPONSE],
                    'meta' => $data[self::METAS]
                )
            );
            $result[] = $formulaAttribute->toArray(BarnetEntity::$PUBLIC_ALL);
        }

        return $result;
    }

    public function getProductSubConceptCategories(){  
        /*
        $my_ids = get_posts(
            array(
                'post_type' => 'barnet-product',
                'posts_per_page' => -1,
                'fields' => 'ids',
            )
        );
        */
        $terms = get_terms( array(
            'taxonomy'   => 'sub-concept-category',
           // 'object_ids' => $my_ids,
            'hide_empty' => false,
        ) );
       
        $result = array();
        
        foreach($terms as $term){
            $termItem = array();
            $termItem['term_id'] = $term->term_id;
            $termItem['name'] = $term->name;
            $termItem['slug'] = $term->slug;
            $termItem['term_group'] = $term->term_group;
            $termItem['term_taxonomy_id'] = $term->term_taxonomy_id;
            $termItem['taxonomy'] = $term->taxonomy;
            $termItem['description'] = $term->description;
            $termItem['parent'] = $term->parent;
            $termItem['count'] = $term->count;
            $termItem['filter'] = $term->filter;
            $termItem['image'] = get_term_meta( $term->term_id, 'image', true);
            $termItem['order'] = get_term_meta( $term->term_id, 'order', true);
            $result[] = $termItem;
        }        
        return $result;
    }

    public function getProductAttributeSets(){  
        /*
        $my_ids = get_posts(
            array(
                'post_type' => 'barnet-product',
                'posts_per_page' => -1,
                'fields' => 'ids',
            )
        );
        */
        $terms = get_terms( array(
            'taxonomy'   => 'attribute-set',
           // 'object_ids' => $my_ids,
            'hide_empty' => false,
        ) );
       
        $result = array();
        
        foreach($terms as $term){
            $termItem = array();
            $termItem['term_id'] = $term->term_id;
            $termItem['name'] = $term->name;
            $termItem['slug'] = $term->slug;
            $termItem['term_group'] = $term->term_group;
            $termItem['term_taxonomy_id'] = $term->term_taxonomy_id;
            $termItem['taxonomy'] = $term->taxonomy;
            $termItem['description'] = $term->description;
            $termItem['parent'] = $term->parent;
            $termItem['count'] = $term->count;
            $termItem['filter'] = $term->filter;
            $termItem['order'] = get_term_meta( $term->term_id, 'order', true);
            $result[] = $termItem;
        }        
        return $result;
    }

    public function getProductPConcept($request){    
        $from="0";  
        if(isset($request['from']))$from = $request['from'];
        $dateTime = new DateTime("@$from");
        //Convert to local time
        
        $localTime = get_option('timezone_string');
        $dateTime->setTimezone(new DateTimeZone($localTime)); 
        $dateTimeLocal = $dateTime->format("Y-m-d H:i:s");
        $dateT = strtotime($dateTimeLocal);
        //$dtimestringg=gmdate("Y-m-d\TH:i:s\Z", $dateT);
        //$dTime = new DateTime($dtimestringg);
        $dTime = new DateTime("@$dateT");

        $args = array(
            self::POST_TYPE => 'barnet-pconcept',
            self::POST_STATUS => self::PUBLISH,
            self::NUMBERPOSTS => -1,
            'date_query' => array(
                array(
                    'column' => 'post_modified',
                    'after' => array(
                        'year' => (int)$dTime->format('Y'),
                        'month' => (int)$dTime->format('m'),
                        'day' => (int)$dTime->format('d'),
                        'hour' => (int)$dTime->format('H'),
                        'minute' => (int)$dTime->format('i'),
                        'second' => (int)$dTime->format('s')
                    ),
                    'inclusive' => true,
                )
            ),
        );

        $dataPosts = get_posts($args);
        $result = array();

        foreach ($dataPosts as $p) {
            $postItem = array();
            $postId = $p -> ID;
            
            $postItem['id'] = $postId;

            $postItem['post_author'] =  $p -> post_author;
            $postItem['post_name'] =  $p -> post_name;
            $postItem['post_title'] =  $p -> post_title;
            $postItem['guid'] =  $p -> guid;
            
            $postItem['product_concept_description'] = get_post_meta( $postId, 'product_concept_description', true );
            $postItem['product_concept_right_text'] = get_post_meta( $postId, 'product_concept_right_text', true );
            $postItem['product_concept_order'] = get_post_meta( $postId, 'product_concept_order', true );
            $get_terms = get_the_terms( $postId, 'sub-concept-category' );
            //print_r($get_terms);
            $array_term = array();
            if($get_terms){
                foreach ($get_terms as $term) {
                    $array_term[] = $term->term_id;
                }
            }
            ///$arrayNewData['data']->taxonomy_subconcept = $array_term;
            $postItem['taxonomy_subconcept'] = $array_term;
            global $wpdb;
           

            $queryC = "SELECT `to` FROM wp_mb_relationships WHERE `from` = {$postId} AND `type` = 'pconcepts_to_concepts'";
            $resultListObjConcept = $wpdb->get_results($queryC, ARRAY_A);
            $listObjId = array_map(function ($e) {
                return $e['to'];
            }, $resultListObjConcept);

            $queryC = "SELECT `to` FROM wp_mb_relationships WHERE `from` = {$postId} AND `type` = 'pconcepts_to_products'";
            $resultListObjProduct = $wpdb->get_results($queryC, ARRAY_A);
            $listObjId = array_map(function ($e) {
                return $e['to'];
            }, $resultListObjProduct);      

            if($resultListObjConcept!=[]&&isset($resultListObjProduct[0])){
				
				$postItem['product_id'] = intval($resultListObjProduct[0]['to']);
				$postItem['concept_id'] = intval($resultListObjConcept[0]['to']);


            //$result[] = $productConcept->toArray(BarnetEntity::$PUBLIC_ALL, false, false);
			}
            $postItem['add_custom_body_class'] = get_post_meta( $postId, 'add_custom_body_class', true );
            $postItem['created_at'] =  $p -> post_date;
            $postItem['updated_at'] =  $p -> post_modified;
            $result[] = $postItem;
        }

        return $result;

    }



    public function getRelationShips(){
        global  $wpdb;   
        $queryC = "SELECT * FROM `wp_mb_relationships` ORDER BY `wp_mb_relationships`.`type` ASC";
        $result= $wpdb->get_results($queryC, ARRAY_A);
        return $result;

    }







    public function getSyncAnnoucements($request){  
        $from="0";  
        if(isset($request['from']))$from = $request['from'];
        $dateTime = new DateTime("@$from");
        //Convert to local time
        
        $localTime = get_option('timezone_string');
        $dateTime->setTimezone(new DateTimeZone($localTime)); 
        $dateTimeLocal = $dateTime->format("Y-m-d H:i:s");
        $dateT = strtotime($dateTimeLocal);
        $dTime = new DateTime("@$dateT");

        $args = array(
            self::POST_TYPE => 'barnet-annoucement',
            self::POST_STATUS => self::PUBLISH,
            self::NUMBERPOSTS => -1,
            
            'date_query' => array(
                array(
                    'column' => 'post_modified',
                    'after' => array(
                        'year' => (int)$dTime->format('Y'),
                        'month' => (int)$dTime->format('m'),
                        'day' => (int)$dTime->format('d'),
                        'hour' => (int)$dTime->format('H'),
                        'minute' => (int)$dTime->format('i'),
                        'second' => (int)$dTime->format('s')
                    ),
                    'inclusive' => true,
                )
            ),
            
            'meta_query' => array(
                array(
                    'key' => 'an_device',
                    self::VALUE => array("both", "app"),
                    self::COMPARE => 'IN'
                ),
            )
        );

        $dataPosts = get_posts($args);
        $result = array();

        foreach ($dataPosts as $p) {


            $postItem = array();
            $postId = $p -> ID;
            $postItem['id'] = $postId;
            $postItem['title'] =  $p -> post_title;
            $postItem['body'] = get_post_meta( $postId, 'an_description', true );
            $postItem['cta_url'] = get_post_meta( $postId, 'an_optional', true );
            $postItem['button_text'] = get_post_meta( $postId, 'an_btn_text', true );
            $postItem['button_type'] = get_post_meta( $postId, 'an_btn_type', true );
            $postItem['new_tab'] = get_post_meta( $postId, 'an_new_window', true );
            $postItem['alert_banner'] = get_post_meta( $postId, 'an_alert_banner', true );
            $postItem['alert_background_color'] = get_post_meta( $postId, 'an_alert_bg_color', true );
            $postItem['style'] = get_post_meta( $postId, 'an_style', true );
            $postItem['background_image'] = get_post_meta( $postId, 'an_bb_image', true );
            $postItem['expiration_date'] = get_post_meta( $postId, 'an_expirated_date', true );
            $postItem['location_type'] = get_post_meta( $postId, 'an_location_type', true );
            $postItem['show_in'] = get_post_meta( $postId, 'an_device', true );
            $postItem['region_type'] = get_post_meta( $postId, 'an_area', true );
            $postItem['created_at'] =  $p -> post_date;
            $postItem['updated_at'] =  $p -> post_modified;
            $result[] = $postItem;
        }

        return $result;

    }

    public function getSyncCodeLists($request){    
        $from="0";  
        if(isset($request['from']))$from = $request['from'];
        $dateTime = new DateTime("@$from");
        //Convert to local time
        
        $localTime = get_option('timezone_string');
        $dateTime->setTimezone(new DateTimeZone($localTime)); 
        $dateTimeLocal = $dateTime->format("Y-m-d H:i:s");
        $dateT = strtotime($dateTimeLocal);
        //$dtimestringg=gmdate("Y-m-d\TH:i:s\Z", $dateT);
        //$dTime = new DateTime($dtimestringg);
        $dTime = new DateTime("@$dateT");

        $args = array(
            self::POST_TYPE => 'barnet-digital-code',
            self::POST_STATUS => self::PUBLISH,
            self::NUMBERPOSTS => -1,
            'date_query' => array(
                array(
                    'column' => 'post_modified',
                    'after' => array(
                        'year' => (int)$dTime->format('Y'),
                        'month' => (int)$dTime->format('m'),
                        'day' => (int)$dTime->format('d'),
                        'hour' => (int)$dTime->format('H'),
                        'minute' => (int)$dTime->format('i'),
                        'second' => (int)$dTime->format('s')
                    ),
                    'inclusive' => true,
                )
            ),
        );

        $dataPosts = get_posts($args);
        $result = array();

        foreach ($dataPosts as $p) {
            $postItem = array();
            $postId = $p -> ID;
            $postItem['id'] = $postId;
            $postItem['title'] =  $p -> post_title;
            $postItem['code'] = get_post_meta( $postId, 'digital_code', true );
            $postItem['product_id'] = get_post_meta( $postId, 'products_to_digitals_from', true );
            $postItem['customer_id'] = get_post_meta( $postId, 'digitals_to_customers_to', true );

            $postItem['created_at'] =  $p -> post_date;
            $postItem['updated_at'] =  $p -> post_modified;
            $result[] = $postItem;
        }

        return $result;

    }


    public function getSyncCustomer($request){    
        $from="0";  
        if(isset($request['from']))$from = $request['from'];
        $dateTime = new DateTime("@$from");
        //Convert to local time
        
        $localTime = get_option('timezone_string');
        $dateTime->setTimezone(new DateTimeZone($localTime)); 
        $dateTimeLocal = $dateTime->format("Y-m-d H:i:s");
        $dateT = strtotime($dateTimeLocal);
        //$dtimestringg=gmdate("Y-m-d\TH:i:s\Z", $dateT);
        //$dTime = new DateTime($dtimestringg);
        $dTime = new DateTime("@$dateT");

        $args = array(
            self::POST_TYPE => 'barnet-customer',
            self::POST_STATUS => self::PUBLISH,
            self::NUMBERPOSTS => -1,
            'date_query' => array(
                array(
                    'column' => 'post_modified',
                    'after' => array(
                        'year' => (int)$dTime->format('Y'),
                        'month' => (int)$dTime->format('m'),
                        'day' => (int)$dTime->format('d'),
                        'hour' => (int)$dTime->format('H'),
                        'minute' => (int)$dTime->format('i'),
                        'second' => (int)$dTime->format('s')
                    ),
                    'inclusive' => true,
                )
            ),
        );

        $dataPosts = get_posts($args);

       // print_r($dataPosts);
        $result = array();

        foreach ($dataPosts as $p) {
            $postItem = array();
            $postId = $p -> ID;
            $postItem['id'] = $postId;
            $postItem['title'] =  $p -> post_title;
            $postItem['customer_roles'] = get_post_meta( $postId, 'customer_roles', false );
            //$postItem['product_id'] = get_post_meta( $postId, 'products_to_digitals_from', true );
            //$postItem['customer_id'] = get_post_meta( $postId, 'digitals_to_customers_to', true );

            $postItem['created_at'] =  $p -> post_date;
            $postItem['updated_at'] =  $p -> post_modified;
            $result[] = $postItem;
        }

        return $result;

    }

    


    

    public function getSyncConcepts($request){    
        $from="0";  
        if(isset($request['from']))$from = $request['from'];
        $dateTime = new DateTime("@$from");
        //Convert to local time
        
        $localTime = get_option('timezone_string');
        $dateTime->setTimezone(new DateTimeZone($localTime)); 
        $dateTimeLocal = $dateTime->format("Y-m-d H:i:s");
        $dateT = strtotime($dateTimeLocal);
        //$dtimestringg=gmdate("Y-m-d\TH:i:s\Z", $dateT);
        //$dTime = new DateTime($dtimestringg);
        $dTime = new DateTime("@$dateT");

        $args = array(
            self::POST_TYPE => 'barnet-concept',
            self::POST_STATUS => self::PUBLISH,
            self::NUMBERPOSTS => -1,
            'date_query' => array(
                array(
                    'column' => 'post_modified',
                    'after' => array(
                        'year' => (int)$dTime->format('Y'),
                        'month' => (int)$dTime->format('m'),
                        'day' => (int)$dTime->format('d'),
                        'hour' => (int)$dTime->format('H'),
                        'minute' => (int)$dTime->format('i'),
                        'second' => (int)$dTime->format('s')
                    ),
                    'inclusive' => true,
                )
            ),
        );

        $dataPosts = get_posts($args);
        $result = array();

        foreach ($dataPosts as $p) {
            $postItem = array();
            $postId = $p -> ID;
            $postItem['id'] = $postId;
            $postItem['title'] =  $p -> post_title;
            $postItem['type'] = get_post_meta( $postId, 'concept_type', true );
            $postItem['region_type'] = get_post_meta( $postId, 'concept_area', true );
            $postItem['header_style'] = get_post_meta( $postId, 'concept_style', true );
            $postItem['short_description'] = get_post_meta( $postId, 'concept_short_description', true );
            $postItem['description'] = get_post_meta( $postId, 'concept_description', true );
            $postItem['parent_id'] = get_post_meta( $postId, 'concept_parent', true );
            $postItem['order'] = get_post_meta( $postId, 'concept_order', true );
            $postItem['presentation'] = get_post_meta( $postId, 'concept_presention_docs', true );
            $postItem['video'] = get_post_meta( $postId, 'concept_videos_doc', true );
            $postItem['is_formula_collection'] = get_post_meta( $postId, 'concept_formula_collection', true );
            $postItem['keywords'] = get_post_meta( $postId, 'concept_keyword', true );
            $postItem['concept_keyword_custom'] = get_post_meta( $postId, 'concept_keyword_custom', true );          

            $postItem['created_at'] =  $p -> post_date;
            $postItem['updated_at'] =  $p -> post_modified;
            $result[] = $postItem;
        }

        return $result;

    }

    public function getSyncConceptBooks($request){    
        $from="0";  
        if(isset($request['from']))$from = $request['from'];
        $dateTime = new DateTime("@$from");
        //Convert to local time
        
        $localTime = get_option('timezone_string');
        $dateTime->setTimezone(new DateTimeZone($localTime)); 
        $dateTimeLocal = $dateTime->format("Y-m-d H:i:s");
        $dateT = strtotime($dateTimeLocal);
        //$dtimestringg=gmdate("Y-m-d\TH:i:s\Z", $dateT);
        //$dTime = new DateTime($dtimestringg);
        $dTime = new DateTime("@$dateT");

        $args = array(
            self::POST_TYPE => 'barnet-concept-book',
            self::POST_STATUS => self::PUBLISH,
            self::NUMBERPOSTS => -1,
            'date_query' => array(
                array(
                    'column' => 'post_modified',
                    'after' => array(
                        'year' => (int)$dTime->format('Y'),
                        'month' => (int)$dTime->format('m'),
                        'day' => (int)$dTime->format('d'),
                        'hour' => (int)$dTime->format('H'),
                        'minute' => (int)$dTime->format('i'),
                        'second' => (int)$dTime->format('s')
                    ),
                    'inclusive' => true,
                )
            ),
        );

        $dataPosts = get_posts($args);
        $result = array();

        foreach ($dataPosts as $p) {
            $postItem = array();
            $postId = $p -> ID;
            $postItem['id'] = $postId;
            $postItem['name'] =  $p -> post_title;
            $postItem['header_style'] = get_post_meta( $postId, 'concept_book_style', true );
            $postItem['region_type'] = get_post_meta( $postId, 'concept_book_area', true );
            $postItem['order'] = get_post_meta( $postId, 'concept_book_order', true );    

            $postItem['created_at'] =  $p -> post_date;
            $postItem['updated_at'] =  $p -> post_modified;
            $result[] = $postItem;
        }

        return $result;

    }


}




$barnetRestAPI = new BarnetRestAPI();