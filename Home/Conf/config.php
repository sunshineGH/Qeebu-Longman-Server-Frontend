<?php
$arr1=array(
	//'配置项'=>'配置'
	'APP_GROUP_LIST' => 'Admin,Company,Public', //项目分组设定
	'DEFAULT_GROUP'  => 'Company', //默认分组
	'TMPL_FILE_DEPR'=>'_',//tpl模板分割符号
	'URL_MODE'=>2,
	'SQLITE_TASK_PATH'=>'Public/Uploads/sqlite/init/task.db',
	'PHPMQTTCLIENT' => './Home/Extend/Vendor/PhpMQTTClient/SAM/php_sam.php',
	'PHPALIPAYCLIENT' => './Home/Extend/Vendor/AliPay/',
);
include_once './config.php';
return array_merge($arr1,$arr2);
?>
