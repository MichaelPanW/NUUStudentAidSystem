<?php 

/***
 * 
 * 
 *
 * @package      	photonicCMS
 ***/
namespace Admin\Controller;
use Think\Controller;
class AboutController extends GlobalController
{
    function _initialize()
    {
        parent::_initialize();
		

    }

    /**
     * 列表
     *
     */
    public function index()
    {
		$getId = ( $_GET['id']?$_GET['id']:1 );
		
		$dataList = M('Article')->field("id, title, description, content")->where("id=".$getId)->select();
		$this->assign('vo', $dataList[0]);
		
		$this->display();
    }

	/**
     * 階層參數
     *
     */
    public function para()
    {
		$getId = isset($_GET['id'])&&!empty($_GET['id'])?$_GET['id']:2;
		$position = getPositionCategory( $getId );
		$this->assign("position", $position );
		$dataList = getAboutcatTop($this->aboutcat);
		$this->assign('dataList', $dataList);
		$Model = new Model();

		$query = "select * from aboutcat where id=".$getId." order by orders desc,id desc";
		$row = $Model->query( $query );
		
		$this->assign( "vo", $row[0] );
		$this->assign( "pid", $row[0]['parent_id'] );      //dump($dataList);exit;     
        $this->display();

    }

	/**
     * 新增分類
     * getPositionCategory 在 Thinkphp 內的 common/function.php 內
     */
    public function addClass()
    {
		$getId = isset($_GET['id'])&&!empty($_GET['id'])?$_GET['id']:1;
		$position = getPositionCategory( "Aboutcat", $getId );
		//dump($position);exit;
		$this->assign("position", $position );
		//$dataList = getAboutcatTop($this->aboutcat);
		// 不想用內建的, 排序方向會反過來
		$dataList = M('Aboutcat')->where('id='.$getId.' or parent_id='.$getId)->order('orders asc,id asc')->select();
		//dump($dataList);exit;
		$this->assign('dataList', $dataList);
        $this->display();
    }



	function getSubCategroyProduct($channel)
	{
		global $out;
		$out2 = '';
		$Model = new Model();
		foreach ($channel as $k=>$v){ 
			$query =  "select * from aboutcat where parent_id=".$v[id]." order by orders desc,id desc";
			if($one = $Model->query( $query ) ){                        
				$out2 = $this->getSubCategroyProduct($one); //查看下级栏目
			}else{
				$out[] = $v[id];
			}             
		}
		return $out;
	}

	function getParentCategroyProduct($channel)
	{
		global $out;
		$out2 = '';
		$Model = new Model();

		foreach ($channel as $k=>$v){                      
			$query =  "select * from aboutcat where id=".$v[id]." order by orders desc,id desc";
			if($one = $Model->query( $query ) ){                        
				$out2 = $this->getParentCategroyProduct($one); //查看下级栏目
			}else{
				if( $v['parent_id'] == 2 ){
					return $out;
				}else{
					$out[] = $v['parent_id'];
				}
			}             
		}
		return $out;
	}

    /***
	 * insert
     * 寫入
     *
     ***/
    public function insert()
    {
		$getId = isset($_GET['id'])&&!empty($_GET['id'])?$_GET['id']:$this->topAboutcat;
		$this->assign('cid', $getId);
		$position = getPositionCategory( $getId );
		//dump($getId);exit;

		$parent = M('Aboutcat')->where('id='.$getId)->select();
		
		$parent[0]['id'] = ($parent[0]['id']?$parent[0]['id']:$getId);

		$this->assign("vo", $parent[0]);
		$this->assign("position", $position );

		$this->display();
    }

