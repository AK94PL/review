<?php


function disableEvent($eventId){
    $obj = new Event();
    $params = array(':id'=>$eventId,':status'=>2);
    $disableEvent = $obj->update('events','events.status = :status','events.id = :id',$params);
    return $disableEvent;
}


function addEvent($title,$content,$city,$categoryId,$tags,$date_start,$time_start,$date_end,$time_end,$user_id,$user_type,$picture,$status,$verificated){
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
    $eventObj = new Event();
    $result = $eventObj->addEvent(trim($title),trim($content),trim($city),$categoryId,$tagList,trim($date_start),trim($time_start),trim($date_end),trim($time_end),$user_id,$user_type,$picture,$status,$verificated);
    if($result > 0){
        return $result;
    }else{
        return null;
    }
}


function updateEvent($eventId,$title,$content,$city,$categoryId,$tags,$date_start,$time_start,$date_end,$time_end,$user_id,$user_type,$picture,$status,$verificated,$revision){
    $dateTimeStart = $date_start.' '.$time_start;
    if(!empty($date_end && !empty($time_end))){
        $dateTimeEnd = $date_end.' '.$time_end;
    }else{
        $dateTimeEnd = null;
    }
    $eventObj = new Event();
    $set = ' events.subject = :subject, events.content = :content, events.city = :city, events.tags = :tags,  events.category_id = :categoryId, events.date_start = :dateStart, events.date_end = :dateEnd, events.user_id = :userId, events.user_type = :userType '.($status != 0 ?',events.status = :status':'');
    $params = array(':subject'=>$title, ':content'=>$content, ':city'=>$city,':tags'=>$tags, ':categoryId'=>$categoryId, ':dateStart'=>$dateTimeStart, ':dateEnd'=>$dateTimeEnd,':userId'=>$user_id, ':userType'=>$user_type);
    if($status!=0){
        $params += [':status'=>$status];
    }

    $set .= ' , events.revision = :revision ';
    $params += [':revision'=>$revision];

    if(!empty($verificated)){
        $set .= ' , events.verificated = :verificated ';
        $params += [':verificated'=>$verificated];
    }

    if($picture != null){
        $set .=' , events.image = :image ';
        $params += [':image'=>$picture];
    }
    $results = $eventObj->updateEvent($eventId,$set,$params);
    return $results;
}


function getEventById($id){
    $conditions = '  events.id = :eventId ';
    $params = array(':eventId'=>$id);
    $eventObj = new Event();
    $results = $eventObj->getEvents($conditions,$params);
    return $results;
}


function getAuthor_Event($id){
    $event = getEventById($id);
    $eventArray = ['author'=>$event[0]['user_id'],'author_type'=>$event[0]['user_type']];
    return $eventArray;
}


function getEvent($id, $title){
    $conditions = ' events.status = :status AND events.id = :eventId AND plSafeChars(events.subject) = :eventTitle ';
    $params = array(':status'=>1,':eventId'=>$id,':eventTitle'=>$title);
    $eventObj = new Event();
    $results = $eventObj->getEvents($conditions,$params);
    return $results;
}


function getCurrentEvents($limit, $category){
    $limit = (int)$limit;
    $conditions = null;
    if($category!=null){
        $conditions = ' plSafeChars(categories.name) = :category AND ';
    }

    $conditions .= '  events.status = :status';
        $conditions .= '   AND (NOW() <= date_end OR date_end IS NULL)';

    if($limit != null){
        $conditions .= ' ORDER BY events.id DESC LIMIT '.$limit;
    }
    $params = array(':status'=>1);
    if($category!=null){
        $params += [':category'=>$category];
    }
    $eventObj = new Event();
    $results = $eventObj->getEvents($conditions,$params);
    $results = sortArrayByPromoted($results);
    return $results;
}

function getCurrentEvents_Tag($limit, $tag){
    $conditions = null;
    if($tag!=null){
        $conditions = ' ( (plSafeChars(events.tags) LIKE  "%'.$tag.'%") OR (events.tags LIKE  "%'.$tag.'%") ) AND ';
    }
//    $conditions .= '  events.status = :status AND date_start <= NOW() AND NOW() <= date_end ';
    $conditions .= '  events.status = :status ';
    if($limit != null){
        $conditions .= ' ORDER BY events.id DESC LIMIT '.$limit;
    }
    $params = array(':status'=>1);
    $eventObj = new Event();
    $results = $eventObj->getEvents($conditions,$params);
    $results = sortArrayByPromoted($results);
    return $results;
}

