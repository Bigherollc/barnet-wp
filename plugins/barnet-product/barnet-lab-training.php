<?php

class BarnetLabTraining extends BarnetDataType
{
    public function createPostType()
    {
        register_post_type(
            $this->postType,
            $this->buildArgs(
                'Lab Training',
                'Lab Training'
            )
        );
    }

    public function addExt()
    {
        return array(
            'title' => esc_html__('Lab Training Type', $this->domain),
            'id' => $this->postType,
            'post_types' => array($this->postType),
            'context' => 'normal',
            'priority' => 'high',
            'clone' => true,
            'fields' => array(
                array(
                    'type' => 'number',
                    'name' => esc_html__('Lab Number', $this->domain),
                    'id' => $this->prefix . 'number',
                ),
                array(
                    'type' => 'text',
                    'name' => esc_html__('Lab Title', $this->domain),
                    'id' => $this->prefix . 'title',
                ),
                array(
                    'type' => 'text',
                    'name' => esc_html__('Lab Code', $this->domain),
                    'id' => $this->prefix . 'code',
                ),
                array(
                    'type' => 'textarea',
                    'name' => esc_html__('Lab Descripttion', $this->domain),
                    'id' => $this->prefix . 'description',
                ),
                array(
                    'type' => 'group',
                    'name' => esc_html__('Group lessons', $this->domain),
                    'id' => 'group_lessions',
                    'clone' => true,
                    'fields' => array(
                        array(
                            'name' => esc_html__('Lesson Title', $this->domain),
                            'id' => 'lession_title',
                            'type' => 'text',
                        ),
                        array(
                            'name' => esc_html__('Lesson Content', $this->domain),
                            'id' => 'lession_content',
                            'type' => 'wysiwyg',
                        ),
                    ),
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

$barnetLabTraining = new BarnetLabTraining('lab_', 'lab-training');
