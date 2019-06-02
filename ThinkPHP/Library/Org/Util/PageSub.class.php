<?php
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK IT ]
// +----------------------------------------------------------------------
// | Copyright (c) 2009 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: liu21st <liu21st@gmail.com>
// +----------------------------------------------------------------------
// $Id$

class PageSub extends Think {
    // 起始行數
    public $firstRow	;
    // 列表每頁顯示行數
    public $listRows	;
    // 頁數跳轉時要帶的參數
    public $parameter  ;
    // 分頁總頁面數
    protected $totalPages  ;
    // 總行數
    protected $totalRows  ;
    // 當前頁數
    protected $nowPage    ;
    // 分頁的欄的總頁數
    protected $coolPages   ;
    // 分頁欄每頁顯示的頁數
    protected $rollPage   ;
	// 分頁顯示定制
    protected $config = array('header'=>'項',
	'prev'=>'<img src="/Public/Images/list-back.gif" width="24" height="20" border="0" />',
	'next'=>'<img src="/Public/Images/list-next.gif" width="24" height="20" border="0" />',
	'theme'=>'%upPage% <li>%nowPage%/%totalPage% 頁</li> %downPage%');

    /**
     +----------------------------------------------------------
     * 架構函數
     +----------------------------------------------------------
     * @access public
     +----------------------------------------------------------
     * @param array $totalRows  總的記錄數
     * @param array $listRows  每頁顯示記錄數
     * @param array $parameter  分頁跳轉的參數
     +----------------------------------------------------------
     */
    public function __construct($totalRows,$listRows,$parameter='') {
        $this->totalRows = $totalRows;
        $this->parameter = $parameter;
        $this->rollPage = 1;
        $this->listRows = !empty($listRows)?$listRows:C('PAGE_LISTROWS');
        $this->totalPages = ceil($this->totalRows/$this->listRows);     //總頁數
        $this->coolPages  = ceil($this->totalPages/$this->rollPage);
        $this->nowPage  = !empty($_POST[C('VAR_PAGE')])?$_POST[C('VAR_PAGE')]:1;
        if(!empty($this->totalPages) && $this->nowPage>$this->totalPages) {
            $this->nowPage = $this->totalPages;
        }
        $this->firstRow = $this->listRows*($this->nowPage-1);
    }

    public function setConfig($name,$value) {
        if(isset($this->config[$name])) {
            $this->config[$name] = $value;
        }
    }

    /**
     +----------------------------------------------------------
     * 分頁顯示輸出
     +----------------------------------------------------------
     * @access public
     +----------------------------------------------------------
     */
    public function show($INid) 
	{
        if(0 == $this->totalRows) return '';
        $p = C('VAR_PAGE');
        $nowCoolPage = ceil($this->nowPage/$this->rollPage);
        $url  =  auto_charset($_SERVER['REQUEST_URI'].(strpos($_SERVER['REQUEST_URI'],'?')?'':"?").$this->parameter,'GBK','UTF8');
        
        $parse = parse_url($url);
        if(isset($parse['query'])) {
            parse_str($parse['query'],$params);
            unset($params[$p]);
            $url   =  $parse['path'].'?'.http_build_query($params);
        }
        //上下翻頁字符串
        $upRow   = $this->nowPage-1;
        $downRow = $this->nowPage+1;
        if ($upRow>0){
            $upPage="<li><a onclick='turnPage(". ($INid?$INid:"") .", $upRow)'>".$this->config['prev']."</a></li>";
        }else{
            $upPage="";
        }

        if ($downRow <= $this->totalPages){
            $downPage="<li><a onclick='turnPage(". ($INid?$INid:"") .", $downRow)'>".$this->config['next']."</a><li>";
        }else{
            $downPage="";
        }
        $pageStr =  str_replace(
            array('%header%','%nowPage%','%totalRow%','%totalPage%','%upPage%','%downPage%','%prePage%','%linkPage%','%nextPage%'),
            array($this->config['header'],$this->nowPage,$this->totalRows,$this->totalPages,$upPage,$downPage,$prePage,$linkPage,$nextPage),$this->config['theme']);
        return "<ul>".$pageStr."</ul>";
    }

}
?>