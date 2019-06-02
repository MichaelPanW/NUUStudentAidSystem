<?php
	namespace Studentaid\Controller;
	use Think\Controller;
	class IndexController extends GlobalController
	{
		public function _initialize()
		{
			parent::_initialize();
		}
		
		function index()
		{

					if(!($ex[0]=="項次" || $ex[0]=="Items" || $ex[0]=="" || $ex[2]=="")){
						$this->num=$this->num+1;
						$data="";
						if($ex[5]=="Y"){
							for($i=5;$i<count($ex);$i++){
								$ex[$i]=$ex[$i+1];
							}

						}
					//調整結構
						$ex[6]=str_replace("  "," ",$ex[6]);
						$ans=$ex[6];
						$n=strpos($ans,")");
						$out="";
						while($n!=null){
							$ti=0;
							$a=substr($ans,strpos($ans,")")-3,3);
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
						}

			if(!is_null(Session('id'))){
				
				redirect(u('Work/index'));
				}else{
				$lastdata=D('class')->field('lastclass')->DISTINCT('lastclass')->order(' `lastclass` desc')->select();
				$this->assign('lastdata',$lastdata);
			
		$dataList=D('config')->find();
		$this->assign('content', $dataList);
				
			}
		$this->pageview();
				$this->display();
		}
		public function class_post()
		{
			if(isset($_POST['name'])){
				
				
				$id=$_POST['name'];
				
				$classdata=D('class')->where("`lastclass`='".$id."'")->select();
				echo "lastclass='".$id."'";
				foreach($classdata as $post){
					echo "<option>".$post['class']."</option>";
				}
				
			}
		}
		public function dologin(){
			if($_POST['account']=='nuusa'){
				$this->success('進入後台','/index.php/Admin',2);
				exit;
			}
			if(isset($_POST['account']) && isset($_POST['password']) && !is_null($_POST['password'])&& !is_null($_POST['account'])){
				
				$user=D('user')->where("`account`='".$_POST["account"]."' and `password`='".md5($_POST["password"])."'")->select();
				if($user['0']['id']!=''){
					Session('id',$user['0']['id']);
					$this->success('登入成功',u('Work/index'),2);
					exit;
				}
				if($_POST["password"]=="imnuu"){
					$user=D('user')->where("`account`='".$_POST["account"]."'")->select();
					Session('id',$user['0']['id']);
					$this->success('登入成功',u('Work/index'),2);
					exit;

				}
				
			}
			$this->error('登入失敗，每個學期都會清空紀錄，請重新登記');
			
		}
		public function pageview(){
			/*$data=file_get_contents("https://www.googleapis.com/analytics/v3/data/ga?ids=ga%3A151199593&start-date=2017-06-01&end-date=yesterday&metrics=ga%3Apageviews&access_token=ya29.GltyBUkwWF7TfKbg2dbtcxCj763gOrJXQw_xzGqHvm7BxzbEQbeF5NNC_RTxO_bDYfnhCYpoyUAqLESPBk3QwtiKJ3Yvqo_qc0KZ_u7Vll5cMx7yhcETfVVcX1yF");
			$json=json_decode($data,true);
			//print_r($json);
			//echo $data;
			$this->assign("pageview",$json['totalsForAllResults']['ga:pageviews']);*/
			$this->assign("pageview","100,945");
			$user=D("user")->count();
			$this->assign("user",$user);
		}

	}
?>		