<?php


class Admin_Settings extends Database
{

    public function getSettings_Admin($conditions,$params){
        $result = $this->select('*','settings',$conditions,$params);
        return $result->fetchAll();
    }

    public function updateSettings_Admin($set,$where,$params){
        $result = $this->update('settings',$set,$where,$params);
        return $result;
    }
}