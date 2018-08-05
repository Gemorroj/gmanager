<?php
/**
 *
 * This software is distributed under the GNU GPL v3.0 license.
 *
 * @author    Gemorroj
 * @copyright 2008-2018 http://wapinet.ru
 * @license   http://www.gnu.org/licenses/gpl-3.0.txt
 * @link      https://github.com/Gemorroj/gmanager
 *
 */

class Config_Ini implements Config_Interface
{
    /**
     * @var array
     */
    private $_config = array();


    /**
     * Constructor
     * 
     * @param string $config
     */
    public function __construct ($config)
    {
        $this->_config = parse_ini_file($config, true);
    }


    /**
     * get
     * 
     * @param string $section
     * @param string $property
     * @return string
     */
    public function get ($section, $property)
    {
        return $this->_config[$section][$property];
    }


    /**
     * getSection
     * 
     * @param string $section
     * @return array
     */
    public function getSection ($section)
    {
        return $this->_config[$section];
    }
}
