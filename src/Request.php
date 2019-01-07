<?php

namespace Jyokyoku\AjaxMailer;

class Request
{
    /**
     * @return bool
     */
    public static function isAjax()
    {
        return isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';
    }

    /**
     * @param null $key
     * @param null $default
     * @return array|mixed|null
     */
    public static function post($key = null, $default = null)
    {
        if (!empty($key)) {
            return isset($_POST[$key]) ? $_POST[$key] : $default;
        }

        return $_POST;
    }

    /**
     * @param null $key
     * @param null $default
     * @return array|mixed|null
     */
    public static function get($key = null, $default = null)
    {
        if (!empty($key)) {
            return isset($_GET[$key]) ? $_GET[$key] : $default;
        }

        return $_GET;
    }
}