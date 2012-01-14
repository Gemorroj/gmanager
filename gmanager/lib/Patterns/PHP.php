<?php
/**
 * 
 * This software is distributed under the GNU GPL v3.0 license.
 * @author Gemorroj
 * @copyright 2008-2012 http://wapinet.ru
 * @license http://www.gnu.org/licenses/gpl-3.0.txt
 * @link http://wapinet.ru/gmanager/
 * @version 0.8
 * 
 * PHP version >= 5.2.1
 * 
 */


class Patterns_PHP implements Patterns_Interface
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
