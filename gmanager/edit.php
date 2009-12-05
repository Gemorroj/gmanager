<?php
// encoding = 'utf-8'
/**
 * 
 * This software is distributed under the GNU LGPL v3.0 license.
 * @author Gemorroj
 * @copyright 2008-2009 http://wapinet.ru
 * @license http://www.gnu.org/licenses/lgpl-3.0.txt
 * @link http://wapinet.ru/gmanager/
 * @version 0.7.2 beta
 * 
 * PHP version >= 5.2.1
 * 
 */


$_GET['f'] = isset($_GET['f']) ? $_GET['f'] : '';
$_GET['go'] = isset($_GET['go']) ? $_GET['go'] : '';
$_GET['c'] = isset($_GET['c']) ? $_GET['c'] : '';
if (!isset($_GET['charset'])) {
	$_GET['charset'] = '';
} else {
	$_GET['c'] = rawurldecode($_GET['c']);
    
    if ($_GET['f'] != '') {
        $_GET['f'] = rawurldecode($_GET['f']);
    }
}

if (isset($_POST['get'])) {
    header('Location: http://' . str_replace(array('\\', '//'), '/', $_SERVER['HTTP_HOST'] . dirname($_SERVER['PHP_SELF']) . '/change.php?get=' . rawurlencode($_GET['c'] . ($_GET['f'] ? '&f=' . $_GET['f'] : ''))));
    exit;
} elseif (isset($_POST['line_editor'])) {
    $_GET['go'] = '';
}

require 'functions.php';

$charset = array('', '');
$full_charset = '';

if ($_GET['charset']) {
    list($charset[0], $charset[1],) = encoding('', $_GET['charset']);
    $full_charset = 'charset=' . htmlspecialchars($charset[0], ENT_COMPAT, 'UTF-8') . '&amp;';
}

$current = c($_SERVER['QUERY_STRING'], rawurlencode($_GET['c']));
$h_current = htmlspecialchars($current, ENT_COMPAT);
$r_current = str_replace('%2F', '/', rawurlencode($current));

send_header($_SERVER['HTTP_USER_AGENT']);

echo str_replace('%dir%', $h_current, $GLOBALS['top']) . '
<div class="w2">
' . $GLOBALS['lng']['title_edit'] . '<br/>
</div>
' . this($current);

$archive = is_archive(get_type(basename($h_current)));

