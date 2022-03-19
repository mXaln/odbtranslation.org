<?php

namespace App\Domain;

class MarkdownUtils
{
    public static function isImageTag($text): bool
    {
        return preg_match("/^!\[.*\]\(.*\)$/", $text) != false;
    }

    public static function convertToMd($text, $original) {
        if (self::isImageTag($original)) return $original;

        $text = self::cleanTag($text);
        $tag = self::extractTag($original);

        return $tag . $text;
    }

    public static function extractTag($text) {
        $found = preg_match("/^([#>-]+\s?)|(\d+.\s?)/", $text, $matches);
        if (!$found) return null;

        return $matches[0];
    }

    public static function cleanTag($text) {
        return preg_replace("/^([#>-]+\s?)|(\d+.\s?)/", "", $text);
    }
}