<?php

function getAuthor_Advertisement($id,$title){
    $advert = getAdvertisement($id,$title);
    $advertArray = ['author'=>$advert[0]['user_id'],'author_type'=>$advert[0]['user_type']];
    return $advertArray;
}



function updateAdvertisement($advertId,$title,$content,$category_id,$tags,$type,$city,$price,$phone,$user_id,$user_type,$verificated,$revision,$status){
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
    $advObj = new Advertisement();
    $result = $advObj->updateAdvertisement($advertId,trim($title),trim($content),$category_id,trim($tagList),$type,$city,$price,$phone,$user_id,$user_type,$verificated,$revision,$status);
    if($result > 0){
        return $result;
    }else{
        return null;
    }
}


function addAdvertisement($title,$content,$category_id,$tags,$type,$city,$price,$phone,$user_id,$user_type,$status,$verificated){
    if($user_type === 'company'){
        $userIdSafe = getCompanyOwnerId($user_id);
    }else{
        $userIdSafe = $user_id;
    }

    if(!canAddNewContent($userIdSafe)){
        return false;
    }

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
    $advObj = new Advertisement();
    $result = $advObj->addAdvertisement(trim($title),trim($content),$category_id,trim($tagList),$type,$city,$price,$phone,$user_id,$user_type,$status,$verificated);

        return $result;

}

function getAdvertisementTypes(){
    $conditions = null;
    $params = null;
    $advertisementObj = new Advertisement();
    $results = $advertisementObj->getAdvertisementTypes($conditions, $params);
    return $results;
}

function getAdvertisementsFromWishList($id){
    $condtions = ' advertisements.id = :id  ';
    $params = array(':id'=>$id);
    $advertisementObj = new Advertisement();
    $results = $advertisementObj->getAdvertisements($condtions, $params);
    return $results;
}

function getAdvertisement($id,$title)
{
    $condtions = ' advertisements.id = :id  ';
//    $condtions = ' advertisements.id = :id AND NOW() BETWEEN  advertisements.created_date AND advertisements.end_date ';
    $params = array(':id'=>$id);
    if($title!=null){
        $condtions.=' AND plSafeChars(advertisements.title) = :title';
        $params+=[':title'=>$title];
    }
    $advertisementObj = new Advertisement();
    $results = $advertisementObj->getAdvertisements($condtions, $params);


    return $results;
}

function getAdvertisements($limit,$category)
{
    $conditions = null;
    $params = array(':status'=>1,':verificated'=>1);

    if($category!=null){
        $conditions .= ' plSafeChars(categories.name) = :category AND ';
    }
    $conditions .= '  NOW() BETWEEN  advertisements.created_date AND advertisements.end_date AND advertisements.status = :status AND advertisements.verificated = :verificated ';
    if ($limit != null) {
        $conditions .= ' ORDER BY advertisements.id DESC LIMIT ' . $limit;
    }

    if($category!=null){
        $params += [':category'=>$category];
    }
    $advertisementObj = new Advertisement();
    $results = $advertisementObj->getAdvertisements($conditions, $params);
    $results = sortArrayByPromoted($results);

    return $results;
}


function getAdvertisements_Tag($limit,$tag)
{
    $conditions = null;
    $params = array(':status'=>1,':verificated'=>1);
        $conditions .= ' ( (plSafeChars(advertisements.tags) LIKE "%'.$tag.'%") OR (advertisements.tags LIKE "%'.$tag.'%") ) AND ';

    $conditions .= '  NOW() BETWEEN  advertisements.created_date AND advertisements.end_date AND advertisements.status = :status AND advertisements.verificated = :verificated ';
    if ($limit != null) {
        $conditions .= ' ORDER BY advertisements.id DESC LIMIT ' . $limit;
    }


    $advertisementObj = new Advertisement();
    $results = $advertisementObj->getAdvertisements($conditions, $params);
    $results = sortArrayByPromoted($results);

    return $results;
}


function getAdvertisements_WordAndTag($limit,$tag)
{
    $conditions = null;
    $params = array(':status'=>1,':verificated'=>1);

    $conditions .= ' ( (plSafeChars(advertisements.title) LIKE "%'.$tag.'%") OR (advertisements.title LIKE "%'.$tag.'%") OR (plSafeChars(advertisements.tags) LIKE "%'.$tag.'%") OR (advertisements.tags LIKE "%'.$tag.'%") ) AND ';

    $conditions .= '  NOW() BETWEEN  advertisements.created_date AND advertisements.end_date AND advertisements.status = :status AND advertisements.verificated = :verificated ';
    if ($limit != null) {
        $conditions .= ' ORDER BY advertisements.id DESC LIMIT ' . $limit;
    }


    $advertisementObj = new Advertisement();
    $results = $advertisementObj->getAdvertisements($conditions, $params);
    $results = sortArrayByPromoted($results);

    return $results;
}

function getAllAdvertisements($city, $page,$limit)
{

    $conditions=null;
    $params = array(':status'=>1,':verificated'=>1);

    if($city!=null){
        $conditions.=' plSafeChars(advertisements.city) = :city AND ';
    }
    $conditions .= '  NOW() BETWEEN  advertisements.created_date AND advertisements.end_date AND advertisements.status = :status AND advertisements.verificated = :verificated ';
    $from = ($page * $limit) - $limit;
    $range = $from.','.$limit;

    $conditions .= ' ORDER BY advertisements.id DESC LIMIT ' . $range;

    if($city!=null){
        $params += [':city'=>$city];
    }

    $advertisementObj = new Advertisement();
    $results = $advertisementObj->getAdvertisements($conditions, $params);
    $results = sortArrayByPromoted($results);

    return $results;
}


function getCountUserAdvertisements($userId,$status,$type){
    $userCompanies = getUserCompanies($userId,1);

    $conditions = 'advertisements.status = :status AND ((advertisements.user_type = "user" AND advertisements.user_id = :userId AND users.id = :userId ) ';
    $params = array(':status'=>$status,':userId'=>$userId);

    $i = 1;
    foreach($userCompanies as $uc){
        $conditions.=' OR (advertisements.user_type = "company" AND advertisements.user_id = :companyId'.$i.')';
        $params += [':companyId'.$i=>$uc['companyId']];
        $i++;
    }
    $conditions .= ' ) AND ';
    if($status === 1 || $status === 0){
        $conditions.=' advertisements.end_date > NOW() ';
    }else{
        $conditions.=' advertisements.end_date < NOW() ';
    }
    if($type!=null){
        $conditions.=' AND ( advertisements.type_id = advertisements_types.id AND advertisements_types.type = :type) ';
        $params +=[':type'=>$type];
    }

    $advertisementObj = new Advertisement();
    $results = $advertisementObj->getAdvertisements($conditions,$params);
    return count($results);
}



