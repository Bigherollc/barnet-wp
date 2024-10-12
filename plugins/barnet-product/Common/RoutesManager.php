<?php

class BarnetRoutesManager
{
    protected $restAPI;
    protected $namespace = 'barnet/v1';
    protected $auth;
    protected $whiteList;
    protected $route;

    public function __construct($restAPI,  $namespace = null)
    {
        $this->restAPI = $restAPI;

        $this->auth = new \JWTAuth\BarnetAuth();
       // $this->whiteList = $m_whiteList;
        if (isset($namespace)) {
            $this->namespace = $namespace;
        } elseif (defined('BARNET_API_NAMESPACE')) {
            $this->namespace = BARNET_API_NAMESPACE;
        }
    }

    public function addRoute($route, $method, $callback, $params = null)
    {
        
        add_action('rest_api_init', function () use ($route, $method, $callback, $params) {
            $args = array(
                'methods' => $method,
                'callback' => array($this->restAPI, $callback),
                'permission_callback' => function () use ($route, $method, $callback, $params) {
                    global $whiteList ;
                    $cur_route='/wp-json/barnet/v1/'.$route;
                    
                    $whiteflag=false;
                    foreach($whiteList as $w_item){
                      
                        //if($w_item&&!( is_array($w_item)))
                      		$w_item= trim($w_item);
                        $whiteflag=strcmp($cur_route,$w_item);
                        if($whiteflag==0)return $whiteflag;
                        $lastchar=substr($w_item,-1,1);
                        if($lastchar=='*'){
                            $restString=substr($w_item,0,-1);
                            if(str_contains($cur_route, $restString))return true;
                        }
                    }
                    
                    $validateAuthToken = $this->auth->validate_token();
                    
                    $returnFlag=false;
                    if($validateAuthToken->data['success']){
                        $returnFlag= $validateAuthToken->data['success'];
                    }else{
                        $returnFlag= new WP_Error(
                            'rest_forbidden',
                            $validateAuthToken->data['message'],
                            array( $validateAuthToken->data )
                        );
                    }
                    return $returnFlag;
                  }
            );

            if (is_array($params)) {
                $_args = array();
                foreach ($params as $key => $param) {
					
                    $_args[$key] = array('validate_callback' => function ($param){return $param;});
                }
                $args['args'] = $_args;
            }
			/*
			if (str_contains($route, 'proxy_attachment')){
				
			};
			*/
			
             register_rest_route($this->namespace, $route, $args);
        });

        return $this;
    }

    public static function addAuthWhiteList($whiteList)
    {
        add_filter('jwt_auth_whitelist', function ($endpoints) use ($whiteList) {
            return $whiteList;
        });
    }

    public static function addCacheList($cacheList)
    {
        add_filter('wp_rest_cache/allowed_endpoints', function ($allowedEndpoints) use ($cacheList) {
            foreach ($cacheList as $cacheEndPoint) {
                if (!isset($allowedEndpoints[BARNET_API_NAMESPACE]) ||
                    !in_array($cacheEndPoint, $allowedEndpoints[BARNET_API_NAMESPACE])) {
                    $allowedEndpoints[BARNET_API_NAMESPACE][] = $cacheEndPoint;
                }
            }
            return $allowedEndpoints;
        }, 10, 1);

        add_filter('wp_rest_cache/cacheable_request_headers', function ($cacheableHeaders) use ($cacheList) {
            foreach ($cacheList as $cacheEndPoint) {
                $cacheableHeaders[BARNET_API_NAMESPACE . "/$cacheEndPoint"] = 'Authorization';
            }

            return $cacheableHeaders;
        }, 10, 1);
    }
}
