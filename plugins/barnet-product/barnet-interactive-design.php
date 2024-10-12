<?php

class BarnetInteractiveDesign extends BarnetDataType
{
    public function createPostType()
    {
        register_post_type(
            $this->postType,
            $this->buildArgs(
                'Interactive Diagrams',
                'Interactive Designs'
            )
        );
    }

    public function addExt()
    {
        return array(
            'title' => esc_html__('Interactive Design Type', $this->domain),
            'id' => $this->postType,
            'post_types' => array($this->postType),
            'context' => 'normal',
            'priority' => 'high',
            'clone' => true,
            'fields' => array(
                array(
                    'type' => 'text',
                    'name' => esc_html__('Slide Sub-Title', $this->domain),
                    'id' => $this->prefix . 'subtitle',
                ),
                array(
                    'type' => 'text',
                    'name' => esc_html__('Slide Link Label', $this->domain),
                    'id' => $this->prefix . 'link_label',
                ),
                array(
                    'name' => esc_html__('Slide Link', $this->domain),
                    'id' => $this->prefix . 'slide_link',
                    'ajax' => true,
                    'query_args' => array(
                        'post_status' => 'publish'
                    ),
                    'type' => 'taxonomy',
                    'taxonomy' => 'sub-concept-category'
                ),
                array(
                    'type' => 'wysiwyg',
                    'name' => esc_html__('Html block', $this->domain),
                    'id' => $this->prefix . 'html',
                ),

                array(
                    'type' => 'image_advanced',
                    'max_file_uploads' => 1,
                    'max_status' => 'false',
                    'name' => esc_html__('Image', $this->domain),
                    'id' => $this->prefix . 'image',
                ),
                array(
                    'type' => 'text',
                    'name' => esc_html__('Coordinates', $this->domain),
                    'id' => $this->prefix . 'coordinates',
                ),
            ),
        );
    }

    public function createTaxonomy()
    {
    }


    public function addRelationship()
    {

    }
}

$barnetInteractiveDesign = new BarnetInteractiveDesign('ia_', 'concept-interactive', 'barnet-concept');
