<?php
interface PEAR2_Pyrus_IChannel
{
    public function getAlias();
    public function getName();
    public function getPort();
    public function getSSL();
    public function getSummary();
    public function getPath($protocol);
    public function getREST();
    public function getFunctions($protocol);
    public function getBaseURL($resourceType);
    public function toChannelObject();
    public function __toString();
    public function __get($var);
    public function supportsREST();
    public function supports($type, $name = null, $version = '1.0');
    public function resetXmlrpc();
    public function resetSOAP();
    public function resetREST();
    public function setName($name);
    public function setPort($port);
    public function setSSL($ssl = true);
    public function setPath($protocol, $path);
    public function addFunction($type, $version, $name);
    public function setBaseUrl($resourceType, $url);
    public function getValidationObject($package = false);
    public function getValidationPackage();
}