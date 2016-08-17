<?php
/**
 * Created by PhpStorm.
 * User: mXaln
 * Date: 20.06.2016
 * Time: 17:41
 */

namespace Helpers;


class UsfmParser
{

    public static function parse($usfm)
    {
        $result = array("chapters" => array());

        $lines = preg_split("/\n/", $usfm);
        $sectionStarted = false;
        $lastChapter = 0;

        foreach ($lines as $line) {
            if(!isset($result["id"]))
            {
                preg_match("/^\\\\id\s(.*)/", $line, $matches);
                if(!empty($matches))
                    $result["id"] = $matches[1];
            }

            if(!isset($result["ide"]))
            {
                preg_match("/\\\\ide\s(.*)/", $line, $matches);
                if(!empty($matches))
                    $result["ide"] = $matches[1];
            }

            if(!isset($result["h"]))
            {
                preg_match("/\\\\h\s(.*)/", $line, $matches);
                if(!empty($matches))
                    $result["h"] = $matches[1];
            }

            if(!isset($result["toc1"]))
            {
                preg_match("/^\\\\toc1\s(.*)/", $line, $matches);
                if(!empty($matches))
                    $result["toc1"] = $matches[1];
            }

            if(!isset($result["toc2"]))
            {
                preg_match("/\\\\toc2\s(.*)/", $line, $matches);
                if(!empty($matches))
                    $result["toc2"] = $matches[1];
            }

            if(!isset($result["toc3"]))
            {
                preg_match("/\\\\toc3\s(.*)/", $line, $matches);
                if(!empty($matches))
                    $result["toc3"] = $matches[1];
            }

            if(!isset($result["mt"]))
            {
                preg_match("/\\\\mt\s(.*)/", $line, $matches);
                if(!empty($matches))
                    $result["mt"] = $matches[1];
            }

            // Start Section
            if(!$sectionStarted && preg_match("/\\\\s5/", $line))
            {
                $sectionStarted = true;
            }

            // Start chapter
            if(preg_match("/\\\\c\s([0-9]+)/", $line, $matches))
            {
                if(!empty($matches) && !isset($result["chapters"][$matches[1]]))
                {
                    $result["chapters"][$matches[1]] = array();
                    $lastChapter = $matches[1];
                }
            }

            // Push verse to section
            if(preg_match("/\\\\v\s([0-9]+)\s(.*)/", $line, $matches))
            {
                // Push section to chapter
                if($sectionStarted && $lastChapter > 0)
                {
                    $result["chapters"][$lastChapter][] = array();
                    $sectionStarted = false;
                }

                // Italic style
                $verse = preg_replace("/\\\\it ([\p{L}\p{N}]*)\\\\it\\*/u", "<em>$1</em>", $matches[2]);

                $result["chapters"][$lastChapter][sizeof($result["chapters"][$lastChapter])-1][$matches[1]] = $verse;
            }
        }

        if(sizeof($result["chapters"]) <= 0)
            $result = array();

        return $result;
    }
}