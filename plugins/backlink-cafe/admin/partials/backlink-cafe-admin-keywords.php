<?php

/**
 * Keyword finder
 *
 * @link  https://backlink.cafe
 * @since 1.0.0
 *
 * @package    Backlink_Cafe
 * @subpackage Backlink_Cafe/admin
 */

/**
 * Keyword finder
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @package    Backlink_Cafe
 * @subpackage Backlink_Cafe/admin
 * @author     Backlink Cafe <hi@backlink.cafe>
 */
class Backlink_Cafe_Admin_Keywords
{
    public static function fuzzy_keyword_search($inputString, $searchString)
    {
        $positions = array();
        $pattern = '/<!--[^>]*-->|\b\w+\b/i';

        preg_match_all($pattern, $inputString, $matches, PREG_OFFSET_CAPTURE);

        foreach ($matches[0] as $match) {
            $word = $match[0];
            $startingIndex = $match[1];
            $endingIndex = $startingIndex + strlen($word) - 1;

            if (levenshtein(strtolower($searchString), strtolower($word)) <= 2) {
                $positions[] = array(
                    "startingIndex" => $startingIndex + 1,
                    "endingIndex" => $endingIndex + 2
                );
            }
        }

        return $positions;
    }

}