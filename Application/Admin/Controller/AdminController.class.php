<?php 

/**
 * 
 * Admin(管理員)
 *
 * @package      	photonicCMS
 * @version        	$Id: AdminAction.class.php
*/ 	namespace Admin\Controller;
	use Think\Controller;class AdminController extends GlobalController
{
    public $dao;
	public function _initialize()
	{
		parent::_initialize();
		$this->dao = D('Admin');
	}

    /**
     * 列表
     *
     */
	public function index()
	{
		if(Session('roleId')!=1){ parent::_message('error', '權限不足'); }
		parent::_checkPermission();
		$condition = array();
		$title = formatQuery($_POST['title']);
		$selectType= trim($_POST['selecttype']);
		$username = formatQuery($_GET['username']);
		$userId = intval($_GET['userId']);
		$orderBy = trim($_GET['orderBy']);
		$orderType = trim($_GET['orderType']);
		$setOrder = setOrder(array(array('loginCount', 'admin.login_count'), array('id', 'admin.id')), $orderBy, $orderType);
		$pageSize = intval($_GET['pageSize']);
		$username &&  $condition['admin.username'] = array('like', '%'.$username.'%');
		$userId &&  $condition['admin.id'] = array('eq', $userId);
		//$condition['admin.id'] = array('neq', 1);
		$title && $condition[$selectType] = array('like', '%'.$title.'%');
		//$count = $this->dao->where($condition)->count();
		$count = $this->dao->count();
		$listRows = empty($pageSize) || $pageSize > 100 ? 15 : $pageSize ;
		if( !isset( $_GET['p'] ) || $_GET['p'] <= 0 ) {
			$_GET['p'] = 1;
		}
		$this->assign( "starti", $count-($listRows * ( $_GET['p'] - 1 )) + 1 );
		$p = new \Org\Util\Page($count, $listRows);
		$dataList = $this->dao->Table('admin')->Join('role on admin.role_id=role.id')->Field('admin.*,role.role_name as role_name')->Order($setOrder)->Where($condition)->Limit($p->firstRow.','.$p->listRows)->select();
		//echo $this->dao->getLastSql();
		$page = $p->show();
		if($dataList!=false){
			$this->assign('page', $page);
			$this->assign('dataList', $dataList);
		}
		$this->display();
	}

	/**
     * 錄入
     *
     */
	public function insert()
	{
		parent::_checkPermission();
		$condition = array();
		$title = formatQuery($_POST['title']);
		$selectType= trim($_POST['selecttype']);
		$username = formatQuery($_GET['username']);
		$userId = intval($_GET['userId']);
		$orderBy = trim($_GET['orderBy']);
		$orderType = trim($_GET['orderType']);
		$setOrder = setOrder(array(array('loginCount', 'admin.login_count'), array('id', 'admin.id')), $orderBy, $orderType);
		$pageSize = intval($_GET['pageSize']);
		$username &&  $condition['admin.username'] = array('like', '%'.$username.'%');
		$userId &&  $condition['admin.id'] = array('eq', $userId);
	    $condition['admin.id'] = array('neq', 1);			/*過濾wanwan帳號id不顯示*/
		$title && $condition[$selectType] = array('like', '%'.$title.'%');
		//dump($condition);
		$count = $this->dao->where($condition)->count();
		$listRows = empty($pageSize) || $pageSize > 100 ? 15 : $pageSize ;
		if( !isset( $_GET['p'] ) || $_GET['p'] <= 0 ){
			$_GET['p'] = 1;
		}
		$this->assign( "starti", $count-($listRows * ( $_GET['p'] - 1 )) + 1 );
		$p = new \Org\Util\Page($count, $listRows);
		$dataList = $this->dao->Table('admin')->Join('role on admin.role_id=role.id')->Field('admin.*,role.role_name as role_name')->Order($setOrder)->Where($condition)->Limit($p->firstRow.','.$p->listRows)->select();
		//echo $this->dao->getLastSql();
		$page = $p->show();
		if($dataList!=false){
			$this->assign('page', $page);
			$this->assign('dataList', $dataList);
		}
		$roleList = M('Role')->Order("id DESC")->select();
		empty($roleList) && parent::_message('error', '用戶組丟失，請檢查');
		$this->assign('roleList', $roleList);
		
		//
		//$this->assign('moduleList', M('Module')->select());
		
		$this->display();
	}

	/**
     * 提交錄入
     *
     */
	public function doInsert()
	{
		//dump($_POST);exit;
		// 密碼驗證
		if($_POST['password']!=$_POST['password1']) parent::_message('error', '兩次的密碼不同');
		// 帳號驗證
		if(!isEnglist($_POST['username'])) parent::_message('error', '帳號必須為英文或英文數字的組合');
		// 儲存限制區域
		if( $_POST['role_id']==3 ){
			$bufStr = "";
			foreach($_POST['passes'] as $key=>$value){
				$bufStr .= $value.",";
			}
			unset($_POST['passes']);
			$_POST['passes'] = $bufStr;
		}
		
		if($daoCreate = $this->dao->create()){
			$this->dao->password = md5($_POST['password']);
			$this->dao->password1 = $_POST['password'];
			$daoAdd = $this->dao->add();
			if(false !== $daoAdd){
				$this->success('錄入成功');
			}else{
				$this->success->error('錄入失敗');
			}
		}else{
			parent::_message('error', $this->dao->getError());
		}
	}

	/**
     * 編輯
     *
     */
	public function modify()
	{
		parent::_checkPermission();
		$condition = array();
		$title = formatQuery($_POST['title']);
		$selectType= trim($_POST['selecttype']);
		$username = formatQuery($_GET['username']);
		$userId = intval($_GET['userId']);
		$orderBy = trim($_GET['orderBy']);
		$orderType = trim($_GET['orderType']);
		$setOrder = setOrder(array(array('loginCount', 'admin.login_count'), array('id', 'admin.id')), $orderBy, $orderType);
		$pageSize = intval($_GET['pageSize']);
		$username &&  $condition['admin.username'] = array('like', '%'.$username.'%');
		$userId &&  $condition['admin.id'] = array('eq', $userId);
		$condition['admin.id'] = array('neq', 1);			/*過濾wanwan帳號id不顯示*/
		$title && $condition[$selectType] = array('like', '%'.$title.'%');
		$count = $this->dao->where($condition)->count();
		$listRows = empty($pageSize) || $pageSize > 100 ? 15 : $pageSize ;
		if( !isset( $_GET['p'] ) || $_GET['p'] <= 0 ){
			$_GET['p'] = 1;
		}
		$this->assign( "starti", $count-($listRows * ( $_GET['p'] - 1 )) + 1 );
		$p = new \Org\Util\Page($count, $listRows);
		$dataList = $this->dao->Table('admin')->Join('role on admin.role_id=role.id')->Field('admin.*,role.role_name as role_name')->Order($setOrder)->Where($condition)->Limit($p->firstRow.','.$p->listRows)->select();
		//echo $this->dao->getLastSql();
		$page = $p->show();
		if($dataList!=false){
			$this->assign('page', $page);
			$this->assign('dataList', $dataList);
		}
		$item = intval($_GET["id"]);
		$jumpUri = trim($_GET['jumpUri']);
		//編輯自己資料時跳過權限檢測，可以編輯自己帳戶信息
		if($item != parent::_getAdminUid()) parent::_checkPermission();
		$record = $this->dao->Where('id='.$item)->find();
		if (empty($item) || empty($record)) parent::_message('error', '記錄不存在');
		$roleList = M('Role')->Order("id")->select();
		empty($roleList) && parent::_message('error', '當前無角色組，請先錄入角色組');
		$this->assign('roleList', $roleList);
		$this->assign('jumpUri', $jumpUri);
		//var_dump( $record );
		$this->assign('vos', $record);
		$this->display();
	}

	/**
     * 提交編輯
     *
     */
	public function doModify()
	{
	    parent::_setMethod('post');
		$item = intval($_POST['id']);
		//dump( $_POST );exit;
		//在無管理員管理權限的情況下，可以編輯自己帳戶信息
		if($item != parent::_getAdminUid())
		empty($item) && parent::_message('error', 'ID獲取錯誤,未完成編輯');
		$password = $_POST['password'];
		$opassword = $_POST['opassword'];
		if($this->dao->create()){
			if(!empty($password)){
				$this->dao->password = md5($password);
				$this->dao->password1 = $password;
			}else{
				$this->dao->password = $opassword;
			}
			if($item == 1){
				//防止修改默認用戶所發屬組導致不能登錄
				$this->dao->role_id = 1;
			}
			$daoSave = $this->dao->save();
			//dump($daoSave);exit;
			if(false !== $daoSave)
			{
				//防止無權限操作情況下，修改自身資料跳轉死循環
				$jumpUri = empty($_POST['jumpUri']) ? 0 : U('Index/index');
				$this->success( '更新成功', $jumpUri);
			}else{
				parent::_message('error', '更新失敗');
			}
		}else{
			parent::_message('error', $this->dao->getError());
		}
	}

	/**
     * 批量操作
     *
     */
	public function doCommand()
	{
		parent::_checkPermission('Admin_command');
		if(getMethod() == 'get'){
			$operate = trim($_GET['operate']);
		}elseif(getMethod() == 'post'){
			$operate = trim($_POST['operate']);
		}else{
			parent::_message('error', '只支持POST,GET數據');
		}

		switch ($operate){
			case 'delete': parent::_delete();break;
			case 'update': parent::_batchModify(0, $_POST, array('realname')); break;
			case 'setStatus': parent::_setStatus('set');break;
            case 'unSetStatus': parent::_setStatus('unset');break;
			default: parent::_message('error', '操作類型錯誤') ;
		}
	}
	
	/***
	 *
	 *
	 *
	 ***/
	public function addModule()
	{
		$model = M('Module');
		$this->assign('dataList', M('Module')->select());
		
		$this->display();
	}
	
	/***
	 *
	 *
	 *
	 ***/
	public function doAddModule()
	{
		$model = M('Module');
		
		if( $_POST && ($_POST['pw']=="ep 1p3ao6u.30 fm06a83") ){
			if( $model->create() ){
				$result = $model->add();
				if( $result ){
					$this->success( '錄入成功', U('Admin/addModule'));
				}
		    }
		}
		
		$this->error('success', '新增失敗');
	}

}

