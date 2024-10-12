<?php

class BarnetDB
{
    public static function sql($query)
    {
        global $wpdb;

        return $wpdb->get_results($query, ARRAY_A);
    }
}