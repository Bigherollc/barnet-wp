<?php

class BarnetSessionCacheManager
{
    const POST_LAST_UPDATE_OPTION = '_barnet_post_las_update_option';
    const TERM_LAST_UPDATE_OPTION = '_barnet_term_las_update_option';
    const SESSION_EXPIRATION = 'expiration';
    const SESSION_EXPIRATION_TIME = 1800;

    const SESSION_API_DATA_SEARCH = 'data_search';
    const SESSION_API_TAXONOMIES = 'taxonomies';
    const SESSION_API_DATA = 'data';
	const SESSION_API_DATA_FOR_SEARCH = 'data_for_search';
    const SESSION_API_CONCEPT = 'barnet_concept';
    const SESSION_API_PRODUCT = 'barnet_product';
    const SESSION_API_FORLUMA = 'barnet_formula';
    const SESSION_API_RESOURCES = 'barnet_resources';


    public function __construct()
    {
        $this->init();
    }

    public function init()
    {
        if (!session_id()) {
            session_start();
        }
    }

    public function getSessionPrefix()
    {
        return 'barnet_session';
    }

    protected function getAjaxUrl()
    {
        return $_SERVER['REQUEST_URI'];
    }

    protected function getUserPrefix()
    {
        return get_current_user_id() ? get_current_user_id() : 'unlogin';
    }

    public function checkExpiration($data_session_time)
    {
        if (time() < $data_session_time) {
            return true;
        }

        return false;
    }

    public function setSessionData($var, $data = [])
    {
        $_SESSION[$this->getSessionPrefix()][$this->getUserPrefix()][$var][] = $this->dataObject($data);

        return $this;
    }

    protected function dataObject($data)
    {
        return [
            'value' => $data,
            'url' => $this->getAjaxUrl(),
            'expiration' => time() + $this::SESSION_EXPIRATION_TIME
        ];
    }

    public function getSessionData($var) {
        $urlCurrent = $this->getAjaxUrl();
        $sesionData = [];

        if (!isset($_SESSION[$this->getSessionPrefix()][$this->getUserPrefix()][$var]) || count($_SESSION[$this->getSessionPrefix()][$this->getUserPrefix()][$var]) == 0) {
            return null;
        }

        foreach ($_SESSION[$this->getSessionPrefix()][$this->getUserPrefix()][$var] as $data) {
            if ($data['url'] != $urlCurrent) {
                continue;
            }
            
            if ($this->checkExpiration($data['expiration'])) {
                $sesionData = $data['value'];
                break;
            }
        }

        return $sesionData;
    }

    public function clearSessionData($var)
    {
        if (isset($_SESSION[$this->getSessionPrefix()][$this->getUserPrefix()][$var])) {
            unset($_SESSION[$this->getSessionPrefix()][$this->getUserPrefix()][$var]);
        }
    }

    public function clearAllSessionData()
    {
        if (isset($_SESSION[$this->getSessionPrefix()])) {
            unset($_SESSION[$this->getSessionPrefix()]);
        }
    }
}