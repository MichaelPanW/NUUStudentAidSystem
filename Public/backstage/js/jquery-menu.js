<!--主選單jq-->
$(document).ready(function(){
//----------- 菜單點擊滑動 ------------------//  
  //指定.menu ul li .panel為content
  var content=$('.nav');
	  //點擊時隱藏所有div.panel
	  content.hide();
  //當滑鼠在li上面時就使用滑出視窗
  $('.iteMenu').click(function(){
	  //把選擇的索引值存入title
	  //var title=$('.iteMenu').index($(this));
	  //把索引值帶入對應div參數中並顯示其內容
	  $(content).show().animate(
		{"width":"340"},
		{queue:false,
		   duration:500}
		).children().fadeIn(1000)});
	  
//---------------- 關閉按鈕 ------------------//	  
  $('#btnclose').click(function(){
			  content.animate(
		        {width:0,
			     queue:true,
			     duration:500},
			     function(){ $(this).hide(); }
		    );
	});

//---------------- 子選單 ------------------//	  
  $(".iteNav > ul > li > a").click(function(){
		  var _this=$(this);
		  if(_this.next("ul").length>0){
			  if(_this.next().is(":visible")){
				  
				  _this.html(_this.html()).next().slideUp().hide();
			  }else{
				  
				  _this.html(_this.html()).next().slideToggle().show();
			  }
			 
			  return false;
		  }
	  });
	  
	  $(".submenu > li > a").click(function(){
		  var _this=$(this);
		  if(_this.next("ul").length>0){
			  if(_this.next().is(":visible")){
				  
				 _this.html(_this.html()).next().slideUp().hide();
			  }else{
				  
				  _this.html(_this.html()).next().slideToggle().show();
			  }
			  
			  return false;
		  }
	  });
	  
	 
	  $("a").focus( function(){
		  $(this).blur();
	  });
	  
	  
	  //----------- 伸縮下拉出現 ------------------//  
	  //指定shopA為shop
	  var shop=$('.shopA');
	  //點擊時隱藏所有div.panel
		  shop.hide();
	  //當滑鼠在li上面時就使用滑出視窗
	  $('.itemA').hover(function(){
		  //把選擇的索引值存入title
		  var itemA=$('.itemA').index($(this));
		  //把索引值帶入對應div參數中並顯示其內容
		  $(shop.get(itemA)).stop(true,false).fadeIn();
	  }, function(){
				  shop.fadeOut().hide();
		});
	  
	  
		// 預設顯示第一個 Tab
		var _showTab = 0;
		var $defaultLi = $('.abgne_tab ul.tabs li').eq(_showTab).addClass('active');
		$($defaultLi.find('a').attr('href')).siblings().hide();
		
		// 當 li 頁籤被點擊時...
		// 若要改成滑鼠移到 li 頁籤就切換時, 把 click 改成 mouseover
		$('.abgne_tab ul.tabs li').click(function() {
			// 找出 li 中的超連結 href(#id)
			var $this = $(this),
				_clickTab = $this.find('a').attr('href');
			// 把目前點擊到的 li 頁籤加上 .active
			// 並把兄弟元素中有 .active 的都移除 class
			$this.addClass('active').siblings('.active').removeClass('active');
			// 淡入相對應的內容並隱藏兄弟元素
			$(_clickTab).stop(false, true).fadeIn().siblings().hide();

			return false;
		}).find('a').focus(function(){
			this.blur();
		});
	  
	  
  });
