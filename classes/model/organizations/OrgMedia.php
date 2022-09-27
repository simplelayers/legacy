<?php
namespace model\organizations;

use model\MongoCRUD;
use utils\ParamUtil;

class OrgMedia extends MongoCRUD
{

    const DATATYPE_EMPLOYEE_MEDIA = 'employee';

    const DATATYPE_ORGANIZATION_MEDIA = 'org_media';

    const MEDIATYPE_LINK = 'org_media_link';

    const MEDIATYPE_IMAGE = 'org_media_image';

    const MEDIATYPE_FILE = 'org_media_file';

    public function __construct()
    {
        $this->collectionName = 'org_media';
        parent::__construct();
    }

    public function SetMediaTypes()
    {
        $document = $this->FindOneByCriteria(\Comparisons::ToMongoCriteria('data.type', 'media_types'));
        $data = array(
            'type' => 'media_types',
            'types' => array(
                'org' => array(
                    'help_link' => array(
                        'label' => "Help Link",
                        'requires' => 'text',
                        'formatd' => 'url',
                        'media_type' => self::MEDIATYPE_LINK
                    ),
                    'help_pdf' => array(
                        'label' => "Help PDF",
                        'requires' => 'pdf',
                        'format' => 'pdf',
                        'media_type' => self::MEDIATYPE_FILE
                    ),
                    'logo' => array(
                        'label' => 'Logo',
                        'requires' => 'image',
                        'media_type' => self::MEDIATYPE_FILE
                    )
                ),
                'employee' => array(
                    'avatar' => 'img'
                )
            )
        );
        
        if (! $document) {
            $this->MakeDocument($data);
        } else {
            $document['data'] = $data;
            $document = $this->Update($document);
        }
        return $document;
    }

    public function GetMediaTypes($contextType = 'org')
    {
        $doc = $this->FindOneByCriteria(\Comparisons::ToMongoCriteria('data.type', \Comparisons::COMPARE_EQUALS, 'media_types'));
        return $doc['data']['types'][$contextType];
    }

    public function RemoveMediaContent($params)
    {
        $id = ParamUtil::Get($params, 'id');
        $orgId = ParamUtil::Get($params, 'orgId');
        $org = null;
        if (is_null($orgId)) {
            $user = \SimpleSession::Get()->GetUser();
            $org = \Organization::GetOrgByUserId($user->id, false);
            $orgId = $org->id;
            $params['orgId'] = $orgId;
        }
        
        if (! $id) {
            $orgId = ParamUtil::RequiresOne($params, 'orgId');
            $media = ParamUtil::RequiresOne($params, 'media');
            $criteria = \Comparisons::ToMongoCriteria('data.orgId', \Comparisons::COMPARE_EQUALS, $orgId);
            $document = $this->FindOneByCriteria($criteria);
            
            $id = $document['data']['media'][$media];
        }
        
        $criteria = \Comparisons::ToMongoCriteria('data.media.' . $id . '.id', \Comparisons::COMPARE_EQUALS, $id);
        $mediaId = 'data.media.' . $id;
        $document = $this->FindOneByCriteria($criteria, array(
            $mediaId => 1
        ));
        
        if (count($document['data'])) {
            $item = $document['data']['media'][$id];
            $itemObj = null;
            switch ($item['media_type']) {
                case self::MEDIATYPE_IMAGE:
                    $itemObj = new OrgMediaImage($item);
                    
                    break;
                case self::MEDIATYPE_FILE:
                    $itemObj = new OrgMediaFile($item);
                    break;
                
                case self::MEDIATYPE_LINK:
                    break;
            }
            if ($itemObj)
                $item->Remove();
        } else {
            return null;
        }
    }

