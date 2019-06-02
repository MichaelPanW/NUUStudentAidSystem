<?php

class Cart_Ezship extends Think
{
	protected $su_id;
	protected $cvnFormat, $deliveryFormat, $ezFormat;
	
	public function __construct( $INsuid=NULL )
	{
		if( empty($INsuid) ){ return NULL; }
		$this->su_id = $INsuid;
	}
	
	/***
	 *
	 * 超商選擇
	 * V1.0
	 *
	 ***/
	public function getCvnStoreGNPHtml( $INorderId=NULL, $INrturl=NULL )
	{
		if( empty($INorderId)||empty($INrturl) ){ return false; }
		$this->cvnFormat = array(
		    "suID" => $this->su_id,           // 業主帳號
			"processID" => $INorderId,        // 訂單編號
			//"stCate" => "",                   // 門市通路代號
			//"stCode" => "",                   // 門市店面代號
			"rtURL" => $INrturl,              // 回傳頁面網址
			"webPara" => md5($this->su_id),       // 網站判別用代碼
		);
		$htmlStr = "";
		
		//$htmlStr = "<form action='https://www.ezship.com.tw/emap/rv_request_web.jsp' method='post' >";
		
		foreach($this->cvnFormat as $key=>$value){
			//$htmlStr .= "$key<BR>";
			$htmlStr .= "<input type='hidden' name='". $key ."' value='". $value ."' />";
		}
		
		/*$htmlStr .= "<input name='check2send' type='submit' value='選擇門市' />
		             </form>";*/
					 
		return $htmlStr;
	}
	
	/***
	 *
	 * getDeliveryHtml
	 * 宅配取貨-參數版
	 *
	 ***/
	/*public function getDeliveryHtml( $INpid=NULL, $INispay=true, $INamount=NULL, $INname=NULL, $INemail=NULL, 
	                                 $INmobile=NULL, $INaddr=NULL, $INzip=NULL, $INrturl=NULL )
	{			
		if( empty($this->su_id)||empty($INpid)||!isset($INamount)||empty($INname)||empty($INemail)||
		    empty($INmobile)||empty($INaddr)||empty($INzip)||empty($INrturl) ){ return false; }
		$this->deliveryFormat = array(
		    "su_id" => $this->su_id,                        // 業主帳號
			"order_id" => $INpid,                           // 訂單編號
			"order_status" => "A05",                        // 宅配訂單狀態
			"order_type" => ($INispay?"1":"3"),             // 宅配訂單
			"order_amount" => $INamount,                    // 代收金額或訂單金額
			"rv_name" => $INname,                           // 取件人姓名
			"rv_email" => $INemail,                         // 取件人電子郵件
			"rv_mobile" => $INmobile,                       // 取件人行動電話 
			//"st_code" => "",                           // 取件門市
			"rv_addr" => iconv("Big5", "UTF-8", $INaddr),   // 取件人收件地址
			"rv_zip" => $INzip,                             // 取件人郵遞區號
			"rtn_url" => $INrturl,                          // 回傳頁面網址
			"web_para" => md5($this->su_id),                // 網站判別用代碼
		);
		foreach($this->deliveryFormat as $k=>$v){
			if( empty($v)&&!isset($v) ){ return false; }
		}
		$htmlStr = "<form action='https://www.ezship.com.tw/emap/ezship_request_order_api.jsp' method='post' >";
		//$htmlStr = "<form action='". $_SERVER['PHP_SELF'] ."' method='post' >";
		
		foreach($this->deliveryFormat as $key=>$value){
			$htmlStr .= "$key<BR>";
			$htmlStr .= "<input name='". $key ."' value='". $value ."'><BR>";
		}
		
		$htmlStr .= "<input name='check2send' type='submit' value='確認送出' />
		             </form>";
					 
		return $htmlStr;
	}*/
	
	/***
	 *
	 * getEzHtml
	 * 整合版-參數版
	 *
	 ***/
	public function getEzHtml( $INpid=NULL, $INstatus=NULL, $INispay=true, $INamount=NULL, $INname=NULL, 
	                           $INemail=NULL, $INmobile=NULL, $INcode=NULL, $INaddr=NULL, $INzip=NULL, $INrturl=NULL )
	{			
		if( empty($this->su_id)||empty($INpid)||empty($INstatus)||!isset($INamount)||empty($INname)||empty($INemail)||
		    empty($INmobile)||empty($INrturl) ){ return false; }
			
		$htmlStr = "";
		
		$this->ezFormat = array(
		    "su_id" => $this->su_id,                        // 業主帳號
			"order_id" => $INpid,                           // 訂單編號
			"order_status" => $INstatus,                    // 宅配訂單狀態
			"order_type" => ($INispay?"1":"3"),             // 宅配訂單
			"order_amount" => $INamount,                    // 代收金額或訂單金額
			"rv_name" => $INname,                           // 取件人姓名
			"rv_email" => $INemail,                         // 取件人電子郵件
			"rv_mobile" => $INmobile,                       // 取件人行動電話 
			"st_code" => $INcode,                           // 取件門市
			"rv_addr" => $INaddr,                           // 取件人收件地址
			"rv_zip" => $INzip,                             // 取件人郵遞區號
			"rtn_url" => $INrturl,                          // 回傳頁面網址
			"web_para" => md5($this->su_id),                // 網站判別用代碼
		);
		/*foreach($this->ezFormat as $k=>$v){
			if( empty($v)&&!isset($v) ){ return false; }
		}*/
		//$htmlStr = "<form action='https://www.ezship.com.tw/emap/ezship_request_order_api.jsp' method='post' >";
		//$htmlStr = "<form action='". $_SERVER['PHP_SELF'] ."' method='post' >";
		
		foreach($this->ezFormat as $key=>$value){
			//$htmlStr .= "$key<BR>";
			$htmlStr .= "<input type='hidden' name='". $key ."' value='". $value ."' />";
		}
		
		//$htmlStr .= "<input name='check2send' type='submit' value='確認送出' /></form>";
					 
		return $htmlStr;
	}
}

?>