<?php

class Barnet
{
    const MIME_TYPE = 'mime_type';
    const RESPONSE_TEXT = 'response';
    const ADVANCE_TEXT = 'advance';
    const PERCENT_POINT = 'percent_point';
    const ACTIVE_TEXT = 'active';
    const UNIQUE_POINT = 'unique_point';
    const EXTRA_TEXT = 'extra';
    const MODIFIED_DATE = 'modified_date';
    const POST_TYPE_TEXT = 'post_type';
    const RELATION_KEY_TEXT = 'relation_key';
    const POST_MODIFIED_TEXT = 'post_modified';
    const CONFIG_SEARCHES = "/Config/searches.yml";
    const ORDER = "order";
    const RELATIONSHIP = "relationship";
    const TAXONOMY = "taxonomy";
    const GLOBAL_NOTICE_NONCE = "global_notice_nonce";
    const CAT_NAME = "cat_name";
    const CHILDREN = "children";
    const COUNTRIES = "countries";
    const WPNONCE = "_wpnonce";
    const SUBMIT = "submit";
    const REVERT = "revert";
    const BARNET_OPT_SA_PP_PP_ACTIVE = "barnet_opt_sa_pp_active";
    const BARNET_OPT_SA_SA_UP_ACTIVE = "barnet_opt_sa_up_active";
    const BARNET_OPT_SE_MD_ACTIVE = "barnet_opt_se_md_active";
    const BARNET_MENU_APP_ADMIN = 'edit.php?post_type=barnet-role';

    protected static $DEFAULT_ROLES = array('administrator', 'editor', 'contributor', 'author', 'subscriber');
    public static $EMAIL_CONTACT_LIST = array (
        'email_contact_sales' => 'Sales',
        'email_contact_marketing' => 'Marketing',
        'email_contact_cutomer_service' => 'Customer Service',
        'email_contact_global_compliance' => 'Global Compliance & Regulatory Affairs',
        'email_contact_samples' => 'Samples',
        'email_contact_global_innovation' => 'Global Innovation Center Lab'
    );

    protected static $LIST_TAX_ORDER = array(
        'formula-category',
        'product-category',
        'sub-concept-category',
        'concept-category',
        'resource-type',
        'resource-folder'
    );

    protected $yamlHelper;
    protected $barnetAuth;
    protected $barnetPageManager;
    protected $metaboxes;
    protected $dataType = array();
    protected $titlePlaceHolder = array();
    protected $mimeTypeAdv = array(
        'svg' => 'image/svg+xml'
    );

    protected $searchConfig = array();
    protected $sortCustomColumns = array();

    public function __construct()
    {
        $this->yamlHelper = new YamlHelper();
        $this->barnetPageManager = new BarnetPageManager();
        $this->barnetAuth = new \JWTAuth\BarnetAuth();

        $yamlHelper = new YamlHelper();
        if (file_exists(__DIR__ . self::CONFIG_SEARCHES)) {
            $this->searchConfig = $yamlHelper->loadFile(__DIR__ . self::CONFIG_SEARCHES);
        }
    }

    public function init()
    {
        foreach ($this->dataType as $dataType) {
            $dataType->init();
            $this->metaboxes[] = $dataType->addExt();
            if (count($dataType->addCustomFieldTaxonomy()) > 0) {
                $customFieldTaxonomies = $dataType->addCustomFieldTaxonomy();
                if (isset($customFieldTaxonomies['taxonomies'])) {
                    $this->metaboxes[] = $customFieldTaxonomies;
                } else {
                    $this->metaboxes = array_merge($this->metaboxes, $customFieldTaxonomies);
                }
            }

            $this->titlePlaceHolder = array_merge($this->titlePlaceHolder, $dataType->getTitlePlaceHolder());
            $dataType->removeRoleMetaBox();
        }

        // Add metabox
        add_filter('rwmb_meta_boxes', array($this, 'addMetaBox'));
        add_action('save_post', array($this, 'savePost'));

        // Change title placeholder
        add_filter('enter_title_here', array($this, 'changeTitle'));

        // Add new mime type upload (svg)
        add_filter('wp_check_filetype_and_ext', array($this, 'checkFileTypeAndExt'));
        add_filter('upload_mimes', array($this, 'allowMimeType'));
        add_action('admin_head', array($this, 'fixMimeAdminHead'));

        // Remove role when trash barnet-role
        add_action('wp_trash_post', array($this, 'trashPost'));
        add_action('untrash_post', array($this, 'unTrashPost'));

        // Custom filter taxonomy by product type
        add_filter('get_terms', array($this, 'reloadTerm'), 10, 3);

        // Remove taxonomy
        add_action('delete_term', array($this, 'deleteTerm'), 10, 3);

        add_filter('terms_clauses', array($this, 'addTermsClauses'), 10, 3);
        // Generate pages necessary
        add_action('admin_init', array($this, 'initPage'));

        // Create token when login
        add_action('wp_login', array($this, 'initWebLogin'), 10, 2);

        // Remove token when logout
        add_action('wp_logout', array($this, 'initWebLogout'));

        add_action('switch_to_user', array($this, 'switchToUser'));
        add_action('switch_back_user', array($this, 'switchToUser'));

        add_action('profile_update', array($this, 'profileUpdate'), 10, 2);

        // Add login expired to auto logout
        add_filter('auth_cookie_expiration', array($this, 'getLoginExpired'));

        add_filter('wp_insert_post_data', array($this, 'insertPostData'), 10, 2);

        add_filter('wp_handle_upload', array($this, 'unzipUploadFile'), 10, 2);
        add_filter('rwmb_ia_slide_link_choice_label', array($this, 'changeLabelSliderInteractiveLink'), 10, 3);
        add_filter( 'wp_update_attachment_metadata', array($this, 'updateAttach'), 10, 2 );
        add_action( 'edit_user_profile_update', array($this, 'updateUser'), 10, 1 );

        add_action('save_post', array($this, 'barnetClearSearchSessionCacheHook'), 10, 1);
        add_action('saved_term', array($this, 'barnetClearSearchSessionCacheFunc'));

        add_action('add_meta_boxes', [$this, 'product_type_meta_box']);
        
        
        // Add login expired field setting
        $this->addSettingField('login_expired_time', 'Login Expired (seconds)', 604800);

        $searchConfig = $this->yamlHelper->loadFile(__DIR__ . self::CONFIG_SEARCHES);
        if ($this->searchConfig['tools']['show_setting']) {
            $this->addSettingField(
                'search_setting',
                'Search Setting',
                json_encode($searchConfig),
                'textarea',
                'general',
                true
            );
        }

        // Add Order column in management table
        $this->addPostTypeColumnList(
            'barnet-concept',
            array(self::ORDER => 'Order'),
            array(self::ORDER => 'concept_order')
        );

        // Add Order column in management table
        $arrTag = self::$LIST_TAX_ORDER;
        if (!empty($arrTag)) {
            foreach ($arrTag as $v) {
                $this->addPostTypeColumnList(
                    $v,
                    array(self::ORDER => 'Order'),
                    array(self::ORDER => 'order'),
                    true,
                    false,
                    true
                );
            }
        }

        // Add Order column in management table
        $this->addPostTypeColumnList(
            'barnet-concept-book',
            array(self::ORDER => 'Order'),
            array(self::ORDER => 'concept_book_order')
        );

        $this->addPostTypeColumnList(
            'barnet-pattribute',
            array(self::ORDER => 'Order'),
            array(self::ORDER => 'product-attribute_order')
        );

        $this->addPostTypeColumnList(
            'barnet-pconcept',
            array(self::ORDER => 'Order'),
            array(self::ORDER => 'product_concept_order')
        );
        $this->addPostTypeColumnListSort(
            'barnet-pconcept',
            array(self::ORDER => 'product_concept_order')
        );

        // Add Code list column in management table
        $this->addPostTypeColumnList(
            'barnet-digital-code',
            array('code' => 'Code'),
            array('code' => 'digital_code')
        );

        // Add Code list column in management table
        $this->addPostTypeColumnList(
            'barnet-digital-code',
            array(
                'products' => 'Product',
                'customers' => 'Customer'
            ),
            array(
                'products' => 'products_to_digitals',
                'customers' => 'digitals_to_customers'
            ),
            false,
            true
        );

        $this->addPostTypeColumnList(
            'barnet-product',
            array('post_modified' => 'Date Modified'),
            array('post_modified' => 'post_modified'),
            false
        );
        $this->addPostTypeColumnList(
            'barnet-formula',
            array('post_modified' => 'Date Modified'),
            array('post_modified' => 'post_modified'),
            false
        );
        $this->addPostTypeColumnList(
            'barnet-concept',
            array('post_modified' => 'Date Modified'),
            array('post_modified' => 'post_modified'),
            false
        );

        add_action('wp_ajax_barnet_get_concept_interactive_image', array($this, 'ajaxGetInteractiveImage'));
        add_action('admin_print_footer_scripts-edit-tags.php', array($this, 'quickEditCategoryJavascript'));

        // Add Order to Quick Edit
        (new BarnetQuickEdit(
            self::ORDER,
            'Order',
            'number',
            'barnet-concept',
            'concept'
        ))->addToQuickEdit();

        add_action('post_submitbox_misc_actions', array($this, 'publishInLabelAdmin'));

        if ($this->searchConfig['tools']['show_setting']) {
            $this->showSearchSetting();
        }
        $this->showMultiUserRoleSelected();
        $this->addMenuAppAdmin();
        $this->addDeletedTable();
        $this->addUpdateTaxTable();
		$this->addSubMenuApiCache();

        /*add_filter('manage_posts_columns', function ($defaults) {
            $new = array();
            $valueCustomColumns = array();
            foreach ($this->sortCustomColumns as $sortCustomColumn) {
                $valueCustomColumns[$sortCustomColumn] = $defaults[$sortCustomColumn];
                unset($defaults[$sortCustomColumn]);
            }

            foreach ($defaults as $key => $value) {
                if ($key == 'date') {  // when we find the date column
                    foreach ($this->sortCustomColumns as $sortCustomColumn) {
                        $new[$sortCustomColumn] = $valueCustomColumns[$sortCustomColumn];
                    }
                }
                $new[$key] = $value;
            }

            return $new;
        });*/
    }


