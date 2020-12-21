<?php

namespace Studentaid\Controller;

use Think\Controller;

class WorkController extends GlobalController
{
	public function _initialize()
	{
		parent::_initialize();
	}

	public function index()
	{
		$ar = array(
			array("", "一", "二", "三", "四", "五", "六"),
			array("08：00～08：50", " ", " ", " ", " ", " ", " "),
			array("09：00～09：50", " ", " ", " ", " ", " ", " "),
			array("10：10～11：00", " ", " ", " ", " ", " ", " "),
			array("11：10～12：00", " ", " ", " ", " ", " ", " "),
			array("13：00～13：50", " ", " ", " ", " ", " ", " "),
			array("14：10～14：50", " ", " ", " ", " ", " ", " "),
			array("15：10～16：00", " ", " ", " ", " ", " ", " "),
			array("16：10～17：00", " ", " ", " ", " ", " ", " "),
			array("17：10～18：00", " ", " ", " ", " ", " ", " "),
			array("18：20～19：05", " ", " ", " ", " ", " ", " "),
			array("19：10～19：55", " ", " ", " ", " ", " ", " "),
			array("20：05～20：50", " ", " ", " ", " ", " ", " "),
			array("20：55～21：40", " ", " ", " ", " ", " ", " ")
		);
		$chclass = '';
		$num = 0;
		if (!is_null(Session('id'))) {
			$user = D('user')->where('id=' . Session('id'))->select();
			$user = $user['0'];
			$class = $user['class'];
			$conbox = explode('@', $user['content']);
			$where = "";
			foreach ($conbox as $vo) {
				if ($vo != '') {
					$where .= "`class_number`='" . $vo . "' or ";
				}
			}
			$elective = D('elective')->where($where . " false")->select();
			foreach ($elective as $key => $post) {


				$ex1 = explode(" ", $post['class_sit']);
				if ($chclass != $post['class_name']) {
					$num += $post['class_num'];
					$rcolor = $this->randColor();
					for ($i = 0; $i < count($ex1) - 1; $i++) {

						$ex2 = explode("x", $ex1[$i]);
						if ($post['classify'] == '1') {
							$ar[$ex2[1]][$ex2[0]] = "[必]" . $post['class_name'];
							$ac[$ex2[1]][$ex2[0]] = $rcolor;
						} else {
							$ar[$ex2[1]][$ex2[0]] = "[選]" . $post['class_name'];
							$ac[$ex2[1]][$ex2[0]] = $rcolor;
						}
					}
				}
				$elective[$key]['color'] = $rcolor;
				$chclass = $post['class_name'];
			}
		} else {

			if (isset($_GET['department']) && isset($_GET['olddata'])) {
				$class = $_GET['department'];
			} else {
				if (isset($_POST['class'])) {
					$class = $_POST['class'];
				} else {
					$this->redirect('Index/index');
				}
			}
			$elective = D('elective')->where("class='{$class}' && `classify`='1'")->select();
			$chclass = "";
			foreach ($elective as $key => $post) {


				$ex1 = explode(" ", $post['class_sit']);
				if ($chclass != $post['class_name']) {
					$num += $post['class_num'];
					$rcolor = $this->randColor();
					for ($i = 0; $i < count($ex1) - 1; $i++) {

						$ex2 = explode("x", $ex1[$i]);
						$ar[$ex2[1]][$ex2[0]] = "[必]" . $post['class_name'];
						$ac[$ex2[1]][$ex2[0]] = $rcolor;
					}
				}
				$elective[$key]['color'] = $rcolor;
				$chclass = $post['class_name'];
			}
		}

		if ((!(isset($_GET['olddata']))) || $class != "") {
			if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
				$myip = $_SERVER['HTTP_CLIENT_IP'];
			} else if (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
				$myip = $_SERVER['HTTP_X_FORWARDED_FOR'];
			} else {
				$myip = $_SERVER['REMOTE_ADDR'];
			}
			$data->date = date("Y/m/d");
			$data->time = date("h:i:s");
			$data->dataname = $class;
			$data->ip = $myip;
			D('statistics')->data($data)->add();
		}
		$class_type = D("class_type")->select();
		//dump($class_type);
		$this->assign("class_type", $class_type);
		$this->assign("elective", $elective);
		$this->assign("myclass", $elective);
		$this->assign("num", $num);
		$this->assign("class", $class);
		$this->assign("ar", $ar);
		$this->assign("ac", $ac);
		$this->display();
	}
	public function sit_post()
	{
		if (isset($_POST['name']) && isset($_POST['ob'])) {

			$id = $_POST['name'];
			$ob = $_POST['ob'];
			$classdata = D('elective')->where("`class_number`='" . $id . "'")->select();
			foreach ($classdata as $post) {
				echo $post['class_name'] . " ";
				echo $post['class_num'] . " ";
				echo $post['class_sit'];
			}
		}
	}
	public function name_post()
	{
		//dump($_POST);
		if (isset($_POST['id']) && isset($_POST['ob']) && isset($_POST['classname'])) {


			$id = $_POST['id'];
			//$class=mb_substr($_POST['classname'],0,3,"UTF-8");//班級名稱
			$class = $this->csubstr($_POST['classname'], 0, 3);
			$ob = $_POST['ob'];
			list($x, $y) = explode('x', $id);

			$ser = "";
			$ler = "";
			if ($x != 0) { //有沒有選課堂時間
				if ($y != 0) {
					$ser = "where `class_sit` like '%$id %'";
				} else {
					$ser = "where `class_sit` like '%" . $x . "x%'";
				}
			} else {
				if ($y != 0) {
					$ser = "where `class_sit` like '%x$y %'";
				} else {
					$ser = "where `class_sit` like '%'";
				}
			}
			if ($ob == "班級選必修") {

				$ler .= " && class like '%{$_POST['classname']}%'";
			} elseif ($ob == "全部課程") {
			} else {

				$ler .= "&& `obligatory` LIKE '%{$_POST['ob']}%'";
			}

			if ($_POST['ob'] == "主系必修" || $_POST['ob'] == "主系選修") {
				$ler .= "&& `class` LIKE '%{$class}%'";
			}
			$sqltag = ["class_name", "class_sitname", "class", "class_number"];
			$splitSeach = explode(" ", $_POST['search']);
			$searchcode = "&& ( false ";
			foreach ($splitSeach as $skey => $stag) {
				if ($stag == "二坪" || $stag == "八甲") continue;
				foreach ($sqltag as $key => $tag) {
					$searchcode .= " || `{$tag}` LIKE '%{$stag}%'";
				}
			}
			$ler .= $searchcode . " )";
			foreach ($splitSeach as $skey => $stag) {
				if ($stag == "二坪" || $stag == "八甲")
					$ler .= " && `school` LIKE '%{$stag}%'";
			}


			$sql = "SELECT * FROM `elective` " . $ser . $ler;
			//echo $sql;
			$posts = D()->query($sql);
			$posts = $this->colorsit($posts);
			$this->assign('count', count($posts));
			$this->assign('cho', $posts);
			$this->display();
		}
	}

	function csubstr($string, $start, $length)
	{
		$str = "";
		$len = $start + $length;

		for ($i = $start; $i < $len; $i++) {
			if (ord(substr($string, $i, 1)) > 0xa0) {
				$str .= substr($string, $i, 2);
				$i++;
			} else {
				$str .= substr($string, $i, 1);
			}
		}
		return $str;
	}
	public function colorsit($db)
	{

		foreach ($db as $key => $value) {
			if ($value['school'] == "二坪") {
				$db[$key]['color'] = "#d32f2f";
			} else {

				$db[$key]['color'] = "#00796b";
			}
		}
		return $db;
	}
	public function uplist()
	{
		$sql = "";
		if (isset($_POST['name'])) {

			$name = $_POST['name'];

			$box = explode('@', $name);
			foreach ($box as $vo) {
				$sql .= " `class_number` = '" . $vo . "' ||";
			}
			$sql .= ' false';
			$data = D('elective')->where($sql)->select();
			foreach ($data as $post) {
				echo "<tr><td BGColor='#ffffff'>{$post['class']}</td>
					<td BGColor='#ffffff'>{$post['obligatory']}</td>
					<td BGColor='#ffffff'>{$post['class_name']}</td>
					<td BGColor='#616161' style='color:#fff'>{$post['class_number']}</td>
					
						<td BGColor='#ffffff'>{$post['teacher']}</td>
						<td BGColor='#ffffff'>{$post['class_num']}</td>
						<td BGColor='#ffffff'><a href='https://www.dcard.tw/search?forum=nuu&query={$post['teacher']}' target=
						'_blank'><input type='button' value='評價' class='loginbu' /></a></td></tr>";
			}
		}
	}
	public function check()
	{

		if (!empty($_POST['UID']) && !empty($_POST['pass'])) {
			$account = $_POST['UID'];
			$pass = $_POST['pass'];
			$myda = $_POST['content'];
			$cl = $_POST['cl'];
			$check = false;

			//驗證資料
			$ch = curl_init();
			$options = array(
				CURLOPT_RETURNTRANSFER => 1,
				//CURLOPT_SSL_VERIFYHOST => 0,
				//CURLOPT_SSL_VERIFYPEER => 0,
				CURLOPT_URL => "http://www3.nuu.edu.tw/~auser/APauth/LDAPCheck.php",
				//CURLOPT_URL => "http://elearning.nuu.edu.tw/sso_login.php",
				CURLOPT_POST => true,
				CURLOPT_POSTFIELDS => http_build_query(array("userID" => $_POST['UID'], "passwd" => $_POST['pass']))
				//CURLOPT_POSTFIELDS => http_build_query( array( "userID"=>"asd", "passwd"=>"asd", "apID" => "DocPeper", "apPWD" => "Doc15hAn"))
			);
			curl_setopt_array($ch, $options);
			$data = curl_exec($ch);
			curl_close($ch);
			//分析資料
			$loginname = "";
			$userID = "";
			for ($i = strpos($data, "loginname") + 19; $data[$i] != "\"" && $i < strlen($data) - 1; $i++) {
				$loginname .= $data[$i];
			} //for end
			for ($i = strpos($data, "userID") + 16; $data[$i] != "\"" && $i < strlen($data) - 1; $i++) {
				$userID .= $data[$i];
			} //for end
			if ($loginname == "NuuError" || $userID == "NuuError") $check = false;
			else if ($loginname == "" || $userID == "") $check = true;
			else echo "error";
			if (D('user')->where("account='" . $account . "' and password='" . md5($pass) . "'")->count() == 0) {
				if ($check) {
					$datae->account = $account;
					$datae->password = md5($pass);
					$datae->content = $myda;
					$datae->class = $cl;
					$myid = D('user')->data($datae)->add();
					Session('id', $myid);
					echo '1';
				} else {
					echo '0';
				}
			} else {
				$datae->content = $myda;
				$datae->class = $cl;
				D('user')->where("account='" . $account . "'")->data($datae)->save();
				$user = D('user')->where("account='" . $account . "'")->find();

				Session('id', $user['id']);
				echo '2';
			}
		} //if end

	}
	public function save()
	{

		if (!empty($_POST['content']) && !empty($_POST['content'])) {
			$datae->content = $_POST['content'];
			D('user')->where("id='" . session('id') . "'")->data($datae)->save();
		} //if end

	}


	function randColor()
	{

		$bg = array("#444444", "#000000", "#A20055", "#8C0044", "#AA0000", "#880000", "#C63300", "#A42D00", "#CC6600", "#BB5500", "#AA7700", "#886600", "#BBBB00", "#888800", "#88AA00", "#668800", "#55AA00", "#227700", "#00AA00", "#008800", "#00AA55", "#008844", "#00AA88", "#008866", "#00AAAA", "#008888", "#0088A8", "#007799", "#003C9D", "#003377", "#0000AA", "#000088", "#2200AA", "#220088", "#4400B3", "#3A0088", "#66009D", "#550088", "#7A0099", "#660077", "#990099", "#770077");
		$pos = rand(0, count($bg) - 1);


		return $bg[$pos];
	}
	/**
	 * 退出登录
	 *
	 */
	public function logout()
	{
		Session(null);
		$this->success('登出成功', u('Index/index'));
	}
	public function test()
	{
		dump("hello");
		$account = 'M0633010';
		$pass = '128416829';
		$check = false;
		$data = file_get_contents("http://www3.nuu.edu.tw/~auser/APauth/LDAPCheck.php?userID=" . $account . "&passwd=" . $_POST['pass']);
		dump($data);
		/*
					//驗證資料
				$ch=curl_init();
				$options=array(
					CURLOPT_RETURNTRANSFER => 1,
					//CURLOPT_SSL_VERIFYHOST => 0,
					//CURLOPT_SSL_VERIFYPEER => 0,
					CURLOPT_URL => "http://www3.nuu.edu.tw/~auser/APauth/LDAPCheck.php",
					//CURLOPT_URL => "http://elearning.nuu.edu.tw/sso_login.php",
					CURLOPT_POST => true,
					CURLOPT_POSTFIELDS => http_build_query( array( "userID"=>$_POST['UID'], "passwd"=>$_POST['pass']))
					//CURLOPT_POSTFIELDS => http_build_query( array( "userID"=>"asd", "passwd"=>"asd", "apID" => "DocPeper", "apPWD" => "Doc15hAn"))
					);
				curl_setopt_array($ch, $options);
				$data=curl_exec($ch);
				curl_close($ch);
				dump($data);*/
	}
}
