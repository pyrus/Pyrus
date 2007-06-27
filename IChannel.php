<?php
interface PEAR2_Pyrus_IChannel
{
    public function getName();
    public function getPort($mirror = false);
    public function getSSL($mirror = false);
    public function getSummary();
    public function toChannelObject();
    public function __toString();
}