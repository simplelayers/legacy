<?php

use utils\ParamUtil;
use model\ControlledVocabularies;

function _exec() {
    $args = WAPI::GetParams();

    $layer = ParamUtil::RequiresOne($args, 'layerId');
    $features = array(
        'searchable' => false,
        'meta' => false,
        'vocab' => false
    );

    switch (ParamUtil::Get($args, 'action')) {
        case WAPI::ACTION_GET:
            $layer = Layer::GetLayer($layer, true);
            $reqFeatures = explode(',', ParamUtil::GetOne($args, 'features', ''));
            $searchable = false;
            $meta = false;
            foreach ($reqFeatures as $feature) {
                if (isset($features[$feature]))
                    $features[$feature] = true;
            }

            $attributes =  $layer->getAttributesVerbose(false, false, $features['meta']);
            $results = array();

            foreach ($attributes as $attribute => $data) {

                $data['name'] = $attribute;
                $nameAsAtt = true;
                if ($features['searchable']) {
                    if (isset($data['searchable'])) {
                        if ($data['searchable'] == true) {
                            $results[$attribute] = $data;
                            $nameAsAtt = false;
                        }
                    }
                }
                if (isset($features['vocab'])) {
                    $vocab = ControlledVocabularies::Get();

                    $record = $vocab->GetVocab($layer->id, $attribute);
                    $data['vocab'] = $record['vocab'];
                }
                if ($nameAsAtt) {
                    $results[$attribute] = $data;
                } else {
                    $results[] = $data;
                }
            }

            WAPI::SendSimpleResponse(array(
                'attributes' => $results
            ));
            break;
        case "set_vocab":
            list($attribute, $vocabulary) = ParamUtil::Requires($args, 'attribute', 'vocab');

            $vocab = ControlledVocabularies::Get();

            $vocab->SaveVocab($layer, $attribute, $vocabulary);

            WAPI::SendSimpleResponse(array('message' => 'vocab received'));
            break;
        case "get_vocab":
            list($attribute) = ParamUtil::Requires($args, 'attribute');
            $vocab = ControlledVocabularies::Get();

            WAPI::SendSimpleResults($vocab->GetVocab($layer, $attribute)->GetRecord());
            break;
        case "reset_attributes":
            $wapi = System::GetWapi();
            $l = $wapi->RequireALayer();
            $layer;
            $player;
            if (is_a($l, ProjectLayer::class)) {
                $layer = $l->layer;
                $player = $l;
            } else {
                $layer = $l;
            }
            if ($layer->getPermissionById(SimpleSession::Get()->GetUser()->id) < AccessLevels::EDIT) {
                throw new Exception('You do not have permission to change this layer\'s attribute information');
            }
            $layer->field_info = '';
            $layer->field_info = $layer->getAttributesVerbose(false);
            WAPI::SendSimpleResponse($layer->field_info);
            
    }
}
