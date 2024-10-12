<?php

class BarnetFormula extends BarnetDataType
{
    const FORMULA_CATEGORY = "formula-category";
    const IMAGE_ADVANCED = "image_advanced";
    const MAX_FILE_UPLOADS = "max_file_uploads";
    const MEDIA_BUTTON = "media_buttons";
    const BARNET_PRODUCT = "barnet-product";
    const DESCRIPTION = "Description";
    const CLONE = "clone";
    const FIELD = "fields";
    const WYSIWYG = "wysiwyg";
    const OPTION =  "options";
    const GROUP = "group";
    const POST_TYPE = "post_type";
    const CHECKBOX = "checkbox";
    const POSTTYPE = "post_type";
    const BARNETRESOURCE = "barnet-resource";
    const FIELDTYPE = "field_type";
    const SELECTADVANCED = "select_advanced";
    const QUERYARGS = "query_args";
    const POSTPERPAGE = "posts_per_page";
    const TAXQUERY = "tax_query";
    const RESOURCE_FOLDER = "resource-folder";
    const TAXONOMY = "taxonomy";
    const TERMS = "terms";



    public function createPostType()
    {
        register_post_type(
            $this->postType,
            $this->buildArgs(
                'Formulas',
                'Barnet Formulas',
                array(self::FORMULA_CATEGORY)
            )
        );
    }

