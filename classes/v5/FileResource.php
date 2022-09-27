<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace v5;

/**
 * Description of FileResource
 *
 * @author arthu
 */
class FileResource {

    //put your code here
    protected $orgId;
    protected $storageId;
    protected $layerId;
    protected $fieldName = '';
    protected $featureId = null;
    protected $mediaValue;
    protected $fileInfo;
    protected $feature;
    protected $serviceRecord;
    protected $targetPath;
    protected $storageFeatureId;
    protected $url;
    protected $isFile = false;

    public function __construct($orgId, $fieldName, $featureInfo, $layerId, $mediaValue, $fileInfo) {
        $this->orgId = $orgId;
        $this->fieldName = $fieldName;
        $this->feature = $featureInfo;
        $this->layerId = $layerId;
        $this->mediaValue = $mediaValue;
        $this->fileInfo = $fileInfo;
        $this->isFile = !is_string($fileInfo);
    }

    public function IsFile() {
        return $this->isFile === true;
    }

    public function ProcessFile($featureId) {
        $this->featureId = $featureId;
        $this->SetTargetFilePath();
        $storage = StorageTypes::GetStorage($this->serviceRecord['type']);
        $storage->FromFileInfo($this->serviceRecord, $this->fileInfo, $this->targetPath);
        $this->AddStorageRecord();
        return $this->GetURL();
    }

    public function GetURL() {
        $this->url = 'https://media.simplelayers.com/' . $this->storageFeatureId . '/';
        
        if ($this->mediaValue['displayPath']) {
            $okToGo = false;
            switch ($this->mediaValue['displayPath']) {
                case '':
                    $okToGo = true;
                    break;
                case 'relPath':
                    $this->url .= $this->ProcessRelPath($this->mediaValue['relPath']);
                    break;
                case 'file-name':
                    $this->url .= $this->fileInfo['fileName'] . '/';
                    break;
                case 'id-file':
                    $this->url .= $this->layerId . '/' . $this->fileInfo['fileName'] . '/';
                    break;
                case 'id-field-name':
                    $altId = \utils\ParamUtil::Get($this->mediaValue, 'altId');
                    $this->url .= $this->layerId . '/' . $altId . '/' . $this->fileInfo['fileName'] . '/';
            }
            if(substr($this->url,-1,1) === '/') {
                $this->url = substr($this->url,0,strlen($this->url)-1);
            }
            if ($okToGo === false) {
                return $this->url;
            }
        }

        $this->url = $this->url . $this->layerId . '/';


        $altId = \utils\ParamUtil::Get($this->mediaValue, 'altId');
        if (!is_null($altId)) {
            $this->url = $this->url . $altId . '/' . $this->feature[$altId] . '/';
        } else {
            $this->url = $this->url . $this->featureId;
        }

        $this->url = $this->url . $this->fileInfo['fileName'] . '/';

        return $this->url;
    }

    public function AddStorageRecord() {
        $db = \System::GetDB();
        $metadata = $this->mediaValue;
        $remoteInfo = $this->fileInfo;
        unset($remoteInfo['data']);
        unset($metadata['__id__']);

        $remoteInfo = json_encode($remoteInfo);
        $metadata = json_encode($metadata);

        $record = [
            'storage' => $this->serviceRecord['id'],
            'layer' => $this->layerId,
            'feature' => $this->featureId,
            'path' => $this->targetPath,
            'mime' => $this->fileInfo['mimeType']
        ];

        if (isset($this->mediaValue['altId'])) {
            $record['alt_id_field'] = $this->mediaValue['altId'];
        }
        $emptyRecord = $db->Execute("select * from storage_feature_media where id = -1");
        $sql = $db->getInsertSql($emptyRecord, $record);

        $this->storageFeatureId = $db->GetOne($sql . 'Returning Id');
        $sql2 = <<<SQL
update storage_feature_media set remote_info='$remoteInfo'::json , metadata='$metadata'::json
SQL;

        $db->Execute($sql2);
    }

    public function SetTargetFilePath() {
        $service = $this->GetFeatureService();
        $pathRecord = $this->GetPathRecord();
        $this->serviceId = $service['id'];
        $this->serviceRecord = $service;
        $path = $this->GetPath($pathRecord, $this->mediaValue['pathName']);

        $relPath = $this->ProcessRelPath($this->mediaValue['relPath']);
        if (substr($path, -1, 1) !== '/') {
            $path = $path . '/';
        }

        $this->targetPath = $path . $relPath;
    }

    public function ProcessRelPath($relPath) {
        $matches = [];
        $regEx = <<<REGEX
/\[([^\]]+)/
REGEX;
        $result = preg_match_all($regEx, $relPath, $matches);

        if (count($matches) > 1) {
            $pathVars = $matches[1];

            foreach ($pathVars as $pathVar) {
                switch ($pathVar) {
                    case '_attribute_':
                        $relPath = str_ireplace('[_attribute_]', $this->fieldName, $relPath);
                        break;
                    case '_featureId_':
                        $relPath = str_ireplace('[_featureId_]', $this->featureId, $relPath);
                        break;
                    case '_layerId_':
                        $relPath = str_ireplace('[_layerId_]', $this->layerId, $relPath);

                        break;
                    case '_fileName_':
                        $relPath = str_ireplace('[_fileName_]', $this->fileInfo['fileName'], $relPath);
                        break;
                    case 'gid':
                        $relPath = str_ireplace('[gid]', $this->featureId, $relPath);
                        break;
                    case $this->fieldName:
                        $relPath = str_ireplace('[' . $this->fieldName . ']', $this->fieldName, $relPath);
                        break;
                    default:
                        if (isset($this->feature[$pathVar])) {
                            $relPath = str_ireplace("[${pathVar}]", $this->fileInfo[$pathVar], $relPath);
                        } else {
                            $relPath = str_ireplace("[${pathVar}]", '', $relPath);
                        }
                        break;
                }
            }
        }


        if (substr($relPath, 0, 1) === '/') {
            $relPath = substr($relPath, 1);
        }
        return $relPath;
    }

    public function GetFeatureService() {
        $db = \System::GetDB();


        $this->serviceRecord = $db->GetRow('select * from storage_services where id=?', [$this->mediaValue['serviceId']]);
        return $this->serviceRecord;
    }

    public function GetPath($pathRecord, $pathName) {
        $paths = \utils\ParamUtil::GetJSON($pathRecord, 'paths');

        if (isset($paths[$pathName])) {
            return $paths[$pathName];
        }
        return null;
    }

    public function GetPathRecord() {
        $db = \System::GetDB();
        return $db->GetRow('select * from storage_organization where org=? and storage=?', [$this->orgId, $this->serviceRecord['id']]);
    }

}
