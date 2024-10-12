<?php

class BarnetFormulaAttribute extends BarnetDataType
{
    const FATTRIBUTE_SET = "fattribute-set";
    const ATTRIBUTE_SET = "Formula Attribute Set";
    public function createPostType()
    {
        register_post_type(
            $this->postType,
            $this->buildArgs(
                'Formula Attribute',
                'Formula Attributes',
                array(self::FATTRIBUTE_SET),
                'Barnet Formula Attributes'
            )
        );
    }

    public function addExt()
    {
        return array(
            'title' => esc_html__('Formula Attribute Type', $this->domain),
            'id' => $this->postType,
            'post_types' => array($this->postType),
            'context' => 'normal',
            'priority' => 'high',
            'clone' => true,
            'fields' => array(
                array(
                    'type' => 'file_advanced',
                    'name' => esc_html__('Image', $this->domain),
                    'id' => $this->prefix . 'media',
                    'desc' => esc_html__('Image', $this->domain),
                    'mime_type' => $this->mimeImage,
                    'max_file_uploads' => 1,
                ),
            ),
        );
    }

    public function createTaxonomy()
    {
        register_taxonomy(self::FATTRIBUTE_SET, $this->menuSlugTaxonomy, array(
            'hierarchical' => true,
            'labels' => array(
                'name' => _x(self::ATTRIBUTE_SET, self::ATTRIBUTE_SET),
                'singular_name' => _x(self::ATTRIBUTE_SET, self::ATTRIBUTE_SET),
                'search_items' => __('Search ' . self::ATTRIBUTE_SET),
                'all_items' => __('All ' . self::ATTRIBUTE_SET),
                'parent_item' => __('Parent ' . self::ATTRIBUTE_SET),
                'parent_item_colon' => __('Parent '. self::ATTRIBUTE_SET . ':'),
                'edit_item' => __('Edit '. self::ATTRIBUTE_SET),
                'update_item' => __('Update ' . self::ATTRIBUTE_SET),
                'add_new_item' => __('Add New ' . self::ATTRIBUTE_SET),
                'new_item_name' => __('New ' . self::ATTRIBUTE_SET . ' Name'),
                'menu_name' => __(self::ATTRIBUTE_SET),
            ),
            'show_ui' => true,
            'show_in_rest' => true,
            'show_admin_column' => true,
            'query_var' => true,
            'rewrite' => array('slug' => 'attribute-set'),
        ));
    }

    public function addCustomFieldTaxonomy()
    {
        return array(
            'taxonomies' => self::FATTRIBUTE_SET,
            'title' => 'Standard Fields Attribute',
            'fields' => array(
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
        return array($this->postType => "Formula Attribute Name");
    }
}

$BarnetFormulaAttribute = new BarnetFormulaAttribute('formula-attribute_', 'barnet-fattribute', 'barnet-formula');
