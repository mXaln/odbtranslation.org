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
        $result = array();
        $chapters = explode('\c ', $usfm);

        $headings = preg_split("/\\r\\n|\\n|\\r/", $chapters[0]);

        foreach ($headings as $heading) {
            if(trim($heading) == "") continue;

            // Id tag
            preg_match("/^\\\\id (.*)/", $heading, $matches);
            if(!empty($matches))
                $result["id"] = $matches[1];

            // Ide tag
            preg_match("/^\\\\ide (.*)/", $heading, $matches);
            if(!empty($matches))
                $result["ide"] = $matches[1];

            // H tag
            preg_match("/^\\\\h (.*)/", $heading, $matches);
            if(!empty($matches))
                $result["h"] = $matches[1];

            // Toc1 tag
            preg_match("/^\\\\toc1 (.*)/", $heading, $matches);
            if(!empty($matches))
                $result["toc1"] = $matches[1];

            // Toc2 tag
            preg_match("/^\\\\toc2 (.*)/", $heading, $matches);
            if(!empty($matches))
                $result["toc2"] = $matches[1];

            // Toc3 tag
            preg_match("/^\\\\toc3 (.*)/", $heading, $matches);
            if(!empty($matches))
                $result["toc3"] = $matches[1];

            // Mt tag
            preg_match("/^\\\\(mt[0-9]*) (.*)/", $heading, $matches);
            if(!empty($matches))
                $result[$matches[1]] = $matches[2];

            // Ms tag
            preg_match("/^\\\\ms (.*)/", $heading, $matches);
            if(!empty($matches))
                $result["ms"] = $matches[1];

            // Cl tag
            preg_match("/^\\\\cl (.*)/", $heading, $matches);
            if(!empty($matches))
                $result["cl"] = $matches[1];
        }

        $result["chapters"] = array();
        foreach ($chapters as $chapter => $chapData) {
            if($chapter == 0) continue;

            $chunks = explode('\s', $chapData);

            foreach ($chunks as $chunk => $chunkData) {
                //$result["chapters"][$chapter][$chunk] = array();

                $verses = explode('\v ', $chunkData);

                foreach ($verses as $verse => $verseData) {
                    if($verse == 0) continue;

                    $vData = preg_replace("/\\n|\\r\\n|\\n/", " ", trim($verseData));
                    $vData = explode(' ', $vData);
                    //$vData = preg_split("/\s/", $vData);

                    $vNum = $vData[0];
                    unset($vData[0]);

                    $vText = join(" ", $vData);

                    // Word listing (remove - beginning)
                    $vText = preg_replace("/\s?\|strong=\"[A-Z0-9]+\"\s?/Uui", "", $vText);
                    $vText = preg_replace("/\s?x-morph=\"strongMorph:[A-Z0-9]+\"\s?/Uui", "", $vText);

                    $vText = htmlspecialchars($vText, ENT_COMPAT | ENT_SUBSTITUTE, "UTF-8");

                    // Italic style
                    $vText = preg_replace("/\\\\it (.*)\\\\it\\*/U", "<em>$1</em>", $vText);

                    // Word listing (remove - continue)
                    $vText = preg_replace("/\\\\w\s?(.*)\s?\\\\w\\*/U", "$1", $vText);

                    // Footnotes
                    $vText = preg_replace("/\\\\fqa\s?(.*)\s?\\\\fqa\\*/U", "$1", $vText);
                    $replacement = "<span data-toggle=\"tooltip\" data-placement=\"auto right\" title=\"$1\" class=\"booknote glyphicon glyphicon-file\"></span>";
                    $vText = preg_replace("/\\\\f\s?\\+\s?\\\\ft\s?(.*)\s?\\\\f\\*/uU", $replacement, $vText);

                    // Remove all other usfm tags
                    // TODO Parse other usfm tags
                    $vText = preg_replace("/\\\\[a-z0-9]+ ?/", "", $vText);

                    $result["chapters"][$chapter][$chunk][$vNum] = $vText;
                }
            }
        }

        return $result;
    }
}