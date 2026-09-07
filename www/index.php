<?php
date_default_timezone_set ( "Asia/Shanghai" );
header ( 'Content-Type: text/html; charset=utf-8' );
define('XQ_DEVELOP',true);
$app=new xqkeji\App();
$app->run();

