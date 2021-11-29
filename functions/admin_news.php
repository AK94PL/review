<?php

function getNewses_Admin($title,$categoryId,$dateSort,$statusSort){

    $limit = 20;
    $page = (int)htmlspecialchars($_GET['p']);

    $from = ($page * $limit) - $limit;
    $range = $from.','.$limit;

    $conditions = ' news.id > 0 ';
    $params = array();

    if(!empty($title)){
        $conditions .=' AND (news.subject LIKE "%'.$title.'%" OR news.id = :title) ' ;
        $params += [':title'=>$title];
    }

    if(($categoryId > 0) && $categoryId != 1){
        $conditions .=' AND news.category_id = :categoryId';
        $params += [':categoryId'=>$categoryId];
    }

    if($statusSort!=3){
        $conditions.=' AND news.status = :status';
        $params += [':status'=>$statusSort];
    }

    if(!empty($dateSort)){
        $conditions.= ' ORDER BY news.created_date '.$dateSort;
    }else{
        $conditions.= ' ORDER BY news.created_date DESC ';
    }

    $conditions.=' LIMIT '.$range;
    $obj = new Admin_News();
    $result = $obj->getNewses($conditions,$params);
    return $result;
}


function getAllNewses_Admin($title,$categoryId,$dateSort,$statusSort){


    $conditions = ' news.id > 0 ';
    $params = array();

    if(!empty($title)){
        $conditions .=' AND (news.subject LIKE "%'.$title.'%" OR news.id = :title) ' ;
        $params += [':title'=>$title];
    }

    if(($categoryId > 0) && $categoryId != 1){
        $conditions .=' AND news.category_id = :categoryId';
        $params += [':categoryId'=>$categoryId];
    }

    if($statusSort!=3){
        $conditions.=' AND news.status = :status';
        $params += [':status'=>$statusSort];
    }

    if(!empty($dateSort)){
        $conditions.= ' ORDER BY news.created_date '.$dateSort;
    }else{
        $conditions.= ' ORDER BY news.created_date DESC ';
    }

    $obj = new Admin_News();
    $result = $obj->getNewses($conditions,$params);
    return $result;
}


function getNewses_Count_Admin($array){
    return count($array);
}



function getNewsesPages_Admin($array){
    $limit = 20;
    $count = getNewses_Count_Admin($array);
    $pagesCount = ceil($count / $limit);
    return $pagesCount;
}


function updateNews_Admin($subject,$content,$tags,$category_id,$image,$status,$verificated,$revision,$articleId,$promoted,$updateDate){
    $articleObj = new Admin_News();
    $set = ' news.subject = :subject, news.content = :content, news.tags = :tags,  news.category_id = :categoryId, news.status = :status, news.verificated = :verificated, news.revision = :revision, news.promoted = :promoted';
    if($updateDate){
        $set.=' , news.created_date = :createdDate ';
    }
    $params = array(':subject'=>$subject, ':content'=>$content, ':tags'=>$tags, ':categoryId'=>$category_id,':status'=>$status,':verificated'=>$verificated, ':revision'=>$revision, ':promoted'=>$promoted);
    if($updateDate){
        $params += [':createdDate'=>date('Y-m-d H:i:s')];
    }
    $set .=' , news.image = :image ';
    $params += [':image'=>$image];

    $result = $articleObj->updateNews($articleId,$set,$params);
    return $result;
}

function getNews_Admin($id){
    $condtions = '  news.id = :id';
    $params = array(':id'=>$id);
    $articleObj = new Admin_News();
    $results = $articleObj->getNewses($condtions,$params);
    return $results;
}

function deleteNews_Admin($id){
    $obj = new Admin_News();
    $delete = $obj->deleteNews($id);
    return $delete;
}

?>