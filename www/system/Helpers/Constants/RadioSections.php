<?php
/**
 * Created by PhpStorm.
 * User: Maxim
 * Date: 29 Feb 2016
 * Time: 20:33
 */

namespace Helpers\Constants;


class RadioSections
{
    const TITLE             = 1;
    const CONTENT           = 2;

    private static $enum = [
        self::TITLE => "title",
        self::CONTENT => "content"
    ];

    public static function enum($section)
    {
        if($section > 2) return self::$enum[self::CONTENT];

        return self::$enum[$section];
    }
}