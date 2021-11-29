<?php

function addTheard($subject,$content,$tags,$category_id,$user_id,$user_type,$status,$verificated){

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
    $articleObj = new Forum();
    $result = $articleObj->addTheard(trim($subject),trim($content),trim($tagList),$category_id,$user_id,$user_type,$status,$verificated);
    if($result > 0){
        return $result;
    }else{
        return null;
    }
}

function addReply($theard_id,$content,$user_id,$user_type){

    if($user_type === 'company'){
        $userIdSafe = getCompanyOwnerId($user_id);
    }else{
        $userIdSafe = $user_id;
    }

    if(!canAddNewContent($userIdSafe)) {
        return false;
    }
    $articleObj = new Forum();
    $result = $articleObj->addReply((int)$theard_id,trim($content),$user_id,$user_type);
    if($result > 0){
        return $result;
    }else{
        return null;
    }
}

function getTheard($id,$title){
    $conditions = ' AND forum_theards.id = :id ';
    $params = array(':id'=>$id);
    if($title != null){
        $conditions.=' AND plSafeChars(forum_theards.subject) = :title ';
        $params += [':title'=>$title];
    }
    $forumObj = new Forum();
    $results = $forumObj->getTheards($conditions,$params);

    return $results;
}

function getTheards($limit,$category){
    $conditions = null;
    $conditions .= ' AND (forum_theards.status = 1 OR forum_theards.status = 2) ';
    if($category!=null){
        $conditions .= ' AND plSafeChars(categories.name) = :category ';
    }
    $conditions .= ' ORDER BY forum_theards.created_date DESC  ';
    if($limit != null){
        $conditions .= '  LIMIT '.$limit;
    }
    $params = array();
    if($category != null){
        $params += [':category'=>$category];
    }
    $forumObj = new Forum();
    $results = $forumObj->getTheards($conditions,$params);
    $results = sortArrayByPromoted($results);

    return $results;
}


function getTheards_Tag($limit,$tag){
    $conditions = null;
    $conditions .= ' AND ( (plSafeChars(forum_theards.tags) LIKE "%'.$tag.'%") OR (forum_theards.tags LIKE "%'.$tag.'%") ) ';

    $conditions .= ' ORDER BY forum_theards.created_date DESC  ';
    if($limit != null){
        $conditions .= '  LIMIT '.$limit;
    }
    $params = null;
    $forumObj = new Forum();
    $results = $forumObj->getTheards($conditions,$params);
    $results = sortArrayByPromoted($results);

    return $results;
}



function getTheards_WordAndTag($limit,$keyword){
    $conditions = null;
    $conditions .= ' AND ((plSafeChars(forum_theards.subject) LIKE "%'.$keyword.'%") OR (forum_theards.subject LIKE "%'.$keyword.'%")  OR (plSafeChars(forum_theards.tags) LIKE "%'.$keyword.'%") OR (forum_theards.tags LIKE "%'.$keyword.'%") ) ';

    $conditions .= ' ORDER BY forum_theards.created_date DESC  ';
    if($limit != null){
        $conditions .= '  LIMIT '.$limit;
    }
    $params = null;
    $forumObj = new Forum();
    $results = $forumObj->getTheards($conditions,$params);
    $results = sortArrayByPromoted($results);

    return $results;
}

function searchUserTheards($userId,$status,$page,$limit,$sort,$query){
    $userCompanies = getUserCompanies($userId,1);
    $conditions = '  AND (forum_theards.subject LIKE "%'.$query.'%" OR forum_theards.tags LIKE "%'.$query.'%") AND (  (forum_theards.user_type = "user" AND forum_theards.user_id = :userId )  ';
    $params = array(':status'=>$status,':userId'=>$userId);
    $i = 1;
    foreach($userCompanies as $uc){
        $conditions.=' OR (forum_theards.user_type = "company" AND forum_theards.user_id = :companyId'.$i.'  )';
        $params += [':companyId'.$i=>$uc['companyId']];
        $i++;
    }
    $conditions .= '   ) AND forum_theards.status = :status';
    $from = ($page * $limit) - $limit;
    $range = $from.','.$limit;
    $conditions .= ' ORDER BY '.$sort.' LIMIT '.$range;
    $forumObj = new Forum();
    $results = $forumObj->getTheards($conditions,$params);
    return $results;
}