function getCurrentEvents_WordAndTag($limit, $word){
    $conditions = null;
    if($word!=null){
        $conditions = ' ( (plSafeChars(events.subject) LIKE  "%'.$word.'%") OR (events.subject LIKE  "%'.$word.'%") OR (plSafeChars(events.tags) LIKE  "%'.$word.'%") OR (events.tags LIKE  "%'.$word.'%") ) AND ';
    }
//    $conditions .= '  events.status = :status AND date_start <= NOW() AND NOW() <= date_end ';
    $conditions .= '  events.status = :status  ';
    if($limit != null){
        $conditions .= ' ORDER BY events.id DESC LIMIT '.$limit;
    }
    $params = array(':status'=>1);
    $eventObj = new Event();
    $results = $eventObj->getEvents($conditions,$params);
    $results = sortArrayByPromoted($results);
    return $results;
}

function getAllEventsFromCategory($city,$categoryName,$page,$limit){
    $from = ($page * $limit) - $limit;
    $range = $from.','.$limit;
    $conditions=null;
    if($city!=null){
        $conditions =' plSafeChars(events.city) = :city AND ';
    }
//    $conditions .= '  events.status = :status AND date_start <= NOW() AND NOW() <= date_end AND plSafeChars(categories.name) = :categoryName ';
    $conditions .= '  events.status = :status ';
    if(isCategoryExist($categoryName))
    {
        $conditions.=' AND plSafeChars(categories.name) = :categoryName ';

    }
    $conditions .= ' ORDER BY events.id DESC LIMIT '.$range;
    if(isCategoryExist($categoryName))
    {
        $params = array(':status'=>1,':categoryName'=>$categoryName);
    }
    if($city!=null)
    {
        $params += [':city'=>$city];
    }
    $eventObj = new Event();
    $results = $eventObj->getEvents($conditions,$params);

        $results = sortArrayByPromoted($results);
        return $results;

}

function getAllEventsFromUser($userId,$page,$limit){
    $conditions=null;
//    $conditions .= '  events.status = :status AND date_start <= NOW() AND NOW() <= date_end AND events.user_type = "user" AND events.user_id = :userId ';
    $conditions .= '  events.status = :status AND events.user_type = "user" AND events.user_id = :userId ';
    if($limit!=null){
        $from = ($page * $limit) - $limit;
        $range = $from.','.$limit;
        $conditions .= ' ORDER BY events.id DESC LIMIT '.$range;
    }else{
        $conditions .=' ORDER BY events.id DESC ';
    }
    $params = array(':status'=>1,':userId'=>$userId);
    $eventObj = new Event();
    $results = $eventObj->getEvents($conditions,$params);

        $results = sortArrayByPromoted($results);
        return $results;

}



function getAllEventsFromCompany($userId,$page,$limit){
    $conditions=null;
//    $conditions .= '  events.status = :status AND date_start <= NOW() AND NOW() <= date_end AND events.user_type = "company" AND events.user_id = :userId ';
    $conditions .= '  events.status = :status  AND events.user_type = "company" AND events.user_id = :userId ';

    if($limit!=null){
        $from = ($page * $limit) - $limit;
        $range = $from.','.$limit;
        $conditions .= ' ORDER BY events.id DESC LIMIT '.$range;
    }else{
        $conditions .=' ORDER BY events.id DESC ';
    }
    $params = array(':status'=>1,':userId'=>$userId);
    $eventObj = new Event();
    $results = $eventObj->getEvents($conditions,$params);

        $results = sortArrayByPromoted($results);
        return $results;

}

