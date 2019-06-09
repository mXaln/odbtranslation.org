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

    private static $enumNotes = [
            "none" => 0,
            "pray" => 1,
            "consume" => 2,
            "read-chunk" => 3,
            "blind-draft" => 4,
            "self-check" => 5,
            "finished" => 6,
            ];

    private static $enumNotesChk = [
        "none" => 0,
        "pray" => 1,
        "consume" => 2,
        "highlight" => 3,
        "self-check" => 4,
        "keyword-check" => 5,
        "peer-review" => 6,
        "finished" => 7,
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

    private static $enumQuestions = [
        "none" => 0,
        "pray" => 1,
        "multi-draft" => 2,
        "self-check" => 3,
        "finished" => 4,
    ];

    private static $enumQuestionsChk = [
        "none" => 0,
        "pray" => 1,
        "keyword-check" => 2,
        "peer-review" => 3,
        "finished" => 4,
    ];

    private static $enumWords = [
        "none" => 0,
        "pray" => 1,
        "multi-draft" => 2,
        "self-check" => 3,
        "finished" => 4,
    ];

    private static $enumWordsChk = [
        "none" => 0,
        "pray" => 1,
        "keyword-check" => 2,
        "peer-review" => 3,
        "finished" => 4,
    ];

    public static function enum($step, $mode = null, $chk = false)
    {
        switch($mode)
        {
            case "tn":
                if($chk)
                    return self::$enumNotesChk[$step];
                else
                    return self::$enumNotes[$step];
                break;

            case "sun":
                return self::$enumSun[$step];
                break;

            case "tq":
                if($chk)
                    return self::$enumQuestionsChk[$step];
                else
                    return self::$enumQuestions[$step];
                break;

            case "tw":
                if($chk)
                    return self::$enumWordsChk[$step];
                else
                    return self::$enumWords[$step];
                break;

            case "li":
                return self::$enumLangInput[$step];
                break;

            default:
                return self::$enum[$step];
                break;
        }
    }

    public static function enumArray($mode = null, $chk = false)
    {
        switch($mode)
        {
            case "tn":
                if($chk)
                    return self::$enumNotesChk;
                else
                    return self::$enumNotes;
                break;

            case "sun":
                return self::$enumSun;
                break;

            case "tq":
                if($chk)
                    return self::$enumQuestionsChk;
                else
                    return self::$enumQuestions;
                break;

            case "tw":
                if($chk)
                    return self::$enumWordsChk;
                else
                    return self::$enumWords;
                break;

            case "li":
                return self::$enumLangInput;
                break;

            default:
                return self::$enum;
                break;
        }
    }
}