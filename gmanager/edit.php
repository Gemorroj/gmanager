<?php
/**
 * 
 * This software is distributed under the GNU GPL v3.0 license.
 * @author Gemorroj
 * @copyright 2008-2012 http://wapinet.ru
 * @license http://www.gnu.org/licenses/gpl-3.0.txt
 * @link http://wapinet.ru/gmanager/
 * @version 0.8.1 beta
 * 
 * PHP version >= 5.2.3
 * 
 */


if (isset($_POST['get'])) {
    header('Location: http://' . str_replace(array('\\', '//'), '/', $_SERVER['HTTP_HOST'] . dirname($_SERVER['PHP_SELF']) . '/change.php?get=' . rawurlencode($_GET['c'] . ($_GET['f'] ? '&f=' . $_GET['f'] : ''))));
    exit;
}

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

if (isset($_POST['editorLine']) || isset($_POST['editorReplace'])) {
    $_GET['go'] = '';
}

require 'bootstrap.php';


$charset = array('', '');
$full_charset = '';

if ($_GET['charset'] && $_GET['charset'] != 'default') {
    list($charset[0], $charset[1]) = explode(' -> ', $_GET['charset']);
    $full_charset = 'charset=' . htmlspecialchars($charset[0], ENT_COMPAT, 'UTF-8') . '&amp;';
}

Gmanager::getInstance()->sendHeader();

echo str_replace('%title%', Registry::get('hCurrent'), Registry::get('top')) . '<div class="w2">' . Language::get('title_edit') . '<br/></div>' . Gmanager::getInstance()->head();

$archive = Helper_Archive::isArchive(Helper_System::getType(Helper_System::basename(Registry::get('hCurrent'))));

