<?php

function getArticles_Admin($title,$categoryId,$dateSort,$statusSort){

    $limit = 20;
    $page = (int)htmlspecialchars($_GET['p']);

    $from = ($page * $limit) - $limit;
    $range = $from.','.$limit;

    $conditions = ' articles.id > 0 ';
    $params = array();

    if(!empty($title)){
        $conditions .=' AND (articles.subject LIKE "%'.$title.'%" OR articles.id = :title) ' ;
        $params += [':title'=>$title];
    }

    if(($categoryId > 0) && $categoryId != 1){
        $conditions .=' AND articles.category_id = :categoryId';
        $params += [':categoryId'=>$categoryId];
    }

    if($statusSort!=3){
        $conditions.=' AND articles.status = :status';
        $params += [':status'=>$statusSort];
    }

    if(!empty($dateSort)){
        $conditions.= ' ORDER BY articles.created_date '.$dateSort;
    }else{
        $conditions.= ' ORDER BY articles.created_date DESC ';
    }

    $conditions.=' LIMIT '.$range;
    $obj = new Admin_Article();
    $result = $obj->getArticles($conditions,$params);
    return $result;
}


function getAllArticles_Admin($title,$categoryId,$dateSort,$statusSort){


    $conditions = ' articles.id > 0 ';
    $params = array();

    if(!empty($title)){
        $conditions .=' AND (articles.subject LIKE "%'.$title.'%" OR articles.id = :title) ' ;
        $params += [':title'=>$title];
    }

    if(($categoryId > 0) && $categoryId != 1){
        $conditions .=' AND articles.category_id = :categoryId';
        $params += [':categoryId'=>$categoryId];
    }

    if($statusSort!=3){
        $conditions.=' AND articles.status = :status';
        $params += [':status'=>$statusSort];
    }

    if(!empty($dateSort)){
        $conditions.= ' ORDER BY articles.created_date '.$dateSort;
    }else{
        $conditions.= ' ORDER BY articles.created_date DESC ';
    }

    $obj = new Admin_Article();
    $result = $obj->getArticles($conditions,$params);
    return $result;
}

//function getArticles_Count_Admin($title,$categoryId,$dateSort,$statusSort){
//    $conditions = 'articles.id > 0 ';
//    $params = array();
//    if(!empty($title)){
//        $conditions .=' articles.subject = :title';
//        $params += [':title'=>$title];
//    }
//
////    if(!empty($category)){
////        if(!empty($title)){
////            $conditions.=' AND';
////        }
////        $conditions .=' articles.category_id = :categoryId';
////        $params += [':categoryId'=>$categoryId];
////    }
//
//    if(!empty($statusSort) && $statusSort != 3){
////        if(!empty($title) || !empty($category)){
//        if(!empty($title)){
//            $conditions.= ' AND ';
//        }
//        $conditions.=' articles.status = :status';
//        $params += [':status'=>$statusSort];
//    }
//
//    $obj = new Admin_Article();
//    $result = $obj->getArticles($conditions,$params);
//    return count($result);
//}



function getArticles_Count_Admin($array){
    return count($array);
}



function getArticlesPages_Admin($array){
    $limit = 20;
    $count = getArticles_Count_Admin($array);
    $pagesCount = ceil($count / $limit);
    return $pagesCount;
}


function updateArticle_Admin($subject,$content,$tags,$category_id,$image,$status,$verificated,$revision,$articleId,$promoted,$updateDate){
    $articleObj = new Admin_Article();
    $set = ' articles.subject = :subject, articles.content = :content, articles.tags = :tags,  articles.category_id = :categoryId, articles.status = :status, articles.verificated = :verificated, articles.revision = :revision, articles.promoted = :promoted';
    if($updateDate){
        $set.=' , articles.created_date = :createdDate ';
    }
    $params = array(':subject'=>$subject, ':content'=>$content, ':tags'=>$tags, ':categoryId'=>$category_id,':status'=>$status,':verificated'=>$verificated, ':revision'=>$revision, ':promoted'=>$promoted);
    if($updateDate){
        $params += [':createdDate'=>date('Y-m-d H:i:s')];
    }
        $set .=' , articles.image = :image ';
        $params += [':image'=>$image];

    $result = $articleObj->updateArticle($articleId,$set,$params);
    return $result;
}

function getArticle_Admin($id){
    $condtions = '  articles.id = :id';
    $params = array(':id'=>$id);
    $articleObj = new Admin_Article();
    $results = $articleObj->getArticles($condtions,$params);
    return $results;
}

function deleteArticle_Admin($id){
    $obj = new Admin_Article();
    $delete = $obj->deleteArticle($id);
    return $delete;
}

?>