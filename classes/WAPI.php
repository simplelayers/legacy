<?php

use auth\Context;
use auth\Creds;
use utils\DOUtil;
use utils\ParamUtil;
use v5\Projections;

class WAPI {

    const ALAYER_LAYER = 'layer';
    const ALAYER_PROJECTLAYER = 'project';
    const FORMAT_CSV = 'csv';
    const FORMAT_JPEG = 'jpg';
    const FORMAT_JSON = 'json';
    const FORMAT_PNG = 'png';
    const FORMAT_XLS = 'xls';
    const FORMAT_XML = 'xml';
    const ACTION_ADD = 'add';
    const ACTION_CHANGESET = 'changeset';
    const ACTION_DELETE = 'delete';
    const ACTION_GET = 'get';
    const ACTION_IMPORT = 'import';
    const ACTION_LIST = 'list';
    const ACTION_RESET = 'reset';
    const ACTION_SAVE = 'save';
    const ACTION_UPDATE = 'update';
    const ACTION_DRIVER = 'driver';
    const ACTION_UPSERT = 'upsert';
    const TARGET_CONTEXT = 'context';
    const TARGET_PERMISSION = 'permission';
    const TARGET_PLAN = 'plan';
    const TARGET_ROLE = 'role';
    const TARGET_SEAT = 'seat';

    public $format = 'xml';

    /* @var $project Project */
    private $project;
    private $projectPermission;

    /* @var $projectLayer ProjectLayer */
    private $projectLayer;
    private $projectLayers;

    /* @var $layer Layer */
    public $layer;
    public $layers;
    public $layerType;

    /* @var $connection ADOConnection */
    public $connection;

    public function __construct() {
        $this->format = ParamUtil::Get($_REQUEST, 'format', $this->format);
    }

    public function SetConnection($connection) {
        $this->connection = $connection;
    }

    public function RequireToken() {
        $session = SimpleSession::Get();
        $userInfo = $session->GetUserInfo();
        $user = $session->GetUser();
        $context = Context::Get(Creds::GetFromRequest());
        if (!RequestUtil::HasParam('token')) {
            // $connection->LoginToken($_REQUEST['token']);
            $tokenValid = $context->sessState = SimpleSession::STATE_SESS_OK;
            if (!$tokenValid) {
                throw new Exception('invalid token');
            }
        }
        return $user;
    }

    public function RequireProject($projectId = null) {

        $sys = System::Get();

        if (!is_null($projectId))
            RequestUtil::Set('project', $projectId);

        $userInfo = SimpleSession::Get()->GetUserInfo();

        if (is_null($this->project)) {
            $project = RequestUtil::Get('project', RequestUtil::Get('mapId', false));

            if (is_string($project)) {
                $project = Project::Get($project);
            }

            if (!$project)
                throw new Exception("Parameter not present:Parameter &project must be specified");

            list ($embeddable, $bpermission) = $project->checkBrowserPermission($userInfo['id'], $_REQUEST, $_SERVER);

            $permission = max($embeddable, $bpermission);

            $this->project = $project;
            $this->projectPermission = $permission;

            if ($permission < AccessLevels::READ)
                throw new Exception("Invalid Permission: you do not have permission to view this map.");
        }

        return array(
            'project' => $this->project,
            'permission' => $this->projectPermission
        );
    }
    public function RequireProjection($args = null) {
        if (is_null($args)) {
            $args = $this->GetParams();
        }
        list($srsIn) = ParamUtil::Requires($args, 'srs');
        $srs = '';
        switch ($srsIn) {
            case 'prj':

                list($prj) = ParamUtil::Requires($args, 'prj');
                $srs = $prj;
                break;
            case 'srid':
                list($srid) = ParamUtil::Requires($args, 'srid');
                $srs = Projections::SRID2SRS($srid);
                break;
            case 'authcode':
                list($authCode) = ParamUtil::Requires($args, 'auth-code');

                $srs = Projections::GetSRS($authCode);
                break;
            case 'layer':
                $layerId = ParamUtil::RequiresOne($args, 'layer', 'lid', 'layerid', 'layer-id', 'layer_id');
                $srs = Projections::GetLayerSRS($layerId);
                break;
            case 'web':
                $srs = Projections::GetWebSRS();
                break;
            case 'latlon':
                $srs = Projections::GetLatLonSRS();
                break;
            case 'default':
            default:
                $srs = Projections::GetDefaultSRS();
                break;
        }
        return $srs;
    }
    public function RequireAdmin() {
        $context = Context::Get();
        if (!$context->IsSysAdmin()) {
            throw new Exception('Access Denied: this service is only available to system administrators');
        }
    }

