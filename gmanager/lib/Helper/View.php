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


class Helper_View
{
    const MESSAGE_SUCCESS       = 0;
    const MESSAGE_ERROR         = 1;
    const MESSAGE_ERROR_EMAIL   = 2;

    /**
     * getRawurl
     *
     * @param string $str
     * @return string
     */
    public static function getRawurl ($str)
    {
        return str_replace('%2F', '/', rawurlencode($str));
    }


    /**
     * Get rows for textarea
     *
     * @param string $str
     * @return int
     */
    public static function getRows ($str)
    {
        $rows = sizeof(explode("\n", $str)) + 1;
        if ($rows < 3) {
            $rows = 3;
        }
        return $rows;
    }


    /**
     * formatSize
     *
     * @param int|bool   $size
     * @param int        $int
     * @return string
     */
    public static function formatSize ($size = false, $int = 2)
    {
        if ($size === false) {
            return Language::get('unknown');
        } elseif ($size < 1024) {
            return $size . ' Byte';
        } elseif ($size < 1048576) {
            return round($size / 1024, $int) . ' Kb';
        } elseif ($size < 1073741824) {
            return round($size / 1048576, $int) . ' Mb';
        } else {
            return round($size / 1073741824, $int) . ' Gb';
        }
    }


    /**
     * strLink
     *
     * @param string $str
     * @param bool   $sub
     * @return string
     */
    public static function strLink ($str = '', $sub = false)
    {
        if (!$sub) {
            return $str;
        }

        $len = mb_strlen($str);
        $maxLen = Config::get('Gmanager', 'maxLinkSize');

        if ($len > $maxLen) {
            $start = ceil($maxLen / 2);
            $end = $len - $start;
            if ($maxLen % 2) {
                $end += 1;
            }
            return mb_substr($str, 0, $start) . ' ... ' . mb_substr($str, $end);
        }

        return $str;
    }


    /**
     * xhtmlHighlight
     *
     * @param string $fl
     * @return array
     */
    public static function xhtmlHighlight ($fl = '')
    {
        return array_slice(
            explode(
                "\n",
                str_replace(
                    array('&nbsp;', '<code>', '</code>', '<br />'),
                    array(' ', '', '', "\n"),
                    preg_replace(
                        '#color="(.*?)"#', 'style="color: $1"',
                        str_replace(
                            array('<font ', '</font>'),
                            array('<span ', '</span>'),
                            highlight_string($fl, true)
                        )
                    )
                )
            ),
            1,
            -2
        );
    }


    /**
     * urlHighlight
     *
     * @param string $fl
     * @return array
     */
    public static function urlHighlight ($fl = '')
    {
        return explode(
            "\n",
            preg_replace(
                '/(&quot;|&#039;)[^<>]*(&quot;|&#039;)/iU', '<span style="color:#DD0000">$0</span>',
                preg_replace(
                    '/&lt;!--.*--&gt;/iU', '<span style="color:#FF8000">$0</span>',
                    preg_replace(
                        '/(&lt;[^\s!]*\s)([^<>]*)([\/?]?&gt;)/iU', '$1<span style="color:#007700">$2</span>$3',
                        preg_replace(
                            '/&lt;[^<>]*&gt;/iU',
                            '<span style="color:#0000BB">$0</span>',
                            htmlspecialchars($fl, ENT_QUOTES)
                        )
                    )
                )
            )
        );
    }


    /**
     * message
     *
     * @param string $text
     * @param int    $error Helper_View::MESSAGE_SUCCESS - success,
     *                      Helper_View::MESSAGE_ERROR - error,
     *                      Helper_View::MESSAGE_ERROR_EMAIL - error and email
     * @return string
     */
    public static function message ($text = '', $error = Helper_View::MESSAGE_SUCCESS)
    {
        if ($error == self::MESSAGE_ERROR_EMAIL) {
            return '<div class="red">' . $text . '<br/></div><div><form action="change.php?go=send_mail&amp;c=' . Registry::get('rCurrent') . '" method="post"><div><input type="hidden" name="to" value="wapinet@mail.ru"/><input type="hidden" name="theme" value="Gmanager ' . Config::getVersion() . ' Error (' . Config::get('Gmanager', 'mode') . ')"/><input type="hidden" name="mess" value="' . htmlspecialchars('URI: ' . Helper_System::basename($_SERVER['PHP_SELF']) . '?' . $_SERVER['QUERY_STRING'] . "\n" . 'PHP: ' . PHP_VERSION . "\n" . htmlspecialchars_decode(str_replace('<br/>', "\n", $text), ENT_COMPAT), ENT_COMPAT) . '"/><input type="submit" value="' . Language::get('send_report') . '"/></div></form></div>';
        } elseif ($error == self::MESSAGE_ERROR) {
            return '<div class="red">' . $text . '<br/></div>';
        }

        return '<div class="green">' . $text . '<br/></div>';
    }
}
