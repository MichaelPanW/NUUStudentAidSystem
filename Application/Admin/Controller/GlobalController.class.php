<?php
	namespace Admin\Controller;
	use Think\Controller;
	class GlobalController extends Controller
	{
		private $roleId,$adminId,$adminAccess, $username;
		protected $upload = 'Uploads/',$sys_version = '1.0';
		public $configAll;
		
		function _initialize() {
		
		$where['id']=1;

		$Config    =   M("Config");
		$list	=	$Config->where($where)->select();
		$this->assign('company_logo', $list[0]['company_logo']);
		$this->assign('company_name', $list[0]['company_name']);
		
			$this->roleId = intval(Session('roleId'));
			$this->adminId = intval(Session('adminId'));
			$this->username = Session('userName');
			$this->adminAccess = Session('adminAccess');
			if(empty($this->adminId) ||empty($this->roleId) ||$this->adminAccess != C('ADMIN_ACCESS')) {
				redirect( U('Public/login',array('jumpUri'=>safe_b64encode($_SERVER['REQUEST_URI']))));
			}
			import("ORG.Util.Page");
			import("ORG.Util.longPage");
			//$module = D('Module')->Where('left_menu=1 and status=0')->Order('display_order DESC,id ASC')->select();
			//$data['leftBar'] = $module;
			$appHost = empty($_SERVER['SERVER_NAME']) ?$_SERVER['SERVER_NAME'] : $_SERVER['HTTP_HOST'];
			$frontUrl = 'http://'.$appHost .dirname($_SERVER['SCRIPT_NAME']);
			Load('extend');
			$data['moduleName'] = MODULE_NAME;
			$data['serverTime'] = time();
			$data['UPLOADS'] = $this->upload;
			$this->assign($data);
			$this->assign('appHost',$appHost);
			$this->assign('frontUrl',$frontUrl);
			$this->assign('roleId',$this->roleId);
			$this->assign('adminId',$this->adminId);
			$this->assign('username',$this->username);
			$this->assign('appname',CONTROLLER_NAME);
			
			//是否显示回函表
			if(isset($_SESSION['adminId'])) {
				$this->configAll  = array();
				if(isset($_SESSION['Config'.$_SESSION['adminId']])) {
					//如果已经缓存，直接读取缓存
					$this->configAll   =   $_SESSION['Config'.$_SESSION['adminId']];
					}else {
					//读取数据库模块列表生成菜单项
					$Config    =   M("Config");
					$where['id']=1;
					$list	=	$Config->where($where)->find();
					$this->configAll = $list;
					//缓存菜单访问
					$_SESSION['Config'.$_SESSION['adminId']]	=	$this->configAll;
				}
				// 
				$cpic = M('Config')->field('company_pic')->where("id=1")->select();
				if($cpic[0]['company_pic']){$this->configAll['company_pic'] = $cpic[0]['company_pic'];}
				$this->assign('configAll',$this->configAll);
			}
			
			// 各頁面可見權限鎖
			if( $this->roleId==3 ){
				$buf = M('Admin')->field('passes')->where('id='.$this->adminId)->select();
				//$this->assign('moduleLock', explode(",", $buf[0]['passes'], -1));
				$this->assign('moduleLock', $buf[0]['passes']);
			}
			
		}
		
		Protected function _getAdminUid() {
			return $this->adminId;
		}
		
		Protected function _getRoleId() {
			return $this->roleId;
		}
		
		Protected function _getAdminName() {
			return $this->username;
		}
		
		public function debug() {
			$this->display('Public:debug');
		}
		
		protected function _insert($model = false,$jumpModel ='later') {
			$module = empty($model) ?CONTROLLER_NAME : $model;
			$dao = D($module);
			if($daoCreate = $dao->create()) {
				$daoAdd = $dao->add();
				if(false !== $daoAdd) {
					self::_message('success','錄入成功');
					} else {
					self::_message('error','錄入失敗');
				}
				} else {
				self::_message('error',$dao->getError());
			}
		}
		
		function _modify($model=false, $jumpModel='later', $primary='id') {
			$id = intval($_POST['id']);
			empty($id) &&$this->error('error','記錄不存在');
			$module = empty($model) ?CONTROLLER_NAME : $model;
			$dao = D($module);
			$daoCreate = $dao->create();
			if($daoCreate) {
				$daoSave = $dao->save();
				//echo $dao->getLastSql();exit;
				if(false !== $daoSave) {
					if( strtolower( $module ) ==  "config" ) {
						$this->configAll  = array();			
						//读取数据库模块列表生成菜单项
						$Config    =   M("Config");
						$where['id']=1;
						$list	=	$Config->where($where)->find();
						$this->configAll = $list;
						//缓存菜单访问
						$_SESSION['Config'.$_SESSION['adminId']]	=	$this->configAll;
					}
					$this->success('更新成功');
					} else {
					$this->error('更新失敗1');
				}
				} else {
				$this->error($dao->getError());
			}
		}
		
		protected function _delete($model = 0,$jumpUri= 0,$param = 0,$field = 'id') 
		{
			if(getMethod() == 'get'){
				$operate = trim($_GET['operate']);
				$item = intval($_GET[$field]);
				}else if(getMethod() == 'post'){
				$operate = trim($_POST['operate']);
				$item = $_POST[$field];
			}
			if($item){
				if(empty($operate) ||$operate !='delete') //self::_message('error','操作類型錯誤');
				$this->error('操作類型錯誤');
				$jumpUri = empty($jumpUri) ?'' : $jumpUri ;
				//echo $jumpUri;
				$daoModel = empty($model)?CONTROLLER_NAME : $model;
				$items = is_array($item) ?implode(',',$item) : $item;
				
				if(empty($param)){
					$dao = D($daoModel);
					$daoResult = $dao->where($field.' IN('.$items.')')->delete();
					//echo $dao->GetLastSql();exit;
					if(false !== $daoResult){
						$this->success("刪除成功",$jumpUri);
						//$this->success("刪除成功");
						}else{
						//$this->_message('error',"刪除失敗",$jumpUri);
						$this->error("刪除失敗");
					}
					}else{
					self::_deleteWith($daoModel,$items,$param,$jumpUri,$field);
				}
				}else{
				//$this->_message('error',"未選擇要刪除的記錄",$jumpUri);
				$this->error("未選擇要刪除的記錄");
			}
		}
		
		
		protected function _deleteWith($model = 0,$items = 0,$param = 0,$jumpUri= 0,$field = 'id') 
		{
			$dao = D($model);
			$daoList = $dao->Where($field.' IN('.$items.')')->select();
			
			foreach ((array)$daoList as $row){
				foreach ((array)$param as $value) {
					if(!empty($row[$value])) {
						@unlink(UPLOAD_PATH.'/'.$row[$value]);
					}
				}
			}
			
			$daoResult = $dao->Where($field.' IN('.$items.')')->delete();
			if(false !== $daoResult) {
				//echo $jumpUri;exit;
				$this->success("刪除成功",$jumpUri);
				//$this->success("刪除成功");
				}else{
				$this->_message('error',"刪除失敗",$jumpUri);
				$this->error("刪除失敗");
			}
		}
		
		
		protected function _recommend($type = 'set',$model = 0,$jumpUri = '',$field = 'id') 
		{
			if(getMethod() == 'get'){
				$operate = trim($_GET['operate']);
				$item = intval($_GET[$field]);
				}elseif(getMethod() == 'post'){
				$operate = trim($_POST['operate']);
				$item = $_POST[$field];
			}
			
			if($item){
				if(empty($operate) ||!in_array($operate,array('recommend','unRecommend'))) self::_message('error','操作類型錯誤',$jumpUri);
				$daoModel = empty($model)?CONTROLLER_NAME : $model ;
				$items = is_array($item) ?implode(',',$item) : $item ;
				$dao = D($daoModel);
				$condition['recommend'] = $type == 'set'?1 : 0 ;
				$daoResult = $dao->Where($field.' IN('.$items.')')->save($condition);
				if(false !== $daoResult){
					$mOperate = $operate == 'recommend'?'推薦:': '取消推薦:';
					$this->success("更新成功",$jumpUri);
					}else{
					$this->_message('error',"更新失敗",$jumpUri);
				}
				}else{
				$this->_message('error',"未選擇要更新的記錄");
			}
		}
		
		
		protected function _setTop($type = 'set',$model = 0,$jumpUri= '',$field = 'id')
		{
			if(getMethod() == 'get'){
				$operate = trim($_GET['operate']);
				$item = intval($_GET[$field]);
				}elseif(getMethod() == 'post'){
				$operate = trim($_POST['operate']);
				$item = $_POST[$field];
			}
			
			if($item){
				if(empty($operate) ||!in_array($operate,array('setTop','unSetTop'))) self::_message('error','操作類型錯誤',$jumpUri);
				$daoModel = empty($model)?CONTROLLER_NAME : $model ;
				$items = is_array($item) ?implode(',',$item) : $item ;
				$dao = D($daoModel);
				$condition['istop'] = $type == 'set'?1 : 0 ;
				$daoResult = $dao->Where($field.' IN('.$items.')')->save($condition);
				
				if(false !== $daoResult){
					$mOperate = $operate == 'setTop'?'置頂:': '取消置頂:';
					$this->success("更新成功",$jumpUri);
					}else{
					$this->_message('error',"更新失敗",$jumpUri);
				}
				}else{
				$this->_message('error',"未選擇要更新的記錄",$jumpUri);
			}
		}
		
		protected function _setStatus($type = 'set',$model = 0,$jumpUri = '',$field = 'id')
		{
			//dump($_POST);exit;
			if(getMethod() == 'get'){
				$operate = trim($_GET['operate']);
				$item = intval($_GET[$field]);
				}elseif(getMethod() == 'post'){
				$operate = trim($_POST['operate']);
				$item = $_POST[$field];
			}
			
			if($item){
				if(empty($operate) ||!in_array($operate,array('setStatus','unSetStatus'))) //self::_message('error','操作類型錯誤',$jumpUri);
				$this->error('操作類型錯誤');
				$daoModel = empty($model)?CONTROLLER_NAME : $model ;
				$items = is_array($item) ?implode(',',$item) : $item ;
				$dao = D($daoModel);
				$condition['start_time'] = $type == 'set'?date("Y-m-d") : 0 ;
				$condition['end_time'] = $type == 'set'?date("Y-m-d") : 0 ;
				$condition['status'] = $type == 'set'?0 : 1 ;
				$daoResult = $dao->Where($field.' IN('.$items.')')->save($condition);
				//echo $dao->GetLastSql();exit;
				if(false !== $daoResult){
					$mOperate = $operate == 'setStatus'?'設置顯示:': '設置隱藏:';
					//$this->success("ID: {$items} 更新成功",$jumpUri);
					$this->success("更新成功");
					}else{
					//$this->_message('error',"更新失敗",$jumpUri);
					$this->error("更新失敗");
				}
				}else{
				//$this->_message('error',"未選擇要更新的記錄",$jumpUri);
				$this->error("未選擇要更新的記錄");
			}
		}
		
		/***
			*
			* 
			* 資料搬移
			*
		***/
		protected function _move($newCategoryId = 0,$model = 0,$jumpUri = '',$field = 'id')
		{
			if(getMethod() == 'get'){
				$operate = trim($_GET['operate']);
				$item = intval($_GET[$field]);
				}elseif(getMethod() == 'post'){
				$operate = trim($_POST['operate']);
				$item = $_POST[$field];
			}
			
			if($item){
				if(empty($operate) ||$operate != 'move') self::_message('error','操作類型錯誤',$jumpUri);
				empty($newCategoryId) &&self::_message('error','新類別獲取錯誤',$jumpUri);
				$daoModel = empty($model)?CONTROLLER_NAME : $model ;
				$items = is_array($item) ?implode(',',$item) : $item ;
				$dao = D($daoModel);
				$condition['category_id'] = $newCategoryId ;
				$daoResult = $dao->Where($field.' IN('.$items.')')->save($condition);
				if(false !== $daoResult){
					$this->success("更新成功",$jumpUri);
					}else{
					$this->_message('error',"更新失敗",$jumpUri);
				}
				}else{
				$this->_message('error',"未選擇要更新的記錄",$jumpUri);
			}
		}
		
		protected function _batchModify($model = 0,$dataList = 0,$fields = array(),$jumpUri ='',$cache = 0,$cacheOrder = '',$cacheWhere = '',$condition = 'UpdateId')
		{
			//dump($dataList);
			$count = count($dataList[$condition]);
			if($count>0){
				$fieldsMerge = array_merge($fields, array($condition)) ;
				$daoModel = empty($model) ?CONTROLLER_NAME : $model;
				$dao = D($daoModel);
				foreach($dataList[$condition] as $key=>$row){
					foreach($fieldsMerge as $item){
						if(isset($dataList[$item][$key])){
							$data[$key][$item] = $dataList[$item][$key];
						}
					}
				}
				//dump( $data );exit;
				
				$updateCount = 0;
				$getflag = get_magic_quotes_gpc();
				
				foreach($data as $key =>$value){
					if($getflag){
						foreach( $value as $k => $v ){
							$value[$k] = stripslashes($v);
						}
					}
					
					//dump($value);exit;
					$result = $dao->Where('id='.$data[$key][$condition])->save($value);
					//echo $dao->getLastSql() . "<br />";
					//dump($result);exit;
					
					if( $result === false ){
						self::_message('error',"更新失敗，請檢查數據的正確性",$jumpUri,10);
					}
					$updateCount++;
					$items[] = $data[$key][$condition];
				}
				
				$updateItems = implode(',',$items);
				
				!empty($cache) &&writeCache($cache,0,$cacheOrder,$cacheWhere);
				
				$this->success("更新成功");
				
				//self::_message('success',"更新 {$updateItems}，影響 {$updateCount} 條記錄",$jumpUri);
				}else{
				$this->error('數據獲取錯誤，可能是沒有記錄被選擇');
				//self::_message('error','數據獲取錯誤，可能是沒有記錄被選擇',$jumpUri);
			}
		}
		
		
		protected function _tags($method = 'insert',$tags = '',$titleId = 0,$model = 0,$jumpUri = '')
		{
			if(!empty($tags)){
				if($method == 'insert'){
					self::_tagsInsert($tags,$titleId,$model,$jumpUri);
					}elseif($method == 'modify'){
					self::_tagsModify($tags,$titleId,$model,$jumpUri);
				}
			}
		}
		
		
		protected function _tagsInsert($tags = '',$titleId = 0,$model = 0,$jumpUri)
		{
			$dao = D('tags');
			$model = empty($model) ?CONTROLLER_NAME : $model ;
			$tagsValue = str_replace(array(' ','，'),',',$tags);
			$explodeTags = array_unique(explode(',',$tagsValue));
			$tagCount = 0;
			foreach ((array)$explodeTags as $value) {
				$value = dHtml(trim($value));
				if(!empty($value)){
					$condition['tag_name'] = $value;
					$condition['module'] = $model;
					$getTags = $dao->where($condition)->find();
					if(empty($getTags)){
						$tagInsert['tag_name'] = $value;
						$tagInsert['module'] = $model;
						$tagInsert['total_count'] = 1;
						$tagInsert['tag_name'] = $value;
						$daoTagsAdd = $dao->add($tagInsert);
						if(false === $daoTagsAdd){
							self::_message('error',"內容寫入成功，tags:{$value} 寫入失敗",$jumpUri);
						}
						$getTags = false;
						}else{
						$data['id'] =  $getTags['id'];
						$dao->setInc('total_count',$data);
					}
					self::_tagsCache($value,$titleId,$model);
					$tagCount++;
					if($tagCount >4) {
						unset($explodeTags);
						break;
					}
				}
			}
		}
		
		
		protected function _tagsModify($tags = '',$titleId = 0,$model = 0,$jumpUri)
		{
			$daoTagCache = D('tagsCache');
			$daoTags = D('tags');
			$titleTagsArray = $daoTagCache->Where('title_id='.$titleId)->select();
			$tagsArray = array();
			foreach ((array)$titleTagsArray as $row){
				$tagsArray[] = $row['tag_name'];
			}
			$titleTags = implode(',',$tagsArray);
			$dao = D('tags');
			$model = empty($model) ?CONTROLLER_NAME : $model ;
			$tagsValue = str_replace(array(' ','，'),',',$tags);
			$explodeTags = array_unique(explode(',',$tagsValue));
			$tagCount = 0;
			foreach ((array)$explodeTags as $value) {
				$value = dHtml(trim($value));
				if(!empty($value)){
					$condition['tag_name'] = $value;
					$condition['module'] = $model;
					$TagsArrayNew[] = $value;
					if(!in_array($value,$tagsArray)) {
						$getTags = $dao->where($condition)->find();
						if(empty($getTags)){
							$tagInsert['tag_name'] = $value;
							$tagInsert['module'] = $model;
							$tagInsert['total_count'] = 1;
							$tagInsert['tag_name'] = $value;
							$daoTagsAdd = $dao->add($tagInsert);
							if(false === $daoTagsAdd){
								self::_message('error',"內容寫入成功，tags:{$value} 寫入失敗",$jumpUri);
							}
							$getTags = false;
							}else{
							$data['id'] =  $getTags['id'];
							$dao->setInc('total_count',$data);
						}
						self::_tagsCache($value,$titleId,$model);
					}
					$tagCount++;
					
					if($tagCount >4) {
						unset($explodeTags);
						break;
					}
				}
			}
			foreach ($tagsArray as $tagName){
				if(!in_array($tagName,$TagsArrayNew)){
					$getTagsCount = $daoTagCache->Where("title_id!={$titleId} and tag_name='{$tagName}'")->count();
					if($getTagsCount){
						$daoTags->setDec('total_count',"tag_name='{$tagName}'");
						}else{
						$daoTags->Where("tag_name='{$tagName}'")->delete();
					}
					$daoTagCache->Where("title_id={$titleId} and tag_name='{$tagName}'")->delete();
				}
			}
		}
		
		protected function _tagsCache($tags = '',$titleId = 0,$model = 0)
		{
			$tagsCache = D('tagsCache');
			$tagsCacheCondition['title_id'] = $titleId;
			$tagsCacheCondition['tag_name'] = $tags;
			$tagsCacheCondition['module'] = $model;
			$tagsCache->tag_name = $tags;
			$tagsCache->title_id = $titleId;
			$tagsCache->module = $model;
			$daoTagsCacheAdd = $tagsCache->add();
			if(false === $daoTagsCacheAdd){
				self::_message('error','內容寫入成功，tagCache寫入失敗');
			}
		}
		
		protected function _tagsDelete($module = NULL,$field = 'id')
		{
			if(getMethod() == 'get'){
				$operate = trim($_GET['operate']);
				$item = intval($_GET[$field]);
				}elseif(getMethod() == 'post'){
				$operate = trim($_POST['operate']);
				$item = $_POST[$field];
			}
			if(is_array($item)){
				foreach ($item as $tid){
					$condition['module'] = $module;
					$condition['title_id'] = $tid;
					$dao = D('TagsCache');
					$dao->where($condition)->delete();
				}
				}else{
				$condition['module'] = $module;
				$condition['title_id'] = $item;
				$dao = D('TagsCache');
				$dao->Where($condition)->delete();
			}
		}
		
		
		protected function _sysLog($action = '',$message = '',$uri = NULL)
		{
			
			
			$formatMessage = empty($message) ?'': " ({$message})";
			
			
			$getConfig = getContent('cms.config.php','../');
			
			
			$sysLog = $getConfig['sys_log'];
			
			
			$sysLogExt = $getConfig['sys_log_ext'];
			
			
			if(!empty($action) &&!empty($sysLog) &&in_array($action,explode(',',$sysLogExt))){
				
				
				$getUri =  empty($uri) ?formatQuery($_SERVER['REQUEST_URI']) : $uri ;
				
				
				$dao = D('AdminLog');
				
				
				$dao->user_id = intval($this->adminId);
				
				
				$dao->username = $this->username;
				
				
				$dao->action = $getUri .$formatMessage;
				
				
				$dao->ip = get_client_ip();
				
				
				$dao->create_time = time();
				
				
				$daoAdd = $dao->add();
				
				
				$lastSql = $dao->getLastSql();
				
				
				if(false === $daoAdd){
					
					
					self::_message('error',"日誌寫入失敗:<br />{$lastSql}",0,30);
					
					
				}
				
				
			}
			
			
		}
		
		
		protected function _setMethod($set = 'POST')
		
		
		{
			
			
			$getMethod = strtolower($_SERVER['REQUEST_METHOD']);
			
			
			if($getMethod != $set){
				
				
				self::_message('error',"當前只接受 {$set} 數據");
				
				
			}
			
			
		}
		
		
		protected function _message($type = 'success',$content = '更新成功',$jumpUrl = '',$time = 3,$ajax = false)
		{
			
			
			$jumpUrl = empty($jumpUrl) ?'' : $jumpUrl ;
			
			
			//echo $jumpUrl;exit;
			
			
			switch ($type){
				
				
				case 'success':
				
				
				$this->assign('jumpUrl',$jumpUrl);
				
				
				$this->assign('waitSecond',$time);
				
				
				$this->success($content,$ajax);
				
				
				break;
				
				
				case 'error':
				
				
				$this->assign('jumpUrl','javascript:history.back(-1);');
				
				
				$this->assign('waitSecond',$time);
				
				
				$this->assign('error',$content);
				
				
				$this->error($content,$ajax);
				
				
				break;
				
				
				case 'errorUri':
				
				
				$this->assign('jumpUrl',$jumpUrl);
				
				
				$this->assign('waitSecond',$time);
				
				
				$this->assign('error',$content);
				
				
				$this->error($content,$ajax);
				
				
				break;
				
				
				default:
				
				
				die('error type');
				
				
				break;
				
				
			}
			
			
		}
		
		
		protected function _checkPermission($action = NULL)
		
		
		{
			
			
			$formatAction = strtolower($action);
			
			
			if(empty($action)) $formatAction = strtolower(MODULE_NAME.'_'.ACTION_NAME);
			
			
			$permissionFile = CMS_DATA."/cache.adminRole.php";
			
			
			//var_dump( $permissionFile);exit;
			
			
			$permission = Session('permission');
			
			
			//var_dump( $permission );
			
			
			if($permission != 'all'){
				
				
				if(file_exists($permissionFile)){
					
					
					$getPermission = @require($permissionFile);
					
					
					foreach((array)$getPermission as $row){
						
						
						if($row['id'] == $this->roleId){
							
							
							$arrPermission = explode(',',strtolower($row['role_permission']).',index_index');
							
							
						}
						
						
					}
					
					
					}else{
					
					
					$roleDao = D('Role');
					
					
					$getPermission = $roleDao->Where("id={$this->roleId}")->find();
					
					
					$arrPermission = explode(',',strtolower($getPermission['role_permission']).',index_index');
					
					
					//var_dump( $getPermission );
					
					
					writeCache('Admin');
					
					
				}
				
				/*
				if(!in_array($formatAction,$arrPermission)){
					
					
					self::_message('error','當前角色組無權限進行此操作，請聯系管理員授權',0,20);
					
					
				}*/
				
				
			}
			
			
		}
		
		
		
		
		
		
		
		
		/**
			
			
			+----------------------------------------------------------
			
			
			* 取得操作成功後要返回的URL地址
			
			
			* 默認返回當前模塊的默認操作
			
			
			* 可以在action控制器中重載
			
			
			+----------------------------------------------------------
			
			
			* @access public
			
			
			+----------------------------------------------------------
			
			
			* @return string
			
			
			+----------------------------------------------------------
			
			
			* @throws ThinkExecption
			
			
			+----------------------------------------------------------
			
			
		*/
		
		
		function getReturnUrl() {
			
			
			return '' . '?' . C ( 'VAR_MODULE' ) . '=' . MODULE_NAME . '&' . C ( 'VAR_ACTION' ) . '=' . C ( 'DEFAULT_ACTION' );
			
			
		}
		
		
		/**
			
			
			+----------------------------------------------------------
			
			
			* 默認禁用操作
			
			
			*
			
			
			+----------------------------------------------------------
			
			
			* @access public
			
			
			+----------------------------------------------------------
			
			
			* @return string
			
			
			+----------------------------------------------------------
			
			
			* @throws FcsException
			
			
			+----------------------------------------------------------
			
			
		*/
		
		
		public function forbid() {
			
			
			$name=CONTROLLER_NAME;
			
			
			$model = D ($name);
			
			
			$pk = $model->getPk ();
			
			
			$id = $_REQUEST [$pk];
			
			
			$condition = array ($pk => array ('in', $id ) );
			
			
			$list=$model->where ( $condition )->setField("status",0);
			
			
			if ($list!==false) {
				
				
				$this->assign ( "jumpUrl", $this->getReturnUrl () );
				
				
				$this->success ( '狀態禁用成功' );
				
				
				} else {
				
				
				$this->error  (  '狀態禁用失敗！' );
				
				
			}
			
			
		}
		
		
		
		
		
		/**
			
			
			+----------------------------------------------------------
			
			
			* 默認恢復操作
			
			
			*
			
			
			+----------------------------------------------------------
			
			
			* @access public
			
			
			+----------------------------------------------------------
			
			
			* @return string
			
			
			+----------------------------------------------------------
			
			
			* @throws FcsException
			
			
			+----------------------------------------------------------
			
			
		*/
		
		
		function resume() {
			
			
			//恢復指定記錄
			
			
			$name=CONTROLLER_NAME;
			
			
			$model = D ($name);
			
			
			$pk = $model->getPk ();
			
			
			$id = $_GET [$pk];
			
			
			$condition = array ($pk => array ('in', $id ) );
			
			
			$list=$model->where($condition)->setField("status",1);
			
			
			if (false !== $list) {
				
				
				$this->assign ( "jumpUrl", $this->getReturnUrl () );
				
				
				$this->success ( '狀態恢復成功！' );
				
				
				} else {
				
				
				$this->error ( '狀態恢復失敗！' );
				
				
			}
			
			
		}
		
		/**
			+----------------------------------------------------------
			* 默認刪除操作
			+----------------------------------------------------------
			* @access public
			+----------------------------------------------------------
			* @return string
			+----------------------------------------------------------
			* @throws ThinkExecption
			+----------------------------------------------------------
		*/
		public function delete() 
		{
			//刪除指定記錄
			$name=CONTROLLER_NAME;
			$model = M($name);
			if(!empty( $model )){
				$pk = $model->getPk();
				$id = $_REQUEST[$pk];
				//dump($id);
				if(isset( $id )){
					$condition = array($pk => array ('in', explode ( ',', $id ) ) );
					//dump($condition);exit;
					$list = $model->where($condition)->delete();
					if($list!==false){
						$this->success('刪除成功！');
						}else{
						$this->error('刪除失敗！');
					}
					}else{
					$this->error('非法操作');
				}
			}
		}
		
		public function _empty() 
		{
			R('Empty/_empty');  
		}
		
		
		
		
		/***
			*
		***/
		public function getMenu($model)
		{
			$field = "id, title, orders, status";
			$order = "orders asc, id desc";
			$where = "islevel<3 and id!=1 and parent_id=1";
			
			// 取第一階
			$menuLevelA = M($model)->field($field)->order($order)->where($where)->select();
			foreach($menuLevelA as $key=>$value){
				// 查看有無子階
				$hasChild = M($model)->where('parent_id='.$value['id'])->count();
				
				$menuLevelA[$key]['child'] = ($hasChild==0?0:$this->getMenu_child( $model, $value['id'], $key+1));
			}
			
			return $menuLevelA;
		}
		
		/***
			*
		***/
		public function getMenu_child($model, $parent_id, $levelNow="")
		{
			// key 會累加, 只好另外計
			$field = "id, title, orders, status";
			$order = "orders asc, id desc";
			$where = "islevel<3 and parent_id=".$parent_id;
			
			$menuLevelN = M($model)->field($field)->order($order)->where($where)->select();
			
			foreach($menuLevelN as $key=>$value){
				// 查看有無子階
				$hasChild = M($model)->where('parent_id='.$value['id'])->count();
				$menuLevelN[$key]['rel'] = $levelNow.".".($key+1);
				$menuLevelN[$key]['child'] = ($hasChild==0?0:$this->getMenu_child( $model, $value['id'], $menuLevelN[$key]['rel'] ));
			}
			
			return $menuLevelN;
		}
		
		/***
			*
		***/
		public function menuToShow( $InMenu, $url )
		{
			$outBuf = '';
			
			foreach($InMenu as $key=>$value){			 
				if(strlen($value['rel'])==3){
					$outBuf .= '<ul class="submenu"><li><a ';
					if($value['child']==0){
						$outBuf .= 'href="http://'.$_SERVER['HTTP_HOST'].'/Admin/'.$url.'/id/'.$value['id'].'"';
					}
					$outBuf .= ' >'.$value['title'].'</a>
					<input id="statusId" name="id[]" type="hidden" value="'.$value['id'].'">
					<input id="menuId" name="UpdateId[]" type="hidden" value="'.$value['id'].'">
					<input class="checkOptMenu '.($value['status']?'':'ispause').'" style="width:20px;" name="orders[]" type="text" maxlength="2" AutoComplete="Off" placeholder='.$value['orders'].' value='.$value['orders'].'>';
					}else if(strlen($value['rel'])>=5){
					// 設計只做了3層, 所以這裡判斷超過3層也當三層算(前端程式應事先鎖住避免問題發生)
					$outBuf .= '<ul class="lastmenu"><li><a ';
					if($value['child']==0){
						$outBuf .= 'href="http://'.$_SERVER['HTTP_HOST'].'/Admin/'.$url.'/id/'.$value['id'].'"';
						}else{
						// 為了解決超過三層做的預防措施
						$outBuf .= 'style="color:#313846;"';
					}
					$outBuf .= ' >'.$value['title'].'</a>
					<input id="statusId" name="id[]" type="hidden" value="'.$value['id'].'">
					<input id="menuId" name="UpdateId[]" type="hidden" value="'.$value['id'].'">
					<input class="checkOptMenu '.($value['status']?'':'ispause').'" style="width:20px;"  name="orders[]" type="text" maxlength="2" AutoComplete="Off" placeholder='.$value['orders'].' value='.$value['orders'].'>';
					}else{
					$outBuf .= '<ul><li><a class="grayA" ';
					if($value['child']==0){
						$outBuf .= 'href="http://'.$_SERVER['HTTP_HOST'].'/Admin/'.$url.'/id/'.$value['id'].'"';
					}
					$outBuf .= ' >'.$value['title'].'</a>
					<!--<input id="statusId" name="id[]" type="hidden" value="'.$value['id'].'">
					<input id="menuId" name="UpdateId[]" type="hidden" value="'.$value['id'].'">
					<input class="checkOptMenu '.($value['status']?'':'ispause').'" style="width:20px;"  name="orders[]" type="text" maxlength="2" AutoComplete="Off" placeholder='.$value['orders'].' value='.$value['orders'].'>-->';
				}
				
				// 檢查子項
				if($value['child']){
					$outBuf .= $this->menuToShow( $value['child'], $url );
				}
				// 加上結尾
				$outBuf .= '</li></ul>';
			}
			return $outBuf;
		}
		
		
	}
	
	
?>


