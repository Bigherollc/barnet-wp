<?php

$routerConfig = $yamlHelper->load(__DIR__ . '/Config/routes.yml');
$routers = $routerConfig['Routes'] ?? array();
$whiteList = $routerConfig['Whitelist'] ?? array();
$cacheList = $routerConfig['Cachelist'] ?? array();

foreach ($routers as $rest => $routerList) {
    if (!class_exists($rest)) {
        continue;
    }
	
    $routeManager = new BarnetRoutesManager(new $rest());
    foreach ($routerList as $router) {
        $params = $router['params'] ?? null;
		
        $routeManager->addRoute($router['route'], $router['method'], $router['callback'], $params);
    }
}

BarnetRoutesManager::addAuthWhiteList($whiteList);
BarnetRoutesManager::addCacheList($cacheList);