switch ($_GET['go']) {
    default:
    case 'replace':
		$to = $from = '';

        if (!$GLOBALS['mode']->is_file($current)) {
            echo report($GLOBALS['lng']['not_found'], 1);
            break;
        }

	    if ($_GET['go'] == 'replace' && isset($_POST['from']) && isset($_POST['to'])) {
            $from = htmlspecialchars($_POST['from'], ENT_COMPAT);
            $to = htmlspecialchars($_POST['to'], ENT_COMPAT);
            if ($archive == 'ZIP') {
                echo zip_replace($current, $_GET['f'], $_POST['from'], $_POST['to'], $_POST['regexp']);
            } else {
                echo replace($current, $_POST['from'], $_POST['to'], isset($_POST['regexp']));
            }
        }

        if ($archive == 'ZIP') {
            $content = edit_zip_file($current, $_GET['f']);
            $content['text'] = htmlspecialchars($content['text'], ENT_COMPAT);
            $f = '&amp;f=' . rawurlencode($_GET['f']);
        } else {
            $content['text'] = htmlspecialchars($GLOBALS['mode']->file_get_contents($current), ENT_COMPAT);
            $content['size'] = format_size(size($current));
            $content['lines'] = sizeof(explode("\n", $content['text']));
            $f = '';
        }

        if ($charset[0] && $content['size'] > 0) {
            $content['text'] = iconv($charset[0], $charset[1], $content['text']);
        }

$r = realpath($current);
$l = iconv_strlen($_SERVER['DOCUMENT_ROOT']);
if (!$path = @iconv_substr($r, $l)) {
    $path = iconv($GLOBALS['altencoding'], 'UTF-8', substr($r, $l));
}

if ($GLOBALS['class'] == 'http' && $path) {
    $http = '<div class="rb">
<a href="http://' . $_SERVER['HTTP_HOST'] . str_replace('//', '/', '/' . str_replace('%2F', '/', rawurlencode(str_replace('\\', '/', $path)))).'">'.$GLOBALS['lng']['look'].'</a><br/>
</div>';
} else {
    $http = '';
}

if ($GLOBALS['line_editor']['on']) {
    $i = $start = isset($_POST['start']) ? intval($_POST['start']) - 1 : 0;
    $end = isset($_POST['end']) ? intval($_POST['end']) : $GLOBALS['line_editor']['lines'];
    
    $edit = '';
    foreach(array_slice(explode("\n", $content['text']), $start, $end) as $var) {
        $i++;
        $edit .= $i . '<input name="line[' . ($i - 1) . ']" type="text" value="' . $var . '"/><br/>';
    }
    
    $edit .= '<input onkeypress="return number(event)" style="width:24pt;" type="text" value="' . ($start + 1) . '" name="start" />
    -
    <input onkeypress="return number(event)" style="width:24pt;" type="text" value="' . $end . '" name="end"/>
    <input name="line_editor" type="submit" value="' . $GLOBALS['lng']['look'] . '"/><br/>';
} else {
    $edit = '<textarea name="text" rows="18" cols="64" wrap="' . ($GLOBALS['wrap'] ? 'on' : 'off') . '">' . $content['text'] . '</textarea><br/>';
}

echo '<div class="input">
' . $GLOBALS['lng']['sz'] . ': ' . $content['size'] . '<br/>
Строк: ' . $content['lines'] . '
<form action="edit.php?go=save&amp;c=' . $r_current . $f . '" method="post">
<div>' . $edit .'<input type="submit" value="' . $GLOBALS['lng']['save'] . '"/>
<select name="charset">
<option value="utf-8">utf-8</option>
<option value="windows-1251"'.($charset[1] == 'windows-1251'? ' selected="selected"' : '').'>windows-1251</option>
<option value="iso-8859-1"'.($charset[1] == 'iso-8859-1'? ' selected="selected"' : '').'>iso-8859-1</option>
<option value="cp866"'.($charset[1] == 'cp866'? ' selected="selected"' : '').'>cp866</option>
<option value="koi8-r"'.($charset[1] == 'koi8-r'? ' selected="selected"' : '').'>koi8-r</option>
</select><br/>
' . $GLOBALS['lng']['chmod'] . ' <input onkeypress="return number(event)" type="text" name="chmod" value="' . look_chmod($current) . '" size="4" maxlength="4" style="width:28pt;"/><br/>
<input type="submit" name="get" value="' . $GLOBALS['lng']['get'] . '"/>
</div>
</form>
</div>
<div class="input">
<form action="edit.php?go=replace&amp;c=' . $r_current . $f . '" method="post">
<div>
' . $GLOBALS['lng']['replace_from'] . '<br/>
<input type="text" name="from" value="' . $from . '" style="width:128pt;"/>' . $GLOBALS['lng']['replace_to'] . '<input type="text" name="to" value="' . $to . '" style="width:128pt;"/><br/>
<input type="checkbox" name="regexp" value="1"' . (isset($_POST['regexp']) ? ' checked="checked"' : '') . '/>' . $GLOBALS['lng']['regexp'] . '<br/>
<input type="submit" value="' . $GLOBALS['lng']['replace'] . '"/>
</div>
</form>
</div>
'.$http.'
<div class="rb">
<a href="edit.php?c=' . $r_current . $f . '&amp;' . $full_charset . 'go=syntax">' . $GLOBALS['lng']['syntax'] . '</a><br/>
</div>';


if ($archive == '' && extension_loaded('xml')) {
    echo '<div class="rb">
<a href="edit.php?c=' . $r_current . '&amp;' . $full_charset . 'go=validator">' . $GLOBALS['lng']['validator'] . '</a><br/>
</div>';
}

echo '<div class="rb">
' . $GLOBALS['lng']['charset'] . '
<form action="edit.php?" method="get" style="padding:0; margin:0;">
<div>
<input type="hidden" name="c" value="' . $r_current . '"/>
<input type="hidden" name="f" value="' . rawurlencode($_GET['f']) . '"/>
<select name="charset">
<option value="">'.$GLOBALS['lng']['charset_no'].'</option>
<optgroup label="UTF-8">
<option value="utf-8 -&gt; windows-1251"'.($_GET['charset'] == 'utf-8 -> windows-1251' ? ' selected="selected"' : '').'>utf-8 -&gt; windows-1251</option>
<option value="utf-8 -&gt; iso-8859-1"'.($_GET['charset'] == 'utf-8 -> iso-8859-1' ? ' selected="selected"' : '').'>utf-8 -&gt; iso-8859-1</option>
<option value="utf-8 -&gt; cp866"'.($_GET['charset'] == 'utf-8 -> cp866' ? ' selected="selected"' : '').'>utf-8 -&gt; cp866</option>
<option value="utf-8 -&gt; koi8-r"'.($_GET['charset'] == 'utf-8 -> koi8-r' ? ' selected="selected"' : '').'>utf-8 -&gt; koi8-r</option>
</optgroup>
<optgroup label="Windows-1251">
<option value="windows-1251 -&gt; utf-8"'.($_GET['charset'] == 'windows-1251 -> utf-8' ? ' selected="selected"' : '').'>windows-1251 -&gt; utf-8</option>
<option value="windows-1251 -&gt; iso-8859-1"'.($_GET['charset'] == 'windows-1251 -> iso-8859-1' ? ' selected="selected"' : '').'>windows-1251 -&gt; iso-8859-1</option>
<option value="windows-1251 -&gt; cp866"'.($_GET['charset'] == 'windows-1251 -> cp866' ? ' selected="selected"' : '').'>windows-1251 -&gt; cp866</option>
<option value="windows-1251 -&gt; koi8-r"'.($_GET['charset'] == 'windows-1251 -> koi8-r' ? ' selected="selected"' : '').'>windows-1251 -&gt; koi8-r</option>
</optgroup>
<optgroup label="ISO-8859-1">
<option value="iso-8859-1 -&gt; utf-8"'.($_GET['charset'] == 'iso-8859-1 -> utf-8' ? ' selected="selected"' : '').'>iso-8859-1 -&gt; utf-8</option>
<option value="iso-8859-1 -&gt; windows-1251"'.($_GET['charset'] == 'iso-8859-1 -> windows-1251' ? ' selected="selected"' : '').'>iso-8859-1 -&gt; windows-1251</option>
<option value="iso-8859-1 -&gt; cp866"'.($_GET['charset'] == 'iso-8859-1 -> cp866' ? ' selected="selected"' : '').'>iso-8859-1 -&gt; cp866</option>
<option value="iso-8859-1 -&gt; koi8-r"'.($_GET['charset'] == 'iso-8859-1 -> koi8-r' ? ' selected="selected"' : '').'>iso-8859-1 -&gt; koi8-r</option>
</optgroup>
<optgroup label="CP866">
<option value="cp866 -&gt; utf-8"'.($_GET['charset'] == 'cp866 -> utf-8' ? ' selected="selected"' : '').'>cp866 -&gt; utf-8</option>
<option value="cp866 -&gt; windows-1251"'.($_GET['charset'] == 'cp866 -> windows-1251' ? ' selected="selected"' : '').'>cp866 -&gt; windows-1251</option>
<option value="cp866 -&gt; iso-8859-1"'.($_GET['charset'] == 'cp866 -> iso-8859-1' ? ' selected="selected"' : '').'>cp866 -&gt; iso-8859-1</option>
<option value="cp866 -&gt; koi8-r"'.($_GET['charset'] == 'cp866 -> koi8-r' ? ' selected="selected"' : '').'>cp866 -&gt; koi8-r</option>
</optgroup>
<optgroup label="KOI8-R">
<option value="koi8-r -&gt; utf-8"'.($_GET['charset'] == 'koi8-r -> utf-8' ? ' selected="selected"' : '').'>koi8-r -&gt; utf-8</option>
<option value="koi8-r -&gt; windows-1251"'.($_GET['charset'] == 'koi8-r -> windows-1251' ? ' selected="selected"' : '').'>koi8-r -&gt; windows-1251</option>
<option value="koi8-r -&gt; iso-8859-1"'.($_GET['charset'] == 'koi8-r -> iso-8859-1' ? ' selected="selected"' : '').'>koi8-r -&gt; iso-8859-1</option>
<option value="koi8-r -&gt; cp866"'.($_GET['charset'] == 'koi8-r -> cp866' ? ' selected="selected"' : '').'>koi8-r -&gt; cp866</option>
</optgroup>
</select><br/>
<input type="submit" value="' . $GLOBALS['lng']['ch'] . '"/>
</div>
</form>
</div>';

        break;


    case 'save':
        if ($GLOBALS['line_editor']['on']) {
            $_POST['text'] = $_POST['line'] + explode("\n", $GLOBALS['mode']->file_get_contents($current));
            ksort($_POST['text'], SORT_NUMERIC);
            $_POST['text'] = implode("\n", $_POST['text']);
        }

        if ($_POST['charset'] != 'utf-8') {
            $_POST['text'] = iconv('UTF-8', $_POST['charset'], $_POST['text']);
        }

        if ($archive == 'ZIP') {
            echo edit_zip_file_ok($current, $_GET['f'], $_POST['text']);
        } else {
            echo create_file($current, $_POST['text'], $_POST['chmod']);
        }
        break;


    case 'syntax':
        if ($archive == 'ZIP') {
            echo zip_syntax($current, $_GET['f'], $charset);
        } else {
            if ($GLOBALS['syntax']) {
                echo syntax2($current, $charset);
            } else {
                echo syntax($current, $charset);
            }
        }
        break;


    case 'validator':
    /*
        echo validator('http://' . $_SERVER['HTTP_HOST'] . str_replace('\\', '/', substr(realpath($current), strlen($_SERVER['DOCUMENT_ROOT']))), $charset);
    */
        echo validator($current, $charset);
        break;
}


echo '<div class="rb">' . round(microtime(true) - $ms, 4) . '<br/></div>' . $GLOBALS['foot'];

?>
