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
    const VERBALIZE             = "verbalize";
    const CHUNKING              = "chunking";
    const READ_CHUNK            = "read-chunk";
    const BLIND_DRAFT           = "blind-draft";
    const SELF_CHECK            = "self-check";
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

    private static $enumNotes = [
            "none" => 0,
            "pray" => 1,
            "consume" => 2,
            "read-chunk" => 3,
            "blind-draft" => 4,
            "self-check" => 5,
            "peer-review" => 6,
            "finished" => 7,
            ];

    public static function enum($step, $mode = null)
    {
        switch($mode)
        {
            case "tn":
                return self::$enumNotes[$step];
                break;

            default:
                return self::$enum[$step];
                break;
        }
    }

    public static function enumArray($mode = null)
    {
        switch($mode)
        {
            case "tn":
                return self::$enumNotes;
                break;

            default:
                return self::$enum;
                break;
        }
    }
}