    public function GetMediaContent($params, $sendHeader = true)
    {
        $id = ParamUtil::Get($params, 'id');
        $orgId = ParamUtil::Get($params, 'orgId');
        $org = null;
        if (is_null($orgId)) {
            $user = \SimpleSession::Get()->GetUser();
            $org = \Organization::GetOrgByUserId($user->id, false);
            $orgId = $org->id;
            $params['orgId'] = $orgId;
        }
        
        if (! $id) {
            $orgId = ParamUtil::RequiresOne($params, 'orgId');
            $media = ParamUtil::RequiresOne($params, 'media');
            $criteria = \Comparisons::ToMongoCriteria('data.orgId', \Comparisons::COMPARE_EQUALS, $orgId);
	    $document = $this->FindOneByCriteria($criteria);
	    if(!is_null($document)) {
            	$id = $document['data']['media'][$media];
	    } else {
		return null;
	    }
	}
        
        $criteria = \Comparisons::ToMongoCriteria('data.media.' . $id . '.id', \Comparisons::COMPARE_EQUALS, $id);
        $mediaId = 'data.media.' . $id;
        $document = $this->FindOneByCriteria($criteria, array(
            $mediaId => 1
        ));
       
        if (count($document['data'])) {
            $item = $document['data']['media'][$id];
            
            switch ($item['media_type']) {
                
                case self::MEDIATYPE_IMAGE:
                    $img = new OrgMediaImage($item);
                    if ($sendHeader)
                        $img->SendImageHeader();
                    $img->WriteImage($sendHeader);
                    break;
                case self::MEDIATYPE_FILE:
                    $file = new OrgMediaFile($item);
                    if ($sendHeader)
                        $file->SendFileHeader();
                    $file->WriteFile($sendHeader);
                    break;
                
                case self::MEDIATYPE_LINK:
                    $link = new OrgMediaLink($item);
                    $link->GoToink();
                    echo $link;
                    
                    break;
            }
        } else {
            return null;
        }
    }

    public function GetOrgMedia($dataType, $params, $asDocument = false)
    {
        $criteria[] = \Comparisons::ToMongoCriteria('data.type', \Comparisons::COMPARE_EQUALS, $dataType);
        list ($orgId) = ParamUtil::Requires($params, 'orgId');
        
        $criteria[] = \Comparisons::ToMongoCriteria('data.orgId', \Comparisons::COMPARE_EQUALS, $orgId);
        if ($dataType == self::DATATYPE_EMPLOYEE_MEDIA) {
            list ($employeeId) = ParamUtil::Requires($params, 'employeeId');
            $criteria[] = \Comparisons::ToMongoCriteria('data.employeeId', \Comparisons::COMPARE_EQUALS, $employeeId);
        }
        
        $criteria = \Comparisons::GroupMongoCriteria($criteria, \Comparisons::OPERATOR_MONGO_AND);
        
        $document = $this->FindOneByCriteria($criteria);
        if ($asDocument) {
            return $document;
        }
        
        if (! $document) {
            $document = $this->MakeDocument(array(
                'media' => array()
            ));
        }
        $media = $document['data']['media'];
        $orgMedia = array();
        
        $whichMedia = ParamUtil::Get($params, 'media_name');
        foreach ($media as $mediaName => $item) {
            
            if (is_numeric(substr($mediaName, 0, 1))) {
                continue;
            }
            
            $mediaId = $media[$mediaName];
            
            if (! isset($media[$mediaId])) {
                continue;
            }
            $item = $media[$mediaId];
            
            $mediaType = $item['media_type'];
            
            switch ($mediaType) {
                
                case self::MEDIATYPE_FILE:
                    $item = new OrgMediaFile($item, $dataType);
                    $url = $item->MakeURL(BASEURL, $dataType);
                    break;
                case self::MEDIATYPE_IMAGE:
                    $item = new OrgMediaImage($item, $dataType);
                    $url = $item->MakeURL(BASEURL, $dataType);
                    break;
                case self::MEDIATYPE_LINK:
                    $item = new OrgMediaLink($item);
                    $url = $item->MakeURL();
                    break;
            }
            $orgMedia[$mediaName]['url'] = $url;
            $orgMedia[$mediaName]['media_type'] = $mediaType;
            if ($mediaName == $whichMedia)
                return ParamUtil::Get($params, 'as_item') ? $item : $orgMedia[$mediaName];
        }
        
        return $orgMedia;
    }

    public function RemoveOrg($orgId)
    {
        $criteria = \Comparisons::ToMongoCriteria('data.orgId', \Comparisons::COMPARE_EQUALS, $orgId);
        $this->DeleteByCriteria($criteria);
    }