function searchCountUserAdvertisements($userId,$status,$type,$query){
    $userCompanies = getUserCompanies($userId,1);

    $conditions = ' advertisements.status = :status AND (advertisements.title LIKE "%'.$query.'%" OR advertisements.tags LIKE "%'.$query.'%") AND ((advertisements.user_type = "user" AND advertisements.user_id = :userId AND users.id = :userId ) ';
    $params = array(':status'=>$status,':userId'=>$userId);

    $i = 1;
    foreach($userCompanies as $uc){
        $conditions.=' OR (advertisements.user_type = "company" AND advertisements.user_id = :companyId'.$i.')';
        $params += [':companyId'.$i=>$uc['companyId']];
        $i++;
    }
    $conditions .= ' ) AND ';
    if($status === 1 || $status === 0){
        $conditions.=' advertisements.end_date > NOW() ';
    }else{
        $conditions.=' advertisements.end_date < NOW() ';
    }
    if($type!=null){
        $conditions.=' AND ( advertisements.type_id = advertisements_types.id AND advertisements_types.type = :type) ';
        $params +=[':type'=>$type];
    }

    $advertisementObj = new Advertisement();
    $results = $advertisementObj->getAdvertisements($conditions,$params);
    return count($results);
}


function getUserAdvertisements($userId,$status,$page,$limit,$sort,$type){
   $conditions = null;
    $userCompanies = getUserCompanies($userId,1);
    if((int)$status === 2){
        $conditions.= ' (advertisements.status = :status OR advertisements.end_date <= NOW()) ';
    }else{
        $conditions.= ' advertisements.status = :status ';
    }
    $conditions .= ' AND ( (advertisements.user_type = "user" AND advertisements.user_id = :userId ) ';

    $params = array(':status'=>$status,':userId'=>$userId);

    $i = 1;
    foreach($userCompanies as $uc){
        $conditions.=' OR (advertisements.user_type = "company" AND advertisements.user_id = :companyId'.$i.')';
        $params += [':companyId'.$i=>$uc['companyId']];
        $i++;
    }

    $conditions .= ' )  ';
    if((int)$status === 1){
        $conditions.=' AND NOW() BETWEEN  advertisements.created_date AND advertisements.end_date ';
    }



    if($type!=null){
        $conditions.=' AND ( advertisements.type_id = advertisements_types.id AND advertisements_types.type = :type) ';
        $params +=[':type'=>$type];
    }

    $from = ($page * $limit) - $limit;
    $range = $from.','.$limit;
    $conditions .= ' ORDER BY '.$sort.' LIMIT '.$range;

    $advertisementObj = new Advertisement();
    $results = $advertisementObj->getAdvertisements($conditions,$params);

    return $results;
}


function getUserAdvertisementsCount($userId,$status,$type){
    $userCompanies = getUserCompanies($userId,1);

    $conditions = ' advertisements.status = :status AND ((advertisements.user_type = "user" AND advertisements.user_id = :userId AND users.id = :userId ) ';
    $params = array(':status'=>$status,':userId'=>$userId);
    if($type!=null){
        $conditions.=' AND (advertisements.type_id = advertisements_types.id AND advertisements_types.type = :type)';
        $params += [':type'=>$type];
    }

    $i = 1;
    foreach($userCompanies as $uc){
        $conditions.=' OR (advertisements.user_type = "company" AND advertisements.user_id = :companyId'.$i.')';
        $params += [':companyId'.$i=>$uc['companyId']];
        $i++;
    }
    $conditions .= ' ) AND  ';
    if($status === 1 || $status === 0){
        $conditions.=' advertisements.end_date > NOW() ';
    }else{
        $conditions.=' advertisements.end_date < NOW() ';
    }

    $conditions .= ' ORDER BY advertisements.id DESC ';
    $eventObj = new Advertisement();
    $results = $eventObj->getAdvertisements($conditions,$params);
    return (count($results) > 0 ? count($results) : 1);
}


function searchUserAdvertisements($userId,$status,$page,$limit,$sort,$type,$query){
    $userCompanies = getUserCompanies($userId,1);

    $conditions = ' advertisements.status = :status AND (advertisements.title LIKE "%'.$query.'%" OR advertisements.tags LIKE "%'.$query.'%") AND ((advertisements.user_type = "user" AND advertisements.user_id = :userId AND users.id = :userId ) ';
    $params = array(':status'=>$status,':userId'=>$userId);

    $i = 1;
    foreach($userCompanies as $uc){
        $conditions.=' OR (advertisements.user_type = "company" AND advertisements.user_id = :companyId'.$i.')';
        $params += [':companyId'.$i=>$uc['companyId']];
        $i++;
    }
    $conditions .= ' ) AND ';
    if($status === 1 || $status === 0){
        $conditions.=' advertisements.end_date > NOW() ';
    }else{
        $conditions.=' advertisements.end_date < NOW() ';
    }
    if($type!=null){
        $conditions.=' AND ( advertisements.type_id = advertisements_types.id AND advertisements_types.type = :type) ';
        $params +=[':type'=>$type];
    }

    $from = ($page * $limit) - $limit;
    $range = $from.','.$limit;
    $conditions .= ' ORDER BY '.$sort.' LIMIT '.$range;
    $advertisementObj = new Advertisement();
    $results = $advertisementObj->getAdvertisements($conditions,$params);
    return $results;
}



function get_UserAdvertisements($uid){
//    function without deleting of commecrial adv's - used to deleting user account
    $conditions = ' (advertisements.user_type = "user" AND advertisements.user_id = :userId  ) ';
    $params = array(':userId'=>$uid);
    $advertisementObj = new Advertisement();
    $results = $advertisementObj->getAdvertisements($conditions,$params);
    $results = sortArrayByPromoted($results);

    return $results;

}


function get_CompanyAdvertisements($uid){
//    function  deleting commecrial adv's - used to deleting user account
    $conditions = ' (advertisements.user_type = "company" AND advertisements.user_id = :userId ) ';
    $params = array(':userId'=>$uid);
    $advertisementObj = new Advertisement();
    $results = $advertisementObj->getAdvertisements($conditions,$params);
    $results = sortArrayByPromoted($results);

    return $results;

}




function getAllAdvertisementsFromCategory($city,$category,$page,$limit)
{
    $condtions = null;
    $params = array(':status'=>1, ':verificated'=>1);
    if($city!=null){
        $condtions.=' plSafeChars(advertisements.city) = :city AND';
    }

    $condtions .= ' plSafeChars(categories.name) = :categoryName AND NOW() BETWEEN  advertisements.created_date AND advertisements.end_date AND advertisements.status = :status AND advertisements.verificated = :verificated ';
    $from = ($page * $limit) - $limit;
    $range = $from.','.$limit;

    $condtions .= ' ORDER BY advertisements.id DESC LIMIT ' . $range;

    if($city!=null){
        $params += [':city'=>$city,':categoryName'=>$category];
    }else{
        $params += [':categoryName'=>$category];
    }
    $advertisementObj = new Advertisement();
    $results = $advertisementObj->getAdvertisements($condtions, $params);
    $results = sortArrayByPromoted($results);

    return $results;
}

