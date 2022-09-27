<?php
/**
 * Paging refers to paged db results.
 *
 * This class is meant as a way to pull paging related info out of the $_REQUEST
 * super global variable such that this paging object may be passed to functions
 * performing db operations. It is ment to be an object passed to and modified by
 * a funciton such that the start (offset) and limit are provided, and the target
 * function passes the records list to this object to calculate the last property.
 *
 * Helper functions are provided for getting the paging content as strings for
 * a variety of common SimpleLayers contexts to simplify outputing to templates and
 * even for building SQL queries with LIMIT and OFFSET properties.
 *
 * The helper functions all return "" if the $_REQUEST superglobal doesn't have paging params.
 * What this means is that you can reliably tag on the output from a helper
 * function and if no paging parameters are present it will have no negative impact
 * on the resulting string. So a function can make paging optional and always append
 * a queryString with $pagingObj->toQueryString() and the query string will either
 * have the limit and offset content or it will be appended with "". 
 *
 * The count property if null should be set by a function doing db work. The count
 * property should be the total count of records (as if paging were not in effect).
 * The idea is that if count is null then the function should get the count.
 * If count is not null then it means paging is in progress and the step of
 * getting the count need not be redone thus saving a little processing. 
 *
 * Public attributes:
 * - first -- null or index of the first result (offest in query).
 * - last -- null or index of the last record in the query result.
 * - limit -- null or number of records per page (LIMIT specified in query).
 * - count -- null or total number of records (see note above)
 *
 * @package ClassHierarchy
 */
class Paging
{
	var $first;
	var $last;
	var $limit;
	var $count;
	
	public function __construct($firstParam="first", $limitParam="limit",$countParam="count",$src=null)
	{
	    
		if(is_null($src)) $src = $_REQUEST;
		$this->first = isset($src[$firstParam]) ? $src[$firstParam] : 0;
		$this->limit = isset( $src[$limitParam] ) ? $src[$limitParam] : -1;
		$this->count = isset($src[$countParam] ) ? $src[$countParam] : -1;
	}
	
	
	public function toAttString()
	{	
		if( $this->isNull() ) return "";
		return "first=\"{$this->first}\" last=\"{$this->last}\" count=\"{$this->count}\"";
	}
	
	public function toURLString()
	{

		return "&first=".$this->first."&last=".$this->last."&count=".$this->count;
	}
	public function mergeData(array &$data) {
	    $data['first'] = $this->first;
	    $data['last'] = $this->last;
	    $data['limit'] = $this->limit;
	    $data['count'] = $this->count;
	    $data['next'] = $this->last + 1;
	    if($data['next'] >= $this->count) $data['next'] = '';
	    $data['prev'] = $this->first - $this->limit;
	    if($data['prev'] <=0) $data['prev'] = '0';
	    

	}
	public function toQueryString()
	{
		if( $this->isNull() ) return "";
		if( $this->limit < 0 ) return "";
		return " LIMIT {$this->limit} OFFSET {$this->first}";
	}
	
	public function toFromToOfString()
	{

		if( $this->isNull() && $this->count==-1 ) return "";
		return $this->first." to ".$this->last." of ".$this->count;
	}
	
	public function setResults(  $resultArray, $count="" )
	{
	    if($resultArray === false) {
	        $this->count = 0;
	        $this->last = 0;
	        return;
	    }
	    if(is_a($resultArray,'ADORecordSet')) {
	       $this->last = ((int)$this->first + $resultArray->NumRows())-1;
	    } else {
	       $this->last =(int)$this->first + sizeof( $resultArray );
	    }
		$this->count = $count;

	}
	
	public function isNull()
	{

		return ($this->first<=0) && ($this->count<0);
	}
	
}


?>
