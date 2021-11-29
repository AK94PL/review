<?php


function getSettings($setting){
    $obj = new Admin_Settings();
    $conditions = ' name = :setting';
    $params = array(':setting'=>$setting);
    $result = $obj->getSettings_Admin($conditions,$params);
    $result = $result[0];
    return $result['setting'];
}

function updateSetting($setting,$newSet){
    $obj = new Settings();
    $obj->updateSetting($setting,$newSet);
}