    public function RequireProjectLayers($requiredType) {
        $players = RequestUtil::GetList('players', ',');
        if (is_null($players))
            $players = array();

        $this->projectLayers = array();
        foreach ($players as $player) {
            $layer = $this->RequireProjectLayer($requiredType, $player);
            if (!is_null($requiredType)) {
                if (!is_array($requiredType))
                    $requiredType = array(
                        $requiredType
                    );
                if (!in_array($this->layer->type, $requiredType))
                    throw new Exception(DENIED_WRONGGEOM);
            }
            $this->projectLayers[] = $layer;
            $this->projectLayer = $this->layer = null;
        }
        if (count($this->projectLayers) == 0) {
            $this->projectLayers = array(
                $this->RequireProjectLayer($requiredType)
            );
            return $this->projectLayers;
        } else {
            $this->projectLayer = null;
        }
        return $this->projectLayers;
    }

    public function RequireProjectLayer($requiredType = null, $id = null) {

        if (!is_null($id)) {
            $_REQUEST['plid'] = $id;

            $this->projectLayer = $pLayer = ProjectLayer::Get($id);
            $this->layer = $this->projectLayer->layer;
        }

        $this->layerType = self::ALAYER_PROJECTLAYER;

        if (!is_null($this->projectLayer)) {
            if (is_null($requiredType))
                return $this->projectLayer;
        } elseif (isset($_REQUEST['plid'])) {
            $plid = $_REQUEST['plid'];
            $this->projectLayer = ProjectLayer::Get($plid);
            $this->layer = $this->projectLayer->layer;
        } else {
            return false;
        }

        if (!is_null($requiredType)) {
            if (!is_array($requiredType))
                $requiredType = array(
                    $requiredType
                );
            if (!in_array($this->layer->type, $requiredType))
                throw new Exception(DENIED_WRONGGEOM);
        }

        return $this->projectLayer;
    }

    public function RequireLayerId($requiredPermission, $idParam = 'layerId', $requiredType = null, $asLayer = true) {

        if ($this->layer)
            return ($asLayer) ? $this->layer : $this->layer->id;
        $layerId = ParamUtil::Get($_REQUEST, $idParam);

        if (is_null($layerId))
            return null;
        $this->layer = Layer::GetLayer($layerId);
        if (is_null($this->layer))
            return null;
        if ($requiredType) {
            if (!is_array($requiredType))
                $requiredType = array(
                    $requiredType
                );
            if (!in_array($this->layer->type, $requiredType))
                throw new Exception(DENIED_WRONGGEOM);
        }
        $user = SimpleSession::Get()->GetUser();
        if (!AccessLevels::HasAccess($this->layer->getPermissionById($user->id), $requiredPermission)) {
            $access = AccessLevels::GetEnum();

            $accessLevel = $access[$requiredPermission];

            throw new Exception('Permission Denied: user ' . $user->username . " must have $accessLevel permission");
        }

        return ($asLayer) ? $this->layer : $layerId;
    }

    public function RequireLayer($requiredType = null, $id = null) {
        if (!is_null($id)) {
            $_REQUEST['lid'] = $id;
            $this->layer = System::Get()->getLayerById($id);
        }
        $layerId = ParamUtil::RequiresOne($_REQUEST, 'layer', 'layerId', 'lid', 'layer_id');
        $this->layer = System::Get()->getLayerById($layerId);

        $this->layerType = self::ALAYER_LAYER;
        if (is_a($this->layer, 'Layer') && $requiredType) {
            if (!is_array($requiredType))
                $requiredType = array(
                    $requiredType
                );
            if (!in_array($this->layer->type, $requiredType))
                throw new Exception(DENIED_WRONGGEOM);
        }
        return $this->layer;
    }