    public function product_type_meta_box(){
        remove_meta_box('tagsdiv-product-type', 'barnet-product', 'side');
        add_meta_box(
            'Product-Type-temp',
            'Product Type',
            [$this, 'display_product_type_metabox'],
            'barnet-product',
            'normal',
            'high'
        );
        add_meta_box(
            'Concept-Type-temp',
            'Concept Type',
            [$this, 'display_concept_type_metabox'],
            'barnet-concept',
            'normal',
            'high'
        );
        add_meta_box(
            'Product-Type-Page-temp',
            'Product Type',
            [$this, 'display_page_product_type_metabox'],
            'page',
            'side',
            'high'
        );
    }
	
	public function addSubMenuApiCache(){
        add_action('admin_menu', function () {
            add_submenu_page(
                'edit.php?post_type=barnet-role',
                'Api Cache',
                'Api Cache',
                'manage_options',
                'barnet-api-cache',
                function () {
					$storage_barnet_api_cache="database";
				    if(isset($_POST['storage_barnet_api_cache'])){ 
						$storage_barnet_api_cache=$_POST['storage_barnet_api_cache'];
					}
					
					$update_interval_api_cache="hourly";
				    if(isset($_POST['update_interval_api_cache'])){ 
						$update_interval_api_cache=$_POST['update_interval_api_cache'];
					}
					
					if($storage_barnet_api_cache!="database")update_option("storage_barnet_api_cache", $storage_barnet_api_cache);					
					$storage_barnet_api_cache=get_option("storage_barnet_api_cache","database");
					
					if($update_interval_api_cache!="hourly")update_option("update_interval_api_cache", $update_interval_api_cache);					
					$update_interval_api_cache=get_option("update_interval_api_cache","hourly");			
                    ?>
					<div>
						<form method="post">
							<?php wp_nonce_field('barnet-api-cache'); ?>
							<h1>Api Cache</h1>
							<table class="form-table">
								<tr valign="top">
									<th>
										<label>Automatic Cache Setting</label>
									</th>
								</tr>

								<tr>
									<th scope="row">
										<label for="update_interval_api_cache">Update Interval</label>
									</th>
									<td>
										<select name="update_interval_api_cache" id="update_interval_api_cache" value="<?php echo $update_interval_api_cache;?>">
											<option value="hourly" <?php if($update_interval_api_cache=="hourly")echo "selected"; ?>>Once Hourly</option>
											<option value="daily" <?php if($update_interval_api_cache=="daily")echo "selected"; ?>>Once Daily</option>
											<option value="twicedaily" <?php if($update_interval_api_cache=="twicedaily")echo "selected"; ?>>Twice Daily</option>
											<option value="weekly" <?php if($update_interval_api_cache=="weekly")echo "selected"; ?>>Once Weekly</option>
										</select>
									</td>
								</tr>
								<tr>
									<th scope="row">
										<label for="update_interval_api_cache">Cache Storage</label>
									</th>
									<td>
										<select name="storage_barnet_api_cache" id="storage_barnet_api_cache" value="<?php echo $storage_barnet_api_cache;?>">
											<option value="file" <?php if($storage_barnet_api_cache=="file")echo "selected"; ?>>File</option>
											<option value="database" <?php if($storage_barnet_api_cache=="database")echo "selected"; ?>>Database</option>
										</select>
									</td>
								</tr>
								<tr>
									<td>
										<?php submit_button(); ?>
										<p class="submit">
											<a class="button button-primary" href="<?php echo site_url().'/wp-admin/edit.php?post_type=barnet-role&page=barnet-api-cache&caching_barnet_api=true';?>">Update Cache</a>
										</p>										
										<p class="submit">
											<a class="button button-primary" href="<?php echo site_url().'/wp-admin/edit.php?post_type=barnet-role&page=barnet-api-cache&caching_barnet_api_manual=true';?>">Update Cache(manually)</a>
										</p>
									</td>
								</tr>
								
							</table>
						</form>
					</div>
	<?php
                }
            );
        });		
	}

    public function display_page_product_type_metabox($post){
        //$template_path = get_post_meta($post->ID, '_wp_page_template', true);
        //$template = str_replace('.php', '', basename($template));
        $template_path = get_page_template_slug($post->ID);
        
        echo '<input type="hidden" id="current_page_template" value="'.$template_path.'" />';
        echo '<select name="page-product-type" id="page-product-type">';
        $terms = get_terms(array(
            'taxonomy'   => 'product-type',
            'hide_empty' => false,
        ) );     
        $page_product_type = get_post_meta( $post->ID, 'page-product-type', true );
        echo '<option value="">Select Product Type</option>';
        foreach ($terms as $term) {
            $selected="";            
            if($page_product_type && $page_product_type==$term->term_id)$selected="selected";
            echo '<option value="' . $term->term_id . '"'.$selected.'>' . $term->name . '</option>';
        }   
        echo '</select>';
    }

    

    public function display_concept_type_metabox($post){       
        $unupdated="";
		$barnet_concept_type_updated=get_option("barnet_concept_type_updated");  
        if(isset($_GET['unupdated'])=='unupdated' && !$barnet_concept_type_updated){
            $updated_flag=true;
            $my_ids = get_posts(
                array(
                    'post_type' => 'barnet-concept',
                    'posts_per_page' => -1,
                    'fields' => 'ids',
                )
            );
    
            $terms1 = get_terms(array(
                'taxonomy'   => 'concept-type',
                'hide_empty' => false,
                'fields' => 'ids',
            ) );       
            foreach($my_ids as $id){
                $concept_type=get_post_meta( $id, 'concept_type', true ); 

                //wp_remove_object_terms( $id, $terms1, 'product-type' );
			   //$product_type_term_pre = get_term_by('name', $prduct_type, 'product-type');
				//delete_post_meta( $id, 'product_type_term', $product_type_term_pre->term_id );
               
                if($concept_type){
						wp_insert_term($concept_type, 'concept-type');
						$concept_type_term = get_term_by('name', $concept_type, 'concept-type');
						update_post_meta( $id, 'concept_type_term', $concept_type_term->term_id );
                } 
								
            }
            if($updated_flag)add_option("barnet_concept_type_updated", 1);
        }
        
       if(!$barnet_concept_type_updated){
            echo '<input type="hidden" id="barnet_concept_type_updated" value="unupdated" />';
        }
        echo '<select name="concept_type_term" id="concept_type_term">';
        $terms = get_terms(array(
            'taxonomy'   => 'concept-type',
            'hide_empty' => false,
        ) );
		
		$concept_type_term_id = get_post_meta( $post->ID, 'concept_type_term', true );
        //$product_term = wp_get_post_terms( $post->ID, 'product-type', array( 'fields' => 'ids' ) ); 
        echo '<option value="">Select Concept Type</option>';
        foreach ($terms as $term) {
            $selected="";            
            if($concept_type_term_id && $concept_type_term_id==$term->term_id)$selected="selected";
            echo '<option value="' . $term->term_id . '"'.$selected.'>' . $term->name . '</option>';
        }   
        echo '</select>';
    }

