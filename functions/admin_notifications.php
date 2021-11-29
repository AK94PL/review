<?php

function getNotifications_count($type,$status){
    $conditions = null;
    $param = array();
    if($type!=null){
        $param += [':type'=>$type];
        $conditions.= ' admin_notifications.type = :type';
        $param += [':type'=>$type];
    }

    if(!empty($type)){
        $conditions .= ' AND ';
    }
    $conditions.=' admin_notifications.status = :status';
    $param += ['status'=>$status];

    $ntfsObj = new Admin_Notifications();
    $result = $ntfsObj->getNotifications($conditions,$param);
    return count($result);
}

function setNotification_read($ntfId){
    $ntfObj = new Admin_Notifications();
    $set =  ' admin_notifications.status = :status';
    $params = array(':status'=>1);
    $result = $ntfObj->updateNotification($ntfId,$set,$params);
    return $result;
}

function getNotifications_Panel($type,$status,$page){
    $limit = 20;
    $from = $page * $limit - $limit;
    $to = $page * $limit;
    $conditions = null;
    $param = array();
    if(!empty($type)){
        $param = array(':type'=>$type);
        $conditions.= ' admin_notifications.type = :type';
        $param += [':type'=>$type];
    }

        if(!empty($type)){
            $conditions .= ' AND ';
        }
        $conditions.=' admin_notifications.status = :status';
        $param += ['status'=>$status];

    $ntfsObj = new Admin_Notifications();
    if(is_null($type) && is_null($status)){
        $conditions.=' admin_notifications.id > 0 ';
    }
    $conditions .= ' ORDER BY created_date DESC LIMIT '.$from. ','.$to;
    $result = $ntfsObj->getNotifications($conditions,$param);
    return $result;
}

function getNotificationsPages($type,$status){
    $limit = 20;
    $count = getNotifications_count($type,$status);
    $pagesCount = ceil($count / $limit);
    return $pagesCount;
}