function getAllEventsFromTag($tag,$page,$limit){
    $from = ($page * $limit) - $limit;
    $range = $from.','.$limit;
    $conditions=null;

//    $conditions .= '  events.status = :status AND date_start <= NOW() AND NOW() <= date_end AND ( (plSafeChars(events.tags) LIKE "%'.$tag.'%") OR  (events.tags LIKE "%'.$tag.'%") )';
   $conditions .= '  events.status = :status AND ( (plSafeChars(events.tags) LIKE "%'.$tag.'%") OR  (events.tags LIKE "%'.$tag.'%") )';


    $conditions .= ' ORDER BY events.id DESC LIMIT '.$range;
    $params = array(':status'=>1);

    $eventObj = new Event();
    $results = $eventObj->getEvents($conditions,$params);

        $results = sortArrayByPromoted($results);
        return $results;

}


function getAllEventsFromWordAndTag($word,$page,$limit){
    $from = ($page * $limit) - $limit;
    $range = $from.','.$limit;
    $conditions=null;

//    $conditions .= '  events.status = :status AND date_start <= NOW() AND NOW() <= date_end AND ( (plSafeChars(events.tags) LIKE "%'.$word.'%") OR  (events.tags LIKE "%'.$word.'%") OR (plSafeChars(events.subject) LIKE "%'.$word.'%") OR  (events.subject LIKE "%'.$word.'%"))';
    $conditions .= '  events.status = :status AND ( (plSafeChars(events.tags) LIKE "%'.$word.'%") OR  (events.tags LIKE "%'.$word.'%") OR (plSafeChars(events.subject) LIKE "%'.$word.'%") OR  (events.subject LIKE "%'.$word.'%"))';

    $conditions .= ' ORDER BY events.id DESC LIMIT '.$range;
    $params = array(':status'=>1);

    $eventObj = new Event();
    $results = $eventObj->getEvents($conditions,$params);
    $results = sortArrayByPromoted($results);
    return $results;

}

function getAllEvents($city,$page,$limit){
    $from = ($page * $limit) - $limit;
    $conditions = null;
    if($city!=null){
        $conditions = ' plSafeChars(events.city) = :city AND ';
    }
    $conditions .= '  events.status = :status AND events.verificated = :verificated  ';

    $range = $from.','.$limit;
    $conditions .= ' ORDER BY events.id DESC LIMIT '.$range;
    $params = array(':status'=>1,'verificated'=>1);
    if($city!=null){
        $params += [':city'=>$city];
    }
    $eventObj = new Event();
    $results = $eventObj->getEvents($conditions,$params);
    $results = sortArrayByPromoted($results);
    return $results;
}

function getUserEvents($userId,$status,$page,$limit,$sort){
    $userCompanies = getUserCompanies($userId,1);
    $conditions = '((events.user_type = "user" AND events.user_id = :userId AND users.id = events.user_id) ';
    $params = array(':status'=>$status,':userId'=>$userId);
    $i = 1;
    foreach($userCompanies as $uc){
        $conditions.=' OR (events.user_type = "company" AND events.user_id = :companyId'.$i.')';
        $params += [':companyId'.$i=>$uc['companyId']];
        $i++;
    }
    $conditions .= ' ) AND events.status = :status ';
    $from = ($page * $limit) - $limit;
    $range = $from.','.$limit;
    $conditions .= ' ORDER BY '.$sort.' LIMIT '.$range;
    $eventObj = new Event();
    $results = $eventObj->getEvents($conditions,$params);
    return $results;
}

function searchUserEvents($userId,$status,$page,$limit,$sort,$query){
    $userCompanies = getUserCompanies($userId,1);
    $conditions = '(events.subject LIKE "%'.$query.'%" OR events.tags LIKE "%'.$query.'%") AND ((events.user_type = "user" AND events.user_id = :userId AND users.id = :userId ) ';
    $params = array(':status'=>$status,':userId'=>$userId);
    $i = 1;
    foreach($userCompanies as $uc){
        $conditions.=' OR (events.user_type = "company" AND events.user_id = :companyId'.$i.')';
        $params += [':companyId'.$i=>$uc['companyId']];
        $i++;
    }
    $conditions .= ' ) AND events.status = :status ';
    $from = ($page * $limit) - $limit;
    $range = $from.','.$limit;
    $conditions .= ' ORDER BY '.$sort.' LIMIT '.$range;
    $eventObj = new Event();
    $results = $eventObj->getEvents($conditions,$params);

    return $results;
}


