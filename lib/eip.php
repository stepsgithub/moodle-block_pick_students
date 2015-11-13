<?php
// Ensure library/ is on include_path
set_include_path(implode(PATH_SEPARATOR, array(
    realpath('lib'),
    get_include_path(),
)));
require_once 'Zend/Http/Client.php';

function schlist()
{
        $client = new Zend_Http_Client('[SCHOOL_LIST_URL]');

        try {
            $response = $client->request();
        } catch (Exception $e) {
            return false;
        }
        $status = $response->getStatus();
        $body = $response->getBody();
        if ($status == 200 || ($status == 400 && !empty($body))) {
                $bodyArray = explode("\n", $body);
                        $newArray = array();
                        while(list($key, $val) = each($bodyArray)) {
                                $dataArray = array('sid' => '', 'sname' => '');
                                @list($dataArray['sid'], $dataArray['sname']) = explode(",", $val);
                                if (!empty($dataArray['sid']))
                                        $newArray[trim($dataArray['sid'])] = trim($dataArray['sname']);
                        }
            return $newArray;
        }else{
            return false;
        }
}

function banlist($sid)
{
        $client = new Zend_Http_Client('[BAN_LIST_URL]?sshcode=' . $sid);

        try {
            $response = $client->request();
        } catch (Exception $e) {
            return false;
        }
        $status = $response->getStatus();
        $body = $response->getBody();
        if ($status == 200 || ($status == 400 && !empty($body))) {
                $bodyArray = explode("\n", $body);
                        $newArray = array();
                        while(list($key, $val) = each($bodyArray)) {
                                $dataArray = array('bid' => '');
                                list($dataArray['bid']) = explode(",", $val);
                                if (!empty($dataArray['bid']))
                                        $newArray[trim($dataArray['bid'])] = trim($dataArray['bid']);
                        }
            return renderoptions($newArray);
        }else{
            return false;
        }
}

function askban($sid, $ban)
{
        $client = new Zend_Http_Client('[BAN_STUDENT_URL]?sshcode=' . $sid . '&ban=' . $ban);

        try {
            $response = $client->request();
        } catch (Exception $e) {
            return false;
        }
        $status = $response->getStatus();
        $body = $response->getBody();
        if ($status == 200 || ($status == 400 && !empty($body))) {
                $bodyArray = explode("\n", $body);
                        $newArray = array();
                        while(list($key, $val) = each($bodyArray)) {
                                $dataArray = array('stname' => '', 'stusername' => '');
                                @list($dataArray['stname'], $dataArray['stusername']) = explode(",", $val);
                                $dataArray_stname = trim($dataArray['stname']);
                                $dataArray_stusername = trim($dataArray['stusername']);
                                if (!empty($dataArray_stusername))
                                        $newArray[] = array('operator' => 2, 'value' => $dataArray_stusername, 'stname' => $dataArray_stname);
                        }

            global $SESSION;
            $SESSION->user_filtering = array();
            if (empty($newArray))
                $SESSION->user_filtering['username'] = array('operator' => 2, 'value' => 'no_student');
            else
                $SESSION->user_filtering['username'] = $newArray;
            //$SESSION->user_filtering['username'][] = array('operator' => 2, 'value' => 'snic');

            return $newArray;
        }else{
            return false;
        }
}

function asksch($sid)
{
        $client = new Zend_Http_Client('[TEACHER_LIST_URL]?sshcode=' . $sid);

        try {
            $response = $client->request();
        } catch (Exception $e) {
            return false;
        }
        $status = $response->getStatus();
        $body = $response->getBody();
        if ($status == 200 || ($status == 400 && !empty($body))) {
                $bodyArray = explode("\n", $body);
                        $newArray = array();
                        while(list($key, $val) = each($bodyArray)) {
                                $dataArray = array('sid' => '', 'sname' => '', 'tname' => '', 'tusername' => '');
                                list($dataArray['sid'], $dataArray['sname'], $dataArray['tname'], $dataArray['tusername']) = explode(",", $val);
                                if (!empty($dataArray['tusername']))
                                        $newArray[trim($dataArray['tusername'])] = trim($dataArray['tname']);
                        }
            return renderoptions($newArray);
        }else{
            return false;
        }
}

function askth($tusername, $sid)
{
        $client = new Zend_Http_Client('[STUDENT_LIST_URL]?anony=' . $tusername);

        try {
            $response = $client->request();
        } catch (Exception $e) {
            return false;
        }
        $status = $response->getStatus();
        $body = $response->getBody();
        if ($status == 200 || ($status == 400 && !empty($body))) {
                $bodyArray = explode("\n", $body);
                        $newArray = array();
                        while(list($key, $val) = each($bodyArray)) {
                                $dataArray = array('no' => '', 'tname' => '', 'cname' => '', 'sid' => '');
                                @list($dataArray['no'], $dataArray['tname'], $dataArray['ban'], $dataArray['sid']) = explode(",", $val);
                                $dataArray_sid = trim($dataArray['sid']);
                                if (!empty($dataArray['ban']) && $dataArray_sid == $sid)
                                        $newArray[trim($dataArray['ban'])] = trim($dataArray['ban']);
                        }
            return renderoptions($newArray);
        }else{
            return false;
        }
}

function renderoptions($data_array)
{
    $options = '';
    foreach ($data_array as $key => $value) {
        $options .= '<option value="' . $key . '">' . $value  . '</option>';
    }
    return $options;
}