    /***
	 * doInsert
     * 儲存父類別
     *
     ***/
    public function doInsert()
    {
		//dump($_POST);exit;
		
		/*if( $_POST['istop'] == 1 ) {
		$countistop = $this->dao->where('istop=1')->count();
		if( $countistop >= $this->configAll['istopnum'] ) {
				$this->error('首頁最多只能顯示'.$this->configAll['istopnum'].'個商品');
			}
		}*/
		/*if( $_POST['recommend'] == 1 ) {
		$countrecommend = $this->dao->where('recommend=1')->count();
		if( $countrecommend >= $this->configAll['recommendnum'] ) {
				$this->error('首頁最多只能顯示'.$this->configAll['recommendnum'].'個推薦商品');
			}
		}*/
        if($daoCreate = $this->dao->create())
        {
            $style = createStyle($_POST);
            $this->dao->user_id = parent::_getAdminUid();
            $this->dao->username = parent::_getAdminName();
			//echo $this->dao->user_id . "---" . $this->dao->username;exit;
			$globalConfig = getContent('cms.config.php','../');
			$globalAttachSize = intval($globalConfig['global_attach_size']);
			$globalAttachSuffix = $globalConfig['global_attach_suffix'];
			$dot = '/';
			$model = CONTROLLER_NAME;
			$setFolder = empty($model) ?'file/': $model .$dot ;
			$setUserPath = makeFolderName(1) ;
			$finalPath = UPLOAD_PATH.$dot.$setFolder.$setUserPath;
			if(!is_dir($finalPath)){
				@mkdir($finalPath);
			}
			//var_dump( $_FILES );EXIT;
			$isupload = false;
			$explain_file = $_FILES['explain_book'];
			IF ($explain_file[type]!="application/octet-stream" AND $explain_file[type]!="application/x-zip-compressed" AND $explain_file[type]!="application/x-rar-compressed" AND $explain_file[type]!="text/plain" AND $explain_file[type]!="application/msword" AND $explain_file[type]!="application/pdf" AND $explain_file[type]!="application/vnd.ms-excel" AND $explain_file[type]!="application/x-shockwave-flash" AND $explain_file[type]!="application/vnd.ms-powerpoint" AND $explain_file[type]!="application/octet-stream" AND $explain_file[type]!="application/vnd.openxmlformats-officedocument.wordprocessingml.document" AND $explain_file[type]!="application/vnd.openxmlformats-officedocument.presentationml.presentation" AND $explain_file[type]!="image/jpg" AND $explain_file[type]!="image/pjpeg" AND $explain_file[type]!="image/png" AND $explain_file[type]!="image/jpeg" AND $explain_file[type]!="image/gif" AND $explain_file[type]!="image/bmp" AND $explain_file[size]!=0)
               {
				echo "<script language='javascript'>window.alert('接受類型為(jpg,gif,png,jpeg,doc,docx,ppt,pptx,txt,rar,zip,pdf,xls,swf,bmp)檔案');history.back();</script>";exit;} 
			if($explain_file[size]!=0) {
               if( preg_match('/([\x80-\xFE][\x40-\x7E\x80-\xFE])+/', $explain_file['name']) ) {
					$this->dao->explain_book=$dot.$setFolder.$setUserPath."explain_".time().strtolower(strrchr($explain_file['name'], "."));
				} else {
					$this->dao->explain_book=$dot.$setFolder.$setUserPath.$explain_file['name'];
				}
                copy($explain_file[tmp_name],UPLOAD_PATH.$this->dao->explain_book);
				unset($_FILES['explain_book']);
			}
			
			//dump($_FILES);exit;
			foreach( $_FILES as $val ) {
				if( isset( $val['name'] ) && $val['name'] != "" ) {
					$isupload = true; break;
				}
			}
			if( $isupload ) {
				import("ORG.Net.UploadFile");
				$upload = new \Org\Net\UploadFile();
				$allowExts = "jpg,gif,png,jpeg,doc,docx,ppt,pptx,txt,rar,zip,pdf,xls,swf,bmp";
				$upload->maxSize = empty($fileSize) ?$globalAttachSize : intval($fileSize) ;
				$upload->allowExts = empty($allowExts) ?explode(',',$globalAttachSuffix) : explode(',',$allowExts) ;
				$upload->savePath = $finalPath;
				$upload->saveRule = 'uniqid';
				$upload->thumb = true;
				$globalThumbStatus = intval($globalConfig['product_thumb_status']);;
				//$globalThumbSize = trim($globalConfig['product_thumb_size']);
				//$globalThumbSize = "264,250";
				//$globalThumbSizeExplode = explode(',',$globalThumbSize);
				$upload->thumbMaxWidth = "1024,300";
				$upload->thumbMaxHeight = "768,300";
				$upload->thumbPrefix = '';
				$upload->thumbSuffix = '_s';
				if(!$upload->upload()) {
					echo ($upload->getErrorMsg());
				} else {
					$i = 0;
					$uploadList = $upload->getUploadFileInfo();
					//dump($uploadList);exit;
					foreach( $uploadList as $val) {
						$array_map = array( "main_file" => "main_image", 
											"attach_file1" => "attach_image1",
											"attach_file2" => "attach_image2",
											"attach_file3" => "attach_image3",
											"attach_file4" => "attach_image4"
											);
						$largepath = formatAttachPath($uploadList[$i]['savepath']) . $uploadList[$i]['savename'];
						$thumbpath = $uploadList[$i]['savepath'] . splitThumb($uploadList[$i]['savename']);
						if( $val['key'] == "main_file" ) {
							$this->dao->main_image = $largepath;
							$this->dao->main_thumb = fileExit($thumbpath) ? formatAttachPath($thumbpath) : '';
						} else if( $val['key'] == "attach_file1" ) {
							$this->dao->attach_image1 = $largepath;
							$this->dao->attach_thumb1 = fileExit($thumbpath) ? formatAttachPath($thumbpath) : '';
						} else if( $val['key'] == "attach_file2" ) {
							$this->dao->attach_image2 = $largepath;
							$this->dao->attach_thumb2 = fileExit($thumbpath) ? formatAttachPath($thumbpath) : '';
						} else if( $val['key'] == "attach_file3" ) {
							$this->dao->attach_image3 = $largepath;
							$this->dao->attach_thumb3 = fileExit($thumbpath) ? formatAttachPath($thumbpath) : '';
						} else if( $val['key'] == "attach_file4" ) {
							$this->dao->attach_image4 = $largepath;
							$this->dao->attach_thumb4 = fileExit($thumbpath) ? formatAttachPath($thumbpath) : '';
						}
						$i++;
					}
				}
			}
            $daoAdd = $this->dao->add();
			//echo $this->dao->getLastSql();exit;
            if(false !== $daoAdd) {
				$this->success('錄入成功');
            } else {
				$this->error('錄入失敗');
            }
        } else {
            $this->error($this->dao->getError());
        }
    }