    public function RequireLayers($requiredType = null, $layerVar = 'layers', $args = null) {
        if (is_null($args))
            $args = $_REQUEST;
        $this->layers = array();
        $layers = ParamUtil::Get($args, 'layers', array());
        if (!is_null($layers)) {
            if (!is_array($layers)) {
                $layers = explode(',', $layers);
            }
        }

        foreach ($layers as $layerId) {
            array_push($this->layers, $this->RequireLayer($requiredType, $layerId));
        }

        if (count($this->layers) == 0) {
            $this->layers = array(
                $this->RequireLayer($requiredType)
            );
        } else {
            $this->layer = null;
        }

        return $this->layers;
    }

    public function RequireALayer($requiredType = null, $pLayerVar = 'player', $layerVar = 'layer') {
        $player = ParamUtil::Get(self::GetParams(), $pLayerVar);

        if (!is_null($player)) {
            return $this->RequireProjectLayer($requiredType, RequestUtil::Get($pLayerVar));
        }
        return $this->RequireLayer($requiredType, RequestUtil::Get($layerVar));
    }
    public function RequireALayer_v5($requiredType = null, $args = null) {
        if (is_null($args)) {
            $args = $_REQUEST;
        }
        $player = ParamUtil::GetOne($_REQUEST, 'pid', 'plid', 'playerid', 'pLayerId', 'player_id', 'player', 'pLayer');
        $layer = ParamUtil::GetOne($_REQUEST, 'lid', 'layerid', 'layerId', 'layer_id', 'layer');

        if (!is_null($player)) {

            return $this->RequireProjectLayer($requiredType, $player);
        }
        return $this->RequireLayer($requiredType, $layer);
    }

    public static function GetInputXML() {
        $xmlString = file_get_contents('php://input');
        $xml = ($xmlString == "") ? "" : new SimpleXMLElement($xmlString);
        $_REQUEST['xml'] = $xml;
        return $xml;
    }

    public static function GetInputJSON() {
        $jsonString = file_get_contents('php://input');
        $_REQUEST['json'] = json_encode($jsonString);
        return $_REQUEST['json'];
    }

    public function TokenValid() {
        if (!$this->connection->tokenValid)
            throw new Exception("Invalid Request: provided token was not valid");
        return $this->connection->token;
    }

    public static function HandleError($exception) {
        error_log($exception->getMessage() . "::" . $exception->getTraceAsString());

        switch ($wapi = WAPI::GetFormat()) {
            case self::FORMAT_JSON:
                $json = array();
                $info = explode(":", $exception->getMessage());
                $message = array_shift($info);
                $detail = count($info) ? implode(":", $info) : "";
                $json['status'] = $message;
                $json['detail'] = $detail;
                die(json_encode(array(
                    'json' => $json
                )));
                break;
            case self::FORMAT_XML:
                header('Content-type: application/xml');
                $template = new SLSmarty(WEBROOT . 'contexts/wapi/templates/');
                $template->assign("ok", "problem");
                $template->assign("message", $exception->getMessage());
                $template->display('wapi/okno.tpl');
                exit(0);
                break;
        }
    }
    public static function WriteJSONHeader() {
        header('Content-type: application/json');
    }
    public static function WriteXMLHeader() {
         header('Content-type: application/xml');
    }
    public static function DecorateConfig(&$config) {
        $config["header"] = false;
        $config["footer"] = false;
        $config["customHeaders"] = true;
        $config['sendUser'] = true;
        $config['sendWorld'] = true;
        return $config;
    }

    public static function SetWapiHeaders($type = null, $fileName = null) {
        $format = (!is_null($type)) ? $type : RequestUtil::Get('format', self::FORMAT_JSON);

        switch ($format) {
            case self::FORMAT_CSV:
                if (is_null($fileName)) {
                    throw new Exception('Attempting to export CSV without a filename');
                }
                header('Content-Encoding: UTF-8');
                header('Content-type: text/csv; charset=UTF-8');
                header("Content-Disposition: attachment; filename={$fileName}");
                echo "\xEF\xBB\xBF"; // UTF-8 BOM
                break;
            case self::FORMAT_XLS:
                header('Content-Encoding: UTF-8');
                header('Content-type: application/vnd.ms-excel; charset=UTF-8');
                header('Content-Disposition: attachment; filename=SearchResults.xls');
                break;
            case self::FORMAT_PNG:
                header('Content-Type: image/png');
                break;
            case self::FORMAT_JPEG:
                header('Content-Type: image/jpeg');
                break;
            case "json":
            case "ajax":
                // header('Content-Encoding: UTF-8');
                header('Content-Type:text/plain');
                break;
            case "xml":
                header('Content-Type: text/xml');
                break;
            default:
                header('Content-Type: text/html');
                break;
        }
    }

