<?php

class BarnetPage extends BarnetDataType
{


    public function createPostType()
    {
    }

    public function addExt()
    {
        return array(
            'title' => esc_html__('Custom Fields', $this->domain),
            'id' => 'barnet-page',
            'post_types' => array($this->postType),
            'context' => 'normal',
            'priority' => 'high',
            'clone' => true,
            'fields' => array(

                array(
                    'type' => 'radio',
                    'name' => esc_html__('Style', $this->domain),
                    'id' => $this->prefix . 'style',
                    'options' => array(
                        'light' => esc_html__('Light', $this->domain),
                        'dark' => esc_html__('Dark', $this->domain),
                    ),
                ),
                array(
                    'name' => esc_html__('Thumbnail Image', $this->domain),
                    'id' => $this->prefix . 'bb_image',
                    'type' => 'image_advanced',
                    'max_file_uploads' => 1,
                    'max_status' => 'false',
                ),
                array(
                    'name' => esc_html__('Background Image', $this->domain),
                    'id' => $this->prefix . 'background_image',
                    'type' => 'image_advanced',
                    'max_file_uploads' => 1,
                    'max_status' => 'false',
                ),
                array(
                    'name' => esc_html__('Title Page', $this->domain),
                    'id' => $this->prefix . 'title',
                    'type' => 'text',
                ),
                array(
                    'name' => esc_html__('Short Description', $this->domain),
                    'id' => $this->prefix . 'short_description',
                    'type' => 'textarea',
                ),
                array(
                    'type' => 'checkbox',
                    'name' => esc_html__('Show App', $this->domain),
                    'id' => $this->prefix . 'show_app',
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

$barnetPage = new BarnetPage("p_", 'page');
