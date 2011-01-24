<?php
/**
 * \PEAR2\Pyrus\Installer\Role\Customcommand
 *
 * PHP version 5
 *
 * @category  PEAR2
 * @package   PEAR2_Pyrus
 * @author    Greg Beaver <cellog@php.net>
 * @copyright 2010 The PEAR Group
 * @license   http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @version   SVN: $Id$
 * @link      http://svn.php.net/viewvc/pear2/Pyrus/
 */

/**
 * Custom task xml file role
 *
 * @category  PEAR2
 * @package   PEAR2_Pyrus
 * @author    Greg Beaver <cellog@php.net>
 * @copyright 2010 The PEAR Group
 * @license   http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @link      http://svn.php.net/viewvc/pear2/Pyrus/
 */
namespace PEAR2\Pyrus\Installer\Role;
class Customcommand extends \PEAR2\Pyrus\Installer\Role\Data
{
    function validate(\PEAR2\Pyrus\PackageInterface $package, array $file)
    {
        $parser = new \PEAR2\Pyrus\XMLParser;
        $schemapath = \PEAR2\Pyrus\Main::getDataPath();
        if (!file_exists(\PEAR2\Pyrus\Main::getDataPath() . '/customcommand-2.0.xsd')) {
            $schemapath = realpath(__DIR__ . '/../../../../data');
        }
        $taskschema = $schemapath . '/customcommand-2.0.xsd';
        try {
            $taskinfo = $parser->parse($package->getFilePath($file['attribs']['name']), $taskschema);
        } catch (\Exception $e) {
            throw new \PEAR2\Pyrus\Installer\Role\Exception('Invalid custom command definition file,' .
                                                           ' file does not conform to the schema', $e);
        }
    }
}
?>