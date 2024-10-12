<?php

class BarnetProductConcept extends BarnetDataType
{
    const PRODUCTCONCEPT = "Product-Concept Mapping";
    const SUB_CONCEPT_CATEGORY = "sub-concept-category";
    const SUBCONCEPT = "Sub Concepts";
    public function createPostType()
    {
        register_post_type(
            $this->postType,
            $this->buildArgs(
                self::PRODUCTCONCEPT,
                'Product Concepts',
                array(self::SUB_CONCEPT_CATEGORY),
                'Barnet Product Concepts'
            )
        );
    }

    public function addExt()
    {
        return array(
            'title' => esc_html__('Product Concept Type', $this->domain),
            'id' => $this->postType,
            'post_types' => array($this->postType),
            'context' => 'normal',
            'priority' => 'high',
            'fields' => array(
                array(
                    'type' => 'wysiwyg',
                    'name' => esc_html__('Description', $this->domain),
                    'id' => 'product_concept_description',
                ),
                array(
                    'type' => 'text',
                    'name' => esc_html__('Right Sub-Text Product', $this->domain),
                    'id' => 'product_concept_right_text',
                ),
                array(
                    'type' => 'number',
                    'name' => esc_html__('Order', $this->domain),
                    'id' => 'product_concept_order',
                ),
            ),
        );
    }

    public function createTaxonomy()
    {
        register_taxonomy(self::SUB_CONCEPT_CATEGORY, array($this->menuSlugTaxonomy), array(
            'hierarchical' => true,
            'labels' => array(
                'name' => _x(self::SUBCONCEPT, self::SUBCONCEPT),
                'singular_name' => _x(self::SUBCONCEPT, self::SUBCONCEPT),
                'search_items' => __('Search Sub Concept'),
                'all_items' => __('All Sub Concepts'),
                'parent_item' => __('Parent Sub Concepts'),
                'parent_item_colon' => __('Parent Sub Concepts:'),
                'edit_item' => __('Edit Sub Concept'),
                'update_item' => __('Update Sub Concept'),
                'add_new_item' => __('Add New Sub Concept'),
                'new_item_name' => __('New Sub Concept'),
                'menu_name' => __('Sub Concept'),
            ),
            'show_ui' => true,
            'show_in_rest' => true,
            'show_admin_column' => true,
            'query_var' => true,
            'rewrite' => array('slug' => self::SUB_CONCEPT_CATEGORY),
        ));
    }

    public function addCustomFieldTaxonomy()
    {
        return array(
            'taxonomies' => self::SUB_CONCEPT_CATEGORY,
            'title' => 'Standard Fields Sub Concept',
            'fields' => array(
                array(
                    'name' => esc_html__('Image', $this->domain),
                    'id' => 'image',
                    'type' => 'image_advanced',
                    'max_file_uploads' => 1,
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

    public function addRelationship()
    {
        $this->addDataRelationship($this->postType, 'barnet-product', null, false, null, self::PRODUCTCONCEPT, true);
        $this->addDataRelationship($this->postType, 'barnet-concept', null, false, null, self::PRODUCTCONCEPT, true);
    }
}

$barnetProductConcept = new BarnetProductConcept('product_concept_', 'barnet-pconcept', 'barnet-product');
