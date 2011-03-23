<?php
/**
 * 
 * This software is distributed under the GNU LGPL v3.0 license.
 * @author Gemorroj
 * @copyright 2008-2010 http://wapinet.ru
 * @license http://www.gnu.org/licenses/lgpl-3.0.txt
 * @link http://wapinet.ru/gmanager/
 * @version 0.7.4 beta
 * 
 * PHP version >= 5.2.1
 * 
 */


class Patterns_PHP
{
    /**
     * pattern
     * 
     * @return array
     */
    public static function get ()
    {
        return array(
            'Default'                           => '<?php

?>',
            'Class'                             => '<?php
/**
 *
 */
class MyClass
{   /**
     *
     */
    public function myFunction ()
    {

    }
}

?>'
        );
    }
}

?>