    public function addExt()
    {
        return array(
            'title' => esc_html__('Formula Type', $this->domain),
            'id' => $this->postType,
            'post_types' => array($this->postType),
            'context' => 'normal',
            'priority' => 'high',
            self::CLONE => true,
            self::FIELD => array(
                array(
                    'type' => 'text',
                    'name' => esc_html__('Code', $this->domain),
                    'id' => $this->prefix . 'code',
                ),
                array(
                    'type' => 'text',
                    'name' => esc_html__('Code (ERP)', $this->domain),
                    'id' => $this->prefix . 'code_erp',
                ),
                array(
                    'type' => 'datetime',
                    'name' => esc_html__('Date of creation', $this->domain),
                    'id' => 'date_created',
                ),

                array(
                    'type' => 'datetime',
                    'name' => esc_html__('Date since last update', $this->domain),
                    'id' => 'date_updated',
                ),
                array(
                    'type' => self::IMAGE_ADVANCED,
                    self::MAX_FILE_UPLOADS => 1,
                    'max_status' => 'false',
                    'name' => esc_html__('Header Image', $this->domain),
                    'id' => $this->prefix . 'image',
                ),
                array(
                    'type' => self::WYSIWYG,
                    'name' => esc_html__(self::DESCRIPTION, $this->domain),
                    'id' => $this->prefix . self::DESCRIPTION,
                    self::OPTION => array(
                        self::MEDIA_BUTTON => false,
                    ),
                ),
                array(
                    'type' => 'radio',
                    'name' => esc_html__('Region Type', $this->domain),
                    'id' => $this->prefix . 'area',
                    BarnetProduct::OPTION => BarnetProduct::$AREA_LIST,
                    'std' => 'global'
                ),
                /*array(
                    'type' => 'file_advanced',
                    'name' => esc_html__('List of Files', $this->domain),
                    'id' => $this->prefix . 'files',
                ),*/
                array(
                    'type' => 'text',
                    'name' => esc_html__('The Formula\'s Sheet Title', $this->domain),
                    'id' => $this->prefix . 'sheet_doc_label'
                ),
                array(
                    'type' => 'post',
                    'name' => esc_html__('The Formula\'s Sheet', $this->domain),
                    'id' => $this->prefix . 'sheet_doc',
                    'desc' => esc_html__('The Formula\'s Sheet document, attached as a PDF', $this->domain),
                    self::POSTTYPE => self::BARNETRESOURCE,
                    self::FIELDTYPE => self::SELECTADVANCED,
                    'ajax' => true,
                    self::QUERYARGS => array(
                        self::POSTPERPAGE => -1,
                        self::POSTTYPE => self::BARNETRESOURCE,
                        self::TAXQUERY => array(
                            array(
                                self::TAXONOMY => self::RESOURCE_FOLDER,
                                'field' => 'slug',
                                self::TERMS => 'sheet',
                            )
                        )
                    )
                ),
                array(
                    'type' => 'text',
                    'name' => esc_html__('The Formula\'s Card Title', $this->domain),
                    'id' => $this->prefix . 'card_doc_label'
                ),
                array(
                    'type' => 'post',
                    'name' => esc_html__('The Formula\'s Card', $this->domain),
                    'id' => $this->prefix . 'card_doc',
                    'desc' => esc_html__('The Formula\'s Card document, attached as a PDF', $this->domain),
                    self::POSTTYPE => self::BARNETRESOURCE,
                    self::FIELDTYPE => self::SELECTADVANCED,
                    'ajax' => true,
                    self::QUERYARGS => array(
                        self::POSTPERPAGE => -1,
                        self::POSTTYPE => self::BARNETRESOURCE,
                        self::TAXQUERY => array(
                            array(
                                self::TAXONOMY => self::RESOURCE_FOLDER,
                                'field' => 'slug',
                                self::TERMS => 'card',
                            )
                        )
                    )
                ),
                array(
                    'type' => self::GROUP,
                    'name' => esc_html__('List of Ingredients', $this->domain),
                    'id' => $this->prefix . 'ingredients',
                    self::CLONE => true,
                    'sort_clone' => true,
                    self::FIELD => array(
                        array(
                            'name' => esc_html__('Link To', $this->domain),
                            'id' => 'f_linkto',
                            //'type' => 'text',
                            'type' => 'post',
                            self::POST_TYPE => self::BARNET_PRODUCT,
                            'field_type' => 'select_advanced',
                            'ajax' => true,
                            'query_args' => array(
                                'post_status' => 'publish'
                            ),
                            'js_options' => array('data' => $this->getProductStringData()),
                        ),
                        array(
                            'name' => esc_html__('Phase', $this->domain),
                            'id' => 'phase',
                            'type' => 'text',
                        ),
                        array(
                            'name' => esc_html__('Material Name', $this->domain),
                            'id' => 'f_material',
                            'type' => 'text',
                        ),
                        array(
                            'name' => esc_html__('INCI', $this->domain),
                            'id' => 'f_inci',
                            'type' => 'text',
                        ),
                        array(
                            'name' => esc_html__('Supplier', $this->domain),
                            'id' => 'f_supplier',
                            'type' => 'text',
                        ),
                        array(
                            'name' => esc_html__('Properties', $this->domain),
                            'id' => 'f_properties',
                            'type' => 'text',
                        ),
                        array(
                            'name' => esc_html__('%', $this->domain),
                            'id' => 'f_percent',
                            'type' => 'number',
                            'step' => 'any'
                        ),
                    ),
                ),
                array(
                    'type' => self::GROUP,
                    'name' => esc_html__('Specifications', $this->domain),
                    'id' => $this->prefix . 'specifications',
                    self::CLONE => true,
                    self::FIELD => array(
                        array(
                            'name' => esc_html__('Label', $this->domain),
                            'id' => 's_label',
                            'type' => 'text',
                        ),
                        array(
                            'name' => esc_html__('Specification', $this->domain),
                            'id' => 's_specification',
                            'type' => 'text',
                        ),
                    ),
                ),
                array(
                    'type' => self::GROUP,
                    'name' => esc_html__('Processing Steps', $this->domain),
                    'id' => 'process_steps',
                    self::CLONE => true,
                    self::FIELD => array(
                        array(
                            'name' => esc_html__('Step', $this->domain),
                            'id' => 'p_step',
                            'type' => 'text',
                        ),
                        array(
                            'name' => esc_html__(self::DESCRIPTION, $this->domain),
                            'id' => 'p_description',
                            'type' => 'text',
                        ),
                    ),
                ),
                array(
                    'type' => self::WYSIWYG,
                    'name' => esc_html__('About The Base', $this->domain),
                    'id' => $this->prefix . 'base',
                    self::OPTION => array(
                        self::MEDIA_BUTTON => false,
                    ),
                ),
                array(
                    'type' => 'post',
                    'name' => esc_html__('Video', $this->domain),
                    'id' => $this->prefix . 'video_resource',
                    'desc' => esc_html__('Video, attached as a VIDEO', $this->domain),
                    self::POSTTYPE => self::BARNETRESOURCE,
                    self::FIELDTYPE => self::SELECTADVANCED,
                    'ajax' => true,
                    self::QUERYARGS => array(
                        self::POSTPERPAGE => -1,
                        self::POSTTYPE => self::BARNETRESOURCE,
                        self::TAXQUERY => array(
                            array(
                                self::TAXONOMY => self::RESOURCE_FOLDER,
                                'field' => 'slug',
                                self::TERMS => 'videos',
                            )
                        )
                    )
                ),
                /*array(
                    'type' => 'video',
                    'name' => esc_html__('Video', $this->domain),
                    'id' => $this->prefix . 'video',
                    self::MAX_FILE_UPLOADS => 1,
                ),*/
                array(
                    'type' => self::CHECKBOX,
                    'name' => esc_html__('Feature Formula Flag', $this->domain),
                    'id' => $this->prefix . 'featured',
                ),
                array(
                    'type' => 'text',
                    'name' => esc_html__('Keywords', $this->domain),
                    'id' => $this->prefix . 'keyword'
                ),
                array(
                    'type' => 'text',
                    'name' => esc_html__('Custom Keywords', $this->domain),
                    'id' => $this->prefix . 'keyword_custom'
                ),
                array(
                    'type' => self::GROUP,
                    'name' => esc_html__('Key Features', $this->domain),
                    'id' => $this->prefix . 'key_feature',
                    self::FIELD => array(
                      array(
                        'name' => esc_html__('List Key Features', $this->domain),
                        'id' => 'kf_info',
                        'type' => 'text',
                        self::CLONE => true
                      ),
                    ),
                ),
                array(
                    'type' => self::GROUP,
                    'name' => esc_html__('How To Use', $this->domain),
                    'id' => $this->prefix . 'how_use',
                    self::FIELD => array(
                      array(
                        'name' => esc_html__('List How To Use', $this->domain),
                        'id' => 'htu_info',
                        'type' => 'text',
                        self::CLONE => true
                      ),
                    ),
                ),
                array(
                    'type' => self::GROUP,
                    'name' => esc_html__('Flexible Formula', $this->domain),
                    'id' => $this->prefix . 'flexible',
                    self::FIELD => array(
                        array(
                          'type' => self::CHECKBOX,
                          'name' => esc_html__('Is Display', $this->domain),
                          'id' => $this->prefix . 'show_flexible',
                        ),
                        array(
                            'type' => self::WYSIWYG,
                            'name' => esc_html__('Description Flexible', $this->domain),
                            'id' => $this->prefix . 'description_flexible',
                            'raw' => true,
                            'options' => array(
                                'media_buttons' => false,
                          ),
                        ),
                    ),
                ),
                array(
                    'type' => self::GROUP,
                    'name' => esc_html__('Key Ingredients', $this->domain),
                    'id' => $this->prefix . 'key_ingredients',
                    self::CLONE => true,
                    self::FIELD => array(
                        array(
                            'name' => esc_html__('Title', $this->domain),
                            'id' => 'ki_title',
                            'type' => 'text',
                        ),
                        array(
                            'name' => esc_html__(self::DESCRIPTION, $this->domain),
                            'id' => 'ki_description',
                            'type' => self::WYSIWYG,
                        ),
                        array(
                            'type' => 'post',
                            'name' => esc_html__('Product CPT', $this->domain),
                            'id' => 'ki_product_cpt',
                            self::POST_TYPE => self::BARNET_PRODUCT,
                            'field_type' => 'select_advanced',
                            'ajax' => true,
                            'query_args' => array(
                                'posts_per_page' => -1,
                                self::POST_TYPE => self::BARNET_PRODUCT
                            )
                        ),
                    ),
                ),
            ),
        );
    }

