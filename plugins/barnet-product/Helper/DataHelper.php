<?php

final class DataHelper
{
    /**
     * Change snake_case to camelCase
     * Example:
     * - barnet_product_description => barnetProductDescription
     * - barnet-product_description => barnetProductDescription
     *
     * @param $s
     * @param $lcfirst
     * @return string
     */
    public static function snake2CamelCase($s, $lcfirst = true)
    {
        $result = str_replace(array('-', '_'), "", ucwords($s, "-_"));
        return $lcfirst ? lcfirst($result) : $result;
    }

    public static function camel2SnakeCase($s)
    {
        preg_match_all('!([A-Z][A-Z0-9]*(?=$|[A-Z][a-z0-9])|[A-Za-z][a-z0-9]+)!', $s, $matches);
        $ret = $matches[0];
        foreach ($ret as &$match) {
            $match = $match == strtoupper($match) ? strtolower($match) : lcfirst($match);
        }

        return implode('_', $ret);
    }

    public static function camel2Display($s)
    {
        $snake = static::camel2SnakeCase($s);
        return implode(' ', array_map(function ($e) {
            return ucfirst($e);
        }, explode('_', $snake)));
    }

    public static function randomString($length = 7)
    {

        $chars = "abcdefghijkmnopqrstuvwxyz023456789";
        srand((double)microtime() * 1000000);
        $i = 0;
        $result = '';

        while ($i <= $length) {
            $num = rand() % 33;
            $tmp = substr($chars, $num, 1);
            $result = $result . $tmp;
            $i++;
        }

        return $result;
    }

    public static function removeChar($str, $handle)
    {
        return str_replace($handle, '', $str);
    }

    public static function removeDuplicateWhiteSpace($str)
    {
        return preg_replace('/\s+/', ' ', $str);
    }

    public static function compactString($str, $delimiter = '-')
    {
        return implode('', array_map(function($e) {
            return $e[0];
        }, explode($delimiter, $str)));
    }
}