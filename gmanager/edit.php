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


define('GMANAGER_START', microtime(true));


$_GET['f']          = isset($_GET['f']) ? $_GET['f'] : '';
$_GET['go']         = isset($_GET['go']) ? $_GET['go'] : '';
$_GET['c']          = isset($_GET['c']) ? $_GET['c'] : '';
$_GET['charset']    = isset($_GET['charset']) ? $_GET['charset'] : '';
$_GET['beautify']   = isset($_GET['beautify']) ? $_GET['beautify'] : '';

if ($_GET['charset'] || $_GET['beautify']) {
    $_GET['c'] = rawurldecode($_GET['c']);
    if ($_GET['f'] != '') {
        $_GET['f'] = rawurldecode($_GET['f']);
    }
}

if (isset($_POST['get'])) {
    header('Location: http://' . str_replace(array('\\', '//'), '/', $_SERVER['HTTP_HOST'] . dirname($_SERVER['PHP_SELF']) . '/change.php?get=' . rawurlencode($_GET['c'] . ($_GET['f'] ? '&f=' . $_GET['f'] : ''))));
    exit;
} else if (isset($_POST['line_edit'])) {
    $_GET['go'] = '';
}


require 'lib/Config.php';


$charset = array('', '');
$full_charset = '';

if ($_GET['charset']) {
    list($charset[0], $charset[1],) = Registry::getGmanager()->encoding('', $_GET['charset']);
    $full_charset = 'charset=' . htmlspecialchars($charset[0], ENT_COMPAT, 'UTF-8') . '&amp;';
}

Registry::getGmanager()->sendHeader();

echo str_replace('%title%', Registry::get('hCurrent'), Registry::get('top')) . '<div class="w2">' . Language::get('title_edit') . '<br/></div>' . Registry::getGmanager()->head();

$archive = Registry::getGmanager()->isArchive(Registry::getGmanager()->getType(basename(Registry::get('hCurrent'))));