    public function display_product_type_metabox($post){       
        $unupdated="";
		$barnet_product_type_updated=get_option("barnet_product_type_updated");  
        if(isset($_GET['unupdated'])=='unupdated' && !$barnet_product_type_updated){
            $updated_flag=true;
            $my_ids = get_posts(
                array(
                    'post_type' => 'barnet-product',
                    'posts_per_page' => -1,
                    'fields' => 'ids',
                )
            );
    
            $terms1 = get_terms(array(
                'taxonomy'   => 'product-type',
                'hide_empty' => false,
                'fields' => 'ids',
            ) );       
            foreach($my_ids as $id){
                $prduct_type=get_post_meta( $id, 'product_type', true ); 

                //wp_remove_object_terms( $id, $terms1, 'product-type' );
			   //$product_type_term_pre = get_term_by('name', $prduct_type, 'product-type');
				//delete_post_meta( $id, 'product_type_term', $product_type_term_pre->term_id );
               
                if($prduct_type){
						wp_insert_term($prduct_type, 'product-type');
						$product_type_term = get_term_by('name', $prduct_type, 'product-type');
						update_post_meta( $id, 'product_type_term', $product_type_term->term_id );
                } 
								
            }
            if($updated_flag)add_option("barnet_product_type_updated", 1);
        }
        
       if(!$barnet_product_type_updated){
            echo '<input type="hidden" id="barnet_product_type_updated" value="unupdated" />';
        }
        echo '<select name="product_type_term" id="product_type_term">';
        $terms = get_terms(array(
            'taxonomy'   => 'product-type',
            'hide_empty' => false,
        ) );
		
		$product_type_term_id = get_post_meta( $post->ID, 'product_type_term', true );
        //$product_term = wp_get_post_terms( $post->ID, 'product-type', array( 'fields' => 'ids' ) ); 
        echo '<option value="">Select Product Type</option>';
        foreach ($terms as $term) {
            $selected="";            
            if($product_type_term_id && $product_type_term_id==$term->term_id)$selected="selected";
            echo '<option value="' . $term->term_id . '"'.$selected.'>' . $term->name . '</option>';
        }   
        echo '</select>';
    }

    public function updateUser($user_id) {
        $user_info = get_userdata( $user_id );
        if (in_array('administrator', $user_info->roles)) {
            $termsSendApp = array();
            $termName = 'resource-folder';
            $postType = 'barnet-resource';
            $arrayType = array('application/pdf', 'diagram', 'video/mp4');
            $restApi = new BarnetRestAPI();
            $termsSendApp = $restApi->getAllTerm($termName);
            foreach ($termsSendApp as $tmp) {
                $allPost = $restApi->getAllPostByTerm($termName, $tmp->term_id, $postType);
                foreach ($allPost as $post) {
                    $postContent = get_post_meta($post->ID);
                    $mediaID = $postContent['resource_media'][0];
                    $type = get_post_mime_type($mediaID);
                    if ( in_array($type, $arrayType) ) {
                        $arg = array(
                            'ID' => $post->ID,
                        );
                        wp_update_post( $arg );
                    }
                }
            }
        }
        wp_update_term( 1353, 'resource-folder', $args = array() );
    }

    public function updateAttach($data, $attachment_id) {
        $posts = get_posts(array(
            'post_type' => 'barnet-resource',
            'numberposts' => -1,
            'post_status' => null,
            'meta_query' => array(
                array(
                    'key' => 'resource_media',
                    'value' => $attachment_id,
                ),
            ),
        ));
        foreach($posts as $post) {
            $arg = array(
                'ID' => $post->ID,
            );
            wp_update_post( $arg );
        }
        return true;
    }

    function publishInLabelAdmin($post)
    {
        echo '<div class="misc-pub-section misc-pub-modified">
                <span id="bn-timestamp-modified">
                    Last Modified: <b>' . get_post_modified_time('M j, Y \a\t H:i', true, $post->ID, false) . '</b>
                </span>
                </div>';
    }

    public function quickEditCategoryJavascript()
    {
        $arrTag = self::$LIST_TAX_ORDER;
        if (!empty($arrTag)) {
            $current_screen = get_current_screen();
            $showJs = false;
            foreach ($arrTag as $v) {
                if ($current_screen->id == 'edit-'.$v && $current_screen->taxonomy == $v) {
                    $showJs = true;
                }
            }

            if ($showJs) {
                echo '<script type="text/javascript">jQuery(function(t){var e=inlineEditTax.edit;inlineEditTax.edit=function(i){e.apply(this,arguments);var n=0;if("object"==typeof i&&(n=parseInt(this.getId(i))),n>0){var a=t("#edit-"+n),r=t("#tag-"+n),d=t(".column-order",r).text();"Yes"==t(".column-featured",r).text()&&(featured_product=!0),t(\':input[name="order"]\',a).val(d)}}});</script>';
            }
        }

    }

    public function savePost($postId)
    {
        $this->saveExt($postId);
        $this->syncRole($postId);
        $this->updateProductCustomKeywords($postId);
        $this->updateFormulaCustomKeywords($postId);
        $this->updateConceptCustomKeywords($postId);
    }

    public function setMetaBox($metaBoxs)
    {
        $this->metaboxes = $metaBoxs;
    }

    public function addMetaBox()
    {
        return $this->metaboxes;
    }

    public function changeTitle($title)
    {
        $screen = get_current_screen();
        if (isset($this->titlePlaceHolder[$screen->post_type])) {
            $title = $this->titlePlaceHolder[$screen->post_type];
        }

        return $title;
    }

    public function addDataType($dataType)
    {
        $this->dataType[] = $dataType;
        return $this;
    }

    public function getDataTypes()
    {
        return $this->dataType;
    }

    public function clearDataType()
    {
        $this->dataType = array();
        return $this;
    }

    public function checkFileTypeAndExt($data, $file = null, $filename = null, $mimes = null)
    {
        global $wp_version;
        if ($wp_version !== '4.7.1') {
            return $data;
        }

        $filetype = wp_check_filetype($filename, $mimes);

        return array(
            'ext' => $filetype['ext'],
            'type' => $filetype['type'],
            'proper_filename' => $data['proper_filename']
        );
    }

    public function allowMimeType($mimes)
    {
        return array_merge($mimes, $this->mimeTypeAdv);
    }

    public function fixMimeAdminHead()
    {
        echo '<style type="text/css">
        .attachment-266x266, .thumbnail img {
             width: 100% !important;
             height: auto !important;
        }
        </style>';
    }

    public function insertPostData($data, $postArr)
    {
        if (isset($_POST[self::POST_TYPE_TEXT]) && isset($this->searchConfig[self::RELATIONSHIP])) {
            global $wpdb;

            foreach ($this->searchConfig[self::RELATIONSHIP] as $postType => $config) {
                foreach ($config as $configName => $configData) {
                    $fieldName = DataHelper::compactString($configName, '_');
                    foreach ($configData as $rlPostType => $rlData) {
                        if ($rlPostType != $_POST[self::POST_TYPE_TEXT]) {
                            continue;
                        }

                        $relationKey = isset($rlData[self::RELATION_KEY_TEXT]) ? $rlData[self::RELATION_KEY_TEXT] : null;
                        $objSplit = explode('_to_', $relationKey);

                        if ($rlPostType == trim("barnet-{$objSplit[0]}", 's')) {
                            $wpQuery = "SELECT `to` FROM wp_mb_relationships WHERE `type` = '$relationKey' AND `from` = {$postArr['ID']}";
                        } elseif ($rlPostType == trim("barnet-{$objSplit[1]}", 's')) {
                            $wpQuery = "SELECT `from` FROM wp_mb_relationships WHERE `type` = '$relationKey' AND `to` = {$postArr['ID']}";
                        } else {
                            continue;
                        }

                        $unsetPostIds = array_map(function ($e) {
                            return isset($e['from']) ? $e['from'] : $e['to'];
                        }, $wpdb->get_results($wpQuery, ARRAY_A));

                        $fieldName .= '_' . DataHelper::compactString($rlPostType);
                        $fieldUpdate = array();
                        foreach ($rlData as $rlFieldKey => $rlPoint) {
                            if ($rlFieldKey == self::RELATION_KEY_TEXT) {
                                continue;
                            }

                            if ($rlFieldKey != self::TAXONOMY) {
                                $fieldUpdate[] = $fieldName . '_' . DataHelper::compactString($rlFieldKey, '_');
                            } else {
                                $fieldName .= '_tax';
                                foreach ($rlPoint as $taxName => $taxData) {
                                    $terms = get_the_terms($postArr['ID'], $taxName);
                                    if (!$terms) {
                                        continue;
                                    }

                                    $fieldName .= '_' . DataHelper::compactString($taxName);
                                    foreach ($taxData as $taxField => $taxValue) {
                                        $fieldUpdate[] = $fieldName . '_' . $taxField;
                                    }
                                }
                            }
                        }

                        foreach ($unsetPostIds as $unsetPostId) {
                            foreach ($fieldUpdate as $fieldUnset) {
                                $metaData = get_post_meta($unsetPostId, $fieldUnset);
                                if (isset($metaData[0])) {
                                    $metaData = $metaData[0];
                                }

                                if (is_array($metaData) && isset($metaData[$postArr['ID']])) {
                                    unset($metaData[$postArr['ID']]);
                                }


                                update_post_meta($unsetPostId, $fieldUnset, $metaData);
                            }
                        }
                    }
                }
            }
        }
        
        if (is_plugin_active('wp-rest-cache/wp-rest-cache.php')) {
            include_once __DIR__ . "/Common/CacheManager.php";
            $userId = get_current_user_id();
            $caching = new CacheManager();
            $caching->flushUser($userId);
        }

        return $data;
    }

