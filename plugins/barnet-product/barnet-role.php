<?php

class BarnetRole extends BarnetDataType
{
    public function createPostType()
    {
        $args = $this->buildArgs(
            'Barnet Role',
            'Barnet Roles'
        );
        $args['show_in_menu'] = Barnet::BARNET_MENU_APP_ADMIN;
        register_post_type(
            $this->postType,
            $args
        );
    }

    public function addExt()
    {
        return array(
            'title' => esc_html__('Role Type', $this->domain),
            'id' => $this->postType,
            'post_types' => array($this->postType),
            'context' => 'normal',
            'priority' => 'high',
            'clone' => true,
            'fields' => array(
                array(
                    'type' => 'WYSIWYG',
                    'name' => esc_html__('Role Description', $this->domain),
                    'id' => $this->prefix . 'description',
                    'desc' => esc_html__('Role Description', $this->domain),
                    'clone' => true,
                ),
            ),
        );
    }
}

$barnetRole = new BarnetRole('role_', 'barnet-role');