    public function RemoveEmployee($orgId, $employeeId)
    {
        $criteria[] = \Comparisons::ToMongoCriteria('data.type', \Comparisons::COMPARE_EQUALS, self::DATATYPE_EMPLOYEE_MEDIA);
        $criteria[] = \Comparisons::ToMongoCriteria('data.orgId', \Comparisons::COMPARE_EQUALS, $orgId);
        $criteria[] = \Comparisons::ToMongoCriteria('data.employeeId', \Comparisons::COMPARE_EQUALS, $employeeId);
        $criteria = \Comparisons::GroupMongoCriteria($criteria, \Comparisons::OPERATOR_MONGO_AND);
        
        $this->DeleteByCriteria($criteria);
    }

    public function UpsertOrgMedia($orgId, $name, $content)
    {
        self::RemoveOrgMedia($orgId, self::DATATYPE_ORGANIZATION_MEDIA, $name);
        
        $criteria[] = \Comparisons::ToMongoCriteria('data.type', \Comparisons::COMPARE_EQUALS, self::DATATYPE_ORGANIZATION_MEDIA);
        $criteria[] = \Comparisons::ToMongoCriteria('data.orgId', \Comparisons::COMPARE_EQUALS, $orgId);
        $criteria = \Comparisons::GroupMongoCriteria($criteria, \Comparisons::OPERATOR_MONGO_AND);
        
        $document = $this->FindOneByCriteria($criteria);
        
        if (! $document) {
            $data = array(
                'orgId' => $orgId,
                'type' => 'org_media',
                'media' => array()
            );
            $document = $this->MakeDocument($data);
        }
        
        $isContentObj = false;
        if (is_a($content, 'model\organizations\OrgMediaImage')) {
            $isContentObj = true;
        } elseif (is_a($content, 'model\organizations\OrgMediaLink')) {
            $isContentObj = true;
        } elseif (is_a($content, 'model\organizations\OrgMediaFile')) {
            $isContentObj = true;
        }
        if ($isContentObj)
            $content = $content->data;
        $content['media_name'] = $name;
        $document['data']['media'][$name] = $content['id'];
        $document['data']['media'][$content['id']] = $content;
        $this->Update($document);
    }

    public function RemoveOrgMedia($orgId, $type, $name = null)
    {
        $criteria[] = \Comparisons::ToMongoCriteria('data.type', \Comparisons::COMPARE_EQUALS, self::DATATYPE_ORGANIZATION_MEDIA);
        $criteria[] = \Comparisons::ToMongoCriteria('data.orgId', \Comparisons::COMPARE_EQUALS, "" . $orgId);
        $criteria = \Comparisons::GroupMongoCriteria($criteria, \Comparisons::OPERATOR_MONGO_AND);
        
        $document = $this->FindOneByCriteria($criteria);
        
        $item = null;
        if (! is_null($name)) {
            foreach ($document['data']['media'] as $key => $value) {
                
                if ($value['media_name'] == $name) {
                    $item = $document['data']['media'][$key];
                    unset($document['data']['media'][$key]);
                }
            }
        } else {
            unset($document['data']['media'][$type]);
        }
        
        $this->Update($document);
    }

    public function RemoveOrgMediaById($orgId, $type, $mediaId)
    {
        $criteria[] = \Comparisons::ToMongoCriteria('data.type', \Comparisons::COMPARE_EQUALS, self::DATATYPE_ORGANIZATION_MEDIA);
        $criteria[] = \Comparisons::ToMongoCriteria('data.orgId', \Comparisons::COMPARE_EQUALS, "" . $orgId);
        $criteria = \Comparisons::GroupMongoCriteria($criteria, \Comparisons::OPERATOR_MONGO_AND);
        $document = $this->FindOneByCriteria($criteria);
        
        foreach ($document['data']['media'] as $key => $value) {
            
            if ($value['id'] == $mediaId) {
                unset($document['data']['media'][$key]);
            }
        }
        
        $this->Update($document);
    }

