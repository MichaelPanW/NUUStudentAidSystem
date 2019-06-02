<?php
namespace Admin\Controller;
use Think\Controller;
class RelateController extends GlobalController
{
	protected $category, $dao, $rel;
	function _initialize()
	{
		parent::_initialize();
	}
	function index(){
		$relate=D('relate')->order("`end_time` desc")->select();
		$this->assign('relate',$relate);
		$this->display();
	}
	function oldindex(){
		$relate=D('relate')->order("`end_time` desc")->select();
		$this->assign('relate',$relate);
		$this->display();
	}
	function onupdateexcel(){
		if( $_FILES['excel']['error'] == 0 and strlen($_FILES['excel']['tmp_name']) > 0 ){
			import("Org.Util.PHPExcel");
			$PHPExcel = new \PHPExcel();
			$PHPReader = new \PHPExcel_Reader_Excel2007();
				// 檢查匯入的檔案是否為 Excel 檔
			if(!$PHPReader->canRead($_FILES['excel']['tmp_name'])){
				$PHPReader = new \PHPExcel_Reader_Excel5();
				if(!$PHPReader->canRead($_FILES['excel']['tmp_name'])){
					$this->error('檔案錯誤, 請確認檔案為 Excel', U('Imcrm/index','',''), 3);
					exit;
				}
			}
			$PHPExcel = $PHPReader->load($_FILES['excel']['tmp_name']);
			$this->ExcelToDb_Where("eletest",$PHPExcel);
			import("ORG.Net.UploadFile");
			$name=time();
			$upload = new \Org\Net\UploadFile();
			$upload->saveRule  = $name ;// 设置附件上传大小
			$upload->uploadReplace  = true ;// 设置附件上传大小
			$upload->allowExts  = array('xlsx');// 设置附件上传类型
			$upload->savePath =  './Uploads/Relate/';// 设置附件上传目录
			if(!$upload->upload()) {// 上传错误提示错误信息
				$this->error($upload->getErrorMsg());
			}else{
				$info =  $upload->getUploadFileInfo();
			}

		}
	}
				/***
			將資料比對並匯入資料庫
			輸入資料表名
			***/
		public function ExcelToDb_Where($DB_TABLE,$Excel)//存入資料庫
		{
			D($DB_TABLE)->where("true")->delete();
			//excel模組
			import("ORG.Util.PHPExcel");
			$sqlline="";
			
			$sheetData = $Excel->getSheet()->toArray(null,true,true,true);
			//dump($sheetData);
			//讀列
			foreach($sheetData as $key => $col){
				if($key==1) continue;
				$colindex=0;/*`id`, `class`, `obligatory`, `class_name`, `teacher`, `school`, `class_sit`, `class_number`, `class_num`, `classify`, `class_sitname`*/
				$data['id']=$key-1;
				$data['class']=$col['C'];
				$data['obligatory']=$col['D'];
				$data['class_name']=$col['A'];
				$data['school']=$col['F'];
				$data['class_sit']=$this->splitclass($col['G']);
				$data['class_sitname']=$col['G'];
				$data['class_number']=$col['H'];
				$data['class_num']=$col['L'];
				$data['classify']='2';
				$data['teacher']=$col['B'];
				D($DB_TABLE)->data($data)->add();
			}
			$this->num=count($sheetData);
			$this->saverelate("relate","Uploads/Relate/".$name.".xlsx");
			$this->sortdata();
			//$this->autoclass();
		}
		function onupdate(){
			import("ORG.Net.UploadFile");
			$upload = new \Org\Net\UploadFile();
			$upload->saveRule  = "Datawindow" ;// 设置附件上传大小
			$upload->uploadReplace  = true ;// 设置附件上传大小
			$upload->allowExts  = array('pdf');// 设置附件上传类型
			$upload->savePath =  './Uploads/Relate/';// 设置附件上传目录
			if(!$upload->upload()) {// 上传错误提示错误信息
				$this->error($upload->getErrorMsg());
			}else{
				$info =  $upload->getUploadFileInfo();
			}
			$results = shell_exec('java -jar /usr/local/apache2/htdocs/studentaid.nuucloud.com/Uploads/Relate/linjar.jar');
			$this->num=0;
			$my=$this->texttodb("Uploads/Relate/data.txt");
			$this->saverelate("relate",$my);
		}

