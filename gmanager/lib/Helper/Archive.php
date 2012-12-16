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


class Helper_Archive
{
    /**
     * isArchive
     *
     * @param string $type
     * @return string   archive type or empty string
     */
    public static function isArchive ($type)
    {
        if ($type === 'ZIP' || $type === 'JAR' || $type === 'AAR' || $type === 'WAR') {
            return Archive::FORMAT_ZIP;
        } elseif ($type === 'TAR' || $type === 'TGZ' || $type === 'TGZ2' || $type === 'TAR.GZ' || $type === 'TAR.GZ2') {
            return Archive::FORMAT_TAR;
        } elseif ($type === 'GZ' || $type === 'GZ2') {
            return Archive::FORMAT_GZ;
        } elseif (($type === 'TBZ' || $type === 'TBZ2' || $type === 'TAR.BZ' || $type === 'TAR.BZ2' || $type === 'BZ' || $type === 'BZ2') && extension_loaded('bz2')) {
            return Archive::FORMAT_BZ2;
        } elseif ($type === 'RAR' && extension_loaded('rar')) {
            return Archive::FORMAT_RAR;
        }

        return '';
    }
}
