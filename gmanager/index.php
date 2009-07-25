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


require 'functions.php';


$current = c($_SERVER['QUERY_STRING'], isset($_GET['c']) ? rawurlencode($_GET['c']) : '');
if($current == '.'){
	$h_current = htmlspecialchars($mode->getcwd(), ENT_COMPAT);
}
else{
	$h_current = htmlspecialchars($current, ENT_COMPAT);
}
$r_current = str_replace('%2F', '/', rawurlencode($current));

$type = get_type($h_current);
$archive = is_archive($type);
$f = 0;
$if = isset($_GET['f']);
$ia = isset($_GET['add_archive']);

send_header($_SERVER['HTTP_USER_AGENT']);

echo str_replace('%dir%', $h_current, $top) . '
<div class="w2">
' . $lng['title_index'] . '<br/>
</div>
' . this($current);

if ($string) {
echo '<div>
<form action="index.php?" method="get">
<div>';
    if ($ia) {
        echo '<input type="hidden" name="add_archive" value="' . rawurlencode($_GET['add_archive']) . '"/><input type="hidden" name="go" value="1"/>';
    }
echo '<input type="text" name="c" value="' . $h_current . '"/><br/>
<input type="submit" value="' . $lng['go'] . '"/>
</div>
</form>
</div>';
}

if($idown = isset($_GET['down'])){
	$down = '&amp;up';
	$mnem = '&#171;';
}
else{
	$down = '&amp;down';
	$mnem = '&#187;';
}

