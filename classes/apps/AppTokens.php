<?php
namespace apps;

use model\MongoCRUD;
use utils\ParamUtil;

class AppTokens extends MongoCRUD
{
    public function __construct() {
        $this->collection = 'app_tokens';
    }
    
    public function SetToken($data) {
        $app = ParamUtil::GetOne($data, 'app','application');
        if(!$app) throw new \Exception('Could not create token for unspecified application');
        return $this->MakeDocument($data);
        
    }
    
    public function VerifyToken($token) {
       $tokens = $this->FindDocumentById($token,MongoCRUD::NOT_MONGO_ID);
       
    }
    
    public function Cleanup() {
        $tokens = $this->collection->find ( array (
            'created' => 'Date('.(strtotime('-8 hr day')*1000).')'
        ) )->sort ( array (
            'created' => - 1
        ) );
        
        $numTokens = $tokens->count ();
        #var_dump($numSessions);
        
        if ($numTokens > 1) {
            $ctr = 0;
            	
            foreach ( $tokens as $token ) {
                // ar_dump($sess);
                $this->collection->save($token);
                $this->DeleteItem($token['id']);
                $ctr ++;
                if ($ctr == $numTokens)
                    break;
            }            
        }
    }
}

?>