function getAllAdvertisementsFromUser($userId,$page,$limit)
{
    $condtions = null;
    $params = array(':userId'=>$userId, ':status'=>1, ':verificated'=>1);

    $condtions .= ' advertisements.user_type = "user" AND advertisements.user_id = :userId AND NOW() BETWEEN  advertisements.created_date AND advertisements.end_date AND advertisements.status = :status AND advertisements.verificated = :verificated ';
    if($limit!=null){
        $from = ($page * $limit) - $limit;
        $range = $from.','.$limit;

        $condtions .= ' ORDER BY advertisements.id DESC LIMIT ' . $range;
    }else{
        $condtions .= ' ORDER BY advertisements.id DESC ';
    }

    $advertisementObj = new Advertisement();
    $results = $advertisementObj->getAdvertisements($condtions, $params);
    $results = sortArrayByPromoted($results);

    return $results;
}


function getAllAdvertisementsFromCompany($userId,$page,$limit)
{
    $condtions = null;
    $params = array(':userId'=>$userId, ':status'=>1, ':verificated'=>1);

    $condtions .= ' advertisements.user_type = "company" AND advertisements.user_id = :userId AND NOW() BETWEEN  advertisements.created_date AND advertisements.end_date AND advertisements.status = :status AND advertisements.verificated = :verificated ';
    if($limit!=null){
        $from = ($page * $limit) - $limit;
        $range = $from.','.$limit;

        $condtions .= ' ORDER BY advertisements.id DESC LIMIT ' . $range;
    }else{
        $condtions .= ' ORDER BY advertisements.id DESC ';
    }

    $advertisementObj = new Advertisement();
    $results = $advertisementObj->getAdvertisements($condtions, $params);
    $results = sortArrayByPromoted($results);

    return $results;
}




function getAllAdvertisementsFromTag($tag,$page,$limit)
{
    $params = array(':status'=>1, ':verificated'=>1);
    $condtions = null;
    $condtions .= ' ((plSafeChars(advertisements.tags) LIKE "%'.$tag.'%") OR (advertisements.tags LIKE "%'.$tag.'%")) AND NOW() BETWEEN  advertisements.created_date AND advertisements.end_date AND advertisements.status = :status AND advertisements.verificated = :verificated ';
    $from = ($page * $limit) - $limit;
    $range = $from.','.$limit;

    $condtions .= ' ORDER BY advertisements.id DESC LIMIT ' . $range;
    $advertisementObj = new Advertisement();
    $results = $advertisementObj->getAdvertisements($condtions, $params);
    $results = sortArrayByPromoted($results);

    return $results;
}

function getAllAdvertisementsFromWordAndTag($tag,$page,$limit)
{
    $params = array(':status'=>1,':verificated'=>1);
    $condtions = null;
    $condtions .= ' ((plSafeChars(advertisements.tags) LIKE "%'.$tag.'%") OR (advertisements.tags LIKE "%'.$tag.'%") OR (plSafeChars(advertisements.title) LIKE "%'.$tag.'%") OR (advertisements.title LIKE "%'.$tag.'%")) AND NOW() BETWEEN  advertisements.created_date AND advertisements.end_date AND advertisements.status = :status AND advertisements.verificated = :verificated ';
    $from = ($page * $limit) - $limit;
    $range = $from.','.$limit;

    $condtions .= ' ORDER BY advertisements.id DESC LIMIT ' . $range;
    $advertisementObj = new Advertisement();
    $results = $advertisementObj->getAdvertisements($condtions, $params);
    $results = sortArrayByPromoted($results);

    return $results;
}

function getAdvertisementsCount($city){
    $advertisementObj = new Advertisement();
    $conditions = null;
    $params = array(':status'=>1,':verificated'=>1);
    if($city!=null){
        $conditions.=' plSafeChars(advertisements.city) =  :city AND ';
        $params += [':city'=>$city];
    }
    $conditions .= '  NOW() BETWEEN  advertisements.created_date AND advertisements.end_date AND advertisements.status = :status AND advertisements.verificated = :verificated ';
    $count = $advertisementObj->getAdvertisementsCount($conditions,$params);
    return $count;
}

function getAdvertisementsCountFromCategory($city,$category){
    $advertisementObj = new Advertisement();
    $conditions = null;
    $params = array(':status'=>1, ':verificated'=>1);

    if($city!=null){
        $conditions.=' plSafeChars(advertisements.city) = :city AND ';
        $params += [':city'=>$city];
    }

    if($category!=null){
        $conditions.=' plSafeChars(categories.name) = :categoryName AND ';
        $params += [':categoryName'=>$category];
    }
    $conditions .= '   NOW() BETWEEN  advertisements.created_date AND advertisements.end_date AND advertisements.status = :status AND advertisements.verificated = :verificated ';

    $count = $advertisementObj->getAdvertisementsCount($conditions,$params);
        return $count;
}

function getAdvertisementsCountFromUser($userId){
    $advertisementObj = new Advertisement();
    $conditions = null;
    $conditions .= ' advertisements.user_type = "user" AND advertisements.user_id = :userId AND  NOW() BETWEEN  advertisements.created_date AND advertisements.end_date AND advertisements.status = :status AND advertisements.verificated = :verificated ';
    $params = array(':userId'=>$userId, ':status'=>1, ':verificated'=>1);
    $count = $advertisementObj->getAdvertisementsCount($conditions,$params);
        return $count;
}

function getAdvertisementsCountFromCompany($userId){
    $advertisementObj = new Advertisement();
    $conditions = null;
    $conditions .= ' advertisements.user_type = "company" AND advertisements.user_id = :userId AND  NOW() BETWEEN  advertisements.created_date AND advertisements.end_date AND advertisements.status = :status AND advertisements.verificated = :verificated ';
    $params = array(':userId'=>$userId,':status'=>1,':verificated'=>1);
    $count = $advertisementObj->getAdvertisementsCount($conditions,$params);
        return $count;
}

function getAdvertisementsCountFromTag($tag){
    $advertisementObj = new Advertisement();
    $conditions = null;
    $params = array(':status'=>1,':verificated'=>1);
    $conditions .= ' ((plSafeChars(advertisements.tags) LIKE "%'.$tag.'%") OR (advertisements.tags LIKE "%'.$tag.'%")) AND  NOW() BETWEEN  advertisements.created_date AND advertisements.end_date AND advertisements.status = :status AND advertisements.verificated = :verificated ';

    $count = $advertisementObj->getAdvertisementsCount($conditions,$params);
    return $count;
}

function getAdvertisementsCountFromWordAndTag($tag){
    $advertisementObj = new Advertisement();
    $conditions = null;
    $params = null;
    $conditions .= ' ((plSafeChars(advertisements.tags) LIKE "%'.$tag.'%") OR (advertisements.tags LIKE "%'.$tag.'%") OR (plSafeChars(advertisements.title) LIKE "%'.$tag.'%") OR (advertisements.title LIKE "%'.$tag.'%")) AND  NOW() BETWEEN  advertisements.created_date AND advertisements.end_date ';

    $count = $advertisementObj->getAdvertisementsCount($conditions,$params);
    return $count ;
}