function getUserTheards($userId,$status,$page,$limit,$sort){
    $userCompanies = getUserCompanies($userId,1);
    $conditions = '  AND (  (forum_theards.user_type = "user" AND forum_theards.user_id = :userId )  ';
    $params = array(':status'=>$status,':userId'=>$userId);
    $i = 1;
    foreach($userCompanies as $uc){
        $conditions.=' OR (forum_theards.user_type = "company" AND forum_theards.user_id = :companyId'.$i.'  )';
        $params += [':companyId'.$i=>$uc['companyId']];
        $i++;
    }
    $conditions .= '   ) AND forum_theards.status = :status';
    $from = ($page * $limit) - $limit;
    $range = $from.','.$limit;
    $conditions .= ' ORDER BY '.$sort.' LIMIT '.$range;
    $forumObj = new Forum();
    $results = $forumObj->getTheards($conditions,$params);
    return $results;
}

function getUserTheardsCount($userId,$status){
    $userCompanies = getUserCompanies($userId,1);
    $conditions = '  AND (  (forum_theards.user_type = "user" AND forum_theards.user_id = :userId )  ';
    $params = array(':status'=>$status,':userId'=>$userId);
    $i = 1;
    foreach($userCompanies as $uc){
        $conditions.=' OR (forum_theards.user_type = "company" AND forum_theards.user_id = :companyId'.$i.'  )';
        $params += [':companyId'.$i=>$uc['companyId']];
        $i++;
    }
    $conditions .= '   ) AND forum_theards.status = :status';
    $conditions .= ' ORDER BY forum_theards.id';
    $forumObj = new Forum();
    $results = $forumObj->getTheards($conditions,$params);
    return (count($results) > 0 ? count($results) : 0);
}



function searchtUserTheardsCount($userId,$status,$query){
    $userCompanies = getUserCompanies($userId,1);
    $conditions = '  AND (forum_theards.subject LIKE "%'.$query.'%" OR forum_theards.tags LIKE "%'.$query.'%") AND (  (forum_theards.user_type = "user" AND forum_theards.user_id = :userId )  ';
    $params = array(':status'=>$status,':userId'=>$userId);
    $i = 1;
    foreach($userCompanies as $uc){
        $conditions.=' OR (forum_theards.user_type = "company" AND forum_theards.user_id = :companyId'.$i.'  )';
        $params += [':companyId'.$i=>$uc['companyId']];
        $i++;
    }
    $conditions .= '   ) AND forum_theards.status = :status';
    $conditions .= ' ORDER BY forum_theards.id';
    $forumObj = new Forum();
    $results = $forumObj->getTheards($conditions,$params);
    return (count($results) > 0 ? count($results) : 0);
}

