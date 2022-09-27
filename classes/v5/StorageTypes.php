<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace v5;

/**
 * Description of StorageTypes
 *
 * @author arthu
 */
class StorageTypes {
    //put your code here
    const INTERNAL = 'sl_internal';
    const RCLONE = 'rclone';
    public static function GetStorage($type) {
        switch($type) {
            case self::INTERNAL:
                return new stores\InternalStorage();
            case self::RCLONE:
                return new stores\RCloneStorage();
        }
    }
}
