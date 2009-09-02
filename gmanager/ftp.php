<?php
// кодировка UTF-8
/**
 * 
 * This software is distributed under the GNU LGPL v3.0 license.
 * @author Gemorroj
 * @copyright 2008-2009 http://wapinet.ru
 * @license http://www.gnu.org/licenses/lgpl-3.0.txt
 * @link http://wapinet.ru/gmanager/
 * @version 0.7
 * 
 * PHP version >= 5.2.1
 * 
 */


$mode = new ftp;
$class = 'ftp';

class ftp
{
	private $user = 'root'; // логин
	private $password = ''; // пароль
	private $host = 'localhost'; // хост
	private $port = 21; // порт
    private $res;
    private $url;
    private $rawlist;
    private $dir;

    public function __construct()
    {
        // установка соединения
        $this->res = ftp_connect($this->host, $this->port, 10);

        // вход с именем пользователя и паролем
        ftp_login($this->res, $this->user, $this->password);

        // включение пассивного режима
        ftp_pasv($this->res, true);
        
        // формируем строку URL
        //$this->url = 'ftp://'.$this->user.':'.$this->password.'@'.$this->host.':'.$this->port;
    }



    public function __destruct()
    {
    	// закрываем соединение
        return ftp_close($this->res);
    }


    //////////////////////////////////////////////////////////////////

   	public static function change_symbol($str = ''){
		return ($str[0] == '/' ? $str : '/'.$str);
	}

    public function mkdir($dir = '', $chmod = '0755')
    {
    	ftp_chdir($this->res, '/');
    	if(!$this->is_dir($dir)){
        	$tmp = ftp_mkdir($this->res, $dir);
        }
        else{
        	$tmp = true;
       	}
        $this->chmod($dir, $chmod);
        return $tmp;
    }

    public function chmod($file = '', $chmod = '0755')
    {
    	/*
    	$win = ftp_systype($this->res);
    	if($win[0] . $win[1] . $win[2] == 'WIN'){
    		trigger_error($GLOBALS['lng']['win_chmod']);
    		return false;
   		}
   		*/

    	ftp_chdir($this->res, '/');
    	settype($chmod, 'string');
  		$strlen = strlen($chmod);
		if(!ctype_digit($chmod) || ($strlen != 3 && $strlen != 4)){
    		return false;
   		}
   		if($strlen == 3){
    		$chmod = '0' . $chmod;
   		}
		if($file[0] != '/'){
			$file = '/' . $file;
		}
        return ftp_chmod($this->res, octdec(intval($chmod)), $file);
    }

	public function file_get_contents($str = ''){
		ftp_chdir($this->res, '/');
    	$tmp = fopen('php://temp', 'r+');

 		if(ftp_fget($this->res, $tmp, $str, FTP_ASCII, 0)){
			rewind($tmp);
        	return stream_get_contents($tmp);
    	}
		else{
        	return false;
		}
	}

	public function file_put_contents($file = '', $data = ''){
		$php_temp = dirname(__FILE__).'/data/GmanagerEditor'.time().'.tmp';
		file_put_contents($php_temp, $data);
		chmod($php_temp, 0666);

		$tmp = iconv_substr($file, 0, strrpos($file, '/'));
		if($tmp === false){
			$tmp = substr($file, 0, strrpos($file, '/'));
		}

		ftp_chdir($this->res, $tmp);
		$result = ftp_put($this->res, basename($file), $php_temp, FTP_BINARY);

		unlink($php_temp);
		return $result;
	}

    public function is_dir($str = ''){
    	//$str = self::change_symbol($str);
    	//return is_dir($this->url.$str);
    	$dir = str_replace('\\', '/', dirname($str));
    	if($str == '.' || $str == '..' || $str == '/' || $str == './' || $str == $dir){
    		return true;
   		}
    	if(!isset($this->rawlist[$dir])){
    		$this->rawlist($dir);
   		}

		$b = basename($str);
    	return (isset($this->rawlist[$dir][$b]) && $this->rawlist[$dir][$b]['type'] == 'dir');
   	}

    public function is_file($str = ''){
   		//$str = self::change_symbol($str);
    	//return is_file($this->url.$str);
    	$dir = str_replace('\\', '/', dirname($str));
   		if($str == '.' || $str == '..' || $str == '/' || $str == './' || $str == $dir){
    		return false;
   		}

    	if(!isset($this->rawlist[$dir])){
    		$this->rawlist($dir);
   		}

   		$b = basename($str);
    	return (isset($this->rawlist[$dir][$b]) && $this->rawlist[$dir][$b]['type'] == 'file');
   	}

    public function is_link($str = ''){
    	return false;
    	//$str = self::change_symbol($str);
    	//return is_link($this->url.$str);
   	}

    public function is_readable($str = ''){
    	return true;
    	//$str = self::change_symbol($str);
    	//return is_readable($this->url.$str);
   	}

    public function is_writable($str = ''){
    	return true;
    	//$str = self::change_symbol($str);
    	//return is_writable($this->url.$str);
   	}

