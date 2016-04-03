<?php

class IMAction extends Action{

    public $token;
    //var_dump(changePwdToken('ali18626258389','123456'));
    //var_dump(delUserToken('ali18626258389'));
    //var_dump(registerToken('ali18626258389','123456'));
    //授权注册模式 POST /{org_name}/{app_name}/users
    function registerToken($nikename,$pwd){
        $formgettoken="https://a1.easemob.com/".C('IM_CONFIG')['org_name']."/".C('IM_CONFIG')['app_name']."/users";
        $body=array(
            "username"=>$nikename,
            "password"=>$pwd,
        );
        $patoken=json_encode($body);
        $header = array($this->_get_token());
        $res = $this->_curl_request($formgettoken,$patoken,$header);
        $arrayResult =  json_decode($res, true);
        return $arrayResult;
    }

//重置用户密码 PUT /{org_name}/{app_name}/users/{username}/password
    function changePwdToken($nikename,$newpwd){
        $formgettoken="https://a1.easemob.com/".C('IM_CONFIG')['org_name']."/".C('IM_CONFIG')['app_name']."/users/".$nikename."/password";
        $body=array(
            "newpassword"=>$newpwd,
        );
        $patoken=json_encode($body);
        $header = array($this->_get_token());
        $method = "PUT";
        $res = $this->_curl_request($formgettoken,$patoken,$header,$method);
        $arrayResult =  json_decode($res, true);
        return $arrayResult ;
    }

//删除 DELETE /{org_name}/{app_name}/users/{username}
    function delUserToken($nikename){
        $formgettoken="https://a1.easemob.com/".C('IM_CONFIG')['org_name']."/".C('IM_CONFIG')['app_name']."/users/".$nikename;
        $body=array();
        $patoken=json_encode($body);
        $header = array($this->_get_token());
        $method = "DELETE";
        $res = $this->_curl_request($formgettoken,$patoken,$header,$method);
        $arrayResult =  json_decode($res, true);
        return $arrayResult ;
    }

//先获取app管理员token POST /{org_name}/{app_name}/token
    function _get_token(){

        $formgettoken="https://a1.easemob.com/".C('IM_CONFIG')['org_name']."/".C('IM_CONFIG')['app_name']."/token";
        $body=array(
            "grant_type"=>"client_credentials",
            "client_id" =>C('IM_CONFIG')['client_id'],
            "client_secret"=>C('IM_CONFIG')['client_secret']
        );
        $patoken=json_encode($body);
        $res = $this->_curl_request($formgettoken,$patoken);
        $tokenResult = array();

        $tokenResult =  json_decode($res, true);
        //var_dump($tokenResult);
        return "Authorization: Bearer ". $tokenResult["access_token"];
    }

    function _curl_request($url, $body, $header = array(), $method = "POST"){
        array_push($header, 'Accept:application/json');
        array_push($header, 'Content-Type:application/json');
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 60);
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        //curl_setopt($ch, $method, 1);

        switch ($method){
            case "GET" :
                curl_setopt($ch, CURLOPT_HTTPGET, true);
                break;
            case "POST":
                curl_setopt($ch, CURLOPT_POST,true);
                break;
            case "PUT" :
                curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "PUT");
                break;
            case "DELETE":
                curl_setopt ($ch, CURLOPT_CUSTOMREQUEST, "DELETE");
                break;
        }

        curl_setopt($ch, CURLOPT_USERAGENT, 'SSTS Browser/1.0');
        curl_setopt($ch, CURLOPT_ENCODING, 'gzip');
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 1);
        if (isset($body{3}) > 0) {
            curl_setopt($ch, CURLOPT_POSTFIELDS, $body);
        }
        if (count($header) > 0) {
            curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
        }
        $ret = curl_exec($ch);
        $err = curl_error($ch);
        curl_close($ch);
        //clear_object($ch);
        //clear_object($body);
        //clear_object($header);
        if ($err) {
            return $err;
        }
        return $ret;
    }
}