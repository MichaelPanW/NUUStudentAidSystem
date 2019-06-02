<?php
	
	/**
		* 
		* Public(公共)
		*
		* @package      	photonicCMS
	*/
  	namespace Admin\Controller;
	use Think\Controller;
	class PublicController extends GlobalController
	{
		private $adminId;
		private $roleId;
		
		function _initialize()
		{
			$this->adminId = Session('adminId');
			$this->roleId = Session('roleId');
		}
		
		/**
			* 登录页
			*
		*/
		public function login()
		{
			$debug =  intval($_GET['debug']);
			$jumpUri = safe_b64decode($_GET['jumpUri']);
			$this->assign('jumpUri', $jumpUri);
			$company_name=D('Config')->select()[0]['programe_name'];
			$this->assign("company_name",$company_name);
			$this->display();
		}
		
		/**
			* 提交登录
			*
		*/
		public function doLogin()
		{
			
			$username = trim($_POST['username']);
			$password = trim($_POST['password']);
			$email = trim($_POST['email']);
			$condition = array();
			$dao = D('Admin');
			//dump($username);exit;
			if(empty($username) || empty($password)){
				$this->error('請輸入帳號及密碼');
			}
			
			$condition = array();
			$dao = D('Admin');
			$condition["username"] = $username;
			$condition["status"] = 1;
			$record = $dao->where($condition)->find();
			//dump($record);exit;
			if(false == $record){
			
				$this->error('無此帳號, 重複錯誤多次將會導致您的 IP 會暫時無法登入...');
				}else{
				if ($record['password'] != md5($password)){
					//echo "passwordFalse";exit();
					//self::_message('error', '密碼錯誤', U('Public/login'));
				$this->error('無此帳號, 重複錯誤多次將會導致您的 IP 會暫時無法登入...');
				}
				if (!Preg_match("/^[1-5]$/",$record['role_id'])) {
					//echo "roleFalse";exit();
					//self::_message('error', '此用戶已被停權', U('Public/login'));
				$this->error('無此帳號, 重複錯誤多次將會導致您的 IP 會暫時無法登入...');
				}
				
				$Config    =   M("Config");
				$where['id']=1;
				$list	=	$Config->where($where)->find();
				//var_dump( $list );exit;
				
				// 根據資料庫設定將使用者資料存於英文版或中文版 session
				if( $list['multilan'] == 0 ) {
					Session('en_userName', $record['username']);
					Session('en_adminId', $record['id']);
					Session('en_roleId', $record['role_id']);
					Session('en_adminAccess', C('ADMIN_ACCESS'));
				}
				Session('userName', $record['username']);
				Session('adminId', $record['id']);
				Session('roleId', $record['role_id']);
				Session('adminAccess', C('ADMIN_ACCESS'));
				Session('multilan', $list['multilan']);
				
				//保存登录信息
				//var_dump( Session('en_adminId') );exit;
				$time	=	time();
				$data = array();
				$data['id']	=	$record['id'];
				$data['last_login_time']	=	$time;
				$data['login_count']	=	array('exp','login_count+1');
				$dao->save($data);
				//echo "loginSuccess";exit();
				
				$this->assign('jumpUrl', U('Index/index'));
				$this->success('登錄成功');
			}
		}
		
		// 检查用户是否登录
		protected function checkUser() 
		{
			if(!isset($_SESSION['adminId'])){
				$this->assign('jumpUrl','Public/login');
				self::_message('error', '登出成功', U('Public/login'));
			}
		}
		
		// 顶部页面
		public function top() 
		{
			C('SHOW_RUN_TIME',false);			// 运行时间显示
			C('SHOW_PAGE_TRACE',false);
			$model	=	M("Group");
			$list	=	$model->where('status=1')->select();
			$allow = array();
			foreach( $list as $key => $val ){
				$role_array = explode( "|", $val['roleid'] );
				if( in_array( $_SESSION['roleId'], $role_array ) ){
					$allow[] = $val;
				}
			}
			$this->assign('nodeGroupList',$allow);
			$this->display();
		}
		
		// 尾部页面
		public function footer() 
		{
			C('SHOW_RUN_TIME',false);			// 运行时间显示
			C('SHOW_PAGE_TRACE',false);
			$this->display();
		}
		
		// 后台首页 查看系统信息
		public function main() 
		{
			$info = array(
            '操作系统'=>PHP_OS,
            '运行环境'=>$_SERVER["SERVER_SOFTWARE"],
            'PHP运行方式'=>php_sapi_name(),
            /*'ThinkPHP版本'=>THINK_VERSION.' [ <a href="http://thinkphp.cn" target="_blank">查看最新版本</a> ]',*/
            '上传附件限制'=>ini_get('upload_max_filesize'),
            '执行时间限制'=>ini_get('max_execution_time').'秒',
            '服务器时间'=>date("Y年n月j日 H:i:s"),
            '北京时间'=>gmdate("Y年n月j日 H:i:s",time()+8*3600),
            '服务器域名/IP'=>$_SERVER['SERVER_NAME'].' [ '.gethostbyname($_SERVER['SERVER_NAME']).' ]',
            '剩余空间'=>round((@disk_free_space(".")/(1024*1024)),2).'M',
            'register_globals'=>get_cfg_var("register_globals")=="1" ? "ON" : "OFF",
            'magic_quotes_gpc'=>(1===get_magic_quotes_gpc())?'YES':'NO',
            'magic_quotes_runtime'=>(1===get_magic_quotes_runtime())?'YES':'NO',
            );
			$this->assign('info',$info);
			$this->display();
		}
		
		public function profile() 
		{
			$this->checkUser();
			$User	 =	 M("Admin");
			$vo	=	$User->getById($_SESSION['adminId']);
			$this->assign('vo',$vo);
			$this->display();
		}
		
		// 修改資料
		public function change() 
		{
			$this->checkUser();
			$User	 =	 D("Admin");
			if(!$User->create()){
				$this->error($User->getError());
			}
			
			$result	=	$User->save();
			if(false !== $result){
				$this->assign( "jumpUrl", U('Index/index') );
				$this->success('資料修改成功！');
				}else{
				$this->error('資料修改失敗!');
			}
		}
		
		// 更換密碼
		public function changePwd()
		{
			$this->checkUser();
			//對表單提交處理進行處理或者增加非表單數據
			if(md5($_POST['verify']) != $_SESSION['verify']) {
				$this->error('驗證碼錯誤！');
			}
			$map	=	array();
			$map['password']= pwdHash($_POST['oldpassword']);
			if(isset($_POST['account'])){
				$map['account']	 =	 $_POST['account'];
				}elseif(isset($_SESSION[C('USER_AUTH_KEY')])) {
				$map['id']		=	$_SESSION[C('USER_AUTH_KEY')];
			}
			
			//檢查用戶
			$User    =   M("Admin");
			if(!$User->where($map)->field('id')->find()){
				$this->error('舊密碼不符或者用戶名錯誤！');
				}else{
				$User->password	=	pwdHash($_POST['password']);
				$User->password1	=	$_POST['password'];
				$User->save();
				$this->assign( "jumpUrl", U('Index/index') );
				$this->success('密碼修改成功！');
			}
		}
		
		/**
			* 验证码
			*
		*/
		public function verify()
		{
			import('ORG.Util.Image');
			if(isset($_REQUEST['adv'])){
				Image::showAdvVerify();
				}else{
				Image::buildImageVerify();
			}
		}
		
		/**
			* 输出信息
			*
			* @param unknown_type $type
			* @param unknown_type $content
			* @param unknown_type $jumpUrl
			* @param unknown_type $time
			* @param unknown_type $ajax
		*/
		public function _message($type = 'success', $content = '更新成功', $jumpUrl = __URL__, $time = 3, $ajax = false)
		{
			$jumpUrl = empty($jumpUrl) ? __URL__ : $jumpUrl ;
			if($type == 'success'){
				$this->assign('jumpUrl', $jumpUrl);
				$this->assign('waitSecond', $time);
				$this->success($content, $ajax);
				}elseif($type == 'error'){
				$this->assign('jumpUrl', $jumpUrl);
				$this->assign('waitSecond', $time);
				$this->error($content, $ajax);
				}elseif($type == 'redirect'){
				$this->redirect($jumpUrl);
			}
		}
		
		/**
			* 无权限操作显示页
			*
		*/
		public function accessFalse()
		{
			$this->display();
		}
		
		/**
			* 退出登录
			*
		*/
		public function logout()
		{
			$this->assign('jumpUrl', U('Public/login'));
			if(!empty($this->adminId)) {
				session(null);
				cookie('tempTheme', null);
				
				$this->success('登出成功');
				}else {
				$this->error('已經退出登錄');
			}
		}
	}
	
	
