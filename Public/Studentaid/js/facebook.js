
  	/********************************************************************************************
	facebook登入
	********************************************************************************************/
	var fb_name;//使用者姓名
	var fb_id;//使用者id
    /*顯示facebook登入icon
	input:face 介面1.原版 2.無提示版3.自定義版
			id 物件id
	output:登入按鈕*/
  function show_fb_login(id,face){
	switch(face){
		case 1:
			document.write("<fb:login-button scope='public_profile,email' onlogin='checkLoginState();' id='"+id+"'></fb:login-button><div id='status'></div>");
			break;
		case 2:
			document.write("<fb:login-button scope='public_profile,email' onlogin='checkLoginState();' id='"+id+"'></fb:login-button><div id='status' style='display:none'></div>");
			break;
		case 3:
			document.write("<div scope='public_profile,email' onlogin='checkLoginState();' id='"+id+"'>facebook</div><div id='status' style='display:none'></div>");
			break;
		default:
			document.write("<fb:login-button scope='public_profile,email' onlogin='checkLoginState();' id='"+id+"'></fb:login-button><div id='status'></div>");
	}
	
  }

  //檢查登入狀態
  function statusChangeCallback(response) {
    if (response.status === 'connected') {//已登入
      fb_get_value();
    } else if (response.status === 'not_authorized') {//登入facebook 未登入系統
      document.getElementById('status').innerHTML = '請登入這個系統';
    } else {//未登入facebook
      document.getElementById('status').innerHTML = '請登入facebook';
    }
  }

	//檢查登入
  function checkLoginState() {
    FB.getLoginStatus(function(response) {
      statusChangeCallback(response);
    });
  }
	//登入設定

  window.fbAsyncInit = function() {
    FB.init({
      appId            : '431779660362216',
      autoLogAppEvents : true,
      xfbml            : true,
      version          : 'v3.3'
    });
  };
 
  // 登入設定檔
  (function(d, s, id) {
    var js, fjs = d.getElementsByTagName(s)[0];
    if (d.getElementById(id)) return;
    js = d.createElement(s); js.id = id;
    js.src = "//connect.facebook.net/zh_TW/sdk.js";
    fjs.parentNode.insertBefore(js, fjs);
  }(document, 'script', 'facebook-jssdk'));

  //登入成功時
  function fb_get_value() {
    FB.api('/me', function(response) {
      document.getElementById('status').innerHTML =
        '歡迎您的使用, ' + response.name + '!';
		myname= response.name;
		myid= response.id;
    });
  }
	
	/********************************************************************************************
	facebook登出
	********************************************************************************************/	
	
   /*顯示facebook登出icon
	input:id 物件id
	output:登出按鈕*/
  function show_fb_logout(id){
		document.write("<div id='"+id+"' onclick='fb_logout()'>登出</div>");
  }
  
  	//登出
	function fb_logout(){
	  FB.logout(function(response) {
		checkLoginState();
	});
	}
	
	
	/********************************************************************************************
	facebook分享
	********************************************************************************************/	
	
   /*顯示facebook登出icon
	input:id 物件id
			layout 介面 1.上有分享數 2.內有分享數3.無分享數
			size 按鈕大小兩種 small、large
			href 分享連結
	output:登出按鈕*/
  function show_fb_share(id,layout,size,href){
		if(size=="")size="small";
		if(href=="")href=location.href;
	switch(layout){
		case 1:
			document.write("<div class='fb-share-button' id='"+id+"' data-href='"+href+"' data-layout='box_count' data-size='"+size+"'></div>");
			break;
		case 2:
			document.write("<div class='fb-share-button' id='"+id+"' data-href='"+href+"' data-layout='button_count' data-size='"+size+"'></div>");
			break;
		case 3:
			document.write("<div class='fb-share-button' id='"+id+"' data-href='"+href+"' data-layout='button' data-size='"+size+"'></div>");
			break;
		default:
			document.write("<div class='fb-share-button' id='"+id+"' data-href='"+href+"' data-layout='button_count' data-size='"+size+"'></div>");
	}
  }
	  //分享資訊
  (function(d, s, id) {
	  var js, fjs = d.getElementsByTagName(s)[0];
	  if (d.getElementById(id)) return;
	  js = d.createElement(s); js.id = id;
	  js.src = "//connect.facebook.net/zh_TW/sdk.js#xfbml=1";
	  fjs.parentNode.insertBefore(js, fjs);
	}(document, 'script', 'facebook-jssdk'));
	
	/********************************************************************************************
	facebook留言
	********************************************************************************************/	
	
   /*顯示facebook登出icon
	input:id 物件id
			width 留言寬度
			numposts 留言數
			href 留言連結
	output:留言畫面*/
	
  function show_fb_talks(id,width,numposts,href){
  
		if(href=="")href=location.href;
		if(width==""|| width==0)width=500;
		if(numposts=="" || numposts==0)numposts=5;
		document.write("<div class='fb-comments' id='"+id+"' data-href='"+href+"' data-width="+width+" data-numposts="+numposts+"></div></div>");

  }
	 //留言資訊
	(function(d, s, id) {
	  var js, fjs = d.getElementsByTagName(s)[0];
	  if (d.getElementById(id)) return;
	  js = d.createElement(s); js.id = id;
	  js.src = "//connect.facebook.net/zh_TW/sdk.js#xfbml=1&version=v2.7&appId=431779660362216";
	  fjs.parentNode.insertBefore(js, fjs);
	}(document, 'script', 'facebook-jssdk'));

	
	/********************************************************************************************
	facebook粉絲團
	********************************************************************************************/		
	
	   /*顯示facebook粉絲團資訊
			input:id 物件id
				width 寬度
				height 高度
				href 粉絲團連結
			output:粉絲團資訊*/
	
	  function show_fb_fans(id,width,height,href){
	  
			if(href=="")href="https://www.facebook.com/文化珍珠鍊-苗栗文創跨域再建與推廣-830069307098218";
			
			document.write("<div class='fb-page' data-href='"+href+"' data-tabs='timeline' data-width="+width+" data-height="+height+" 	data-small-header='false'data-adapt-container-width='true' data-hide-cover='false' data-show-facepile='false'><blockquote cite='https://www.facebook.com/%E5%82%B3%E8%A8%8A%E5%85%89%E7%A7%91%E6%8A%80-358077690789/?fref=ts' class='fb-xfbml-parse-ignore'><a href='"+href+"'></a></blockquote></div>");
	  }
	 
	//粉絲團設定資訊
	(function(d, s, id) {
	  var js, fjs = d.getElementsByTagName(s)[0];
	  if (d.getElementById(id)) return;
	  js = d.createElement(s); js.id = id;
	  js.src = "//connect.facebook.net/zh_TW/sdk.js#xfbml=1&version=v2.7&appId=431779660362216";
	  fjs.parentNode.insertBefore(js, fjs);
	}(document, 'script', 'facebook-jssdk'));
	
	
	function facebook_post(){
		FB.api(
			"/me/feed",
			"POST",
			{
				"message": "This is a test message"
			},
			function (response) {
			  if (response && !response.error) {
				/* handle the result */
			  }
			}
		);
	}