if (!$if){
	if (!$archive) {

	$itype = '';

    if (isset($_GET['time'])) {
    	$itype = 'time';
echo '<form action="change.php?c=' . $r_current . '&amp;go=1" method="post">
<div class="telo">
<table>
<tr>
<th>' . $lng['ch_index'] . '</th>
' . ($index['name'] ? '<th><a href="?c=' . $r_current . '">' . $lng['name'] . '</a></th>' : '') . '
' . ($index['down'] ? '<th>' . $lng['get'] . '</th>' : '') . '
' . ($index['type'] ? '<th><a href="?c=' . $r_current . '&amp;type">' . $lng['type'] . '</a></th>' : '') . '
' . ($index['size'] ? '<th><a href="?c=' . $r_current . '&amp;size">' . $lng['size'] . '</a></th>' : '') . '
' . ($index['change'] ? '<th>' . $lng['change'] . '</th>' : '') . '
' . ($index['del'] ? '<th>' . $lng['del'] . '</th>' : '') . '
' . ($index['chmod'] ? '<th><a href="?c=' . $r_current . '&amp;chmod">' . $lng['chmod'] . '</a></th>' : '') . '
' . ($index['date'] ? '<th>' . $mnem . ' <a href="?c=' . $r_current . '&amp;time' . $down . '">' . $lng['date'] . '</a></th>' : '') . '
<th>' . $lng['n'] . '</th>
</tr>';
    }
	else if (isset($_GET['type'])) {
		$itype = 'type';
echo '<form action="change.php?c=' . $r_current . '&amp;go=1" method="post">
<div class="telo">
<table>
<tr>
<th>' . $lng['ch_index'] . '</th>
' . ($index['name'] ? '<th><a href="?c=' . $r_current . '">' . $lng['name'] . '</a></th>' : '') . '
' . ($index['down'] ? '<th>' . $lng['get'] . '</th>' : '') . '
' . ($index['type'] ? '<th>' . $mnem . ' <a href="?c=' . $r_current . '&amp;type' . $down . '">' . $lng['type'] . '</a></th>' : '') . '
' . ($index['size'] ? '<th><a href="?c=' . $r_current . '&amp;size">' . $lng['size'] . '</a></th>' : '') . '
' . ($index['change'] ? '<th>' . $lng['change'] . '</th>' : '') . '
' . ($index['del'] ? '<th>' . $lng['del'] . '</th>' : '') . '
' . ($index['chmod'] ? '<th><a href="?c=' . $r_current . '&amp;chmod">' . $lng['chmod'] . '</a></th>' : '') . '
' . ($index['date'] ? '<th><a href="?c=' . $r_current . '&amp;time">' . $lng['date'] . '</a></th>' : '') . '
<th>' . $lng['n'] . '</th>
</tr>';
    }
	else if (isset($_GET['size'])) {
		$itype = 'size';
echo '<form action="change.php?c=' . $r_current . '&amp;go=1" method="post">
<div class="telo">
<table>
<tr>
<th>' . $lng['ch_index'] . '</th>
' . ($index['name'] ? '<th><a href="?c=' . $r_current . '">' . $lng['name'] . '</a></th>' : '') . '
' . ($index['down'] ? '<th>' . $lng['get'] . '</th>' : '') . '
' . ($index['type'] ? '<th><a href="?c=' . $r_current . '&amp;type">' . $lng['type'] . '</a></th>' : '') . '
' . ($index['size'] ? '<th>' . $mnem . ' <a href="?c=' . $r_current . '&amp;size' . $down . '">' . $lng['size'] . '</a></th>' : '') . '
' . ($index['change'] ? '<th>' . $lng['change'] . '</th>' : '') . '
' . ($index['del'] ? '<th>' . $lng['del'] . '</th>' : '') . '
' . ($index['chmod'] ? '<th><a href="?c=' . $r_current . '&amp;chmod">' . $lng['chmod'] . '</a></th>' : '') . '
' . ($index['date'] ? '<th><a href="?c=' . $r_current . '&amp;time">' . $lng['date'] . '</a></th>' : '') . '
<th>' . $lng['n'] . '</th>
</tr>';
    }
	else if (isset($_GET['chmod'])) {
		$itype = 'chmod';
echo '<form action="change.php?c=' . $r_current . '&amp;go=1" method="post">
<div class="telo">
<table>
<tr>
<th>' . $lng['ch_index'] . '</th>
' . ($index['name'] ? '<th><a href="?c=' . $r_current . '">' . $lng['name'] . '</a></th>' : '') . '
' . ($index['down'] ? '<th>' . $lng['get'] . '</th>' : '') . '
' . ($index['type'] ? '<th><a href="?c=' . $r_current . '&amp;type">' . $lng['type'] . '</a></th>' : '') . '
' . ($index['size'] ? '<th><a href="?c=' . $r_current . '&amp;size">' . $lng['size'] . '</a></th>' : '') . '
' . ($index['change'] ? '<th>' . $lng['change'] . '</th>' : '') . '
' . ($index['del'] ? '<th>' . $lng['del'] . '</th>' : '') . '
' . ($index['chmod'] ? '<th>' . $mnem . ' <a href="?c=' . $r_current . '&amp;chmod' . $down . '">' . $lng['chmod'] . '</a></th>' : '') . '
' . ($index['date'] ? '<th><a href="?c=' . $r_current . '&amp;time">' . $lng['date'] . '</a></th>' : '') . '
<th>' . $lng['n'] . '</th>
</tr>';
    }
	else {
		$itype = '';
echo '<form action="change.php?c=' . $r_current . '&amp;go=1" method="post">
<div class="telo">
<table>
<tr>
<th>' . $lng['ch_index'] . '</th>
' . ($index['name'] ? '<th>' . $mnem . ' <a href="?c=' . $r_current . $down . '">' . $lng['name'] . '</a></th>' : '') . '
' . ($index['down'] ? '<th>' . $lng['get'] . '</th>' : '') . '
' . ($index['type'] ? '<th><a href="?c=' . $r_current . '&amp;type">' . $lng['type'] . '</a></th>' : '') . '
' . ($index['size'] ? '<th><a href="?c=' . $r_current . '&amp;size">' . $lng['size'] . '</a></th>' : '') . '
' . ($index['change'] ? '<th>' . $lng['change'] . '</th>' : '') . '
' . ($index['del'] ? '<th>' . $lng['del'] . '</th>' : '') . '
' . ($index['chmod'] ? '<th><a href="?c=' . $r_current . '&amp;chmod">' . $lng['chmod'] . '</a></th>' : '') . '
' . ($index['date'] ? '<th><a href="?c=' . $r_current . '&amp;time">' . $lng['date'] . '</a></th>' : '') . '
<th>' . $lng['n'] . '</th>
</tr>';
    }
}
elseif($archive != 'GZ'){
	echo '<form action="change.php?c=' . $r_current . '&amp;go=1" method="post">
<div class="telo">
<table>
<tr>
<th>' . $lng['ch_index'] . '</th>
' . ($index['name'] ? '<th>' . $mnem . ' <a href="?c=' . $r_current . $down . '">' . $lng['name'] . '</a></th>' : '') . '
' . ($index['down'] ? '<th>' . $lng['get'] . '</th>' : '') . '
' . ($index['type'] ? '<th>' . $lng['type'] . '</th>' : '') . '
' . ($index['size'] ? '<th>' . $lng['size'] . '</th>' : '') . '
' . ($index['change'] ? '<th>' . $lng['change'] . '</th>' : '') . '
' . ($index['del'] ? '<th>' . $lng['del'] . '</th>' : '') . '
' . ($index['chmod'] ? '<th>' . $lng['chmod'] . '</th>' : '') . '
' . ($index['date'] ? '<th>' . $lng['date'] . '</th>' : '') . '
<th>' . $lng['n'] . '</th>
</tr>';
}
}

