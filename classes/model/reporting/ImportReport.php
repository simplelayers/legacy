<?php
namespace model\reporting;

use model\MongoCRUD;
class ImportReport
extends MongoCRUD {
	
	public $layers = null;
	public $messages = null;
	
	protected $collectionName = 'importReports';
	/* (non-PHPdoc)
	 * @see \model\MongoCRUD::__construct()
	 */

	public function __construct() {
		$this->layers = array();
		$this->messages = array();
		parent::__construct();
	}
	
	public function AddLayerReport($layerid,$stats) {
		
		$record = array('layerid'=>$layerid,'stats'=>$stats);
		$stats['imported'] = strtotime('now');
		$this->layers[] = $record;
		return $record;
	}
	
	public function AddMessage($message,$type='problem') {
		$this->messages = array('type'=>$type,'message'=>$message);
	}
	
	public function CreateImportReport() {
		
		return $this->MakeDocument(array('layers'=>$this->layers,'messages'=>$this->messages));
	}
	
	public function GetReportsByLayer($layerid){ 
		
		$reports =  $this->FindByCriteria( new \Comparisons("data.layers.layerid", \Comparisons::COMPARE_EQUALS,"".layerid));
		return $reports;
	}

	
	public function GetReport($reportId) {
		return $this->FindDocumentById($reportId,false);
	}
	
}

?>