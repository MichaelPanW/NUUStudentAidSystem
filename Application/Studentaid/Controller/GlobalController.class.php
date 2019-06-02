<?php
	namespace Studentaid\Controller;
	use Think\Controller;
	class GlobalController extends Controller
	{
		function _initialize() 
		{

if ($_SERVER["HTTPS"] <> "on")
{
    $xredir="https://".$_SERVER["SERVER_NAME"].$_SERVER["REQUEST_URI"];
    header("Location: ".$xredir);
}
			$config=D('config')->select();
			$this->assign('con',$config[0]);
		}
		
		
	}
	
?>