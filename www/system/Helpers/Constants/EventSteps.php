<?php
/**
 * Created by PhpStorm.
 * User: Maxim
 * Date: 29 Feb 2016
 * Time: 19:41
 */

namespace Helpers\Constants;

class EventSteps
{
    const NONE                  = "none";
    const PRAY                  = "pray";
    const CONSUME               = "consume";
    const HIGHLIGHT             = "highlight";
    const VERBALIZE             = "verbalize";
    const CHUNKING              = "chunking";
    const READ_CHUNK            = "read-chunk";
    const BLIND_DRAFT           = "blind-draft";
    const MULTI_DRAFT           = "multi-draft";
    const REARRANGE             = "rearrange";
    const SYMBOL_DRAFT          = "symbol-draft";
    const SELF_CHECK            = "self-check";
    const THEO_CHECK            = "theo-check";
    const PEER_REVIEW           = "peer-review";
    const KEYWORD_CHECK         = "keyword-check";
    const CONTENT_REVIEW        = "content-review";
    const FINAL_REVIEW          = "final-review";
    const FINISHED              = "finished";

    private static $enum = [
        "none" => 0,
        "pray" => 1,
        "consume" => 2,
        "verbalize" => 3,
        "chunking" => 4,
        "read-chunk" => 5,
        "blind-draft" => 6,
        "self-check" => 7,
        "peer-review" => 8,
        "keyword-check" => 9,
        "content-review" => 10,
        "final-review" => 11,
        "finished" => 12,
        ];

    private static $enumLangInput = [
        "none" => 0,
        "pray" => 1,
        "multi-draft" => 2,
        "self-check" => 3,
        "finished" => 4,
    ];

    private static $enumSun = [
        "none" => 0,
        "pray" => 1,
        "consume" => 2,
        "chunking" => 3,
        "rearrange" => 4,
        "symbol-draft" => 5,
        "self-check" => 6,
        "theo-check" => 7,
        "content-review" => 8,
        "final-review" => 9,
        "finished" => 10,
    ];

    private static $enumOdbSun = [
        "none" => 0,
        "pray" => 1,
        "consume" => 2,
        "rearrange" => 3,
        "symbol-draft" => 4,
        "self-check" => 5,
        "theo-check" => 6,
        "content-review" => 7,
        "finished" => 8,
    ];

    public static function enum($step, $mode = null, $chk = false)
    {
        switch($mode)
        {
            case "sun":
                return self::$enumSun[$step];

            case "odbsun":
                return self::$enumOdbSun[$step];

            case "li":
                return self::$enumLangInput[$step];

            default:
                return self::$enum[$step];
        }
    }

    public static function enumArray($mode = null, $chk = false)
    {
        switch($mode)
        {
            case "sun":
                return self::$enumSun;

            case "odbsun":
                return self::$enumOdbSun;

            case "li":
                return self::$enumLangInput;

            default:
                return self::$enum;
        }
    }
}