<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

function _exec()
{
    $wapi = \System::GetWapi();
    $params = $wapi->GetParams();
    
    $loopHandler = function($out) {
        echo $out;
        @ ob_flush();
        flush();           
    };
    $endHandler = function($state) {
        if($state == 'done') {
            echo "<p>complete";
        }
    };
    
    WAPI::RunIt('tools/test_api', $params,$loopHandler,$endHandler);
    
    
}


/*echo "in test fork";
flush();

$ok = false;

function shutdown()
{
    global $ctr;
    global $pid;
    global $ok;
    
    if (! $ok) {
        
        $ps = shell_exec("ps -p $pid -o pid=");
        error_log("Premature end: should be killing process $pid;$ps");
        shell_exec('kill -9 ' . $pid);
        $ps = shell_exec("ps -p $pid -o pid= | grep $pid");
        error_log("Kill signal sent for $pid;$ps");
    }
}

register_shutdown_function('shutdown');
echo "<p>Starting long process";
$cmd = "/usr/local/zend/bin/php $path/test_worker.php > /dev/null 2>&1 & echo $!";

$pid = trim(shell_exec($cmd));

$ctr = 0;
flush();
ob_start();
var_dump($cmd);
while (shell_exec("ps -p $pid -o pid=") != "") {
    echo "<p>$ctr";
    ob_flush();
    flush();
    usleep(500000);
    // keep looping
    $ctr += 1;
}
echo "<p>long process completed";
$ok = true;
flush();

?>
*/