    public function UpsertEmployeeMedia($orgId, $employeeId, $type, $content)
    {
        $criteria[] = \Comparisons::ToMongoCriteria('data.type', \Comparisons::COMPARE_EQUALS, self::DATATYPE_EMPLOYEE_MEDIA);
        $criteria[] = \Comparisons::ToMongoCriteria('data.orgId', \Comparisons::COMPARE_EQUALS, $orgId);
        $criteria[] = \Comparisons::ToMongoCriteria('data.employeeId', \Comparisons::COMPARE_EQUALS, $employeeId);
        $criteria = \Comparisons::GroupMongoCriteria($criteria, \Comparisons::OPERATOR_MONGO_AND);
        
        $document = $this->FindOneByCriteria($criteria);
        if (! $document) {
            $data = array(
                'orgId' => $orgId,
                'employeeId' => $employeeId,
                'type' => 'employee_media',
                'media' => array()
            );
            $document = $this->MakeDocument($data);
        }
        if (is_a($content, 'OrgMediaImage'))
            $content = $content->data;
        if (is_a($content, 'OrgMediaLink'))
            $content = $content->data;
        $document['data']['media'][$type] = $content;
        $this->Update($document);
    }

    public function RemoveEmployeeMedia($orgId, $employeeId, $type)
    {
        $criteria[] = \Comparisons::ToMongoCriteria('data.type', \Comparisons::COMPARE_EQUALS, self::DATATYPE_EMPLOYEE_MEDIA);
        $criteria[] = \Comparisons::ToMongoCriteria('data.orgId', \Comparisons::COMPARE_EQUALS, $orgId);
        $criteria[] = \Comparisons::ToMongoCriteria('data.employeeId', \Comparisons::COMPARE_EQUALS, $employeeId);
        $criteria = \Comparisons::GroupMongoCriteria($criteria, \Comparisons::OPERATOR_MONGO_AND);
        $document = $this->FindOneByCriteria($criteria);
        unset($document['data']['media']['type']);
        $this->Update($document);
    }

    public function HandleChanges($params, $changes)
    {
        list ($orgId, $dataType) = ParamUtil::Requires($params, 'orgId', 'dataType');
        
        switch ($dataType) {
            case self::DATATYPE_ORGANIZATION_MEDIA:
                foreach ($changes as $type => $change) {
                    if ($change['isDeleted']) {
                        $this->RemoveOrgMedia($orgId, $type);
                        continue;
                    }
                    if ($change['isChanged']) {
                        unset($change['isChanged']);
                        $this->UpsertOrgMedia($orgId, $type, $change);
                        continue;
                    }
                }
                break;
            case self::DATATYPE_EMPLOYEE_MEDIA:
                list ($employeeId) = ParamUtil::Requires($params, $employeeId);
                foreach ($changes as $type => $change) {
                    if ($change['isDeleted']) {
                        $this->RemoveEmployeeMedia($orgId, $employeeId, $type);
                        continue;
                    }
                    if ($change['isChanged']) {
                        unset($change['isChanged']);
                        $this->UpsertEmployeeMedia($orgId, $employeeId, $type, $change);
                        continue;
                    }
                }
                break;
        }
    }

    function Go($params)
    {
        list ($orgId, $mediaName) = ParamUtil::Requires($params, 'orgId', 'go');
        $media = $this->GetOrgMedia(OrgMedia::DATATYPE_ORGANIZATION_MEDIA, array(
            'orgId' => $orgId
        ));
        $options = array_keys($media);
        $url = null;
        if (isset($media[$mediaName])) {
            $url = $media[$mediaName]["url"];
        }
        switch ($mediaName) {
            case 'help_link':
                if (isset($media['help_pdf'])) {
                    $url = $media['help_pdf']['url'];
                }
                if(is_null($url)) {
                    $media = $this->GetOrgMedia(OrgMedia::DATATYPE_ORGANIZATION_MEDIA, array(
                        'orgId' => 1
                    ));
                    $url = $media['help_pdf']['url'];
                }
                break;
                
        }
        if (! is_null($url)) {
            if ($url) {
                if (\SimpleSession::Get()->isEmbedded) {
                    $token = ParamUtil::Get($params, 'token');
                    if ($token)
                        $url .= '/token:' . $token;
                }
            }
            header("Location: $url");
            return true;
        }
        return false;
    }
}

?>
