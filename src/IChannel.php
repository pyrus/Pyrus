<?php
interface PEAR2_Pyrus_IChannel
{
    public function getName();
    public function getPort($mirror = false);
    public function getSSL($mirror = false);
    public function getSummary();
    public function getPath($protocol);
    public function getREST();
    public function getFunction($type, $name = null);
    public function getFunctions($protocol);
    public function getBaseURL($resourceType);
    public function toChannelObject();
    public function __toString();
    public function supportsREST();
    public function supports($type, $name = null, $version = '1.0');
}