function getEventsCount($city){
    $condition = null;
    if($city != null){
        $condition = ' plSafeChars(events.city) = :city AND ';
    }
    $condition .= '  events.status = :status ';
    $params = array(':status'=>1);
    if($city!=null){
        $params += [':city'=>$city];
    }
    $eventObj = new Event();
    $count = $eventObj->getEventsCount($condition,$params);
    return $count;
}

function getEventsCountTag($tag){
    $condition = null;
    if($tag != null){
        $condition = ' ((plSafeChars(events.tags) LIKE "%'.$tag.'%") OR (events.tags LIKE "%'.$tag.'%")) AND ';
    }
    $condition .= '  events.status = :status  ';

    $params = array(':status'=>1);
    $eventObj = new Event();
    $count = $eventObj->getEventsCount($condition,$params);
    return $count;
}


function getEventsCountWordAndTag($keyword){
    $condition = null;
    if($keyword != null){
        $condition = ' ((plSafeChars(events.tags) LIKE "%'.$keyword.'%") OR (events.tags LIKE "%'.$keyword.'%") OR (plSafeChars(events.subject) LIKE "%'.$keyword.'%") OR (events.subject LIKE "%'.$keyword.'%")) AND ';
    }
    $condition .= '  events.status = :status  ';
    $params = array(':status'=>1);
    $eventObj = new Event();
    $count = $eventObj->getEventsCount($condition,$params);
    return $count;
}

function getEventsCountUser($userId){
    $condition = null;
    $condition .= '  events.status = :status AND events.user_type = "user" AND events.user_id = :userId  ';

    $params = array(':status'=>1,':userId'=>$userId);
    $eventObj = new Event();
    $count = $eventObj->getEventsCount($condition,$params);
    return $count;
}


function getEventsCountCompany($userId){
    $condition = null;
   $condition .= '  events.status = :status AND events.user_type = "company" AND events.user_id = :userId  ';
    $params = array(':status'=>1,':userId'=>$userId);
    $eventObj = new Event();
    $count = $eventObj->getEventsCount($condition,$params);
    return $count;
}


function getEventsCategoryCount($city, $category){
    $params = array(':status'=>1);
    if($city !=null){
        $condition = ' plSafeChars(events.city) = :city AND ';
        $params += [':city'=>$city];
    }
    $condition .= '  events.status = :status ';


    if($category!=null && isCategoryExist($category)){
        $condition .= ' AND plSafeChars(categories.name) = :categoryName';
        $params += [':categoryName'=>$category];
    }

    $eventObj = new Event();
    $count = $eventObj->getEventsCount($condition,$params);
    return $count;
}


function getUserEventsCount($userId,$status,$page,$limit){
    $userCompanies = getUserCompanies($userId,1);
    $conditions = '((events.user_type = "user" AND events.user_id = :userId AND users.id = :userId ) ';
    $params = array(':status'=>$status,':userId'=>$userId);
    $i = 1;
    foreach($userCompanies as $uc){
        $conditions.=' OR (events.user_type = "company" AND events.user_id = :companyId'.$i.')';
        $params += [':companyId'.$i=>$uc['companyId']];
        $i++;
    }
    $conditions .= ' ) AND events.status = :status ';
    $conditions .= ' ORDER BY events.id DESC  ';
    $eventObj = new Event();
    $results = $eventObj->getEvents($conditions,$params);
    return (count($results) > 0 ? count($results) : 0);
}


function searchUserEventsCount($userId,$status,$page,$limit,$query){
    $userCompanies = getUserCompanies($userId,1);
    $conditions = '(events.subject LIKE "%'.$query.'%" OR events.tags LIKE "%'.$query.'%") AND ((events.user_type = "user" AND events.user_id = :userId AND users.id = :userId ) ';
    $params = array(':status'=>$status,':userId'=>$userId);
    $i = 1;
    foreach($userCompanies as $uc){
        $conditions.=' OR (events.user_type = "company" AND events.user_id = :companyId'.$i.')';
        $params += [':companyId'.$i=>$uc['companyId']];
        $i++;
    }
    $conditions .= ' ) AND events.status = :status ';
    $conditions .= ' ORDER BY events.id DESC  ';
    $eventObj = new Event();
    $results = $eventObj->getEvents($conditions,$params);
    return (count($results) > 0 ? count($results) : 1);
}




