<?php
/**
 *
 * This software is distributed under the GNU GPL v3.0 license.
 *
 * @author    Gemorroj
 * @copyright 2008-2017 http://wapinet.ru
 * @license   http://www.gnu.org/licenses/gpl-3.0.txt
 * @link      https://github.com/Gemorroj/gmanager
 *
 */


class NetworkWrapper
{
    /**
     * Getter
     *
     * @param string $data
     * @return string
     */
    public static function get ($data)
    {
        return $data;
    }


    /**
     * Setter
     *
     * @param string $data
     * @return string
     */
    public static function set ($data)
    {
        $idna = new NetworkWrapper_IdnaConvert();
        return $idna->encode_uri($data);
    }
}
