<?php
/**
 *
 * This software is distributed under the GNU GPL v3.0 license.
 * @author Gemorroj
 * @copyright 2008-2011 http://wapinet.ru
 * @license http://www.gnu.org/licenses/gpl-3.0.txt
 * @link http://wapinet.ru/gmanager/
 * @version 0.8 beta
 *
 * PHP version >= 5.2.1
 *
 */


class Helper_Editor
{
    /**
     * encoding
     *
     * @param string $text
     * @param string $charset
     * @return array
     */
    public static function encoding ($text = '', $charset)
    {
        $ch = explode(' -> ', $charset);
        if ($text) {
            $text = mb_convert_encoding($text, $ch[1], $ch[0]);
        }
        return array(0 => $ch[0], 1 => $ch[1], 'text' => $text);
    }
}

?>
