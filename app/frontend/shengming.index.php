<?php



class Index extends Page{
    var $AuthLevel = ACT_OPEN;
    
    function process(){
    	$pvar = array(
            //search form Action
            'formAct' => Core::getUrl('showlist', 'article', array('page'=>0), true,true),     
        );
        //设定模板使用变量
        $this->assign('v', stripQuotes($pvar));
        $this->display();
    }
    
}
?>