function showAdvertisementsUserPagination($userId,$status,$limit,$kindSafe,$type,$sort){

    $currentPage = (isset($_POST['page'])?(int)htmlspecialchars($_POST['page']):1);
    $pagesCount = ceil(getUserAdvertisementsCount($userId,$status,$kindSafe) / $limit);
$pagesCount = ($pagesCount>0?$pagesCount:1);
    $allPages = $pagesCount;
    $pageLoop = 1;
    $pagination = null;
    $pagination.= '
    <form method="post" action="">
    <input type="text" hidden readonly name="type" value="'.$type.'">
<input type="text" hidden readonly name="kind" value="'.$kindSafe.'">
<input type="text" hidden readonly name="sort" value="'.$sort.'">
                <ul class="pagination list">
                    <li class="pagination__item">
                        <button class="pagination__link" type="submit" name="page" value="1" title="Przejdź na stronę">
                            <svg class="pagination__svg" xmlns="http://www.w3.org/2000/svg"
                                xmlns:xlink="http://www.w3.org/1999/xlink" version="1.1" viewBox="0 0 24 24">
                                <path d="M15.41,16.58L10.83,12L15.41,7.41L14,6L8,12L14,18L15.41,16.58Z"></path>
                            </svg>
                        </button>
                    </li>
                    ';
    if($currentPage >= 3 && $currentPage + 2 <= $pagesCount){
        for($i = max(1, $currentPage - 2), $iMax = min($currentPage + 2, $pagesCount); $i <= $iMax; $i++){
            $pagination.='
                    <li class="pagination__item">
                        <'.($currentPage === (int)$i ? 'span':'button').' class="pagination__link '.($currentPage === (int)$i ? 'active':'').'" value="'.$i.'" '.($currentPage === (int)$i ? '':'type="submit"').' name="page" title="Przejdź na stronę">'.$i.'</'.($currentPage === (int)$i ? 'span':'button').'>
                    </li>
                    ';
        }
    }else{
        if($currentPage <= 2 && $currentPage>0){
            while($pagesCount > 0 && $pageLoop<=5){
                $pagination.='
                    <li class="pagination__item">
                        <'.($currentPage === $pageLoop? 'span':'button').' class="pagination__link '.($currentPage === $pageLoop ? 'active':'').'" value="'.$pageLoop.'" name="page" '.($currentPage === $pageLoop ? '':'type="submit"').' title="Przejdź na stronę">'.$pageLoop.'</'.($currentPage === $pageLoop ? 'span':'button').'>
                    </li>
                    ';
                $pageLoop++;
                $pagesCount--;
            }
        }elseif($currentPage>0 && $currentPage<=$pagesCount){
            $endPag = $pagesCount-4;
            while($endPag<=$pagesCount){
                if($endPag > 0){
                    $pagination.='
                    <li class="pagination__item">
                        <'.($currentPage === (int)$endPag ? 'span':'button').' class="pagination__link '.($currentPage === (int)$endPag ? 'active':'').'" value="'.$endPag.'" type="submit" name="page" title="Przejdź na stronę">'.$endPag.'</'.($currentPage === (int)$endPag ? 'span':'button').'>
                    </li>
                    ';
                }
                $endPag++;
            }
        }
    }
    $pagination.=' 

                    <li class="pagination__item">
                        <button class="pagination__link" value="'.$allPages.'" name="page" type="submit" title="Przejdź na stronę">
                            <svg class="pagination__svg" xmlns="http://www.w3.org/2000/svg"
                                xmlns:xlink="http://www.w3.org/1999/xlink" version="1.1" viewBox="0 0 24 24">
                                <path d="M8.59,16.58L13.17,12L8.59,7.41L10,6L16,12L10,18L8.59,16.58Z"></path>
                            </svg>
                        </button>
                    </li>
                </ul>
                </form>
    ';
    echo $pagination;
}

function searchAdvertisementsUserPagination($userId,$status,$limit,$kindSafe,$type,$sort,$query){
    $currentPage = (isset($_POST['page'])?(int)htmlspecialchars($_POST['page']):1);
    $pagesCount = ceil(searchCountUserAdvertisements($userId,$status,$kindSafe,$query) / $limit);
$pagesCount = ($pagesCount>0?$pagesCount:1);
    $allPages = $pagesCount;
    $pageLoop = 1;
    $pagination = null;
    $pagination.= '
    <form method="post" action="">
    <input type="text" hidden readonly name="type" value="'.$type.'">
<input type="text" hidden readonly name="kind" value="'.$kindSafe.'">
<input type="text" hidden readonly name="sort" value="'.$sort.'">
<input type="text" hidden readonly name="query" value="'.$query.'">
                <ul class="pagination list">
                    <li class="pagination__item">
                        <button class="pagination__link" type="submit" name="page" value="1" title="Przejdź na stronę">
                            <svg class="pagination__svg" xmlns="http://www.w3.org/2000/svg"
                                xmlns:xlink="http://www.w3.org/1999/xlink" version="1.1" viewBox="0 0 24 24">
                                <path d="M15.41,16.58L10.83,12L15.41,7.41L14,6L8,12L14,18L15.41,16.58Z"></path>
                            </svg>
                        </button>
                    </li>
                    ';
    if($currentPage >= 3 && $currentPage + 2 <= $pagesCount){
        for($i = max(1, $currentPage - 2), $iMax = min($currentPage + 2, $pagesCount); $i <= $iMax; $i++){
            $pagination.='
                    <li class="pagination__item">
                        <'.($currentPage === (int)$i ? 'span':'button').' class="pagination__link '.($currentPage === (int)$i ? 'active':'').'" value="'.$i.'" '.($currentPage === (int)$i ? '':'type="submit"').' name="page" title="Przejdź na stronę">'.$i.'</'.($currentPage === (int)$i ? 'span':'button').'>
                    </li>
                    ';
        }
    }else{
        if($currentPage <= 2 && $currentPage>0){
            while($pagesCount > 0 && $pageLoop<=5){
                $pagination.='
                    <li class="pagination__item">
                        <'.($currentPage === $pageLoop ? 'span':'button').' class="pagination__link '.($currentPage === $pageLoop ? 'active':'').'" value="'.$pageLoop.'" name="page" '.($currentPage === $pageLoop ? '':'type="submit"').' title="Przejdź na stronę">'.$pageLoop.'</'.($currentPage === $pageLoop ? 'span':'button').'>
                    </li>
                    ';
                $pageLoop++;
                $pagesCount--;
            }
        }elseif($currentPage>0 && $currentPage<=$pagesCount){
            $endPag = $pagesCount-4;
            while($endPag<=$pagesCount){
                if($endPag > 0){
                    $pagination.='
                    <li class="pagination__item">
                        <'.($currentPage === (int)$endPag ? 'span':'button').' class="pagination__link '.($currentPage === (int)$endPag ? 'active':'').'" value="'.$endPag.'" '.($currentPage === (int)$endPag ? '':'type="submit"').' name="page" title="Przejdź na stronę">'.$endPag.'</'.($currentPage === (int)$endPag ? 'span':'button').'>
                    </li>
                    ';
                }
                $endPag++;
            }
        }
    }
    $pagination.=' 

                    <li class="pagination__item">
                        <button class="pagination__link" value="'.$allPages.'" name="page" type="submit" title="Przejdź na stronę">
                            <svg class="pagination__svg" xmlns="http://www.w3.org/2000/svg"
                                xmlns:xlink="http://www.w3.org/1999/xlink" version="1.1" viewBox="0 0 24 24">
                                <path d="M8.59,16.58L13.17,12L8.59,7.41L10,6L16,12L10,18L8.59,16.58Z"></path>
                            </svg>
                        </button>
                    </li>
                </ul>
                </form>
    ';
    echo $pagination;
}


