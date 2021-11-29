<?php


class Rotator extends Database
{
    public function getRotator($conditions,$params){
        $rotators = $this->select(' rotators.* ','rotators',$conditions,$params);
        return $rotators->fetchAll();
    }


    public function addView($rotatorId){
        $params = array(':rotatorId'=>$rotatorId);
        $result = $this->update('rotators','views = views+1',' rotators.id = :rotatorId',$params);
        return $result;
    }

}