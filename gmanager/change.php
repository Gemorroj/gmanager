<?php
// encoding = 'utf-8'
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

require 'functions.php';

$_GET['go'] = isset($_GET['go']) ? $_GET['go'] : '';

if (isset($_GET['get']) && $GLOBALS['class']->is_file($_GET['get'])) {
    if (isset($_GET['f'])) {
        $f = get_archive_file($_GET['get'], $_GET['f']);
        $name = basename($_GET['f']);
    } else {
        $f = $GLOBALS['class']->file_get_contents($_GET['get']);
        $name = basename($_GET['get']);
    }

    getf($f, $name, true, false);
    exit;
}


$current = c($_SERVER['QUERY_STRING'], isset($_POST['c']) ? $_POST['c'] : (isset($_GET['c']) ? rawurlencode($_GET['c']) : ''));
$h_current = htmlspecialchars($current);
$r_current = str_replace('%2F', '/', rawurlencode($current));
$realpath = realpath($current);
if ($realpath && $GLOBALS['class']->is_dir($current)) {
    $realpath .= '/';
}
$realpath = $realpath ? htmlspecialchars(str_replace('\\', '/', $realpath)) : $h_current;


send_header($_SERVER['HTTP_USER_AGENT']);


if ($_SERVER['QUERY_STRING'] == 'phpinfo') {
    phpinfo();
    exit;
} else if (isset($_POST['add_archive']) && !isset($_POST['name'])) {
    header('Location: http://' . $_SERVER['HTTP_HOST'] . str_replace(array('\\', '//'), '/', dirname($_SERVER['PHP_SELF']) . '/') . 'index.php?c=' . dirname($current) . '&add_archive=' . $current, true, 301);
    exit;
}

echo str_replace('%dir%', ($_GET['go'] && $_GET['go'] != 1) ? htmlspecialchars($_GET['go'], ENT_NOQUOTES) : (isset($_POST['full_chmod']) ? $GLOBALS['lng']['chmod'] : (isset($_POST['full_del']) ? $GLOBALS['lng']['del'] : (isset($_POST['full_rename']) ? $GLOBALS['lng']['change'] : (isset($_POST['fname']) ? $GLOBALS['lng']['rename'] : (isset($_POST['create_archive']) ? $GLOBALS['lng']['create_archive'] : htmlspecialchars(rawurldecode($_SERVER['QUERY_STRING']), ENT_NOQUOTES)))))), $GLOBALS['top']) . '<div class="w2">' . $GLOBALS['lng']['title_change'] . '<br/></div>' . this($current);

