<?php
/**
 * PEAR2_Pyrus_Installer_Role_Customtask
 *
 * PHP version 5
 *
 * @category  PEAR2
 * @package   PEAR2_Pyrus
 * @author    Greg Beaver <cellog@php.net>
 * @copyright 2008 The PEAR Group
 * @license   http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @version   SVN: $Id$
 * @link      http://svn.pear.php.net/wsvn/PEARSVN/Pyrus/
 */

/**
 * Custom task xml file role
 *
 * @category  PEAR2
 * @package   PEAR2_Pyrus
 * @author    Greg Beaver <cellog@php.net>
 * @copyright 2008 The PEAR Group
 * @license   http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @link      http://svn.pear.php.net/wsvn/PEARSVN/Pyrus/
 */
class PEAR2_Pyrus_Installer_Role_Customtask extends PEAR2_Pyrus_Installer_Role_Data
{
    function validate(PEAR2_Pyrus_IPackage $package, array $file)
    {
        $parser = new PEAR2_Pyrus_XMLParser;
        $schemapath = PEAR2_Pyrus::getDataPath();
        if (!file_exists(PEAR2_Pyrus::getDataPath() . '/customtask-2.0.xsd')) {
            $schemapath = realpath(__DIR__ . '/../../../../data');
        }
        $taskschema = $schemapath . '/customtask-2.0.xsd';
        try {
            $taskinfo = $parser->parse($package->getFilePath($file['attribs']['name']), $taskschema);
        } catch (\Exception $e) {
            throw new PEAR2_Pyrus_Installer_Role_Exception('Invalid custom task definition file,' .
                                                           ' file does not conform to the schema', $e);
        }
    }
}
?>