<?php

class BarnetConceptBook extends BarnetDataType
{
    public function createPostType()
    {
        register_post_type(
            $this->postType,
            $this->buildArgs(
                'Concept Book',
                'Barnet Concept Books',
                array('concept-category')
            )
        );
    }

    public function addExt()
    {
        return array(
            'title' => esc_html__('Concept Type', $this->domain),
            'id' => $this->postType,
            'post_types' => array($this->postType),
            'context' => 'normal',
            'priority' => 'high',
            'clone' => true,
            'fields' => array(
                array(
                    'type' => 'image',
                    'name' => esc_html__('Concept Book Image', $this->domain),
                    'id' => $this->prefix . 'image',
                ),
                array(
                    'type' => 'number',
                    'name' => esc_html__('Concept Book Order', $this->domain),
                    'id' => $this->prefix . 'order',
                ),
                array(
                    'type' => 'radio',
                    'name' => esc_html__('Header Style', $this->domain),
                    'id' => $this->prefix . 'style',
                    'options' => array(
                        'light' => esc_html__('Light', $this->domain),
                        'dark' => esc_html__('Dark', $this->domain),
                    ),
                ),
                array(
                    'type' => 'radio',
                    'name' => esc_html__('Region Type', $this->domain),
                    'id' => $this->prefix . 'area',
                    BarnetProduct::OPTION => BarnetProduct::$AREA_LIST,
                    'std' => 'global'
                ),
            ),
        );
    }

    public function addRelationship()
    {
        $this->addDataRelationship(
            $this->postType,
            'barnet-role',
            'concepts_book_to_roles',
            false,
            'Concept Book Roles',
            'Concepts Book'
        );
    }
}

$barnetConceptBook = new BarnetConceptBook('concept_book_', 'barnet-concept-book', 'barnet-concept');
