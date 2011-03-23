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


class Patterns_HTML
{
    /**
     * pattern
     * 
     * @return array
     */
    public static function get ()
    {
        return array(
            'HTML5' => '<!DOCTYPE html>
<head>
<title></title>
</head>
<body>
<div>

</div>
</body>
</html>',
            'HTML 4.01 Strict'           => '<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN" "http://www.w3.org/TR/html4/strict.dtd">
<html>
<head>
<title></title>
</head>
<body>
<div>

</div>
</body>
</html>',
            'HTML 4.01 Transitional'      => '<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html>
<head>
<title></title>
</head>
<body>
<div>

</div>
</body>
</html>',

            'HTML 4.01 Frameset'        => '<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Frameset//EN" "http://www.w3.org/TR/html4/frameset.dtd">
<html>
<head>
<title></title>
</head>
<body>
<div>

</div>
</body>
</html>'
        );
    }
}

?>
