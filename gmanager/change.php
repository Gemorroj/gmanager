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


if ($_SERVER['QUERY_STRING'] == 'phpinfo') {
    if (function_exists('phpinfo')) {
        phpinfo();
        exit;
    }
}

require 'functions.php';

if ($mode->is_file($_GET['get'])) {
    if ($_GET['f']) {
        $f = get_archive_file($_GET['get'], $_GET['f']);
        $name = basename($_GET['f']);
    } else {
        $f = $mode->file_get_contents($_GET['get']);
        $name = basename($_GET['get']);
    }

    getf($f, $name, true, false);
    exit;
}


$current = c($_SERVER['QUERY_STRING'], $_REQUEST['c']);
$h_current = htmlspecialchars($current);
$r_current = str_replace('%2F', '/', rawurlencode($current));
$realpath = realpath($current) . '/';
$realpath = $realpath ? htmlspecialchars(str_replace('\\', '/', $realpath)) : $h_current;


send_header($_SERVER['HTTP_USER_AGENT']);

print str_replace('%dir%', $_GET['go'], $top) . '
<div class="w2">
' . $lng['title_change'] . '<br/>
</div>
' . this($current);

switch ($_GET['go']) {
    default:

        if ($_SERVER['QUERY_STRING'] == 'phpinfo') {
            echo '<div class="red">' . $lng['disable_function'] . '<br/></div>';
            break;
        } elseif (!$mode->file_exists($current)) {
            echo '<div class="red">' . $lng['not_found'] . '<br/></div>';
            break;
        }

        if ($mode->is_dir($current)) {
            $size = dir_size($current);
        } elseif ($mode->is_file($current) || $mode->is_link($current)) {
            $size = file_size($current, true);
        }

echo '<div>
' . $lng['sz'] . ' ' . $size . '<br/>
</div>
<div class="input">
<form action="change.php?go=rename&amp;c=' . $r_current . '" method="post">
<div>
' . $lng['change_func'] . '<br/>
<input type="text" name="name" value="' . $realpath . '"/><br/>
' . $lng['change_del'] . '<br/>
<input type="checkbox" name="del" value="1"/><br/>
' . $lng['change_chmod'] . '<br/>
<input onkeypress="return number(event)" type="text" size="4" maxlength="4" style="width:28pt;" name="chmod" value="' . look_chmod($current) . '"/><br/>
<input type="submit" value="' . $lng['ch'] . '"/>
</div>
</form>
</div>
<div class="rb"><a href="change.php?go=del&amp;c=' . $r_current . '">' . $lng['dl'] .
            '</a><br/></div>';
        break;

    case 1:
        $x = sizeof($_POST['check']);
        if ($_POST['fname']) {
            if (!$_POST['name']) {
                print '<div class="input">
<form action="change.php?go=1&amp;c=' . $r_current . '" method="post">
<div>
' . $lng['rename'] . '<br/>
[name] - ' . $lng['name'] . '<br/>
[f] - ' . $lng['type'] . '<br/>
[n=0] - ' . $lng['meter'] . '<br/>
[date] - ' . $lng['date'] . '<br/>
<input type="text" name="name" value="[name].[f]"/><br/>
' . $lng['str_register'] . '<br/>
<select name="register">
<option value="">' . $lng['str_register_no'] . '</option>
<option value="1">' . $lng['str_register_low'] . '</option>
<option value="2">' . $lng['str_register_up'] . '</option>
</select><br/>
<input name="fname" type="hidden" value="1"/>';

                for ($i = 0; $i < $x; ++$i) {
                    echo '<input name="check[]" type="hidden" value="' . $_POST['check'][$i] . '"/>';
                }

                echo '<input type="submit" value="' . $lng['rename'] . '"/>
</div>
</form>
</div>';
            } else {
                $fin = '';
                for ($i = 0; $i < $x; ++$i) {
                	$_POST['check'][$i] = rawurldecode($_POST['check'][$i]);
                    $fin .= fname($_POST['check'][$i], $_POST['name'], $_POST['register'], $i);
                }
                echo '<div class="red">' . $fin . '</div>';
            }
        } elseif ($_POST['full_del']) {
            for ($i = 0; $i < $x; ++$i) {
            	$_POST['check'][$i] = rawurldecode($_POST['check'][$i]);
                if ($mode->is_dir($_POST['check'][$i])) {
                    echo del_dir($_POST['check'][$i] . '/');
                } else {
                    echo del_file($_POST['check'][$i]);
                }
            }

			echo '<div class="red"><br/>' . $lng['full_del_file_dir_true'] . '<br/></div>';

        } elseif ($_POST['full_chmod']) {
            if (!$_POST['chmod']) {
                echo '<div class="input">
<form action="change.php?go=1&amp;c=' . $r_current . '" method="post">
<div>
' . $lng['change_chmod'] . ' ' . $lng['of files'] . '<br/>
<input onkeypress="return number(event)" type="text" size="4" maxlength="4" style="width:28pt;" name="chmod[]" value="0644"/><br/>
' . $lng['change_chmod'] . ' ' . $lng['of folders'] . '<br/>
<input onkeypress="return number(event)" type="text" size="4" maxlength="4" style="width:28pt;" name="chmod[]" value="0755"/><br/>
<input name="full_chmod" type="hidden" value="1"/>';
                for ($i = 0; $i < $x; ++$i) {
                    echo '<input name="check[]" type="hidden" value="' . $_POST['check'][$i] . '"/>';
                }
                echo '<input type="submit" value="' . $lng['ch'] . '"/>
</div>
</form>
</div>';
            } else {
                for ($i = 0; $i < $x; ++$i) {
                	$_POST['check'][$i] = rawurldecode($_POST['check'][$i]);
                	if($mode->is_dir($_POST['check'][$i])){
                		echo rechmod($_POST['check'][$i], $_POST['chmod'][1]);
               		}
               		else{
               			echo rechmod($_POST['check'][$i], $_POST['chmod'][0]);
         			}
                }
                //print '<div class="red">'.$lng['full_rechmod'].'<br/></div>';
            }
        } elseif ($_REQUEST['mega_full_extract']) {
            if (!$_POST['name'] || !$_POST['chmod']) {
                echo '<div class="input">
<form action="change.php?go=1&amp;c=' . $r_current . '" method="post">
<div>
' . $lng['change_name'] . '<br/>
<input type="text" name="name" value="' . htmlspecialchars(dirname($current), ENT_COMPAT) . '/"/><br/>
' . $lng['change_chmod'] . ' '.$lng['of files'].'<br/>
<input onkeypress="return number(event)" type="text" name="chmod[]" size="4" maxlength="4" style="width:28pt;" value="0644"/><br/>
' . $lng['change_chmod'] . ' '.$lng['of folders'].'<br/>
<input onkeypress="return number(event)" type="text" name="chmod[]" size="4" maxlength="4" style="width:28pt;" value="0755"/><br/>
<input name="mega_full_extract" type="hidden" value="1"/>
<input type="submit" value="' . $lng['extract_archive'] . '"/>
</div>
</form>
</div>';
            } else {
                $type = strtoupper(strrchr($h_current, '.'));
                if ($type == '.ZIP' || $type == '.JAR') {
                    echo extract_zip_archive($current, $_POST['name'], $_POST['chmod']);
                } elseif ($type == '.TAR' || $type == '.TGZ' || $type == '.BZ' || $type == '.BZ2') {
                    echo extract_tar_archive($current, $_POST['name'], $_POST['chmod']);
                } elseif ($type == '.GZ') {
                    echo gz_extract($current, $_POST['name'], $_POST['chmod']);
                }
            }
        } elseif ($_POST['full_extract']) {
            if (!$_POST['name'] || !$_POST['chmod']) {
                echo '<div class="input">
<form action="change.php?go=1&amp;c=' . $r_current . '" method="post">
<div>
' . $lng['change_name'] . '<br/>
<input type="text" name="name" value="' . htmlspecialchars(dirname($current), ENT_COMPAT) . '/"/><br/>
' . $lng['change_chmod'] . '<br/>
<input onkeypress="return number(event)" type="text" name="chmod" size="4" maxlength="4" style="width:28pt;" value="0755"/><br/>
<input name="full_extract" type="hidden" value="1"/>';
                for ($i = 0; $i < $x; ++$i) {
                    echo '<input name="check[]" type="hidden" value="' . $_POST['check'][$i] . '"/>';
                }
                echo '<input type="submit" value="' . $lng['extract_archive'] . '"/>
</div>
</form>
</div>';
            } else {
            	$_POST['check'] = array_map('rawurldecode', $_POST['check']);
                $type = strtoupper(strrchr($h_current, '.'));
                if ($type == '.ZIP' || $type == '.JAR') {
                    echo extract_zip_file($current, $_POST['name'], $_POST['chmod'], $_POST['check']);
                } elseif ($type == '.TAR' || $type == '.TGZ' || $type == '.BZ' || $type == '.BZ2') {
                    echo extract_tar_file($current, $_POST['name'], $_POST['chmod'], $_POST['check']);
                }
            }
        } elseif ($_POST['gz_extract']) {
            if (!$_POST['name'] || !$_POST['chmod']) {
                echo '<div class="input">
<form action="change.php?go=1&amp;c=' . $r_current . '" method="post">
<div>
' . $lng['change_name'] . '<br/>
<input type="text" name="name" value="' . htmlspecialchars(dirname($current), ENT_COMPAT) . '/"/><br/>
' . $lng['change_chmod'] . '<br/>
<input onkeypress="return number(event)" type="text" name="chmod" size="4" maxlength="4" style="width:28pt;" value="0755"/><br/>
<input name="gz_extract" type="hidden" value="1"/>
<input type="submit" value="' . $lng['extract_archive'] . '"/>
</div>
</form>
</div>';
            } else {
                echo gz_extract($current, $_POST['name'], $_POST['chmod']);
            }
        } elseif ($_POST['create_archive']) {
            if (!$_POST['name'] || !$_POST['chmod']) {
                echo '<div class="input">
<form action="change.php?go=1&amp;c=' . $r_current . '" method="post">
<div>
' . $lng['change_name'] . '<br/>
<input type="text" name="name" value="' . $h_current . 'archive.zip"/><br/>
' . $lng['change_chmod'] . '<br/>
<input onkeypress="return number(event)" type="text" name="chmod" size="4" maxlength="4" style="width:28pt;" value="0644"/><br/>
<input name="create_archive" type="hidden" value="1"/>';
                for ($i = 0; $i < $x; ++$i) {
                    echo '<input name="check[]" type="hidden" value="' . $_POST['check'][$i] . '"/>';
                }
                print '<input type="submit" value="' . $lng['create_archive'] . '"/>
</div>
</form>
</div>';
            } else {
            	$_POST['check'] = array_map('rawurldecode', $_POST['check']);
                echo create_zip_archive($_POST['name'], $_POST['chmod'], $_POST['check']);
            }
        } elseif ($_POST['add_archive']) {
            if (!$_POST['name']) {
                header('Location: http://' . $_SERVER['HTTP_HOST'] . str_replace(array('\\', '//'), '/',
                    dirname($_SERVER['PHP_SELF']) . '/') . 'index.php?c=' . dirname($current) .
                    '&add_archive=' . $current, true, 301);
                exit;
            } elseif (!$_POST['dir']) {
                echo '<div class="input">sss
<form action="change.php?go=1&amp;c=' . $r_current . '" method="post">
<div>
' . $lng['add_archive_dir'] . '<br/>
<input type="text" name="dir" value="./"/><br/>
<input name="add_archive" type="hidden" value="' . $_POST['add_archive'] . '"/>';
                for ($i = 0; $i < $x; ++$i) {
                    echo '<input name="check[]" type="hidden" value="' . $_POST['check'][$i] . '"/>';
                }
                print '<input type="submit" name="name" value="' . $lng['add_archive'] . '"/>
</div>
</form>
</div>';
            } else {
            	$_POST['check'] = array_map('rawurldecode', $_POST['check']);
            	$_POST['dir'] = rawurldecode($_POST['dir']);
            	$_POST['add_archive'] = rawurldecode($_POST['add_archive']);

                $type = strtoupper(strrchr($_POST['add_archive'], '.'));

                if ($type == '.ZIP' || $type == '.JAR') {
                    echo add_zip_archive($_POST['add_archive'], $_POST['check'], $_POST['dir']);
                } elseif ($type == '.TAR' || $type == '.TGZ' || $type == '.BZ' || $type == '.BZ2') {
                    echo add_tar_archive($_POST['add_archive'], $_POST['check'], $_POST['dir']);
                }
            }
        } elseif ($_POST['full_rename']) {
            if (!$_GET['go2']) {
                echo '<div class="input">
<form action="change.php?go=1&amp;go2=1&amp;c=' . $r_current . '" method="post">
<div>
' . $lng['change_func2'] . '<br/>
<input type="text" name="name" value="' . $realpath . '"/><br/>
' . $lng['change_del'] . '<br/>
<input type="checkbox" name="del" value="1"/><br/>
<input name="full_rename" type="hidden" value="1"/>';
                for ($i = 0; $i < $x; ++$i) {
                    echo '<input name="check[]" type="hidden" value="' . $_POST['check'][$i] . '"/>';
                }
                echo '<input type="submit" value="' . $lng['ch'] . '"/>
</div>
</form>
</div>';
            } else {
                for ($i = 0; $i < $x; ++$i) {
                	$_POST['check'][$i] = rawurldecode($_POST['check'][$i]);
                    echo frename($_POST['check'][$i], str_replace('//', '/', $_POST['name'] . '/' .
                        basename($_POST['check'][$i])), '', $_POST['del'], $_POST['name']);
                }
            }
        }
        break;

    case 'del':
        if ($mode->is_dir($current)) {
            echo del_dir($current);
        } else {
            echo del_file($current);
        }
        break;

    case 'chmod':
        if (!$_POST['chmod']) {
            echo '<div class="input">
<form action="change.php?go=chmod&amp;c=' . $r_current . '" method="post">
<div>
' . $lng['change_chmod'] . '<br/>
<input onkeypress="return number(event)" type="text" size="4" maxlength="4" style="width:28pt;" name="chmod" value="' . look_chmod($current) . '"/><br/>
<input type="submit" value="' . $lng['ch'] . '"/>
</div>
</form>
</div>';
        } else {
            echo rechmod($current, $_POST['chmod']);
        }
        break;

    case 'create_dir':
        if (!$_POST['name'] || !$_POST['chmod']) {
            echo '<div class="input">
<form action="change.php?go=create_dir&amp;c=' . $r_current . '" method="post">
<div>
' . $lng['change_name'] . '<br/>
<input type="text" name="name" value="dir"/><br/>
' . $lng['change_chmod'] . '<br/>
<input onkeypress="return number(event)" type="text" name="chmod" size="4" maxlength="4" style="width:28pt;" value="0755"/><br/>
<input type="submit" value="' . $lng['cr'] . '"/>
</div>
</form>
</div>';
        } else {
            echo create_dir($current . $_POST['name'], $_POST['chmod']);
        }
        break;

    case 'create_file':
        include 'pattern.dat';
        if (!$_POST['name'] || !$_POST['chmod']) {
            echo '<div class="input">
<form action="change.php?go=create_file&amp;c=' . $r_current . '" method="post">
<div>
' . $lng['change_name'] . '<br/>
<input type="text" name="name" value="file.php"/><br/>
' . $lng['pattern'] . '<br/>
<select name="ptn">';
            $all = sizeof($pattern);
            for ($i = 0; $i < $all; ++$i) {
                echo '<option value="' . $i . '">' . $pattern[$i][0] . '</option>';
            }

            echo '</select><br/>
' . $lng['change_chmod'] . '<br/>
<input onkeypress="return number(event)" type="text" name="chmod" size="4" maxlength="4" style="width:28pt;" value="0644"/><br/>
<input type="submit" value="' . $lng['cr'] . '"/>
</div>
</form>
</div>';
        } else {
            if ($mode->file_exists($current . $_POST['name']) && !$_POST['a']) {
                echo '<div class="red">' . $lng['warning'] . '<br/></div>
<form action="change.php?go=create_file&amp;c=' . $r_current . '" method="post">
<div>
<input type="hidden" name="name" value="' . $_POST['name'] . '"/>
<input type="hidden" name="ptn" value="' . $_POST['ptn'] . '"/>
<input type="hidden" name="chmod" value="' . $_POST['chmod'] . '"/>
<input type="hidden" name="a" value="1"/>
<input type="submit" value="' . $lng['ch'] . '"/>
</div>
</form>';
            } else {
                if ($realname) { 
                    $realpath = $realpath . $_POST['name'];
                } else {
                    $realpath = $h_current . $_POST['name'];
                }

                echo '<div class="border">' . $lng['file'] . ' <strong><a href="edit.php?' . $r_current .
                    $_POST['name'] . '">' . $realpath . '</a></strong> (' . $_POST['chmod'] .
                    ')<br/></div>' . create_file($current . $_POST['name'], $pattern[intval($_POST['ptn'])][1], $_POST['chmod']);
            }
        }
        break;

    case 'rename':
        echo frename($current, $_POST['name'], $_POST['chmod'], $_POST['del'], $_POST['name']);
        echo rechmod($_POST['name'], $_POST['chmod']);
        break;

    case 'del_zip_archive':
        print del_zip_archive($_GET['c'], $_GET['f']);
        break;

    case 'del_tar_archive':
        print del_tar_archive($_GET['c'], $_GET['f']);
        break;

    case 'upload':
        if ((((!$_POST['url'] || $_POST['url'] == 'http://') && ($_FILES['f']['error'] ||
            !$_FILES)) && !$_POST['f']) || !$_POST['name'] || !$_POST['chmod']) {
            print '<div class="input">
<form action="change.php?go=upload&amp;c=' . $r_current . '" method="post" enctype="multipart/form-data">
<div>
' . $lng['url'] . '<br/>
<textarea name="url" type="text" rows="3" cols="48" wrap="off">http://</textarea><br/>
' . $lng['headers'] .'<br/>
<textarea rows="3" cols="32" name="headers">User-Agent: ' . htmlspecialchars($_SERVER['HTTP_USER_AGENT'], ENT_NOQUOTES) . '
Referer:
Accept: ' . htmlspecialchars($_SERVER['HTTP_ACCEPT'], ENT_NOQUOTES) . '
Accept-Charset: ' . htmlspecialchars($_SERVER['HTTP_ACCEPT_CHARSET'], ENT_NOQUOTES) . '
Accept-Language: ' . htmlspecialchars($_SERVER['HTTP_ACCEPT_LANGUAGE'], ENT_NOQUOTES) . '
Connection: Close</textarea><br/>
' . $lng['file'] . ' (' . ini_get('upload_max_filesize') . ')<br/>
<input type="file" name="f"/><br/>
' . $lng['name'] . '<br/>
<input type="text" name="name" value="' . $h_current . '"/><br/>
' . $lng['change_chmod'] . '<br/>
<input onkeypress="return number(event)" type="text" name="chmod" value="0644" size="4" maxlength="4" style="width:28pt;"/><br/>
<input type="submit" value="' . $lng['upload'] . '"/>
</div>
</form>
</div>';
        } else {
            if (!$_FILES['f']['error']) {
                echo upload_files($_FILES['f']['tmp_name'], $_FILES['f']['name'], $_POST['name'],
                    $_POST['chmod']);
            } else {
                echo upload_url($_POST['url'], $_POST['name'], $_POST['chmod'], $_POST['headers']);
            }
        }
        break;

    case 'mod':
        echo '<div class="red">
<ul>
<li><a href="change.php?go=search&amp;c=' . $r_current . '">' . $lng['search'] . '</a></li>
<li><a href="change.php?go=eval&amp;c=' . $r_current . '">' . $lng['eval'] . '</a></li>
<li><a href="change.php?go=sql&amp;c=' . $r_current . '">' . $lng['sql'] . '</a></li>
<li><a href="change.php?go=tables&amp;c=' . $r_current . '">' . $lng['tables'] . '</a></li>
<li><a href="change.php?go=installer&amp;c=' . $r_current . '">' . $lng['create_sql_installer'] . '</a></li>
<li><a href="change.php?go=scan&amp;c=' . $r_current . '">' . $lng['scan'] . '</a></li>
<li><a href="change.php?go=send_mail&amp;c=' . $r_current . '">' . $lng['send_mail'] . '</a></li>
<li><a href="change.php?phpinfo">' . $lng['phpinfo'] . '</a> (' . PHP_VERSION . ')</li>
<li><a href="change.php?go=new_version&amp;c=' . $r_current . '">' . $lng['new_version'] . '</a></li>
</ul>
<span style="color:#000;">&#187;</span> ' . htmlspecialchars($_SERVER['SERVER_SOFTWARE'], ENT_NOQUOTES) . '<br/>
<span style="color:#000;">&#187;</span> ' . htmlspecialchars(php_uname(), ENT_NOQUOTES) . '<br/>
<span style="color:#000;">&#187;</span> ' . strftime('%d.%m.%Y / %H') . '<span style="text-decoration:blink;">:</span>' . strftime('%M') . '<br/></div>';
	break;

    case 'new_version':
    
        $new = getData('http://wapinet.ru/gmanager/gmanager.txt');
        $new_version = $new['body'];
        if ($new_version) {
            print '<div class="red">' . $lng['version_new'] . ': ' . $new_version . '<br/>
' . $lng['version_old'] . ': ' . $version . '<br/>';
            if ($new_version < $version) {
                print $lng['new_version_false'] . '<br/></div>';
            } else {
                print $lng['new_version_true'] .
                    '<br/>&#187; <a href="http://wapinet.ru/gmanager/gmanager.zip">' . $lng['get'] .
                    '</a><br/><input name="" value="http://wapinet.ru/gmanager/gmanager.zip" size="39"/></div>';
            }
        } else {
            print '<div class="red">' . $lng['not_connect'] . '<br/></div>';
        }
        break;

    case 'scan':
        if (!$_POST['url'] || $_POST['url'] == 'http://') {
            print '<div class="input">
<form action="change.php?go=scan&amp;c=' . $r_current . '" method="post">
<div>
' . $lng['url'] . '<br/>
<input type="text" name="url" value="http://"/><br/>
<input type="submit" value="' . $lng['look'] . '"/>
</div>
</form>
</div>';
        } else {
            if ($url = getData($_POST['url'])) {
				$url = $url['headers']."\r\n\r\n".$url['body'];
                echo code($url, 0);
            } else {
                echo '<div class="red">' . $lng['not_connect'] . '<br/></div>';
            }
        }
        break;

    case 'send_mail':
        if (!$_POST['theme'] || !$_POST['mess'] || !$_POST['to'] || !$_POST['from']) {
            print '<div class="input">
<form action="change.php?go=send_mail&amp;c=' . $r_current . '" method="post">
<div>
' . $lng['mail_to'] . '<br/>
<input type="text" name="to" value="@"/><br/>
' . $lng['mail_from'] . '<br/>
<input type="text" name="from" value="admin@' . $_SERVER['HTTP_HOST'] .
                '"/><br/>
' . $lng['mail_theme'] . '<br/>
<input type="text" name="theme" value="Hello"/><br/>
' . $lng['mail_mess'] . '<br/>
<textarea name="mess" rows="8" cols="48"></textarea><br/>
<input type="submit" value="' . $lng['send_mail'] . '"/>
</div>
</form>
</div>';
        } else {
            echo send_mail($_POST['theme'], $_POST['mess'], $_POST['to'], $_POST['from']);
        }
        break;

    case 'eval':
        if ($_POST['eval']) {
            echo show_eval($_POST['eval']);
        }
        echo '<div class="input">
<form action="change.php?go=eval&amp;c=' . $r_current . '" method="post">
<div>
' . $lng['php_code'] . '<br/>
<textarea name="eval" rows="10" cols="48">' . htmlspecialchars($_POST['eval'], ENT_NOQUOTES) .
            '</textarea><br/>
<input type="submit" value="' . $lng['eval_go'] . '"/>
</div>
</form>
</div>';
        break;

    case 'search':
        if ($_POST['search']) {
            if ($string) {
echo '<div>
<form action="change.php?" method="get">
<div>
<input type="text" name="c" value="' . $realpath . '"/><br/>
<input type="hidden" name="go" value="search"/>
<input type="submit" value="' . $lng['go'] . '"/>
</div>
</form>
</div>';
            }
echo '<form action="change.php?c=' . $r_current . '&amp;go=1" method="post">
<div class="telo">
<table>
<tr>
<th>' . $lng['ch_index'] . '</th>
<th>' . $lng['name'] . '</th>
<th>' . $lng['get'] . '</th>
<th>' . $lng['type'] . '</th>
<th>' . $lng['size'] . '</th>
<th>' . $lng['change'] . '</th>
<th>' . $lng['del'] . '</th>
<th>' . $lng['chmod'] . '</th>
<th>' . $lng['date'] . '</th>
<th>' . $lng['n'] . '</th>
</tr>';
            echo search($_POST['where'], $_POST['search'], $_POST['in'], $_POST['register']);
            echo '<tr><td class="w" colspan="9" style="text-align:left;padding:0 0 0 1%;"><input type="checkbox" value="check" onclick="check(this.form,\'check[]\',this.checked)"/> ' .
                $lng['check'] . '</td></tr>
</table>
<div class="ch">
<input type="submit" name="full_chmod" value="' . $lng['chmod'] . '"/>
<input type="submit" name="full_del" value="' . $lng['del'] . '"/>
<input type="submit" name="full_rename" value="' . $lng['change'] . '"/>
<input type="submit" name="create_archive" value="' . $lng['create_archive'] .
                '"/>
</div>
</div>
</form>
<div class="rb">' . $lng['create'] .
                ' <a href="change.php?go=create_file&amp;c=' . $r_current . '">' . $lng['file'] .
                '</a> / <a href="change.php?go=create_dir&amp;c=' . $r_current . '">' . $lng['dir'] .
                '</a><br/></div>
<div class="rb"><a href="change.php?go=upload&amp;c=' . $r_current . '">' . $lng['upload'] .
                '</a><br/></div>
<div class="rb"><a href="change.php?go=mod&amp;c=' . $r_current . '">' . $lng['mod'] .
                '</a><br/></div>';
        }
        echo '<div class="input">
<form action="change.php?go=search&amp;c=' . $r_current . '" method="post">
<div>
' . $lng['where_search'] . '<br/>
<input type="text" name="where" value="' . $realpath . '"/><br/>
<select name="in">
<option value="0">' . $lng['in_files'] . '</option>
<option value="1"';
        if ($_POST['in']) {
            echo ' selected="selected"';
        }
        echo '>' . $lng['in_text'] . '</option>
</select><br/>
' . $lng['what_search'] . '<br/>
<input type="text" name="search" value="' . htmlspecialchars($_POST['search'], ENT_NOQUOTES) .
            '"/><br/>
' . $lng['register'] . '<br/>
<select name="register">
<option value="0">' . $lng['yes'] . '</option>
<option value="1"';
        if ($_POST['register']) {
            print ' selected="selected"';
        }
        print '>' . $lng['no'] . '</option>
</select><br/>
<input type="submit" value="' . $lng['eval_go'] . '"/>
</div>
</form>
</div>';
        break;

    case 'sql':
        $_POST['sql'] = trim($_POST['sql']);
        if ($_POST['name'] && $_POST['host']) {
            include 'pattern.dat';
            $all = sizeof($sql_ptn);
            $tmp = '<select id="ptn" onchange="javascript:paste(this.value);">';
            for ($i = 0; $i < $all; ++$i) {
                $tmp .= '<option value="' . htmlspecialchars($sql_ptn[$i][1], ENT_COMPAT) . '">' . $sql_ptn[$i][0] .
                    '</option>';
            }
            $tmp .= '</select>';

            if (!$_POST['sql'] && !$_POST['db']) {
                $_POST['sql'] = 'SHOW DATABASES';
            } elseif (!$_POST['sql']) {
                $_POST['sql'] = 'SHOW TABLES';
            }

            echo '<div>&#160;' . $_POST['name'] . ($_POST['db'] ? ' =&gt; ' . $_POST['db'] :
                '') . '<br/></div><div class="red">' . sql($_POST['name'], $_POST['pass'], $_POST['host'],
                $_POST['db'], $_POST['sql'], $_POST['charset']) . '<br/></div>
<div>
<form action="">
<div>
<textarea rows="' . (substr_count($_POST['sql'], "\n") + 1) . '" cols="48">' .
                htmlspecialchars($_POST['sql'], ENT_NOQUOTES) . '</textarea>
</div>
</form>
</div>
<div class="input">
<form action="change.php?go=sql&amp;c=' . $r_current . '" method="post" id="post">
<div>
' . $lng['sql_query'] . ' ' . $tmp . '<br/>
<textarea id="sql" name="sql" rows="4" cols="48"></textarea><br/>
<input type="hidden" name="name" value="' . $_POST['name'] . '"/>
<input type="hidden" name="pass" value="' . $_POST['pass'] . '"/>
<input type="hidden" name="host" value="' . $_POST['host'] . '"/>
<input type="hidden" name="db" value="' . $_POST['db'] . '"/>
<input type="hidden" name="charset" value="' . $_POST['charset'] . '"/>
<input type="submit" value="' . $lng['sql'] . '"/>
</div>
</form>
</div>';
        } else {
            echo '<div class="input">
<form action="change.php?go=sql&amp;c=' . $r_current . '" method="post" id="post">
<div>
' . $lng['mysql_user'] . '<br/>
<input type="text" name="name" value=""/><br/>
' . $lng['mysql_pass'] . '<br/>
<input type="text" name="pass"/><br/>
' . $lng['mysql_host'] . '<br/>
<input type="text" name="host" value="localhost"/><br/>
' . $lng['mysql_db'] . '<br/>
<input type="text" name="db"/><br/>
' . $lng['charset'] . '<br/>
<input type="text" name="charset" value="utf8"/><br/>
' . $lng['sql_query'] . '<br/>
<textarea id="sql" name="sql" rows="4" cols="48">' . htmlspecialchars($_POST['sql'], ENT_NOQUOTES) .
                '</textarea><br/>
<input type="submit" value="' . $lng['sql'] . '"/>
</div>
</form>
</div>';
        }
        break;

    case 'tables':
        if (!$mode->file_exists($_POST['tables'])) {
            print '<div class="input">
<form action="change.php?go=tables&amp;c=' . $r_current . '" method="post">
<div>
' . $lng['mysql_user'] . '<br/>
<input type="text" name="name"/><br/>
' . $lng['mysql_pass'] . '<br/>
<input type="text" name="pass"/><br/>
' . $lng['mysql_host'] . '<br/>
<input type="text" name="host" value="localhost"/><br/>
' . $lng['mysql_db'] . '<br/>
<input type="text" name="db"/><br/>
' . $lng['charset'] . '<br/>
<input type="text" name="charset" value="utf8"/><br/>
' . $lng['tables_file'] . '<br/>
<input type="text" name="tables" value="' . $h_current . '"/><br/>
<input type="submit" value="' . $lng['tables'] . '"/>
</div>
</form>
</div>';
        } else {
            print '<div class="red">' . sql($_POST['name'], $_POST['pass'], $_POST['host'],
                $_POST['db'], $mode->file_get_contents($_POST['tables']), $_POST['charset']) .
                '<br/></div>';
        }
        break;

    case 'installer':
        if (substr($h_current, -1) != '/') {
            $d_current = str_replace('\\', '/', htmlspecialchars(dirname($current) . '/'));
        }
        else{
        	$d_current = $h_current;
       	}

        if (!$_POST) {
            print '<div class="input">
<form action="change.php?go=installer" method="post">
<div>
' . $lng['mysql_user'] . '<br/>
<input type="text" name="name"/><br/>
' . $lng['mysql_pass'] . '<br/>
<input type="text" name="pass"/><br/>
' . $lng['mysql_host'] . '<br/>
<input type="text" name="host" value="localhost"/><br/>
' . $lng['mysql_db'] . '<br/>
<input type="text" name="db"/><br/>
' . $lng['charset'] . '<br/>
<input type="text" name="charset" value="utf8"/><br/>
' . $lng['tables_file'] . '<br/>
<input type="text" name="tables" value="' . $h_current . '"/><br/>
' . $lng['chmod'] . '<br/>
<input onkeypress="return number(event)" type="text" name="chmod" size="4" maxlength="4" style="width:28pt;" value="0644"/><br/>
<input name="save_as" type="submit" value="' . $lng['save_as'] . '"/><input type="text" name="c" value="' . $d_current . 'sql_installer.php"/><br/>
</div>
</form>
</div>';
        } else {
            if (!$sql = sql_installer(trim($_POST['host']), trim($_POST['name']), trim($_POST['pass']),
                trim($_POST['db']), trim($_POST['charset']), $mode->file_get_contents($_POST['tables']))) {
                echo '<div class="red">' . $lng['sql_parser_error'] . '<br/></div>';
            } else {
                echo create_file(trim($_POST['c']), $sql, $_POST['chmod']);
            }
        }
        break;
}

echo '<div class="rb">' . round(microtime(true) - $ms, 4) . '<br/></div>' . $foot;
?>