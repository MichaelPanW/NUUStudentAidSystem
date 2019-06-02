<?php 
/**
 * 
 * Index(后台首页)
 *
 * @package      	photonicCMS
 */
 	namespace Admin\Controller;
	use Think\Controller;
class IndexController extends GlobalController{
   function _initialize()
    {
	
        parent::_initialize();
        $adminId = parent::_getAdminUid();
        $username = parent::_getAdminName();
        $roleId = parent::_getRoleId();
        // if (!$roleId || !$adminId) $this->redirect(U('Public/login'));
        $this->assign('adminId', $adminId);
        $this->assign('username', $username);
        $this->assign('security', $security);
        parent::_checkPermission();

    }

    /**
     * 后台管理首页
     *
     */
    public function index()
    {
		
        //$item = 1;

		//$Model  = new Model();
        //$record = $Model->query( "select * from article where id= " . $item);
		//echo $Model->GetLastSql();
        //if (empty($item) || empty($record)) parent::_message('error', '記錄不存在');
        //$this->assign('vo', $record[0]);
		
		//$sta=D()->query("SELECT  DISTINCT date,(select count(*) from statistics where date=s.date) count  FROM statistics s");
		//$date=D('statistics')->distinct('date')->field('date')->order('date')->select();
		$statistics=D('statistics')->field('date,dataname')->order('date')->select();
		foreach($statistics as $key =>$vo){
			$date[$vo['date']]['name']=$vo['date'];
			$date[$vo['date']]['value']=$date[$vo['date']]['value']+1;
			}
		$num=D()->query("select count(*) today,(select count(*) from statistics) total from statistics where date='".date("Y/m/d")."'");
		$news=D('user')->count();
		$this->assign("news",$news);
		$this->assign("date",$date);
		$this->assign("num",$num[0]);
        $this->display();
    }

}
