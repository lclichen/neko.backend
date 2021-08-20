<?php
include_once("./config.php");

function initPostData(){
    $data = array();
    if(!empty($_GET)){
        $data = array_merge($data,$_GET);
        //print_r("GET-".$data);
        return $data;
    }
    if(!empty($_POST) && $_SERVER["CONTENT_TYPE"]!='application/json'){
        $data = array_merge($data,$_POST);
        //print_r("POST-".$_SERVER["CONTENT_TYPE"].$data);
        return $data;
    }
    $content = file_get_contents('php://input');
    //print_r($content);
    $data = array_merge($data,json_decode($content, true));
    if(empty($data)){
        die("Empty!");
    }
    return $data;
}

function test(){
    echo $GLOBALS['dbhost'];
    echo $GLOBALS['dbname'];
    die("NEKOMIMI");
}