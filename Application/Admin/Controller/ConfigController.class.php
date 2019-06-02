<?php
	
	/**
		* 
		* Config(系統配置)
		*
		* @package      	photonicCMS
	*/
 	namespace Admin\Controller;
	use Think\Controller;
	class ConfigController extends GlobalController
	{
		private $dbconfig,$dao;
		
		function _initialize()
		{
		
			parent::_initialize();
			$this->dao = D('Config');
		}
		
		/**
			* 系統配置
			*
		*/
		public function index()
		{
			// 網站瀏覽人數統計
			$totalCome = M('Admin')->field('login_count')->where('id=1')->select();
			$this->assign('totalCome', $totalCome[0]['login_count']);
			$record = $this->dao->where('id=1')->find();
			$this->assign('vo', $record);
			$this->assign('time', time());
			$this->display();
		}
		
		/**
			* 提交編輯
			*
		*/
		public function doModify()
		{
			define('APP_STATUS','home');
			//parent::_checkPermission('Config_modify');
			//parent::_setMethod('post');
			//D('Article')->where('id=5')->data($_POST)->save();
			$id = '1';$_POST['id']=1;
			empty($id) && $this->error('記錄不存在');
			if($daoCreate = $this->dao->create()){
				//var_dump( $this->dao );exit;
				//upload($model='',$path = 1,$fileSize = 0,$thumbStatus = 1,$thumbSize = 0,$allowExts = 0,$attachFields = 'attach_file')
				$globalAttachSize = 20480000;
				$globalAttachSuffix = '';
				$dot = '/';
				$model = CONTROLLER_NAME;
				$setFolder = empty($model) ?'file/': $model .$dot ;
				$setUserPath = makeFolderName(1) ;
				$finalPath = 'Uploads'.C('__UPLOAD__').$dot.$setFolder.$setUserPath;
				if(!is_dir($finalPath)){
					@mkdir($finalPath);
				}
				//var_dump( $_FILES );EXIT;
				$isupload = false;
				foreach( $_FILES as $val ){
					if( isset( $val['name'] ) && $val['name'] != "" ){
						$isupload = true; break;
					}
				}
				/*
				if( $isupload ){
				
					$upload = new \Org\Net\UploadFile();
					$allowExts = "jpg,gif,png";
					$upload->maxSize = empty($fileSize) ?$globalAttachSize : intval($fileSize) ;
					$upload->allowExts = empty($allowExts) ?explode(',',$globalAttachSuffix) : explode(',',$allowExts) ;
					$upload->savePath = $finalPath;
					$upload->saveRule = 'uniqid';
					$upload->thumb = false;
					if(!$upload->upload()){
						echo ($upload->getErrorMsg());
						}else{
						$i = 0;
						$uploadList = $upload->getUploadFileInfo();
						foreach( $uploadList as $val){
							$array_map = array( "company_logoimg" => "company_logo", 
							"company_picimg" => "company_pic",
							"photonic_logoimg" => "photonic_logo" 
							);
							if( $val['key'] == "company_logoimg" ){
								$this->dao->company_logo = formatAttachPath($uploadList[$i]['savepath']) . $uploadList[$i]['savename'];
								}else if( $val['key'] == "company_picimg" ){
								$this->dao->company_pic = formatAttachPath($uploadList[$i]['savepath']) . $uploadList[$i]['savename'];
								}else if( $val['key'] == "photonic_logoimg" ){
								$this->dao->photonic_logo = formatAttachPath($uploadList[$i]['savepath']) . $uploadList[$i]['savename'];
							}
							unlink(UPLOAD_PATH.$_POST[$array_map[$val['key']] . '_old']);
							$i++;
						}
					}
				}*/
				$daoSave = $this->dao->save();
				if(false!== $daoSave){
					$record = $this->dao->where('id=1')->find();
					$configHeader = "<?php\n/** \n* cms.config.php\n*\n* @package      	photonicCMS\n* \n*/\n\nif (!defined('PHOTONICCMS')) exit();\n\nreturn array(\r\n";
					$configFooter .= ');';
					foreach((array)$record as $key => $value){
						//過濾無關POST key
						if(strtolower($value) == "true" || strtolower($value) == "false" || is_numeric($value)){
							$configBody .= "    '".$key."' => ".dadds($value).",\r\n";
							}else{
							$configBody .= "    '".$key."' => '".dadds($value)."',\r\n";
						}
					}
					$configData = $configHeader . $configBody . $configFooter;
					putContent($configData, 'cms.config.php', '..');
					
					$this->success('更新成功');
					}else{
					$this->error('更新失敗');
				}
				}else{
				$this->error($this->dao->getError());
			}
		}
		
		/**
			* Email配置
			*
		*/
		public function email()
		{
			$record = $this->dao->field('id,email')->where('id=1')->find();
			$this->assign('vo', $record);
			$this->display();
		}
		
		/**
			* 清除图片或文件
			*
		*/
		public function clearFile()
		{
			parent::_checkPermission();
			parent::_setMethod('get');
			$item = $_GET['id'];
			$Model = new Model();
			$temp = "";
			if( $item=="a" ){
				$temp = "company_logo=''";
			}
			if( $item=="b" ){
				$temp = "company_pic=''";
			}
			$query =  "update config set " . $temp . " where id=1";
			$result = $Model->query( $query );	
			if(false !== $result){
				parent::_message('success', '更新完成');
				}else{
				parent::_message('error', '更新失敗');
			}
		}
		
		/**
			* 提交內核配置
			*
		*/
		public function doCore()
		{
			parent::_setMethod('post');
			parent::_checkPermission('Config_coreModify');
			$config = $this->dbconfig;
			$configHeader = "<?php\n/** \n* db.config.php\n*\n* @package      	photonicCMS\n* /\n\nif (!defined('PHOTONICCMS')) exit();\n\nreturn array(\r\n";
			$configFooter .= ');';
			$config['APP_DEBUG'] = trim($_POST['APP_DEBUG']);
			$config['URL_ROUTER_ON'] = trim($_POST['URL_ROUTER_ON']);
			$config['URL_DISPATCH_ON'] = trim($_POST['URL_DISPATCH_ON']);
			$config['URL_MODEL'] = trim($_POST['URL_MODEL']);
			$config['URL_PATHINFO_DEPR'] = trim($_POST['URL_PATHINFO_DEPR']);
			$config['URL_HTML_SUFFIX'] = trim($_POST['URL_HTML_SUFFIX']);
			$config['TMPL_CACHE_ON'] = trim($_POST['TMPL_CACHE_ON']);
			$config['TMPL_CACHE_ON'] = trim($_POST['TMPL_CACHE_ON']);
			$config['TOKEN_NAME'] = trim($_POST['TOKEN_NAME']);
			$config['TMPL_CACHE_ON'] = trim($_POST['TMPL_CACHE_ON']);
			$config['TMPL_CACHE_TIME'] = trim($_POST['TMPL_CACHE_TIME']);
			foreach((array)$config as $key => $value)
			{
				if($value === true || $value == 'true'){
					$configBody .= "    '".$key."' => true,\r\n";
					}else if($value === false || $value == 'false'){
					$configBody .= "    '".$key."' => false,\r\n";
					} else if(is_numeric($value)){
					$configBody .= "    '".$key."' => $value,\r\n";
					}else{
					$configBody .= "    '".$key."' => '$value',\r\n";
				}
			}
			
			$configData = $configHeader . $configBody . $configFooter;
			putContent($configData, 'db.config.php', '.');
			parent::_sysLog('modify', "編輯內核配置");
			parent::_message('success', '內核更新成功');
		}
	}
	