switch (isset($_POST['editorSave']) ? 'save' : $_GET['go']) {
    case 'save':
        if (Registry::get('lineEditor') && isset($_POST['start']) && isset($_POST['end'])) {
            $fill = array_fill($_POST['start'] - 1, $_POST['end'], 1);
            if ($archive == Archive::FORMAT_ZIP) {
                $obj = new Archive;
                $tmp = explode("\n", $obj->setFormat(Archive::FORMAT_ZIP)->setFile(Registry::get('current'))->factory()->lookFile($_GET['f'], true));
            } else {
                $tmp = explode("\n", Gmanager::getInstance()->file_get_contents(Registry::get('current')));
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
            $_POST['text'] = mb_convert_encoding($_POST['text'], $_POST['charset'], 'UTF-8');
        }

        if ($archive == Archive::FORMAT_ZIP) {
            $obj = new Archive;
            echo $obj->setFormat(Archive::FORMAT_ZIP)->setFile(Registry::get('current'))->factory()->setEditFile($_GET['f'], $_POST['text']);
        } else {
            echo Gmanager::getInstance()->createFile(Registry::get('current'), $_POST['text'], $_POST['chmod']);
        }
        break;


    case 'syntax':
        if ($archive == Archive::FORMAT_ZIP) {
            $obj = new Archive;
            $content = $obj->setFormat(Archive::FORMAT_ZIP)->setFile(Registry::get('current'))->factory()->getEditFile($_GET['f']);
            $content = $content['text'];
        } else {
            $content = Gmanager::getInstance()->file_get_contents(Registry::get('current'));
        }

        if (Config::get('Gmanager', 'syntax') == Config::SYNTAX_WAPINET) {
            echo Gmanager::getInstance()->syntaxWapinet($content, $charset);
        } else {
            echo Gmanager::getInstance()->syntax($content, $charset);
        }
        break;


    case 'validator':
        echo Gmanager::getInstance()->validator(Registry::get('current'), $charset);
        break;


    default:
        if (Registry::get('currentType') != 'file' && Registry::get('currentTypeLink') != 'file') {
            echo Helper_View::message(Language::get('not_found'), Helper_View::MESSAGE_ERROR);
            break;
        }
        $start = isset($_POST['start']) ? intval($_POST['start']) - 1 : 0;
        $end = isset($_POST['end']) ? intval($_POST['end']) : Config::get('LineEditor', 'lines');

        if (isset($_POST['editorReplace']) && isset($_POST['from']) && isset($_POST['to'])) {
            if ($archive == Archive::FORMAT_ZIP) {
                $data = Gmanager::getInstance()->replaceZip(Registry::get('current'), $_GET['f'], $_POST['from'], $_POST['to'], isset($_POST['regexp']), isset($_POST['case']));
            } else {
                $data = Gmanager::getInstance()->replace(Registry::get('current'), $_POST['from'], $_POST['to'], isset($_POST['regexp']), isset($_POST['case']));
            }
            echo $data['message'];
            unset($data['message']);
        } else {
            $data = array();
            if ($archive == Archive::FORMAT_ZIP) {
                $obj = new Archive;
                $archData = $obj->setFormat(Archive::FORMAT_ZIP)->setFile(Registry::get('current'))->factory()->getEditFile($_GET['f']);
                $data['content'] = $archData['text'];
            } else {
                $data['content'] = Gmanager::getInstance()->file_get_contents(Registry::get('current'));
            }
        }

        if ($archive == Archive::FORMAT_ZIP) {
            $f = '&amp;f=' . rawurlencode($_GET['f']);
        } else {
            $f = '';
        }

        if ($charset[0] && $data['content']) {
            $data['content'] = mb_convert_encoding($data['content'], $charset[1], $charset[0]);
        }

        if ($_GET['beautify']) {
            $data['content'] = Gmanager::getInstance()->beautify($data['content']);
        }

        $quotes = defined('ENT_IGNORE') ? ENT_COMPAT | ENT_IGNORE : ENT_COMPAT;

        $data['size'] = Helper_View::formatSize(strlen($data['content']));
        $data['lines'] = mb_substr_count($data['content'], "\n") + 1;

        $path = mb_substr(Gmanager::getInstance()->realpath(Registry::get('current')), mb_strlen(IOWrapper::get($_SERVER['DOCUMENT_ROOT'])));

        if (Config::get('Gmanager', 'mode') == 'HTTP' && $path) {
            $http = '<div class="rb"><a href="http://' . $_SERVER['HTTP_HOST'] . str_replace('//', '/', '/' . Helper_View::getRawurl(str_replace('\\', '/', $path))) . '">' . Language::get('look') . '</a><br/></div>';
        } else {
            $http = '';
        }

        if (Registry::get('lineEditor') && $data['lines'] > Config::get('LineEditor', 'minLines')) {
            $isLineEditor = true;
            $i = $start;
            $j = 0;

            $edit = '<table class="pedit"><tbody id="pedit">';

            foreach (array_slice(explode("\n", $data['content']), $start, $end) as $var) {
                $j++;
                $i++;
                $edit .= '<tr id="i' . $j . '"><td class="pedit_l">' . $i . '</td><td class="pedit_c"><input name="line[' . ($i - 1) . '][]" type="text" value="' . htmlspecialchars($var, $quotes, 'UTF-8') . '"/></td><td class="pedit_r"><a title="' . Language::get('add') . '" href="javascript:void(0);" onclick="Gmanager.editAdd(this);">[+]</a> / <a title="' . Language::get('dl') . '" href="javascript:void(0);" onclick="Gmanager.editDel(this);">[-]</a></td></tr>';
            }
            if ($end > $i) {
                $j++;
                $edit .= '<tr id="i' . $j . '"><td class="pedit_l">' . ($i + 1) . '+</td><td class="pedit_c"><input name="line[' . $i . '][]" type="text"/></td><td class="pedit_r"><a title="' . Language::get('add') . '" href="javascript:void(0);" onclick="Gmanager.editAdd(this);">[+]</a> / <a title="' . Language::get('dl') . '" href="javascript:void(0);" onclick="Gmanager.editDel(this);">[-]</a></td></tr>';
            }

            $edit .= '</tbody></table>';
            $appendEdit = '<input onkeypress="return Gmanager.number(event)" style="-wap-input-format:\'*N\';width:24pt;" type="text" value="' . ($start + 1) . '" name="start" /> - <input onkeypress="return Gmanager.number(event)" style="-wap-input-format:\'*N\';width:24pt;" type="text" value="' . $end . '" name="end"/> <input name="editorLine" type="submit" value="' . Language::get('look') . '"/><br/>';
        } else {
            $isLineEditor = false;
            $edit = '<textarea class="lines" name="text" rows="18" cols="64" wrap="' . (Config::get('Editor', 'wrap') ? 'on' : 'off') . '">' . htmlspecialchars($data['content'], $quotes, 'UTF-8') . '</textarea><br/>';
            $appendEdit = '';
        }

        echo '<div class="input">' . $data['lines'] . ' ' . Language::get('lines') . ' / ' . $data['size'] . '<form action="edit.php?c=' . Registry::get('rCurrent') . $f . '" method="post"><div class="edit">' . $edit . '</div><fieldset class="edit">' . $appendEdit . '<input name="editorSave" type="submit" value="' . Language::get('save') . '"/><select name="charset"><option value="utf-8">utf-8</option><option value="windows-1251"' . ($charset[1] == 'windows-1251'? ' selected="selected"' : '') . '>windows-1251</option><option value="iso-8859-1"' . ($charset[1] == 'iso-8859-1'? ' selected="selected"' : '') . '>iso-8859-1</option><option value="cp866"' . ($charset[1] == 'cp866'? ' selected="selected"' : '') . '>cp866</option><option value="koi8-r"' . ($charset[1] == 'koi8-r'? ' selected="selected"' : '') . '>koi8-r</option></select><br/>' . Language::get('chmod') . ' <input onkeypress="return Gmanager.number(event)" type="text" name="chmod" value="' . Gmanager::getInstance()->lookChmod(Registry::get('current')) . '" size="4" maxlength="4" style="-wap-input-format:\'4N\';width:28pt;"/><br/><input type="submit" name="get" value="' . Language::get('get') . '"/><br/>' . ($isLineEditor ? '<a href="edit.php?lineEditor=0&amp;c=' . Registry::get('rCurrent') . $f . '">' . Language::get('basic_editor') . '</a>' : Language::get('basic_editor')) . ' / ' . ($isLineEditor ? Language::get('line_editor') : '<a href="edit.php?lineEditor=1&amp;c=' . Registry::get('rCurrent') . $f . '">' . Language::get('line_editor') . '</a>') . '</fieldset><fieldset class="edit">' . Language::get('replace_from') . '<br/><input type="text" name="from" value="' . (isset($_POST['from']) ? htmlspecialchars($_POST['from']) : '') . '" style="width:128pt;"/>' . Language::get('replace_to') . '<input type="text" name="to" value="' . (isset($_POST['to']) ? htmlspecialchars($_POST['to']) : '') . '" style="width:128pt;"/><br/><input type="checkbox" name="regexp" id="regexp" value="1"' . (isset($_POST['regexp']) ? ' checked="checked"' : '') . '/><label for="regexp">' . Language::get('regexp') . '</label><br/><input type="checkbox" name="case" id="case" value="1"' . (isset($_POST['case']) ? ' checked="checked"' : '') . '/><label for="case">' . Language::get('register') . '</label><br/><input type="submit" name="editorReplace" value="' . Language::get('replace') . '"/></fieldset></form></div>' . $http . '<div class="rb"><a href="edit.php?c=' . Registry::get('rCurrent') . $f . '&amp;' . $full_charset . 'go=syntax">' . Language::get('syntax') . '</a><br/></div>';


        if ($archive == '' && extension_loaded('xml')) {
            echo '<div class="rb"><a href="edit.php?c=' . Registry::get('rCurrent') . '&amp;' . $full_charset . 'go=validator">' . Language::get('validator') . '</a><br/></div>';
        }

        echo '<div class="rb">' . Language::get('charset') . '<form action="edit.php?" style="padding:0;margin:0;"><div><input type="hidden" name="c" value="' . Registry::get('rCurrent') . '"/><input type="hidden" name="f" value="' . rawurlencode($_GET['f']) . '"/>' . ($isLineEditor ? '<input type="hidden" name="start" value="' . ($start + 1) . '"/><input type="hidden" name="end" value="' . $end . '"/>' : '') . '<select name="charset"><option value="default">' . Language::get('charset_no') . '</option><optgroup label="UTF-8"><option value="utf-8 -&gt; windows-1251"' . ($_GET['charset'] == 'utf-8 -> windows-1251' ? ' selected="selected"' : '') . '>utf-8 -&gt; windows-1251</option><option value="utf-8 -&gt; iso-8859-1"' . ($_GET['charset'] == 'utf-8 -> iso-8859-1' ? ' selected="selected"' : '') . '>utf-8 -&gt; iso-8859-1</option><option value="utf-8 -&gt; cp866"' . ($_GET['charset'] == 'utf-8 -> cp866' ? ' selected="selected"' : '') . '>utf-8 -&gt; cp866</option><option value="utf-8 -&gt; koi8-r"' . ($_GET['charset'] == 'utf-8 -> koi8-r' ? ' selected="selected"' : '') . '>utf-8 -&gt; koi8-r</option></optgroup><optgroup label="Windows-1251"><option value="windows-1251 -&gt; utf-8"' . ($_GET['charset'] == 'windows-1251 -> utf-8' ? ' selected="selected"' : '') . '>windows-1251 -&gt; utf-8</option><option value="windows-1251 -&gt; iso-8859-1"' . ($_GET['charset'] == 'windows-1251 -> iso-8859-1' ? ' selected="selected"' : '') . '>windows-1251 -&gt; iso-8859-1</option><option value="windows-1251 -&gt; cp866"' . ($_GET['charset'] == 'windows-1251 -> cp866' ? ' selected="selected"' : '') . '>windows-1251 -&gt; cp866</option><option value="windows-1251 -&gt; koi8-r"' . ($_GET['charset'] == 'windows-1251 -> koi8-r' ? ' selected="selected"' : '') . '>windows-1251 -&gt; koi8-r</option></optgroup><optgroup label="ISO-8859-1"><option value="iso-8859-1 -&gt; utf-8"' . ($_GET['charset'] == 'iso-8859-1 -> utf-8' ? ' selected="selected"' : '') . '>iso-8859-1 -&gt; utf-8</option><option value="iso-8859-1 -&gt; windows-1251"' . ($_GET['charset'] == 'iso-8859-1 -> windows-1251' ? ' selected="selected"' : '') . '>iso-8859-1 -&gt; windows-1251</option><option value="iso-8859-1 -&gt; cp866"' . ($_GET['charset'] == 'iso-8859-1 -> cp866' ? ' selected="selected"' : '') . '>iso-8859-1 -&gt; cp866</option><option value="iso-8859-1 -&gt; koi8-r"' . ($_GET['charset'] == 'iso-8859-1 -> koi8-r' ? ' selected="selected"' : '') . '>iso-8859-1 -&gt; koi8-r</option></optgroup><optgroup label="CP866"><option value="cp866 -&gt; utf-8"' . ($_GET['charset'] == 'cp866 -> utf-8' ? ' selected="selected"' : '') . '>cp866 -&gt; utf-8</option><option value="cp866 -&gt; windows-1251"' . ($_GET['charset'] == 'cp866 -> windows-1251' ? ' selected="selected"' : '') . '>cp866 -&gt; windows-1251</option><option value="cp866 -&gt; iso-8859-1"' . ($_GET['charset'] == 'cp866 -> iso-8859-1' ? ' selected="selected"' : '') . '>cp866 -&gt; iso-8859-1</option><option value="cp866 -&gt; koi8-r"' . ($_GET['charset'] == 'cp866 -> koi8-r' ? ' selected="selected"' : '') . '>cp866 -&gt; koi8-r</option></optgroup><optgroup label="KOI8-R"><option value="koi8-r -&gt; utf-8"' . ($_GET['charset'] == 'koi8-r -> utf-8' ? ' selected="selected"' : '') . '>koi8-r -&gt; utf-8</option><option value="koi8-r -&gt; windows-1251"' . ($_GET['charset'] == 'koi8-r -> windows-1251' ? ' selected="selected"' : '') . '>koi8-r -&gt; windows-1251</option><option value="koi8-r -&gt; iso-8859-1"' . ($_GET['charset'] == 'koi8-r -> iso-8859-1' ? ' selected="selected"' : '') . '>koi8-r -&gt; iso-8859-1</option><option value="koi8-r -&gt; cp866"' . ($_GET['charset'] == 'koi8-r -> cp866' ? ' selected="selected"' : '') . '>koi8-r -&gt; cp866</option></optgroup></select> <input type="submit" value="' . Language::get('ch') . '"/></div></form></div><div class="rb">' . Language::get('beautifier') . ' (alpha)<form action="edit.php?" style="padding:0;margin:0;"><div><input type="hidden" name="beautify" value="1"/><input type="hidden" name="c" value="' . Registry::get('rCurrent') . '"/><input type="hidden" name="f" value="' . rawurlencode($_GET['f']) . '"/><input type="hidden" name="f" value="' . rawurlencode($_GET['f']) . '"/>' . ($isLineEditor ? '<input type="hidden" name="start" value="' . ($start + 1) . '"/><input type="hidden" name="end" value="' . $end . '"/>' : '') . '<input type="submit" value="' . Language::get('beautify') . '" /></div></form></div>';
        break;
}


echo '<div class="rb">' . round(microtime(true) - GMANAGER_START, 4) . ' / ' . Helper_View::formatSize(memory_get_peak_usage()) . '<br/></div>' . Registry::get('foot');
