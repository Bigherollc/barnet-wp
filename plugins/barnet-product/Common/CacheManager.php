<?php

class CacheManager extends \WP_Rest_Cache_Plugin\Includes\Caching\Caching
{
    protected $_instance;
    protected $db_table_caches;
    protected $db_table_relations;
    protected $barnetAuth;

    public function __construct()
    {
        global $wpdb;

        $this->db_table_caches = $wpdb->prefix . self::TABLE_CACHES;
        $this->db_table_relations = $wpdb->prefix . self::TABLE_RELATIONS;
        $this->barnetAuth = new \JWTAuth\BarnetAuth();
    }

    public function flushUser($userId, $delete = false)
    {
        global $wpdb;

        $dbTableCache = $wpdb->prefix . self::TABLE_CACHES;

        if ($delete) {
            $setClause = '`expiration` = \'' . date_i18n('Y-m-d H:i:s', 1) . '\', `deleted` = 1';
        } else {
            $setClause = '`expiration` = \'' . date_i18n('Y-m-d H:i:s', 1) . '\'';
        }

        $sql = "SELECT cache_id, request_headers FROM $dbTableCache WHERE `expiration` > utc_timestamp()";
        $caches = $wpdb->get_results($sql, ARRAY_A);

        $cacheIds = array();
        foreach ($caches as $cache) {
            $requestHeaders = json_decode($cache['request_headers'], true);
            if (!isset($requestHeaders['Authorization'])) {
                continue;
            }

            list($token) = sscanf($requestHeaders['Authorization'], 'Bearer %s');
            $validateAuthToken = $this->barnetAuth->validate_auth_token($token);

            if (!isset($validateAuthToken->data->user->id)) {
                continue;
            }

            $tokenUserId = $validateAuthToken->data->user->id;

            if ($tokenUserId != $userId) {
                continue;
            }

            $cacheIds[] = $cache['cache_id'];
        }

        $cacheIds = implode(",", $cacheIds);

        $sql = "UPDATE $dbTableCache SET $setClause WHERE cache_id IN ($cacheIds)";
        $wpdb->query($sql);
    }
}