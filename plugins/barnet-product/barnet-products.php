<?php

class BarnetProduct extends BarnetDataType
{
    const PRODUCTCATEGORY = "product-category";
	const PRODUCTTYPE = "product-type";
    const FIELDS = "fields";
    const CLONE = "clone";
    const OPTION = "options";
    const GROUP = "group";
    const WYSIWYG = "WYSIWYG";
    const MEDIABUTTONS = "media_buttons";
    const POSTTYPE = "post_type";
    const BARNETRESOURCE = "barnet-resource";
    const FIELDTYPE = "field_type";
    const SELECTADVANCED = "select_advanced";
    const QUERYARGS = "query_args";
    const POSTPERPAGE = "posts_per_page";
    const TAXQUERY = "tax_query";
    const RESOURCE_FOLDER = "resource-folder";
    const TAXONOMY = "taxonomy";
    const FIELD = "field";
    const TERMS = "terms";
    public static $IS_REMOVE_ROLE = false;
    public static $AREA_LIST = array(
        'global' => 'Global',
        'international_only' => 'International Only',
        'domestic_only' => 'Domestic Only'
    );

    public function createPostType()
    {
        register_post_type(
            $this->postType,
            $this->buildArgs(
                'Products',
                'Barnet Products',
                array(self::PRODUCTCATEGORY,self::PRODUCTTYPE)
            )
        );
    }

