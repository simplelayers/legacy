<?php

namespace utils;

/**
 * ResponseUtil
 *
 * ResponseUtil is meant to help create resposnses in REST endpoints.
 * Given a format and the current action it is possible to
 * send back simple responses, or results either from an array/object or
 * from an ADORecordSet (results from querying the database).
 *
 * ResponseUtil will support JSON or XML output. Using this class it is
 * possible to send data back in common formats without having to know much
 * about them.
 *
 * As far as writing data out is concerned the ResponseUtil functionality
 * has an optimization in that it doesn't build the response in memory and then
 * send it, rather it writes out stuff to the client as it goes.
 */
class ResponseUtil {

    const FORMAT_JSON = "json";
    const FORMAT_XML = "xml";
    const IS_FIRST = true;
    const NOT_FIRST = false;
    const FULL_RESPONSE = true;
    const ACTION_ONLY = false;
    const AS_ARRAY = false;
    const AS_OBJ = true;
    const AS_ANY = -1;
    const STATUS_OK = true;
    const NO_VALUE = null;

    private $format = self::FORMAT_JSON;
    private $action;
    private $includeHeaders = true;
    private $started = false;
    private $resultsStarted = false;
    private $resultsAsObj = false;
    private $resultCount = 0;
    private $properties = null;
    private $responseEnded = false;
    private $resultsEnded = false;
    public $includeGeom = false;

    /**
     * Constructor function
     *
     * When the object is initially constructed it does not actually begin
     * sending anything until it is explicitly told to do so.
     *
     * Example
     *
     * $response = new ResponseUtil('json','myendpoint/action');
     *
     * @param string $format
     *            'json' or 'xml'
     * @param string $action
     *            the endpoint and action that was called
     * @param boolean $includeHeaders
     *            generate html headers for the response, this should only be done if you have not already sent headers or written back text.
     *            
     */
    public function __construct($format, $action, $includeHeaders = true) {
        $this->format = $format;
        $this->action = $action;
        $this->includeHeaders = $includeHeaders;
    }

    /**
     * Properties are a set of key/vals that will be added automatically
     * when StartResponse is called.
     *
     * @param mixed $props
     *            arry or object with key val data.
     */
    public function SetProps($props) {
        $this->properties = $props;
    }

    /*
     * public static function RequireSessionUser() {
     *
     * $sessionUser = Users::GetSessionUser();
     * if (! $sessionUser) {
     * throw new \Exception('Session Error: No session or session user not known');
     * $response = new ResponseUtil(ParamUtil::Get($_REQUEST,'format'),'site_data/...');
     * $response->SendError('no session or session user not known',true);
     * return;
     * }
     * return $sessionUser;
     *
     * }
     */

