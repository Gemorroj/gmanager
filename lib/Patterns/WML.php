<?php
/**
 *
 * This software is distributed under the GNU GPL v3.0 license.
 *
 * @author    Gemorroj
 * @copyright 2008-2018 http://wapinet.ru
 * @license   http://www.gnu.org/licenses/gpl-3.0.txt
 * @link      https://github.com/Gemorroj/gmanager
 *
 */


class Patterns_WML implements Patterns_Interface
{
    /**
     * pattern
     * 
     * @return array
     */
    public static function get ()
    {
        return array(
            'WML 1.3'   => '<?xml version="1.0" encoding="utf-8"?>
<!DOCTYPE wml PUBLIC "-//WAPFORUM//DTD WML 1.3//EN" "http://www.wapforum.org/DTD/wml13.dtd">
<wml>
<card id="card1" title="">
<p>

</p>
</card>
</wml>'
        );
    }
}
