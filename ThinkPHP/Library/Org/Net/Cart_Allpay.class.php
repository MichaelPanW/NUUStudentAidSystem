<?php

class Cart_Allpay extends Think
{
	/********************************************************************************************/
	// 交易網址(測試環境)
	// 會員登入測試: https://login-stage.allpay.com.tw/OpenID/Login
	// 會員登入正式網址: https://login.allpay.com.tw/OpenID/Login
	// 刷卡測試網址: 
	// 刷卡正式網址: 
	/*$gateway_url = "http://payment-stage.allpay.com.tw/Cashier/AioCheckOut";
	// 廠商編號
	$merchant_id = "1130774";
	// HashKey
	$hash_key = "6MKPaM08XBhrmmBu";
	// HashIV
	$hash_iv = "mBXMPuZXYynMtbXF";*/
	/********************************************************************************************/
	protected $gateway_url, $merchant_id, $hash_key, $hash_iv, $login_back_url;
	
	// 廠商傳送至會員登入介接 
	/*$login_format = array(
	    // 廠商編號
	    "MerchantID"     => $merchant_id,
		// 時戳 
		"TimeStamp"      => time(),
		// 導回廠商 URL
		"LoginBackUrl"   => "http://2535.com.tw/"
	);*/
	
	// 廠商傳送至會員登入介接, 回傳參數
	/*$login_back = array(
	    // 會員標記
	    "Token"       => "",
		// 時戳
		"TimeStamp"   => "",
		// 回傳代碼 
		"RtnCode"     => "",
		// 回傳訊息
		"RtnMsg"      => ""
	);*/
	
	// 取得會員資訊
	/*$vip_format = array(
	    // 會員編號
	    "MerchantID"   => $merchant_id,
		// 加密參數
		"OpenData"     => $open_data,
	);*/
	
	// 取得會員資訊, 回傳參數
	/*$vip_back = array(
	    // 會員識別碼
	    "AccountID"       => "",
		// 回傳代碼
		"RtnCode"     => "",
		// 回傳訊息
		"RtnMsg"      => ""
	);*/
	
	//
	protected $cashier_format;
	
	public function __construct( $INmid=NULL, $INhkey=NULL, $INhiv=NULL )
    {
        if( empty($INmid) || empty($INhkey) || empty($INhiv) ){ return NULL; }
		
		$this->merchant_id = $INmid;
		$this->hash_key = $INhkey;
		$this->hash_iv = $INhiv;
    }
	
	// 設定連結網址, true: 測試網址, false: 正式網址
	public function setLogin( $INbackurl=NULL, $INistest=false )
	{
		if(empty($INbackurl)){
			$this->login_back_url = $INbackurl;
		}else{
			return false;
		}
		$this->gateway_url = ( $INistest?"https://login-stage.allpay.com.tw/OpenID/Login":"https://login.allpay.com.tw/OpenID/Login" );
		return true;
	}
	
	// 取得操作的 html 程式碼
	public function getLoginHtml()
	{
		// structure
		/*$login_format = array(
		    // 會員編號
	        "MerchantID"     => $merchant_id,
			// 時戳 
			"TimeStamp"      => "",
			// 導回廠商 URL
			"LoginBackUrl"   => ""
		);*/
		/*$html_code = "<form target='_blank' method='post' action='http://anson.dayfor.net/test/show.php' onsubmit='javascript:document.getElementById(\"TStime\").value=Math.floor(Date.now()/1000);' >
		              <input type='text' readonly='readonly' name='MerchantID' value='". $this->merchant_id ."' />
					  <input type='text' id='TStime' name='TimeStamp' value='' />
					  <input type='text' readonly='readonly' name='LoginBackUrl' value='". $this->login_back_url ."' />
					 ";*/
		
		$html_code = "<form target='_blank' method='post' action='". $this->gateway_url ."' onsubmit='javascript:document.getElementById(\"TStime\").value=Math.floor(Date.now()/1000);' >
		              <input type='hidden' readonly='readonly' name='MerchantID' value='". $this->merchant_id ."' />
					  <input type='hidden' id='TStime' readonly='readonly' name='TimeStamp' value='". $this->merchant_id ."' />
					  <input type='hidden' readonly='readonly' name='LoginBackUrl' value='". $this->login_back_url ."' />
					 ";
		$html_code .= "<input type='submit' value='Send' />
		               </form>
					  ";
		return $html_code;
	}
	