switch ($_GET['go']) {
    case 'save':
        if (Registry::get('lineEditor')) {
            $fill = array_fill($_POST['start'] - 1, $_POST['end'], 1);
            if ($archive == 'ZIP') {
                Registry::set('archiveDriver', 'zip');
                $tmp = explode("\n", Archive::main()->lookFile(Registry::get('current'), $_GET['f'], true));
            } else {
                $tmp = explode("\n", Registry::getGmanager()->file_get_contents(Registry::get('current')));
            }


            $all = sizeof($tmp);
            for ($i = 0; $i <= $all; ++$i) {
                if (isset($fill[$i])) {
                    if (isset($_POST['line'][$i])) {
                        $tmp[$i] = (is_array($_POST['line'][$i]) ? implode("\n", $_POST['line'][$i]) : $_POST['line'][$i] . "\n");
                    } else {
                        unset($tmp[$i]);
                    }
                }
            }
            $_POST['text'] = implode("\n", $tmp);
        }

        if ($_POST['charset'] != 'utf-8') {
            $_POST['text'] = iconv('UTF-8', $_POST['charset'], $_POST['text']);
        }

        if ($archive == 'ZIP') {
            Registry::set('archiveDriver', 'zip');
            echo Archive::main()->setEditFile(Registry::get('current'), $_GET['f'], $_POST['text']);
        } else {
            echo Registry::getGmanager()->createFile(Registry::get('current'), $_POST['text'], $_POST['chmod']);
        }
        break;


    case 'syntax':
        if ($archive == 'ZIP') {
            Registry::set('archiveDriver', 'zip');
            $content = Archive::main()->getEditFile(Registry::get('current'), $_GET['f']);
            $content = $content['text'];
        } else {
            $content = Registry::getGmanager()->file_get_contents(Registry::get('current'));            
        }

        if (Config::get('Gmanager', 'syntax') == Config::SYNTAX_WAPINET) {
            echo Registry::getGmanager()->syntaxWapinet($content, $charset);
        } else {
            echo Registry::getGmanager()->syntax($content, $charset);
        }
        break;


    case 'validator':
        echo Registry::getGmanager()->validator(Registry::get('current'), $charset);
        break;


    case 'replace':
    default:
        $to = $from = '';

        if (Registry::get('currentType') != 'file') {
            echo Errors::message(Language::get('not_found'), Errors::MESSAGE_FAIL);
            break;
        }

        if ($_GET['go'] == 'replace' && isset($_POST['from']) && isset($_POST['to'])) {
            $from = htmlspecialchars($_POST['from'], ENT_COMPAT);
            $to = htmlspecialchars($_POST['to'], ENT_COMPAT);
            if ($archive == 'ZIP') {
                echo Registry::getGmanager()->zipReplace(Registry::get('current'), $_GET['f'], $_POST['from'], $_POST['to'], $_POST['regexp']);
            } else {
                echo Registry::getGmanager()->replace(Registry::get('current'), $_POST['from'], $_POST['to'], isset($_POST['regexp']));
            }
        }

        $quotes = defined('ENT_IGNORE') ? ENT_COMPAT | ENT_IGNORE : ENT_COMPAT;

        if ($archive == 'ZIP') {
            Registry::set('archiveDriver', 'zip');
            $content = Archive::main()->getEditFile(Registry::get('current'), $_GET['f']);
            $content['text'] = htmlspecialchars($content['text'], $quotes, 'UTF-8');
            $f = '&amp;f=' . rawurlencode($_GET['f']);
        } else {
            $content['text'] = htmlspecialchars(Registry::getGmanager()->file_get_contents(Registry::get('current')), $quotes, 'UTF-8');
            $content['size'] = Registry::getGmanager()->formatSize(Registry::getGmanager()->size(Registry::get('current')));
            $content['lines'] = substr_count($content['text'], "\n") + 1;
            $f = '';
        }

        if ($charset[0] && $content['size'] > 0) {
            $content['text'] = iconv($charset[0], $charset[1], $content['text']);
        }

        if ($_GET['beautify']) {
            $content['text'] = Registry::getGmanager()->beautify($content['text']);
            $content['size'] = Registry::getGmanager()->formatSize(strlen($content['text']));
            $content['lines'] = substr_count($content['text'], "\n");
        }


        $r = Registry::getGmanager()->realpath(Registry::get('current'));
        $l = iconv_strlen(IOWrapper::get($_SERVER['DOCUMENT_ROOT']));
        if (!$path = @iconv_substr($r, $l)) {
            $path = iconv(Config::get('Gmanager', 'altEncoding'), 'UTF-8', iconv_substr($r, $l));
        }

        if (Config::get('Gmanager', 'mode') == 'HTTP' && $path) {
            $http = '<div class="rb"><a href="http://' . $_SERVER['HTTP_HOST'] . str_replace('//', '/', '/' . str_replace('%2F', '/', rawurlencode(str_replace('\\', '/', $path)))) . '">' . Language::get('look') . '</a><br/></div>';
        } else {
            $http = '';
        }

        if (Registry::get('lineEditor') && $content['lines'] > Config::get('LineEditor', 'minLines')) {
            $i = $start = isset($_REQUEST['start']) ? intval($_REQUEST['start']) - 1 : 0;
            $j = 0;
            $end = isset($_REQUEST['end']) ? intval($_REQUEST['end']) : Config::get('LineEditor', 'lines');

            $edit = '<table class="pedit">';

            foreach (array_slice(explode("\n", $content['text']), $start, $end) as $var) {
                $j++;
                $i++;
                $edit .= '<tr id="i' . $j . '"><td style="width:10px;">' . $i . '</td><td><input name="line[' . ($i - 1) . '][]" type="text" value="' . $var . '"/></td><td class="pedit_r"><a href="javascript:void(0);" onclick="Gmanager.edit(1,this.parentNode);">[+]</a> / <a href="javascript:void(0);" onclick="Gmanager.edit(0,this.parentNode);">[-]</a></td></tr>';
            }
            if ($end > $i) {
                $j++;
                $edit .= '<tr id="i' . $j . '"><td style="width:10px">' . ($i + 1) . '+</td><td><input name="line[' . $i . '][]" type="text"/></td><td class="pedit_r"><a href="javascript:void(0);" onclick="Gmanager.edit(1,this.parentNode);">[+]</a> / <a href="javascript:void(0);" onclick="Gmanager.edit(0,this.parentNode);">[-]</a></td></tr>';
            }

            $edit .= '</table><input onkeypress="return Gmanager.number(event)" style="-wap-input-format:\'*N\';width:24pt;" type="text" value="' . ($start + 1) . '" name="start" /> - <input onkeypress="return Gmanager.number(event)" style="-wap-input-format:\'*N\';width:24pt;" type="text" value="' . $end . '" name="end"/> <input name="line_edit" type="submit" value="' . Language::get('look') . '"/><br/>';
        } else {
            $edit = '<textarea name="text" rows="18" cols="64" wrap="' . (Config::get('Editor', 'wrap') ? 'on' : 'off') . '">' . $content['text'] . '</textarea><br/>';
        }

        echo '<div class="input">' . $content['lines'] . ' ' . Language::get('lines') . ' / ' . $content['size'] . '<form action="edit.php?go=save&amp;c=' . Registry::get('rCurrent') . $f . '" method="post"><div class="edit">' . $edit . '<input type="submit" value="' . Language::get('save') . '"/><select name="charset"><option value="utf-8">utf-8</option><option value="windows-1251"' . ($charset[1] == 'windows-1251'? ' selected="selected"' : '') . '>windows-1251</option><option value="iso-8859-1"' . ($charset[1] == 'iso-8859-1'? ' selected="selected"' : '') . '>iso-8859-1</option><option value="cp866"' . ($charset[1] == 'cp866'? ' selected="selected"' : '') . '>cp866</option><option value="koi8-r"' . ($charset[1] == 'koi8-r'? ' selected="selected"' : '') . '>koi8-r</option></select><br/>' . Language::get('chmod') . ' <input onkeypress="return Gmanager.number(event)" type="text" name="chmod" value="' . Registry::getGmanager()->lookChmod(Registry::get('current')) . '" size="4" maxlength="4" style="-wap-input-format:\'4N\';width:28pt;"/><br/><input type="submit" name="get" value="' . Language::get('get') . '"/></div></form><a href="edit.php?lineEditor=0&amp;c=' . Registry::get('rCurrent') . $f . '">' . Language::get('basic_editor') . '</a> / <a href="edit.php?lineEditor=1&amp;c=' . Registry::get('rCurrent') . $f . '">' . Language::get('line_editor') . '</a></div><div class="input"><form action="edit.php?go=replace&amp;c=' . Registry::get('rCurrent') . $f . '" method="post"><div>' . Language::get('replace_from') . '<br/><input type="text" name="from" value="' . $from . '" style="width:128pt;"/>' . Language::get('replace_to') . '<input type="text" name="to" value="' . $to . '" style="width:128pt;"/><br/><input type="checkbox" name="regexp" id="regexp" value="1"' . (isset($_POST['regexp']) ? ' checked="checked"' : '') . '/><label for="regexp">' . Language::get('regexp') . '</label><br/><input type="submit" value="' . Language::get('replace') . '"/></div></form></div>' . $http . '<div class="rb"><a href="edit.php?c=' . Registry::get('rCurrent') . $f . '&amp;' . $full_charset . 'go=syntax">' . Language::get('syntax') . '</a><br/></div>';


        if ($archive == '' && extension_loaded('xml')) {
            echo '<div class="rb"><a href="edit.php?c=' . Registry::get('rCurrent') . '&amp;' . $full_charset . 'go=validator">' . Language::get('validator') . '</a><br/></div>';
        }

        echo '<div class="rb">' . Language::get('charset') . '<form action="edit.php?" style="padding:0;margin:0;"><div><input type="hidden" name="c" value="' . Registry::get('rCurrent') . '"/><input type="hidden" name="f" value="' . rawurlencode($_GET['f']) . '"/><input type="hidden" name="f" value="' . rawurlencode($_GET['f']) . '"/>' . (Registry::get('lineEditor') ? '<input type="hidden" name="start" value="' . ($start + 1) . '"/><input type="hidden" name="end" value="' . $end . '"/>' : '') . '<select name="charset"><option value="">' . Language::get('charset_no') . '</option><optgroup label="UTF-8"><option value="utf-8 -&gt; windows-1251"' . ($_GET['charset'] == 'utf-8 -> windows-1251' ? ' selected="selected"' : '') . '>utf-8 -&gt; windows-1251</option><option value="utf-8 -&gt; iso-8859-1"' . ($_GET['charset'] == 'utf-8 -> iso-8859-1' ? ' selected="selected"' : '') . '>utf-8 -&gt; iso-8859-1</option><option value="utf-8 -&gt; cp866"' . ($_GET['charset'] == 'utf-8 -> cp866' ? ' selected="selected"' : '') . '>utf-8 -&gt; cp866</option><option value="utf-8 -&gt; koi8-r"' . ($_GET['charset'] == 'utf-8 -> koi8-r' ? ' selected="selected"' : '') . '>utf-8 -&gt; koi8-r</option></optgroup><optgroup label="Windows-1251"><option value="windows-1251 -&gt; utf-8"' . ($_GET['charset'] == 'windows-1251 -> utf-8' ? ' selected="selected"' : '') . '>windows-1251 -&gt; utf-8</option><option value="windows-1251 -&gt; iso-8859-1"' . ($_GET['charset'] == 'windows-1251 -> iso-8859-1' ? ' selected="selected"' : '') . '>windows-1251 -&gt; iso-8859-1</option><option value="windows-1251 -&gt; cp866"' . ($_GET['charset'] == 'windows-1251 -> cp866' ? ' selected="selected"' : '') . '>windows-1251 -&gt; cp866</option><option value="windows-1251 -&gt; koi8-r"' . ($_GET['charset'] == 'windows-1251 -> koi8-r' ? ' selected="selected"' : '') . '>windows-1251 -&gt; koi8-r</option></optgroup><optgroup label="ISO-8859-1"><option value="iso-8859-1 -&gt; utf-8"' . ($_GET['charset'] == 'iso-8859-1 -> utf-8' ? ' selected="selected"' : '') . '>iso-8859-1 -&gt; utf-8</option><option value="iso-8859-1 -&gt; windows-1251"' . ($_GET['charset'] == 'iso-8859-1 -> windows-1251' ? ' selected="selected"' : '') . '>iso-8859-1 -&gt; windows-1251</option><option value="iso-8859-1 -&gt; cp866"' . ($_GET['charset'] == 'iso-8859-1 -> cp866' ? ' selected="selected"' : '') . '>iso-8859-1 -&gt; cp866</option><option value="iso-8859-1 -&gt; koi8-r"' . ($_GET['charset'] == 'iso-8859-1 -> koi8-r' ? ' selected="selected"' : '') . '>iso-8859-1 -&gt; koi8-r</option></optgroup><optgroup label="CP866"><option value="cp866 -&gt; utf-8"' . ($_GET['charset'] == 'cp866 -> utf-8' ? ' selected="selected"' : '') . '>cp866 -&gt; utf-8</option><option value="cp866 -&gt; windows-1251"' . ($_GET['charset'] == 'cp866 -> windows-1251' ? ' selected="selected"' : '') . '>cp866 -&gt; windows-1251</option><option value="cp866 -&gt; iso-8859-1"' . ($_GET['charset'] == 'cp866 -> iso-8859-1' ? ' selected="selected"' : '') . '>cp866 -&gt; iso-8859-1</option><option value="cp866 -&gt; koi8-r"' . ($_GET['charset'] == 'cp866 -> koi8-r' ? ' selected="selected"' : '') . '>cp866 -&gt; koi8-r</option></optgroup><optgroup label="KOI8-R"><option value="koi8-r -&gt; utf-8"' . ($_GET['charset'] == 'koi8-r -> utf-8' ? ' selected="selected"' : '') . '>koi8-r -&gt; utf-8</option><option value="koi8-r -&gt; windows-1251"' . ($_GET['charset'] == 'koi8-r -> windows-1251' ? ' selected="selected"' : '') . '>koi8-r -&gt; windows-1251</option><option value="koi8-r -&gt; iso-8859-1"' . ($_GET['charset'] == 'koi8-r -> iso-8859-1' ? ' selected="selected"' : '') . '>koi8-r -&gt; iso-8859-1</option><option value="koi8-r -&gt; cp866"' . ($_GET['charset'] == 'koi8-r -> cp866' ? ' selected="selected"' : '') . '>koi8-r -&gt; cp866</option></optgroup></select> <input type="submit" value="' . Language::get('ch') . '"/></div></form></div><div class="rb">' . Language::get('beautifier') . ' (alpha)<form action="edit.php?" style="padding:0;margin:0;"><div><input type="hidden" name="beautify" value="1"/><input type="hidden" name="c" value="' . Registry::get('rCurrent') . '"/><input type="hidden" name="f" value="' . rawurlencode($_GET['f']) . '"/><input type="hidden" name="f" value="' . rawurlencode($_GET['f']) . '"/>' . (Registry::get('lineEditor') ? '<input type="hidden" name="start" value="' . ($start + 1) . '"/><input type="hidden" name="end" value="' . $end . '"/>' : '') . '<input type="submit" value="' . Language::get('beautify') . '" /></div></form></div>';
        break;
}


echo '<div class="rb">' . round(microtime(true) - GMANAGER_START, 4) . ' / ' . Registry::getGmanager()->formatSize(memory_get_peak_usage()) . '<br/></div>' . Registry::get('foot');

?>
