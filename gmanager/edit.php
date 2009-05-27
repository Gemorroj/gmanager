<?php
// кодировка UTF-8
/**
 * 
 * This software is distributed under the GNU LGPL v3.0 license.
 * @author Gemorroj
 * @copyright 2008-2009 http://wapinet.ru
 * @license http://www.gnu.org/licenses/lgpl-3.0.txt
 * @link http://wapinet.ru/gmanager/
 * @version 0.7 alpha
 * 
 * PHP version >= 5.2.1
 * 
 */


if (isset($_POST['get'])) {
    header('Location: http://' . str_replace(array('\\', '//'), '/', $_SERVER['HTTP_HOST'] . dirname($_SERVER['PHP_SELF']) . '/change.php?get=' . rawurlencode($_GET['c'] . ($_GET['f'] ? '&f=' . $_GET['f'] : ''))));
    exit;
}

require 'functions.php';

$charset = $_GET['charset'];
if ($charset == 'utf-8' || $charset == 'windows-1251') {
    $full_charset = 'charset=' . $charset . '&amp;';
}

$current = c($_SERVER['QUERY_STRING'], rawurlencode($_GET['c']));
$h_current = htmlspecialchars($current);
$r_current = str_replace('%2F', '/', rawurlencode($current));

send_header($_SERVER['HTTP_USER_AGENT']);

echo str_replace('%dir%', $h_current, $top) . '
<div class="w2">
' . $lng['title_edit'] . '<br/>
</div>
' . this($current);

$type = strtoupper(strrchr($h_current, '.'));

switch ($_GET['go']) {
    default:

        if (!$mode->is_file($current)) {
            echo report($lng['not_found'], true);
            break;
        }


        if ($type == '.ZIP' || $type == '.JAR') {
            $content = edit_zip_file($current, $_GET['f']);
            $content['text'] = htmlspecialchars($content['text'], ENT_NOQUOTES);
            $f = '&amp;f=' . $_GET['f'];
        } else {
            $content['text'] = htmlspecialchars($mode->file_get_contents($current), ENT_NOQUOTES);
            $content['size'] = file_size($current, true);
            $content['lines'] = sizeof(explode("\n", $content['text']));
            $f = '';
        }

        if ($charset == 'windows-1251') {
            $content['text'] = iconv('UTF-8', 'windows-1251', $content['text']);
        } elseif ($charset == 'utf-8') {
            $content['text'] = iconv('windows-1251', 'UTF-8', $content['text']);
        }



if(get_class($mode) == 'http'){
	$http = '<div class="rb">
<a href="http://' . $_SERVER['HTTP_HOST'] . str_replace('\\', '/', iconv_substr(realpath($current), iconv_strlen($_SERVER['DOCUMENT_ROOT']))).'">'.$lng['look'].'</a><br/>
</div>';
}
else{
	$http = '';
}


echo '<div class="input">
' . $lng['sz'] . ': ' . $content['size'] . '<br/>
Строк: ' . $content['lines'] . '
<form action="edit.php?go=save&amp;c=' . $r_current . $f . '" method="post">
<div>
<textarea name="text" rows="18" cols="64" wrap="off">' . $content['text'] . '</textarea>
<br/>
<input type="submit" value="' . $lng['save'] . '"/> <input type="submit" name="get" value="' . $lng['get'] . '"/><br/>
' . $lng['chmod'] . ' <input onkeypress="return number(event)" type="text" name="chmod" value="' . look_chmod($current) . '" size="4" maxlength="4" style="width:28pt;"/>
</div>
</form>
</div>
<div class="input">
<form action="edit.php?go=replace&amp;c=' . $r_current . $f . '" method="post">
<div>
' . $lng['replace_from'] . '<br/>
<input type="text" name="from" style="width:128pt;"/>' . $lng['replace_to'] . '<input type="text" name="to" style="width:128pt;"/><br/>
<input type="checkbox" name="regexp" value="1"/>' . $lng['regexp'] . '<br/>
<input type="submit" value="' . $lng['replace'] . '"/>
</div>
</form>
</div>
'.$http.'
<div class="rb">
<a href="edit.php?c=' . $r_current . $f . '&amp;' . $full_charset . 'go=syntax">' . $lng['syntax'] . '</a><br/>
</div>';
        if ($type != '.ZIP' && $type != '.JAR' && extension_loaded('xml')) {
echo '<div class="rb">
<a href="edit.php?c=' . $r_current . '&amp;' . $full_charset . 'go=validator">' . $lng['validator'] . '</a><br/>
</div>';
        }
echo '<div class="rb">
' . $lng['charset'] . '
<form action="edit.php?" method="get" style="padding:0; margin:0;">
<div>
<input type="hidden" name="c" value="' . $h_current . '"/>
<input type="hidden" name="f" value="' . $_GET['f'] . '"/>';
        if ($charset == 'windows-1251') {
echo '<input type="radio" name="charset"/>' . $lng['charset_no'] . '
<input type="radio" name="charset" value="utf-8"/>' . $lng['charset_utf'] . '
<input type="radio" name="charset" value="windows-1251" checked="checked"/>' . $lng['charset_win'];
        } elseif ($charset == 'utf-8') {
echo '<input type="radio" name="charset"/>' . $lng['charset_no'] . '
<input type="radio" name="charset" value="utf-8" checked="checked"/>' . $lng['charset_utf'] .
                '
<input type="radio" name="charset" value="windows-1251"/>' . $lng['charset_win'];
        } else {
echo '<input type="radio" name="charset" checked="checked"/>' . $lng['charset_no'] . '
<input type="radio" name="charset" value="utf-8"/>' . $lng['charset_utf'] . '
<input type="radio" name="charset" value="windows-1251"/>' . $lng['charset_win'];
        }

echo '<br/><input type="submit" value="' . $lng['ch'] . '"/>
</div>
</form>
</div>';
        break;

    case 'save':
        if ($type == '.ZIP' || $type == '.JAR') {
            echo edit_zip_file_ok($current, $_GET['f'], $_POST['text']);
        } else {
            echo create_file($current, $_POST['text'], $_POST['chmod']);
        }
        break;

    case 'replace':
        if ($type == '.ZIP' || $type == '.JAR') {
            echo zip_replace($current, $_GET['f'], $_POST['from'], $_POST['to'], $_POST['regexp']);
        } else {
            echo replace($current, $_POST['from'], $_POST['to'], $_POST['regexp']);
        }
        break;

    case 'syntax':
        if ($type == '.ZIP' || $type == '.JAR') {
            echo zip_syntax($current, $_GET['f'], $charset, $syntax);
        } else {
            if (!$syntax) {
                echo syntax($current, $charset);
            } else {
                echo syntax2($current, $charset);
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

echo '<div class="rb">' . round(microtime(true) - $ms, 4) . '<br/></div>' . $foot;
?>