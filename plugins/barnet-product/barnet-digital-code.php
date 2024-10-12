<?php

class BarnetDigitalCode extends BarnetDataType
{
    public function createPostType()
    {
        register_post_type(
            $this->postType,
            $this->buildArgs(
                'Codes Lists',
                'Digital Codes'
            )
        );
    }

    public function addExt()
    {
        return array(
            'title' => esc_html__('Digital Code', $this->domain),
            'id' => 'barnet-concept',
            'post_types' => array($this->postType),
            'context' => 'normal',
            'priority' => 'high',
            'clone' => true,
            'fields' => array(
                array(
                    'type' => 'text',
                    'name' => esc_html__('Code', $this->domain),
                    'id' => $this->prefix . 'code',
                ),
            ),
        );
    }

    public function addRelationship()
    {
        $this->addDataRelationship($this->postType, 'barnet-customer', null, false, null, null, true, false);
    }
}

$barnetDigitalCode = new BarnetDigitalCode('digital_', 'barnet-digital-code');