switch ($_GET['go']) {
    default:
        if (!$GLOBALS['class']->file_exists($current)) {
            echo report($GLOBALS['lng']['not_found'], 1);
            break;
        }

        $archive = is_archive(get_type($current));
        if (isset($_GET['f']) && ($archive == 'ZIP' || $archive == 'TAR')) {
            $r_file = str_replace('%2F', '/', rawurlencode($_GET['f']));
            $h_file = htmlspecialchars($_GET['f']);
            echo '<div class="input"><form action="change.php?go=rename&amp;c=' . $r_current . '&amp;f=' . $r_file . '" method="post"><div><input type="hidden" name="arch_name" value="' . $r_file . '"/>' . $GLOBALS['lng']['change_func'] . '<br/><input type="text" name="name" value="' . $h_file . '"/><br/><input type="checkbox" name="overwrite" checked="checked"/>' . $GLOBALS['lng']['overwrite_existing_files'] . '<br/><input type="checkbox" name="del" value="1"/>' . $GLOBALS['lng']['change_del'] . '<br/><input type="submit" value="' . $GLOBALS['lng']['ch'] . '"/></div></form></div>';
        } else {
            if ($GLOBALS['class']->is_dir($current)) {
                $size = format_size(size($current, true));
                $md5 = '';
            } else if ($GLOBALS['class']->is_file($current) || $GLOBALS['class']->is_link($current)) {
                $size = format_size(size($current));
                if ($GLOBALS['mode'] == 'FTP') {
                    $md5 = $GLOBALS['lng']['md5'] . ': ' . md5($GLOBALS['class']->file_get_contents($current));
                } else {
                    $md5 = $GLOBALS['lng']['md5'] . ': ' . md5_file($current);
                }
            }

            echo '<div class="input"><form action="change.php?go=rename&amp;c=' . $r_current . '" method="post"><div>' . $GLOBALS['lng']['change_func'] . '<br/><input type="text" name="name" value="' . $realpath . '"/><br/><input type="checkbox" name="overwrite" checked="checked"/>' . $GLOBALS['lng']['overwrite_existing_files'] . '<br/><input type="checkbox" name="del" value="1"/>' . $GLOBALS['lng']['change_del'] . '<br/><input onkeypress="return number(event)" type="text" size="4" maxlength="4" style="width:28pt;" name="chmod" value="' . look_chmod($current) . '"/>' . $GLOBALS['lng']['change_chmod'] . '<br/><input type="submit" value="' . $GLOBALS['lng']['ch'] . '"/></div></form></div><div>' . $GLOBALS['lng']['sz'] . ': ' . $size . '<br/>' . $md5 . '</div>';
        }
        break;

    case 1:
        $x = isset($_POST['check']) ? sizeof($_POST['check']) : 0;
        if (isset($_POST['fname'])) {
            if (!isset($_POST['name'])) {
                echo '<div class="input"><form action="change.php?go=1&amp;c=' . $r_current . '" method="post"><div>' . $GLOBALS['lng']['rename'] . '<br/>[replace=from,to] - ' . $GLOBALS['lng']['replace'] . '<br/>[name] - ' . $GLOBALS['lng']['name'] . '<br/>[f] - ' . $GLOBALS['lng']['type'] . '<br/>[n=0] - ' . $GLOBALS['lng']['meter'] . '<br/>[date] - ' . $GLOBALS['lng']['date'] . '<br/>[rand=8,16] - ' . $GLOBALS['lng']['rand'] . '<br/><input type="text" name="name" value="[name].[f]"/><br/><select name="register"><option value="">' . $GLOBALS['lng']['str_register_no'] . '</option><option value="1">' . $GLOBALS['lng']['str_register_low'] . '</option><option value="2">' . $GLOBALS['lng']['str_register_up'] . '</option></select>' . $GLOBALS['lng']['str_register'] . '<br/><input type="checkbox" name="overwrite" checked="checked"/>' . $GLOBALS['lng']['overwrite_existing_files'] . '<br/><input name="fname" type="hidden" value="1"/>';

                for ($i = 0; $i < $x; ++$i) {
                    echo '<input name="check[]" type="hidden" value="' . $_POST['check'][$i] . '"/>';
                }

                echo '<input type="submit" value="' . $GLOBALS['lng']['rename'] . '"/></div></form></div>';
            } else {
                for ($i = 0; $i < $x; ++$i) {
                    $_POST['check'][$i] = rawurldecode($_POST['check'][$i]);
                    echo fname($_POST['check'][$i], $_POST['name'], $_POST['register'], $i, isset($_POST['overwrite']));
                }
            }
        } else if (isset($_POST['full_del'])) {
            for ($i = 0; $i < $x; ++$i) {
                $_POST['check'][$i] = rawurldecode($_POST['check'][$i]);
                if ($GLOBALS['class']->is_dir($_POST['check'][$i])) {
                    echo del_dir($_POST['check'][$i] . '/');
                } else {
                    echo del_file($_POST['check'][$i]);
                }
            }

            // echo report('<br/>' . $GLOBALS['lng']['full_del_file_dir_true'], 0);

        } else if (isset($_POST['full_chmod'])) {
            if (!isset($_POST['chmod'])) {
                echo '<div class="input"><form action="change.php?go=1&amp;c=' . $r_current . '" method="post"><div><input onkeypress="return number(event)" type="text" size="4" maxlength="4" style="width:28pt;" name="chmod[]" value="0644"/>' . $GLOBALS['lng']['change_chmod'] . ' ' . $GLOBALS['lng']['of files'] . '<br/><input onkeypress="return number(event)" type="text" size="4" maxlength="4" style="width:28pt;" name="chmod[]" value="0755"/>' . $GLOBALS['lng']['change_chmod'] . ' ' . $GLOBALS['lng']['of folders'] . '<br/><input name="full_chmod" type="hidden" value="1"/>';
                for ($i = 0; $i < $x; ++$i) {
                    echo '<input name="check[]" type="hidden" value="' . $_POST['check'][$i] . '"/>';
                }
                echo '<input type="submit" value="' . $GLOBALS['lng']['ch'] . '"/></div></form></div>';
            } else {
                for ($i = 0; $i < $x; ++$i) {
                    $_POST['check'][$i] = rawurldecode($_POST['check'][$i]);
                    if ($GLOBALS['class']->is_dir($_POST['check'][$i])) {
                        echo rechmod($_POST['check'][$i], $_POST['chmod'][1]);
                    } else {
                        echo rechmod($_POST['check'][$i], $_POST['chmod'][0]);
                    }
                }
            }
        } else if (isset($_REQUEST['mega_full_extract'])) {
            if (!isset($_POST['name']) || !isset($_POST['chmod'])) {
                echo '<div class="input"><form action="change.php?go=1&amp;c=' . $r_current . '" method="post"><div>' . $GLOBALS['lng']['change_name'] . '<br/><input type="text" name="name" value="' . htmlspecialchars(dirname($current), ENT_COMPAT) . '/"/><br/><input type="checkbox" name="overwrite" checked="checked"/>' . $GLOBALS['lng']['overwrite_existing_files'] . '<br/><input onkeypress="return number(event)" type="text" name="chmod[]" size="4" maxlength="4" style="width:28pt;" value="0644"/>' . $GLOBALS['lng']['change_chmod'] . ' ' . $GLOBALS['lng']['of files'] . '<br/><input onkeypress="return number(event)" type="text" name="chmod[]" size="4" maxlength="4" style="width:28pt;" value="0755"/>' . $GLOBALS['lng']['change_chmod'] . ' ' . $GLOBALS['lng']['of folders'] . '<br/><input name="mega_full_extract" type="hidden" value="1"/><input type="submit" value="' . $GLOBALS['lng']['extract_archive'] . '"/></div></form></div>';
            } else {
                $archive = is_archive(get_type(basename($h_current)));

                if ($archive == 'ZIP') {
                    echo extract_zip_archive($current, $_POST['name'], $_POST['chmod'], isset($_POST['overwrite']));
                } else if ($archive == 'TAR') {
                    echo extract_tar_archive($current, $_POST['name'], $_POST['chmod'], isset($_POST['overwrite']));
                } else if ($archive == 'GZ') {
                    echo gz_extract($current, $_POST['name'], $_POST['chmod'], isset($_POST['overwrite']));
                } else if ($archive == 'RAR' && extension_loaded('rar')) {
                    echo extract_rar_archive($current, $_POST['name'], $_POST['chmod'], isset($_POST['overwrite']));
                }
            }
        } else if (isset($_POST['full_extract'])) {
            if (!isset($_POST['name']) || !isset($_POST['chmod'])) {
                echo '<div class="input"><form action="change.php?go=1&amp;c=' . $r_current . '" method="post"><div>' . $GLOBALS['lng']['change_name'] . '<br/><input type="text" name="name" value="' . htmlspecialchars(dirname($current), ENT_COMPAT) . '/"/><br/><input type="checkbox" name="overwrite" checked="checked"/>' . $GLOBALS['lng']['overwrite_existing_files'] . '<br/><input onkeypress="return number(event)" type="text" name="chmod" size="4" maxlength="4" style="width:28pt;" value="0755"/>' . $GLOBALS['lng']['change_chmod'] . '<br/><input name="full_extract" type="hidden" value="1"/>';
                for ($i = 0; $i < $x; ++$i) {
                    echo '<input name="check[]" type="hidden" value="' . $_POST['check'][$i] . '"/>';
                }
                echo '<input type="submit" value="' . $GLOBALS['lng']['extract_archive'] . '"/></div></form></div>';
            } else {
                $_POST['check'] = array_map('rawurldecode', $_POST['check']);

                $archive = is_archive(get_type(basename($h_current)));

                if ($archive == 'ZIP') {
                    echo extract_zip_file($current, $_POST['name'], $_POST['chmod'], $_POST['check'], isset($_POST['overwrite']));
                } else if ($archive == 'TAR') {
                    echo extract_tar_file($current, $_POST['name'], $_POST['chmod'], $_POST['check'], isset($_POST['overwrite']));
                } else if ($archive == 'RAR' && extension_loaded('rar')) {
                    echo extract_rar_file($current, $_POST['name'], $_POST['chmod'], $_POST['check'], isset($_POST['overwrite']));
                }
            }
        } else if (isset($_POST['gz_extract'])) {
            if (!isset($_POST['name']) || !isset($_POST['chmod'])) {
                echo '<div class="input"><form action="change.php?go=1&amp;c=' . $r_current . '" method="post"><div>' . $GLOBALS['lng']['change_name'] . '<br/><input type="text" name="name" value="' . htmlspecialchars(dirname($current), ENT_COMPAT) . '/"/><br/><input type="checkbox" name="overwrite" checked="checked"/>' . $GLOBALS['lng']['overwrite_existing_files'] . '<br/><input onkeypress="return number(event)" type="text" name="chmod" size="4" maxlength="4" style="width:28pt;" value="0755"/>' . $GLOBALS['lng']['change_chmod'] . '<br/><input name="gz_extract" type="hidden" value="1"/><input type="submit" value="' . $GLOBALS['lng']['extract_archive'] . '"/></div></form></div>';
            } else {
                echo gz_extract($current, $_POST['name'], $_POST['chmod'], isset($_POST['overwrite']));
            }
        } else if (isset($_POST['create_archive'])) {
            if (!isset($_POST['name'])) {
                echo '<div class="input"><form action="change.php?go=1&amp;c=' . $r_current . '" method="post"><div>' . $GLOBALS['lng']['change_name'] . '<br/><input type="text" name="name" value="' . $h_current . 'archive.zip"/><br/><input onkeypress="return number(event)" type="text" name="chmod" size="4" maxlength="4" style="width:28pt;" value="0644"/>' . $GLOBALS['lng']['change_chmod'] . '<br/><input type="checkbox" name="overwrite" checked="checked"/>' . $GLOBALS['lng']['overwrite_existing_files'] . '<br/>' . $GLOBALS['lng']['comment_archive'] . '<br/><textarea name="comment" rows="2" cols="24"></textarea><br/><input name="create_archive" type="hidden" value="1"/>';
                for ($i = 0; $i < $x; ++$i) {
                    echo '<input name="check[]" type="hidden" value="' . $_POST['check'][$i] . '"/>';
                }
                echo '<input type="submit" value="' . $GLOBALS['lng']['create_archive'] . '"/></div></form></div>';
            } else {
                $_POST['check'] = array_map('rawurldecode', $_POST['check']);
                echo create_zip_archive($_POST['name'], $_POST['chmod'], $_POST['check'], $_POST['comment'], isset($_POST['overwrite']));
            }
        } else if (isset($_POST['add_archive'])) {
            if (isset($_POST['dir'])) {
                $_POST['check'] = array_map('rawurldecode', $_POST['check']);
                $_POST['dir'] = rawurldecode($_POST['dir']);
                $_POST['add_archive'] = rawurldecode($_POST['add_archive']);

                $archive = is_archive(get_type(basename($_POST['add_archive'])));

                if ($archive == 'ZIP') {
                    echo add_zip_archive($_POST['add_archive'], $_POST['check'], $_POST['dir']);
                } else if ($archive == 'TAR') {
                    echo add_tar_archive($_POST['add_archive'], $_POST['check'], $_POST['dir']);
                } else if ($archive == 'RAR') {
                    echo add_rar_archive($_POST['add_archive'], $_POST['check'], $_POST['dir']);
                }
            } else {
                echo '<div class="input"><form action="change.php?go=1&amp;c=' . $r_current . '" method="post"><div>' . $GLOBALS['lng']['add_archive_dir'] . '<br/><input type="text" name="dir" value="./"/><br/><input name="add_archive" type="hidden" value="' . $_POST['add_archive'] . '"/>';
                for ($i = 0; $i < $x; ++$i) {
                    echo '<input name="check[]" type="hidden" value="' . $_POST['check'][$i] . '"/>';
                }
                echo '<input type="submit" name="name" value="' . $GLOBALS['lng']['add_archive'] . '"/></div></form></div>';
            }
        } else if (isset($_POST['full_rename'])) {
            if (!isset($_GET['go2'])) {
                echo '<div class="input"><form action="change.php?go=1&amp;go2=1&amp;c=' . $r_current . '" method="post"><div>' . $GLOBALS['lng']['change_func2'] . '<br/><input type="text" name="name" value="' . $realpath . '"/><br/><input type="checkbox" name="overwrite" checked="checked"/>' . $GLOBALS['lng']['overwrite_existing_files'] . '<br/><input type="checkbox" name="del" value="1"/>' . $GLOBALS['lng']['change_del'] . '<br/><input name="full_rename" type="hidden" value="1"/>';
                for ($i = 0; $i < $x; ++$i) {
                    echo '<input name="check[]" type="hidden" value="' . $_POST['check'][$i] . '"/>';
                }
                echo '<input type="submit" value="' . $GLOBALS['lng']['ch'] . '"/></div></form></div>';
            } else {
                for ($i = 0; $i < $x; ++$i) {
                    $_POST['check'][$i] = rawurldecode($_POST['check'][$i]);
                    echo frename($_POST['check'][$i], str_replace('//', '/', $_POST['name'] . '/' . basename($_POST['check'][$i])), '', isset($_POST['del']), $_POST['name'], isset($_POST['overwrite']));
                }
            }
        } else if (isset($_POST['del_archive'])) {
                $archive = is_archive(get_type(basename($current)));
                $_POST['check'] = array_map('rawurldecode', $_POST['check']);

                if ($archive == 'ZIP') {
                    foreach ($_POST['check'] AS $ch) {
                        echo del_zip_archive($current, $ch);
                    }
                } else if ($archive == 'TAR') {
                    foreach ($_POST['check'] AS $ch) {
                        echo del_tar_archive($current, $ch);
                    }
                }
        }
        break;

    case 'del':
        if ($GLOBALS['class']->is_dir($current)) {
            echo del_dir($current);
        } else {
            echo del_file($current);
        }
        break;

    case 'chmod':
        if (!isset($_POST['chmod'])) {
            echo '<div class="input"><form action="change.php?go=chmod&amp;c=' . $r_current . '" method="post"><div><input onkeypress="return number(event)" type="text" size="4" maxlength="4" style="width:28pt;" name="chmod" value="' . look_chmod($current) . '"/>' . $GLOBALS['lng']['change_chmod'] . '<br/><input type="submit" value="' . $GLOBALS['lng']['ch'] . '"/></div></form></div>';
        } else {
            echo rechmod($current, $_POST['chmod']);
        }
        break;

    case 'create_dir':
        if (!isset($_POST['name'])) {
            echo '<div class="input"><form action="change.php?go=create_dir&amp;c=' . $r_current . '" method="post"><div>' . $GLOBALS['lng']['change_name'] . '<br/><input type="text" name="name" value="dir"/><br/><input onkeypress="return number(event)" type="text" name="chmod" size="4" maxlength="4" style="width:28pt;" value="0755"/>' . $GLOBALS['lng']['change_chmod'] . '<br/><input type="submit" value="' . $GLOBALS['lng']['cr'] . '"/></div></form></div>';
        } else {
            echo create_dir($current . $_POST['name'], $_POST['chmod']);
        }
        break;

    case 'create_file':
        include 'pattern.dat';
        if (!isset($_POST['name'])) {
            echo '<div class="input"><form action="change.php?go=create_file&amp;c=' . $r_current . '" method="post"><div>' . $GLOBALS['lng']['change_name'] . '<br/><input type="text" name="name" value="file.php"/><br/><select name="ptn">';
            $all = sizeof($pattern);
            for ($i = 0; $i < $all; ++$i) {
                echo '<option value="' . $i . '">' . $pattern[$i][0] . '</option>';
            }
            echo '</select>' . $GLOBALS['lng']['pattern'] . '<br/><input onkeypress="return number(event)" type="text" name="chmod" size="4" maxlength="4" style="width:28pt;" value="0644"/>' . $GLOBALS['lng']['change_chmod'] . '<br/><input type="submit" value="' . $GLOBALS['lng']['cr'] . '"/></div></form></div>';
        } else {
            if ($GLOBALS['class']->file_exists($current . $_POST['name']) && !isset($_POST['a'])) {
                echo '<div class="red">' . $GLOBALS['lng']['warning'] . '<br/></div><form action="change.php?go=create_file&amp;c=' . $r_current . '" method="post"><div><input type="hidden" name="name" value="' . htmlspecialchars($_POST['name'], ENT_COMPAT) . '"/><input type="hidden" name="ptn" value="' . htmlspecialchars($_POST['ptn'], ENT_COMPAT) . '"/><input type="hidden" name="chmod" value="' . htmlspecialchars($_POST['chmod'], ENT_COMPAT) . '"/><input type="hidden" name="a" value="1"/><input type="submit" value="' . $GLOBALS['lng']['ch'] . '"/></div></form>';
            } else {
                if ($GLOBALS['realname']) {
                    $realpath = $realpath . htmlspecialchars($_POST['name'], ENT_NOQUOTES, 'UTF-8');
                } else {
                    $realpath = $h_current . htmlspecialchars($_POST['name'], ENT_NOQUOTES, 'UTF-8');
                }

                echo '<div class="border">' . $GLOBALS['lng']['file'] . ' <strong><a href="edit.php?' . $r_current . rawurlencode($_POST['name']) . '">' . $realpath . '</a></strong> (' . $_POST['chmod'] . ')<br/></div>' . create_file($current . $_POST['name'], $pattern[intval($_POST['ptn'])][1], $_POST['chmod']);
            }
        }
        break;

    case 'rename':
        if (@$_POST['name'] != '') {
            $archive = is_archive(get_type($current));
            $if = isset($_GET['f']);
            if ($if && $archive == 'ZIP') {
                echo rename_zip_file($current, $_POST['name'], rawurldecode($_POST['arch_name']), isset($_POST['del']), isset($_POST['overwrite']));
            } else if ($if && $archive == 'TAR') {
                echo rename_tar_file($current, $_POST['name'], rawurldecode($_POST['arch_name']), isset($_POST['del']), isset($_POST['overwrite']));
            } else {
                echo frename($current, $_POST['name'], @$_POST['chmod'], isset($_POST['del']), $_POST['name'], isset($_POST['overwrite']));
                if (@$_POST['chmod']) {
                    echo rechmod($_POST['name'], $_POST['chmod']);
                }
            }
        } else {
            echo report($GLOBALS['lng']['filename_empty'], 1);
        }
        break;

    case 'del_zip_archive':
        echo del_zip_archive($_GET['c'], $_GET['f']);
        break;

    case 'del_tar_archive':
        echo del_tar_archive($_GET['c'], $_GET['f']);
        break;

    case 'upload':
        if ((((!isset($_POST['url']) || $_POST['url'] == 'http://' || $_POST['url'] == '') && (!isset($_FILES['f']) || $_FILES['f']['error'][0])) && !isset($_POST['f'])) || !isset($_POST['name']) || !isset($_POST['chmod'])) {
            echo '<div class="input"><form action="change.php?go=upload&amp;c=' . $r_current . '" method="post" enctype="multipart/form-data"><div>' . $GLOBALS['lng']['url'] . '<br/><textarea name="url" type="text" rows="3" cols="48" wrap="off">http://</textarea><br/>' . $GLOBALS['lng']['headers'] . '<br/><textarea rows="3" cols="32" name="headers">User-Agent: ' . htmlspecialchars(@$_SERVER['HTTP_USER_AGENT'], ENT_NOQUOTES) . "\n" . 'Cookie: ' . "\n" . 'Referer: ' . "\n" . 'Accept: ' . htmlspecialchars(@$_SERVER['HTTP_ACCEPT'], ENT_NOQUOTES) . "\n" . 'Accept-Charset: ' . htmlspecialchars(@$_SERVER['HTTP_ACCEPT_CHARSET'], ENT_NOQUOTES) . "\n" . 'Accept-Language: ' . htmlspecialchars(@$_SERVER['HTTP_ACCEPT_LANGUAGE'], ENT_NOQUOTES) . "\n" . 'Connection: Close' . "\n" . '</textarea><br/>' . $GLOBALS['lng']['file'] . ' (' . ini_get('upload_max_filesize') . ') <a href="#" onclick="files(1);">[+]</a> / <a href="#" onclick="files(0);">[-]</a><br/><div id="fl"><input type="file" name="f[]"/><br/></div>' . $GLOBALS['lng']['name'] . '<br/><input type="text" name="name" value="' . $h_current . '"/><br/><input onkeypress="return number(event)" type="text" name="chmod" value="0644" size="4" maxlength="4" style="width:28pt;"/>' . $GLOBALS['lng']['change_chmod'] . '<br/><input type="text" name="set_time_limit" value="3600" size="5" style="width:28pt;"/>' . $GLOBALS['lng']['set_time_limit'] . '<br/><input type="checkbox" name="ignore_user_abort" checked="checked" />' . $GLOBALS['lng']['ignore_user_abort'] . '<br/><br/><input type="submit" value="' . $GLOBALS['lng']['upload'] . '"/></div></form></div>';
        } else {
            if (!$_FILES['f']['error'][0]) {
                $all = sizeof($_FILES['f']['tmp_name']);
                if ($all > 1) {
                    if (substr($_POST['name'], -1) != '/') {
                        $_POST['name'] .= '/';
                    }
                }

                for ($i = 0; $i < $all; ++$i ) {
                    echo upload_files($_FILES['f']['tmp_name'][$i], $_FILES['f']['name'][$i], $_POST['name'], $_POST['chmod']);
                }
            } else {
                echo upload_url($_POST['url'], $_POST['name'], $_POST['chmod'], $_POST['headers']);
            }
        }
        break;

    case 'mod':
        $safe = strtolower(ini_get('safe_mode'));
        echo '<div class="red"><ul><li><a href="change.php?go=search&amp;c=' . $r_current . '">' . $GLOBALS['lng']['search'] . '</a></li><li><a href="change.php?go=eval&amp;c=' . $r_current . '">' . $GLOBALS['lng']['eval'] . '</a></li><li><a href="change.php?go=cmd&amp;c=' . $r_current . '">' . $GLOBALS['lng']['cmd'] . '</a></li><li><a href="change.php?go=sql&amp;c=' . $r_current . '">' . $GLOBALS['lng']['sql'] . '</a></li><li><a href="change.php?go=tables&amp;c=' . $r_current . '">' . $GLOBALS['lng']['tables'] . '</a></li><li><a href="change.php?go=installer&amp;c=' . $r_current . '">' . $GLOBALS['lng']['create_sql_installer'] . '</a></li><li><a href="change.php?go=scan&amp;c=' . $r_current . '">' . $GLOBALS['lng']['scan'] . '</a></li><li><a href="change.php?go=send_mail&amp;c=' . $r_current . '">' . $GLOBALS['lng']['send_mail'] . '</a></li><li><a href="change.php?phpinfo">' . $GLOBALS['lng']['phpinfo'] . '</a> (' . PHP_VERSION . ')</li><li><a href="change.php?go=new_version&amp;c=' . $r_current . '">' . $GLOBALS['lng']['new_version'] . '</a></li></ul><span style="color:#000;">&#187;</span> Safe Mode: ' . ($safe == 1 || $safe == 'on' ? '<span style="color:#b00;">ON</span>' : '<span style="color:#0f0;">OFF</span>') . '<br/><span style="color:#000;">&#187;</span> ' . htmlspecialchars($_SERVER['SERVER_SOFTWARE'], ENT_NOQUOTES) . '<br/><span style="color:#000;">&#187;</span> ' . htmlspecialchars(php_uname(), ENT_NOQUOTES) . '<br/><span style="color:#000;">&#187;</span> ' . $GLOBALS['lng']['disk_total_space'] . ' ' . format_size(@disk_total_space($_SERVER['DOCUMENT_ROOT'])) . '; ' . $GLOBALS['lng']['disk_free_space'] . ' ' . format_size(@disk_free_space($_SERVER['DOCUMENT_ROOT'])) . '<br/><span style="color:#000;">&#187;</span> ' . strftime('%d.%m.%Y / %H') . '<span style="text-decoration:blink;">:</span>' . strftime('%M') . '<br/></div>';
    break;

    case 'new_version':
    
        $new = getData('http://wapinet.ru/gmanager/gmanager.txt');
        if ($new['body']) {
            if (version_compare($new['body'], $GLOBALS['version'], '<=')) {
                echo report($GLOBALS['lng']['version_new'] . ': ' . $new['body'] . '<br/>' . $GLOBALS['lng']['version_old'] . ': ' . $GLOBALS['version'] . '<br/>' . $GLOBALS['lng']['new_version_false'], 0);
            } else {
                echo report($GLOBALS['lng']['version_new'] . ': ' . $new['body'] . '<br/>' . $GLOBALS['lng']['version_old'] . ': ' . $GLOBALS['version'] . '<br/>' . $GLOBALS['lng']['new_version_true'] . '<br/>&#187; <a href="http://wapinet.ru/gmanager/gmanager.zip">' . $GLOBALS['lng']['get'] . '</a><br/><input name="" value="http://wapinet.ru/gmanager/gmanager.zip" size="39"/>', 1);
            }
        } else {
            echo report($GLOBALS['lng']['not_connect'], 2);
        }
        break;

    case 'scan':
        if (!isset($_POST['url']) || $_POST['url'] == 'http://') {
            echo '<div class="input"><form action="change.php?go=scan&amp;c=' . $r_current . '" method="post"><div>' . $GLOBALS['lng']['url'] . '<br/><input type="text" name="url" value="http://"/><br/>' . $GLOBALS['lng']['headers'] . '<br/><textarea rows="3" cols="32" name="headers">User-Agent: ' . htmlspecialchars(@$_SERVER['HTTP_USER_AGENT'], ENT_NOQUOTES) . "\n" . 'Cookie: ' . "\n" . 'Referer: ' . "\n" . 'Accept: ' . htmlspecialchars(@$_SERVER['HTTP_ACCEPT'], ENT_NOQUOTES) . "\n" . 'Accept-Charset: ' . htmlspecialchars(@$_SERVER['HTTP_ACCEPT_CHARSET'], ENT_NOQUOTES) . "\n" . 'Accept-Language: ' . htmlspecialchars(@$_SERVER['HTTP_ACCEPT_LANGUAGE'], ENT_NOQUOTES) . "\n" . 'Connection: Close' . "\n" . '</textarea><br/>POST<br/><input type="text" name="post"/><br/><input type="checkbox" name="oh"/>' . $GLOBALS['lng']['only_headers'] . '<br/><input type="submit" value="' . $GLOBALS['lng']['look'] . '"/></div></form></div>';
        } else {
            $only_headers = isset($_POST['oh']);
            if ($url = getData($_POST['url'], $_POST['headers'], $only_headers, $_POST['post'])) {
                $url = $url['headers'] . ($only_headers ? '' : "\r\n\r\n" . $url['body']);
                echo '<div class="code">IP: ' . implode(', ', gethostbynamel(parse_url($_POST['url'], PHP_URL_HOST))) . '<br/></div>' . code($url, 0);
            } else {
                echo report($GLOBALS['lng']['not_connect'], 2);
            }
        }
        break;

    case 'send_mail':
        if (!isset($_POST['from']) || !isset($_POST['theme']) || !isset($_POST['mess']) || !isset($_POST['to'])) {
            echo '<div class="input"><form action="change.php?go=send_mail&amp;c=' . $r_current . '" method="post"><div>' . $GLOBALS['lng']['mail_to'] . '<br/><input type="text" name="to" value="' . (isset($_POST['to']) ? htmlspecialchars($_POST['to'], ENT_COMPAT) : '@') . '"/><br/>' . $GLOBALS['lng']['mail_from'] . '<br/><input type="text" name="from" value="admin@' . $_SERVER['HTTP_HOST'] . '"/><br/>' . $GLOBALS['lng']['mail_theme'] . '<br/><input type="text" name="theme" value="' . (isset($_POST['theme']) ? htmlspecialchars($_POST['theme'], ENT_COMPAT) : 'Hello') . '"/><br/>' . $GLOBALS['lng']['mail_mess'] . '<br/><textarea name="mess" rows="8" cols="48">' . (isset($_POST['mess']) ? htmlspecialchars($_POST['mess'], ENT_NOQUOTES) : '') . '</textarea><br/><input type="submit" value="' . $GLOBALS['lng']['send_mail'] . '"/></div></form></div>';
        } else {
            echo send_mail($_POST['theme'], $_POST['mess'], $_POST['to'], $_POST['from']);
        }
        break;

    case 'eval':
        if (isset($_POST['eval'])) {
            echo show_eval($_POST['eval']);
            $v = htmlspecialchars($_POST['eval'], ENT_NOQUOTES);
        } else {
            $v = '';
        }
        echo '<div class="input"><form action="change.php?go=eval&amp;c=' . $r_current . '" method="post"><div>' . $GLOBALS['lng']['php_code'] . '<br/><textarea name="eval" rows="10" cols="48">' . $v . '</textarea><br/><input type="submit" value="' . $GLOBALS['lng']['eval_go'] . '"/></div></form></div>';
        break;

    case 'search':
        if (isset($_POST['search']) && $_POST['search'] != '') {
            $v = htmlspecialchars($_POST['search'], ENT_NOQUOTES);
            if ($GLOBALS['string']) {
                echo '<div><form action="change.php?" method="get"><div><input type="text" name="c" value="' . $realpath . '"/><br/><input type="hidden" name="go" value="search"/><input type="submit" value="' . $GLOBALS['lng']['go'] . '"/></div></form></div>';
            }
            echo '<form action="change.php?c=' . $r_current . '&amp;go=1" method="post"><div class="telo"><table><tr><th>' . $GLOBALS['lng']['ch_index'] . '</th>' . ($GLOBALS['index']['name'] ? '<th>' . $GLOBALS['lng']['name'] . '</th>' : '') . ($GLOBALS['index']['down'] ? '<th>' . $GLOBALS['lng']['get'] . '</th>' : '') . ($GLOBALS['index']['type'] ? '<th>' . $GLOBALS['lng']['type'] . '</th>' : '') . ($GLOBALS['index']['size'] ? '<th>' . $GLOBALS['lng']['size'] . '</th>' : '') . ($GLOBALS['index']['change'] ? '<th>' . $GLOBALS['lng']['change'] . '</th>' : '') . ($GLOBALS['index']['del'] ? '<th>' . $GLOBALS['lng']['del'] . '</th>' : '') . ($GLOBALS['index']['chmod'] ? '<th>' . $GLOBALS['lng']['chmod'] . '</th>' : '') . ($GLOBALS['index']['date'] ? '<th>' . $GLOBALS['lng']['date'] . '</th>' : '') . ($GLOBALS['index']['uid'] ? '<th>' . $GLOBALS['lng']['uid'] . '</th>' : '') . ($GLOBALS['index']['n'] ? '<th>' . $GLOBALS['lng']['n'] . '</th>' : '') . '</tr>' . search($_POST['where'], $_POST['search'], isset($_POST['in']), isset($_POST['register']), isset($_POST['hex']), $_POST['size'] * 1048576, isset($_POST['archive'])) . '<tr><td class="w" colspan="' . (array_sum($GLOBALS['index']) + 1) . '" style="text-align:left;padding:0 0 0 1%;"><input type="checkbox" value="check" onclick="check(this.form,\'check[]\',this.checked)"/>' . $GLOBALS['lng']['check'] . '</td></tr></table><div class="ch"><input type="submit" name="full_chmod" value="' . $GLOBALS['lng']['chmod'] . '"/><input type="submit" name="full_del" value="' . $GLOBALS['lng']['del'] . '"/><input type="submit" name="full_rename" value="' . $GLOBALS['lng']['change'] . '"/><input type="submit" name="create_archive" value="' . $GLOBALS['lng']['create_archive'] . '"/></div></div></form><div class="rb">' . $GLOBALS['lng']['create'] . '<a href="change.php?go=create_file&amp;c=' . $r_current . '">' . $GLOBALS['lng']['file'] . '</a> / <a href="change.php?go=create_dir&amp;c=' . $r_current . '">' . $GLOBALS['lng']['dir'] . '</a><br/></div><div class="rb"><a href="change.php?go=upload&amp;c=' . $r_current . '">' . $GLOBALS['lng']['upload'] . '</a><br/></div><div class="rb"><a href="change.php?go=mod&amp;c=' . $r_current . '">' . $GLOBALS['lng']['mod'] . '</a><br/></div>';
        } else {
            $v = '';
        }
        echo '<div class="input"><form action="change.php?go=search&amp;c=' . $r_current . '" method="post"><div>' . $GLOBALS['lng']['where_search'] . '<br/><input type="text" name="where" value="' . (isset($_POST['where']) ? htmlspecialchars($_POST['where'], ENT_COMPAT) : $realpath) . '"/><br/>' . $GLOBALS['lng']['what_search'] . '<br/><input type="text" name="search" value="' . $v . '"/><br/><fieldset><legend><input type="checkbox" name="in"' . (@$_POST['in'] ? ' checked="checked"' : '') . '/> ' . $GLOBALS['lng']['in_text'] . '</legend><input type="text" name="size" value="' . (isset($_POST['size']) ? htmlspecialchars($_POST['size']) : 8) . '" style="width: 28pt;" size="4" onkeypress="return number(event)"/> ' . $GLOBALS['lng']['search_limit'] . '<br/><input type="checkbox" name="archive"' . (isset($_POST['archive']) ? ' checked="checked"' : '') . '/> ' . $GLOBALS['lng']['search_archives'] . ' (GZ)<br/></fieldset><input type="checkbox" name="register"' . (isset($_POST['register']) ? ' checked="checked"' : '') . '/>' . $GLOBALS['lng']['register'] . '<br/><input type="checkbox" name="hex"' . (isset($_POST['hex']) ? ' checked="checked"' : '') . '/>HEX<br/><input type="submit" value="' . $GLOBALS['lng']['eval_go'] . '"/></div></form></div>';
        break;

    case 'sql':
        $_POST['sql'] = isset($_POST['sql']) ? trim($_POST['sql']) : '';
        if (isset($_POST['name']) && isset($_POST['host'])) {
            if (isset($_POST['backup'])) {
                if (@$_POST['file']) {
                    echo sql_backup($_POST['name'], $_POST['pass'], $_POST['host'], $_POST['db'], $_POST['sql'], $_POST['charset'], array('tables' => @array_map('rawurldecode', @$_POST['tables']), 'data' => @array_map('rawurldecode', @$_POST['data']), 'file' => $_POST['file']));
                } else {
                    $tables = sql_backup($_POST['name'], $_POST['pass'], $_POST['host'], $_POST['db'], $_POST['sql'], $_POST['charset'], false);
                    echo '<div class="input"><form action="change.php?go=sql&amp;c=' . $r_current . '" method="post"><div>' . $GLOBALS['lng']['mysql_backup_structure'] . '<br/><select name="tables[]" multiple="true" size="5">' . $tables . '</select><br/>' . $GLOBALS['lng']['mysql_backup_data'] . '<br/><select name="data[]" multiple="true" size="5">' . $tables . '</select><br/>' . $GLOBALS['lng']['file'] . '<br/><input type="text" name="file" value="' . $h_current . 'backup_' . htmlspecialchars($_POST['db']) . '.sql"/><br/><input type="hidden" name="name" value="' . htmlspecialchars($_POST['name']) . '"/><input type="hidden" name="pass" value="' . htmlspecialchars($_POST['pass']) . '"/><input type="hidden" name="host" value="' . htmlspecialchars($_POST['host']) . '"/><input type="hidden" name="db" value="' . htmlspecialchars($_POST['db']) . '"/><input type="hidden" name="charset" value="' . htmlspecialchars($_POST['charset']) . '"/><input type="submit" name="backup" value="' . $GLOBALS['lng']['mysql_backup'] . '"/></div></form></div>';
                }
            } else {
                include 'pattern.dat';
                $tmp = '<select id="ptn" onchange="paste(this.value);">';
                $all = sizeof($sql_ptn);
                for ($i = 0; $i < $all; ++$i) {
                    $tmp .= '<option value="' . htmlspecialchars($sql_ptn[$i][1], ENT_COMPAT) . '">' . $sql_ptn[$i][0] . '</option>';
                }
                $tmp .= '</select>';

                if (!$_POST['sql'] && !$_POST['db']) {
                    $_POST['sql'] = 'SHOW DATABASES';
                } else if (!$_POST['sql']) {
                    $_POST['sql'] = 'SHOW TABLES';
                }
                echo '<div>&#160;' . $_POST['name'] . ($_POST['db'] ? ' =&gt; ' . $_POST['db'] : '') . '<br/></div>' . sql($_POST['name'], $_POST['pass'], $_POST['host'], $_POST['db'], $_POST['sql'], $_POST['charset']) . '<div><form action=""><div><textarea rows="' . (substr_count($_POST['sql'], "\n") + 1) . '" cols="48">' . htmlspecialchars($_POST['sql'], ENT_NOQUOTES) . '</textarea></div></form></div><div class="input"><form action="change.php?go=sql&amp;c=' . $r_current . '" method="post" id="post"><div>' . $GLOBALS['lng']['sql_query'] . ' ' . $tmp . '<br/><textarea id="sql" name="sql" rows="6" cols="48"></textarea><br/><input type="hidden" name="name" value="' . htmlspecialchars($_POST['name']) . '"/><input type="hidden" name="pass" value="' . htmlspecialchars($_POST['pass']) . '"/><input type="hidden" name="host" value="' . htmlspecialchars($_POST['host']) . '"/><input type="hidden" name="db" value="' . htmlspecialchars($_POST['db']) . '"/><input type="hidden" name="charset" value="' . htmlspecialchars($_POST['charset']) . '"/><input type="submit" value="' . $GLOBALS['lng']['sql'] . '"/>' . ($_POST['db'] ? ' <input type="submit" name="backup" value="' . $GLOBALS['lng']['mysql_backup'] . '"/>' : '') . '</div></form></div>';
            }
        } else {
            echo '<div class="input"><form action="change.php?go=sql&amp;c=' . $r_current . '" method="post" id="post"><div>' . $GLOBALS['lng']['mysql_user'] . '<br/><input type="text" name="name" value=""/><br/>' . $GLOBALS['lng']['mysql_pass'] . '<br/><input type="text" name="pass"/><br/>' . $GLOBALS['lng']['mysql_host'] . '<br/><input type="text" name="host" value="localhost"/><br/>' . $GLOBALS['lng']['mysql_db'] . '<br/><input type="text" name="db"/><br/>' . $GLOBALS['lng']['charset'] . '<br/><input type="text" name="charset" value="utf8"/><br/>' . $GLOBALS['lng']['sql_query'] . '<br/><textarea id="sql" name="sql" rows="4" cols="48">' . htmlspecialchars($_POST['sql'], ENT_NOQUOTES) . '</textarea><br/><input type="submit" value="' . $GLOBALS['lng']['sql'] . '"/></div></form></div>';
        }
        break;

    case 'tables':
        if (!(isset($_POST['tables']) && $GLOBALS['class']->is_file($_POST['tables'])) && !(isset($_FILES['f_tables']) && !$_FILES['f_tables']['error'])) {
            echo '<div class="input"><form action="change.php?go=tables&amp;c=' . $r_current . '" method="post" enctype="multipart/form-data"><div>' . $GLOBALS['lng']['mysql_user'] . '<br/><input type="text" name="name"/><br/>' . $GLOBALS['lng']['mysql_pass'] . '<br/><input type="text" name="pass"/><br/>' . $GLOBALS['lng']['mysql_host'] . '<br/><input type="text" name="host" value="localhost"/><br/>' . $GLOBALS['lng']['mysql_db'] . '<br/><input type="text" name="db"/><br/>' . $GLOBALS['lng']['charset'] . '<br/><input type="text" name="charset" value="utf8"/><br/>' . $GLOBALS['lng']['tables_file'] . '<br/><input type="text" name="tables" value="' . $h_current . '" style="width:40%"/><input type="file" name="f_tables" style="width:40%"/><br/><input type="submit" value="' . $GLOBALS['lng']['tables'] . '"/></div></form></div>';
        } else {
            echo sql($_POST['name'], $_POST['pass'], $_POST['host'], $_POST['db'], !$_FILES['f_tables']['error'] ? file_get_contents($_FILES['f_tables']['tmp_name']) : $GLOBALS['class']->file_get_contents($_POST['tables']), $_POST['charset']);
        }
        break;

    case 'installer':
        if (substr($h_current, -1) != '/') {
            $d_current = str_replace('\\', '/', htmlspecialchars(dirname($current) . '/', ENT_COMPAT));
        } else {
            $d_current = $h_current;
        }

        if (!(isset($_POST['tables']) && $GLOBALS['class']->is_file($_POST['tables'])) && !(isset($_FILES['f_tables']) && !$_FILES['f_tables']['error'])) {    
            echo '<div class="input"><form action="change.php?go=installer" method="post" enctype="multipart/form-data"><div>' . $GLOBALS['lng']['mysql_user'] . '<br/><input type="text" name="name"/><br/>' . $GLOBALS['lng']['mysql_pass'] . '<br/><input type="text" name="pass"/><br/>' . $GLOBALS['lng']['mysql_host'] . '<br/><input type="text" name="host" value="localhost"/><br/>' . $GLOBALS['lng']['mysql_db'] . '<br/><input type="text" name="db"/><br/>' . $GLOBALS['lng']['charset'] . '<br/><input type="text" name="charset" value="utf8"/><br/>' . $GLOBALS['lng']['tables_file'] . '<br/><input type="text" name="tables" value="' . $h_current . '" style="width:40%"/><input type="file" name="f_tables" style="width:40%"/><br/><input onkeypress="return number(event)" type="text" name="chmod" size="4" maxlength="4" style="width:28pt;" value="0644"/>' . $GLOBALS['lng']['chmod'] . '<br/><input name="save_as" type="submit" value="' . $GLOBALS['lng']['save_as'] . '"/><input type="text" name="c" value="' . $d_current . 'sql_installer.php"/><br/></div></form></div>';
        } else {
            if ($sql = sql_installer(trim($_POST['host']), trim($_POST['name']), trim($_POST['pass']), trim($_POST['db']), trim($_POST['charset']), !$_FILES['f_tables']['error'] ? file_get_contents($_FILES['f_tables']['tmp_name']) : $GLOBALS['class']->file_get_contents($_POST['tables']))) {
                echo create_file(trim($_POST['c']), $sql, $_POST['chmod']);
            } else {
                echo report($GLOBALS['lng']['sql_parser_error'], 2);
            }
        }
        break;
        
    case 'cmd':
        if (isset($_POST['cmd'])) {
            echo show_cmd($_POST['cmd']);
            $v = htmlspecialchars($_POST['cmd'], ENT_COMPAT);
        } else {
            $v = '';
        }
        echo '<div class="input"><form action="change.php?go=cmd&amp;c=' . $r_current . '" method="post"><div>' . $GLOBALS['lng']['cmd_code'] . '<br/><input name="cmd" value="' . $v . '" style="width:99%"/><br/><input type="submit" value="' . $GLOBALS['lng']['cmd_go'] . '"/></div></form></div>';
    
        break;
}

echo '<div class="rb">' . round(microtime(true) - $ms, 4) . '<br/></div>' . $GLOBALS['foot'];

?>