function showAdvertisementPagination($city, $page,$limit){
    if(isset($_GET['category'])){
        $category = htmlspecialchars($_GET['category']);
    }
    if(isset($_GET['m'])){
        $m = htmlspecialchars($_GET['m']);
    }

    $link='/ogloszenia'.(!empty($category)?'/'.$category.'/':'').(!empty($m)?'/'.$m.'/':'');


    $pagesCount = ceil(getAdvertisementsCount($city) / $limit);
$pagesCount = ($pagesCount>0?$pagesCount:1);
    $allPages = $pagesCount;
    $pageLoop = 1;
    $currentPage = (isset($_GET['p'])?(int)(htmlspecialchars($_GET['p'])):1);
    $pagination = null;
    $pagination.= '
    
                <ul class="pagination list">
                    <li class="pagination__item">
                        <a class="pagination__link" href="'.$link.'/1/" title="Przejdź na stronę">
                            <svg class="pagination__svg" xmlns="http://www.w3.org/2000/svg"
                                xmlns:xlink="http://www.w3.org/1999/xlink" version="1.1" viewBox="0 0 24 24">
                                <path d="M15.41,16.58L10.83,12L15.41,7.41L14,6L8,12L14,18L15.41,16.58Z"></path>
                            </svg>
                        </a>
                    </li>
                    ';
    if($currentPage >= 3 && $currentPage + 2 <= $pagesCount){
        for($i = max(1, $currentPage - 2), $iMax = min($currentPage + 2, $pagesCount); $i <= $iMax; $i++){
            $pagination.='
                    <li class="pagination__item">
                        <'.($currentPage === (int)$i ? 'span':'a').' class="pagination__link '.($currentPage === (int)$i ? 'active':'').'" '.($currentPage === (int)$i ? '':'href="'.$link.'/'.$i.'/" ').' title="Przejdź na stronę">'.$i.'</'.($currentPage == (int)$i ? 'span':'a').'>
                    </li>
                    ';
        }
    }else{
        if($currentPage <= 2 && $currentPage>0){
            while($pagesCount > 0 && $pageLoop<=5){
                $pagination.='
                    <li class="pagination__item">
                        <'.($currentPage == $pageLoop ? 'span':'a').' class="pagination__link '.($currentPage === $pageLoop ? 'active':'').'" '.($currentPage == $pageLoop ? '':'href="'.$link.'/'.$pageLoop.'/"').' title="Przejdź na stronę">'.$pageLoop.'</'.($currentPage == (int)$i ? 'span':'a').'>
                    </li>
                    ';
                $pageLoop++;
                $pagesCount--;
            }
        }elseif($currentPage>0 && $currentPage<=$pagesCount){
            $endPag = $pagesCount-4;
            while($endPag<=$pagesCount){
                if($endPag > 0){
                    $pagination.='
                    <li class="pagination__item">
                        <'.($currentPage === (int)$endPag ? 'span':'a').' class="pagination__link '.($currentPage === (int)$endPag ? 'active':'').'" '.($currentPage === (int)$endPag ? '':'href="'.$link.'/'.$endPag.'/"').' title="Przejdź na stronę">'.$endPag.'</'.($currentPage === (int)$endPag ? 'span':'a').'>
                    </li>
                    ';
                }

                $endPag++;
            }
        }
    }
    $pagination.=' 

                    <li class="pagination__item">
                        <a class="pagination__link" href="'.$link.'/'.$allPages.'/" title="Przejdź na stronę">
                            <svg class="pagination__svg" xmlns="http://www.w3.org/2000/svg"
                                xmlns:xlink="http://www.w3.org/1999/xlink" version="1.1" viewBox="0 0 24 24">
                                <path d="M8.59,16.58L13.17,12L8.59,7.41L10,6L16,12L10,18L8.59,16.58Z"></path>
                            </svg>
                        </a>
                    </li>
                </ul>
    ';
    echo $pagination;
}

function showAdvertisementPaginationFromCategory($city,$category,$page,$limit){
    if(isset($_GET['category'])){
        $category = htmlspecialchars($_GET['category']);
    }
    if(isset($_GET['m'])){
        $m = htmlspecialchars($_GET['m']);
    }

    $link='/ogloszenia'.(!empty($category)?'/'.$category:'').(!empty($m)?'/'.$m:'');

    $pagesLimit = 5;
    $pagesCount = ceil((getAdvertisementsCountFromCategory($city,$category)>0?getAdvertisementsCountFromCategory($city,$category):1) / $limit);
$pagesCount = ($pagesCount>0?$pagesCount:1);
    $allPages = $pagesCount;
    $pageLoop = 1;
    $currentPage = (isset($_GET['p'])?(int)htmlspecialchars($_GET['p']):1);
    $pagination = null;
    $pagination.= '
    
                <ul class="pagination list">
                    <li class="pagination__item">
                        <a class="pagination__link" href="'.$link.'/1/" title="Przejdź na stronę">
                            <svg class="pagination__svg" xmlns="http://www.w3.org/2000/svg"
                                xmlns:xlink="http://www.w3.org/1999/xlink" version="1.1" viewBox="0 0 24 24">
                                <path d="M15.41,16.58L10.83,12L15.41,7.41L14,6L8,12L14,18L15.41,16.58Z"></path>
                            </svg>
                        </a>
                    </li>
                    ';
    if($currentPage >= 3 && $currentPage + 2 <= $pagesCount){
        for($i = max(1, $currentPage - 2), $iMax = min($currentPage + 2, $pagesCount); $i <= $iMax; $i++){
            $pagination.='
                    <li class="pagination__item">
                        <'.($currentPage === (int)$i ? 'span':'a').' class="pagination__link '.($currentPage === (int)$i ? 'active':'').'" '.($currentPage === (int)$i ? '':'href="'.$link.'/'.$i.'/" ').' title="Przejdź na stronę">'.$i.'</'.($currentPage === (int)$i ? 'span':'a').'>
                    </li>
                    ';
        }
    }else{
        if($currentPage <= 2 && $currentPage>0){
            while($pagesCount > 0 && $pageLoop<=5){
                $pagination.='
                    <li class="pagination__item">
                        <'.($currentPage === $pageLoop ? 'span':'a').' class="pagination__link '.($currentPage === $pageLoop ? 'active':'').'" '.($currentPage === $pageLoop ? '':'href="'.$link.'/'.$pageLoop.'/"').' title="Przejdź na stronę">'.$pageLoop.'</'.($currentPage === $pageLoop ? 'span':'a').'>
                    </li>
                    ';
                $pageLoop++;
                $pagesCount--;
            }
        }elseif($currentPage>0 && $currentPage<=$pagesCount){
            $endPag = $pagesCount-4;
            while($endPag<=$pagesCount){
                if($endPag>0){
                    $pagination.='
                    <li class="pagination__item">
                        <'.($currentPage === (int)$endPag ? 'span':'a').' class="pagination__link '.($currentPage === (int)$endPag ? 'active':'').'" '.($currentPage === (int)$endPag ? '':'href="'.$link.'/'.$endPag.'/"').' title="Przejdź na stronę">'.$endPag.'</'.($currentPage === (int)$endPag ? 'span':'a').'>
                    </li>
                    ';
                }
                $endPag++;
            }
        }
    }
    $pagination.=' 

                    <li class="pagination__item">
                        <a class="pagination__link" href="'.$link.'/'.$allPages.'/" title="Przejdź na stronę">
                            <svg class="pagination__svg" xmlns="http://www.w3.org/2000/svg"
                                xmlns:xlink="http://www.w3.org/1999/xlink" version="1.1" viewBox="0 0 24 24">
                                <path d="M8.59,16.58L13.17,12L8.59,7.41L10,6L16,12L10,18L8.59,16.58Z"></path>
                            </svg>
                        </a>
                    </li>
                </ul>
    ';
    echo $pagination;
}

