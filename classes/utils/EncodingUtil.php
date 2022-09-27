<?php

namespace utils;

class EncodingUtil {
	public static function GetEncoding($code) {
		
		switch ($code) {
			case 1 :
				return array (
						'code' => "437",
						"title" => "U.S. MS-DOS" 
				);
			case 2 :
				return array (
						'code' => "850",
						"title" => "International MS-DOS" 
				);
			case 3 :
				return array (
						'code' => "WINDOWS-1252",
						"title" => "Window ANSI" 
				);
			case 8 :
				return array (
						'code' => "865",
						"title" => "Danish OEM" 
				);
			case 9 :
				return array (
						'code' => "437",
						"title" => "Dutch OEM" 
				);
			case 10 :
				return array (
						'code' => "850",
						"title" => "Dutch OEM*" 
				);
			case 11 :
				return array (
						'code' => "437",
						"title" => "Finnish OEM" 
				);
			case 13 :
				return array (
						'code' => "437",
						"title" => "French OEM" 
				);
			case 14 :
				return array (
						'code' => "850",
						"title" => "French OEM*" 
				);
			case 15 :
				return array (
						'code' => "437",
						"title" => "German OEM" 
				);
			case 16 :
				return array (
						'code' => "850",
						"title" => "German OEM*" 
				);
			case 17 :
				return array (
						'code' => "437",
						"title" => "Italian OEM" 
				);
			case 18 :
				return array (
						'code' => "850",
						"title" => "Italian OEM*" 
				);
			case 19 :
				return array (
						'code' => "SHIFT-JIS",
						"title" => "Japanese Shift-JIS" 
				);
			case 20 :
				return array (
						'code' => "850",
						"title" => "Spanish OEM*" 
				);
			case 21 :
				return array (
						'code' => "437",
						"title" => "Swedish OEM" 
				);
			case 22 :
				return array (
						'code' => "850",
						"title" => "Swedish OEM*" 
				);
			case 23 :
				return array (
						'code' => "865",
						"title" => "Norwegian OEM" 
				);
			case 24 :
				return array (
						'code' => "437",
						"title" => "Spanish OEM" 
				);
			case 25 :
				return array (
						'code' => "437",
						"title" => "English OEM (Britain)" 
				);
			case 26 :
				return array (
						'code' => "850",
						"title" => "English OEM (Britain)*" 
				);
			case 27 :
				return array (
						'code' => "437",
						"title" => "English OEM (U.S.)" 
				);
			case 28 :
				return array (
						'code' => "863",
						"title" => "French OEM (Canada)" 
				);
			case 29 :
				return array (
						'code' => "850",
						"title" => "French OEM*" 
				);
			case 31 :
				return array (
						'code' => "852",
						"title" => "Czech OEM" 
				);
			case 34 :
				return array (
						'code' => "852",
						"title" => "Hungarian OEM" 
				);
			case 35 :
				return array (
						'code' => "852",
						"title" => "Polish OEM" 
				);
			case 36 :
				return array (
						'code' => "860",
						"title" => "Portugese OEM" 
				);
			case 37 :
				return array (
						'code' => "850",
						"title" => "Potugese OEM*" 
				);
			case 38 :
				return array (
						'code' => "866",
						"title" => "Russian OEM" 
				);
			case 55 :
				return array (
						'code' => "850",
						"title" => "English OEM (U.S.)* " 
				);
			case 64 :
				return array (
						'code' => "852",
						"title" => "Romanian OEM" 
				);
			case 77 :
				return array (
						'code' => "936",
						"title" => "Chinese GBK (PRC)" 
				);
			case 78 :
				return array (
						'code' => "CP949",
						"title" => "Korean (ANSI/OEM) " 
				);
			case 79 :
				return array (
						'code' => "CP950",
						"title" => "Chinese Big 5 (Taiwan)" 
				);
			case 80 :
				return array (
						'code' => "874",
						"title" => "Thai (ANSI/OEM) " 
				);
			case 87 :
				return array (
						'code' => "WINDOWS-1252",
						"title" => "ANSI" 
				);
			case 88 :
				return array (
						'code' => "WINDOWS-1252",
						"title" => "Western European ANSI " 
				);
			case 89 :
				return array (
						'code' => "WINDOWS-1252",
						"title" => "Spanish ANSI" 
				);
			case 100 :
				return array (
						'code' => "852",
						"title" => "Eastern European MS-DOS" 
				);
			case 101 :
				return array (
						'code' => "866",
						"title" => "Russian MS-DOS" 
				);
			case 102 :
				return array (
						'code' => "865",
						"title" => "Nordic MS-DOS" 
				);
			case 103 :
				return array (
						'code' => "861",
						"title" => "Icelandic MS-DOS" 
				);
			case 106 :
				return array (
						'code' => "737",
						"title" => "Greek MS-DOS (437G)" 
				);
			case 107 :
				return array (
						'code' => "857",
						"title" => "Turkish MS-DOS" 
				);
			case 108 :
				return array (
						'code' => "863",
						"title" => "French-Canadian MS-DOS" 
				);
			case 120 :
				return array (
						'code' => "CP950",
						"title" => "Taiwan Big 5" 
				);
			case 121 :
				return array (
						'code' => "WINDOWS-949",
						"title" => "Hangul (Wansung) " 
				);
			case 122 :
				return array (
						'code' => "CP936",
						"title" => "PRC GBK" 
				);
			case 123 :
				return array (
						'code' => "CP932",
						"title" => "Japanese Shift-JIS " 
				);
			case 124 :
				return array (
						'code' => "874",
						"title" => "Thai Windows/MS-DOS " 
				);
			case 134 :
				return array (
						'code' => "CP737",
						"title" => "Greek OEM" 
				);
			case 135 :
				return array (
						'code' => "852",
						"title" => "Slovenian OEM " 
				);
			case 136 :
				return array (
						'code' => "CP857",
						"title" => "Turkish OEM " 
				);
			case 200 :
				return array (
						'code' => "WINDOWS-1250",
						"title" => "Eastern European Windows" 
				);
			case 201 :
				return array (
						'code' => "WINDOWS-1254",
						"title" => "Russian Windows " 
				);
			case 202 :
				return array (
						'code' => "WINDOWS-1254",
						"title" => "Turkish Windows " 
				);
			case 203 :
				return array (
						'code' => "WINDOWS-1253",
						"title" => "Greek Windows" 
				);
			case 204 :
				return array (
						'code' => "WINDOWS-1257",
						"title" => "Baltic Windows" 
				);
		}
	}
}

?>