    /***
	 * modify
     * 編輯
     *
     ***/
    public function modify()
    {
        $getId = ( $_GET['id']?$_GET['id']:1 );
		
		$dataList = M('Article')->field("id, title, description, content")->where("id=".$getId)->select();
		$this->assign('vo', $dataList[0]);
		
		$this->display('About:modify');
    }

    /***
	 * doModify
     * 提交編輯
     *
     ***/
    public function doModify()
    {   
			if(isset($_POST['status_description'])){

				$daoSave = D('config')->data($_POST)->where('id=1')->save();
				
				if(false !== $daoSave){
					$this->success('更新成功');
					exit;
					}else{
					$this->error('更新失敗');
				}
				}else{
					$this->error('不能輸入空值');
			}
    }

    /***
	 * doModifyBoss
     * 提交編輯
     *
     ***/
    public function doModifyBoss()
    {   

			
			$this->dao= D('boss');
			
			if($this->dao->create())
			{
				$riveraAttachSize = 20480000;
				$riveraAttachSuffix = "";
				$dot = '/';
				
				$model = CONTROLLER_NAME;
				$setFolder = empty($model) ?'file/': $model .$dot ;
				$setUserPath = makeFolderName(1) ;
				$finalPath = 'Uploads'.$dot.$setFolder.$setUserPath;
				if(!is_dir($finalPath)){
					@mkdir($finalPath);
				}
				//var_dump( $_FILES );EXIT;
				
				$isupload = false;
				$explain_file = $_FILES['explain_book'];
				//echo $explain_file[type];exit;
				
				if($explain_file[type]!="application/octet-stream" AND $explain_file[type]!="application/x-zip-compressed" AND $explain_file[type]!="application/x-rar-compressed" AND $explain_file[type]!="text/plain" AND $explain_file[type]!="application/msword" AND $explain_file[type]!="application/pdf" AND $explain_file[type]!="application/vnd.ms-excel" AND $explain_file[type]!="application/x-shockwave-flash" AND $explain_file[type]!="application/vnd.ms-powerpoint" AND $explain_file[type]!="application/octet-stream" AND $explain_file[type]!="application/vnd.openxmlformats-officedocument.wordprocessingml.document" AND $explain_file[type]!="application/vnd.openxmlformats-officedocument.presentationml.presentation" AND $explain_file[type]!="image/jpg" AND $explain_file[type]!="image/pjpeg" AND $explain_file[type]!="image/png" AND $explain_file[type]!="image/jpeg" AND $explain_file[type]!="image/gif" AND $explain_file[type]!="image/bmp" AND $explain_file[size]!=0){
					echo "<script language='javascript'>window.alert('檔案接受類型為(jpg,gif,png,jpeg,doc,docx,ppt,pptx,txt,rar,zip,pdf,xls,swf,bmp)檔案');history.back();</script>";exit;
				} 
				
				if($explain_file[size]!=0){
					if( preg_match('/([\x80-\xFE][\x40-\x7E\x80-\xFE])+/', $explain_file['name']) ){
						$this->dao->explain_book=$dot.$setFolder.$setUserPath."explain_".time().strtolower(strrchr($explain_file['name'], "."));
						}else{
						$this->dao->explain_book=$dot.$setFolder.$setUserPath.$explain_file['name'];
					}
					
					copy($explain_file[tmp_name],UPLOAD_PATH.$this->dao->explain_book);
					
					@unlink(UPLOAD_PATH . "/" . $_POST['explain_book_old']);
					unset($_FILES['explain_book']);
				}
				
				foreach( $_FILES as $val ){
					if( isset( $val['name'] ) && $val['name'] != "" ){
						$isupload = true; break;
					}
				}
				
				if( $isupload ){
					import("ORG.Net.UploadFile");
					$upload = new \Org\Net\UploadFile();
					$allowExts = "jpg,gif,png,jpeg,doc,docx,ppt,pptx,txt,rar,zip,pdf,xls,swf,bmp";
					$upload->maxSize = empty($fileSize) ?$riveraAttachSize : intval($fileSize) ;
					$upload->allowExts = empty($allowExts) ?explode(',',$riveraAttachSuffix) : explode(',',$allowExts) ;
					$upload->savePath = $finalPath;
					$upload->saveRule = 'uniqid';
					$upload->thumb = false;
					$upload->thumbMaxWidth = "110,140";
					$upload->thumbMaxHeight = "110,140";
					$upload->thumbPrefix = '';
					$upload->thumbSuffix = '_s';
					if(!$upload->upload()){
						echo ($upload->getErrorMsg());
						}else{
						$i = 0;
						$uploadList = $upload->getUploadFileInfo();
						foreach( $uploadList as $val) {
							$array_map = array( "main_file" => "main_image", 
							"attach_file1" => "attach_image1",
							"attach_file2" => "attach_image2",
							"attach_file3" => "attach_image3",
							"attach_file4" => "attach_image4",
							"icon1" => "icon1",
							"icon2" => "icon2",
							"icon3" => "icon3"
							);
							
							$largepath = formatAttachPath($uploadList[$i]['savepath']) . $uploadList[$i]['savename'];
							
								$this->dao->main_image = $largepath;
								@unlink(UPLOAD_PATH . "/" . $_POST['attach_image1_old']);

							$i++;
						}
					}
				}
				$this->dao->description=$_POST['description'];
				$daoSave = $this->dao->save();//dump($daoSave);exit;
				if(false !== $daoSave){
					$this->success('更新成功');
					}else{
					$this->error('更新失敗');
				}
				}else{
				$this->error($this->dao->getError());
			}
    }