function showAdvertisementPaginationFromUser($userId,$userName,$page,$limit){

    $link='/profil/'.$userName.','.$userId.'/ogloszenia';

    $pagesLimit = 5;
    $pagesCount = ceil(getAdvertisementsCountFromUser($userId) / $limit);
$pagesCount = ($pagesCount>0?$pagesCount:1);
    $allPages = $pagesCount;
    $pageLoop = 1;
    $currentPage = (isset($_GET['p'])?(int)htmlspecialchars($_GET['p']):1);
    $pagination = null;
    $pagination.= '
    
                <ul class="pagination list">
                    <li class="pagination__item">
                        <a class="pagination__link" href="'.$link.'/1/" title="Przejdź na stronę">
                            <svg class="pagination__svg" xmlns="http://www.w3.org/2000/svg"
                                xmlns:xlink="http://www.w3.org/1999/xlink" version="1.1" viewBox="0 0 24 24">
                                <path d="M15.41,16.58L10.83,12L15.41,7.41L14,6L8,12L14,18L15.41,16.58Z"></path>
                            </svg>
                        </a>
                    </li>
                    ';
    if($currentPage >= 3 && $currentPage + 2 <= $pagesCount){
        for($i = max(1, $currentPage - 2), $iMax = min($currentPage + 2, $pagesCount); $i <= $iMax; $i++){
            $pagination.='
                    <li class="pagination__item">
                        <'.($currentPage === (int)$i ? 'span':'button').' class="pagination__link '.($currentPage === (int)$i ? 'active':'').'" '.($currentPage === (int)$i ? '':'href="'.$link.'/'.$i.'/" ').' title="Przejdź na stronę">'.$i.'</a>
                    </li>
                    ';
        }
    }else{
        if($currentPage <= 2 && $currentPage>0){
            while($pagesCount > 0 && $pageLoop<=5){
                $pagination.='
                    <li class="pagination__item">
                        <'.($currentPage === $pageLoop ? 'span':'a').' class="pagination__link '.($currentPage === $pageLoop ? 'active':'').'" '.($currentPage === $pageLoop ? '':'href="'.$link.'/'.$pageLoop.'/"').' title="Przejdź na stronę">'.$pageLoop.'</'.($currentPage === $pageLoop ? 'span':'a').'>
                    </li>
                    ';
                $pageLoop++;
                $pagesCount--;
            }
        }elseif($currentPage>0 && $currentPage<=$pagesCount){
            $endPag = $pagesCount-4;
            while($endPag<=$pagesCount){
                if($endPag>0){
                    $pagination.='
                    <li class="pagination__item">
                        <'.($currentPage === (int)$endPag ? 'span':'a').' class="pagination__link '.($currentPage === (int)$endPag ? 'active':'').'" '.($currentPage === (int)$endPag ? '':'href="'.$link.'/'.$endPag.'/"').' title="Przejdź na stronę">'.$endPag.'</'.($currentPage === $pageLoop ? 'span':'a').'>
                    </li>
                    ';
                }
                $endPag++;
            }
        }
    }
    $pagination.=' 

                    <li class="pagination__item">
                        <a class="pagination__link" href="'.$link.'/'.$allPages.'/" title="Przejdź na stronę">
                            <svg class="pagination__svg" xmlns="http://www.w3.org/2000/svg"
                                xmlns:xlink="http://www.w3.org/1999/xlink" version="1.1" viewBox="0 0 24 24">
                                <path d="M8.59,16.58L13.17,12L8.59,7.41L10,6L16,12L10,18L8.59,16.58Z"></path>
                            </svg>
                        </a>
                    </li>
                </ul>
    ';
    return $pagination;
}


function showAdvertisementPaginationFromCompany($companyId,$companyName,$page,$limit){

    $link='/firmy/'.$companyName.','.$companyId.'/ogloszenia';

    $pagesLimit = 5;
    $pagesCount = ceil(getAdvertisementsCountFromCompany($companyId) / $limit);
$pagesCount = ($pagesCount>0?$pagesCount:1);
    $allPages = $pagesCount;
    $pageLoop = 1;
    $currentPage = (isset($_GET['p'])?(int)htmlspecialchars($_GET['p']):1);
    $pagination = null;
    $pagination.= '
    
                <ul class="pagination list">
                    <li class="pagination__item">
                        <a class="pagination__link" href="'.$link.'/1/" title="Przejdź na stronę">
                            <svg class="pagination__svg" xmlns="http://www.w3.org/2000/svg"
                                xmlns:xlink="http://www.w3.org/1999/xlink" version="1.1" viewBox="0 0 24 24">
                                <path d="M15.41,16.58L10.83,12L15.41,7.41L14,6L8,12L14,18L15.41,16.58Z"></path>
                            </svg>
                        </a>
                    </li>
                    ';
    if($currentPage >= 3 && $currentPage + 2 <= $pagesCount){
        for($i = max(1, $currentPage - 2), $iMax = min($currentPage + 2, $pagesCount); $i <= $iMax; $i++){
            $pagination.='
                    <li class="pagination__item">
                        <'.($currentPage === (int)$i ? 'span':'a').' class="pagination__link '.($currentPage === (int)$i ? 'active':'').'" '.($currentPage === (int)$i ? '':'href="'.$link.'/'.$i.'/" ').' title="Przejdź na stronę">'.$i.'</'.($currentPage === (int)$i ? 'span':'a').'>
                    </li>
                    ';
        }
    }else{
        if($currentPage <= 2 && $currentPage>0){
            while($pagesCount > 0 && $pageLoop<=5){
                $pagination.='
                    <li class="pagination__item">
                        <'.($currentPage === $pageLoop ? 'span':'a').' class="pagination__link '.($currentPage === $pageLoop ? 'active':'').'" '.($currentPage === $pageLoop ? '':'href="'.$link.'/'.$pageLoop.'/"').' title="Przejdź na stronę">'.$pageLoop.'</'.($currentPage === $pageLoop ? 'span':'a').'>
                    </li>
                    ';
                $pageLoop++;
                $pagesCount--;
            }
        }elseif($currentPage>0 && $currentPage<=$pagesCount){
            $endPag = $pagesCount-4;
            while($endPag<=$pagesCount){
                if($endPag>0){
                    $pagination.='
                    <li class="pagination__item">
                        <'.($currentPage === (int)$endPag ? 'span':'a').' class="pagination__link '.($currentPage === (int)$endPag ? 'active':'').'" '.($currentPage === (int)$endPag ? '':'href="'.$link.'/'.$endPag.'/"').' title="Przejdź na stronę">'.$endPag.'</'.($currentPage === (int)$endPag ? 'span':'a').'>
                    </li>
                    ';
                }
                $endPag++;
            }
        }
    }
    $pagination.=' 

                    <li class="pagination__item">
                        <a class="pagination__link" href="'.$link.'/'.$allPages.'/" title="Przejdź na stronę">
                            <svg class="pagination__svg" xmlns="http://www.w3.org/2000/svg"
                                xmlns:xlink="http://www.w3.org/1999/xlink" version="1.1" viewBox="0 0 24 24">
                                <path d="M8.59,16.58L13.17,12L8.59,7.41L10,6L16,12L10,18L8.59,16.58Z"></path>
                            </svg>
                        </a>
                    </li>
                </ul>
    ';
    return $pagination;
}


