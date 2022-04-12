<?php
/**
 * The form for importing CSV data.
 * Actually, it's not CSV at all, it's tab-delimited; but the acrnym makes a convenient label.
 * @package Dispatchers
 */
/**
 */
function _config_getmedia() {
	$config = Array();
	// Start config
	$config["sendUser"] = false;
	$config["authUser"] = 0;
	$config["header"] = false;
	$config["footer"] = false;
	// Stop config
	return $config;
}

function _dispatch_getmedia($template, $args) {
	$world = System::Get();
	
	$org_div = (int)$_REQUEST['context'];
	$file = $_REQUEST['name'];
	
	$record = $world->db->GetRow('Select * from org_media where org_div=? and org_media_name=?',array($org_div,$file));

	$media_file = getcwd()."/org_media/{$record['org_div']}/{$record['media_path']}";
	
	$segs = explode(".",$media_file);
	$ext = array_pop($segs);
	$mimes = mimeTypes();
	if(isset($mimes[$ext])) {
		header('Content-Type: '.$mimes[$ext]);
	}
	header('Content-Length: '.filesize($media_file));
	readfile($media_file);
}

function mimeTypes($file=null) {
	$file = '/usr/share/webmin/mime.types';
	if (!is_file($file) || !is_readable($file)) return false;
	$types = array();
	$fp = fopen($file,"r");
	while (false != ($line = fgets($fp,4096))) {
	    $regex = '/^\s*(?!#)\s*(\S+)\s+(?=\S)(.+)/';
		if (!preg_match($regex,$line,$match)) continue;
		$tmp = preg_split("/\s/",trim($match[2]));
		foreach($tmp as $type) $types[strtolower($type)] = $match[1];
	}
	fclose ($fp);
		         
	return $types;
}
/*
This is php's default mimetype reader. I don't know exactly you are trying to achieve but this might help.
function getUrlMimeType($url) {
    $buffer = file_get_contents($url);
    $finfo = new finfo(FILEINFO_MIME_TYPE);
    return $finfo->buffer($buffer);
}
*/
    # [...]
#
#     # read the mime-types
#         $mimes = mimeTypes('/usr/local/apache/current/conf/mime.types');
#
#             # use them ($ext is the extension of your file)
#                 if (isset($mimes[$ext])) header("Content-Type: ".$mimes[$ext]);
#                     header("Content-Length: ".@filesize($fullpath));
#                         readfile($fullpath); exit;

?>
