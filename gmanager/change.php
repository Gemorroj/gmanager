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


define('GMANAGER_START', microtime(true));

require 'lib/Config.php';
$Gmanager = new Gmanager;


$_GET['go'] = isset($_GET['go']) ? $_GET['go'] : '';

if (isset($_GET['get']) && $Gmanager->is_file($_GET['get'])) {
    if (isset($_GET['f'])) {
        $f = $Gmanager->getArchiveFile($_GET['get'], $_GET['f']);
        $name = basename($_GET['f']);
    } else {
        $f = $Gmanager->file_get_contents($_GET['get']);
        $name = basename($_GET['get']);
    }

    Getf::download($f, $name, true, false);
    exit;
}


$realpath = $Gmanager->realpath(Config::$current);
if ($realpath && $Gmanager->is_dir(Config::$current)) {
    $realpath .= '/';
}
$realpath = $realpath ? htmlspecialchars(str_replace('\\', '/', $realpath)) : Config::$hCurrent;


$Gmanager->sendHeader();


if ($_SERVER['QUERY_STRING'] == 'phpinfo') {
    $Gmanager->phpinfo();
    exit;
} else if (isset($_POST['add_archive']) && !isset($_POST['name'])) {
    header('Location: http://' . $_SERVER['HTTP_HOST'] . str_replace(array('\\', '//'), '/', dirname($_SERVER['PHP_SELF']) . '/') . 'index.php?c=' . rawurlencode(dirname(Config::$current)) . '&add_archive=' . Config::$rCurrent, true, 301);
    exit;
}

echo str_replace('%title%', ($_GET['go'] && $_GET['go'] != 1) ? htmlspecialchars($_GET['go'], ENT_NOQUOTES) : (isset($_POST['full_chmod']) ? Language::get('chmod') : (isset($_POST['full_del']) ? Language::get('del') : (isset($_POST['full_rename']) ? Language::get('change') : (isset($_POST['fname']) ? Language::get('rename') : (isset($_POST['create_archive']) ? Language::get('create_archive') : htmlspecialchars(rawurldecode($_SERVER['QUERY_STRING']), ENT_NOQUOTES)))))), Config::$top) . '<div class="w2">' . Language::get('title_change') . '<br/></div>' . $Gmanager->head() . $Gmanager->langJS();


