<?php

class BarnetProductAttribute extends BarnetDataType
{
    const ATTRIBUTE_SET = "attribute-set";
    const ATTRIBUTESET = "Attribute Set";
    public function createPostType()
    {
        register_post_type(
            $this->postType,
            $this->buildArgs(
                'Product Attribute',
                'Product Attributes',
                array(self::ATTRIBUTE_SET),
                'Barnet Product Attributes'
            )
        );
    }

    public function addExt()
    {
		$concepOptions=[];
		$concepOptions['default']='defualt';
		$concepts = get_posts([
			'post_type' => 'barnet-concept',
			'post_status' => 'publish',
			'orderby'  => 'title',
			'order'    => 'ASC',
			'numberposts' => -1
			// 'order'    => 'ASC'
		  ]);
		 foreach($concepts as $concept){
			 $concepOptions[$concept->post_name]=$concept->post_title;
		 }
		
        return array(
            'title' => esc_html__('Product Attribute Type', $this->domain),
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
                array(
                    'name' => esc_html__('Thumbnail Image', $this->domain),
                    'id' => $this->prefix . 'thumnail',
                    'type' => 'image_advanced',
                    'max_file_uploads' => 1,
                    'max_status' => 'false',
                ),
                array(
                    'name' => esc_html__('Short Description', $this->domain),
                    'id' => $this->prefix . 'short_description',
                    'type' => 'textarea',
                ),
                array(
                    'type' => 'number',
                    'name' => esc_html__('Product Attribute Order', $this->domain),
                    'id' => $this->prefix . 'order',
                ),
				
				
				array(
                    'type' => 'select',
                    'name' => esc_html__('Concept', $this->domain),
                    'id' => '_concept_slug',
                    'options' => $concepOptions,
                ),				
            ),
        );
    }

    public function createTaxonomy()
    {
        register_taxonomy(self::ATTRIBUTE_SET, array($this->menuSlugTaxonomy), array(
            'hierarchical' => true,
            'labels' => array(
                'name' => _x(self::ATTRIBUTESET, self::ATTRIBUTESET),
                'singular_name' => _x(self::ATTRIBUTESET, self::ATTRIBUTESET),
                'search_items' => __('Search Attribute Set'),
                'all_items' => __('All Attribute Set'),
                'parent_item' => __('Parent Attribute Set'),
                'parent_item_colon' => __('Parent Attribute Set:'),
                'edit_item' => __('Edit Attribute Set'),
                'update_item' => __('Update Attribute Set'),
                'add_new_item' => __('Add New Attribute Set'),
                'new_item_name' => __('New Attribute Set Name'),
                'menu_name' => __(self::ATTRIBUTESET),
            ),
            'show_ui' => true,
            'show_in_rest' => true,
            'show_admin_column' => true,
            'query_var' => true,
            'rewrite' => array('slug' => self::ATTRIBUTE_SET),
        ));
    }

    public function addCustomFieldTaxonomy()
    {
        return array(
            'taxonomies' => "attribute-set",
            'title' => 'Standard Fields Attribute Set',
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
        return array($this->postType => "Product Attribute Name");
    }
}

$barnetProductAttribute = new BarnetProductAttribute('product-attribute_', 'barnet-pattribute', 'barnet-product');