function showAdvertisementPaginationFromTag($tag,$page,$limit){
    if(isset($_GET['tag'])){
        $tag = htmlspecialchars($_GET['tag']);
    }
    $link='/tag/ogloszenia'.(!empty($tag)?'/'.$tag:'');
    $pagesCount = ceil(getAdvertisementsCountFromTag($tag) / $limit);
    $pagesCount = ($pagesCount>0?$pagesCount:1);
    $allPages = $pagesCount;
    $pageLoop = 1;
    $currentPage = (isset($_GET['p'])?(int)(htmlspecialchars($_GET['p'])):1);
    (string) $pagination ;

    $pagination.= '
    
                <ul class="pagination list">
                    <li class="pagination__item">
                        <a class="pagination__link" href="'.$link.'/1/" title="Przejdź na stronę">
                            <svg class="pagination__svg" xmlns="http://www.w3.org/2000/svg"
                                xmlns:xlink="http://www.w3.org/1999/xlink" version="1.1" viewBox="0 0 24 24">
                                <path d="M15.41,16.58L10.83,12L15.41,7.41L14,6L8,12L14,18L15.41,16.58Z"></path>
                            </svg>
                        </a>
                    </li>
                    ';
    if($currentPage >= 3 && $currentPage + 2 <= $pagesCount){
        for($i = max(1, $currentPage - 2), $iMax = min($currentPage + 2, $pagesCount); $i <= $iMax; $i++){
            $pagination.='
                    <li class="pagination__item">
                        <'.($currentPage === (int)$i ? 'span':'a').' class="pagination__link '.($currentPage === (int)$i ? 'active':'').'" '.($currentPage === (int)$i ? '':'href="'.$link.'/'.$i.'/" ').' title="Przejdź na stronę">'.$i.'</'.($currentPage === (int)$i ? 'span':'a').'>
                    </li>
                    ';
        }
    }else{
        if($currentPage <= 2 && $currentPage>0){
            while($pagesCount > 0 && $pageLoop<=5){
                $pagination.='
                    <li class="pagination__item">
                        <'.($currentPage === $pageLoop ? 'span':'a').' class="pagination__link '.($currentPage === $pageLoop ? 'active':'').'" '.($currentPage === $pageLoop ? '':'href="'.$link.'/'.$pageLoop.'/"').' title="Przejdź na stronę">'.$pageLoop.'</'.($currentPage === $pageLoop ? 'span':'a').'>
                    </li>
                    ';
                $pageLoop++;
                $pagesCount--;
            }
        }elseif($currentPage>0 && $currentPage<=$pagesCount){
            $endPag = $pagesCount-4;
            while($endPag<=$pagesCount){
                if($endPag>0){
                    $pagination.='
                    <li class="pagination__item">
                        <'.($currentPage === (int)$endPag ? 'span':'a').' class="pagination__link '.($currentPage === (int)$endPag ? 'active':'').'" '.($currentPage === (int)$endPag ? '':'href="'.$link.'/'.$endPag.'/"
').' title="Przejdź na stronę">'.$endPag.'</'.($currentPage === (int)$endPag ? 'span':'a').'>
                    </li>
                    ';
                }
                $endPag++;
            }
        }
    }
    $pagination.=' 

                    <li class="pagination__item">
                        <a class="pagination__link" href="'.$link.'/'.$allPages.'/" title="Przejdź na stronę">
                            <svg class="pagination__svg" xmlns="http://www.w3.org/2000/svg"
                                xmlns:xlink="http://www.w3.org/1999/xlink" version="1.1" viewBox="0 0 24 24">
                                <path d="M8.59,16.58L13.17,12L8.59,7.41L10,6L16,12L10,18L8.59,16.58Z"></path>
                            </svg>
                        </a>
                    </li>
                </ul>
    ';
    echo $pagination;
}


