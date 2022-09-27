<?php
namespace model;

use model\records\CachedRecord;
use model\records\ControlledVocab;
class ControlledVocabularies
extends CRUD
{
    public function __construct() {
        parent::__construct();
        $this->table = 'controlled_vocabs';
        $this->idField = 'id';
        $this->nameField = null;
        $this->hasModified = true;
        $this->creationFields = array('layer_id','attribute','vocab');
        $this->isReadyOnly = false;
        $this->numericFields=array('id','layer_id');
    }
    
    public function GetVocab($layerId,$attribute) {
        $db = \System::GetDB();
        $record = $db->GetRow("select id,layer_id,decode(vocab,'base64') as vocab,modified from {$this->table} where layer_id  =? and attribute=?",array($layerId,$attribute));
        return $this->GetObject($record);
    }
    
    public function SaveVocab($layerId,$attribute,$vocab) {
        $db = \System::GetDB();
        
        $id = $db->GetOne("select id from {$this->table} where layer_id=? and attribute=?",array($layerId,$attribute));
        if(!$id) {
            
            $db->Execute("insert into {$this->table} (layer_id,attribute,vocab) values(?,?,encode(?,'base64'))",array($layerId,$attribute,$vocab));
            return;
        }
        $db->Execute("update {$this->table} set vocab=encode(?,'base64') where attribute=? and layer_id=?",array($vocab,$attribute,$layerId));
    }
    
    public function DeleteVocab($layerId,$attribute) {
        $db = \System::GetDB();
        $db->Execute("update {$this->table} set vocab=encode(?,'base64') where attribute=? and layer_id=?",array($vocab,$attribute,$layerId));
    }

    public function GetObject($record) {
        $this->IsReady();
        return new ControlledVocab($record,$this->table);        	
    }
    
    public static function Get() {
        return new ControlledVocabularies();
    }
    
}



?>