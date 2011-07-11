<?php
/**
 * \Pyrus\Installer\Role\Customtask
 *
 * PHP version 5
 *
 * @category  Pyrus
 * @package   Pyrus
 * @author    Greg Beaver <cellog@php.net>
 * @copyright 2010 The PEAR Group
 * @license   http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @version   SVN: $Id$
 * @link      http://svn.php.net/viewvc/pear2/Pyrus/
 */

/**
 * Custom task xml file role
 *
 * @category  Pyrus
 * @package   Pyrus
 * @author    Greg Beaver <cellog@php.net>
 * @copyright 2010 The PEAR Group
 * @license   http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @link      http://svn.php.net/viewvc/pear2/Pyrus/
 */
namespace Pyrus\Installer\Role;
class Customtask extends \Pyrus\Installer\Role\Data
{
    function validate(\Pyrus\PackageInterface $package, array $file)
    {
        $parser = new \Pyrus\XMLParser;
        $schemapath = \Pyrus\Main::getDataPath();
        if (!file_exists(\Pyrus\Main::getDataPath() . '/customtask-2.0.xsd')) {
            $schemapath = realpath(__DIR__ . '/../../../data');
        }
        $taskschema = $schemapath . '/customtask-2.0.xsd';
        try {
            $taskinfo = $parser->parse($package->getFilePath($file['attribs']['name']), $taskschema);
        } catch (\Exception $e) {
            throw new \Pyrus\Installer\Role\Exception('Invalid custom task definition file,' .
                                                           ' file does not conform to the schema', $e);
        }
    }
}
?>