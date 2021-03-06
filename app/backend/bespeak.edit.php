<?php
/*-----------------------------------------------------+
 * 编辑留言信息
 *
 * @author Try.Shieh@gamil.com 
 +-----------------------------------------------------*/
class Edit extends Page{
    var $db;
    var $currentId;
    var $tab = 'bespeak';

    
    function __construct(){
        parent::__construct();
        $this->tab = $this->conf->get('table_prefix').$this->tab;
        $this->db = getDb();
        //$this->db->debug = true;
    }

    
    function process(){
        //获取当前的操作id
        $this->currentId = is_numeric($this->input['id'])
            ? $this->input['id']
            : $this->input['item']['id'];
        //没有则返回错误信息
        if(!is_numeric($this->currentId)){
            Core::raiseMsg($this->lang->get('e_bespeak_idIsEmpty'));
        }
        //检查是否提交数据
        if(!empty($this->input['submit'])){
            $this->submit();
            return;
        }
        //输出页面
        $this->export(stripQuotes($this->getData()));
    }

    
    function submit(){
        //获取提交的数据
        $item = stripQuotes(trimArr($this->input['item']));
        //检查数据合法性
        $emsg = $this->validate($item);
        //如果数据不合法则输出
        if(count($emsg)){
            $this->export($item, $emsg);
            return;
        }
        //数据合法则更新数据
        $this->updateDb($item);
        //返回列表页
        Core::redirect(Core::getUrl('showlist','','',true));
    }

    
    function export($item=null, $emsg=null){
        //页面输出的数据
        $pvar = array(
            'item' => $item,
            'jsonData' => $this->getJsonData($item, $emsg),
            'title' => $this->lang->get('p_bespeak_editTitle'),
            'formAct' => Core::getUrl(),
        );
        $this->assign('v', stripQuotes($pvar));
        $this->addTplFile('form');
        $this->display();
    }

    
    function getJsonData($item, $emsg=null){
        //如果只支持一种语言,则给lang加上默认值.
        if(count($this->conf->get('language_support')) < 2){
            $item['lang'] = strlen($item['lang']) ? $item['lang'] : $this->conf->get('language_default');
        }
        return array(
            'emsg' => $emsg,
            //状态选择器
            'item[state]' => array(
                'value' => $item['state'],
                'list' => array(
                    '0' => $this->lang->get('global_stateDisabled'),
                    '1' => $this->lang->get('global_stateEnabled'),
                    //'2' => $this->lang->get('global_stateDelete'),
                ),
            ),
            //语言选择器
            'item[lang]' => array(
                'value' => $item['lang'],
                'list' => $this->lang->getSelectList(),
            ),
            //手术类型选择器
            'item[types]' => array(
                'value' => $item['types'],
                'list' => $this->lang->get('bespeak_type'),
            ),
            'goBackLink' =>array('url' => Core::getUrl('showlist','','',true)),
        );
    }

    
    function getData(){
        $sql = "select * from {$this->tab} where id={$this->currentId}";
        $item = $this->db->GetRow($sql);
        $item['pub_time'] = date('Y-m-d', $item['pub_time']);
        $item['operation_time'] = date('Y-m-d', $item['operation_time']);
        return $item;
    }

    
    function updateDb($item){
        unset($item['id']);//防止ID号被修改
        $item['operation_time'] = strlen($item['operation_time'])?ext('unixtime',$item['operation_time']):time();
        $sql = "select * from {$this->tab} where id={$this->currentId}";
        $sql = $this->db->GetUpdateSQL($this->db->Execute($sql), $item);
        return $this->db->Execute($sql);
    }

    
    function validate($i){
        $e = array();
        if(!$i['name']){
            $e['name'] = $this->lang->get('e_bespeak_nameIsEmpty');
        }
        if(!is_numeric($i['state'])){
            $e['state'] = $this->lang->get('e_bespeak_stateIsEmpty');
        }
        
        if(!strlen($i['content'])){
            $e['content'] = $this->lang->get('e_bespeak_contentIsEmpty');
        }
        //将错误信息的key转换为表单的name
        $emsg = array();
        foreach($e as $key=>$val) $emsg["item[{$key}]"] = $val;
        return $emsg;
    }
}
?>