    public function filesize($str = ''){
    	//$str = self::change_symbol($str);
    	ftp_chdir($this->res, '/');
    	return sprintf('%u', ftp_size($this->res, $str));
   	}

    public function file_exists($str = ''){
    	//$str = self::change_symbol($str);
    	//return file_exists($this->url.$str);
    	return ($this->is_file($str) || $this->is_dir($str) || $this->is_link($str));
   	}

    public function filemtime($str = ''){
    	//$str = self::change_symbol($str);
    	//return filemtime($this->url.$str);
    	$dir = str_replace('\\', '/', dirname($str));
    	if(!isset($this->rawlist[$dir])){
    		$this->rawlist($dir);
   		}
    	return $this->rawlist[$dir][basename($str)]['time'];
   	}

    public function unlink($str = ''){
    	//$str = self::change_symbol($str);
    	ftp_chdir($this->res, '/');
    	return ftp_delete($this->res, $str);
   	}

    public function rename($from = '', $to = ''){
    	//$from = self::change_symbol($from);
    	//$to = self::change_symbol($to);
    	ftp_chdir($this->res, '/');
    	return ftp_rename($this->res, $from, $to);
   	}

    public function copy($from = '', $to = '', $chmod = '0644'){
    	//$from = self::change_symbol($from);
    	//$to = self::change_symbol($to);
    	//$result = copy($this->url.$from, $this->url.$to);
    	//$this->chmod($this->url.$to, $chmod);
    	if($result = $this->file_put_contents($to, $this->file_get_contents($from))){
    		$this->chmod($to, $chmod);
   		}

    	return $result;
   	}

    public function rmdir($str = ''){
    	//$str = self::change_symbol($str);
    	ftp_chdir($this->res, '/');
    	return ftp_rmdir($this->res, $str);
   	}

    public function iterator($str = ''){
    	$tmp = array();

    	if(!isset($this->rawlist[$str])){
    		$this->rawlist($str);
   		}

    	foreach($this->rawlist[$str] as $var){
    		$tmp[] = basename($var['file']);
   		}

   		return $tmp;
   	}

    public function fileperms($str = ''){
    	//$str = self::change_symbol($str);
    	//return fileperms($this->url.$str);
    	$dir = str_replace('\\', '/', dirname($str));
    	if(!isset($this->rawlist[$dir])){
    		$this->rawlist($dir);
   		}
    	return $this->rawlist[$dir][basename($str)]['chmod'];
   	}

    public function getcwd(){
    	$str = ftp_pwd($this->res);
    	if($str == '.'){
    		$str = '/';
   		}
    	return $str;
   	}

   	private function rawlist($dir = '/'){
   		ftp_chdir($this->res, '/');
   		$raw_dir = $dir = str_replace('\\', '/', $dir);
   		if(preg_match('/^[A-Z]+?:[\\*|\/*]+(.*)/', $dir, $match)){
   			$raw_dir = $match[1] ? '/'.$match[1] : '/';
		}

		$items = array();
   		if($list = ftp_rawlist($this->res, '/' . $raw_dir)){

			foreach($list as $var){
				@preg_replace(
					'`^(.{10}+)\s*(\d{1,3})\s*(\d+?|\w+?)'.
					'\s*(\d+?|\w+?)\s*(\d*)\s'.
					'([a-zA-Z]{3}+)\s*([0-9]{1,2}+)'.
					'\s*([0-9]{2}+):?([0-9]{2}+)\s*(.*)$`Ue',

					'$items[trim("$10")] = array(
					"chmod" => $this->chmodnum("$1"),
					"owner" => "$3",
					"group" => "$4",
					"filesize" => "$5",
					"time" => strtotime("$6 $7 $8:$9"),
					"file" => trim("$10"),
					"type" => print_r((preg_match("/^d/", "$1") ? "dir" : "file"), true)
					);',

					$var) ;
			}

		}
		$this->dir = $dir;
		$this->rawlist[$dir] = & $items;
		return $items;
	}

	private function chmodnum($permissions = 'rw-r--r--') {
		$mode = 0; 

		if ($permissions[1] == 'r'){
			$mode += 0400;
		}
		if ($permissions[2] == 'w'){
			$mode += 0200;
		}
		if ($permissions[3] == 'x'){
			$mode += 0100;
		}
		else if ($permissions[3] == 's'){
			$mode += 04100;
		}
		else if ($permissions[3] == 'S'){
			$mode += 04000;
		}


		if ($permissions[4] == 'r'){
			$mode += 040;
		}
		if ($permissions[5] == 'w'){
			$mode += 020;
		}
		if ($permissions[6] == 'x'){
			$mode += 010;
		}
		else if ($permissions[6] == 's'){
			$mode += 02010;
		}
		else if ($permissions[6] == 'S'){
			$mode += 02000;
		}


		if ($permissions[7] == 'r'){
			$mode += 04;
		}
		if ($permissions[8] == 'w'){
			$mode += 02;
		}
		if ($permissions[9] == 'x'){
			$mode += 01;
		}
		else if ($permissions[9] == 't'){
			$mode += 01001;
		}
		else if ($permissions[9] == 'T'){
			$mode += 01000;
		}


		return $mode;
	}

}

?>