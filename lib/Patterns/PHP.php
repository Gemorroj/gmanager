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
class Patterns_PHP implements Patterns_Interface
{
    /**
     * pattern.
     *
     * @return array
     */
    public static function get()
    {
        return [
            'Default' => '<?php

?>',
            'Class' => '<?php
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

?>',
        ];
    }
}
