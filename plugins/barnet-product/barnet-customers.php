<?php

class BarnetCustomer extends BarnetDataType
{
    public function createPostType()
    {
        register_post_type(
            $this->postType,
            $this->buildArgs(
                'Customers',
                'Barnet Customers'
            )
        );
    }

    public function addExt()
    {
        return array(
            'title' => esc_html__('Customer Type', $this->domain),
            'id' => $this->postType,
            'post_types' => array($this->postType),
            'context' => 'normal',
            'priority' => 'high',
            'clone' => true,
            'fields' => array(
                array(
                    'type' => 'select_advanced',
                    'name' => esc_html__('Roles', $this->domain),
                    'id' => $this->prefix . 'roles',
                    'options' => $this->getRoleList(),
                    'multiple' => true,
                ),
            ),
        );
    }

    public function addRelationship()
    {

    }
}

$barnetCustomer = new BarnetCustomer('customer_', 'barnet-customer', 'barnet-digital-code');