	public function setCashier( $INorderId, $INtotalAmt, $INtradeDesc, $INitemName, $INtradeNo, $INbuyerE, $INbuyerP, $INbuyerU )
	{
		$INpayment = "ALL";
		$this->cashier_format = array(
		    "MerchantID" => $this->merchant_id,           // 廠商編號
		    "MerchantTradeNo" => $INtradeNo,              // 廠商交易編號
		    "MerchantTradeDate" => date("Y/m/d H:i:s"),   // 廠商交易時間
		    "PaymentType" => "aio",                       // 交易類型
			
	        "TotalAmount" => $INtotalAmt,                 // 交易金額 
		    "TradeDesc" => $INtradeDesc,                  // 交易描述
		    "ItemName" => $INitemName,                    // 商品名稱
			
            "ReturnURL" => "http://2535.com.tw/index.php/Cat/allpayCheck",                   // 付款完成通知回傳網址(背景)
            "ChoosePayment" => "ALL",                     // 選擇預設付款子項目
            "ClientBackURL" => "http://2535.com.tw/index.php/Cat/cat_delivery2/ofid/".$INorderId,               // Client 端返回廠商網址 
			//"ItemURL" => "",                              // 商品銷售網址
			//"Remark" => "",                               // 備註欄位
			//"ChooseSubPayment" => "",                     // 選擇預設付款子項目 
			//"OrderResultURL" => "",                       // Client 端回傳付款結果網址 (ClientBackURL 會失效)
	        "NeedExtraPaidInfo" => "Y",                   // 額外付款資訊
			//"DeviceSource" => "P",                        // 裝置來源 
			"IgnorePayment" => "WebATM#ATM#CVS#BARCODE#Alipay#Tenpay#TopUpUsed",                        // 隱藏付款方式
			//"PlatformID" => "",                           // 特約合作平台商代號 
			//"InvoiceMark" => "",                          // 電子發票開註記
			//"HoldTradeAMT" => "0",                        // 是否延遲撥款 
			//"EncryptType" => "",                          // CheckMacValue 加密類型 
		);
		
		switch( $INpayment ){
			case "ALL":
			    $this->cashier_format += array(
				    "AlipayItemName"      => "服飾",   // 商品名稱 
					"AlipayItemCounts"    => "1",   // 商品購買數量
					"AlipayItemPrice"     => $INtotalAmt,   // 商品單價
					"Email"               => $INbuyerE,   // 購買人信箱
					"PhoneNo"             => $INbuyerP,   // 購買人電話
					"UserName"            => $INbuyerU    // 購買人姓名
				);
			    break;
			case "Credit":
			    // Credit 一般信用卡付款方式
			    $this->cashier_format += array(
				    "CreditInstallment"   => "",   // 信用卡分期
			        "InstallmentAmount"   => "",   // 分期付款金額
		        	"Redeem"              => "",   // 信用卡紅利折抵
			        "UnionPay"            => "",   // 銀聯卡
			        "Language"            => ""    // 語系設定
				);
			    break;
			default:
			    return false;
		}
		
		return true;
	}
	
	public function getCashierHtml()
	{
		//$html_code = "<form target='_blank' method='post' action='http://anson.dayfor.net/test/show.php' >";
		$html_code = "";// "<form target='_blank' method='post' action='https://payment-stage.allpay.com.tw/Cashier/AioCheckOut' >";
		$bufAry = $this->_sortAry($this->cashier_format);
		
		foreach( $bufAry as $key=>$value ){
			//$html_code .= $key ."<BR>";
			$html_code .= "<input type='hidden' ". ($key=="MerchantTradeDate"?"id='CashTime'":"") ." name='". $key ."' value='". $value ."' ><BR>";
		}
		
		//$html_code .= "<input type='submit' value='Send' /></form>";
		/*$html_code .= "<input type='submit' value='送出' onClick='javascript:var t=new Date();document.getElementById(\"CashTime\").value=t.getFullYear()+\"/\"+(t.getMonth()<9?\"0\":\"\")+(t.getMonth()+1)+\"/\"+(t.getDate()<10?\"0\":\"\")+t.getDate()+\" \"+(t.getHours()<9?\"0\":\"\")+t.getHours()+\":\"+(t.getMinutes()<9?\"0\":\"\")+t.getMinutes()+\":\"+(t.getSeconds()<9?\"0\":\"\")+t.getSeconds();
					   ' />
		               </form>";*/
		return $html_code;
		
	}
	
	/***
     *
     * _replaceChar
	 * 特殊字元置換
	 *
	 ***/
	protected function _replaceChar($value)
	{
		$search_list = array('%2d', '%5f', '%2e', '%21', '%2a', '%28', '%29');
		$replace_list = array('-', '_', '.', '!', '*', '(', ')');
		$value = str_replace($search_list, $replace_list ,$value);
		
		return $value;
	}
	
	/***
     *
     * _getMacValue
	 * 產生檢查碼
	 *
	 ***/
	protected function _getMacValue($hash_key, $hash_iv, $form_array)
	{
		$encode_str = "HashKey=" . $hash_key;
		foreach ($form_array as $key => $value)
		{
			$encode_str .= "&" . $key . "=" . $value;
		}
		$encode_str .= "&HashIV=" . $hash_iv;
		$encode_str = strtolower(urlencode($encode_str));
		$encode_str = $this->_replaceChar($encode_str);

		return md5($encode_str);
	}
	
	/***
     *
     * _sortAry
	 * 調整ksort排序規則--依自然排序法(大小寫不敏感)
	 *
	 ***/
	protected function _sortAry($INform_array)
	{
		ksort($INform_array, SORT_NATURAL |SORT_FLAG_CASE);
		// 取得 Mac Value
	    $INform_array['CheckMacValue'] = $this->_getMacValue($this->hash_key, $this->hash_iv, $INform_array);
		return $INform_array;
	}
	
	/***
     *
     * _encode_open_key
	 * 返回加密過 JSON 格式的資料 (取得會員資訊)
	 *
	 ***/
	protected function _encode_open_key($INtoken, $INopen_key)
	{
		import("ORG.Net.AESCrypt");
		$aes = new AESCrypt($hash_key, $hash_iv);
		return base64_encode($aes->encrypt( json_encode( array("Token"=>$INtoken, "OpenKey"=>$INopen_key, "TimeStamp"=>time()) ) ));
	}
	
	public function isCheckMacValue( $INresponse )
	{
		$checkBuf = $INresponse['CheckMacValue'];
		unset( $INresponse['CheckMacValue'] );
		$trialBuf = $this->_sortAry( $INresponse );
		
		return (strtoupper($trialBuf['CheckMacValue'])==$checkBuf?true:false);
	}
	
}

?>