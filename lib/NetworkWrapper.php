<?php
/**
 * This software is distributed under the GNU GPL v3.0 license.
 *
 * @author    Gemorroj
 * @copyright 2008-2023 http://wapinet.ru
 * @license   http://www.gnu.org/licenses/gpl-3.0.txt
 *
 * @see      https://github.com/Gemorroj/gmanager
 */
class NetworkWrapper
{
    public static function convertUrl(string $url): string
    {
        $idna = new \Algo26\IdnaConvert\ToIdn();

        return $idna->convertUrl($url);
    }

    public static function convertHost(string $host): string
    {
        $idna = new \Algo26\IdnaConvert\ToIdn();

        return $idna->convert($host);
    }
}
