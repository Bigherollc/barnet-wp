<?php

class BarnetConcept extends BarnetDataType
{
    const CONCEPT_CATEGORY = "concept-category";
    const CONCEPT_TYPE = "concept-type";
    const IMAGE_ADVANCED = "image_advanced";
    const MAX_FILE_UPLOAD = "max_file_uploads";
    const MAX_STATUS = "max_status";
    const OPTION = "options";
    const FALSE = "false";
    const CLONE = "clone";
    const FIELD = "fields";


    public function createPostType()
    {
        register_post_type(
            $this->postType,
            $this->buildArgs(
                'Concepts',
                'Barnet Concepts',
                array(self::CONCEPT_CATEGORY, self::CONCEPT_TYPE)
            )
        );
    }

    public function addExt()
    {
        $fields = array(
            /*
            array(
                'type' => 'select',
                'name' => esc_html__('Concept Type', $this->domain),
                'id' => $this->prefix . 'type',
                self::OPTION => array(
                    'Active' => esc_html__('Active', $this->domain),
                    'System' => esc_html__('System', $this->domain),
                    'ActiveSystem' => esc_html__('Active and System', $this->domain),
                ),
            ),*/
            array(
                'type' => 'WYSIWYG',
                'name' => esc_html__('Concept Description', $this->domain),
                'id' => $this->prefix . 'description',
                self::OPTION => array(
                    'media_buttons' => false,
                ),
            ),
            array(
                'type' => 'textarea',
                'name' => esc_html__('Concept Short Description', $this->domain),
                'id' => $this->prefix . 'short_description',
            ),
            array(
                'type' => 'radio',
                'name' => esc_html__('Region Type', $this->domain),
                'id' => $this->prefix . 'area',
                BarnetProduct::OPTION => BarnetProduct::$AREA_LIST,
                'std' => 'global'
            ),
            array(
                'type' => 'select_advanced',
                'name' => esc_html__('Concept Parent', $this->domain),
                'id' => $this->prefix . 'parent',
                self::OPTION => $this->getConcepts(),
            ),
            array(
                'type' => self::IMAGE_ADVANCED,
                self::MAX_FILE_UPLOAD => 1,
                self::MAX_STATUS => self::FALSE,
                'name' => esc_html__('Header Image', $this->domain),
                'id' => $this->prefix . 'image',
            ),
            array(
                'type' => self::IMAGE_ADVANCED,
                self::MAX_FILE_UPLOAD => 1,
                self::MAX_STATUS => self::FALSE,
                'name' => esc_html__('Thumbnail', $this->domain),
                'id' => $this->prefix . 'thumbnail',
            ),
            array(
                'type' => self::IMAGE_ADVANCED,
                self::MAX_FILE_UPLOAD => 1,
                self::MAX_STATUS => self::FALSE,
                'name' => esc_html__('Interactive Image Web', $this->domain),
                'id' => $this->prefix . 'interactive_image',
            ),
            array(
                'type' => self::IMAGE_ADVANCED,
                self::MAX_FILE_UPLOAD => 1,
                self::MAX_STATUS => self::FALSE,
                'name' => esc_html__('Interactive Image App', $this->domain),
                'id' => $this->prefix . 'interactive_image_app',
            ),
            array(
                'type' => 'radio',
                'name' => esc_html__('Header Style', $this->domain),
                'id' => $this->prefix . 'style',
                self::OPTION => array(
                    'light' => esc_html__('Light -Color text #fff', $this->domain),
                    'dark' => esc_html__('Dark -Color text #000', $this->domain),
                ),
            ),
            /*array(
                'type' => 'select_advanced',
                'name' => esc_html__('Concept Children', $this->domain),
                'id' => $this->prefix . 'children',
                'options' => $this->getConcepts(),
            ),*/
            array(
                'type' => 'number',
                'name' => esc_html__('Concept Order', $this->domain),
                'id' => $this->prefix . 'order',
            ),
            /*array(
                'type' => 'group',
                'name' => esc_html__('Related Media', $this->domain),
                'id' => $this->prefix . 'related_media',
                self::CLONE => true,
                self::FIELD => array(
                    array(
                        'name' => esc_html__('Label', $this->domain),
                        'id' => 'media_label',
                        'type' => 'text',
                    ),
                    array(
                        'type' => self::IMAGE_ADVANCED,
                        self::MAX_FILE_UPLOAD => 1,
                        self::MAX_STATUS => self::FALSE,
                        'name' => esc_html__('File', $this->domain),
                        'id' => $this->prefix . 'media_file',
                    ),
                ),
            ),*/
            array(
                'type' => 'group',
                'name' => esc_html__('The Concept\'s Presention\'', $this->domain),
                'id' => $this->prefix . 'presention_docs',
                self::CLONE => true,
                self::FIELD => array(
                    array(
                        'name' => esc_html__('Label', $this->domain),
                        'id' => 'label',
                        'type' => 'text',
                    ),
                    array(
                        'type' => 'post',
                        'name' => esc_html__('File', $this->domain),
                        'id' => 'doc',
                        'desc' => esc_html__('The Concept\'s Presention document, attached as a PDF', $this->domain),
                        'post_type' => 'barnet-resource',
                        'field_type' => 'select_advanced',
                        'ajax' => true,
                        'query_args' => array(
                            'posts_per_page' => -1,
                            'post_type' => 'barnet-resource',
                            'tax_query' => array(
                                array(
                                    'taxonomy' => 'resource-folder',
                                    'field' => 'slug',
                                    'terms' => 'concept-presentions',
                                )
                            )
                        )
                    )
                ),
            ),
            array(
                'type' => 'post',
                'name' => esc_html__('The Concept\'s Video', $this->domain),
                'id' => $this->prefix . 'videos_doc',
                'desc' => esc_html__('The Concept\'s Video, attached as a Videos', $this->domain),
                'post_type' => 'barnet-resource',
                'field_type' => 'select_advanced',
                'ajax' => true,
                'query_args' => array(
                    'posts_per_page' => -1,
                    'post_type' => 'barnet-resource',
                    'tax_query' => array(
                        array(
                            'taxonomy' => 'resource-folder',
                            'field' => 'slug',
                            'terms' => 'concept-videos',
                        )
                    )
                )
            ),
            array(
                'type' => 'checkbox',
                'name' => esc_html__('Is Formula Collection', $this->domain),
                'id' => $this->prefix . 'formula_collection',
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
        );

        foreach ($this->extraFields as $fieldName) {
            $fields[] = array(
                'type' => 'hidden',
                'id' => $fieldName
            );
        }

        return array(
            'title' => esc_html__('Concept Type', $this->domain),
            'id' => $this->postType,
            'post_types' => array($this->postType),
            'context' => 'normal',
            'priority' => 'high',
            self::CLONE => true,
            self::FIELD => $fields
        );
    }

    public function createTaxonomy()
    {
        register_taxonomy(self::CONCEPT_CATEGORY, array('books'), array(
            'hierarchical' => true,
            'labels' => array(
                'name' => _x('Concepts Section', 'Concepts Section'),
                'singular_name' => _x('Concept Sections', 'Concept Sections'),
                'search_items' => __('Search Concept section'),
                'all_items' => __('All Concepts section'),
                'parent_item' => __('Parent Concepts section'),
                'parent_item_colon' => __('Parent Concept section:'),
                'edit_item' => __('Edit Concept section'),
                'update_item' => __('Update Concept section'),
                'add_new_item' => __('Add New Concept section'),
                'new_item_name' => __('New Concept section'),
                'menu_name' => __('Concept Section'),
            ),
            'show_ui' => true,
            'show_in_rest' => true,
            'show_admin_column' => true,
            'query_var' => true,
            'rewrite' => array('slug' => self::CONCEPT_CATEGORY),
        ));

        register_taxonomy(self::CONCEPT_TYPE, array('books'), array(
            'hierarchical' => false,
            'labels' => array(
                'name' => _x('Concept Type', 'Concept Type'),
                'singular_name' => _x('Concept Type', 'Concept Type'),
                'search_items' => __('Search Concept Type'),
                'all_items' => __('All Concept Types'),
                //'parent_item' => __('Parent Products category'),
                //'parent_item_colon' => __('Parent Product category:'),
                'edit_item' => __('Edit Concept Type'),
                'update_item' => __('Update Concept Type'),
                'add_new_item' => __('Add New Concept Type'),
                'new_item_name' => __('New Concept Type Name'),
                'menu_name' => __('Concept Type'),
            ),
            'show_ui' => true,
            'show_in_rest' => false,
            //'show_admin_column' => true,
            'query_var' => true,
            'rewrite' => array('slug' => self::CONCEPT_TYPE),
        ));

    }

    public function addCustomFieldTaxonomy()
    {
        return array(
            'taxonomies' => self::CONCEPT_CATEGORY,
            'title' => 'Standard Fields Concept Category',
            self::FIELD => array(
                array(
                    'name' => esc_html__('Order', $this->domain),
                    'id' => 'order',
                    'type' => 'number',
                    'min'  => 0,
                ),
            ),
        );
    }

    public function addRelationship()
    {
        $this->addDataRelationship($this->postType, 'barnet-resource', null, true, 'Related Resource');
        $this->addDataRelationship($this->postType, 'barnet-role', null, false, 'Concept Roles');
        $this->addDataRelationship(
            $this->postType,
            'concept-interactive',
            null,
            false,
            'InterActice Design',
            'Concept',
            false,
            true
        );
        $this->addDataRelationship($this->postType, $this->postType, null, true, 'Related Concepts');
    }
}

$barnetConcept = new BarnetConcept('concept_', 'barnet-concept');
