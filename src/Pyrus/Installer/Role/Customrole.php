<?php
/**
 * \pear2\Pyrus\Installer\Role\Customrole
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
 * Custom role xml file role
 *
 * @category  PEAR2
 * @package   PEAR2_Pyrus
 * @author    Greg Beaver <cellog@php.net>
 * @copyright 2010 The PEAR Group
 * @license   http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @link      http://svn.php.net/viewvc/pear2/Pyrus/
 */
namespace pear2\Pyrus\Installer\Role;
class Customrole extends \pear2\Pyrus\Installer\Role\Data
{
    function validate(\pear2\Pyrus\PackageInterface $package, array $file)
    {
        $parser = new \pear2\Pyrus\XMLParser;
        $schemapath = \pear2\Pyrus\Main::getDataPath();
        if (!file_exists(\pear2\Pyrus\Main::getDataPath() . '/customrole-2.0.xsd')) {
            $schemapath = realpath(__DIR__ . '/../../../../data');
        }
        $taskschema = $schemapath . '/customrole-2.0.xsd';
        try {
            $taskinfo = $parser->parse($package->getFilePath($file['attribs']['name']), $taskschema);
        } catch (\Exception $e) {
            throw new \pear2\Pyrus\Installer\Role\Exception('Invalid custom role definition file,' .
                                                           ' file does not conform to the schema', $e);
        }
    }
}
?>