		function saverelate($table,$filepath){
			$re_data['start_time']=time();
			$re_data['end_time']=time();
			$re_data['content']=$_POST['title'];
			$re_data['description']=$filepath;
			$re_data['username']=Session('adminId');
			$re_data['scroll']=$this->num;
			D($table)->data($re_data)->add();
		}
		function onback(){
			if(isset($_GET['id'])){
				$dataname=D('relate')->where("id='{$_GET['id']}'")->select()[0]['description'];
				if($dataname!=''){
					$my=$this->texttodb($dataname);
					if($my!=""){
						$re_data['end_time']=time();
						$re_data['description']=$my;
						$re_data['scroll']=$this->num;
						D('relate')->where("id='{$_GET['id']}'")->data($re_data)->save();
					}
				}else{
					echo "資料錯誤";
				}
			}else{
				echo "沒傳資料";
			}
		}
		function on_eback(){
			if(isset($_GET['id'])){
				$dataname=D('relate')->where("id='{$_GET['id']}'")->select()[0]['description'];
				if($dataname!=''){
			import("Org.Util.PHPExcel");
			$PHPExcel = new \PHPExcel();
			$PHPReader = new \PHPExcel_Reader_Excel2007();
				// 檢查匯入的檔案是否為 Excel 檔
			if(!$PHPReader->canRead($dataname)){
				$PHPReader = new \PHPExcel_Reader_Excel5();
				if(!$PHPReader->canRead($dataname)){
					$this->error('檔案錯誤, 請確認檔案為 Excel', U('Imcrm/index','',''), 3);
					exit;
				}
			}
			$PHPExcel = $PHPReader->load($dataname);
			$this->ExcelToDb_Where("eletest",$PHPExcel);
				}else{
					echo "資料錯誤";
				}
			}else{
				echo "沒傳資料";
			}
		}
		function user(){
			$this->display();
		}
		function teach(){
			$this->display();
		}
		function oldteach(){
			$this->display();
		}
		/**列表*/
		public function myclass(){
			$al=D('class')->distinct('lastclass')->field('lastclass')->select();
			$getId = ( $_GET['id']?$_GET['id']:"%" );
			$PageSize = 10;
			$total = D('class')->where("lastclass like '{$getId}'")->count();
			if( !isset( $_GET['p'] ) || $_GET['p'] <= 0 ) {
				$_GET['p'] = 1;
			}
			$this->assign( "startPage", $total-($PageSize * ( $_GET['p'] - 1 )) + 1 );
			// 統計
			$pagwAllA = new \Org\Util\Page($total, $PageSize);
			$pageShowA = $pagwAllA->show();
			$this->assign('pageA', $pageShowA);
			$dataList = D('class')->order("lastclass asc")->where("lastclass like '{$getId}'")->Limit($pagwAllA->firstRow.','.$pagwAllA->listRows)->select();
				/////////////////////////////// 頁數 end ////////////////////////////////
			$this->assign('dataList', $dataList);
			$this->assign('id', $getId);
			$this->assign('al', $al);
			$this->assign('p', $_GET['p']);
			$this->display();
		}
		function onreuser(){
			$user=D("user")->select();
			$table=date("Ymd")."user";
			D()->execute("create table ".$table." like user ");
			foreach($user as $key =>$vo){
				D($table)->data($vo)->add();
			}
			D()->execute("TRUNCATE TABLE user");
				/*
				foreach($user as $key =>$vo){
					$elective=D('elective')->where("`class`='{$vo['class']}' and `classify` ='1'")->select();
					$st="";
					foreach ($elective as $keye => $value) {
						$st.=$value['class_number']."@";
					}
					D('user')->where("id={$vo['id']}")->data("content={$st}")->save();
				}
				*/
				$this->success("成功");
			}
			function texttodb($dataname){
				$dbname="eletest";
				$copy="Uploads/Relate/";
				D($dbname)->where(true)->delete();
				$ar=array("","一","二","三","四","五","六","日");
				$myfile = fopen($dataname, "r") or die("Unable to open file!");
			// 输出单行直到 end-of-file
				while(!feof($myfile)) {
					$n="";
					$getfile=fgets($myfile);
					$ex=explode("xx",$getfile);
					if(!($ex[0]=="項次" || $ex[0]=="Items" || $ex[0]=="" || $ex[2]=="")){
						$this->num=$this->num+1;
						$data="";
						if($ex[5]=="Y"){
							for($i=5;$i<count($ex);$i++){
								$ex[$i]=$ex[$i+1];
							}
						}
						$ans=$ex[6];
						$out=$this->splitclass($ans);
							$teacher=explode(" ",$ex[6]);
							$data['id']=$ex[0];
							$data['class']=$ex[2];
							$data['obligatory']=$ex[3];
							$data['class_name']=$ex[4];
							$data['school']=$ex[5];
							$data['class_sit']=$out;
							$data['class_sitname']=$ex[6];
							$data['class_number']=$ex[7];
							$data['class_num']=trim($ex[11]);
							$data['classify']='2';
							$data['teacher']=trim($ex[12]);
							D($dbname)->data($data)->add();
						}
					}
					$this->sortdata();
					fclose($myfile);
					$newway=$copy.time().D('relate')->count().".txt";
					rename($dataname,$newway);

					return $newway;
				}
				function sortdata(){
					$upsql="update `eletest` set `classify`='1' where `obligatory` like '%必修%'";
					D()->execute($upsql);
					$upsql="update `eletest` set `school`='八甲' where `school` like '%八甲%'";
					D()->execute($upsql);
					$upsql="update `eletest` set `school`='二坪' where `school` like '%二坪%'";
					D()->execute($upsql);
					$upsql="update `eletest` set `school`='二坪' where `school` like '%第一%'";
					D()->execute($upsql);
					$upsql="update `eletest` set `school`='八甲' where `school` like '%第二%'";
					D()->execute($upsql);
					$into="insert `elective` select * from eletest ";
					D("elective")->where(true)->delete();
					D()->execute($into);

				}
				function autoclass(){
					$box=["軍訓","日間","夜間","虛","體育","天","地","人"];
					$where="";
					foreach ($box as $key => $value) {
						$where.="`class` not like '%{$value}%' and ";
					}
					$elective=D("elective")->distinct('class')->field('left(class,3) as lastclass,class')->where($where." true")->select();
					//dump($elective);
					D('class')->where("true")->delete();
					foreach ($elective as $key => $value) {
						D('class')->data($value)->add();
					}
					$data['lastclass']="無科系";
					$data['class']="無班級";
					D('class')->data($data)->add();
					$this->autotype();
				}
				function autotype(){
					D()->execute("TRUNCATE TABLE class_type");
					$elective=D("elective")->distinct("obligatory")->field("obligatory")->select();
					
					foreach($elective as $key=>$value){
						D("class_type")->data($value)->add();
					}
				}
				function splitclass($ans){
					$ar=array("","一","二","三","四","五","六","日");
					$n=strpos($ans,")");
					$out="";
					while($n!=null){
						$ti=0;
						$a=substr($ans,strpos($ans,")")-3,3);
							//dump($a);
						for($i=1;$i<=7;$i++)if($a==$ar[$i])$ti=$i;
							if($ti!=0){
								if(substr($ans,strpos($ans,")")+3,1)=="-"){
									$x= (int)substr($ans,strpos($ans,")")+1,2);
									$y=(int)substr($ans,strpos($ans,")")+4,2);
									for($i=$x;$i<=$y;$i++)$out=$out.$ti."x".$i." ";
								}else{
									$x= (int)substr($ans,strpos($ans,")")+1,2);
									$out=$out.$ti."x".$x." ";
								}
							}
							$ans=substr($ans,strpos($ans,")")+1,strlen($ans));
							$n=strpos($ans,")");
						}
							//dump("out:".$out);
						return $out;
					}
				}
?>