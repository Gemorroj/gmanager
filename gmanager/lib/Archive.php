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


class Archive
{
    const FORMAT_ZIP = 'zip';
    const FORMAT_TAR = 'tar';
    const FORMAT_BZ2 = 'bz2';
    const FORMAT_RAR = 'rar';


    /**
     * factory
     *
     * @return Archive_Zip|Archive_Tars|Archive_Rar|null
     */
    public static function factory ()
    {
        switch (Registry::get('archiveFormat')) {
            case self::FORMAT_ZIP:
                return new Archive_Zip;
                break;


            case self::FORMAT_TAR:
            case self::FORMAT_BZ2:
                // Archive_Tar exists =(
                return new Archive_Tars;
                break;


            case self::FORMAT_RAR:
                if (extension_loaded('rar')) {
                    return new Archive_Rar;
                }
                break;
        }

        return null;
    }
}

?>
