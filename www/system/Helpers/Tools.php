<?php
/**
 * Created by PhpStorm.
 * User: mXaln
 * Date: 20.06.2016
 * Time: 17:41
 */

namespace Helpers;


class Tools
{

    /** Parses combined verses (ex. 4-5, 1-4) into array of verses
     * @param string $verse
     * @return array
     */
    public static function parseCombinedVerses($verse)
    {
        $versesArr = array();
        $verses = explode("-", $verse);

        if(sizeof($verses) < 2)
        {
            $versesArr[] = $verse;
            return $versesArr;
        }

        $fv = $verses[0];
        $lv = $verses[1];

        for($i=$fv; $i <= $lv; $i++)
        {
            $versesArr[] = $i;
        }

    return $versesArr;
    }
}