    public static function WriteXMLStart() {
        echo "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n";
    }

    public static function GetFormat() {
        $api_cmd = DOUtil::Get();
        $api_cmdParts = explode(':', $api_cmd);
        $cmd1 = array_shift($api_cmdParts);
        $version = 'v4';

        if (strpos($cmd1, '.')) {
            $version = 'v3';
        }
        $defautFormat = self::FORMAT_XML;
        if ($version == 'v4')
            $defautFormat = self::FORMAT_JSON;
        return RequestUtil::Get('format', $defautFormat);
    }

    public function ToTemplate($template) {
        if (!is_null($this->project))
            $template->assign('project', $this->project);
        if (!is_null($this->layer))
            $template->assign('layer', $this->layer);
        if (!is_null($this->projectLayer))
            $template->assign('projectLayer', $this->projectLayer);
        $template->assign('world', System::Get());
    }
    
     
    public static function MergeParams($params, $overwrite = true) {
        foreach ($params as $key => $val) {
            if (isset($_REQUEST[$key])) {
                $val = $overwrite ? $params[$key] : $_REQUEST[$key];
            }
            $_REQUEST[$key] = $val;
        }
        return $_REQUEST;
    }

    public static function RunIt($do, $params, $loopHandler = null, $endHandler = null, $asUser = null) {

        // $user = $this->GetUser();
        $params = self::GetParams();
        $params['do'] = $do;
        // $params['userId'] = is_null($asUser) ? $user->id : $asUser;
        $ps = json_encode($params);

        if (ob_get_level() == 1) {
            ob_end_clean();
        }
        $descriptorspec = array(
            0 => array(
                "pipe",
                "r"
            ), // stdin is a pipe that the child will read from
            1 => array(
                "pipe",
                "w"
            ), // stdout is a pipe that the child will write to
            2 => array(
                "pipe",
                "w"
            )
        ); // stderr is a file to write to

        $baseDir = BASEDIR;
        $cmd = '/usr/local/zend/bin/php ' . BASEDIR . '/runnit.php json_params=' . escapeshellarg($ps);
        $proc = proc_open($cmd, $descriptorspec, $pipes, $baseDir, array());
        $status = proc_get_status($proc);
        while ($status['running'] === true) {
            $out = stream_get_contents($pipes[1], 4096);

            if (connection_aborted()) {
                @ proc_terminate($proc);
                @ proc_close($proc);
                // fclose($proc);
                if ($endHandler)
                    $endHandler('abort');
                $endHandler();
                die();
            }
            $status = proc_get_status($proc);
            if ($loopHandler) {
                $loopHandler($out);
            }
        }
        @ proc_close($proc);
        if ($endHandler)
            $endHandler('complete');
    }
      public static function SetParam($target, $val) {
        $_REQUEST[$target] = $val;
        return $_REQUEST;
    }

    public static function GetParams() {
        return $_REQUEST;
    }

    public static function GetParam($param, $default = null) {
        return isset($_REQUEST[$param]) ? $_REQUEST[$param] : $default;
    }

    public static function GetFlagParam($param, $default = false) {
        return isset($_REQUEST[$param]) ? ($_REQUEST[$param] == '1') : $default;
    }

