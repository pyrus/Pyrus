<?php
/**
 * PEAR2_Pyrus_IChannel
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
 * Interface for channels
 *
 * @category  PEAR2
 * @package   PEAR2_Pyrus
 * @author    Greg Beaver <cellog@php.net>
 * @copyright 2008 The PEAR Group
 * @license   http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @link      http://svn.pear.php.net/wsvn/PEARSVN/Pyrus/
 */
interface PEAR2_Pyrus_IChannel
{
    public function getAlias();
    public function getName();
    public function getPort();
    public function getSSL();
    public function getSummary();
    public function getREST();
    public function getFunctions($protocol);
    public function getBaseURL($resourceType);
    public function toChannelObject();
    public function __toString();
    public function __get($var);
    public function __set($var, $value);
    public function supportsREST();
    public function resetREST();
    public function setName($name);
    public function setPort($port);
    public function setSSL($ssl = true);
    public function setBaseUrl($resourceType, $url);
    public function getValidationObject($package = false);
    public function getValidationPackage();
}
