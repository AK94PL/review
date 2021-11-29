<?php


function getSettings_Admin($setting=null){
    $obj = new Admin_Settings();
    $conditions = null;
    $params = array();
    if($setting!=null){
        $conditions = ' name = :setting';
        $params = array(':setting'=>$setting);
    }
    $result = $obj->getSettings_Admin($conditions,$params);
    $result = $result[0];
    return $result['setting'];
}

function updateSetting_Admin($set,$setting){
    $obj = new Admin_Settings();
    $params = array(':set'=>$set,':setting'=>$setting);
    $set = $obj->updateSettings_Admin('setting = :set',' name = :setting',$params);
    return $set;
}