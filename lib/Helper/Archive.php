<?php
/**
 * This software is distributed under the GNU GPL v3.0 license.
 *
 * @author    Gemorroj
 * @copyright 2008-2018 http://wapinet.ru
 * @license   http://www.gnu.org/licenses/gpl-3.0.txt
 *
 * @see      https://github.com/Gemorroj/gmanager
 */
class Helper_Archive
{
    /**
     * isArchive.
     *
     * @param string $type
     *
     * @return string archive type or empty string
     */
    public static function isArchive($type)
    {
        if ('ZIP' === $type || 'JAR' === $type || 'AAR' === $type || 'WAR' === $type) {
            return Archive::FORMAT_ZIP;
        }
        if ('TAR' === $type || 'TGZ' === $type || 'TGZ2' === $type || 'TAR.GZ' === $type || 'TAR.GZ2' === $type) {
            return Archive::FORMAT_TAR;
        }
        if ('GZ' === $type || 'GZ2' === $type) {
            return Archive::FORMAT_GZ;
        }
        if (('TBZ' === $type || 'TBZ2' === $type || 'TAR.BZ' === $type || 'TAR.BZ2' === $type || 'BZ' === $type || 'BZ2' === $type) && \extension_loaded('bz2')) {
            return Archive::FORMAT_BZ2;
        }
        if ('RAR' === $type && \extension_loaded('rar')) {
            return Archive::FORMAT_RAR;
        }

        return '';
    }
}