    protected function saveExt($postId)
    {
        if (isset($_POST[self::POST_TYPE_TEXT]) && isset($this->searchConfig[self::RELATIONSHIP])) {
            global $wpdb;

            foreach ($this->searchConfig[self::RELATIONSHIP] as $postType => $config) {
                foreach ($config as $configName => $configData) {
                    $fieldName = DataHelper::compactString($configName, '_');
                    foreach ($configData as $rlPostType => $rlData) {
                        if ($rlPostType != $_POST[self::POST_TYPE_TEXT]) {
                            continue;
                        }

                        $relationKey = isset($rlData[self::RELATION_KEY_TEXT]) ? $rlData[self::RELATION_KEY_TEXT] : null;
                        $objSplit = explode('_to_', $relationKey);

                        if ($rlPostType == trim("barnet-{$objSplit[0]}", 's')) {
                            $wpQuery = "SELECT `to` FROM wp_mb_relationships WHERE `type` = '$relationKey' AND `from` = $postId";
                        } elseif ($rlPostType == trim("barnet-{$objSplit[1]}", 's')) {
                            $wpQuery = "SELECT `from` FROM wp_mb_relationships WHERE `type` = '$relationKey' AND `to` = $postId";
                        } else {
                            continue;
                        }

                        $updatePostIds = array_map(function ($e) {
                            return isset($e['from']) ? $e['from'] : $e['to'];
                        }, $wpdb->get_results($wpQuery, ARRAY_A));

                        $fieldName .= '_' . DataHelper::compactString($rlPostType);
                        $dataUpdate = array();
                        foreach ($rlData as $rlFieldKey => $rlPoint) {
                            if ($rlFieldKey == self::RELATION_KEY_TEXT) {
                                continue;
                            }

                            if ($rlFieldKey != self::TAXONOMY) {
                                $updateField = $fieldName . '_' . DataHelper::compactString($rlFieldKey, '_');
                                $dataUpdate[$updateField] = $_POST[$rlFieldKey];
                            } else {
                                $fieldName .= '_tax';
                                foreach ($rlPoint as $taxName => $taxData) {
                                    $terms = get_the_terms($postId, $taxName);
                                    if (!$terms) {
                                        continue;
                                    }

                                    $fieldName .= '_' . DataHelper::compactString($taxName);
                                    foreach ($taxData as $taxField => $taxValue) {
                                        $updateField = $fieldName . '_' . $taxField;
                                        $dataUpdate[$updateField] = $terms[0]->$taxField;
                                    }
                                }
                            }
                        }

                        foreach ($updatePostIds as $updatePostId) {
                            foreach ($dataUpdate as $fieldUpdate => $valueUpdate) {
                                $metaData = get_post_meta($updatePostId, $fieldUpdate);
                                if (isset($metaData[0])) {
                                    $metaData = $metaData[0];
                                }

                                $metaData[$postId] = $valueUpdate;
                                update_post_meta($updatePostId, $fieldUpdate, $metaData);
                            }
                        }
                    }
                }
            }
        }

        global $wpdb;

        $deletePostLists = array_map(function ($e) {
            return $e['meta_value'];
        }, $wpdb->get_results("SELECT meta_value FROM wp_postmeta WHERE meta_key = 'resource_ppt_upload'", ARRAY_A));

        $wpdb->delete('wp_postmeta', array('meta_key' => 'resource_ppt_upload'));
        if (count($deletePostLists) > 0) {
            $wpdb->query('DELETE FROM wp_posts WHERE ID in (' . implode(',', $deletePostLists) . ')');
        }

        // Check if our nonce is set.
        if (!isset($_POST[self::GLOBAL_NOTICE_NONCE])) {
            return;
        }

        // Verify that the nonce is valid.
        if (!wp_verify_nonce($_POST[self::GLOBAL_NOTICE_NONCE], self::GLOBAL_NOTICE_NONCE)) {
            return;
        }

        // If this is an autosave, our form has not been submitted, so we don't want to do anything.
        if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
            return;
        }

        // Check the user's permissions.
        if (isset($_POST[self::POST_TYPE_TEXT]) && 'page' == $_POST[self::POST_TYPE_TEXT]) {
            if (!current_user_can('edit_page', $postId)) {
                return;
            }
        } else {
            if (!current_user_can('edit_post', $postId)) {
                return;
            }
        }

        /* OK, it's safe for us to save the data now. */

        // Make sure that it is set.
        if (!isset($_POST['global_notice'])) {
            return;
        }

        // Sanitize user input.
        $my_data = sanitize_text_field($_POST['global_notice']);

