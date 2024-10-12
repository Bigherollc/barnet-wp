<?php

$args = array(
    'type'         => 'post',
    'child_of'     => 0,
    'parent'       => '',
    'orderby'      => 'name',
    'order'        => 'ASC',
    'hide_empty'   => 1,
    'hierarchical' => 1,
    'exclude'      => '',
    'include'      => '',
    'number'       => '',
    'taxonomy'     => 'category',
    'pad_counts'   => false,
);

$categories = get_categories($args);
$categories = get_categories($args);

foreach ($categories as $category) {
    echo '<a href="' . get_category_link($category->term_id) . '">' . $category->name . '</a> ';
}