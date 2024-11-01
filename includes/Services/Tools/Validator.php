<?php

namespace Smartcat\Includes\Services\Tools;

class Validator
{
    public static function parseList($list)
    {
        if (!is_array($list)) {
            return preg_split('/[\s,]+/', $list, -1, PREG_SPLIT_NO_EMPTY);
        }
        return $list;
    }

    public static function restIsInteger($maybeInteger): bool
    {
        return is_numeric($maybeInteger) && round((float)$maybeInteger) === (float)$maybeInteger;
    }

    public static function restIsArray($maybeArray): bool
    {
        if (is_scalar($maybeArray)) {
            $maybeArray = wp_parse_list($maybeArray);
        }

        return wp_is_numeric_array($maybeArray);
    }

}