    public function addExt()
    {
        return array(
            'title' => esc_html__('Product Type', $this->domain),
            'id' => $this->postType,
            'post_types' => array($this->postType),
            'context' => 'normal',
            'priority' => 'high',
            self::CLONE => true,
            self::FIELDS => array(
                /*
                array(
                    'type' => 'select',
                    'name' => esc_html__('Product Type', $this->domain),
                    'id' => 'product_type',
                    self::OPTION =>array(
                        'Active' => esc_html__('Active', $this->domain),
                        'System' => esc_html__('System', $this->domain),
						'Pigments' => esc_html__('Pigments', $this->domain),
                    ),
					
                ),
                */
                array(
                    'name' => esc_html__('Barnet Product ID', $this->domain),
                    'id' => $this->prefix . 'id',
                    'type' => 'text',
                ),
                array(
                    'type' => 'textarea',
                    'name' => esc_html__('INCI Name', $this->domain),
                    'id' => 'inci_name',
                    'desc' => esc_html__('INCI Name', $this->domain),
                ),
                array(
                    'type' => 'checkbox',
                    'name' => esc_html__('Display INCI logged out', $this->domain),
                    'id' => $this->prefix . 'display_inci',
                ),
                array(
                    'name' => esc_html__('Header Image', $this->domain),
                    'id' => $this->prefix . 'header_image',
                    'type' => 'image_advanced',
                    'max_file_uploads' => 1,
                    'max_status' => 'false',
                ),
                /*array(
                    'type' => 'file_advanced',
                    'name' => esc_html__('Video', $this->domain),
                    'id' => $this->prefix . 'video',
                ),*/
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
                                self::FIELD => 'slug',
                                self::TERMS => 'videos',
                            )
                        )
                    )
                ),
                array(
                    'type' => 'textarea',
                    'name' => esc_html__('Product Description Logged Out', $this->domain),
                    'id' => $this->prefix . 'description',
                    'desc' => esc_html__('Product Landing Page Description', $this->domain),
                    self::CLONE => false,
                    self::OPTION => array(
                        self::MEDIABUTTONS => false,
                    ),
                ),
                array(
                    'type' => self::WYSIWYG,
                    'name' => esc_html__('Product Description Logged In', $this->domain),
                    'id' => $this->prefix . 'description_logged',
                    'desc' => esc_html__('Product Landing Page Description', $this->domain),
                    self::CLONE => true,
                    self::OPTION => array(
                        self::MEDIABUTTONS => false,
                    ),
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
                    'type' => 'text',
                    'name' => esc_html__('The product\'s SDS Title', $this->domain),
                    'id' => $this->prefix . 'msds_doc_label'
                ),
                array(
                    'type' => 'post',
                    'name' => esc_html__('The product\'s SDS', $this->domain),
                    'id' => $this->prefix . 'msds_doc',
                    'desc' => esc_html__('The product\'s SDS document, attached as a PDF', $this->domain),
                    self::POSTTYPE => self::BARNETRESOURCE,
                    self::FIELDTYPE => self::SELECTADVANCED,
                    'ajax' => true,
                    self::QUERYARGS => array(
                        self::POSTPERPAGE => -1,
                        self::POSTTYPE => self::BARNETRESOURCE,
                        self::TAXQUERY => array(
                            array(
                                self::TAXONOMY => self::RESOURCE_FOLDER,
                                self::FIELD => 'slug',
                                self::TERMS => 'sds',
                            )
                        )
                    )
                ),
                array(
                    'type' => 'text',
                    'name' => esc_html__('The product\'s SPEC Title', $this->domain),
                    'id' => $this->prefix . 'spec_doc_label'
                ),
                array(
                    'type' => 'post',
                    'name' => esc_html__('The product\'s SPEC', $this->domain),
                    'id' => $this->prefix . 'spec_doc',
                    'desc' => esc_html__('The product\'s SPEC sheet document, attached as a PDF', $this->domain),
                    self::POSTTYPE => self::BARNETRESOURCE,
                    self::FIELDTYPE => self::SELECTADVANCED,
                    'ajax' => true,
                    self::QUERYARGS => array(
                        self::POSTPERPAGE => -1,
                        self::POSTTYPE => self::BARNETRESOURCE,
                        self::TAXQUERY => array(
                            array(
                                self::TAXONOMY => self::RESOURCE_FOLDER,
                                self::FIELD => 'slug',
                                self::TERMS => 'spec',
                            )
                        )
                    )
                ),
                array(
                    'type' => 'text',
                    'name' => esc_html__('The product\'s KISS Title', $this->domain),
                    'id' => $this->prefix . 'kiss_doc_label'
                ),
                array(
                    'type' => 'post',
                    'name' => esc_html__('The product\'s KISS', $this->domain),
                    'id' => $this->prefix . 'kiss_doc',
                    'desc' => esc_html__('The product\'s KISS document, attached as a PDF', $this->domain),
                    self::POSTTYPE => self::BARNETRESOURCE,
                    self::FIELDTYPE => self::SELECTADVANCED,
                    'ajax' => true,
                    self::QUERYARGS => array(
                        self::POSTPERPAGE => -1,
                        self::POSTTYPE => self::BARNETRESOURCE,
                        self::TAXQUERY => array(
                            array(
                                self::TAXONOMY => self::RESOURCE_FOLDER,
                                self::FIELD => 'slug',
                                self::TERMS => 'kiss',
                            )
                        )
                    )
                ),
                array(
                    'type' => 'text',
                    'name' => esc_html__('The product\'s Dossier Title', $this->domain),
                    'id' => $this->prefix . 'dossier_doc_label'
                ),
                array(
                    'type' => 'post',
                    'name' => esc_html__('The product\'s Dossier', $this->domain),
                    'id' => $this->prefix . 'dossier_doc',
                    'desc' => esc_html__('The product\'s Dossier document, attached as a PDF', $this->domain),
                    self::POSTTYPE => self::BARNETRESOURCE,
                    self::FIELDTYPE => self::SELECTADVANCED,
                    'ajax' => true,
                    self::QUERYARGS => array(
                        self::POSTPERPAGE => -1,
                        self::POSTTYPE => self::BARNETRESOURCE,
                        self::TAXQUERY => array(
                            array(
                                self::TAXONOMY => self::RESOURCE_FOLDER,
                                self::FIELD => 'slug',
                                self::TERMS => 'dossier',
                            )
                        )
                    )
                ),
                array(
                    'type' => 'text',
                    'name' => esc_html__('The product\'s Presentation Title', $this->domain),
                    'id' => $this->prefix . 'presentation_doc_label'
                ),
                array(
                    'type' => 'post',
                    'name' => esc_html__('The product\'s Presentation', $this->domain),
                    'id' => $this->prefix . 'presentation_doc',
                    'desc' => esc_html__('The product\'s Presentation document, attached as a PDF', $this->domain),
                    self::POSTTYPE => self::BARNETRESOURCE,
                    self::FIELDTYPE => self::SELECTADVANCED,
                    'ajax' => true,
                    self::QUERYARGS => array(
                        self::POSTPERPAGE => -1,
                        self::POSTTYPE => self::BARNETRESOURCE,
                        self::TAXQUERY => array(
                            array(
                                self::TAXONOMY => self::RESOURCE_FOLDER,
                                self::FIELD => 'slug',
                                self::TERMS => 'presentation',
                            )
                        )
                    )
                ),
                array(
                    'type' => 'text',
                    'name' => esc_html__('The product\'s Formula Title', $this->domain),
                    'id' => $this->prefix . 'formula_doc_label'
                ),
                array(
                    'type' => 'post',
                    'name' => esc_html__('The product\'s Formula', $this->domain),
                    'id' => $this->prefix . 'formula_doc',
                    'desc' => esc_html__('The product\'s Formula document, attached as a PDF', $this->domain),
                    self::POSTTYPE => self::BARNETRESOURCE,
                    self::FIELDTYPE => self::SELECTADVANCED,
                    'ajax' => true,
                    self::QUERYARGS => array(
                        self::POSTPERPAGE => -1,
                        self::POSTTYPE => self::BARNETRESOURCE,
                        self::TAXQUERY => array(
                            array(
                                self::TAXONOMY => self::RESOURCE_FOLDER,
                                self::FIELD => 'slug',
                                self::TERMS => 'formula',
                            )
                        )
                    )
                ),
                array(
                    'type' => 'text',
                    'name' => esc_html__('The product\'s Sustainable Snapshots Title', $this->domain),
                    'id' => $this->prefix . 'snapshots_doc_label'
                ),
                array(
                    'type' => 'post',
                    'name' => esc_html__('The product\'s Sustainable Snapshots', $this->domain),
                    'id' => $this->prefix . 'snapshots_doc',
                    'desc' => esc_html__('The product\'s Sustainable Snapshots document', $this->domain),
                    self::POSTTYPE => self::BARNETRESOURCE,
                    self::FIELDTYPE => self::SELECTADVANCED,
                    'ajax' => true,
                    self::QUERYARGS => array(
                        self::POSTPERPAGE => -1,
                        self::POSTTYPE => self::BARNETRESOURCE,
                        self::TAXQUERY => array(
                            array(
                                self::TAXONOMY => self::RESOURCE_FOLDER,
                                self::FIELD => 'slug',
                                self::TERMS => 'sustainable-snapshots',
                            )
                        )
                    )
                ),
                array(
                    'type' => 'group',
                    'name' => esc_html__('Other Product\'s Documents', $this->domain),
                    'id' => $this->prefix . 'other_docs',
                    self::CLONE => true,
                    self::FIELDS => array(
                        array(
                            'name' => esc_html__('Label', $this->domain),
                            'id' => 'label',
                            'type' => 'text',
                        ),
                        array(
                            'type' => 'post',
                            'name' => esc_html__('File', $this->domain),
                            'id' => 'doc',
                            'desc' => esc_html__('Other Product\'s Documents, attached as a PDF', $this->domain),
                            'post_type' => 'barnet-resource',
                            'field_type' => 'select_advanced',
                            'ajax' => true,
                            'query_args' => array(
                                'posts_per_page' => -1,
                                'post_type' => 'barnet-resource',
                              /*
                                'tax_query' => array(
                                    array(
                                        'taxonomy' => 'resource-folder',
                                        'field' => 'slug',
                                        'terms' => 'other-products-documents',
                                    )
                                )
                              */
                            )
                        ),
                        array(
                            'type' => 'select',
                            'name' => esc_html__('Icon', $this->domain),
                            'id' => 'other_icon',
                            self::OPTION => array(
                                '' => esc_html__('Select a icon', $this->domain),
                                'icon-formula-card' => esc_html__('icon-formula-card', $this->domain),
                                'icon-formula-card' => esc_html__('icon-formula-sheet', $this->domain), 
                                'icon-kiss-sheet' => esc_html__('icon-kiss-sheet', $this->domain),
                                'icon-sds' => esc_html__('icon-sds', $this->domain),
                                'icon-spec' => esc_html__('icon-spec', $this->domain),                                
                                'icon-summary' => esc_html__('icon-summary', $this->domain),
                                'icon-presentation' => esc_html__('icon-presentation', $this->domain),                                
                            ),
                        )
                    ),
                ),
                
                array(
                    'type' => 'radio',
                    'name' => esc_html__('Region Type', $this->domain),
                    'id' => $this->prefix . 'area',
                    self::OPTION => self::$AREA_LIST,
                    'std' => 'global'
                ),
                array(
                    'type' => 'text',
                    'name' => esc_html__('Use Level', $this->domain),
                    'id' => $this->prefix . 'usage',
                    'desc' => esc_html__('Use Level', $this->domain),
                ),
                array(
                    'type' => 'text',
                    'name' => esc_html__('ISO Natural Rating', $this->domain),
                    'id' => $this->prefix . 'iso',
                    'desc' => esc_html__('ISO Natural Rating', $this->domain),
                ),
                array(
                    'type' => 'checkbox',
                    'name' => esc_html__('Product is featured', $this->domain),
                    'id' => $this->prefix . 'featured',
                ),
                array(
                    'type' => self::GROUP,
                    'name' => esc_html__('Global Compliance', $this->domain),
                    'id' => $this->prefix . 'global_compliance',
                    self::CLONE => true,
                    self::FIELDS => array(
                        array(
                            'name' => esc_html__('Label', $this->domain),
                            'id' => 't_label',
                            'type' => 'text',
                        ),
                        array(
                            'name' => esc_html__('Text', $this->domain),
                            'id' => 't_text',
                            'type' => 'textarea',
                        ),
                    ),
                ),
                array(
                    'type' => self::GROUP,
                    'name' => esc_html__('Architecture/Technology', $this->domain),
                    'id' => $this->prefix . 'architecture_technology',
                    self::CLONE => false,
                    self::FIELDS => array(
                        array(
                            'name' => esc_html__('Title', $this->domain),
                            'id' => 'at_title',
                            'type' => 'text',
                        ),
                        array(
                            'type' => self::WYSIWYG,
                            'name' => esc_html__('Description', $this->domain),
                            'id' => 'at_description',
                            'raw' => true,
                            'options' => array(
                                'media_buttons' => false,
                            ),
                        ),
                        array(
                            'name' => esc_html__('Image', $this->domain),
                            'id' => 'at_image',
                            'type' => 'image_advanced',
                            'max_file_uploads' => 1,
                            'max_status' => 'false',
                        ),
                        array(
                            'name' => esc_html__('Image Text', $this->domain),
                            'id' => 'at_image_text',
                            'type' => 'text',
                        ),
                    ),
                ),

                array(
                    'type' => self::GROUP,
                    'name' => esc_html__('Alternate Product', $this->domain),
                    'id' => $this->prefix . 'alternate_group',
                    self::CLONE => true,
                    self::FIELDS => array(
                        array(
                            'type' => self::SELECTADVANCED,
                            'name' => esc_html__('Product', $this->domain),
                            'id' => $this->prefix . 'alternate_product',
                            self::OPTION => $this->getProducts(),
                        ),
                        array(
                            'type' => 'text',
                            'name' => esc_html__('Description', $this->domain),
                            'id' => $this->prefix . 'alternate_description',
                            'desc' => esc_html__('Description', $this->domain),
                            self::OPTION => array(
                                self::MEDIABUTTONS => false,
                            ),
                        ),
                    ),
                ),
                array(
                    'type' => 'checkbox',
                    'name' => esc_html__('Show on logged out view', $this->domain),
                    'id' => $this->prefix . 'public',
                ),
                array(
                    'type' => 'checkbox',
                    'name' => esc_html__('Is Only for Code List?', $this->domain),
                    'id' => $this->prefix . 'only_for_code_list',
                ),
            ),
        );
    }

    public function createTaxonomy()
    {
        register_taxonomy(self::PRODUCTCATEGORY, array('books'), array(
            'hierarchical' => true,
            'labels' => array(
                'name' => _x('Products Category', 'Products category'),
                'singular_name' => _x('Product category', 'Product category'),
                'search_items' => __('Search Product category'),
                'all_items' => __('All Products category'),
                'parent_item' => __('Parent Products category'),
                'parent_item_colon' => __('Parent Product category:'),
                'edit_item' => __('Edit Product category'),
                'update_item' => __('Update Product category'),
                'add_new_item' => __('Add New Product category'),
                'new_item_name' => __('New Product category Name'),
                'menu_name' => __('Product Category'),
            ),
            'show_ui' => true,
            'show_in_rest' => true,
            'show_admin_column' => true,
            'query_var' => true,
			'show_tagcloud'              => false,
            'rewrite' => array('slug' => self::PRODUCTCATEGORY),
        ));
        register_taxonomy(self::PRODUCTTYPE, array('books'), array(
            'hierarchical' => false,
            'labels' => array(
                'name' => _x('Product Type', 'Product Type'),
                'singular_name' => _x('Product Type', 'Product Type'),
                'search_items' => __('Search Product Type'),
                'all_items' => __('All Product Types'),
                //'parent_item' => __('Parent Products category'),
                //'parent_item_colon' => __('Parent Product category:'),
                'edit_item' => __('Edit Product Type'),
                'update_item' => __('Update Product Type'),
                'add_new_item' => __('Add New Product Type'),
                'new_item_name' => __('New Product Type Name'),
                'menu_name' => __('Product Type'),
            ),
            'show_ui' => true,
            'show_in_rest' => false,
            //'show_admin_column' => true,
            'query_var' => true,
            'rewrite' => array('slug' => self::PRODUCTTYPE),
        ));
    }

    public function addCustomFieldTaxonomy()
    {
        return array(
            'taxonomies' => [self::PRODUCTCATEGORY],
            'title' => 'Standard Fields',
            self::FIELDS => array(
                array(
                    'name' => esc_html__('Hide Product Detail', $this->domain),
                    'id' => 'is_hide',
                    'type' => 'checkbox'
                ),
                array(
                    'name' => esc_html__('Order', $this->domain),
                    'id' => 'order',
                    'type' => 'number',
                    'min'  => 0,
                ),

            ),
        );
    }

    public function getTitlePlaceHolder()
    {
        return array($this->postType => "Product Trade Name");
    }

    public function addRelationship()
    {
        $this->addDataRelationship($this->postType, 'barnet-concept', null, true, 'Related Concepts');
        $this->addDataRelationship($this->postType, 'barnet-formula', null, true, 'Starting Formulas');
        $this->addDataRelationship($this->postType, 'barnet-pattribute', null, true, 'Key Attributes');
        $this->addDataRelationship('barnet-formula', 'barnet-concept', null, false);
        $this->addDataRelationship(
            $this->postType,
            'barnet-digital-code',
            null,
            false,
            'Digital Code',
            null,
            false,
            true,
            false,
            $this->getProductDigitalCodes()
        );
        $this->addDataRelationship($this->postType, 'barnet-role', null, false, 'Product Roles');
    }
}

$barnetProduct = new BarnetProduct('product_', 'barnet-product');
