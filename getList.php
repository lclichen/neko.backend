<?php
include_once("./common.php");
header("content-type:text/html;charset=utf-8");
include_once(__DIR__."/common.php");
$data = initPostData();
$id = (int)$data['id'];
$token = $data['token'];
$con = pdo_database();