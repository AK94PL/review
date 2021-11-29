<?php


function getRotator_Admin(){
    $conditions = null;
    $params = array();
    $obj = new Admin_Rotator();
    $rotator = $obj->getRotator($conditions,$params);
    return $rotator;
}

function updateRotator_Admin($rotatorId,$content,$tags,$limit,$status){
    $obj = new Admin_Rotator();
    $up = $obj->updateRotator($rotatorId,$content,$tags,$limit,$status);
    return $up;
}


function deleteRotator_Admin($rotatorId){
    $obj = new Admin_Rotator();
    $up = $obj->deleteRotator($rotatorId);
    return $up;
}


function addRotator_Admin(){
    $obj = new Admin_Rotator();
    $newRotator = $obj->addRotator();
    return $newRotator;
}