function showEventUserPagination($userId,$status,$limit){
    if(isset($_GET['category'])){
        $category = htmlspecialchars($_GET['category']);
    }
    if(isset($_GET['m'])){
        $m = htmlspecialchars($_GET['m']);
    }

    $link='/moje-konto/wydarzenia'.(!empty($category)?'/'.$category:'').(!empty($m)?'/'.$m:'');


    $currentPage = (isset($_GET['p'])?(int)(htmlspecialchars($_GET['p'])):1);
    $pagesCount = ceil((getUserEventsCount($userId,$status,$currentPage,$limit)>0?getUserEventsCount($userId,$status,$currentPage,$limit):1 )/ $limit);
$pagesCount = ($pagesCount>0?$pagesCount:1);
    $allPages = $pagesCount;
    $pageLoop = 1;

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
                        <'.($currentPage === (int)($i) ? 'span':'a').' class="pagination__link '.($currentPage === (int)($i) ? 'active':'').'" '.($currentPage === (int)($i) ? '':'href="'.$link.'/'.$i.'/"').'  title="Przejdź na stronę">'.$i.'</'.($currentPage === (int)($i) ? 'span':'a').'>
                    </li>
                    ';
        }
    }else{
        if($currentPage <= 2 && $currentPage>0){
            while($pagesCount > 0 && $pageLoop<=5){
                $pagination.='
                    <li class="pagination__item">
                        <'.($currentPage === $pageLoop ? 'span':'a').' class="pagination__link '.($currentPage === $pageLoop ? 'active':'').'" '.($currentPage === $pageLoop ? '':'href="'.$link.'/'.$pageLoop.'/"').'  title="Przejdź na stronę">'.$pageLoop.'</'.($currentPage === $pageLoop ? 'span':'a').'>
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
                        <'.($currentPage === (int)$endPag ? 'span':'a').' class="pagination__link '.($currentPage === (int)$endPag ? 'active':'').'" '.($currentPage === (int)$endPag ? '':'href="'.$link.'/'.$endPag.'/"').'  title="Przejdź na stronę">'.$endPag.'</'.($currentPage === (int)$endPag ? 'span':'a').'>
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

function showSearchEventUserPagination($userId,$status,$limit,$query){
    if(isset($_GET['category'])){
        $category = htmlspecialchars($_GET['category']);
    }
    if(isset($_GET['m'])){
        $m = htmlspecialchars($_GET['m']);
    }

    $link='/moje-konto/wydarzenia'.(!empty($category)?'/'.$category:'').(!empty($m)?'/'.$m:'');


    $currentPage = (isset($_GET['p'])?(int)htmlspecialchars($_GET['p']):1);
    $pagesCount = ceil(searchUserEventsCount($userId,$status,$currentPage,$limit,$query) / $limit);
$pagesCount = ($pagesCount>0?$pagesCount:1);
    $allPages = $pagesCount;
    $pageLoop = 1;

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
                        <'.($currentPage === (int)$i ? 'span':'a').' class="pagination__link '.($currentPage === (int)$i ? 'active':'').'" '.($currentPage === (int)$i ? '':'href="'.$link.'/'.$i.'/"').'  title="Przejdź na stronę">'.$i.'</'.($currentPage === (int)$i ? 'span':'a').'>
                    </li>
                    ';
        }
    }else{
        if($currentPage <= 2 && $currentPage>0){
            while($pagesCount > 0 && $pageLoop<=5){
                $pagination.='
                    <li class="pagination__item">
                        <'.($currentPage === $pageLoop ? 'span':'a').' class="pagination__link '.($currentPage === $pageLoop ? 'active':'').'"  '.($currentPage === $pageLoop ? '':'href="'.$link.'/'.$pageLoop.'/"').'  title="Przejdź na stronę">'.$pageLoop.'</'.($currentPage === $pageLoop ? 'span':'a').'>
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
                        <'.($currentPage === (int)$endPag ? 'span':'a').' class="pagination__link '.($currentPage === (int)$endPag ? 'active':'').'" '.($currentPage === (int)$endPag ? '':'href="'.$link.'/'.$endPag.'/"').'  title="Przejdź na stronę">'.$endPag.'</'.($currentPage === (int)$endPag ? 'span':'a').'>
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


function showEventCategoryPagination($city,$category,$page,$limit){
    if(isset($_GET['category'])){
        $category = htmlspecialchars($_GET['category']);
    }
    if(isset($_GET['m'])){
        $m = htmlspecialchars($_GET['m']);
    }

    $link='/wydarzenia'.(!empty($category)?'/'.$category:'').(!empty($m)?'/'.$m:'');


    $pagesLimit = 5;
    $pagesCount = ceil(getEventsCategoryCount($city,$category) / $limit);
    if(empty($pagesCount)){
        $pagesCount =1;
    }
    $allPages = $pagesCount;
    $pageLoop = 1;
    $currentPage = (!isset($_GET['p'])?1: intval($_GET['p']));
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
                        <'.($currentPage === (int)$i ? 'span':'a').' class="pagination__link '.($currentPage === (int)$i ? 'active':'').'" '.($currentPage === (int)$i ? '':'href="'.$link.'/'.$i.'/"').'  title="Przejdź na stronę">'.$i.'</'.($currentPage === (int)$i ? 'span':'a').'>
                    </li>
                    ';
        }
    }else{
        if($currentPage <= 2 && $currentPage>0){
            while($pagesCount > 0 && $pageLoop<=5){
                $pagination.='
                    <li class="pagination__item">
                        <'.($currentPage === $pageLoop ? 'span':'a').' class="pagination__link '.($currentPage === $pageLoop ? 'active':'').'" '.($currentPage === $pageLoop ? '':'href="'.$link.'/'.$pageLoop.'/"').'  title="Przejdź na stronę">'.$pageLoop.'</'.($currentPage === $pageLoop ? 'span':'a').'>
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
                        <'.($currentPage === (int)$endPag ? 'span':'a').' class="pagination__link '.($currentPage === (int)$endPag ? 'active':'').'" '.($currentPage === (int)$endPag ? '':'href="'.$link.'/'.$endPag.'/"').'  title="Przejdź na stronę">'.$endPag.'</'.($currentPage === (int)$endPag ? 'span':'a').'>
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


function showEventPagination($city,$page,$limit){
    if(isset($_GET['category'])){
        $category = htmlspecialchars($_GET['category']);
    }
    if(isset($_GET['m'])){
        $m = htmlspecialchars($_GET['m']);
    }

    $link='/wydarzenia'.(!empty($category)?'/'.$category:'').(!empty($m)?'/'.$m:'');

    $pagesCount = ceil(getEventsCount($city) / $limit);
$pagesCount = ($pagesCount>0?$pagesCount:1);

    $allPages = $pagesCount;
    $pageLoop = 1;
    $currentPage = (!isset($_GET['p'])?1: (int)$_GET['p']);
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
                        <'.($currentPage === (int)$i ? 'span':'a').' class="pagination__link '.($currentPage === (int)$i ? 'active':'').'" '.($currentPage === (int)$i ? '':'href="'.$link.'/'.$i.'/"').'  title="Przejdź na stronę">'.$i.'</'.($currentPage === (int)$i ? 'span':'a').'>
                    </li>
                    ';
        }
    }else{
        if($currentPage <= 2 && $currentPage>0){
            while($pagesCount > 0 && $pageLoop<=5){
                $pagination.='
                    <li class="pagination__item">
                        <'.($currentPage === $pageLoop ? 'span':'a').' class="pagination__link '.($currentPage === $pageLoop ? 'active':'').'" '.($currentPage === $pageLoop ? '':'href="'.$link.'/'.$pageLoop.'/"').'  title="Przejdź na stronę">'.$pageLoop.'</'.($currentPage === $pageLoop ? 'span':'a').'>
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
                        <'.($currentPage === (int)$endPag ? 'span':'a').' class="pagination__link '.($currentPage === (int)$endPag ? 'active':'').'" '.($currentPage === (int)$endPag ? '':'href="'.$link.'/'.$endPag.'/"').'  title="Przejdź na stronę">'.$endPag.'</'.($currentPage === (int)$endPag ? 'span':'a').'>
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



function showEventPaginationTag($tag,$page,$limit){
    if(isset($_GET['tag'])){
        $tag = htmlspecialchars($_GET['tag']);
    }

    $link='/tag/wydarzenia'.(!empty($tag)?'/'.$tag:'');

    $pagesCount = ceil(getEventsCountTag($tag) / $limit);
    $pagesCount = ($pagesCount>0?$pagesCount:1);
    $allPages = $pagesCount;
    $pageLoop = 1;
    $currentPage = (isset($_GET['p'])?(int)htmlspecialchars($_GET['p']):1);
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
                        <'.($currentPage === (int)$i ? 'span':'a').' class="pagination__link '.($currentPage === (int)$i ? 'active':'').'" '.($currentPage === (int)$i ? '':'href="'.$link.'/'.$i.'/"').'  title="Przejdź na stronę">'.$i.'</'.($currentPage === (int)$i ? 'span':'a').'>
                    </li>
                    ';
        }
    }else{
        if($currentPage <= 2 && $currentPage>0){
            while($pagesCount > 0 && $pageLoop<=5){
                $pagination.='
                    <li class="pagination__item">
                        <'.($currentPage === $pageLoop ? 'span':'a').' class="pagination__link '.($currentPage === $pageLoop ? 'active':'').'" '.($currentPage === $pageLoop ? '':'href="'.$link.'/'.$pageLoop.'/"').'  title="Przejdź na stronę">'.$pageLoop.'</'.($currentPage === $pageLoop ? 'span':'a').'>
                    </li>
                    ';
                $pageLoop++;
                $pagesCount--;
            }
        }elseif($currentPage>0 && $currentPage<=$pagesCount){
            $endPag = $pagesCount-4;
            while($endPag<=$pagesCount){
                if ($endPag>0){
                    $pagination.='
                    <li class="pagination__item">
                        <'.($currentPage === (int)$endPag ? 'span':'a').' class="pagination__link '.($currentPage === (int)$endPag ? 'active':'').'" '.($currentPage === (int)$endPag ? '':'href="'.$link.'/'.$endPag.'/"').'  title="Przejdź na stronę">'.(int)$endPag.'</'.($currentPage === (int)$endPag ? 'span':'a').'>
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


function showEventPaginationWordAndTag($keyword,$page,$limit){


    $link='/szukaj/wydarzenia'.(!empty($keyword)?'/'.$keyword:'');
    $pagesCount = ceil(getEventsCountWordAndTag($keyword) / $limit);
$pagesCount = ($pagesCount>0?$pagesCount:1);
    $allPages = $pagesCount;
    $pageLoop = 1;
    $currentPage = (!isset($_GET['p'])?1:(int)$_GET['p']);
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
                        <'.($currentPage === (int)$i ? 'span':'a').' class="pagination__link '.($currentPage === (int)$i ? 'active':'').'" '.($currentPage === (int)$i ? '':'href="'.$link.'/'.$i.'/"').'  title="Przejdź na stronę">'.$i.'</'.($currentPage === (int)$i ? 'span':'a').'>
                    </li>
                    ';
        }
    }else{
        if($currentPage <= 2 && $currentPage>0){
            while($pagesCount > 0 && $pageLoop<=5){
                $pagination.='
                    <li class="pagination__item">
                        <'.($currentPage === $pageLoop ? 'span':'a').' class="pagination__link '.($currentPage === $pageLoop ? 'active':'').'"  '.($currentPage === $pageLoop ? '':'href="'.$link.'/'.$pageLoop.'/"').'  title="Przejdź na stronę">'.$pageLoop.'</'.($currentPage === $pageLoop ? 'span':'a').'>
                    </li>
                    ';
                $pageLoop++;
                $pagesCount--;
            }
        }elseif($currentPage>0 && $currentPage<=$pagesCount){
            $endPag = $pagesCount-4;
            while($endPag<=$pagesCount){
                if ($endPag>0){
                    $pagination.='
                    <li class="pagination__item">
                        <'.($currentPage === (int)$endPag ? 'span':'a').' class="pagination__link '.($currentPage === (int)$endPag ? 'active':'').'" '.($currentPage === (int)$endPag ? '':'href="'.$link.'/'.$endPag.'/"').'  title="Przejdź na stronę">'.$endPag.'</'.($currentPage === (int)$endPag ? 'span':'a').'>
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


function showEventPaginationUser($userId,$userLogin,$page,$limit){

    $link='/profil/'.$userLogin.','.$userId.'/wydarzenia';

    $pagesLimit = 5;
    $pagesCount = ceil(getEventsCountUser($userId) / $limit);
$pagesCount = ($pagesCount>0?$pagesCount:1);
    $allPages = $pagesCount;
    $pageLoop = 1;
    $currentPage = (!isset($_GET['p'])?1: (int)$_GET['p']);
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
                        <'.($currentPage === (int)$i ? 'span':'a').' class="pagination__link '.($currentPage === (int)$i ? 'active':'').'" '.($currentPage === (int)$i ? '':'href="'.$link.'/'.$i.'/"').'  title="Przejdź na stronę">'.$i.'</'.($currentPage === (int)$i ? 'span':'a').'>
                    </li>
                    ';
        }
    }else{
        if($currentPage <= 2 && $currentPage>0){
            while($pagesCount > 0 && $pageLoop<=5){
                $pagination.='
                    <li class="pagination__item">
                        <'.($currentPage === $pageLoop ? 'span':'a').' class="pagination__link '.($currentPage === $pageLoop ? 'active':'').'" '.($currentPage === $pageLoop ? '':'href="'.$link.'/'.$pageLoop.'/"').'  title="Przejdź na stronę">'.$pageLoop.'</'.($currentPage === $pageLoop ? 'span':'a').'>
                    </li>
                    ';
                $pageLoop++;
                $pagesCount--;
            }
        }elseif($currentPage>0 && $currentPage<=$pagesCount){
            $endPag = $pagesCount-4;
            while($endPag<=$pagesCount){
                if ($endPag>0){
                    $pagination.='
                    <li class="pagination__item">
                        <'.($currentPage === (int)$endPag ? 'span':'a').' class="pagination__link '.($currentPage === (int)$endPag ? 'active':'').'" '.($currentPage === (int)$endPag ? '':'href="'.$link.'/'.$endPag.'/"').'  title="Przejdź na stronę">'.$endPag.'</'.($currentPage === (int)$endPag ? 'span':'a').'>
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


function showEventPaginationCompany($companyId,$companyName,$page,$limit){

    $link='/firmy/'.$companyName.','.$companyId.'/wydarzenia';

    $pagesCount = ceil(getEventsCountCompany($companyId) / $limit);
$pagesCount = ($pagesCount>0?$pagesCount:1);
    $allPages = $pagesCount;
    $pageLoop = 1;
    $currentPage = (!isset($_GET['p'])?1: (int)$_GET['p']);
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
                        <'.($currentPage === (int)$i ? 'span':'a').' class="pagination__link '.($currentPage === (int)$i ? 'active':'').'" '.($currentPage === (int)$i ? '':'href="'.$link.'/'.$i.'/"').' title="Przejdź na stronę">'.$i.'</'.($currentPage === (int)$i ? 'span':'a').'>
                    </li>
                    ';
        }
    }else{
        if($currentPage <= 2 && $currentPage>0){
            while($pagesCount > 0 && $pageLoop<=5){
                $pagination.='
                    <li class="pagination__item">
                        <'.($currentPage === $pageLoop ? 'span':'a').' class="pagination__link '.($currentPage === $pageLoop ? 'active':'').'" '.($currentPage === $pageLoop ? '':'href="'.$link.'/'.$pageLoop.'/"').'  title="Przejdź na stronę">'.$pageLoop.'</'.($currentPage === $pageLoop ? 'span':'a').'>
                    </li>
                    ';
                $pageLoop++;
                $pagesCount--;
            }
        }elseif($currentPage>0 && $currentPage<=$pagesCount){
            $endPag = $pagesCount-4;
            while($endPag<=$pagesCount){
                if ($endPag>0){
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



function addEventView($eventId){
    $eventObj = new Event();
    $result = $eventObj->addEventView($eventId);
    return $result;
}