    /**
     * Begins the response by writing out the beginning content including the action.
     *
     * If the object was constructed with includeHeaders set true this will also
     * take care of sending the Content-type header based on the format specified
     * at construction.
     *
     * Output is sent directly to the output stream via echo.
     *
     *
     * @return \utils\ResponseUtil current instance of object; allows chaining
     */
    public function StartResponse($includeResponseKey = true) {
        if ($this->started) {
            return $this;
        }
        switch ($this->format) {
            case 'xml':
                $action = $this->action;
                if ($this->includeHeaders) {
                    header("Content-type: text/xml");
                }
                echo '<?xml version="1.0" encoding="UTF-8" ?>' . "\n";
                $action = htmlentities($action);

                echo <<<XML
<response><action>$action</action>
XML;
                break;
            case 'json':
            default:
                $action = $this->action;
                if ($this->includeHeaders)
                    header("Content-type: application/json");

                if ($includeResponseKey) {
                    echo '{"response":';
                }
                $start = <<<JSON
{"action":"$action"
JSON;
                echo (trim($start));
                break;
        }
        $this->started = true;
        if ($this->properties !== null) {
            $val = "";
            foreach ($this->properties as $key => $val) {
                $this->WriteKeyVal($key, $val);
            }
        }

        return $this;
    }

    /**
     * Send an error message back to the client and sets the status for the
     * response to "error"
     *
     * You can also opt to end the response by passing true for the fullResponse
     * parameter.
     *
     * Example 1:
     *
     * $response = new ResponseUtil('json','myendpoint/action');
     * $response->StartResponse();
     *
     * try {
     * $result = 1/0;
     * } catch (Exception $e) {
     * $response->SendError($e->getMessage(),self::ResponseUtil::FULL_RESPONSE);
     * }
     *
     *
     * Example 2
     *
     * $response = new ResponseUtil('json','myendpoint/action');
     * $response->SendError('Missing parameter; why did you do that?',ResponseUtil::FULL_RESPOSE);
     *
     * @param string $message
     *            Error message to send back to the client
     * @param boolean $fullResponse
     *            (default: ResponseUtil::ACTION_ONLY)
     *            if ResponseUtil::FULL_RESPONSE a full response will be written.
     *            Options: ResponseUtil::FULL_RESPONSE or ResponseUtil::ACTION_ONLY
     * @return \utils\ResponseUtil current instance of object; allows chaining
     */
    public function SendError($message, $fullResponse = self::ACTION_ONLY) {
        if ($fullResponse) {
            $this->StartResponse();
        }
        switch ($this->format) {
            case 'xml':
                $message = htmlentities($message);
                echo <<<XML
<status>error</status>
<error><![CDATA[$message]]></error>    
XML;
                break;
            case 'json':
            default:
                echo ",\"status\":\"error\"";
                echo ",\"message\":\"$message\"";
        }

        if ($fullResponse) {
            $this->EndResponse();
            return $this;
        }
        return $this;
    }

    /**
     * If sending back results, begin the results section of the response.
     *
     *
     * @param boolean $asObj
     *            (default ResponseUtil::AS_ARRAY) only relevant when format
     *            is json indicates whether the results parameter in the output should have
     *            a value defined as an object {} or as an array []. Options are
     *            ResponseUtil::AS_ARRAY or ResponseUtil::AS_OBJ;
     *            
     * @see \model\ResponseUtil::WriteResults()
     * @see \model\ResponseUtil::EndResults();
     * @return \utils\ResponseUtil current instance of object; allows chaining
     */
    public function StartResults($asObj = self::AS_ARRAY) {
        if ($this->resultsStarted) {
            return $this;
        }
        $this->resultsAsObj = $asObj;

        switch ($this->format) {
            case 'xml':
                echo "<results>";
                break;
            default:
            case 'json':
                if ($asObj === self::AS_ANY) {
                    echo ",\"results\":";
                } elseif ($asObj === true) {
                    echo ",\"results\":{";
                } else {
                    echo ",\"results\":[";
                }
                break;
        }

        return $this;
    }

    /**
     * Adds a key (property) to the result object (if JSON).
     * If format is xml the key will
     * be an attribute on the <result> element.
     *
     * @param string $key
     *            The property/attribute name to use.
     * @param mixed $value
     *            value to set for key. If an array or object
     *            WriteKeyVal will be called recursively using the properties of the object
     *            or keys of the array.
     * @return \utils\ResponseUtil current instance of object; allows chaining
     */
    public function WriteKeyVal($key, $value, $isFirst = false, $asArray = false) {
        if ($key === 'the_geom') {
            if (!$this->includeGeom) {
                return $this;
            }
        }
        if ($this->responseEnded)
            return $this;
        switch ($this->format) {
            case 'xml':
                if (is_a($value, "ADORecordSet_assoc_postgres9")) {
                    $this->StartResults(true);
                    $this->WriteResults($value, self::ACTION_ONLY, self::AS_OBJ);
                    $this->EndResults(true);
                } elseif (is_array($value)) {
                    $keys = array_keys($value);
                    echo "<$key>";
                    if (is_string($keys[0])) {
                        foreach ($value as $k => $v) {
                            self::WriteKeyVal("item index=\"$k\"", $v);
                        }
                    } else {
                        $i = 0;
                        foreach ($value as $k => $v) {
                            self::WriteKeyVal("item index=\"$k\"", $v);
                        }
                    }
                    $key = explode(" ", $key);
                    $key = array_shift($key);
                    echo "</$key>";
                } else {

                    echo ("<$key>$value");
                    $key = explode(" ", $key);
                    $key = array_shift($key);
                    echo ("</$key>");
                }
                // $value = is_array($value) ? json_encode($value) : htmlentities($value);

                break;
            case 'json':
            default:
                echo $isFirst ? '' : ',';
                if ($asArray === false) {
                    echo "\"$key\":";
                }
                if (is_a($value, "ADORecordSet_assoc_postgres9")) {

                    echo '[';
                    $isFirst = true;
                    foreach ($value as $result) {
                        $this->WriteResult($result, $isFirst);
                        $isFirst = false;
                    }
                    echo ']';
                } elseif (is_array($value)) {
                    $numericKeys = 0;
                    foreach (array_keys($value) as $key) {
                        if (is_numeric($key)) {
                            $numericKeys += 1;
                        }
                    }

                    $allNumeric = ($numericKeys == count(array_keys($value)));

                    echo $allNumeric ? '[' : '{';

                    $f = true;
                    foreach ($value as $k => $v) {
                        $this->WriteKeyVal($k, $v, $f, $allNumeric);
                        $f = false;
                    }
                    echo $allNumeric ? ']' : '}';
                } else {
                    echo json_encode($value);
                }
                break;
        }
        return $this;
    }

    /**
     *
     * Write a single result entry.
     *
     * If the intent is to write multiple results, then it is important that for
     * the first item to indicate that it is first by passing true as the second
     * parameter
     *
     * Note: isFirst is now optional, and will be autodetected if isFirst is not
     * set based on the $this->resultCount counter which will increment with
     * each write result.
     *
     * @param mixed $result
     *            result record or oobjet to outputas a result item.
     * @param boolean $isFirst
     *            (optional) is this result the first result? If format is json and true prevents extra syntax in output.
     * @param mixed $resultId
     *            (default null) more for xml will set a resultId on result element. If json will create a propry named based on id and set the result as the value for that propery.
     * @return \utils\ResponseUtil current instance of object; allows chaining
     */
    public function WriteResult($result, $isFirst = null, $resultId = null) {
        if (is_a($result, '\model\records\CachedRecord')) {
            $result = $result->GetRecord();
        }
        if ($this->resultsEnded)
            return $this;
        if ($this->resultCount === 0) {
            $isFirst = true;
        }

        switch ($this->format) {
            case 'xml':
                $id = is_null($resultId) ? "" : $resultId;
                // var_dump((preg_match('/[^0-9]/',$id)==1) ? 'true' : 'false');
                $id = (preg_match('/[^0-9]/', $id) == 1) ? "name=\"$resultId\"" : "index=\"$resultId\"";

                echo "<result $id >";

                if (!is_array($result) && !is_object($result)) {
                    $result = array(
                        $result
                    );
                }
                foreach ($result as $key => $val) {

                    $name = (preg_match('/[^0-9]/', $key) == 1) ? "name=\"$key\"" : "index=\"$key\"";

                    if (is_array($val) || is_object($val)) {
                        echo "<item $name type=\"list\">";
                        $this->WriteResult($val, self::NOT_FIRST);
                        echo "</item>";
                    } else {
                        $atts = "";
                        $isInt = is_int($val);
                        $isFloat = is_float($val);
                        $isDouble = is_double($val);
                        $isBool = is_bool($val);
                        if ($isInt) {
                            $atts .= " type=\"int\" ";
                        } elseif ($isFloat) {
                            $atts .= " type=\"float\" ";
                        } elseif ($isDouble) {
                            $atts .= " type=\"double\" ";
                        } elseif ($isBool) {
                            $atts .= " type=\"bool\" ";
                        } else {
                            $atts .= "type=\"string\" ";
                            $val = htmlentities(trim($val));
                            $val = "<![CDATA[" . $this->ForceUTF($val) . "]]>";
                        }
                        echo "<item $name $atts>$val</item>";
                    }
                }
                echo "</result>";
                break;
            case 'json':
            default:
                if (!$isFirst) {
                    echo ",";
                }
                if ($resultId) {
                    if (!is_numeric($resultId)) {
                        echo "\"$resultId\"" . ':';
                    }
                }
                echo '{';
                $myIsFirst = true;
                foreach ($result as $key => $val) {

                    $this->WriteKeyVal($key, $val, $myIsFirst);
                    $myIsFirst = false;
                }

                echo '}';
                /* $item = $result;
                  if (is_array($result) || is_object($result)) {
                  echo json_encode($item);
                  } else {
                  if (is_string($result))
                  $result = json_encode($result);
                  echo $result;
                  } */

                break;
        }
        $this->resultCount += 1;
        return $this;
    }

    /**
     * Given the results from a db query, loop through the and write the
     * results from the database.
     *
     * @param
     *            ADOResultSet Results retrieved from an ADODB Execute (i.e. $db->Execute(...) or simlar call to the database.
     * @param boolean $fullResponse
     *            (default ResponseUtil::ACTION_ONLY)
     *            If set to ResponseUtil::FULL_RESPONSE a full response with resultset
     *            will be written.
     *            Options: ResponseUtil::ACTION_ONLY or ResponseUtil::FULL_RESPONSE
     *            
     * @return \utils\ResponseUtil current instance of object; allows chaining
     */
    public function WriteADOResults($resultCursor, $fullResponse = self::ACTION_ONLY) {
        if ($this->resultsEnded)
            ;
        if ($fullResponse) {
            $this->StartResponse();
        }
        $hasResults = !in_array($resultCursor, array(
                    false,
                    null
        ));

        if (!$hasResults) {
            $this->EndResults();
            if ($fullResponse) {
                $this->EndResponse();
            }
            return $this;
        }
        $isFirst = true;
        $this->StartResults();
        foreach ($resultCursor as $result) {
            $this->WriteResult($result, $isFirst);
            $isFirst = false;
        }
        $this->EndResults();
        if ($fullResponse) {
            $this->WriteOkStatus();
            $this->EndResponse();
        }
        return $this;
    }

    public function WriteProjectLayer(\ProjectLayer $projectLayer, $key = "projectLayer", \ProjectLayerFormatter $formatter, $isFirst = false, $format = 'json') {
        switch ($format) {
            case 'json':
                if (!$isFirst) {
                    echo ',';
                }
                echo "\"$key\":";
                $formatter->WriteJSON($projectLayer);
                break;
            case 'xml':
                echo "<$key>";
                $formatter->WriteXML($projectLayer);
                echo "</$key>";
        }
    }

    /**
     * Given an iterator (array or object or anything that works with foreach
     * like and ADORecordSet) loop through results and write them out as result
     * entries.
     *
     * @param \Iterator $results
     *            an array, object, or ADORecordset holding a set of records.
     * @param boolean $fullResponse
     *            (default ResponseUtil::ACTION_ONLY) If set to ResponseUtil::FULL_RESPONSE a full response with resultset will be written.
     * @return \utils\ResponseUtil current instance of object; allows chaining
     */
    public function WriteResults($results, $fullResponse = false, $asObj = self::AS_ARRAY) {
        if (!is_a($results, 'Iterator')) {
            if (is_a($results, '\model\records\CachedRecord')) {
                $results = array(
                    $results
                );
            }
        }

        if ($this->responseEnded)
            return $this;
        if ($fullResponse) {
            $this->StartResponse();
        }
        if ($fullResponse) {
            $this->StartResults($asObj);
        }
        if ($results === false) {
            $results = array();
        }
        $isFirst = true;

        foreach ($results as $key => $result) {
            $this->WriteResult($result, $isFirst, $key);
            $isFirst = false;
        }

        if ($fullResponse) {
            $this->EndResults();
            $this->WriteOkStatus();
            $this->EndResponse();
        }
        return $this;
    }

    /**
     * Adds a status element in xml or a property to the response json
     * named "status" with a value of "ok".
     *
     * An optional message may be provided which will provide a status_message
     * property to go with the message.
     *
     * @param string $message
     *            (optional) if provided a
     * @return \utils\ResponseUtil current instance of object; allows chaining
     */
    public function WriteOkStatus($message = null, $fullResponse = self::ACTION_ONLY) {
        if ($fullResponse) {
            $this->StartResponse();
        }

        $this->WriteKeyVal('status', 'ok');
        if (!is_null($message)) {
            $this->WriteKeyVal('status_message', $message);
        }
        if ($fullResponse) {
            $this->EndResponse();
        }

        return $this;
    }

    /**
     * Send a full response, optionally with parameters and a status message to
     * include with status=ok.
     *
     * @param string $statusMessage
     *            (optional default ResponseUtil::NO_VALUE, value to use as status message in addition to status=ok)
     * @param type $optParams
     *            (optional default ResponseUtil::NO_VALUE), non-results info as assoc array or object to include with the response.
     * @return \utils\ResponseUtil
     */
    public function WriteSimpleResponse($optParams = self::NO_VALUE, $statusMessage = self::NO_VALUE, $includeResponseKey = true) {
        if (!is_null($optParams)) {
            $this->SetProps($optParams);
        }
        $this->StartResponse($includeResponseKey)->EndResponse($statusMessage, $includeResponseKey);

        return $this;
    }

    /**
     * Write out the end to the results element/array.
     *
     * @param boolean $asObj
     *            (optional default ResponseUtil::AS_ARRAY)
     *            relevant only for json, indicates how to end the results.
     *            Options: ResponseUtil::AS_ARRAY or ResponseUtil::AS_OBJ
     * @return \utils\ResponseUtil current instance of object; allows chaining
     */
    public function EndResults($asObj = null) {
        if ($this->resultsEnded)
            return $this;
        if (is_null($asObj)) {
            $asObj = $this->resultsAsObj;
        }
        switch ($this->format) {
            case 'xml':
                echo "</results>";
                break;

            case 'json':
            default:
                if ($asObj === self::AS_ANY) {
                    return '';
                }
                echo $asObj ? "}" : "]";
        }
        $this->resultsEnded = true;
        return $this;
    }

    /**
     * Writes the end of the response element/property.
     *
     * @param boolean $statusOk
     *            (optional) if set to ResponseUtil::STATUS_OK
     *            will include an ok status element/property before closing the response.
     * @return \utils\ResponseUtil current instance of object; allows chaining
     */
    public function EndResponse($statusOk = false, $includeResponseKey = true) {

        if ($this->responseEnded)
            return $this;
        if ($statusOk !== false) {
            $this->WriteOkStatus($statusOk);
        }

        switch ($this->format) {
            case self::FORMAT_XML:
                echo '</response>';
                break;
            case self::FORMAT_JSON:
            default:
                echo "}";
                if ($includeResponseKey) {
                    echo "}";
                }
                break;
        }
        $this->responseEnded = true;
        return $this;
    }

    public function BeginBody($propName = '') {
        switch ($this->format) {
            case 'xml':
                echo '>';
                if ($propName !== '') {
                    echo "<$propName>";
                }
                break;
            case 'json':
            default:
                if ($propName != '') {
                    echo ',"' . $propName . '":';
                }
                break;
        }
    }

    public function EndBody($propName = '') {
        switch ($this->format) {
            case 'xml':
                if ($propName !== '') {
                    echo "<\/$propName>";
                }
                break;
            case 'json':
            default:
                break;
        }
    }

    /**
     * Calls the die function and will prevent any additional php execution.
     */
    public function End() {
        die();
    }

    public function ForceUTF($text) {
        $regex = <<<'END'
/
  (
    (?: [\x00-\x7F]                 # single-byte sequences   0xxxxxxx
    |   [\xC0-\xDF][\x80-\xBF]      # double-byte sequences   110xxxxx 10xxxxxx
    |   [\xE0-\xEF][\x80-\xBF]{2}   # triple-byte sequences   1110xxxx 10xxxxxx * 2
    |   [\xF0-\xF7][\x80-\xBF]{3}   # quadruple-byte sequence 11110xxx 10xxxxxx * 3
    ){1,100}                        # ...one or more times
  )
| .                                 # anything else
/x
END;
        return preg_replace($regex, '$1', $text);
    }

    public function StartList($name=null,$isFirst=false) {
        if($isFirst === false) {
            echo ',';
        }
        switch ($this->format) {
            case 'xml':
                if(!is_null($name)) {
                    echo "<list name=\"$name\">";
                } else {
                    echo "<list>";
                }
                break;
            case 'json':
                if(!is_null($name)) {
                    echo "\"$name\":[";   
                } else {
                    echo '[';
                }
        }
        return $this;
    }

    public function StartItem($i=null,$name=null,$isFirst=false) {
        $sep ='';
        if(!$isFirst && ($i>0 || is_null($i))) {
            $sep = ',';
        }
        switch($this->format) {
            case 'xml':
                $nameAtt = is_null($name)  ? '' : ' name="'.$name.'"';
                $index = is_null($i) ? '' : "index=\"$i\"";
                echo "<item $index$nameAtt>";
                break;
            case 'json':
                if(is_null($name)) {
                    echo $sep.'{';
                } else {
                    echo $sep."\"$name\":{";
                }
                break;
        }
        return $this;
    }

    public function EndItem() {
        switch($this->format) {
            case 'xml':
                echo "</item>";
                break;
            case 'json':
                echo '}';
                break;
        }
        return $this;
    }

    public function EndList() {
        switch ($this->format) {
            case 'xml':
                echo "</list>";
                break;
            case 'json':
                echo ']';                
                break;
        }
    }

}

?>