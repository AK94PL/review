<?php


class Settings extends Database
{
    public function getSettings($conditions,$params){
        $result = $this->select('setting, plSafeChars(setting) as settingPL','settings',$conditions,$params);
        return $result->fetchAll();
    }

    public function updateSetting($setting,$newSet){
        $params = array(':newSet'=>$newSet,':settingName'=>$setting);
        $this->update('settings','setting = :newSet', 'name = :settingName',$params);
    }
}