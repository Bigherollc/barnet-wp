<?php

class BarnetPostMetaManager
{
    protected $posts;
    protected $metaData;

    public function __construct($posts = null)
    {
        if (isset($posts)) {
            $this->posts = $posts;
            $this->metaData = $this->bindMetaData();
        }
    }

    public function bindMetaData()
    {
        if (count($this->posts) == 0) {
            return array();
        }

        global $wpdb;

        $postIdList = array_map(function ($e) {
            if ($e instanceof WP_Post) {
                return $e->ID;
            }

            if (is_array($e) && isset($e['ID'])) {
                return $e['ID'];
            }
        }, $this->posts);

        $postIdQuery = implode(",", $postIdList);
        $query = "SELECT `post_id`, `meta_key`, `meta_value` FROM $wpdb->postmeta WHERE `post_id` IN ($postIdQuery)";
        $sqlResult = $wpdb->get_results($query, ARRAY_A);
        $result = array();
        foreach ($sqlResult as $row) {
            $postId = $row['post_id'];
            $metaKey = $row['meta_key'];
            $metaValue = $row['meta_value'];

            if (isset($result[$postId][$metaKey]) && !is_array($result[$postId][$metaKey])) {
                $result[$postId][$metaKey] = array($result[$postId][$metaKey]);
                $result[$postId][$metaKey][] = $metaValue;
            } elseif (isset($result[$postId][$metaKey]) && is_array($result[$postId][$metaKey])) {
                $result[$postId][$metaKey][] = $metaValue;
            } else {
                $result[$postId][$metaKey] = $metaValue;
            }
        }

        return $result;
    }

    public function getMetaData($postId, $key = null)
    {
        if (isset($this->metaData[$postId])) {
            if (isset($key)) {
                return $this->metaData[$postId][$key] ?? null;
            }

            return $this->metaData[$postId];
        }

        return null;
    }

    /**
     * @return mixed
     */
    public function getPosts()
    {
        return $this->posts;
    }

    /**
     * @param mixed $post
     * @return $this
     */
    public function addPost($post)
    {
        $this->posts[] = $post;
        return $this;
    }

    /**
     * @return $this
     */
    public function clearPosts()
    {
        $this->posts = array();
        return $this;
    }

    /**
     * @param mixed $posts
     * @return $this
     */
    public function setPosts($posts)
    {
        $this->posts = $posts;
        return $this;
    }
}