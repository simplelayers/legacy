<?php
namespace model\reporting;

class ReportError extends \Exception
{
    protected $report;
    protected $layer; 
    public function __construct($message=null,$report, $layer=null, $code=null,$previous=null) {
        $this->report = $report;
        $this->layer = $layer;
        parent::__construct($message,$code,$previous);
    }
    public function getReport() {
        return $this->report;
    }
    public function getLayer() {
        return $this->layer;
    }
}



?>