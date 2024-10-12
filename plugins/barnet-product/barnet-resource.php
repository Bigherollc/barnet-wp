<?php

class BarnetResource extends BarnetDataType
{
    const RESOURCE_TYPE = "resource-type";
    const RESOURCE_FOLDER = "resource-folder";
    const MAX_FILE_UPLOADS = "max_file_uploads";
    const FIELDS = "fields";
    const TITLE = "title";
    const OPTIONS = "options";

    public function createPostType()
    {
        register_post_type(
            $this->postType,
            $this->buildArgs(
                'Barnet Resource',
                'Barnet Resources',
                array(self::RESOURCE_TYPE, self::RESOURCE_FOLDER)
            )
        );
    }

    public function addExt()
    {
        return array(
            self::TITLE => esc_html__('Resource Type', $this->domain),
            'id' => $this->postType,
            'post_types' => array($this->postType),
            'context' => 'normal',
            'priority' => 'high',
            'clone' => true,
            self::FIELDS => array(
                array(
                    'type' => 'file_advanced',
                    'name' => esc_html__('Media', $this->domain),
                    'id' => $this->prefix . 'media',
                    'desc' => esc_html__('Media file', $this->domain),
                    self::MAX_FILE_UPLOADS => 1
                ),
                array(
                    'type' => 'checkbox',
                    'name' => esc_html__('Show Pdf In Resource', $this->domain),
                    'id' => 'show_resource',
                ),
                array(
                    'type' => 'checkbox',
                    'name' => esc_html__('Show Pdf In Search', $this->domain),
                    'id' => 'show_search',
                ),
                array(
                    'type' => 'select_advanced',
                    'name' => esc_html__('PPT Source', $this->domain),
                    'id' => $this->prefix . 'ppt_source',
                    self::OPTIONS => $this->getPPTList(),
                ),
                array(
                    'type' => 'file',
                    'name' => esc_html__('File', 'online-generator'),
                    'id' => $this->prefix . 'ppt_upload',
                    'max_file_uploads' => 1
                ),
                array(
                    'type' => 'image_advanced',
                    'name' => esc_html__('Thumbnail', $this->domain),
                    'id' => $this->prefix . 'image',
                    'force_delete' => false,
                    self::MAX_FILE_UPLOADS => 1,
                    'max_status' => false,
                    'image_size' => 'thumbnail',
                    'desc' => esc_html__('Thumbnail File', $this->domain)
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
                    'name' => esc_html__('Roles', $this->domain),
                    'id' => $this->prefix . 'roles',
                    self::OPTIONS => $this->getRoleList(),
                    'multiple' => true,
                ),
                array(
                    'type' => 'WYSIWYG',
                    'name' => esc_html__('Resource Description', $this->domain),
                    'id' => $this->prefix . 'description',
                    'raw' => true,
                    self::OPTIONS => array(
                        'media_buttons' => false,
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
                    'type' => 'checkbox',
                    'name' => esc_html__('Show before See more', $this->domain),
                    'id' => $this->prefix . 'show_see_more_before'
                ),
                array(
                    'type' => 'text',
                    'name' => esc_html__('Order', $this->domain),
                    'id' => $this->prefix . 'order'
                ),
            ),
        );
    }

    protected function addMenuAppAdminTaxonomy($name, $link) {
        add_action('admin_menu', function () use ($name, $link) {
            add_submenu_page(
                Barnet::BARNET_MENU_APP_ADMIN,
                $name,
                $name,
                'read',
                $link
            );
        });
    }

    public function createTaxonomy()
    {
        $resourceTypeName = 'Resource type';
        register_taxonomy(self::RESOURCE_TYPE, $this->menuSlugTaxonomy, array(
            'hierarchical' => true,
            'labels' => array(
                'name' => _x('Resources Type', $resourceTypeName),
                'singular_name' => _x($resourceTypeName, $resourceTypeName),
                'search_items' => __('Search Resource type'),
                'all_items' => __('All Resources type'),
                'parent_item' => __('Parent Resources type'),
                'parent_item_colon' => __('Parent Resource type:'),
                'edit_item' => __('Edit Resource type'),
                'update_item' => __('Update Resource type'),
                'add_new_item' => __('Add New Resource type'),
                'new_item_name' => __('New Resource type Name'),
                'menu_name' => __('Resource Type'),
            ),
            'show_ui' => true,
            'show_in_rest' => true,
            'show_admin_column' => true,
            'query_var' => true,
            'rewrite' => array('slug' => self::RESOURCE_TYPE),
        ));
        $this->addMenuAppAdminTaxonomy(
            $resourceTypeName,
            'edit-tags.php?taxonomy=' . self::RESOURCE_TYPE . '&amp;post_type=' . $this->menuSlugTaxonomy
        );

        $resourceFolderName = 'Resource folder';
        register_taxonomy(self::RESOURCE_FOLDER, $this->menuSlugTaxonomy, array(
            'hierarchical' => true,
            'labels' => array(
                'name' => _x('Resources Folder', $resourceFolderName),
                'singular_name' => _x($resourceFolderName, $resourceFolderName),
                'search_items' => __('Search Resource folder'),
                'all_items' => __('All Resources folder'),
                'parent_item' => __('Parent Resources folder'),
                'parent_item_colon' => __('Parent Resource folder:'),
                'edit_item' => __('Edit Resource folder'),
                'update_item' => __('Update Resource folder'),
                'add_new_item' => __('Add New Resource folder'),
                'new_item_name' => __('New Resource folder Name'),
                'menu_name' => __('Resource Folder'),
            ),
            'show_ui' => true,
            'show_in_rest' => true,
            'show_admin_column' => true,
            'query_var' => true,
            'rewrite' => array('slug' => self::RESOURCE_FOLDER),
        ));

        $this->addMenuAppAdminTaxonomy(
            $resourceFolderName,
            'edit-tags.php?taxonomy=' . self::RESOURCE_FOLDER . '&amp;post_type=' . $this->menuSlugTaxonomy
        );
    }

    public function addCustomFieldTaxonomy()
    {
        return array(
            array(
                'taxonomies' => self::RESOURCE_FOLDER,
                self::TITLE => 'Standard Fields Resources folder',
                self::FIELDS => array(
                    array(
                        'name' => esc_html__('Order', $this->domain),
                        'id' => 'order',
                        'type' => 'number',
                        'min' => 0,
                    ),
                    array(
                        'type' => 'checkbox',
                        'name' => esc_html__('Show App', $this->domain),
                        'id' => 'show_app',
                    ),
                ),
            ),
            array(
                'taxonomies' => self::RESOURCE_TYPE,
                self::TITLE => 'Standard Fields Resources type',
                self::FIELDS => array(
                    array(
                        'name' => esc_html__('Order', $this->domain),
                        'id' => 'order',
                        'type' => 'number',
                        'min' => 0,
                    ),
                    array(
                        'name' => esc_html__('Show on landing page', $this->domain),
                        'id' => 'is_showed',
                        'type' => 'checkbox'
                    ),
                ),
            )
        );
    }

    public function addRelationship()
    {
        $this->addDataRelationship($this->postType, 'barnet-product');
        $this->addDataRelationship($this->postType, 'barnet-formula');
        $this->addDataRelationship($this->postType, 'barnet-concept');
        $this->addDataRelationship($this->postType, $this->postType, null, true);
    }
}

$barnetResource = new BarnetResource('resource_', 'barnet-resource', 'barnet-role');
