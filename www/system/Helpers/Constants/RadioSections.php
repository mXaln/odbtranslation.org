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
    const SPEAKERS          = 2;

    private static $enum = [
        self::TITLE => "title",
        self::SPEAKERS => "speakers"
    ];

    public static function enum($section)
    {
        if($section > 2) return self::$enum[self::SPEAKERS];

        return self::$enum[$section];
    }
}