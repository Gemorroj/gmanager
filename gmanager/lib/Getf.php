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


class Getf
{
    /**
     * Downloader
     * 
     * @param string $f         Content
     * @param string $name      Output filename
     * @param bool   $attach
     * @param string $mime      Mime type
     */
    public static function download ($f, $name, $attach = false, $mime = '')
    {
        ob_implicit_flush(1);
        @set_time_limit(9999);

        ini_set('zlib.output_compression', 'Off');
        ini_set('output_handler', '');

        $sz = $len = strlen($f);

        // "От" и  "До" по умолчанию
        $file_range = array(
            'from' => 0,
            'to'   => $len
        );

        // Если докачка
        $range = isset($_SERVER['HTTP_RANGE']);
        if ($range) {
            if (preg_match('/bytes=(\d+)\-(\d*)/i', $_SERVER['HTTP_RANGE'], $matches)) {
                // "От", "До" если "До" нету, "До" равняется размеру файла
                $file_range = array('from' => $matches[1], 'to' => (!$matches[2]) ? $len : $matches[2]);
                // Режем переменную в соответствии с данными
                if ($file_range) {
                    $f = substr($f, $file_range['from'], $file_range['to']);
                    $sz = $file_range['to'] - $file_range['from'];
                }
            }
        }

        // Хэш
        $etag = md5($f);
        $etag = substr($etag, 0, 4) . '-' . substr($etag, 5, 5) . '-' . substr($etag, 10, 8);

        if (isset($_SERVER['HTTP_IF_NONE_MATCH'])) {
            if ($_SERVER['HTTP_IF_NONE_MATCH'] == '"' . $etag . '"') {
                header($_SERVER['SERVER_PROTOCOL'] . ' 304 Not Modified');
                //header('Date: ' . gmdate('r'));
                exit;
            }
        }


        // Ставим MIME в зависимости от расширения
        if (!$mime) {
            switch (strtolower(pathinfo($name, PATHINFO_EXTENSION))) {
                case 'jar':
                    $mime = 'application/java-archive';
                    break;


                case 'jad':
                    $mime = 'text/vnd.sun.j2me.app-descriptor';
                    break;


                case 'cab':
                    $mime = 'application/vnd.ms-cab-compressed';
                    break;


                case 'sis':
                    $mime = 'application/vnd.symbian.install';
                    break;


                case 'zip':
                    $mime = 'application/x-zip';
                    break;


                case 'rar':
                    $mime = 'application/x-rar-compressed';
                    break;


                case '7z':
                    $mime = 'application/x-7z-compressed';
                    break;


                case 'gz':
                case 'tgz':
                    $mime = 'application/x-gzip';
                    break;


                case 'bz':
                case 'bz2':
                    $mime = 'application/x-bzip';
                    break;


                case 'jpg':
                case 'jpe':
                case 'jpeg':
                    $mime = 'image/jpeg';
                    break;


                case 'gif':
                    $mime = 'image/gif';
                    break;


                case 'png':
                    $mime = 'image/png';
                    break;


                case 'bmp':
                    $mime = 'image/bmp';
                    break;


                case 'txt':
                case 'dat':
                case 'php':
                case 'php4':
                case 'php5':
                case 'phtml':
                case 'htm':
                case 'html':
                case 'shtm':
                case 'shtml':
                case 'wml':
                case 'css':
                case 'js':
                case 'xml':
                case 'sql':
                case 'tpl':
                case 'tmp':
                case 'cgi':
                case 'py':
                case 'pl':
                case 'rb':
                    $mime = 'text/plain';
                    break;


                case 'mmf':
                    $mime = 'application/x-smaf';
                    break;


                case 'mid':
                    $mime = 'audio/mid';
                    break;


                case 'mp3':
                    $mime = 'audio/mpeg';
                    break;


                case 'amr':
                    $mime = 'audio/amr';
                    break;


                case 'wav':
                    $mime = 'audio/x-wav';
                    break;


                case 'mp4':
                    $mime = 'video/mp4';
                    break;


                case 'wmv':
                    $mime = 'video/x-ms-wmv';
                    break;


                case '3gp':
                    $mime = 'video/3gpp';
                    break;


                case 'avi':
                    $mime = 'video/x-msvideo';
                    break;


                case 'mpg':
                case 'mpe':
                case 'mpeg':
                    $mime = 'video/mpeg';
                    break;


                case 'pdf':
                    $mime = 'application/pdf';
                    break;


                case 'doc':
                case 'docx':
                case 'dot':
                    $mime = 'application/msword';
                    break;


                case 'swf':
                    $mime = 'application/x-shockwave-flash';
                    break;


                case 'xls':
                    $mime = 'application/vnd.ms-excel';
                    break;


                case 'xlsx':
                    $mime = 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet';
                    break;


                case 'svg':
                    $mime = 'image/svg+xml';
                    break;


                case 'ico':
                    $mime = 'image/x-icon';
                    break;


                default:
                    $mime = 'application/octet-stream';
                    break;
            }
        }


        // Заголовки...
        if ($file_range['from']) {
            header($_SERVER['SERVER_PROTOCOL'] . ' 206 Partial Content');
        } else {
            header($_SERVER['SERVER_PROTOCOL'] . ' 200 OK');
        }

        header('ETag: "' . $etag . '"');


        //header('Date: ' . gmdate('r'));
        //header('Content-Transfer-Encoding: binary');
        //header('Last-Modified: ' . gmdate('r'));

        // Кэш
        header('Cache-Control: public, must-revalidate, max-age=60');
        header('Pragma: public');
        //header('Expires: Tue, 10 Apr 2038 01:00:00 GMT');


        header('Connection: close');
        //header('Keep-Alive: timeout=10, max=60');
        //header('Connection: Keep-Alive');

        header('Accept-Ranges: bytes');
        header('Content-Length: ' . $sz);


        // Если докачка
        if ($range) {
            header('Content-Range: bytes ' . $file_range['from'] . '-' . $file_range['to'] . '/' . $len);
        }


        // Если отдаем как аттач
        if ($attach) {
            header('Content-Type: ' . $mime);
            header('Content-Disposition: attachment; filename="' . $name . '"');
        } elseif ($mime == 'text/plain') {
            // header('Content-Type: text/plain; charset=' . $charset);
            header('Content-Type: text/plain;');
        } else {
            header('Content-Type: ' . $mime);
        }
        //ob_end_flush();

        exit($f);
    }
}