function showAdvertisementPaginationFromWordAndTag($keyword,$page,$limit){

    $link='/szukaj/ogloszenia'.(!empty($keyword)?'/'.$keyword:'');

    $pagesCount = ceil(getAdvertisementsCountFromWordAndTag($keyword) / $limit);
    $pagesCount = ($pagesCount>0?$pagesCount:1);
    $allPages = $pagesCount;
    $pageLoop = 1;
    $currentPage = (isset($_GET['p'])?(int)htmlspecialchars($_GET['p']):1);
    $pagination = null;

    $pagination.= '
    
                <ul class="pagination list">
                    <li class="pagination__item">
                        <a class="pagination__link" href="'.$link.'/1/" title="Przejdź na stronę">
                            <svg class="pagination__svg" xmlns="http://www.w3.org/2000/svg"
                                xmlns:xlink="http://www.w3.org/1999/xlink" version="1.1" viewBox="0 0 24 24">
                                <path d="M15.41,16.58L10.83,12L15.41,7.41L14,6L8,12L14,18L15.41,16.58Z"></path>
                            </svg>
                        </a>
                    </li>
                    ';
    if($currentPage >= 3 && $currentPage + 2 <= $pagesCount){
        for($i = max(1, $currentPage - 2), $iMax = min($currentPage + 2, $pagesCount); $i <= $iMax; $i++){
            $pagination.='
                    <li class="pagination__item">
                        <'.($currentPage === (int)$i ? 'span':'a').' class="pagination__link '.($currentPage === (int)$i ? 'active':'').'" '.($currentPage === (int)$i ? '':'href="'.$link.'/'.$i.'/" ').' title="Przejdź na stronę">'.$i.'</'.($currentPage === (int)$i ? 'span':'a').'>
                    </li>
                    ';
        }
    }else{
        if($currentPage <= 2 && $currentPage>0){
            while($pagesCount > 0 && $pageLoop<=5){
                $pagination.='
                    <li class="pagination__item">
                        <'.($currentPage === $pageLoop ? 'span':'a').' class="pagination__link '.($currentPage === $pageLoop ? 'active':'').'" '.($currentPage === $pageLoop ? '':'href="'.$link.'/'.$pageLoop.'/"').' title="Przejdź na stronę">'.$pageLoop.'</'.($currentPage === $pageLoop ? 'span':'a').'>
                    </li>
                    ';
                $pageLoop++;
                $pagesCount--;
            }
        }elseif($currentPage>0 && $currentPage<=$pagesCount){
            $endPag = $pagesCount-4;
            while($endPag<=$pagesCount){
                if($endPag>0){
                    $pagination.='
                    <li class="pagination__item">
                        <'.($currentPage === (int)$endPag ? 'span':'a').' class="pagination__link '.($currentPage === (int)$endPag ? 'active':'').'" '.($currentPage === (int)$endPag ? '':'href="'.$link.'/'.$endPag.'/"').' title="Przejdź na stronę">'.$endPag.'</'.($currentPage === (int)$endPag ? 'span':'a').'>
                    </li>
                    ';
                }
                $endPag++;
            }
        }
    }
    $pagination.=' 

                    <li class="pagination__item">
                        <a class="pagination__link" href="'.$link.'/'.$allPages.'/" title="Przejdź na stronę">
                            <svg class="pagination__svg" xmlns="http://www.w3.org/2000/svg"
                                xmlns:xlink="http://www.w3.org/1999/xlink" version="1.1" viewBox="0 0 24 24">
                                <path d="M8.59,16.58L13.17,12L8.59,7.41L10,6L16,12L10,18L8.59,16.58Z"></path>
                            </svg>
                        </a>
                    </li>
                </ul>
    ';
    echo $pagination;
}


function getFirstPhoto($advertisement_id){
    $conditions = ' advertisements_galleries.advertisement_id = :advId';
    $params = array(':advId'=>$advertisement_id);
    $conditions .= ' ORDER BY advertisements_galleries.order_place ASC LIMIT 1';
    $advertisementObj = new Advertisement();
    $results = $advertisementObj->getGallery($conditions,$params);
    return $results;
}

function getGallery($advertisement_id){
    $conditions = ' advertisements_galleries.order_place != 0 AND  advertisements_galleries.advertisement_id = :advId';
    $params = array(':advId'=>$advertisement_id);
    $advertisementObj = new Advertisement();
    $results = $advertisementObj->getGallery($conditions,$params);
    return $results;
}


function getAllGallery($advertisement_id){
    $conditions = '  advertisements_galleries.advertisement_id = :advId ORDER BY order_place ASC ';
    $params = array(':advId'=>$advertisement_id);
    $advertisementObj = new Advertisement();
    $results = $advertisementObj->getGallery($conditions,$params);
    return $results;
}

function addAdvertView($id){
    $advertisementObj = new Advertisement();
    $advertisementObj->addView($id);
}

function isAdvertisementTypeExist($type){
    $advertisementObj = new Advertisement();
    if($advertisementObj->isAdvertisementTypeExist($type)){
        return true;
    }else{
        return false;
    }
}

function deleteAdvGallery($advId){
    $advertisementObj = new Advertisement();
    $conditions = ' advertisement_id = :id ';
    $params = array(':id'=>$advId);
    return $advertisementObj->deleteGallery($conditions,$params);
}

function deleteUserAdv($userId){
    $advertisementObj = new Advertisement();
    $advs = get_UserAdvertisements($userId);
    foreach($advs as $adv){
        $advParams = array(':advId'=>$adv['advId']);
        deleteAdvGallery($adv['advId']);
        $advertisementObj->delete('favorites',' favorites.advertisement_id = :advId ',$advParams);
    }
        $params = array(':uid'=>$userId);
        $advertisementObj->delete('advertisements',' advertisements.user_type = "user" AND advertisements.user_id = :uid ',$params);

}

function deleteCompanyAdv($userId){
    $advertisementObj = new Advertisement();
    $advs = get_CompanyAdvertisements($userId);
    foreach($advs as $adv){
        $advParams = array(':advId'=>$adv['advId']);
        deleteAdvGallery($adv['advId']);
        $advertisementObj->delete('favorites',' favorites.advertisement_id = :advId ',$advParams);
    }
    $params = array(':uid'=>$userId);
    $advertisementObj->delete('advertisements',' advertisements.user_type = "company" AND advertisements.user_id = :uid ',$params);
}

function updatePhotoOrder($photoId,$newOrder){
    $advertisementObj = new Advertisement();
    $set = ' advertisements_galleries.order_place = :order';
    $params = array(':order'=>$newOrder);
    $update = $advertisementObj->updatePhotoOrder($photoId,$set,$params);
    return $update;
}

function addPhoto($filename,$order,$advertisementId){
    $advertisementObj = new Advertisement();
    $result = $advertisementObj->addPhoto($filename,$order,$advertisementId);
    return $result;
}

function deletePhoto($photoId,$advertId){
    foreach(getAllGallery($advertId) as $oldItem){
        if((int)$oldItem['id'] === (int)$photoId){
            $oldOrder = $oldItem['order_place'];
            $oldFile = $oldItem['source'];
        }
    }
    $advertisementsObj = new Advertisement();
    $delete = $advertisementsObj->deletePhoto($photoId);
    if($delete){
        foreach(getAllGallery($advertId) as $item){
                if((int)($item['order_place']) > (int)$oldOrder){
                    $newOrder = (int)$item['order_place']-1;
                    updatePhotoOrder($item['id'],$newOrder);
                }
        }
        unlink($_SERVER['DOCUMENT_ROOT']."/uploads/annoucent/".$oldFile);
    }
}

function activateAdvertisement($advertId){
    $advertisementObj = new Advertisement();
    $params = array(':createdDate'=>date('Y-m-d H:i:s'),':endDate'=>date('Y-m-d', strtotime(' + 30 days')),':advertId'=>$advertId,':status'=>1);
    $result = $advertisementObj->update('advertisements', 'created_date = :createdDate, end_date = :endDate, status = :status','advertisements.id =  :advertId',$params);
    return $result;
}


function deactivateAdvertisement($advertId){
    $advertisementObj = new Advertisement();
    $params = array(':endDate'=>date('Y-m-d H:i:s'),':advertId'=>$advertId, ':status'=>2);
    $result = $advertisementObj->update('advertisements', 'end_date = :endDate, status = :status','advertisements.id =  :advertId',$params);
    return $result;
}