    public function createTaxonomy()
    {
        register_taxonomy(self::FORMULA_CATEGORY, $this->menuSlugTaxonomy, array(
            'hierarchical' => true,
            'labels' => array(
                'name' => _x('Formulas Category', 'Formulas category'),
                'singular_name' => _x('Formula category', 'Formula category'),
                'search_items' => __('Search Formula category'),
                'all_items' => __('All Formulas category'),
                'parent_item' => __('Parent Formulas category'),
                'parent_item_colon' => __('Parent Formula category:'),
                'edit_item' => __('Edit Formula category'),
                'update_item' => __('Update Formula category'),
                'add_new_item' => __('Add New Formula category'),
                'new_item_name' => __('New Formula category Name'),
                'menu_name' => __('Formula Category'),
            ),
            'show_ui' => true,
            'show_in_rest' => true,
            'show_admin_column' => true,
            'query_var' => true,
            'rewrite' => array('slug' => self::FORMULA_CATEGORY),
        ));
    }

    public function addCustomFieldTaxonomy()
    {
        return array(
            'taxonomies' => self::FORMULA_CATEGORY,
            'title' => 'Standard Fields Formula Category',
            self::FIELD => array(
                array(
                    'name' => esc_html__('Order', $this->domain),
                    'id' => 'order',
                    'type' => 'number',
                    'min'  => 0,
                ),
                array(
                    'name' => esc_html__('Image', $this->domain),
                    'id' => 'image',
                    'type' => self::IMAGE_ADVANCED,
                    self::MAX_FILE_UPLOADS => 1,
                ),
                array(
                    'name' => esc_html__('Image Dark Mode', $this->domain),
                    'id' => 'image_black',
                    'type' => self::IMAGE_ADVANCED,
                    self::MAX_FILE_UPLOADS => 1,
                ),
            ),
        );
    }

    public function addRelationship()
    {
        $this->addDataRelationship($this->postType, 'barnet-fattribute', null, true, 'Key Attributes');
        $this->addDataRelationship($this->postType, 'barnet-role', null, false, 'Formula Roles');
        $this->addDataRelationship($this->postType, $this->postType, null, true, 'Related Formula');
    }
}

$barnetFormula = new BarnetFormula("formula_", 'barnet-formula');