if ($archive == 'ZIP') {
    if ($if) {
        echo look_zip_file($current, $_GET['f']);
    }
	else {
        echo list_zip_archive($current, $idown);
        $f = 1;
    }
} elseif ($archive == 'TAR') {
    if ($if) {
        echo look_tar_file($current, $_GET['f']);
    }
	else {
        echo list_tar_archive($current, $idown);
        $f = 1;
    }
} elseif ($archive == 'GZ') {
   echo gz($current) . '<div class="ch"><form action="change.php?c=' . $r_current . '&amp;go=1" method="post"><div><input type="submit" name="gz_extract" value="' . $lng['extract_archive'] . '"/></div></form></div>';
    $if = true;
}
else {
    look($current, $itype, $idown);
}

if (!$if) {
	echo '<tr><td class="w" colspan="' . (array_sum($GLOBALS['index']) + 2) . '" style="text-align:left;padding:0 0 0 1%;"><input type="checkbox" value="check" onclick="check(this.form,\'check[]\',this.checked)"/> '.$lng['check'].'</td></tr>';
}


if ($mode->file_exists($current) || $mode->is_link($current)) {
	if($archive){
		$current_d = str_replace('%2F', '/', rawurlencode(dirname($current)));
		$found = '<div class="rb">' . $lng['create'] . ' <a href="change.php?go=create_file&amp;c=' . $current_d . '">' . $lng['file'] . '</a> / <a href="change.php?go=create_dir&amp;c=' . $current_d . '">' . $lng['dir'] . '</a><br/></div><div class="rb"><a href="change.php?go=upload&amp;c=' . $current_d . '">' . $lng['upload'] . '</a><br/></div><div class="rb"><a href="change.php?go=mod&amp;c=' . $current_d . '">' . $lng['mod'] . '</a><br/></div>';
}
	else{
		$found = '<form action="'.$_SERVER['PHP_SELF'].'?'.htmlspecialchars($_SERVER['QUERY_STRING'], ENT_COMPAT, 'UTF-8').'" method="post"><div><input name="limit" value="'.$limit.'" type="text" style="width:2%"/><input type="submit" value="'.$lng['limit'].'"/></div></form>
<div class="rb">' . $lng['create'] . ' <a href="change.php?go=create_file&amp;c=' . $r_current . '">' . $lng['file'] . '</a> / <a href="change.php?go=create_dir&amp;c=' . $r_current . '">' . $lng['dir'] . '</a><br/></div>
<div class="rb"><a href="change.php?go=upload&amp;c=' . $r_current . '">' . $lng['upload'] . '</a><br/></div>
<div class="rb"><a href="change.php?go=mod&amp;c=' . $r_current . '">' . $lng['mod'] . '</a><br/></div>';
	}
}
else {
    $found = '<div class="red">' . $lng['not_found'] . '(' . $h_current . ')' . '<br/></div>';
}


$tm = '<div class="rb">' . round(microtime(true) - $ms, 4) . '<br/></div>';

if (!$if && !$f && !$ia) {
echo '</table>
<div class="ch">
<input type="submit" name="full_chmod" value="' .$lng['chmod'] . '"/>
<input'.($del_notify ? ' onclick="return confirm(\''.$lng['del_notify'].'\')"' : '').' type="submit" name="full_del" value="' . $lng['del'] . '"/>
<input type="submit" name="full_rename" value="' . $lng['change'] . '"/>
<input type="submit" name="fname" value="' . $lng['rename'] . '"/>
<input type="submit" name="create_archive" value="' . $lng['create_archive'] . '"/>
</div>
</div>
</form>' . $found . $tm . $foot;
} elseif ($f) {
echo '</table>
<div class="ch">
<input type="submit" name="full_extract" value="' . $lng['extract_file'] . '"/><br/>
</div>
<div class="ch">
<input type="submit" name="mega_full_extract" value="' . $lng['extract_archive'] . '"/><br/>
</div>
<div class="ch">
<input type="submit" name="add_archive" value="' . $lng['add_archive'] . '"/>
</div>
</div>
</form>' . $found . $tm . $foot;
} elseif ($ia) {
echo '</table>
<div class="ch">
<input type="hidden" name="add_archive" value="' . rawurlencode($_GET['add_archive']) . '"/>
<input type="submit" name="name" value="' . $lng['add_archive'] . '"/>
</div>
</div>
</form>' . $found . $tm . $foot;
}
else {
    echo $found . $tm . $foot;
}
?>