    public static function ResultsToXML($resultCursor, $docElementName = 'items', $docAttributes = null, $resultElementName = 'item', $asElements = false, $itemFunction = null, $includeXMLHeader = true) {
        $hasResults = !in_array($resultCursor, array(
                    false,
                    null
        ));
        if (is_null($docAttributes))
            $docAttributes = array();

        /* @var $resultCursor ADORecordSet */

        if ($hasResults)
            $hasResults = is_a($resultCursor, 'ADORecordSet') ? $resultCursor->RecordCount() > 0 : count($resultCursor) > 0;

        if ($includeXMLHeader)
            echo '<?xml version="1.0" encoding="UTF-8" ?>' . "\n";
        echo "<$docElementName ";
        foreach ($docAttributes as $key => $val) {
            echo $key . '="';
            echo htmlentities($val) . '" ';
        }
        echo ">";
        if ($hasResults) {
            foreach ($resultCursor as $result) {
                $text = "<$resultElementName ";
                if ($asElements)
                    $text .= '>';
                echo ($text);
                foreach ($result as $key => $val) {
                    if ($itemFunction)
                        $val = $itemFunction($key, $val);
                    if ($key != '') {
                        if ($asElements) {

                            $text = "<$key>";
                            echo ($text);
                            if (is_string($val)) {
                                $val = htmlentities($val);
                                $text = "<![CDATA[$val]]>";
                                echo ($text);
                            }
                            $text = '</' . $key . '>';
                            echo ($text);
                        } else {
                            foreach ($result as $key => $val) {
                                if (is_null($val))
                                    $val = "";

                                $val = htmlentities($val);
                                // echo("<br/>");
                                echo " $key=\"$val\"";
                            }
                        }
                    }
                }
                $text = '</' . $resultElementName . '>';
                echo ($text);
            }
        }

        echo "</$docElementName>";
    }

    public static function ResultsToJSON($resultCursor, $varName = 'items', $itemFunction = null) {
        $hasResults = !in_array($resultCursor, array(
                    false,
                    null
        ));

        if ($hasResults)
            $hasResults = is_a($resultCursor, 'ADORecordSet') ? $resultCursor->RecordCount() > 0 : count($resultCursor) > 0;

        echo '{' . $varName . ':[';
        if ($hasResults) {
            foreach ($resultCursor as $result) {
                foreach ($result as $key => $val) {
                    if ($itemFunction)
                        $val = $itemFunction($key, $val);
                    if ($key != '') {
                        $obj = (object) array(
                                    $key => $val
                        );
                        echo json_encode($obj);
                    }
                }
            }
            echo ']}';
        }
    }

    public static function SendSimpleResponse($info, $format = null, $status = 'ok') {

        if (is_null($format)) {
            $format = strtolower(RequestUtil::Get('format', 'json'));
        }

        if ($format == self::FORMAT_JSON) {
            if (is_a($info, 'ADORecordSet')) {
                $info = array(
                    'results' => $info
                );
            }
            $normalJSON = false;
            $hadResults = false;

            if (!isset($info['results'])) {
                if (!isset($info['status'])) {
                    $info['status'] = $status;
                }
                echo json_encode($info);
                return;
            }

            echo '{';
            if (isset($info['results'])) {

                if (is_a($info['results'], 'ADORecordSet')) {
                    echo '"results":[';
                    $isFirst = true;
                    foreach ($info['results'] as $record) {
                        if (!$isFirst)
                            echo ',';
                        echo json_encode($record);
                        $isFirst = false;
                    }
                    echo ']';
                } else {
                    echo '"results":';
                    echo json_encode($info['results']);
                }
                unset($info['results']);
                $hadResults = true;
            }
            if (count($info)) {
                echo ',"metadata":' . json_encode($info);
            }
            echo '}';

            return;
        } else {
            header('Content-type: application/xml');
            echo "<response do='" . DOUtil::Get() . "' status='ok'>";
            $resultsOpen = false;

            foreach ($info as $key => $val) {

                if (is_array($val)) {
                    echo self::ArrayToXML($key, $val);
                } else {
                    $jsonPrefix = '_json:';
                    $val = (substr($val, 0, strlen($jsonPrefix)) == $jsonPrefix) ? substr($val, strlen($jsonPrefix)) : htmlspecialchars($val);
                    echo "<$key><![CDATA[" . $val . "]]></$key>";
                }
            }
            echo "</response>";
        }
    }

