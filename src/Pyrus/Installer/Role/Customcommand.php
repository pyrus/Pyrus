<?php
/**
 * \Pyrus\Installer\Role\Customcommand
 *
 * PHP version 5
 *
 * @category  Pyrus
 * @package   Pyrus
 * @author    Greg Beaver <cellog@php.net>
 * @copyright 2010 The PEAR Group
 * @license   http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @link      https://github.com/pyrus/Pyrus
 */

/**
 * Custom task xml file role
 *
 * @category  Pyrus
 * @package   Pyrus
 * @author    Greg Beaver <cellog@php.net>
 * @copyright 2010 The PEAR Group
 * @license   http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @link      https://github.com/pyrus/Pyrus
 */
namespace Pyrus\Installer\Role;
class Customcommand extends \Pyrus\Installer\Role\Data
{
    function validate(\Pyrus\PackageInterface $package, array $file)
    {
        $parser = new \Pyrus\XMLParser;
        $schemapath = \Pyrus\Main::getDataPath();
        $taskschema = $schemapath . '/customcommand-2.0.xsd';
        try {
            $taskinfo = $parser->parse($package->getFilePath($file['attribs']['name']), $taskschema);
        } catch (\Exception $e) {
            throw new \Pyrus\Installer\Role\Exception('Invalid custom command definition file,' .
                                                           ' file does not conform to the schema', $e);
        }
    }
}
?>