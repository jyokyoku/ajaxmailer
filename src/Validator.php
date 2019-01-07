<?php

namespace Jyokyoku\AjaxMailer;

class Validator
{
    /**
     * @param $value
     * @return bool
     */
    public static function required($value)
    {
        return !empty($value) || ($value !== '' && $value !== [] && $value !== null);
    }

    /**
     * @param $value
     * @return bool
     */
    public static function email($value)
    {
        return filter_var($value, FILTER_VALIDATE_EMAIL) !== false && ']' !== substr($value, -1);
    }

    /**
     * @param $value
     * @return bool
     */
    public static function url($value)
    {
        return filter_var($value, FILTER_VALIDATE_URL) !== false && preg_match('@^https?+://@i', $value);
    }

    /**
     * @param $value
     * @param $regex
     * @return bool
     */
    public static function regex($value, $regex)
    {
        return @preg_match($regex, $value) === 1;
    }

    /**
     * @param $value
     * @param $threshold
     * @return bool
     */
    public static function minlength($value, $threshold)
    {
        return mb_strlen($value) >= $threshold;
    }

    /**
     * @param $value
     * @param $threshold
     * @return bool
     */
    public static function maxlength($value, $threshold)
    {
        return mb_strlen($value) <= $threshold;
    }

    /**
     * @param $value
     * @param array $list
     * @return bool
     */
    public static function contain($value, array $list)
    {
        return in_array($value, $list);
    }
}