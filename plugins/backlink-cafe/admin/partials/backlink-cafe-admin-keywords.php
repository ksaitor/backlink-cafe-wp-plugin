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
    public static function isContainedInTag($content, $index, $keywordLength)
    {
        $startIndex = max(0, $index - 1);
        return $content[$startIndex] === '>' && $content[$startIndex + $keywordLength + 1] === '<';
    }

    public static function firstExactWordIndex($keyword, $text, $offset = 0)
    {
        $pattern = '/\b' . preg_quote($keyword) . '\b/';

        if (preg_match($pattern, $text, $matches, PREG_OFFSET_CAPTURE, $offset)) {
            return $matches[0][1];
        } else {
            return false;
        }
    }

    public static function fuzzy_keyword_search($content, $keyword)
    {
        $indices = [];
        $startingIndex = 0;
        $keywordLength = strlen($keyword);

        while (($startingIndex = self::firstExactWordIndex($keyword, $content, $startingIndex)) !== false) {
            if (!self::isContainedInTag($content, $startingIndex, $keywordLength)) {
                $endingIndex = $startingIndex + strlen($keyword) - 1;
                $indices[] = array(
                    'startingIndex' => $startingIndex,
                    'endingIndex' => $endingIndex
                );
            }
            $startingIndex = $startingIndex + strlen($keyword); // Move starting index to next position
        }

        return $indices;
    }

}