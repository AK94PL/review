<?php
function getTheard_Admin($theardId){
    $conditions = null;
    $params = array();
    $conditions .= ' AND forum_theards.id = :theardId';
    $params += [':theardId'=>$theardId];
    $forumObj = new Admin_Forum();

    $results = $forumObj->getTheards($conditions,$params);
    return $results;
}

function getReplies($theardId,$searchId,$date,$status){
    $obj = new Admin_Forum();
    $conditions = '  forum_replies.theard_id = :theardId ';
    $params = array(':theardId'=>$theardId);
    if(!empty($searchId)){
        $conditions.=' AND forum_replies.id = :replyId ';
        $params += [':replyId'=>$searchId];
    }
    if(!empty($status)){
        $conditions.=' AND forum_replies.status = :status ';
        $params += [':status'=>$status];
    }

    if($date === 'ASC'){
        $conditions.=' ORDER BY forum_replies.created_date ASC';
    }else{
        $conditions.=' ORDER BY forum_replies.created_date DESC';
    }

    $replies = $obj->getReplies($conditions,$params);
    return $replies;
}

function getReplies_Count($theardId){
    $replies = getReplies($theardId);
    return count($replies);
}

function getAllTheards_Admin($title,$categoryId,$dateSort,$statusSort){
    $page = (isset($_GET['p'])?(int)htmlspecialchars($_GET['p']):1);
    $limit = 20;
    $from = ($page * $limit) - $limit;

    $conditions = null;
    $params = array();
    $range = $from.','.$limit;

    if($statusSort!=3){
        $conditions.=' AND forum_theards.status = :status';
        $params += [':status'=>$statusSort];
    }

    if($categoryId!=1){
        $conditions.=' AND forum_theards.category_id = :categoryId';
        $params += [':categoryId'=>$categoryId];
    }


    if(!empty($title)){
        $conditions.=' AND (forum_theards.subject LIKE "%'.$title.'%" OR forum_theards.id = :theardId)';
        $params += [':theardId'=>$title];
    }

    $conditions .= ' ORDER BY forum_theards.created_date '.(!empty($dateSort)?$dateSort:'DESC').' LIMIT '.$range;

    $forumObj = new Admin_Forum();

    $results = $forumObj->getTheards($conditions,$params);
    return $results;
}

function getAllTheards_Count_Admin($title,$categoryId,$dateSort,$statusSort){
    $conditions = null;
    $params = array();

    if($statusSort!=3){
        $conditions.=' AND forum_theards.status = :status';
        $params += [':status'=>$statusSort];
    }

    if($categoryId!=1){
        $conditions.=' AND forum_theards.category_id = :categoryId';
        $params += [':categoryId'=>$categoryId];
    }


    if(!empty($title)){
        $conditions.=' AND forum_theards.subject LIKE "%'.$title.'%"';
    }

    $forumObj = new Admin_Forum();

    $results = $forumObj->getTheards($conditions,$params);
    return count($results);
}

function getTheardsPages_Admin($title,$categoryId,$dateSort,$statusSort){
    $limit = 20;
    $count = getAllTheards_Count_Admin($title,$categoryId,$dateSort,$statusSort);
    $pagesCount = ceil($count / $limit);
    return $pagesCount;
}


function turnOff_Theard_Admin($id){
    $forumObj = new Admin_Forum();
    $update = $forumObj->turnOff_Theard($id);
    return $update;
}


function deleteTheard_Admin($id){
    $obj = new Admin_Forum();
    $delete = $obj->deleteTheard($id);
    return $delete;
}



function updateTheard_Admin($theardId,$title,$content,$categoryId,$tags,$status,$verificated,$promoted,$pinned){
    $forumObj = new Admin_Forum();
    $update = $forumObj->update_Theard($theardId,$title,$content,$categoryId,$tags,$status,$verificated,$promoted,$pinned);
    return $update;
}

function update_Reply($replyId,$content,$status){
    $obj = new Admin_Forum();
    $update = $obj->update_Reply($replyId,$content,$status);
    return $update;
}