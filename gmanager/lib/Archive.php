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
    /**
     * main
     *
     * @return mixed (object or bool)
     */
    public static function main ()
    {
        switch (Registry::get('archiveDriver')) {
            case 'zip':
                return new Archive_Zip;
                break;


            case 'tar':
                // Archive_Tar exists =(
                return new Archive_Tars;
                break;


            case 'rar':
                if (extension_loaded('rar')) {
                    return new Rachive_Rar;
                }
                break;
        }

        return false;
    }
}

?>
