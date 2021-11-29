<?php

function getRotator($tags){
    $obj = new Rotator();

    $conditions = ' rotators.tags LIKE "%'.$tags.'%" AND rotators.status = :status AND (rotators.views_limit IS NULL OR rotators.views_limit = 0 OR rotators.views_limit >= rotators.views) ';
    $params = array(':status'=>1);
    $rotator = $obj->getRotator($conditions,$params);
    addView($rotator[0]['id']);
    return $rotator;
}

function addView($rotatorId){
    $obj = new Rotator();
    $addView = $obj->addView($rotatorId);
}