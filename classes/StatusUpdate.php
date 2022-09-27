<?php 


class StatusUpdate
{
	public function StatusUpdate() {
		
	}
	
	public function WriteJS($listName) {
		$cleanName = str_replace('#','',$listName);
		$cleanName = str_replace('.','',$cleanName);

		echo <<<JS
<script type="text/javascript" >
	
	var _$cleanName = \$('$listName');
	function updateStatus(info) {
		_{$cleanName}.append(\$('<li>'+info+'</li>'));
		
	}
	
</script>
		
		
JS;
	}
	
	public function WriteUpdate($info) {
		#header('Content-Length: '.strlen($info),true);
		echo <<<JS
<script type="text/javascript">
	updateStatus("$info");

</script>
		
JS;
	}
	
}

?>