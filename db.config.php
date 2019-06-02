<?php
/*** 
 * db.config.php
 * 資料庫資訊
 *
 * 資料庫的配置與基本的 ThinkPHP 配置
 ***/


return array(
/////// 資料庫 erp_qhand ///////
    // 資料庫類型
	'DB_TYPE' => 'mysql',
	// Database Server 位址
	'DB_HOST' => 'localhost',
	// 使用的資料庫名稱
	'DB_NAME' => 'studentaid',
	// 登入 SQL 的用戶帳號
	'DB_USER' => 'root',
	// 登入 SQL 的用戶密碼
	'DB_PWD' => '',
	// 登入資料庫使用的 port
	'DB_PORT' => '3306',
	// 資料庫的名稱前綴
	'DB_PREFIX' => '',

'TMPL_ACTION_SUCCESS'=>'Public:dispatch_jump',
'TMPL_ACTION_ERROR'=>'Public:dispatch_jump',
);

?>
