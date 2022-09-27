<?php
namespace reporting;

class PGSQLLogMiner
{
    public static function MineLogs($outPath,$layerIds=null,$months=null) {
        $db = \System::GetDB(\System::DB_ACCOUNT_SU);
        $fh = fopen($outPath.'/pgsqlminer.log','w+');
        if( is_nulL($layerIds) ) {
            
            $query = "SELECT id,name from layers where type=".\LayerTypes::VECTOR;
            
            $layerIds = $db->Execute($query); 
        }
        
        if(is_null($months)) {
            $logFiles = glob('/mnt/data_1/log_bu/varlog/postgresql/postgresql-20*.log*');            
        } else {
            $logFiles = array();
            foreach($months as $month) {
                $suffix = '*.log*';
                if(substr(-1,1)=='.') $suffix = 'log*';
                $glob = "/mnt/data_1/log_bu/varlog/postgresql/postgresql-{$month}{$suffix}";
                $files = glob("$glob");
                $logFiles = array_merge($logFiles,$files);
            }    
        }
        
        $date =null;
        $entry =array();
        $entry[] = 'date';
        $entry[] = 'layer_id';
        $entry[] = 'layer-name';
        $entry[] = 'inserts';
        $entry[] = 'updates';
        $entry[] = 'deletions';
        $entry[] = 'creations';
        $entry[] = 'truncations';
        $entry[] = 'drops';
        $entry[] = 'selects';
        
        
        fwrite($fh,implode("\t",$entry)."\n");
        
        for($i=0; $i < count($logFiles); $i++ ){

            
            $logFile= $logFiles[$i];
            $date = basename($logFile,'.log');
            $date = str_replace('postgresql-','',$date);
            $date = array_shift(explode('.',$date));
            $date = array_shift(explode('_',$date));
            $ii = 0;
            foreach($layerIds as $info) {
                echo 'logfile '.$i.' of '.count($logFiles);
                echo "\tlayer $ii of ".$layerIds->RowCount()."\n";
                ob_flush();
                $ii++;
                $entry = array();
                $entry[] = $date;
                $entry[] = $info['id'];
                $entry[] = $info['name'];
                $escapeqt='\"';
                $cmd = <<<CMD
grep  "INSERT INTO" $logFile | grep -o "vectordata_{$info['id']}" | wc -l
CMD;
                $res = shell_exec($cmd);
                if(is_null($res)) {
                    $entry[] = 0;
                } else {
                      $entry[] = trim(str_replace("\n","",array_pop(explode(":",$res))));
                }
                $hadInsert = (is_null($res)) ? false : true;
                $cmd = <<<CMD
grep  "UPDATE" $logFile | grep -o "vectordata_{$info['id']}" | wc -l
CMD;
                $res = shell_exec($cmd);
                if(is_null($res)) {
                    $entry[] = 0;
                } else {
                    $entry[] = trim(str_replace("\n","",array_pop(explode(":",$res))));
                }
                
                $cmd = <<<CMD
grep "DELETE FROM" $logFile | grep -o "vectordata_{$info['id']}" |  wc -l
CMD;
                $res = shell_exec($cmd);
                if(is_null($res)) {
                    $entry[] = 0;
                } else {
                      $entry[] = trim(str_replace("\n","",array_pop(explode(":",$res))));
                }
                //if($hadInsert) var_dump($cmd);
                $cmd = <<<CMD
grep "CREATE TABLE" $logFile | grep -o  "vectordata_{$info['id']}" | wc -l
CMD;
                $res = shell_exec($cmd);
                if(is_null($res)) {
                    $entry[] = 0;
                } else {
                      $entry[] = trim(str_replace("\n","",array_pop(explode(":",$res))));
                }
                
                $cmd = <<<CMD
grep "TRUNCATE TABLE" $logFile | grep -o "vectordata_{$info['id']}" | wc -l
CMD;
                $res = shell_exec($cmd);
                if(is_null($res)) {
                    $entry[] = 0;
                } else {
                      $entry[] = trim(str_replace("\n","",array_pop(explode(":",$res))));
                }

               
                $cmd = <<<CMD
grep "DROP TABLE IF EXISTS" $logFile | grep -o "vectordata_{$info['id']}" | wc -l
CMD;
                $res = shell_exec($cmd);
                if(is_null($res)) {
                    $entry[] = 0;
                } else {
                      $entry[] = trim(str_replace("\n","",array_pop(explode(":",$res))));
                }
                
                $cmd = <<<CMD
grep "SELECT" $logFile |  grep "FROM " | grep -o "vectordata_{$info['id']}" | wc -l
CMD;
                $res = shell_exec($cmd);
                if(is_null($res)) {
                    $entry[] = 0;
                } else {
                    $entry[] = trim(str_replace("\n","",array_pop(explode(":",$res))));
                }
                
                
                fwrite($fh,implode("\t",$entry)."\n");
                //print(implode("\t",$entry)."\n");
                ob_flush();
             }
            
            
        }
        fclose($fh);
        
    }
    
    
    
}

?>