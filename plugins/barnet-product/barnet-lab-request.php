<?php

class BarnetLabRequest extends BarnetDataType
{
    public function createPostType()
    {
        $args = $this->buildArgs(
            'Lab Request',
            'Lab Requests'
        );
        //$args['capabilities'] = array( 'create_posts' => false );
        //$args['map_meta_cap'] = false;
        register_post_type(
            $this->postType,
            $args
        );
    }

    public function addExt()
    {
    }

    public function addRelationship()
    {
    }

    public function insertData($email, $description)
    {

        $postId = wp_insert_post(array(
            'post_title' => $email . '-' . date('d/m/Y'),
            'post_type' => 'lab-request',
            'comment_status' => 'closed',
            'ping_status' => 'closed',
            'post_content' => $description,
            'post_status' => 'publish',
            'post_author' => 1,
            'menu_order' => 0,
        ));
        if (is_wp_error($postId)) {
            return false;
        }

        return true;
    }
}

$BarnetLabRequest = new BarnetLabRequest('lr_', 'lab-request');
$BarnetLabRequest->addAdminColumn(__('Content'), function ($post_id) {
    $content = get_post_field('post_content', $post_id);
    $content = preg_replace("/[\r\n]/", "</p><p>", $content);
    echo "<p>" . $content . "</p>";
});
