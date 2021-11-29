<?php

function getEvent_Admin($id){

    $conditions = null;
    $params = array();
    $conditions.=' events.id = :eventId ';

    $params += [':eventId'=>$id];
    $eventObj = new Admin_Event();

    $results = $eventObj->getEvents($conditions,$params);
    return $results;
}




function getAllEvents_Admin($title,$categoryId,$dateSort,$statusSort){
    $page = (isset($_GET['p'])?(int)htmlspecialchars($_GET['p']):1);
    $limit = 20;
    $from = ($page * $limit) - $limit;

    $conditions = null;
    $params = array();
    $range = $from.','.$limit;
    $conditions.=' events.id > 0 ';
    if($statusSort!=3){
        $conditions.=' AND events.status = :status';
        $params += [':status'=>$statusSort];
    }

    if($categoryId!=1){
        $conditions.=' AND events.category_id = :categoryId';
        $params += [':categoryId'=>$categoryId];
    }


    if(!empty($title)){
        $conditions.=' AND (events.subject LIKE "%'.$title.'%" OR events.id = :title)';
        $params += [':title'=>$title];
    }

    $conditions .= ' ORDER BY events.id '.(!empty($dateSort)?$dateSort:'DESC').' LIMIT '.$range;

    $eventObj = new Admin_Event();

    $results = $eventObj->getEvents($conditions,$params);
    return $results;
}

function getAllEvents_Admin_Count($title,$categoryId,$dateSort,$statusSort){
    $conditions = null;
    $params = array();

    $conditions.=' events.id > 0 ';
    if($statusSort!=3){
        $conditions.=' AND events.status = :status';
        $params += [':status'=>$statusSort];
    }

    if($categoryId!=1){
        $conditions.=' AND events.category_id = :categoryId';
        $params += [':categoryId'=>$categoryId];
    }


    if(!empty($title)){
        $conditions.=' AND events.subject LIKE "%'.$title.'%"';
    }

    $eventObj = new Admin_Event();
    $results = $eventObj->getEvents($conditions,$params);
    return count($results);
}

function getEventPages_Admin($title,$categoryId,$dateSort,$statusSort){
    $limit = 20;
    $count = getAllEvents_Admin_Count($title,$categoryId,$dateSort,$statusSort);
    $pagesCount = ceil($count / $limit);
    return $pagesCount;
}


function updateEvent_Admin($eventId,$title,$content,$city,$categoryId,$tags,$date_start,$date_end,$picture,$status,$verificated,$revision,$promoted){
    $dateTimeStart = $date_start;
    $dateTimeEnd = $date_end;
    $eventObj = new Admin_Event();
    $set = ' events.subject = :subject, events.content = :content, events.city = :city, events.tags = :tags,  events.category_id = :categoryId, events.date_start = :dateStart, events.date_end = :dateEnd, events.status = :status, events.verificated = :verificated, events.revision = :revision, events.promoted = :promoted';
    $params = array(':subject'=>$title, ':content'=>$content, ':city'=>$city,':tags'=>$tags, ':categoryId'=>$categoryId, ':dateStart'=>$dateTimeStart, ':dateEnd'=>$dateTimeEnd,':status'=>$status,':verificated'=>$verificated,':revision'=>$revision, ':promoted'=>$promoted);


        $set .=' , events.image = :image ';
        $params += [':image'=>$picture];

    $result = $eventObj->updateEvent($eventId,$set,$params);
    return $result;
}


function deleteEvent_Admin($id){
    $obj = new Admin_Event();
    $delete = $obj->deleteEvent($id);
    return $delete;
}