	/***
     * clearFile
     * 刪除檔案
     *
     ***/
    public function clearFile()
    {
        parent::_setMethod('get');

		$cid = $_GET['id'];
		$rel = $_GET['r'];
        $item = intval($_GET['pid']);
		$field = trim($_GET['fname']);
		$setstr = "";

		if( $field == "m" ){
			$setstr = "main_image='',main_thumb='' ";

		}else if( $field == 1 ){
			$setstr = "attach_image1='',attach_thumb1='' ";
		}else if( $field == 2 ){
			$setstr = "attach_image2='',attach_thumb2='' ";
		}else if( $field == 3 ){
			$setstr = "attach_image3='',attach_thumb3='' ";
		}else if( $field == 4 ){
			$setstr = "attach_image4='',attach_thumb4='' ";
		}else if( $field == 5 ){
			$setstr = "explain_book='' ";
		}else if( $field == 'i1' ){
			$setstr = "icon1='' ";
		}else if( $field == 'i2' ){
			$setstr = "icon2='' ";
		}else if( $field == 'i3' ){
			$setstr = "icon3='' ";
		}

        empty($item) && $this->error('記錄不存在');
		$Model = new Model();
		$query =  "update product set " . $setstr . " where id=".$item;
		$result = $Model->query( $query );
		$query = "select * from product where id=" . $item;
		$productRow = $Model->query( $query );

		if(false !== $result){
			if( $field == "m" ){				
				@unlink(UPLOAD_PATH . "/" . $productRow[0]['main_image']);
				@unlink(UPLOAD_PATH . "/" . $productRow[0]['main_thumb']);
			}else if( $field == 1 ){
				@unlink(UPLOAD_PATH . "/" . $productRow[0]['attach_image1']);
				@unlink(UPLOAD_PATH . "/" . $productRow[0]['attach_thumb1']);
			}else if( $field == 2 ){
				@unlink(UPLOAD_PATH . "/" . $productRow[0]['attach_image2']);
				@unlink(UPLOAD_PATH . "/" . $productRow[0]['attach_thumb2']);
			}else if( $field == 3 ){
				@unlink(UPLOAD_PATH . "/" . $productRow[0]['attach_image3']);
				@unlink(UPLOAD_PATH . "/" . $productRow[0]['attach_thumb3']);
			}else if( $field == 4 ){
				@unlink(UPLOAD_PATH . "/" . $productRow[0]['attach_image4']);
				@unlink(UPLOAD_PATH . "/" . $productRow[0]['attach_thumb4']);
			}else if( $field == 5 ){
				@unlink(UPLOAD_PATH . "/" . $productRow[0]['explain_book']);
			}else if( $field == 'i1' ){
				@unlink(UPLOAD_PATH . "/" . $productRow[0]['icon1']);
			}else if( $field == 'i2' ){
				@unlink(UPLOAD_PATH . "/" . $productRow[0]['icon2']);
			}else if( $field == 'i3' ){
				@unlink(UPLOAD_PATH . "/" . $productRow[0]['icon3']);
			}
			parent::_message('success', '更新完成',U('About/modify/pid/'.$item.'/id/'.$cid .'/r/'.$rel));
		}else{
			parent::_message('error', '更新失敗',U('About/modify/pid/'.$item.'/id/' . $cid .'/r/'.$rel));
		}
    }