switch ($_GET['go']) {
    case 1:
        $x = isset($_POST['check']) ? sizeof($_POST['check']) : 0;
        if (isset($_POST['fname'])) {
            if (!isset($_POST['name'])) {
                echo '<div class="input"><form action="change.php?go=1&amp;c=' . Config::$rCurrent . '" method="post"><div>' . Language::get('rename') . '<br/>[replace=from,to] - ' . Language::get('replace') . '<br/>[name] - ' . Language::get('name') . '<br/>[f] - ' . Language::get('type') . '<br/>[n=0] - ' . Language::get('meter') . '<br/>[date] - ' . Language::get('date') . '<br/>[rand=8,16] - ' . Language::get('rand') . '<br/><input type="text" name="name" value="[name].[f]"/><br/><select name="register"><option value="0">' . Language::get('str_register_no') . '</option><option value="1">' . Language::get('str_register_low') . '</option><option value="2">' . Language::get('str_register_up') . '</option></select>' . Language::get('str_register') . '<br/><input type="checkbox" name="overwrite" id="overwrite" checked="checked"/><label for="overwrite">' . Language::get('overwrite_existing_files') . '</label><br/><input name="fname" type="hidden" value="1"/>';

                for ($i = 0; $i < $x; ++$i) {
                    echo '<input name="check[]" type="hidden" value="' . $_POST['check'][$i] . '"/>';
                }

                echo '<input type="submit" value="' . Language::get('rename') . '"/></div></form></div>';
            } else {
                for ($i = 0; $i < $x; ++$i) {
                    $_POST['check'][$i] = rawurldecode($_POST['check'][$i]);
                    echo $Gmanager->fname($_POST['check'][$i], $_POST['name'], $_POST['register'], $i, isset($_POST['overwrite']));
                }
            }
        } else if (isset($_POST['full_del'])) {
            for ($i = 0; $i < $x; ++$i) {
                $_POST['check'][$i] = rawurldecode($_POST['check'][$i]);
                if ($Gmanager->is_dir($_POST['check'][$i])) {
                    echo $Gmanager->delDir($_POST['check'][$i] . '/');
                } else {
                    echo $Gmanager->delFile($_POST['check'][$i]);
                }
            }

            // echo $Gmanager->report('<br/>' . Language::get('full_del_file_dir_true'), 0);

        } else if (isset($_POST['full_chmod'])) {
            if (!isset($_POST['chmod'])) {
                echo '<div class="input"><form action="change.php?go=1&amp;c=' . Config::$rCurrent . '" method="post"><div><input onkeypress="return Gmanager.number(event)" type="text" size="4" maxlength="4" style="-wap-input-format:\'4N\';width:28pt;" name="chmod[]" value="0644"/>' . Language::get('change_chmod') . ' ' . Language::get('of files') . '<br/><input onkeypress="return Gmanager.number(event)" type="text" size="4" maxlength="4" style="-wap-input-format:\'4N\';width:28pt;" name="chmod[]" value="0755"/>' . Language::get('change_chmod') . ' ' . Language::get('of folders') . '<br/><input name="full_chmod" type="hidden" value="1"/>';
                for ($i = 0; $i < $x; ++$i) {
                    echo '<input name="check[]" type="hidden" value="' . $_POST['check'][$i] . '"/>';
                }
                echo '<input type="submit" value="' . Language::get('ch') . '"/></div></form></div>';
            } else {
                for ($i = 0; $i < $x; ++$i) {
                    $_POST['check'][$i] = rawurldecode($_POST['check'][$i]);
                    if ($Gmanager->is_dir($_POST['check'][$i])) {
                        echo $Gmanager->rechmod($_POST['check'][$i], $_POST['chmod'][1]);
                    } else {
                        echo $Gmanager->rechmod($_POST['check'][$i], $_POST['chmod'][0]);
                    }
                }
            }
        } else if (isset($_REQUEST['mega_full_extract'])) {
            if (!isset($_POST['name']) || !isset($_POST['chmod'])) {
                echo '<div class="input"><form action="change.php?go=1&amp;c=' . Config::$rCurrent . '" method="post"><div>' . Language::get('change_name') . '<br/><input type="text" name="name" value="' . htmlspecialchars(dirname(Config::$current), ENT_COMPAT) . '/"/><br/><input type="checkbox" name="overwrite" id="overwrite" checked="checked"/><label for="overwrite">' . Language::get('overwrite_existing_files') . '</label><br/><input onkeypress="return Gmanager.number(event)" type="text" name="chmod[]" size="4" maxlength="4" style="-wap-input-format:\'4N\';width:28pt;" value="0644"/>' . Language::get('change_chmod') . ' ' . Language::get('of files') . '<br/><input onkeypress="return Gmanager.number(event)" type="text" name="chmod[]" size="4" maxlength="4" style="-wap-input-format:\'4N\';width:28pt;" value="0755"/>' . Language::get('change_chmod') . ' ' . Language::get('of folders') . '<br/><input name="mega_full_extract" type="hidden" value="1"/><input type="submit" value="' . Language::get('extract_archive') . '"/></div></form></div>';
            } else {
                $archive = $Gmanager->isArchive($Gmanager->getType(basename(Config::$hCurrent)));

                if ($archive == 'ZIP') {
                    echo $Gmanager->extractZipArchive(Config::$current, $_POST['name'], $_POST['chmod'], isset($_POST['overwrite']));
                } else if ($archive == 'TAR') {
                    echo $Gmanager->extractTarArchive(Config::$current, $_POST['name'], $_POST['chmod'], isset($_POST['overwrite']));
                } else if ($archive == 'GZ') {
                    echo $Gmanager->gzExtract(Config::$current, $_POST['name'], $_POST['chmod'], isset($_POST['overwrite']));
                } else if ($archive == 'BZ2' && extension_loaded('bz2')) {
                    echo $Gmanager->extractTarArchive(Config::$current, $_POST['name'], $_POST['chmod'], isset($_POST['overwrite']));
                } else if ($archive == 'RAR' && extension_loaded('rar')) {
                    echo $Gmanager->extractRarArchive(Config::$current, $_POST['name'], $_POST['chmod'], isset($_POST['overwrite']));
                }
            }
        } else if (isset($_POST['full_extract'])) {
            if (!isset($_POST['name']) || !isset($_POST['chmod'])) {
                echo '<div class="input"><form action="change.php?go=1&amp;c=' . Config::$rCurrent . '" method="post"><div>' . Language::get('change_name') . '<br/><input type="text" name="name" value="' . htmlspecialchars(dirname(Config::$current), ENT_COMPAT) . '/"/><br/><input type="checkbox" name="overwrite" id="overwrite" checked="checked"/><label for="overwrite">' . Language::get('overwrite_existing_files') . '</label><br/><input onkeypress="return Gmanager.number(event)" type="text" name="chmod" size="4" maxlength="4" style="-wap-input-format:\'4N\';width:28pt;" value="0755"/>' . Language::get('change_chmod') . '<br/><input name="full_extract" type="hidden" value="1"/>';
                for ($i = 0; $i < $x; ++$i) {
                    echo '<input name="check[]" type="hidden" value="' . $_POST['check'][$i] . '"/>';
                }
                echo '<input type="submit" value="' . Language::get('extract_archive') . '"/></div></form></div>';
            } else {
                $_POST['check'] = array_map('rawurldecode', $_POST['check']);

                $archive = $Gmanager->isArchive($Gmanager->getType(basename(Config::$hCurrent)));

                if ($archive == 'ZIP') {
                    echo $Gmanager->extractZipFile(Config::$current, $_POST['name'], $_POST['chmod'], $_POST['check'], isset($_POST['overwrite']));
                } else if ($archive == 'TAR') {
                    echo $Gmanager->extractTarFile(Config::$current, $_POST['name'], $_POST['chmod'], $_POST['check'], isset($_POST['overwrite']));
                } else if ($archive == 'BZ2' && extension_loaded('bz2')) {
                    echo $Gmanager->extractTarFile(Config::$current, $_POST['name'], $_POST['chmod'], $_POST['check'], isset($_POST['overwrite']));
                } else if ($archive == 'RAR' && extension_loaded('rar')) {
                    echo $Gmanager->extractRarFile(Config::$current, $_POST['name'], $_POST['chmod'], $_POST['check'], isset($_POST['overwrite']));
                }
            }
        } else if (isset($_POST['gz_extract'])) {
            if (!isset($_POST['name']) || !isset($_POST['chmod'])) {
                echo '<div class="input"><form action="change.php?go=1&amp;c=' . Config::$rCurrent . '" method="post"><div>' . Language::get('change_name') . '<br/><input type="text" name="name" value="' . htmlspecialchars(dirname(Config::$current), ENT_COMPAT) . '/"/><br/><input type="checkbox" name="overwrite" id="overwrite" checked="checked"/><label for="overwrite">' . Language::get('overwrite_existing_files') . '</label><br/><input onkeypress="return Gmanager.number(event)" type="text" name="chmod" size="4" maxlength="4" style="-wap-input-format:\'4N\';width:28pt;" value="0755"/>' . Language::get('change_chmod') . '<br/><input name="gz_extract" type="hidden" value="1"/><input type="submit" value="' . Language::get('extract_archive') . '"/></div></form></div>';
            } else {
                echo $Gmanager->gzExtract(Config::$current, $_POST['name'], $_POST['chmod'], isset($_POST['overwrite']));
            }
        } else if (isset($_POST['create_archive'])) {
            if (!isset($_POST['name'])) {
                echo '<div class="input"><form action="change.php?go=1&amp;c=' . Config::$rCurrent . '" method="post"><div>' . Language::get('change_name') . '<br/><input type="text" name="name" value="' . Config::$hCurrent . 'archive.zip"/><br/><input onkeypress="return Gmanager.number(event)" type="text" name="chmod" size="4" maxlength="4" style="-wap-input-format:\'4N\';width:28pt;" value="0644"/>' . Language::get('change_chmod') . '<br/><input type="checkbox" name="overwrite" id="overwrite" checked="checked"/><label for="overwrite">' . Language::get('overwrite_existing_files') . '</label><br/>' . Language::get('comment_archive') . '<br/><textarea name="comment" rows="2" cols="24"></textarea><br/><input name="create_archive" type="hidden" value="1"/>';
                for ($i = 0; $i < $x; ++$i) {
                    echo '<input name="check[]" type="hidden" value="' . $_POST['check'][$i] . '"/>';
                }
                echo '<input type="submit" value="' . Language::get('create_archive') . '"/></div></form></div>';
            } else {
                $_POST['check'] = array_map('rawurldecode', $_POST['check']);
                echo $Gmanager->createZipArchive($_POST['name'], $_POST['chmod'], $_POST['check'], $_POST['comment'], isset($_POST['overwrite']));
            }
        } else if (isset($_POST['add_archive'])) {
            if (isset($_POST['dir'])) {
                $_POST['check'] = array_map('rawurldecode', $_POST['check']);
                $_POST['dir'] = rawurldecode($_POST['dir']);
                $_POST['add_archive'] = rawurldecode($_POST['add_archive']);

                $archive = $Gmanager->isArchive($Gmanager->getType(basename($_POST['add_archive'])));

                if ($archive == 'ZIP') {
                    echo $Gmanager->addZipArchive($_POST['add_archive'], $_POST['check'], $_POST['dir']);
                } else if ($archive == 'TAR') {
                    echo $Gmanager->addTarArchive($_POST['add_archive'], $_POST['check'], $_POST['dir']);
                } else if ($archive == 'BZ2' && extension_loaded('bz2')) {
                    echo $Gmanager->addTarArchive($_POST['add_archive'], $_POST['check'], $_POST['dir']);
                }
            } else {
                echo '<div class="input"><form action="change.php?go=1&amp;c=' . Config::$rCurrent . '" method="post"><div>' . Language::get('add_archive_dir') . '<br/><input type="text" name="dir" value="./"/><br/><input name="add_archive" type="hidden" value="' . $_POST['add_archive'] . '"/>';
                for ($i = 0; $i < $x; ++$i) {
                    echo '<input name="check[]" type="hidden" value="' . $_POST['check'][$i] . '"/>';
                }
                echo '<input type="submit" name="name" value="' . Language::get('add_archive') . '"/></div></form></div>';
            }
        } else if (isset($_POST['full_rename'])) {
            if (!isset($_GET['go2'])) {
                echo '<div class="input"><form action="change.php?go=1&amp;go2=1&amp;c=' . Config::$rCurrent . '" method="post"><div>' . Language::get('change_func2') . '<br/><input type="text" name="name" value="' . $realpath . '"/><br/><input type="checkbox" name="overwrite" id="overwrite" checked="checked"/><label for="overwrite">' . Language::get('overwrite_existing_files') . '</label><br/><input type="checkbox" name="del" id="del" value="1"/><label for="del">' . Language::get('change_del') . '</label><br/><input name="full_rename" type="hidden" value="1"/>';
                for ($i = 0; $i < $x; ++$i) {
                    echo '<input name="check[]" type="hidden" value="' . $_POST['check'][$i] . '"/>';
                }
                echo '<input type="submit" value="' . Language::get('ch') . '"/></div></form></div>';
            } else {
                for ($i = 0; $i < $x; ++$i) {
                    $_POST['check'][$i] = rawurldecode($_POST['check'][$i]);
                    echo $Gmanager->frename($_POST['check'][$i], str_replace('//', '/', $_POST['name'] . '/' . basename($_POST['check'][$i])), '', isset($_POST['del']), $_POST['name'], isset($_POST['overwrite']));
                }
            }
        } else if (isset($_POST['del_archive'])) {
                $archive = $Gmanager->isArchive($Gmanager->getType(basename(Config::$current)));
                $_POST['check'] = array_map('rawurldecode', $_POST['check']);

                if ($archive == 'ZIP') {
                    foreach ($_POST['check'] as $ch) {
                        echo $Gmanager->delZipArchive(Config::$current, $ch);
                    }
                } else if ($archive == 'TAR') {
                    foreach ($_POST['check'] as $ch) {
                        echo $Gmanager->delTarArchive(Config::$current, $ch);
                    }
                } else if ($archive == 'BZ2' && extension_loaded('bz2')) {
                    foreach ($_POST['check'] as $ch) {
                        echo $Gmanager->delTarArchive(Config::$current, $ch);
                    }
                }
        }
        break;


    case 'del':
        if ($Gmanager->is_dir(Config::$current)) {
            echo $Gmanager->delDir(Config::$current);
        } else {
            echo $Gmanager->delFile(Config::$current);
        }
        break;


    case 'chmod':
        if (!isset($_POST['chmod'])) {
            echo '<div class="input"><form action="change.php?go=chmod&amp;c=' . Config::$rCurrent . '" method="post"><div><input onkeypress="return Gmanager.number(event)" type="text" size="4" maxlength="4" style="-wap-input-format:\'4N\';width:28pt;" name="chmod" value="' . $Gmanager->lookChmod(Config::$current) . '"/>' . Language::get('change_chmod') . '<br/><input type="submit" value="' . Language::get('ch') . '"/></div></form></div>';
        } else {
            echo $Gmanager->rechmod(Config::$current, $_POST['chmod']);
        }
        break;


    case 'create_dir':
        if (!isset($_POST['name'])) {
            echo '<div class="input"><form action="change.php?go=create_dir&amp;c=' . Config::$rCurrent . '" method="post"><div>' . Language::get('change_name') . '<br/><input type="text" name="name" value="dir"/><br/><input onkeypress="return Gmanager.number(event)" type="text" name="chmod" size="4" maxlength="4" style="-wap-input-format:\'4N\';width:28pt;" value="0755"/>' . Language::get('change_chmod') . '<br/><input type="submit" value="' . Language::get('cr') . '"/></div></form></div>';
        } else {
            echo $Gmanager->createDir(Config::$current . $_POST['name'], $_POST['chmod']);
        }
        break;


    case 'create_file':
        include 'pattern.dat';
        if (!isset($_POST['name'])) {
            echo '<div class="input"><form action="change.php?go=create_file&amp;c=' . Config::$rCurrent . '" method="post"><div>' . Language::get('change_name') . '<br/><input type="text" name="name" value="file.php"/><br/><select name="ptn">';
            $all = sizeof($pattern);
            for ($i = 0; $i < $all; ++$i) {
                echo '<option value="' . $i . '">' . $pattern[$i][0] . '</option>';
            }
            echo '</select>' . Language::get('pattern') . '<br/><input onkeypress="return Gmanager.number(event)" type="text" name="chmod" size="4" maxlength="4" style="-wap-input-format:\'4N\';width:28pt;" value="0644"/>' . Language::get('change_chmod') . '<br/><input type="submit" value="' . Language::get('cr') . '"/></div></form></div>';
        } else {
            if ($Gmanager->file_exists(Config::$current . $_POST['name']) && !isset($_POST['a'])) {
                echo '<div class="red">' . Language::get('warning') . '<br/></div><form action="change.php?go=create_file&amp;c=' . Config::$rCurrent . '" method="post"><div><input type="hidden" name="name" value="' . htmlspecialchars($_POST['name'], ENT_COMPAT) . '"/><input type="hidden" name="ptn" value="' . htmlspecialchars($_POST['ptn'], ENT_COMPAT) . '"/><input type="hidden" name="chmod" value="' . htmlspecialchars($_POST['chmod'], ENT_COMPAT) . '"/><input type="hidden" name="a" value="1"/><input type="submit" value="' . Language::get('ch') . '"/></div></form>';
            } else {
                if (Config::$realname) {
                    $realpath = $realpath . htmlspecialchars($_POST['name'], ENT_NOQUOTES, 'UTF-8');
                } else {
                    $realpath = Config::$hCurrent . htmlspecialchars($_POST['name'], ENT_NOQUOTES, 'UTF-8');
                }

                echo '<div class="border">' . Language::get('file') . ' <strong><a href="edit.php?' . Config::$rCurrent . rawurlencode($_POST['name']) . '">' . $realpath . '</a></strong> (' . $_POST['chmod'] . ')<br/></div>' . $Gmanager->createFile(Config::$current . $_POST['name'], $pattern[intval($_POST['ptn'])][1], $_POST['chmod']);
            }
        }
        break;


    case 'rename':
        if (isset($_POST['name']) && $_POST['name'] != '') {
            $archive = $Gmanager->isArchive($Gmanager->getType(Config::$current));
            $if = isset($_GET['f']);
            if ($if && $archive == 'ZIP') {
                echo $Gmanager->renameZipFile(Config::$current, $_POST['name'], rawurldecode($_POST['arch_name']), isset($_POST['del']), isset($_POST['overwrite']));
            } else if ($if && $archive == 'TAR') {
                echo $Gmanager->renameTarFile(Config::$current, $_POST['name'], rawurldecode($_POST['arch_name']), isset($_POST['del']), isset($_POST['overwrite']));
            } else if ($if && $archive == 'BZ2' && extension_loaded('bz2')) {
                echo $Gmanager->renameTarFile(Config::$current, $_POST['name'], rawurldecode($_POST['arch_name']), isset($_POST['del']), isset($_POST['overwrite']));
            } else {
                echo $Gmanager->frename(Config::$current, $_POST['name'], isset($_POST['chmod']) ? $_POST['chmod'] : null, isset($_POST['del']), $_POST['name'], isset($_POST['overwrite']));
                if (isset($_POST['chmod']) && $_POST['chmod']) {
                    echo $Gmanager->rechmod($_POST['name'], $_POST['chmod']);
                }
            }
        } else {
            echo $Gmanager->report(Language::get('filename_empty'), 1);
        }
        break;


    case 'del_zip_archive':
        echo $Gmanager->delZipArchive($_GET['c'], $_GET['f']);
        break;


    case 'del_tar_archive':
        echo $Gmanager->delTarArchive($_GET['c'], $_GET['f']);
        break;


    case 'upload':
        if ((((!isset($_POST['url']) || $_POST['url'] == 'http://' || $_POST['url'] == '') && (!isset($_FILES['f']) || $_FILES['f']['error'][0])) && !isset($_POST['f'])) || !isset($_POST['name']) || !isset($_POST['chmod'])) {
            echo '<div class="input"><form action="change.php?go=upload&amp;c=' . Config::$rCurrent . '" method="post" enctype="multipart/form-data"><div>' . Language::get('url') . '<br/><textarea name="url" rows="3" cols="48" wrap="off">http://</textarea><br/>' . Language::get('headers') . '<br/><textarea rows="3" cols="32" name="headers">User-Agent: ' . htmlspecialchars(@$_SERVER['HTTP_USER_AGENT'], ENT_NOQUOTES) . "\n" . 'Cookie: ' . "\n" . 'Referer: ' . "\n" . 'Accept: ' . htmlspecialchars(@$_SERVER['HTTP_ACCEPT'], ENT_NOQUOTES) . "\n" . 'Accept-Charset: ' . htmlspecialchars(@$_SERVER['HTTP_ACCEPT_CHARSET'], ENT_NOQUOTES) . "\n" . 'Accept-Language: ' . htmlspecialchars(@$_SERVER['HTTP_ACCEPT_LANGUAGE'], ENT_NOQUOTES) . "\n" . 'Connection: Close' . "\n" . '</textarea><br/>' . Language::get('file') . ' (' . ini_get('upload_max_filesize') . ') <a href="javascript:void(0);" onclick="Gmanager.files(1);">[+]</a> / <a href="javascript:void(0);" onclick="Gmanager.files(0);">[-]</a><br/><div id="fl"><input type="file" name="f[]"/><br/></div>' . Language::get('name') . '<br/><input type="text" name="name" value="' . Config::$hCurrent . '"/><br/><input onkeypress="return Gmanager.number(event)" type="text" name="chmod" value="0644" size="4" maxlength="4" style="-wap-input-format:\'4N\';width:28pt;"/>' . Language::get('change_chmod') . '<br/><input type="text" name="set_time_limit" value="3600" size="5" style="-wap-input-format:\'*N\';width:28pt;"/>' . Language::get('set_time_limit') . '<br/><input type="checkbox" name="ignore_user_abort" id="ignore_user_abort" checked="checked" /><label for="ignore_user_abort">' . Language::get('ignore_user_abort') . '</label><br/><br/><input type="submit" value="' . Language::get('upload') . '"/></div></form></div>';
        } else {
            if (!$_FILES['f']['error'][0]) {
                $all = sizeof($_FILES['f']['tmp_name']);
                if ($all > 1) {
                    if (substr($_POST['name'], -1) != '/') {
                        $_POST['name'] .= '/';
                    }
                }

                for ($i = 0; $i < $all; ++$i ) {
                    echo $Gmanager->uploadFiles($_FILES['f']['tmp_name'][$i], $_FILES['f']['name'][$i], $_POST['name'], $_POST['chmod']);
                }
            } else {
                echo $Gmanager->uploadUrl($_POST['url'], $_POST['name'], $_POST['chmod'], $_POST['headers'], isset($_POST['set_time_limit']) ? $_POST['set_time_limit'] : false, isset($_POST['ignore_user_abort']));
            }
        }
        break;


    case 'mod':
        $safe = strtolower(ini_get('safe_mode'));
        $php_user = $Gmanager->getPHPUser();

        echo '<div class="red"><ul><li><a href="change.php?go=search&amp;c=' . Config::$rCurrent . '">' . Language::get('search') . '</a></li><li><a href="change.php?go=eval&amp;c=' . Config::$rCurrent . '">' . Language::get('eval') . '</a></li><li><a href="change.php?go=cmd&amp;c=' . Config::$rCurrent . '">' . Language::get('cmd') . '</a></li><li>SQL<ul><li><a href="change.php?go=mysql&amp;c=' . Config::$rCurrent . '">MySQL</a></li><li><a href="change.php?go=postgresql&amp;c=' . Config::$rCurrent . '">PostgreSQL</a></li><li><a href="change.php?go=sqlite&amp;c=' . Config::$rCurrent . '">SQLite</a></li></ul></li><li><a href="change.php?go=mysql_tables&amp;c=' . Config::$rCurrent . '">' . Language::get('tables') . '</a></li><li><a href="change.php?go=mysql_installer&amp;c=' . Config::$rCurrent . '">' . Language::get('create_sql_installer') . '</a></li><li><a href="change.php?go=scan&amp;c=' . Config::$rCurrent . '">' . Language::get('scan') . '</a></li><li><a href="change.php?go=send_mail&amp;c=' . Config::$rCurrent . '">' . Language::get('send_mail') . '</a></li><li><a href="change.php?phpinfo">' . Language::get('phpinfo') . '</a> (' . PHP_VERSION . ')</li><li><a href="change.php?go=new_version&amp;c=' . Config::$rCurrent . '">' . Language::get('new_version') . '</a></li></ul>' . ($php_user['name'] ? '<span style="color:#000;">&#187;</span> ' . Language::get('php_user') . htmlspecialchars($php_user['name'], ENT_NOQUOTES) . '<br/>' : '') . '<span style="color:#000;">&#187;</span> Safe Mode: ' . ($safe == 1 || $safe == 'on' ? '<span style="color:#b00;">ON</span>' : '<span style="color:#0f0;">OFF</span>') . '<br/><span style="color:#000;">&#187;</span> ' . htmlspecialchars($_SERVER['SERVER_SOFTWARE'], ENT_NOQUOTES) . '<br/><span style="color:#000;">&#187;</span> ' . htmlspecialchars(php_uname(), ENT_NOQUOTES) . '<br/><span style="color:#000;">&#187;</span> ' . Language::get('disk_total_space') . ' ' . $Gmanager->formatSize(@disk_total_space($_SERVER['DOCUMENT_ROOT'])) . '; ' . Language::get('disk_free_space') . ' ' . $Gmanager->formatSize(@disk_free_space($_SERVER['DOCUMENT_ROOT'])) . '<br/><span style="color:#000;">&#187;</span> ' . strftime('%d.%m.%Y / %H') . '<span style="text-decoration:blink;">:</span>' . strftime('%M') . '<br/></div>';
        break;


    case 'new_version':
        $new = $Gmanager->getData('http://wapinet.ru/gmanager/gmanager.txt');
        if ($new['body']) {
            if (version_compare($new['body'], Config::$version, '<=')) {
                echo $Gmanager->report(Language::get('version_new') . ': ' . $new['body'] . '<br/>' . Language::get('version_old') . ': ' . Config::$version . '<br/>' . Language::get('new_version_false'), 0);
            } else {
                echo $Gmanager->report(Language::get('version_new') . ': ' . $new['body'] . '<br/>' . Language::get('version_old') . ': ' . Config::$version . '<br/>' . Language::get('new_version_true') . '<br/>&#187; <a href="http://wapinet.ru/gmanager/gmanager.zip">' . Language::get('get') . '</a><br/><input name="" value="http://wapinet.ru/gmanager/gmanager.zip" size="39"/>', 1);
            }
        } else {
            echo $Gmanager->report(Language::get('not_connect'), 2);
        }
        break;


    case 'scan':
        if (!isset($_POST['url']) || $_POST['url'] == 'http://') {
            echo '<div class="input"><form action="change.php?go=scan&amp;c=' . Config::$rCurrent . '" method="post"><div>' . Language::get('url') . '<br/><input type="text" name="url" value="http://"/><br/>' . Language::get('headers') . '<br/><textarea rows="3" cols="32" name="headers">User-Agent: ' . htmlspecialchars(@$_SERVER['HTTP_USER_AGENT'], ENT_NOQUOTES) . "\n" . 'Cookie: ' . "\n" . 'Referer: ' . "\n" . 'Accept: ' . htmlspecialchars(@$_SERVER['HTTP_ACCEPT'], ENT_NOQUOTES) . "\n" . 'Accept-Charset: ' . htmlspecialchars(@$_SERVER['HTTP_ACCEPT_CHARSET'], ENT_NOQUOTES) . "\n" . 'Accept-Language: ' . htmlspecialchars(@$_SERVER['HTTP_ACCEPT_LANGUAGE'], ENT_NOQUOTES) . "\n" . 'Connection: Close' . "\n" . '</textarea><br/>POST<br/><input type="text" name="post"/><br/><input type="checkbox" name="oh" id="oh" /><label for="oh">' . Language::get('only_headers') . '</label><br/><input type="submit" value="' . Language::get('look') . '"/></div></form></div>';
        } else {
            $only_headers = isset($_POST['oh']);
            if ($url = $Gmanager->getData($_POST['url'], $_POST['headers'], $only_headers, $_POST['post'])) {
                $url = $url['headers'] . ($only_headers ? '' : "\r\n\r\n" . $url['body']);
                echo '<div class="code">IP: <span style="font-weight: normal;">' . implode(', ', gethostbynamel(parse_url($_POST['url'], PHP_URL_HOST))) . '</span><br/></div>' . $Gmanager->code($url, 0, true);
            } else {
                echo $Gmanager->report(Language::get('not_connect'), 2);
            }
        }
        break;


    case 'send_mail':
        if (!isset($_POST['from']) || !isset($_POST['theme']) || !isset($_POST['mess']) || !isset($_POST['to'])) {
            echo '<div class="input"><form action="change.php?go=send_mail&amp;c=' . Config::$rCurrent . '" method="post"><div>' . Language::get('mail_to') . '<br/><input type="text" name="to" value="' . (isset($_POST['to']) ? htmlspecialchars($_POST['to'], ENT_COMPAT) : '@') . '"/><br/>' . Language::get('mail_from') . '<br/><input type="text" name="from" value="admin@' . $_SERVER['HTTP_HOST'] . '"/><br/>' . Language::get('mail_theme') . '<br/><input type="text" name="theme" value="' . (isset($_POST['theme']) ? htmlspecialchars($_POST['theme'], ENT_COMPAT) : 'Hello') . '"/><br/>' . Language::get('mail_mess') . '<br/><textarea name="mess" rows="8" cols="48">' . (isset($_POST['mess']) ? htmlspecialchars($_POST['mess'], ENT_NOQUOTES) : '') . '</textarea><br/><input type="submit" value="' . Language::get('send_mail') . '"/></div></form></div>';
        } else {
            echo $Gmanager->sendMail($_POST['theme'], $_POST['mess'], $_POST['to'], $_POST['from']);
        }
        break;


    case 'eval':
        if (isset($_POST['eval'])) {
            echo $Gmanager->showEval($_POST['eval']);
            $v = htmlspecialchars($_POST['eval'], ENT_NOQUOTES);
        } else {
            $v = '';
        }

        echo '<div class="input"><form action="change.php?go=eval&amp;c=' . Config::$rCurrent . '" method="post"><div>' . Language::get('php_code') . '<br/><textarea onkeypress="return Gmanager.formatCode(event,this);" name="eval" rows="10" cols="48">' . $v . '</textarea><br/><input type="submit" value="' . Language::get('eval_go') . '"/></div></form></div>';
        break;


    case 'search':
        if (isset($_POST['search']) && $_POST['search'] != '') {
            $v = htmlspecialchars($_POST['search'], ENT_NOQUOTES);
            if (Config::$addressBar) {
                echo '<div><form action="change.php?" method="get"><div><input type="text" name="c" value="' . $realpath . '"/><br/><input type="hidden" name="go" value="search"/><input type="submit" value="' . Language::get('go') . '"/></div></form></div>';
            }
            
            echo '<form action="change.php?c=' . Config::$rCurrent . '&amp;go=1" method="post"><div class="telo"><table><tr><th><input type="checkbox" onclick="Gmanager.check(this.form,\'check[]\',this.checked)"/></th>' . (Config::$index['name'] ? '<th>' . Language::get('name') . '</th>' : '') . (Config::$index['down'] ? '<th>' . Language::get('get') . '</th>' : '') . (Config::$index['type'] ? '<th>' . Language::get('type') . '</th>' : '') . (Config::$index['size'] ? '<th>' . Language::get('size') . '</th>' : '') . (Config::$index['change'] ? '<th>' . Language::get('change') . '</th>' : '') . (Config::$index['del'] ? '<th>' . Language::get('del') . '</th>' : '') . (Config::$index['chmod'] ? '<th>' . Language::get('chmod') . '</th>' : '') . (Config::$index['date'] ? '<th>' . Language::get('date') . '</th>' : '') . (Config::$index['uid'] ? '<th>' . Language::get('uid') . '</th>' : '') . (Config::$index['gid'] ? '<th>' . Language::get('gid') . '</th>' : '') . (Config::$index['n'] ? '<th>' . Language::get('n') . '</th>' : '') . '</tr>' . $Gmanager->search($_POST['where'], $_POST['search'], isset($_POST['in']), isset($_POST['register']), isset($_POST['hex']), $_POST['size'] * 1048576, isset($_POST['archive'])) . '</table><div class="ch"><input onclick="return Gmanager.checkForm(document.forms[1],\'check[]\');" type="submit" name="full_chmod" value="' . Language::get('chmod') . '"/> <input onclick="return (Gmanager.checkForm(document.forms[1],\'check[]\') &amp;&amp; Gmanager.delNotify());" type="submit" name="full_del" value="' . Language::get('del') . '"/> <input onclick="return Gmanager.checkForm(document.forms[1],\'check[]\');" type="submit" name="full_rename" value="' . Language::get('change') . '"/> <input onclick="return Gmanager.checkForm(document.forms[1],\'check[]\');" type="submit" name="create_archive" value="' . Language::get('create_archive') . '"/></div></div></form><div class="rb">' . Language::get('create') . '<a href="change.php?go=create_file&amp;c=' . Config::$rCurrent . '">' . Language::get('file') . '</a> / <a href="change.php?go=create_dir&amp;c=' . Config::$rCurrent . '">' . Language::get('dir') . '</a><br/></div><div class="rb"><a href="change.php?go=upload&amp;c=' . Config::$rCurrent . '">' . Language::get('upload') . '</a><br/></div><div class="rb"><a href="change.php?go=mod&amp;c=' . Config::$rCurrent . '">' . Language::get('mod') . '</a><br/></div>';
        } else {
            $v = '';
        }
        echo '<div class="input"><form action="change.php?go=search&amp;c=' . Config::$rCurrent . '" method="post"><div>' . Language::get('where_search') . '<br/><input type="text" name="where" value="' . (isset($_POST['where']) ? htmlspecialchars($_POST['where'], ENT_COMPAT) : $realpath) . '"/><br/>' . Language::get('what_search') . '<br/><input type="text" name="search" value="' . $v . '"/><br/><fieldset><legend><input type="checkbox" name="in" id="in"' . (isset($_POST['in']) ? ' checked="checked"' : '') . '/> <label for="in">' . Language::get('in_text') . '</label></legend><input type="text" name="size" value="' . (isset($_POST['size']) ? htmlspecialchars($_POST['size']) : 8) . '" style="-wap-input-format:\'*N\';width:28pt;" size="4" onkeypress="return Gmanager.number(event)"/> ' . Language::get('search_limit') . '<br/><input type="checkbox" name="archive" id="archive"' . (isset($_POST['archive']) ? ' checked="checked"' : '') . '/><label for="archive">' . Language::get('search_archives') . ' (GZ)</label><br/></fieldset><input type="checkbox" name="register" id="register"' . (isset($_POST['register']) ? ' checked="checked"' : '') . '/><label for="register">' . Language::get('register') . '</label><br/><input type="checkbox" name="hex" id="hex"' . (isset($_POST['hex']) ? ' checked="checked"' : '') . '/><label for="hex">' . Language::get('hex') . '</label><br/><input type="submit" value="' . Language::get('eval_go') . '"/></div></form></div>';
        break;


    case 'mysql':
        Config::$sqlDriver = 'mysql';
        $_POST['sql'] = isset($_POST['sql']) ? trim($_POST['sql']) : '';
        if (isset($_POST['name']) && isset($_POST['host'])) {
            if (isset($_POST['backup'])) {
                if (isset($_POST['file']) && $_POST['file']) {
                    echo $Gmanager->sqlBackup($_POST['host'], $_POST['name'], $_POST['pass'], $_POST['db'], $_POST['charset'], array('tables' => @array_map('rawurldecode', @$_POST['tables']), 'data' => @array_map('rawurldecode', @$_POST['data']), 'file' => $_POST['file']));
                } else {
                    $tables = $Gmanager->sqlBackup($_POST['host'], $_POST['name'], $_POST['pass'], $_POST['db'], $_POST['charset'], array());
                    echo '<div class="input"><form action="change.php?go=mysql&amp;c=' . Config::$rCurrent . '" method="post"><div>' . Language::get('mysql_backup_structure') . '<br/><select name="tables[]" multiple="true" size="5">' . $tables . '</select><br/>' . Language::get('mysql_backup_data') . '<br/><select name="data[]" multiple="true" size="5">' . $tables . '</select><br/>' . Language::get('file') . '<br/><input type="text" name="file" value="' . Config::$hCurrent . 'backup_' . htmlspecialchars($_POST['db']) . '.sql"/><br/><input type="hidden" name="name" value="' . htmlspecialchars($_POST['name']) . '"/><input type="hidden" name="pass" value="' . htmlspecialchars($_POST['pass']) . '"/><input type="hidden" name="host" value="' . htmlspecialchars($_POST['host']) . '"/><input type="hidden" name="db" value="' . htmlspecialchars($_POST['db']) . '"/><input type="hidden" name="charset" value="' . htmlspecialchars($_POST['charset']) . '"/><input type="submit" name="backup" value="' . Language::get('mysql_backup') . '"/></div></form></div>';
                }
            } else {
                include 'pattern.dat';
                $tmp = '<select id="ptn" onchange="Gmanager.paste(this.value);">';
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
                echo '<div>&#160;' . $_POST['name'] . ($_POST['db'] ? ' =&gt; ' . htmlspecialchars($_POST['db'], ENT_NOQUOTES) : '') . '<br/></div>' . $Gmanager->sqlQuery($_POST['host'], $_POST['name'], $_POST['pass'], $_POST['db'], $_POST['charset'], $_POST['sql']) . '<div><form action=""><div><textarea rows="' . (substr_count($_POST['sql'], "\n") + 1) . '" cols="48">' . htmlspecialchars($_POST['sql'], ENT_NOQUOTES) . '</textarea></div></form></div><div class="input"><form action="change.php?go=mysql&amp;c=' . Config::$rCurrent . '" method="post" id="post"><div>' . Language::get('sql_query') . ' ' . $tmp . '<br/><textarea id="sql" name="sql" rows="6" cols="48"></textarea><br/><input type="hidden" name="name" value="' . htmlspecialchars($_POST['name']) . '"/><input type="hidden" name="pass" value="' . htmlspecialchars($_POST['pass']) . '"/><input type="hidden" name="host" value="' . htmlspecialchars($_POST['host']) . '"/><input type="hidden" name="db" value="' . htmlspecialchars($_POST['db']) . '"/><input type="hidden" name="charset" value="' . htmlspecialchars($_POST['charset']) . '"/><input type="submit" value="' . Language::get('sql') . '"/>' . ($_POST['db'] ? ' <input type="submit" name="backup" value="' . Language::get('mysql_backup') . '"/>' : '') . '</div></form></div>';
            }
        } else {
            echo '<div class="input"><form action="change.php?go=mysql&amp;c=' . Config::$rCurrent . '" method="post" id="post"><div>' . Language::get('mysql_user') . '<br/><input type="text" name="name" value=""/><br/>' . Language::get('mysql_pass') . '<br/><input type="text" name="pass"/><br/>' . Language::get('mysql_host') . '<br/><input type="text" name="host" value="localhost"/><br/>' . Language::get('mysql_db') . '<br/><input type="text" name="db"/><br/>' . Language::get('charset') . '<br/><input type="text" name="charset" value="utf8"/><br/>' . Language::get('sql_query') . '<br/><textarea id="sql" name="sql" rows="4" cols="48">' . htmlspecialchars($_POST['sql'], ENT_NOQUOTES) . '</textarea><br/><input type="submit" value="' . Language::get('sql') . '"/></div></form></div>';
        }
        break;


    case 'postgresql':
        Config::$sqlDriver = 'postgresql';
        $_POST['sql'] = isset($_POST['sql']) ? trim($_POST['sql']) : '';
        if (isset($_POST['name']) && isset($_POST['host'])) {
            if (isset($_POST['backup'])) {
                if (isset($_POST['file']) && $_POST['file']) {
                    echo $Gmanager->sqlBackup($_POST['host'], $_POST['name'], $_POST['pass'], $_POST['db'], $_POST['charset'], array('tables' => @array_map('rawurldecode', @$_POST['tables']), 'data' => @array_map('rawurldecode', @$_POST['data']), 'file' => $_POST['file']));
                } else {
                    $tables = $Gmanager->sqlBackup($_POST['host'], $_POST['name'], $_POST['pass'], $_POST['db'], $_POST['charset'], array());
                    echo '<div class="input"><form action="change.php?go=postgresql&amp;c=' . Config::$rCurrent . '" method="post"><div>' . Language::get('mysql_backup_structure') . '<br/><select name="tables[]" multiple="true" size="5">' . $tables . '</select><br/>' . Language::get('mysql_backup_data') . '<br/><select name="data[]" multiple="true" size="5">' . $tables . '</select><br/>' . Language::get('file') . '<br/><input type="text" name="file" value="' . Config::$hCurrent . 'backup_' . htmlspecialchars($_POST['db']) . '.sql"/><br/><input type="hidden" name="name" value="' . htmlspecialchars($_POST['name']) . '"/><input type="hidden" name="pass" value="' . htmlspecialchars($_POST['pass']) . '"/><input type="hidden" name="host" value="' . htmlspecialchars($_POST['host']) . '"/><input type="hidden" name="db" value="' . htmlspecialchars($_POST['db']) . '"/><input type="hidden" name="charset" value="' . htmlspecialchars($_POST['charset']) . '"/><input type="submit" name="backup" value="' . Language::get('mysql_backup') . '"/></div></form></div>';
                }
            } else {
                include 'pattern.dat';
                $tmp = '<select id="ptn" onchange="Gmanager.paste(this.value);">';
                $all = sizeof($sql_ptn);
                for ($i = 0; $i < $all; ++$i) {
                    $tmp .= '<option value="' . htmlspecialchars($sql_ptn[$i][1], ENT_COMPAT) . '">' . $sql_ptn[$i][0] . '</option>';
                }
                $tmp .= '</select>';

                if (!$_POST['sql'] && !$_POST['db']) {
                    $_POST['sql'] = 'SELECT oid, * from pg_database';
                } else if (!$_POST['sql']) {
                    $_POST['sql'] = 'SELECT * FROM information_schema.tables';
                }
                echo '<div>&#160;' . $_POST['name'] . ($_POST['db'] ? ' =&gt; ' . htmlspecialchars($_POST['db'], ENT_NOQUOTES) : '') . '<br/></div>' . $Gmanager->sqlQuery($_POST['host'], $_POST['name'], $_POST['pass'], $_POST['db'], $_POST['charset'], $_POST['sql']) . '<div><form action=""><div><textarea rows="' . (substr_count($_POST['sql'], "\n") + 1) . '" cols="48">' . htmlspecialchars($_POST['sql'], ENT_NOQUOTES) . '</textarea></div></form></div><div class="input"><form action="change.php?go=postgresql&amp;c=' . Config::$rCurrent . '" method="post" id="post"><div>' . Language::get('sql_query') . ' ' . $tmp . '<br/><textarea id="sql" name="sql" rows="6" cols="48"></textarea><br/><input type="hidden" name="name" value="' . htmlspecialchars($_POST['name']) . '"/><input type="hidden" name="pass" value="' . htmlspecialchars($_POST['pass']) . '"/><input type="hidden" name="host" value="' . htmlspecialchars($_POST['host']) . '"/><input type="hidden" name="db" value="' . htmlspecialchars($_POST['db']) . '"/><input type="hidden" name="charset" value="' . htmlspecialchars($_POST['charset']) . '"/><input type="submit" value="' . Language::get('sql') . '"/>' . ($_POST['db'] ? ' <input type="submit" name="backup" value="' . Language::get('mysql_backup') . '"/>' : '') . '</div></form></div>';
            }
        } else {
            echo '<div class="input"><form action="change.php?go=postgresql&amp;c=' . Config::$rCurrent . '" method="post" id="post"><div>' . Language::get('mysql_user') . '<br/><input type="text" name="name" value=""/><br/>' . Language::get('mysql_pass') . '<br/><input type="text" name="pass"/><br/>' . Language::get('mysql_host') . '<br/><input type="text" name="host" value="localhost"/><br/>' . Language::get('mysql_db') . '<br/><input type="text" name="db"/><br/>' . Language::get('charset') . '<br/><input type="text" name="charset" value="utf8"/><br/>' . Language::get('sql_query') . '<br/><textarea id="sql" name="sql" rows="4" cols="48">' . htmlspecialchars($_POST['sql'], ENT_NOQUOTES) . '</textarea><br/><input type="submit" value="' . Language::get('sql') . '"/></div></form></div>';
        }
        break;


    case 'sqlite':
        Config::$sqlDriver = 'sqlite';
        $_POST['sql'] = isset($_POST['sql']) ? trim($_POST['sql']) : '';
        if (isset($_POST['db'])) {
            if (isset($_POST['backup'])) {
                if (isset($_POST['file']) && $_POST['file']) {
                    echo $Gmanager->sqlBackup('', '', '', $_POST['db'], $_POST['charset'], array('tables' => @array_map('rawurldecode', @$_POST['tables']), 'data' => @array_map('rawurldecode', @$_POST['data']), 'file' => $_POST['file']));
                } else {
                    $tables = $Gmanager->sqlBackup('', '', '', $_POST['db'], $_POST['charset'], array());
                    echo '<div class="input"><form action="change.php?go=sqlite&amp;c=' . Config::$rCurrent . '" method="post"><div>' . Language::get('mysql_backup_structure') . '<br/><select name="tables[]" multiple="true" size="5">' . $tables . '</select><br/>' . Language::get('mysql_backup_data') . '<br/><select name="data[]" multiple="true" size="5">' . $tables . '</select><br/>' . Language::get('file') . '<br/><input type="text" name="file" value="' . Config::$hCurrent . 'backup_' . htmlspecialchars(basename($_POST['db'])) . '.sql"/><br/><input type="hidden" name="db" value="' . htmlspecialchars($_POST['db']) . '"/><input type="hidden" name="charset" value="' . htmlspecialchars($_POST['charset']) . '"/><input type="submit" name="backup" value="' . Language::get('mysql_backup') . '"/></div></form></div>';
                }
            } else {
                include 'pattern.dat';
                $tmp = '<select id="ptn" onchange="Gmanager.paste(this.value);">';
                $all = sizeof($sql_ptn);
                for ($i = 0; $i < $all; ++$i) {
                    $tmp .= '<option value="' . htmlspecialchars($sql_ptn[$i][1], ENT_COMPAT) . '">' . $sql_ptn[$i][0] . '</option>';
                }
                $tmp .= '</select>';

                if (!$_POST['sql']) {
                    $_POST['sql'] = 'SELECT name FROM sqlite_master WHERE type = "table" ORDER BY name';
                }
                echo '<div>&#160;' . $_POST['db'] . '<br/></div>' . $Gmanager->sqlQuery('', '', '', $Gmanager->realpath($_POST['db']), $_POST['charset'], $_POST['sql']) . '<div><form action=""><div><textarea rows="' . (substr_count($_POST['sql'], "\n") + 1) . '" cols="48">' . htmlspecialchars($_POST['sql'], ENT_NOQUOTES) . '</textarea></div></form></div><div class="input"><form action="change.php?go=sqlite&amp;c=' . Config::$rCurrent . '" method="post" id="post"><div>' . Language::get('sql_query') . ' ' . $tmp . '<br/><textarea id="sql" name="sql" rows="6" cols="48"></textarea><br/><input type="hidden" name="db" value="' . htmlspecialchars($_POST['db']) . '"/><input type="hidden" name="charset" value="' . htmlspecialchars($_POST['charset']) . '"/><input type="submit" value="' . Language::get('sql') . '"/> <input type="submit" name="backup" value="' . Language::get('mysql_backup') . '"/></div></form></div>';
            }
        } else {
            echo '<div class="input"><form action="change.php?go=sqlite&amp;c=' . Config::$rCurrent . '" method="post" id="post"><div>' . Language::get('mysql_db') . '<br/><input type="text" name="db" value="' . Config::$hCurrent . '"/><br/>' . Language::get('charset') . '<br/><input type="text" name="charset" value="utf8"/><br/>' . Language::get('sql_query') . '<br/><textarea id="sql" name="sql" rows="4" cols="48">' . htmlspecialchars($_POST['sql'], ENT_NOQUOTES) . '</textarea><br/><input type="submit" value="' . Language::get('sql') . '"/></div></form></div>';
        }
        break;


    case 'mysql_tables':
        if (!(isset($_POST['tables']) && $Gmanager->is_file($_POST['tables'])) && !(isset($_FILES['f_tables']) && !$_FILES['f_tables']['error'])) {
            echo '<div class="input"><form action="change.php?go=mysql_tables&amp;c=' . Config::$rCurrent . '" method="post" enctype="multipart/form-data"><div>' . Language::get('mysql_user') . '<br/><input type="text" name="name"/><br/>' . Language::get('mysql_pass') . '<br/><input type="text" name="pass"/><br/>' . Language::get('mysql_host') . '<br/><input type="text" name="host" value="localhost"/><br/>' . Language::get('mysql_db') . '<br/><input type="text" name="db"/><br/>' . Language::get('charset') . '<br/><input type="text" name="charset" value="utf8"/><br/>' . Language::get('tables_file') . '<br/><input type="text" name="tables" value="' . Config::$hCurrent . '" style="width:40%"/><input type="file" name="f_tables" style="width:40%"/><br/><input type="submit" value="' . Language::get('tables') . '"/></div></form></div>';
        } else {
            echo $Gmanager->sqlQuery($_POST['host'], $_POST['name'], $_POST['pass'], $_POST['db'], $_POST['charset'], !$_FILES['f_tables']['error'] ? file_get_contents($_FILES['f_tables']['tmp_name']) : $Gmanager->file_get_contents($_POST['tables']));
        }
        break;


    case 'mysql_installer':
        if (substr(Config::$hCurrent, -1) != '/') {
            $d = str_replace('\\', '/', htmlspecialchars(dirname(Config::$current) . '/', ENT_COMPAT));
        } else {
            $d = Config::$hCurrent;
        }

        if (!(isset($_POST['tables']) && $Gmanager->is_file($_POST['tables'])) && !(isset($_FILES['f_tables']) && !$_FILES['f_tables']['error'])) {
            echo '<div class="input"><form action="change.php?go=mysql_installer&amp;c=' . Config::$rCurrent . '" method="post" enctype="multipart/form-data"><div>' . Language::get('mysql_user') . '<br/><input type="text" name="name"/><br/>' . Language::get('mysql_pass') . '<br/><input type="text" name="pass"/><br/>' . Language::get('mysql_host') . '<br/><input type="text" name="host" value="localhost"/><br/>' . Language::get('mysql_db') . '<br/><input type="text" name="db"/><br/>' . Language::get('charset') . '<br/><input type="text" name="charset" value="utf8"/><br/>' . Language::get('tables_file') . '<br/><input type="text" name="tables" value="' . Config::$hCurrent . '" style="width:40%"/><input type="file" name="f_tables" style="width:40%"/><br/><input onkeypress="return Gmanager.number(event)" type="text" name="chmod" size="4" maxlength="4" style="-wap-input-format:\'4N\';width:28pt;" value="0644"/>' . Language::get('chmod') . '<br/><input name="save_as" type="submit" value="' . Language::get('save_as') . '"/><input type="text" name="file" value="' . $d . 'sql_installer.php"/><br/></div></form></div>';
        } else {
            if ($sql = $Gmanager->sqlInstaller(trim($_POST['host']), trim($_POST['name']), trim($_POST['pass']), trim($_POST['db']), trim($_POST['charset']), !$_FILES['f_tables']['error'] ? file_get_contents($_FILES['f_tables']['tmp_name']) : $Gmanager->file_get_contents($_POST['tables']))) {
                echo $Gmanager->createFile(trim($_POST['file']), $sql, $_POST['chmod']);
            } else {
                echo $Gmanager->report(Language::get('sql_parser_error'), 2);
            }
        }
        break;


    case 'cmd':
        if (isset($_POST['cmd'])) {
            echo $Gmanager->showCmd($_POST['cmd']);
            $v = htmlspecialchars($_POST['cmd'], ENT_COMPAT);
        } else {
            $v = '';
        }
        echo '<div class="input"><form action="change.php?go=cmd&amp;c=' . Config::$rCurrent . '" method="post"><div>' . Language::get('cmd_code') . '<br/><input type="text" name="cmd" value="' . $v . '" style="width:98%"/><br/><input type="submit" value="' . Language::get('cmd_go') . '"/></div></form></div>';
        break;


    default:
        if (!$Gmanager->file_exists(Config::$current)) {
            echo $Gmanager->report(Language::get('not_found'), 1);
            break;
        }

        $archive = $Gmanager->isArchive($Gmanager->getType(Config::$current));
        if (isset($_GET['f']) && ($archive == 'ZIP' || $archive == 'TAR' || $archive == 'BZ2')) {
            $r_file = str_replace('%2F', '/', rawurlencode($_GET['f']));
            $h_file = htmlspecialchars($_GET['f']);
            echo '<div class="input"><form action="change.php?go=rename&amp;c=' . Config::$rCurrent . '&amp;f=' . $r_file . '" method="post"><div><input type="hidden" name="arch_name" value="' . $r_file . '"/>' . Language::get('change_func') . '<br/><input type="text" name="name" value="' . $h_file . '"/><br/><input type="checkbox" name="overwrite" id="overwrite" checked="checked"/><label for="overwrite">' . Language::get('overwrite_existing_files') . '</label><br/><input type="checkbox" name="del" id="del" value="1"/><label for="del">' . Language::get('change_del') . '</label><br/><input type="submit" value="' . Language::get('ch') . '"/></div></form></div>';
        } else {
            if ($Gmanager->is_dir(Config::$current)) {
                $size = $Gmanager->formatSize($Gmanager->size(Config::$current, true));
                $md5 = '';
            } else if ($Gmanager->is_file(Config::$current) || $Gmanager->is_link(Config::$current)) {
                $size = $Gmanager->formatSize($Gmanager->size(Config::$current));
                if (Config::$mode == 'FTP') {
                    $md5 = Language::get('md5') . ': ' . md5($Gmanager->file_get_contents(Config::$current));
                } else {
                    $md5 = Language::get('md5') . ': ' . md5_file(IOWrapper::set(Config::$current));
                }
            }

            echo '<div class="input"><form action="change.php?go=rename&amp;c=' . Config::$rCurrent . '" method="post"><div>' . Language::get('change_func') . '<br/><input type="text" name="name" value="' . $realpath . '"/><br/><input type="checkbox" name="overwrite" id="overwrite" checked="checked"/><label for="overwrite">' . Language::get('overwrite_existing_files') . '</label><br/><input type="checkbox" name="del" id="del" value="1"/><label for="del">' . Language::get('change_del') . '</label><br/><input onkeypress="return Gmanager.number(event)" type="text" size="4" maxlength="4" style="-wap-input-format:\'4N\';width:28pt;" name="chmod" value="' . $Gmanager->lookChmod(Config::$current) . '"/>' . Language::get('change_chmod') . '<br/><input type="submit" value="' . Language::get('ch') . '"/></div></form></div><div>' . Language::get('sz') . ': ' . $size . '<br/>' . $md5 . '</div>';
        }
        break;
}

echo '<div class="rb">' . round(microtime(true) - GMANAGER_START, 4) . '<br/></div>' . Config::$foot;

?>
