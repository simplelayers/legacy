<?php

class SimpleIni {

    private $infoFlat = array();
    private $info = array();
    private $iniFile = "";

    public function __construct($parseSections = FALSE, $file = null) {
        if (is_null($file)) {
            $this->iniFile = defined('SIMPLE_INI') ? SIMPLE_INI : '/etc/simplelayers/simple.ini';
        } else {
            $this->iniFile = $file;
        }

        $this->infoFlat = parse_ini_file($this->iniFile);
        if ($parseSections)
            $this->info = $this->AsSections();
    }

    public function GetKeys() {
        return array_keys($this->infoFlat);
    }

    public function AsSections() {
        if ($this->info)
            return $this->info;

        $this->info = parse_ini_file($this->iniFile, TRUE);

        return $this->info;
    }

    public function GetSection($section) {
        $sections = $this->AsSections();
        if (!isset($sections[$section]))
            return null;
        return $sections[$section];
    }

    public function __get($key) {

        $sfix = false;
        if (!isset($this->infoFlat[$key])) {
            if (!isset($this->infoFlat[$key . "_s"]))
                return null;
            $sfix = true;
        }

        if ($sfix) {
            $em = "AES-256-CBC";
            $salt = base64_decode($this->secret);
            $vi = substr(str_pad($key, 16), 0, 16);
            $val = $this->infoFlat[$key . '_s'];
            $retval = openssl_decrypt($val, $em, $salt, 0, $vi);
            return $retval;
        }
        return $this->infoFlat[$key];
    }

    public function __set($key, $val) {
        $this->infoFlat[$key] = $val;
    }

}

?>