    /***
	 * doCommand
     * 批量操作
     *
     ***/
    public function doCommand()
    {
		//dump($_POST);exit;
            $operate = trim($_POST['operate']);
			$jumpUri = trim($_POST['jumpUri']);


		//$newAboutcat = intval($_POST['newAboutcat']);

        switch($operate){
            case 'delete': parent::_delete('About', U('About/index'), array('main_image', 'main_thumb','attach_image1', 'attach_thumb1','attach_image2', 'attach_thumb2','attach_image3', 'attach_thumb3','attach_image4', 'attach_thumb4','explain_book'));break;
            case 'setStatus': parent::_setStatus('set','About',$jumpUri);break;
            case 'unSetStatus': parent::_setStatus('unset','About',$jumpUri);break;
            case 'update': 
			     //dump($_POST);exit;
			     parent::_batchModify('About', $_POST, array('title','orders','models','link'), $jumpUri);
				 break;
            case 'move': parent::_move($newAboutcat);break;
            default: parent::_message('error', '操作類型錯誤') ;
        }
    }

    /***
	 *
	 ***/
	 public function getMenu($model)
	 {
		 $order = "orders asc, id desc";
		 
		 // 取第一階
		 $menuLevelA = M($model)->field('id, title, orders, status')->order($order)->where("id!=1 and parent_id=1")->select();
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
		 $order = "orders asc, id desc";
		 $where = "parent_id=".$parent_id;
		 $field = "id, title, orders, status";
		 
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
					   <!--<input class="checkOptMenu '.($value['status']?'':'ispause').'" style="width:20px;" name="orders[]" type="text" maxlength="2" AutoComplete="Off" placeholder='.$value['orders'].' value='.$value['orders'].'>-->';
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
					   <!--<input class="checkOptMenu '.($value['status']?'':'ispause').'" style="width:20px;"  name="orders[]" type="text" maxlength="2" AutoComplete="Off" placeholder='.$value['orders'].' value='.$value['orders'].'>-->';
			 }else{
				 $outBuf .= '<ul><li><a class="grayA" ';
				 if($value['child']==0){
					 $outBuf .= 'href="http://'.$_SERVER['HTTP_HOST'].'/Admin/'.$url.'/id/'.$value['id'].'"';
				 }
				 $outBuf .= ' >'.$value['title'].'</a>
				       <input id="statusId" name="id[]" type="hidden" value="'.$value['id'].'">
				       <input id="menuId" name="UpdateId[]" type="hidden" value="'.$value['id'].'">
					   <!--<input class="checkOptMenu '.($value['status']?'':'ispause').'" style="width:20px;"  name="orders[]" type="text" maxlength="2" AutoComplete="Off" placeholder='.$value['orders'].' value='.$value['orders'].'>-->';
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
	  
	/***
	 * 額外設定用頁面
	 *
	 *
	 ***/
	public function extra_set()
	{
		$dataList = M('Article')->field('id, content, link, seo')->where('id=5')->select();
		$this->assign('vo', $dataList[0]);
		
		$this->display();
	}
	
	/***
	 * 額外設定用頁面
	 *
	 *
	 ***/
	public function setlink()
	{
		$dataList = M('Plan')->where('id='.$_GET['id'])->select();
        $this->assign('vo', $dataList[0]);
		$this->display();
	}
	
	/***
	 * 額外設定用頁面
	 *
	 *
	 ***/
	public function saveLink()
	{
		$model = M('Plan');
		
		for($i=1; $i<4; $i++){
			if(($i==2) && ($_POST['data'.$i]!="")){
				if( strstr($_POST['data'.$i],"www.instagram.com") ){
					if( strstr($_POST['data'.$i],"http")===false ){
						$_POST['data'.$i] = "https://".$_POST['data'.$i];
					}
				}else{
					$_POST['data'.$i] = "https://www.instagram.com/".$_POST['data'.$i]."/";
				}
			}else if((substr($_POST['data'.$i], 0, 4) != "http") && ($_POST['data'.$i]!="")){
			    $_POST['data'.$i] = "http://".$_POST['data'.$i];
			}
		}
			 
		if($model->create()){
			$result = $model->save();
			if($result!==false){
				$this->success('儲存成功');
			}else{
				$this->error('儲存失敗');
			}
		}else{
			$this->error('操做失敗');
		}
	}
	/***
	 * 計畫用
	 *
	 *
	 ***/
	public function title()
	{
		
		$dataList=D('config')->find();
		$this->assign('vo', $dataList);
		$this->display();
	}
	/***
	 * 計畫用
	 *
	 *
	 ***/
	public function boss()
	{
		
		if(session('roleId')!=1 && $_GET['id']!=session('roleId')){
			$this->redirect('/'.__ACTION__.'/id/'.session('roleId'));
		}
		$getId = ( $_GET['id']?$_GET['id']:1 );
		$dataList=D('boss')->select();
		$this->assign('id', $getId);
		$this->assign('al', $dataList);
		$this->assign('vo', $dataList[$getId-1]);
		$this->assign("title","計畫內容");
		$this->display();
	}
	/***
	 * 計畫用
	 *
	 *
	 ***/
	public function content()
	{
		
		if(session('roleId')!=1 && $_GET['id']!=session('roleId')){
			$this->redirect('/'.__ACTION__.'/id/'.session('roleId'));
		}
		$getId = ( $_GET['id']?$_GET['id']:0 );
		$dataList=D('plan_content')->select();
		
		$this->assign('id', $getId);
		$this->assign('al', $dataList);
		$this->assign('vo', $dataList[$getId]);
		$this->assign("title","計畫內容");
		$this->display();
	}
}