        // Update the meta field in the database.
        update_post_meta($postId, '_global_notice', $my_data);
    }

    protected function syncRole($postId)
    {
        $post = get_post($postId);
        $postType = $post->post_type;

        if ($postType != 'barnet-role') {
            return;
        }
        //Update code list will update folder Code List
        if ($postType == 'barnet-customer' || $postType == 'barnet-digital-code') {
            wp_update_term( 1353, 'resource-folder', $args = array() );
        }

        global $wp_roles;

        $allRoles = $wp_roles->roles;
        $allRolesName = array_keys($allRoles);
        $postName = $post->post_title;

        if (in_array(strtolower($postName), $allRolesName)) {
            return;
        }

        add_role(strtolower($postName), ucfirst($postName), get_role('subscriber')->capabilities);
    }

    // update custom keywords field on products
    // takes a product's related data posts, and turns it into a meta field on the product
    protected function updateProductCustomKeywords($postId)
    {
        $post = get_post($postId);

        // Check the post type to make sure it's a product
        if ('barnet-product' !== $post->post_type)
            return;

        $keywords = [];

        // get product categories as keywords
        $terms = get_the_terms($post, BarnetProduct::PRODUCTCATEGORY);
        if (!empty($terms)) {
            foreach ($terms as $term) {
                $keywords[] = $term->name;
            }
        }
        // get our related concepts as keywords
        $relatedConcepts = MB_Relationships_API::get_connected([ 'id' => 'products_to_concepts', 'from' => $post->ID ]);
        foreach ($relatedConcepts as $attr) {
            $keywords[] = $attr->post_title;
        }
        // get our related formulas as keywords
        $relatedFormulas = MB_Relationships_API::get_connected([ 'id' => 'products_to_formulas', 'from' => $post->ID ]);
        foreach ($relatedFormulas as $attr) {
            $keywords[] = $attr->post_title;
        }
        // get our key attributes as keywords
        $keyAttributes = MB_Relationships_API::get_connected([ 'id' => 'products_to_pattributes', 'from' => $post->ID ]);
        foreach ($keyAttributes as $attr) {
            $keywords[] = $attr->post_title;
        }

        // get our product-concept mappings
        $relatedProductConcepts = MB_Relationships_API::get_connected([ 'id' => 'pconcepts_to_products', 'to' => $post->ID ]);
        foreach ($relatedProductConcepts as $attr) {
            // find the concept through the product-concept mapping
            // and then get the keywords from it
            $moreRelatedConcepts = MB_Relationships_API::get_connected([ 'id' => 'pconcepts_to_concepts', 'from' => $attr->ID ]);
            foreach ($moreRelatedConcepts as $concept) {
                $keywords[] = $concept->post_title;
            }
        }

        // get our related resources as keywords
        $relatedResources = MB_Relationships_API::get_connected([ 'id' => 'resources_to_products', 'to' => $post->ID ]);
        foreach ($relatedResources as $attr) {
            $keywords[] = $attr->post_title;
        }

        // remove all non-alpha characters
        $keywords = array_map(function ($key) { return preg_replace("/[^A-Za-z0-9]+/", ' ', $key); }, $keywords);
        // lowercase all keywords
        $keywords = array_map(function ($key) { return strtolower($key); }, $keywords);
        // trim whitespace just in case
        $keywords = array_map(function ($key) { return trim($key); }, $keywords);
        // only grab unique keywords
        $keywords = array_unique($keywords);

        // update the product's custom keywords
        update_post_meta($post->ID, 'product_keyword_custom', implode(", ", $keywords));
    }

    // update custom keywords field on formulas
    protected function updateFormulaCustomKeywords($postId)
    {
        $post = get_post($postId);

        // Check the post type to make sure it's a formula
        if ('barnet-formula' !== $post->post_type)
            return;

        $keywords = [];

        // get formula categories as keywords
        $terms = get_the_terms($post, BarnetFormula::FORMULA_CATEGORY);
        if (!empty($terms)) {
            foreach ($terms as $term) {
                $keywords[] = $term->name;
            }
        }
        // get our related concepts as keywords
        $relatedConcepts = MB_Relationships_API::get_connected([ 'id' => 'formulas_to_concepts', 'from' => $post->ID ]);
        foreach ($relatedConcepts as $attr) {
            $keywords[] = $attr->post_title;
        }
        // get our key attributes as keywords
        $keyAttributes = MB_Relationships_API::get_connected([ 'id' => 'formulas_to_fattributes', 'from' => $post->ID ]);
        foreach ($keyAttributes as $attr) {
            $keywords[] = $attr->post_title;
        }
        // get our related formula as keywords
        $keyAttributes = MB_Relationships_API::get_connected([ 'id' => 'formulas_to_formulas', 'from' => $post->ID ]);
        foreach ($keyAttributes as $attr) {
            $keywords[] = $attr->post_title;
        }
        
        // get our related resources as keywords
        $relatedResources = MB_Relationships_API::get_connected([ 'id' => 'resources_to_formulas', 'to' => $post->ID ]);
        foreach ($relatedResources as $attr) {
            $keywords[] = $attr->post_title;
        }

        // remove all non-alpha characters
        $keywords = array_map(function ($key) { return preg_replace("/[^A-Za-z0-9]+/", ' ', $key); }, $keywords);
        // lowercase all keywords
        $keywords = array_map(function ($key) { return strtolower($key); }, $keywords);
        // trim whitespace just in case
        $keywords = array_map(function ($key) { return trim($key); }, $keywords);
        // only grab unique keywords
        $keywords = array_unique($keywords);

        // update the formula's custom keywords
        update_post_meta($post->ID, 'formula_keyword_custom', implode(", ", $keywords));
    }

    // update custom keywords field on concepts
    protected function updateConceptCustomKeywords($postId)
    {
        $post = get_post($postId);

        // Check the post type to make sure it's a concept
        if ('barnet-concept' !== $post->post_type)
            return;

        $keywords = [];

        // get concept sections/categories as keywords
        $terms = get_the_terms($post, BarnetConcept::CONCEPT_CATEGORY);
        if (!empty($term)) {
            foreach ($terms as $term) {
                $keywords[] = $term->name;
            }
        }
        // get our related concepts as keywords
        $relatedConcepts = MB_Relationships_API::get_connected([ 'id' => 'concepts_to_concepts', 'from' => $post->ID ]);
        foreach ($relatedConcepts as $attr) {
            $keywords[] = $attr->post_title;
        }
        // get our related formulas as keywords
        $relatedFormulas = MB_Relationships_API::get_connected([ 'id' => 'formulas_to_concepts', 'to' => $post->ID ]);
        foreach ($relatedFormulas as $attr) {
            $keywords[] = $attr->post_title;
        }

        // get our product-concept mappings
        $relatedProductConcepts = MB_Relationships_API::get_connected([ 'id' => 'pconcepts_to_concepts', 'to' => $post->ID ]);
        foreach ($relatedProductConcepts as $attr) {
            // find the product through the product-concept mapping
            // and then get the keywords from it
            $moreRelatedProducts = MB_Relationships_API::get_connected([ 'id' => 'pconcepts_to_products', 'from' => $attr->ID ]);
            foreach ($moreRelatedProducts as $product) {
                $keywords[] = $product->post_title;
            }
        }
        
        // get our related resources as keywords
        $relatedResources = MB_Relationships_API::get_connected([ 'id' => 'concepts_to_resources', 'from' => $post->ID ]);
        foreach ($relatedResources as $attr) {
            $keywords[] = $attr->post_title;
        }
        // more related resources
        $relatedResources = MB_Relationships_API::get_connected([ 'id' => 'resources_to_concepts', 'to' => $post->ID ]);
        foreach ($relatedResources as $attr) {
            $keywords[] = $attr->post_title;
        }

        // remove all non-alpha characters
        $keywords = array_map(function ($key) { return preg_replace("/[^A-Za-z0-9]+/", ' ', $key); }, $keywords);
        // lowercase all keywords
        $keywords = array_map(function ($key) { return strtolower($key); }, $keywords);
        // trim whitespace just in case
        $keywords = array_map(function ($key) { return trim($key); }, $keywords);
        // only grab unique keywords
        $keywords = array_unique($keywords);

        // update the concept's custom keywords
        update_post_meta($post->ID, 'concept_keyword_custom', implode(", ", $keywords));
    }

    public function trashPost($postId)
    {
        $post = get_post($postId);
        $postType = $post->post_type;

        $barnetPostType = array(
            'barnet-product',
            'barnet-formula',
            'barnet-concept',
            'barnet-customer',
            'barnet-digital-code',
            'barnet-pattribute',
            'barnet-pconcept',
            'barnet-resource',
            'barnet-concept-book',
        );

        if ($postType == 'barnet-role') {
            $roleName = strtolower($post->post_title);

            if (in_array($roleName, self::$DEFAULT_ROLES)) {
                add_action('admin_notices', function () {
                    ?>
                    <div class="notice notice-error">
                        <p><?php echo "You can't remove default roles"; ?></p>
                    </div>
                    <?php
                });
                return;
            }

            if (get_role($roleName)) {
                global $wpdb;
                $wpRolesResult = $wpdb->get_col(
                    $wpdb->prepare("SELECT option_value FROM wp_options where option_name = '%s';", 'wp_user_roles')
                );

                if (count($wpRolesResult) > 0) {
                    $wpRoles = unserialize($wpRolesResult[0]);
                    unset($wpRoles[$roleName]);
                    $wpdb->query($wpdb->prepare("UPDATE wp_options SET option_value='%s' WHERE option_name='%s'", serialize($wpRoles), 'wp_user_roles'));
                }
            }
        } elseif (in_array($postType, $barnetPostType)) {
            global $wpdb;
            $wpdb->insert(
                $wpdb->barnet_deleted,
                array(
                    'post_id' => $postId,
                    'post_type' => $postType,
                    'timestamp' => time()
                )
            );
        }
    }

    public function unTrashPost($postId)
    {
        global $wpdb;
        $post = get_post($postId);
        $postType = $post->post_type;
        $wpdb->delete($wpdb->barnet_deleted, array('post_id' => $postId, 'post_type' => $postType));
    }

    public function deleteTerm($term_id, $tt_id, $taxonomy)
    {
        global $wpdb;
        $wpdb->insert(
            $wpdb->barnet_deleted,
            array(
                'post_id' => $term_id,
                'post_type' => $taxonomy,
                'timestamp' => time()
            )
        );
    }

    public function editTerm($term_id, $tt_id, $taxonomy)
    {
        global $wpdb;
        $wpdb->replace(
            $wpdb->barnet_tax_update,
            array(
                'tax_id' => $term_id,
                'tax_type' => $taxonomy,
                'timestamp' => time()
            )
        );
        $allPost = get_posts(array(
            'post_type' => 'barnet-resource',
            'tax_query' => array(
                array(
                'taxonomy' => $taxonomy,
                'field' => 'term_id',
                'terms' => $term_id)
            ))
        );
        foreach ($allPost as $post) {
            $arg = array(
                'ID' => $post->ID,
            );
            wp_update_post( $arg );
        }
    }

    public function addTermsClauses($clauses, $taxonomies, $args)
    {
        global $wpdb;
        if (!empty($args['product_type_term'])) {
            $product_type_term = $args['product_type_term'];
            $fieldCustom = ", (SELECT COUNT(*) FROM $wpdb->posts AS p
            JOIN $wpdb->term_relationships AS rl ON p.ID = rl.object_id
            JOIN $wpdb->postmeta AS pm ON p.ID = pm.post_id
            WHERE rl.term_taxonomy_id = t.term_id
                  AND p.post_status = 'publish'
                  AND pm.meta_key = 'product_type_term'
                  AND pm.meta_value = '$product_type_term'
            LIMIT 1) AS count_type";

            $clauses['fields'] .=  $fieldCustom;
        }

        return $clauses;
    }

    public function reloadTerm($terms, $taxonomies, $args)
    {
        global $wpdb;
        if (!is_array($terms) && count($terms) < 1) {
            return $terms;
        }

        $ctr = 0;
        $published_terms = array();

        foreach ($terms as $term) {
            if (is_int($term)) {
                $published_terms[] = $term;
                continue;
            }

            if (!isset($term->term_id)) {
                $published_terms[] = $term;
                continue;
            }
            
            $published_terms[$ctr] = $term;
            if (!empty($args['product_type_term'])) {
                $published_terms[$ctr]->count = $term->count_type ?? $term->count;
            }

            if (isset($args['meta_load_field']) && intval($args['meta_load_field']) == 1) {
                $termMeta = get_term_meta($term->term_id);
                if (count($termMeta) > 0) {
                    foreach ($termMeta as $k => $v) {
                        if (is_array($v) && count($v) > 0) {
                            $published_terms[$ctr]->$k = $v[0];
                        }
                    }
                }
            }

            $ctr++;
        }

        return $published_terms;
    }

    public function initPage()
    {
        $config = $this->yamlHelper->load(__DIR__ . '/Config/pages.yml');
        foreach ($config as $key => $value) {
            if (isset($value['Enabled']) && $value['Enabled']) {
                if (isset($value['Categories'])) {
                    foreach ($value['Categories'] as $row) {
                        $args = array(
                            self::CAT_NAME => $row[self::CAT_NAME],
                            self::TAXONOMY => $row[self::TAXONOMY],
                            'category_description' => $row['category_description']
                        );

                        $args['category_parent'] = $this->createTaxonomy($args);

                        if (isset($row[self::CHILDREN]) && is_array($row[self::CHILDREN])) {
                            foreach ($row[self::CHILDREN] as $key => $child) {
                                $args = array_merge($args, $child);
                                $this->createTaxonomy($args);
                            }
                        }
                    }
                }

                if (isset($value['Pages'])) {
                    foreach ($value['Pages'] as $row) {
                        if ($key == 'page' && !file_exists(__DIR__ . "/../../themes/barnettheme/templates/{$row['template']}.php")) {
                            continue;
                        }

                        if ($key == 'page') {
                            $contentPage = '';
                            if (isset($row['content'])) {
                                $contentPage = $row['content'];
                            }
                            $this->barnetPageManager->addNewPage($row['title'], $row['path'], $row['template'], $contentPage);
                        }

                        if ($key == 'barnet-role') {
                            $this->barnetPageManager->addNewRole($row['title'], $row['path']);
                        }
                    }
                }
            }
        }

        if (is_plugin_active('wp-rest-cache/wp-rest-cache.php')) {
            $cacheInMuPlugin = __DIR__ . "/../../mu-plugins/wp-rest-cache.php";
            if (!file_exists(__DIR__ . "/../../mu-plugins")) {
                mkdir(__DIR__ . "/../../mu-plugins");
            }

            if (!file_exists($cacheInMuPlugin)) {
                copy(__DIR__ . "/../wp-rest-cache/sources/wp-rest-cache.php", $cacheInMuPlugin);
            }
        }
    }

    public function initWebLogin($currentlogin, $currentUser)
    {
        if ($currentUser) {
            $token = $this->barnetAuth->generate_token($currentUser);
            $this->setCookie('utk', $token, time() + $this->getLoginExpired(1));
            $this->setCookie('wp-utk', $token, time() + $this->getLoginExpired(1));
        }
    }

    public function initWebLogout()
    {
        if (isset($_COOKIE['jwt_refresh_token'])) {
            $this->setCookie('jwt_refresh_token', 1, 1);
        }

        if (isset($_COOKIE['utk'])) {
            $this->setCookie('utk', 1, 1);
        }

        if (isset($_COOKIE['wp-utk'])) {
            $this->setCookie('wp-utk', 1, 1);
        }

        wp_redirect('/');
        exit;
    }

    public function switchToUser($userId)
    {
        $user = get_userdata($userId);

        if (!$user) {
            return false;
        }

        if (isset($_COOKIE['jwt_refresh_token'])) {
            $this->setCookie('jwt_refresh_token', null, -1);
        }

        if (isset($_COOKIE['utk'])) {
            $this->setCookie('utk', null, -1);
        }

        if (isset($_COOKIE['wp-utk'])) {
            $this->setCookie('wp-utk', null, -1);
        }

        $utk = $this->barnetAuth->generate_token($user);
        $this->setCookie('utk', $utk, time() + $this->getLoginExpired(1));
        $this->setCookie('wp-utk', $utk, time() + $this->getLoginExpired(1));
    }

    public function getLoginExpired($expiredIn)
    {
        $expiredValue = get_option('login_expired_time');
        if (empty($expiredValue)) {
            if (defined('LOGIN_EXPIRED_TIME')) {
                $expiredValue = LOGIN_EXPIRED_TIME;
            } elseif (!defined('LOGIN_EXPIRED_TIME')) {
                $expiredValue = 604800;
            }
        }

        return $expiredValue;
    }

    public function addPostTypeColumnListSort($postType, $fieldNames) {
        if (is_admin()) {
            // make columns sortable
            add_filter("manage_edit-{$postType}_sortable_columns", function ($columns) use ($fieldNames) {
                foreach ($fieldNames as $key => $fieldName) {
                    $columns[$key] = $key;
                }
                return $columns;
            });

            // set query to sort
            add_action('pre_get_posts', function ($query) use ($fieldNames) {
                if (!is_admin()) {
                    return;
                }
                $orderBy = $query->get('orderby');
                foreach ($fieldNames as $key => $fieldValue) {
                    if ($key == $orderBy) {
                        $meta_query = array(
                            'relation' => 'OR',
                            array(
                                'key' => $fieldValue,
                                'compare' => 'NOT EXISTS', // see note above
                            ),
                            array(
                                'key' => $fieldValue,
                            ),
                        );
                        $query->set('meta_query', $meta_query);
                        $query->set('orderby', 'meta_value');
                    }
                }
            });
        }
    }

    public function addPostTypeColumnList(
        $postType,
        $fieldNames,
        $fieldValues,
        $isMeta = true,
        $isRelationship = false,
        $isTaxonomy = false
    ) {
        foreach ($fieldValues as $key => $fieldValue) {
            $this->sortCustomColumns[] = $key;
        }

        if ($isTaxonomy) {
            add_filter("manage_edit-{$postType}_columns", function ($columns) use ($fieldNames) {
                $new_columns = array();
                foreach ($columns as $column_name => $column_value) {
                    $new_columns[$column_name] = $column_value;
                    if ($column_name === 'description') {
                        foreach ($fieldNames as $key => $fieldName) {
                            $new_columns[$key] = $fieldName;
                        }
                    }
                }
                return $new_columns;
            });

            add_filter(
                "manage_{$postType}_custom_column",
                function ($content, $columnName, $termId) use ($fieldValues, $isMeta) {
                    foreach ($fieldValues as $key => $fieldValue) {
                        if ($columnName != $key) {
                            continue;
                        }

                        if ($isMeta) {
                            echo get_term_meta($termId, $fieldValue, true);
                        }
                    }
                },
                10,
                3
            );

            add_action("edited_{$postType}", function ($term_id) use ($fieldValues) {
                foreach ($fieldValues as $key => $fieldValue) {
                    if (isset($_POST[$key])) {
                        if (trim($_POST[$key]) == "") {
                            delete_term_meta($term_id, $key);
                        } else {
                            update_term_meta($term_id, $key, $_POST[$key]);
                        }
                    }
                }
            });

        } else {
            add_filter("manage_{$postType}_posts_columns", function ($columns) use ($fieldNames) {
                $newColumns = array();
                if (isset($columns['date'])) {
                    foreach ($columns as $key => $value) {
                        if ($key == 'date') {
                            foreach ($fieldNames as $key1 => $fieldName) {
                                $newColumns[$key1] = $fieldName;
                            }
                        }
                        $newColumns[$key] = $value;
                    }
                } else {
                    foreach ($fieldNames as $key => $fieldName) {
                        $columns[$key] = $fieldName;
                    }
                    $newColumns = $columns;
                }


                return $newColumns;
            });

            $entity = substr(implode("", array_map(function ($e) {
                    return ucfirst($e);
                }, explode('-', $postType))) . 'Entity',6);

            add_action(
                "manage_{$postType}_posts_custom_column",
                function ($column, $postId) use ($entity, $fieldValues, $isMeta, $isRelationship) {
                    foreach ($fieldValues as $key => $fieldValue) {
                        if ($column != $key) {
                            continue;
                        }

                        if ($isMeta) {
                            echo get_post_meta($postId, $fieldValue, true);
                        } elseif ($isRelationship) {
                            if (class_exists($entity)) {
                                $object = new $entity($postId);
                                $dataRelationship = $object->getRelationship(array($fieldValue));
                                if (isset($dataRelationship[$column])) {
                                    $output = array();
                                    foreach ($dataRelationship[$column] as $objRelationship) {
                                        $output[] = $objRelationship['data']['post_title'];
                                    }

                                    echo implode(", ", $output);
                                }
                            } else {
                                echo "Class $entity not found";
                            }
                        } else {
                            if ($fieldValue == "post_modified") {
                                echo 'Last Modified<br />'.get_post_modified_time('Y/m/d \a\t g:i a', true, $postId, false);
                            } else {
                                echo get_post_field($fieldValue, $postId);
                            }
                        }
                    }
                },
                10,
                2
            );
        }



    }

    public function addSettingField($id, $title, $defaultValue = false, $fieldType = 'text', $group = 'general', $isReadOnly = false)
    {
        add_filter('admin_init', function () use ($id, $title, $fieldType, $group, $defaultValue, $isReadOnly) {
            add_settings_field($id,
                $title,
                function () use ($id, $fieldType, $defaultValue, $isReadOnly) {
                    if ($fieldType == 'textarea') {
                        echo "<textarea " . ($isReadOnly ? "readonly" : "") . " name='$id'>" . get_option($id, $defaultValue) . "</textarea>";
                    } else {
                        echo "<input type='$fieldType' name='$id' value='" . get_option($id, $defaultValue) . "' " . ($isReadOnly ? "readonly" : "") . " />";
                    }
                },
                $group,
                'default',
                array('label_for' => $id));

            register_setting($group, $id, 'esc_attr');
        });
    }

    public function profileUpdate($userId, $old_userData)
    {
        // clear session cache
        $this->barnetClearSearchSessionCacheFunc();

        $old_user_roles = array();
        $old_user_type = array();
        if (isset($_SESSION['users_role'])) {
            $old_user_roles = $_SESSION['users_role'][$userId];
        }
        if (isset($_SESSION['users_type'])) {
            $old_user_type = $_SESSION['users_type'][$userId];
        }
        global $wpdb;
        $user_info = get_userdata( $userId );
        $user = (new UserEntity($userId))->toArray(BarnetEntity::$PUBLIC_ALL);
        $globalText = "global";
        $administrator = "administrator";
        $userType = isset($user['type']) ? $user['type'] : $globalText;
        $arrPC = array();

        $check_role = 0;
        if (!empty($user_info->roles)) {
            if (in_array($administrator, $user_info->roles) && in_array($administrator, $old_user_roles)) {
                return false;
            }
            foreach ($user_info->roles as $role) {
                if (in_array($role, $old_user_roles)) {
                    $check_role++;
                }
            }
            if ($userType == $old_user_type && $check_role == count($user_info->roles)) {
                return false;
            } else {
                if( ! $time ) {
                    $time = strtotime( 'now' );
                }
                $dateTime = new DateTime("@$time");
                $dTime = $dateTime->format("Y-m-d H:i:s");

                $userExtraInfo = array();
                $userExtraInfoGet = get_user_meta($userId, 'user_extra_info', '');
				
                if (!empty($userExtraInfoGet) && is_array($userExtraInfoGet)) {
                    $userExtraInfo = unserialize($userExtraInfoGet[0]);
                    $userExtraInfo['company_name'] = $userExtraInfo['company_name'];
                    $userExtraInfo['address'] = $userExtraInfo['address'];
                    $userExtraInfo['address_optional'] = $userExtraInfo['address_optional'];
                    $userExtraInfo['country'] = $userExtraInfo['country'];
                    $userExtraInfo['city'] = $userExtraInfo['city'];
                    $userExtraInfo['province'] = $userExtraInfo['province'];
                    $userExtraInfo['postal_code'] = $userExtraInfo['postal_code'];
                    $userExtraInfo['phone'] = $userExtraInfo['phone'];
                    $userExtraInfo['phone_optional'] = $userExtraInfo['phone_optional'];
                    $userExtraInfo['job_title_role'] = $userExtraInfo['job_title_role'];
                    $userExtraInfo['about_us'] = $userExtraInfo['about_us'];
                    $userExtraInfo['note'] = $userExtraInfo['note'];
                    //$userExtraInfo['newsletter'] = $userExtraInfo['newsletter'];
                    $userExtraInfo['flag_user_update'] = $dTime;
                }
                update_user_meta($userId, 'user_extra_info', serialize($userExtraInfo));
            }
        } else {
            if (empty($old_user_roles) && $userType == $old_user_type) {
                return false;
            } else {
                if( ! $time ) {
                    $time = strtotime( 'now' );
                }
                $dateTime = new DateTime("@$time");
                $dTime = $dateTime->format("Y-m-d H:i:s");

                $userExtraInfo = array();
                $userExtraInfoGet = get_user_meta($userId, 'user_extra_info', '');
                if (!empty($userExtraInfoGet) && is_array($userExtraInfoGet)) {
                    $userExtraInfo = unserialize($userExtraInfoGet[0]);
                    $userExtraInfo['company_name'] = $userExtraInfo['company_name'];
                    $userExtraInfo['address'] = $userExtraInfo['address'];
                    $userExtraInfo['address_optional'] = $userExtraInfo['address_optional'];
                    $userExtraInfo['country'] = $userExtraInfo['country'];
                    $userExtraInfo['city'] = $userExtraInfo['city'];
                    $userExtraInfo['province'] = $userExtraInfo['province'];
                    $userExtraInfo['postal_code'] = $userExtraInfo['postal_code'];
                    $userExtraInfo['phone'] = $userExtraInfo['phone'];
                    $userExtraInfo['phone_optional'] = $userExtraInfo['phone_optional'];
                    $userExtraInfo['job_title_role'] = $userExtraInfo['job_title_role'];
                    $userExtraInfo['about_us'] = $userExtraInfo['about_us'];
                    $userExtraInfo['note'] = $userExtraInfo['note'];
                    //$userExtraInfo['newsletter'] = $userExtraInfo['newsletter'];
                    $userExtraInfo['flag_user_update'] = $dTime;
                }
                update_user_meta($userId, 'user_extra_info', serialize($userExtraInfo));
            }
        }

        if (is_plugin_active('wp-rest-cache/wp-rest-cache.php')) {
            include_once __DIR__ . "/Common/CacheManager.php";
            $caching = new CacheManager();
            $caching->flushUser($userId);
        }
    }

    protected function createTaxonomy($request)
    {
        $termDefault = get_terms(
            array(
                self::TAXONOMY => $request[self::TAXONOMY],
                'hide_empty' => false
            )
        );

        $isExist = false;
        $taxId = null;

        foreach ($termDefault as $taxonomy) {
            if ($taxonomy->name == $request[self::CAT_NAME]) {
                $taxId = $taxonomy->term_id;
                $isExist = true;
                break;
            }
        }

        if (!$isExist) {
            $taxId = wp_insert_category($request, true);
        }

        return $taxId;
    }

    public function setCookie($name, $value, $expired)
    {
        $secure = ('https' === parse_url(admin_url(), PHP_URL_SCHEME));
        setcookie(
            $name,
            $value,
            $expired,
            COOKIEPATH,
            COOKIE_DOMAIN,
            $secure
        );
    }

    public static function getListCountries()
    {
        $yamlHelper = new YamlHelper();
        $config = $yamlHelper->load(__DIR__ . '/Config/countries.yml');
        if (isset($config[self::COUNTRIES]) && is_array($config[self::COUNTRIES])) {
            return $config[self::COUNTRIES];
        }
        return array();
    }

    public function ajaxGetInteractiveImage()
    {
        $id = empty($_POST["id"]) ? 0 : intval($_POST["id"]);
        $mess = "";
        $status = true;
        if ($id > 0) {
            $metaImage = get_post_meta($id, 'concept_interactive_image');
            if (is_array($metaImage) && count($metaImage) > 0) {
                $mess = wp_get_attachment_url($metaImage[0]);
                $mess = $mess ? $mess : $this->getDefaultImage();
            } else {
                $status = false;
                $mess = "Not find image inteactive";
            }
        } else {
            $status = false;
            $mess = "No data has been submitted";
        }

        if ($status) {
            wp_send_json_success($mess);
        } else {
            wp_send_json_error($mess);
        }


    }

    public function unzipUploadFile($array, $var)
    {
        if ($array['type'] == "application/zip") {
            $barnetFileManager = new BarnetFileManager();
            $barnetFileManager->unzip($array['file'], __DIR__ . "/../../uploads/ppt");
            unlink($array['file']);
        }

        return $array;
    }

    public function changeLabelSliderInteractiveLink($label, $field, $object)
    {
        return $object->slug;
    }

    protected function addMenuAppAdmin()
    {
        add_action('admin_menu', function () {
            add_menu_page(
                'App Admin',
                'App Admin',
                'read',
                self::BARNET_MENU_APP_ADMIN,
                '',
                'dashicons-admin-post',
                29
            );
        });
    }

    public function showMultiUserRoleSelected()
    {
        add_action('admin_menu', function () {
            add_submenu_page(
                'users.php',
                'Apply Role Users',
                'Apply Role Users',
                'manage_options',
                'apply-role-users',
                function () {
                    include_once(__DIR__ . '/../../themes/barnettheme/user/apply_role_users.php');
                }
            );
        });

        add_action('init', function () {
            if (isset($_POST[self::WPNONCE]) && current_user_can('manage_options') && wp_verify_nonce($_POST[self::WPNONCE], 'apply_role_users')) {
                if (isset($_POST['sel_user']) && isset($_POST['sel_role'])) {
                    $users = $_POST['sel_user'];
                    $roles = $_POST['sel_role'];

                    foreach ($users as $userId) {
                        $user = get_userdata($userId);
                        foreach ($roles as $role) {
                            if (in_array($role, (array)$user->roles)) {
                                continue;
                            }

                            $user->add_role($role);
                        }
                    }
                }
            }
        });
    }

    public function showSearchSetting()
    {
        add_action('admin_menu', function () {
            add_submenu_page(
                'options-general.php',
                'Search Setting',
                'Search',
                'manage_options',
                'barnet-search-setting',
                function () {
                    include_once(__DIR__ . '/../../themes/barnettheme/setting/search.php');
                }
            );
        });

        add_action('init', function () {
            if (isset($_POST[self::WPNONCE]) && current_user_can('manage_options') && wp_verify_nonce($_POST[self::WPNONCE], 'barnet_search_setting')) {
                if (isset($_POST[self::SUBMIT])) {
                    foreach ($_POST as $key => $value) {
                        if (in_array($key, array(self::WPNONCE, '_wp_http_referer', self::SUBMIT, self::REVERT))) {
                            continue;
                        }

                        if (is_array($value)) {
                            update_option($key, serialize($value));
                        } else {
                            update_option($key, sanitize_text_field($value));
                        }
                    }

                    if (isset($_POST[self::BARNET_OPT_SA_PP_PP_ACTIVE])) {
                        update_option(self::BARNET_OPT_SA_PP_PP_ACTIVE, 1);
                    } else {
                        update_option(self::BARNET_OPT_SA_PP_PP_ACTIVE, 0);
                    }

                    if (isset($_POST[self::BARNET_OPT_SA_SA_UP_ACTIVE])) {
                        update_option(self::BARNET_OPT_SA_SA_UP_ACTIVE, 1);
                    } else {
                        update_option(self::BARNET_OPT_SA_SA_UP_ACTIVE, 0);
                    }

                    if (isset($_POST[self::BARNET_OPT_SE_MD_ACTIVE])) {
                        update_option(self::BARNET_OPT_SE_MD_ACTIVE, 1);
                    } else {
                        update_option(self::BARNET_OPT_SE_MD_ACTIVE, 0);
                    }
                } elseif (isset($_POST[self::REVERT])) {
                    foreach ($_POST as $key => $value) {
                        if (in_array($key, array(self::WPNONCE, '_wp_http_referer', self::SUBMIT, self::REVERT))) {
                            continue;
                        }

                        $prefix = "barnet_opt";
                        foreach ($this->searchConfig['setting'] as $_key => $config) {
                            $fieldName = $prefix . "_ss_" . DataHelper::compactString($_key);
                            foreach ($config as $field => $value) {
                                if ($field == 'include') {
                                    foreach ($value as $relationshipName) {
                                        $fieldName .= "_" . DataHelper::compactString($relationshipName, '_');
                                        $dataRelationship = $this->searchConfig[self::RELATIONSHIP][$_key][$relationshipName];
                                        foreach ($dataRelationship as $pType => $pRelationship) {
                                            if ($pType == self::ADVANCE_TEXT) {
                                                continue;
                                            }

                                            if (!isset($pRelationship[self::RELATION_KEY_TEXT])) {
                                                continue;
                                            }

                                            $fieldName .= '_' . DataHelper::compactString($pType);
                                            foreach ($pRelationship as $__key => $value) {
                                                if ($__key == self::RELATION_KEY_TEXT) {
                                                    continue;
                                                }

                                                if ($__key == self::TAXONOMY) {
                                                    $fieldName .= '_tax';
                                                    foreach ($value as $taxKey => $taxValue) {
                                                        $fieldName .= '_' . DataHelper::compactString($taxKey);
                                                        foreach ($taxValue as $field => $val) {
                                                            update_option($fieldName . "_" . $field, $val);
                                                        }
                                                    }
                                                } else {
                                                    update_option($fieldName . '_' . DataHelper::compactString($__key, '_'), $value);
                                                }
                                            }
                                        }
                                    }
                                } else {
                                    update_option($fieldName . "_" . $field, $value);
                                }
                            }
                        }

                        update_option("{$prefix}_sa_pp_option", serialize($this->searchConfig[self::ADVANCE_TEXT]['percent_point']));
                        update_option("{$prefix}_sa_up_option", serialize($this->searchConfig[self::ADVANCE_TEXT]['unique_point']));
                        update_option("{$prefix}_se_up_point", $this->searchConfig[self::EXTRA_TEXT][self::MODIFIED_DATE]['point']);
                        update_option("{$prefix}_se_up_value", $this->searchConfig[self::EXTRA_TEXT][self::MODIFIED_DATE]['value']);
                        update_option(self::BARNET_OPT_SA_PP_PP_ACTIVE, 1);
                        update_option(self::BARNET_OPT_SA_SA_UP_ACTIVE, 1);
                        update_option(self::BARNET_OPT_SE_MD_ACTIVE, 1);
                    }
                }
            }
        });
    }

    public function addDeletedTable()
    {
        add_action('init', function () {
            global $wpdb;
            $tableName = $wpdb->prefix . "barnet_deleted";
            global $charset_collate;
            $charset_collate = $wpdb->get_charset_collate();

            if ($wpdb->get_var("SHOW TABLES LIKE '" . $tableName . "'") != $tableName) {
                $createSql = "CREATE TABLE $tableName
                    (
                        post_id BIGINT(20) NOT NULL,
                        post_type VARCHAR(20) NOT NULL,
                        timestamp VARCHAR(20) NOT NULL
                    )$charset_collate;";
            }

            if (isset($createSql)) {
                require_once(ABSPATH . "wp-admin/includes/upgrade.php");
                dbDelta($createSql);
            }
            //register the new table with the wpdb object
            if (!isset($wpdb->barnet_deleted)) {
                $wpdb->barnet_deleted = $tableName;
                //add the shortcut so you can use $wpdb->stats
                $wpdb->tables[] = str_replace($wpdb->prefix, '', $tableName);
            }
        });
    }

    public function addUpdateTaxTable()
    {
        add_action('init', function () {
            global $wpdb;
            $tableName = $wpdb->prefix . "barnet_tax_update";
            global $charset_collate;
            $charset_collate = $wpdb->get_charset_collate();

            if ($wpdb->get_var("SHOW TABLES LIKE '" . $tableName . "'") != $tableName) {
                $createSql = "CREATE TABLE $tableName
                    (
                        tax_id BIGINT(20) NOT NULL,
                        tax_type VARCHAR(20) NOT NULL,
                        timestamp VARCHAR(20) NOT NULL,
                        PRIMARY KEY (tax_id, tax_type)
                    )$charset_collate;";
            }

            if (isset($createSql)) {
                require_once(ABSPATH . "wp-admin/includes/upgrade.php");
                dbDelta($createSql);
            }
            //register the new table with the wpdb object
            if (!isset($wpdb->barnet_tax_update)) {
                $wpdb->barnet_tax_update = $tableName;
                //add the shortcut so you can use $wpdb->stats
                $wpdb->tables[] = str_replace($wpdb->prefix, '', $tableName);
            }
        });
    }

    public function getDefaultImage($type = 'all')
    {
        if ($type == "concept_thumb") {
            return get_template_directory_uri() . "/assets/images/concept-thumb-default.jpg";
        } else if ($type == "concept_header") {
            return get_template_directory_uri() . "/assets/images/concept-header-default.jpg";
        } else if ($type == "formula_header") {
            return get_template_directory_uri() . "/assets/images/formula-header-default.jpg";
        }
        return get_template_directory_uri() . "/assets/images/default.png";
    }

    public function barnetClearSearchSessionCacheHook($postId)
    {
        if (wp_is_post_revision($postId)) {
            return;
        }


        if (array_key_exists('product_type_term', $_POST)) {
            
             //wp_set_post_terms($postId, $_POST['product_type'], 'product-type');
             //$product_type_term_ = get_term_by('id',$_POST['product_type_term'] , 'product-type');
             update_post_meta( $postId, 'product_type_term', $_POST['product_type_term'] );

             
        }

        if (array_key_exists('concept_type_term', $_POST)) {
            
            //wp_set_post_terms($postId, $_POST['product_type'], 'product-type');
            //$product_type_term_ = get_term_by('id',$_POST['product_type_term'] , 'product-type');
            update_post_meta( $postId, 'concept_type_term', $_POST['concept_type_term'] );

            
       }

        if (array_key_exists('page-product-type', $_POST)) {
            
            update_post_meta( $postId, 'page-product-type', $_POST['page-product-type'] );
       }
        $this->barnetClearSearchSessionCacheFunc();
    }

    public function barnetClearSearchSessionCacheFunc()
    {
        if (is_plugin_active('barnet-products/index.php')) {
            $sessionCacheManager = new BarnetSessionCacheManager();
            $sessionCacheManager->clearAllSessionData();
        }
    }

}

global $barnet;
$barnet = new Barnet();
$barnet->addDataType($barnetProduct)
    ->addDataType($barnetProductConcept)
    ->addDataType($barnetProductAttribute)
    ->addDataType($barnetDigitalCode)
    ->addDataType($barnetFormula)
    ->addDataType($BarnetFormulaAttribute)
    ->addDataType($barnetConcept)
    ->addDataType($barnetConceptBook)
    ->addDataType($barnetInteractiveDesign)
    ->addDataType($barnetCustomer)
    ->addDataType($barnetRole)
    ->addDataType($barnetResource)
    ->addDataType($barnetPage)
    ->addDataType($barnetLabTraining)
    ->addDataType($barnetAnnoucement)
    ->addDataType($barnetSampleRequest)
    ->addDataType($BarnetLabRequest)
    ->addDataType($barnetContactLoad);
$barnet->init();
global $barnetUserLoggedIn;
$barnetUserLoggedIn = false;