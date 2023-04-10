<?php
/**
 * This software is distributed under the GNU GPL v3.0 license.
 *
 * @author    Gemorroj
 * @copyright 2008-2018 http://wapinet.ru
 * @license   http://www.gnu.org/licenses/gpl-3.0.txt
 *
 * @see      https://github.com/Gemorroj/gmanager
 */
class Config_Ini implements Config_Interface
{
    /**
     * @var array
     */
    private $_config = [];

    public function __construct(string $configFile)
    {
        $this->_config = \parse_ini_file($configFile, true);
    }

    public function get(string $property, string $section): ?string
    {
        return $this->_config[$section][$property] ?? null;
    }

    public function getSection(string $section): array
    {
        return $this->_config[$section] ?? [];
    }
}
