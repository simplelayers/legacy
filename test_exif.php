<?php 


$exif = exif_read_data($_FILES['mobile_photo']['tmp_name'],'FILE,COMPUTED,ANY_TAG,INFO,THUMBNAIL,COMMENT,EXIF,TIFF',true,true);


?>
<html>
<body>
<pre>
========
Headers:
========
<?php

echo $_SERVER['HTTP_USER_AGENT'];
$browser = get_browser();
print_r($browser);
//$browser = get_browser($_SERVER['HTTP_USER_AGENT']);
//$browser = get_browser(null, true);
//print_r($browser);

?>
</pre>
<script>

</script>
</body>
</html>