function showSearchTheardsUserPagination($userId,$status,$limit,$kindSafe,$type,$sort,$query){

    $currentPage = (isset($_POST['page'])?(int)htmlspecialchars($_POST['page']):1);
    $pagesCount = ceil((searchtUserTheardsCount($userId,$status,$query)>0?searchtUserTheardsCount($userId,$status,$query):1) / $limit);
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
                        <button class="pagination__link" value="1" name="page" type="submit" title="Przejdź na stronę">
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
                            <'.($currentPage === (int)$i ? 'span':'button').' class="pagination__link '.($currentPage === (int)$i ? 'active':'').'" value="'.$i.'" name="page" '.($currentPage === (int)$i ? '':'type="submit"').' title="Przejdź na stronę">'.$i.'</'.($currentPage === (int)$i ? 'span':'button').'>
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
                if($endPag > 0) {
                    $pagination .= '
                    <li class="pagination__item">
                        <' . ($currentPage === (int)$endPag ? 'span' : 'button') . ' class="pagination__link ' . ($currentPage === (int)$endPag ? 'active' : '') . '" value="' . $endPag . '" name="page" ' . ($currentPage === (int)$endPag ? '' : 'type="submit"') . '  title="Przejdź na stronę">' . $endPag . '</' . ($currentPage === (int)$endPag ? 'span' : 'button') . '>
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


function showTheardsUserPagination($userId,$status,$limit,$kindSafe,$type,$sort){


    $currentPage = (isset($_POST['page'])?(int)htmlspecialchars($_POST['page']):1);
    $pagesCount = ceil((getUserTheardsCount($userId,$status)>0?getUserTheardsCount($userId,$status):1) / $limit);
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
                        <button class="pagination__link" value="1" name="page" type="submit" title="Przejdź na stronę">
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
                            <'.($currentPage === (int)$i ? 'span':'button').' class="pagination__link '.($currentPage === (int)$i ? 'active':'').'" value="'.$i.'" name="page" '.($currentPage === (int)$i ? '':'type="submit"').' title="Przejdź na stronę">'.$i.'</'.($currentPage === (int)$i ? 'span':'button').'>
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
                if($endPag > 0) {
                    $pagination .= '
                    <li class="pagination__item">
                        <' . ($currentPage === (int)$endPag ? 'span' : 'button') . ' class="pagination__link ' . ($currentPage === (int)$endPag ? 'active' : '') . '" value="' . $endPag . '" name="page" ' . ($currentPage === (int)$endPag ? '' : 'type="submit"') . '  title="Przejdź na stronę">' . $endPag . '</' . ($currentPage === (int)$endPag ? 'span' : 'button') . '>
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



function getAllTheards($page,$limit){
    $from = ($page * $limit) - $limit;
    $range = $from.','.$limit;
    $condtions = ' AND  (forum_theards.status = 1 OR forum_theards.status = 2) ORDER BY forum_theards.created_date DESC  ';
    $condtions .= '  LIMIT '.$range;
    $params = null;
    $forumObj = new Forum();
    $results = $forumObj->getTheards($condtions,$params);
    $results = sortArrayByPromoted($results);

    return $results;
}


function getTheards_List($page,$limit){
    $range = ($page * $limit) - $limit.','.$limit;
    $condtions = ' AND  (forum_theards.status <> 0) ORDER BY forum_theards.pinned DESC,forum_theards.created_date DESC  ';
    $condtions .= '  LIMIT '.$range;
    $params = null;
    $forumObj = new Forum();
    $results = $forumObj->getTheards_List($condtions,$params);
    $results = sortArrayByPromoted($results);

    return $results;
}

function getAllTheardsFromCategory_List($categoryName,$page,$limit){
    $from = ($page * $limit) - $limit;
    $range = $from.','.$limit;
    $conditions = ' AND plSafeChars(categories.name) = :categoryName  AND  (forum_theards.status <> 0)';
    $conditions .= ' ORDER BY forum_theards.created_date DESC  ';
    $conditions .= '  LIMIT '.$range;

    $params = array(':categoryName'=>$categoryName);
    $forumObj = new Forum();
    $results = $forumObj->getTheards_List($conditions,$params);
    $results = sortArrayByPromoted($results);

    return $results;
}



function getAllTheardsFromCategory($categoryName,$page,$limit){
    $from = ($page * $limit) - $limit;
    $range = $from.','.$limit;
    $conditions = ' AND plSafeChars(categories.name) = :categoryName  AND  (forum_theards.status = 1 OR forum_theards.status = 2)';
    $conditions .= ' ORDER BY forum_theards.created_date DESC  ';
    $conditions .= '  LIMIT '.$range;
    $params = array(':categoryName'=>$categoryName);
    $forumObj = new Forum();
    $results = $forumObj->getTheards($conditions,$params);
    $results = sortArrayByPromoted($results);

    return $results;
}

function getAllTheardsFromUser($userId,$page,$limit){
    $conditions = ' AND forum_theards.user_type = "user" AND forum_theards.user_id = :userId ';
    $conditions .= ' ORDER BY forum_theards.created_date DESC  ';
    if($limit != null){
        $from = ($page * $limit) - $limit;
        $range = $from.','.$limit;
        $conditions .= '  LIMIT '.$range;
    }
    $params = array(':userId'=>$userId);
    $forumObj = new Forum();
    $results = $forumObj->getTheards($conditions,$params);
    $results = sortArrayByPromoted($results);

    return $results;
}


function getAllTheardsFromCompany($userId,$page,$limit){
    $conditions = ' AND forum_theards.user_type = "company" AND forum_theards.user_id = :userId ';
    $conditions .= ' ORDER BY forum_theards.created_date DESC  ';
    if($limit != null){
        $from = ($page * $limit) - $limit;
        $range = $from.','.$limit;
        $conditions .= '  LIMIT '.$range;
    }
    $params = array(':userId'=>$userId);
    $forumObj = new Forum();
    $results = $forumObj->getTheards($conditions,$params);
    $results = sortArrayByPromoted($results);

    return $results;
}

function getAllTheardsFromTag($tag,$page,$limit){
    $from = ($page * $limit) - $limit;
    $range = $from.','.$limit;
    $params = null;
    $conditions = ' AND ((plSafeChars(forum_theards.tags) LIKE "%'.$tag.'%") OR (forum_theards.tags LIKE "%'.$tag.'%")) ';
    $conditions .= ' ORDER BY forum_theards.created_date DESC  ';
    $conditions .= '  LIMIT '.$range;
    $forumObj = new Forum();
    $results = $forumObj->getTheards($conditions,$params);
    $results = sortArrayByPromoted($results);

    return $results;
}

function getAllTheardsFromWordAndTag($tag,$page,$limit){
    $from = ($page * $limit) - $limit;
    $range = $from.','.$limit;
    $params = null;
    $conditions = ' AND ((plSafeChars(forum_theards.tags) LIKE "%'.$tag.'%") OR (forum_theards.tags LIKE "%'.$tag.'%") OR (plSafeChars(forum_theards.subject) LIKE "%'.$tag.'%") OR (forum_theards.subject LIKE "%'.$tag.'%")) ';
    $conditions .= ' ORDER BY forum_theards.created_date DESC  ';
    $conditions .= '  LIMIT '.$range;
    $forumObj = new Forum();
    $results = $forumObj->getTheards($conditions,$params);
    $results = sortArrayByPromoted($results);

    return $results;
}

function getTheardsCount(){
    $companyObj = new Forum();
    $conditions = ' AND  (forum_theards.status = 1 OR forum_theards.status = 2)';
    $count = $companyObj->getTheardsCount($conditions);
    return $count;
}


function getTheardsCountFromCategory($categoryName){
    $companyObj = new Forum();
    $conditions = '  AND  (forum_theards.status = 1 OR forum_theards.status = 2)';
    if($categoryName!=NULL){
        $conditions.=' AND plSafeChars(categories.name) = :categoryName';
        $params = array(':categoryName'=>$categoryName);

    }
    $count = $companyObj->getTheardsCount($conditions,$params);
    return $count;
}

function getTheardsCountFromUser($userId){
    $companyObj = new Forum();
    $conditions = ' AND forum_theards.user_type = "user" AND forum_theards.user_id = :userId ';
    $params = array(':userId'=>$userId);
    $count = $companyObj->getTheardsCount($conditions,$params);
    return $count;
}


function getTheardsCountFromCompany($userId){
    $companyObj = new Forum();
    $conditions = ' AND forum_theards.user_type = "company" AND forum_theards.user_id = :userId ';
    $params = array(':userId'=>$userId);
    $count = $companyObj->getTheardsCount($conditions,$params);
    return $count;
}


function getTheardsCountFromTag($tag){
    $companyObj = new Forum();
    $conditions = ' AND ((plSafeChars(forum_theards.tags) LIKE "%'.$tag.'%") OR (forum_theards.tags LIKE "%'.$tag.'%")) ';
    $params = null;
    $count = $companyObj->getTheardsCount($conditions,$params);
    return $count;
}



function getTheardsCountFromWordAndTag($tag){
    $companyObj = new Forum();
    $conditions = ' AND ((plSafeChars(forum_theards.tags) LIKE "%'.$tag.'%") OR (forum_theards.tags LIKE "%'.$tag.'%") OR (plSafeChars(forum_theards.subject) LIKE "%'.$tag.'%") OR (forum_theards.subject LIKE "%'.$tag.'%")) ';
    $params = null;
    $count = $companyObj->getTheardsCount($conditions,$params);
    return $count;
}


function showTheardsPagination($page,$limit){
    if(isset($_GET['category'])){
        $category = htmlspecialchars($_GET['category']);
    }


    $link='/forum'.(!empty($category)?'/'.$category:'');
    
    $pagesCount = ceil(getTheardsCount() / $limit);
$pagesCount = ($pagesCount>0?$pagesCount:1);
    $allPages = $pagesCount;
    $pageLoop = 1;
    $currentPage = (isset($_GET['p'])?(int)htmlspecialchars($_GET['p']):1);
    $pagination= '
    
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

function showTheardsPaginationFromCategory($categoryName,$page,$limit){
    if(isset($_GET['category'])){
        $category = htmlspecialchars($_GET['category']);
    }


    $link='/forum'.(!empty($category)?'/'.$category:'');

    $pagesLimit = 5;
    $pagesCount = ceil(getTheardsCountFromCategory($categoryName) / $limit);
$pagesCount = ($pagesCount>0?$pagesCount:1);
    $allPages = $pagesCount;
    $pageLoop = 1;
    $currentPage = (isset($_GET['p'])?(int)htmlspecialchars($_GET['p']):1);
    $pagination= '
    
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


function showTheardsPaginationFromUser($userId,$userLogin,$page,$limit){

    $link='/profil/'.$userLogin.','.$userId.'/posty';

    $pagesCount = ceil(getTheardsCountFromUser($userId) / $limit);
$pagesCount = ($pagesCount>0?$pagesCount:1);
    $allPages = $pagesCount;
    $pageLoop = 1;
    $currentPage = (isset($_GET['p'])?(int)htmlspecialchars($_GET['p']):1);
    $pagination= '
    
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

function showTheardsPaginationFromCompany($companyId,$companyName,$page,$limit){

    $link='/firmy/'.$companyName.','.$companyId.'/posty';

    $pagesCount = ceil(getTheardsCountFromCompany($companyId) / $limit);
$pagesCount = ($pagesCount>0?$pagesCount:1);
    $allPages = $pagesCount;
    $pageLoop = 1;
    $currentPage = (isset($_GET['p'])?(int)htmlspecialchars($_GET['p']):1);
    $pagination= '
    
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

function showTheardsPaginationFromTag($tag,$page,$limit){
    if(isset($_GET['tag'])){
        $tag = htmlspecialchars($_GET['tag']);
    }


    $link='/tag/forum'.(!empty($tag)?'/'.$tag:'');

    $pagesCount = ceil(getTheardsCountFromTag($tag) / $limit);
$pagesCount = ($pagesCount>0?$pagesCount:1);
    $allPages = $pagesCount;
    $pageLoop = 1;
    $currentPage = (isset($_GET['p'])?(int)htmlspecialchars($_GET['p']):1);
    $pagination= '
    
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


function showTheardsPaginationFromWordAndTag($tag,$page,$limit){

    $link='/szukaj/forum'.(!empty($tag)?'/'.$tag:'');

    $pagesCount = ceil(getTheardsCountFromWordAndTag($tag) / $limit);
$pagesCount = ($pagesCount>0?$pagesCount:1);
    $allPages = $pagesCount;
    $pageLoop = 1;
    $currentPage = (isset($_GET['p'])?(int)htmlspecialchars($_GET['p']):1);
    $pagination= '
    
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

function getTheardsRepliesCount($theardId){
    $forumObj = new Forum();
    $result = $forumObj->getTheardsReplies_Count($theardId);
    return $result[0]['repliesCount'];
}


function getTheardReplies($theardId){
    $conditions = ' AND forum_replies.theard_id = :theardId AND (forum_replies.status = :statusON OR forum_replies.status = :statusOFF)';
    $params = array(':theardId'=>$theardId,':statusON'=>1, ':statusOFF'=>2);
    $forumObj = new Forum();
    $result = $forumObj->getTheardsReplies($conditions,$params);

    return $result;
}

function getReply($replyId){
    $conditions = ' AND forum_replies.id = :id';
    $params = array(':id'=>$replyId);
    $forumObj = new Forum();
    $result = $forumObj->getReply($conditions,$params);
    return $result;
}

function rateTheard($theardId, $rate, $userId){
    $forumObj = new Forum();
    $results = $forumObj->rateTheard($theardId,$rate,$userId);
    $results = sortArrayByPromoted($results);

    return $results;
}


function rateReply($replyId, $rate, $userId){
    $forumObj = new Forum();
    $results = $forumObj->rateReply($replyId,$rate,$userId);
    return $results;
}

function alreadyVoted($userId,$theardId,$rate){
    $forumObj = new Forum();
    $result = $forumObj->alreadyVoted($userId,$theardId,$rate);
    if(!empty($result)){
        return true;
    }else{
        return false;
    }
}

function alreadyReplyVoted($userId,$replyId,$rate){
    $forumObj = new Forum();
    $result = $forumObj->alreadyReplyVoted($userId,$replyId,$rate);
    if(!empty($result)){
        return true;
    }else{
        return false;
    }
}

function addTheardView($id){
    $forumObj = new Forum();
    $forumObj->addView($id);
}

function updateContent_Reply($replyId,$newContent){
    $forumObj = new Forum();
    return $forumObj->updateContent_Reply($replyId,$newContent);
}

function updateContent_Theard($theardId,$newContent){
    $forumObj = new Forum();
    return $forumObj->updateContent_Theard($theardId,$newContent);
}