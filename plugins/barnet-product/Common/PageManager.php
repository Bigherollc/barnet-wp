<?php

class BarnetPageManager
{
    public function addNewPage($title, $path, $template, $content ='')
    {
        $result = get_page_by_path($path);
        if (isset($result)) {
            return $this;
        }

        wp_insert_post(array(
            'post_title' => $title,
            'post_type' => 'page',
            'post_name' => $path,
            'comment_status' => 'closed',
            'ping_status' => 'closed',
            'post_content' => $content,
            'post_status' => 'publish',
            'post_author' => 1,
            'menu_order' => 0,
            'page_template' => "templates/{$template}.php"
        ));

        return $this;
    }

    public function addNewRole($title, $path)
    {
        $result = get_page_by_path($path, OBJECT, 'barnet-role');
        if (isset($result)) {
            return $this;
        }

        wp_insert_post(array(
            'post_title' => $title,
            'post_type' => 'barnet-role',
            'post_name' => $path,
            'comment_status' => 'closed',
            'ping_status' => 'closed',
            'post_content' => '',
            'post_status' => 'publish',
            'post_author' => 1,
            'menu_order' => 0
        ));

        return $this;
    }
}

$barnetPageManager = new BarnetPageManager();