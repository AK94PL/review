<?php


function getAdvertisement_Admin($id)
{
    $condtions = ' advertisements.id = :id  ';
    $params = array(':id'=>$id);

    $advertisementObj = new Admin_Advertisement();
    $results = $advertisementObj->getAdvertisements($condtions, $params);
    return $results;
}

function getAdvertisements_Admin($title,$categoryId,$dateSort,$statusSort)
{
    $limit = 20;
    $page = (int)htmlspecialchars($_GET['p']);
    $from = ($page * $limit) - $limit;
    $range = $from.','.$limit;

    $conditions = ' advertisements.id > 0 ';
    $params = array();

    if(!empty($title)){
        $conditions .= ' AND (advertisements.title LIKE "%'.$title .'%" OR advertisements.id = :title) ';
        $params +=[':title'=>$title];
    }

    if(intval($categoryId)!=1){
        $conditions .= ' AND advertisements.category_id = :categoryId  ';
        $params +=[':categoryId'=>$categoryId];
    }

    if(intval($statusSort)!=3){
        $conditions.=' AND advertisements.status = :status';
        $params += [':status'=>$statusSort];
    }



    if(!empty($dateSort)){
        $conditions.= ' ORDER BY advertisements.created_date '.$dateSort;
    }else{
        $conditions.= ' ORDER BY advertisements.created_date DESC ';
    }

    $conditions.=' LIMIT '.$range;
    $advertisementObj = new Admin_Advertisement();
    $results = $advertisementObj->getAdvertisements($conditions, $params);
    return $results;
}

function getAdvertisements_Admin_Count($title,$categoryId,$dateSort,$statusSort)
{
    $limit = 20;
    $page = (int)htmlspecialchars($_GET['p']);
    $from = ($page * $limit) - $limit;
    $range = $from.','.$limit;

    $conditions = ' advertisements.id > 0 ';
    $params = array();

    if(!empty($title)){
        $conditions .= ' AND advertisements.title = :title  ';
        $params +=[':title'=>$title];
    }

    if(intval($categoryId)!=1){
        $conditions .= ' AND advertisements.category_id = :categoryId  ';
        $params +=[':categoryId'=>$categoryId];
    }

    if(intval($statusSort)!=3){
        $conditions.=' AND advertisements.status = :status';
        $params += [':status'=>$statusSort];
    }



    if(!empty($dateSort)){
        $conditions.= ' ORDER BY advertisements.created_date '.$dateSort;
    }else{
        $conditions.= ' ORDER BY advertisements.created_date DESC ';
    }

    $advertisementObj = new Admin_Advertisement();
    $results = $advertisementObj->getAdvertisements($conditions, $params);
    return count($results);
}

function getAdvertisementPages_Admin($title,$categoryId,$dateSort,$statusSort){
    $limit = 20;
    $count = getAdvertisements_Admin_Count($title,$categoryId,$dateSort,$statusSort);
    $pagesCount = ceil($count / $limit);
    return $pagesCount;
}



function updateAdvertisement_Admin($advertId,$title,$content,$category_id,$tags,$type,$city,$price,$phone,$status,$verificated,$revision,$promoted){
    $i = 0;
    $tagsArray = explode(',',str_replace(' ','',$tags));
    $tagList = null;
    foreach($tagsArray as $tag){
        if($i != 3){
            $i++;
            $tagList.=$tag;
            if($i<3){
                $tagList.=',';
            }
        }
    }
    $tagList = rtrim($tagList, ',');
    $advObj = new Admin_Advertisement();

    $result = $advObj->updateAdvertisement($advertId,trim($title),trim($content),$category_id,trim($tagList),$type,$city,$price,$phone,$status,$verificated,$revision,$promoted);
    if($result > 0){
        return $result;
    }else{
        return null;
    }
}


    function getAdvertisementTypes_Admin(){
        $advObj = new Admin_Advertisement();
        $result = $advObj->getAdvertisementTypes();
        return $result;
    }

    function deleteAdvert_Admin($id){
        $advObj = new Admin_Advertisement();
        $result = $advObj->deleteAdvert($id);
        return $result;
    }

?>