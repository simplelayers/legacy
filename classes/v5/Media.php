<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace v5;

/**
 * Description of Media
 *
 * @author arthu
 */
class Media {

    const MediaBaseURL = 'https://media.simplelayers.com/';

    //put your code here
    public static function ProcessURL($url) {
        if (stripos($url, self::MediaBaseURL) === 0) {
            self::ProcessMediaURL($url);
        }
    }

    public static function ProcessMediaURL($url) {
        $url = substr($url, strlen('https://media.simplelayers.com/'));
        if (substr($url, -1, 1) === '/') {
            array_pop($parts);
        }
        if (substr($url, 0, 1) === '/') {
            $url = substr($url, 1);
        }
        $parts = explode('/', $url);

        $storageFeatureId = array_shift($parts);
        $fileName = array_pop($parts);
        $db = \System::GetDB();


        $query = 'select *,ss.type from storage_feature_media as sfm join storage_services as ss on sfm.storage=ss.id where sfm.id=?';
        $record = $db->GetRow($query, [$storageFeatureId]);

        $storage = StorageTypes::GetStorage($record['type']);
        $tempDir = \utils\ImportUtil::MakeTmpDir(true);

        $storage->ToFile($record, $record['path'], $tempDir);

        if ($record['mime']) {
            if ($record['mime'] !== '') {
                header('Content-Type:' . $record['mime']);
            }
        }

        readfile('./' . $fileName);
        die();
    }

    public static function GetFeatureImgInfo($imgName) {
        $matches = [];
        preg_match('/([0-9]+)-([0-9]+).img/', $imgName, $matches);
        list($full, $layer, $feature) = $matches;
        $fileName = '/mnt/media/features/' . $layer . '/' . $feature . '.json';
        $imgInfo = false;
        if (file_exists($fileName)) {
            $info = file_get_contents($fileName);

            $imgInfo = json_decode($info, true);
        }
        // $imgInfo['fileName'] = '/mnt/media/features/'.$layer.'/'.$feature;
        return $imgInfo;
    }

    public static function ProcessFeatureImg($imgName) {

        $imgInfo = self::GetFeatureImgInfo($imgName);
        if ($imgInfo) {

            $type = $imgInfo['type'];
            $typeInfo = explode('_', $type);

            $mime = implode('/' . $typeInfo);
            if ($mime !== '') {
                header('Content-Type:' . $mime);
                header("Content-disposition: inline; filename=\"{$record['name']}\"");
            }
            $fileName = '/mnt/media/features/' . $imgInfo['layerId'] . '/' . $imgInfo['featureId'];

            echo file_get_contents($fileName);
        }
    }

}