    public static function SendSimpleResults($results = null, $format = null, $asMongo = false, $status = null, $resultParams = null, $elementNames = null) {
        if (is_null($results))
            $results = array();
        if (is_null($format))
            $format = self::GetFormat();
        if (is_null($resultParams)) {
            $resultAtts = array();
            $resultAtts[] = array();
        }
        if ($asMongo) {
            switch ($format) {
                case self::FORMAT_JSON:
                    self::MongoResultsToJSON($results);
                    return;
                    break;
                case self::FORMAT_XML:
                    self::MongoResultsToXMLResponse($results);
                    return;
                    break;
            }
        } else {
            switch ($format) {
                case self::FORMAT_JSON:
                    self::SendSimpleResponse(array('results' => $results), 'json', $status);
                    return;
                    break;
                case self::FORMAT_XML:
                    echo '<?xml version="1.0" encoding="UTF-8" ?>' . "\n";
                    //echo "<results do=\"" . DOUtil::Get() . "\" >";
                    if (!isset($elementNames)) {
                        $elementNames = array('docName' => 'results', 'itemName' => 'result');
                    }
                    if ($resultParams) {
                        foreach ($resultParams as $key => $resultAttributes) {
                            self::ResultsToXML($results, $elementNames['docName'], $resultAttributes, $elementNames['itemName'], true, null, false, $elementNames);
                        }
                    } else {
                        self::ResultsToXML($results, $elementNames['docName'], null, $elementNames['itemName'], true, null, false, $elementNames);
                    }
                    //echo "</results>";
                    return;
                    break;
            }
        }

        self::SendSimpleResponse(array(
            'results' => $results
                ), $format, $status);
    }

    public static function MongoResultsToJSON($resultSet = false) {
        echo '{results:[';
        $notFirst = false;
        foreach ($resultSet as $result) {
            echo $notFirst ? ',' . json_encode($result) : json_encode($result);
            $notFirst = true;
        }
        echo ']}';
    }

    public static function MongoResultsToXMLResponse($resultSet) {
        $results = array();

        foreach ($resultSet as $result) {

            $results[] = $result;
        }
        self::SendSimpleResponse(array(
            'results' => $results
        ));
    }

    public static function ArrayToXML($elementName, array $array) {
        echo "<$elementName>";
        foreach ($array as $key => $val) {
            if (is_array($val)) {

                $isAssoc = (bool) count(array_filter(array_keys($val), 'is_string'));
                if (is_numeric(substr($key, 0, 1))) {
                    $key = 'item id="' . $key . '"';
                    self::ArrayToXML($key, $val);
                    continue;
                } elseif ($isAssoc) {
                    if (is_numeric($key))
                        $key = 'item';
                    self::ArrayToXML($key, $val);
                    continue;
                } else {
                    $isObjArray = (bool) count(array_filter(array_keys($val[0]), 'is_string'));
                    if ($isObjArray) {

                        self::ArrayToXML($key, $val);

                        $endKey = (stripos($key, ' ') >= 0) ? array_shift(explode(' ', $key)) : $key;
                        //echo "</$endKey>";
                    } else {
                        $endKey = (stripos($key, ' ') >= 0) ? array_shift(explode(' ', $key)) : $key;
                        echo "<$key type='array' separator=','><![CDATA[" . htmlspecialchars(implode(',', $array)) . "]]></$endKey>";
                    }
                }
            } else {
                $type = is_int($val) ? 'int' : null;
                if (is_null($type))
                    $type = is_float($val) ? 'float' : null;
                if (is_null($type))
                    $type = is_string($val) ? 'string' : null;
                if (is_null($type))
                    continue;
                $keyParts = explode(' ', $key);
                $endKey = (stripos($key, ' ') >= 0) ? array_shift($keyParts) : $key;
                $cdataStart = ($type == 'string') ? '<![CDATA[' : '';
                $value = ($type == 'string') ? htmlspecialchars($val) : '' . $val;
                $cdataEnd = ($type == 'string') ? ']]>' : '';
                echo "<$key type='$type'>$cdataStart" . $value . $cdataEnd . "</$endKey>";
            }
        }
        $elementNameParts = explode(' ', $elementName);
        $endElement = (stripos($elementName, ' ') >= 0) ? array_shift($elementNameParts) : $elementName;
        echo '</' . $endElement . ">";
    }

    public static function SendBlankImage() {
        header('Content-type: image/png', true);
        readfile(BASEURL . 'media/images/empty.png');
        die();
    }
    public static function SendDownloadHeaders($filename, $size,$contentType = 'application/x-unkown') {
        $fileName = urlencode($filename);

        header('Content-type: ' . $contentType);
        header("Content-disposition: attachment; filename=\"$fileName\"", true);
    }
}

?>
