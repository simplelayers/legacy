<?php

namespace auth;

use Security;
use SimpleSession;

function CreateSandboxKey($sandboxURL, $sessionId = null)
{
    $sessionId = is_null($sessionId) ? session_id() : $sessionId;
    if ($sessionId === '') $sessionId = null;
    return base64_encode(Security::Encode_TwoWay($sandboxURL, $sessionId));
}
function CreateSandboxAppKey($sandboxKey,$appURL,$sessionId = null)
{
    $sandboxURL = Security::SRead(base64_decode($sandboxKey),$sessionId);
    $url = $sandboxURL.':::'.$appURL;
    return base64_encode(CreateSandboxKey($url,$sessionId));
}
function ResolveSandBoxKey($sandBoxKey,$sessionId=null) {
    $sandboxURL = Security::SRead(base64_decode($sandBoxKey),$sessionId);
    return $sandboxURL;
}
function ResolveAppKey($appKey,$sessionId) {
    $sandboxAPPInfo = Security::SRead(base64_decode($appKey),$sessionId);
    $info = explode(':::',$sandboxAPPInfo);
    array_shift($info); // $sandboxURL
    $appURL = array_shift($info);
    return $appURL;
    
}

function ValidateAppKey($sandboxAppKey,$sandBoxKey,$sessionId) {
    $sandboxURL = ResolveSandBoxKey($sandBoxKey,$sessionId);
    $sandboxAPPInfo = Security::SRead($sandboxAppKey,$sessionId);
    $info = explode(':::',$sandboxAPPInfo);
    $appSandbox = array_shift($info);
    return ($sandboxURL === $appSandbox);
}