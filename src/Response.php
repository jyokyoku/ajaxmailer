<?php

namespace Jyokyoku\AjaxMailer;

class Response
{
    /**
     * @param array $data
     */
    public static function json(array $data = [])
    {
        header("Content-Type: application/json; charset=utf-8");

        $response = json_encode($data);

        if (json_last_error() == JSON_ERROR_NONE) {
            echo $response;
        } else {
            http_response_code(500);
        }

        exit;
    }

    /**
     * @param null $data
     */
    public static function jsonSuccess($data = null)
    {
        static::json([
            'type' => 'success',
            'data' => $data
        ]);
    }

    /**
     * @param null $data
     */
    public static function jsonError($code, $data = null)
    {
        static::json([
            'type' => 'error',
            'code' => $code,
            'data' => $data
        ]);
    }
}