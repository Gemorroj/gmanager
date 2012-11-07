<?php
/**
 * 
 * This software is distributed under the GNU GPL v3.0 license.
 * @author Gemorroj
 * @copyright 2008-2012 http://wapinet.ru
 * @license http://www.gnu.org/licenses/gpl-3.0.txt
 * @link http://wapinet.ru/gmanager/
 * @version 0.8.1 beta
 * 
 * PHP version >= 5.2.3
 * 
 */


class Paginator
{
    /**
     * Get the Paginator
     * 
     * @param int    $pg
     * @param int    $all
     * @param string $text
     * @return string
     */
    public static function get ($pg = 0, $all = 0, $text = '')
    {
        $go = '';

        $page1 = $pg - 2;
        $page2 = $pg - 1;
        $page3 = $pg + 1;
        $page4 = $pg + 2;

        if ($page1 > 0) {
            $go .= '<a href="' . $_SERVER['PHP_SELF'] . '?pg=' . $page1 . $text . '">' . $page1 . '</a> ';
        }

        if ($page2 > 0) {
            $go .= '<a href="' . $_SERVER['PHP_SELF'] . '?pg=' . $page2 . $text . '">' . $page2 . '</a> ';
        }

        $go .= $pg . ' ';

        if ($page3 <= $all) {
            $go .= '<a href="' . $_SERVER['PHP_SELF'] . '?pg=' . $page3 . $text . '">' . $page3 . '</a> ';
        }
        if ($page4 <= $all) {
            $go .= '<a href="' . $_SERVER['PHP_SELF'] . '?pg=' . $page4 . $text . '">' . $page4 . '</a> ';
        }

        if ($all > 3 && $all > $page4) {
            $go .= '... <a href="' . $_SERVER['PHP_SELF'] . '?pg=' . $all . $text . '">' . $all . '</a>';
        }

        if ($page1 > 1) {
            $go = '<a href="' . $_SERVER['PHP_SELF'] . '?pg=1' . $text . '">1</a> ... ' . $go;
        }

        if ($go != $pg . ' ') {
            return '<tr><td class="border" colspan="' . (array_sum(Config::getSection('Display')) + 1) . '">&#160;' . $go . '</td></tr>';
        } else {
            return '';
        }
    }
}
