<?php

use utils\PageUtil;
use utils\ParamUtil;
use utils\ResponseUtil;

function _exec() {
    $args = WAPI::GetParams();
    $user = SimpleSession::Get()->GetUser();
    $format = WAPI::GetFormat();
    $wapi = System::GetWAPI();
    $layer = $wapi->RequireLayer();
    $metadata = $layer->metadata;
    if (!$metadata) {
        $metadata = '';
    }
    $action = ParamUtil::Get($args, 'action');
    $response = new ResponseUtil(WAPI::GetParam('format', 'json'), 'layers/metadata');

    switch ($action) {
        case WAPI::ACTION_SAVE:
            $metadata = $layer->metadata;
             $converter = new Convert();
            WAPI::WriteXMLHeader();
            header('Content-Disposition: attachment; filename="'.$layer->name.'.xml"');
            ob_start();
            $converter->WritePhpToXml($metadata);
            $xml = ob_get_clean();
            header('Content-Length: '.strlen($xml));
            echo $xml;
            return;
            break;
        
        case WAPI::ACTION_GET:
        default:
            $response->StartResponse(true);
            $response->WriteKeyVal('layerName',$layer->name);
            $response->WriteKeyVal('layerId',$layer->id);
            $response->WriteKeyVal('SourceMetadata', $layer->metadata);
            $response->WriteKeyVal('ImportInfo',$layer->import_info);
            $response->EndBody('SimpleMetadata');
            $response->EndResponse(true);
            // WAPI::SendSimpleResponse(['src_metadata' => $metadata], $format);
            return;
       
    }
    WAPI::SendSimpleResponse('nothing to do');
}
