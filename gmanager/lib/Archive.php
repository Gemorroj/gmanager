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


class Archive
{
    /**
     * Archive format
     *
     * @const string
     */
    const FORMAT_ZIP = 'ZIP';
    const FORMAT_TAR = 'TAR';
    const FORMAT_BZ2 = 'BZ2';
    const FORMAT_RAR = 'RAR';
    const FORMAT_GZ  = 'GZ';


    /**
     * @var string
     */
    private $_format;
    /**
     * @var string
     */
    private $_file;


    /**
     * setFormat
     *
     * @param string $format
     * @return Archive
     */
    public function setFormat ($format)
    {
        $this->_format = $format;
        return $this;
    }


    /**
     * setFile
     *
     * @param string $file
     * @return Archive
     */
    public function setFile ($file)
    {
        $this->_file = $file;
        return $this;
    }


    /**
     * factory
     *
     * @return Archive_Zip|Archive_Tars|Archive_Rar|null
     */
    public function factory ()
    {
        switch ($this->_format) {
            case self::FORMAT_ZIP:
                return new Archive_Zip($this->_file);
                break;


            case self::FORMAT_TAR:
            case self::FORMAT_BZ2:
                // Archive_Tar exists =(
                return new Archive_Tars($this->_file);
                break;


            case self::FORMAT_RAR:
                if (extension_loaded('rar')) {
                    return new Archive_Rar($this->_file);
                }
                break;
        }

        return null;
    }
}
