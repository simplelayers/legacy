<?php

use utils\ParamUtil;
use model\media\FeatureImage;
use formats\WMS;

function _exec()
{
    
    $params = WAPI::GetParams();
    $user = SimpleSession::Get()->GetUser();
    $ini = System::GetIni();
    $layerId = ParamUtil::RequiresOne($params,'layer' );
    
    
    $layer = Layer::GetLayer( $layerId );
    if (!$layer || ($layer->getPermissionById ( $user->id ) < AccessLevels::READ))
        return '';
    
    // should we force generation of a new image, or use the cache?
    $cachefile = "";// "{$ini->thumbdir}/thumbnail-{$ini->name}-{$layer->id}.jpg";
    
  
    
    list($force) = ParamUtil::GetBoolean( $params,'force:false');
    
    
    $layer->GenerateThumbnail($force,true);    



}
?>