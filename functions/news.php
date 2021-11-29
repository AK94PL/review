<?php

function setDisabled_News($articleId){
    $obj = new News();
    $set = 'news.status = :status';
    $params = array(':status'=>2);
    $result = $obj->updateNews($articleId,$set,$params);
    return $result;
}

function getAuthor_News($id,$title){
    $article = getNews($id,$title);
    $articleArray = ['author'=>$article[0]['user_id'],'author_type'=>$article[0]['user_type']];
    return $articleArray;
}

function addNews($subject,$content,$tags,$category_id,$image,$user_id,$user_type,$status,$verificated){
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
   $articleObj = new News();
   $result = $articleObj->addNews(trim($subject),trim($content),trim($tagList),$category_id,$image,$user_id,$user_type,$status,$verificated);
   if($result > 0){
       if($user_type === 'company'){
            $userIdBlockade = getCompanyOwnerId($user_id);
        }else{
           $userIdBlockade = $user_id;
        }
       updateBlockadeEnd($userIdBlockade,'user');
       return $result;
   }else{
       return null;
   }
}


function updateNews($subject,$content,$tags,$category_id,$image,$user_id,$user_type,$verificated,$revision,$articleId){
    $articleObj = new News();
    $set = ' news.subject = :subject, news.content = :content, news.tags = :tags,  news.category_id = :categoryId, news.user_id = :userId, news.user_type = :userType ';
    $params = array(':subject'=>$subject, ':content'=>$content, ':tags'=>$tags, ':categoryId'=>$category_id, ':userId'=>$user_id, ':userType'=>$user_type);

        $set .= ' , news.revision = :revision ';
        $params += [':revision'=>$revision];

    if(!empty($verificated)){
        $set .= ' , news.verificated = :verificated ';
        $params += [':verificated'=>$verificated];
    }

    if($image != null){
        $set .= ' , news.image = :image ';
        $params += [':image'=>$image];
    }


   $result = $articleObj->updateNews($articleId,$set,$params);
    return $result;
}

function getNews($id,$title,$status){
    $condtions = '  news.id = :id';
    $params = array(':id'=>$id);
    if($title != null){
        $condtions.=' AND plSafeChars(news.subject) = :title ';
        $params += [':title'=>$title];
    }

    if($status != null){
        $condtions .=' AND news.status = :status';
        $params += [':status'=>$status];
    }
    $articleObj = new News();
    $results = $articleObj->getNewses($condtions,$params);
    return $results;
}


function getNewses($limit,$category){
    $conditions = null;
    if($category!=null){
        $conditions .=' plSafeChars(categories.name) = :category AND ';
    }
    $conditions .= '  news.status = :status AND news.verificated = :verificated ';
    if($limit != null){
        $conditions .= ' ORDER BY news.created_date DESC LIMIT '.$limit;
    }
    $params = array(':status'=>1,':verificated'=>1);
    if($category!=null){
        $params += [':category'=>$category];
    }
    $articleObj = new News();
    $results = $articleObj->getNewses($conditions,$params);
    $results = sortArrayByPromoted($results);
    return $results;
}

function getNewses_Tag($limit,$tag){
    $conditions = null;

    $conditions .=' ( (plSafeChars(news.tags) LIKE "%'.$tag.'%") OR (news.tags LIKE "%'.$tag.'%")) AND ';
    $conditions .= '  news.status = :status ';
    if($limit != null){
        $conditions .= ' ORDER BY news.created_date DESC LIMIT '.$limit;
    }
    $params = array(':status'=>1);
    $articleObj = new News();
    $results = $articleObj->getNewses($conditions,$params);
    $results = sortArrayByPromoted($results);
    return $results;
}

function getNewses_WordAndTag($limit,$keyword){
    $conditions = null;
    $conditions .=' ( (plSafeChars(news.subject) LIKE "%'.$keyword.'%") OR (news.subject LIKE "%'.$keyword.'%") OR  (plSafeChars(news.content) LIKE "%'.$keyword.'%") OR (news.content LIKE "%'.$keyword.'%")) AND ';
    $conditions .= '  news.status = :status ';
    if($limit != null){
        $conditions .= ' ORDER BY news.created_date DESC LIMIT '.$limit;
    }
    $params = array(':status'=>1);
    $articleObj = new News();
    $results = $articleObj->getNewses($conditions,$params);
    $results = sortArrayByPromoted($results);
    return $results;
}


function getAllNewsesFromCategory($categoryName,$page,$limit){
    $from = ($page * $limit) - $limit;
    $condtions = '  news.status = :status AND plSafeChars(categories.name) = :categoryName';
    $range = $from.','.$limit;
    $condtions .= ' ORDER BY news.created_date DESC LIMIT '.$range;
    $params = array(':status'=>1,':categoryName'=>$categoryName);
    $articleObj = new News();
    $results = $articleObj->getNewses($condtions,$params);
    $results = sortArrayByPromoted($results);
    return $results;
}

function getAllNewsesFromUser($userId,$page,$limit){
    $condtions = '  news.status = :status AND news.user_type = "user" AND news.user_id = :userId';
    if($limit != null){
        $from = ($page * $limit) - $limit;
        $range = $from.','.$limit;
        $condtions .= ' ORDER BY news.created_date DESC LIMIT '.$range;
    }else{
        $condtions .= ' ORDER BY news.id DESC ';
    }
    $params = array(':status'=>1,':userId'=>$userId);
    $articleObj = new News();
    $results = $articleObj->getNewses($condtions,$params);
    $results = sortArrayByPromoted($results);
    return $results;
}


function getAllNewsesFromCompany($userId,$page,$limit){
    $condtions = '  news.status = :status AND news.user_type = "company" AND news.user_id = :userId';
    if($limit != null){
        $from = ($page * $limit) - $limit;
        $range = $from.','.$limit;
        $condtions .= ' ORDER BY news.created_date DESC LIMIT '.$range;
    }else{
        $condtions .= ' ORDER BY news.id DESC ';
    }
    $params = array(':status'=>1,':userId'=>$userId);
    $articleObj = new News();
    $results = $articleObj->getNewses($condtions,$params);
    $results = sortArrayByPromoted($results);
    return $results;
}

function getLinkedNewses($tags,$articleId,$random,$limit=2){
    $i = 0;
    $x = 0;
    $tagsArray = explode(',',str_replace(' ','',$tags));
    foreach ($tagsArray as $tag){
        $articlesArray[$i]=getAllNewsesFromTag_NoLimit($tag,$articleId,$random);
    }
    $articlesLimited = array();
    foreach ($articlesArray[0] as $itemArray){
        if($limit>$x){
            array_push($articlesLimited,$itemArray);
        }
        $x++;
    }
  return $articlesLimited;
}

function getAllNewsesFromTag_NoLimit($tag,$articleId,$random){
    $condtions = '  news.status = :status ';
    if(!empty($tag)){
        $condtions.=' AND ((plSafeChars(news.tags) LIKE "%'.$tag.'%") OR (news.tags LIKE "%'.$tag.'%"))';
    }
        $condtions .= ' AND news.id <> :articleId';
    if(!$random){
        $condtions .= ' ORDER BY news.created_date DESC ';
    }else{
        $condtions.=  ' ORDER BY RAND() ';
    }

    $params = array(':status'=>1,':articleId'=>$articleId);
    $articleObj = new News();
    $results = $articleObj->getNewses($condtions,$params);
    if(!$random){
        $results = sortArrayByPromoted($results);
    }
    return $results;
}



function getAllNewsesFromTag($tag,$page,$limit){
    $from = ($page * $limit) - $limit;
    $condtions = '  news.status = :status AND ((plSafeChars(news.tags) LIKE "%'.$tag.'%") OR (news.tags LIKE "%'.$tag.'%"))';
    $range = $from.','.$limit;
    $condtions .= ' ORDER BY news.created_date DESC LIMIT '.$range;
    $params = array(':status'=>1);
    $articleObj = new News();
    $results = $articleObj->getNewses($condtions,$params);
    $results = sortArrayByPromoted($results);
    return $results;
}

function getAllNewsesFromWordAndTag($tag,$page,$limit){
    $from = ($page * $limit) - $limit;
    $condtions = '  news.status = :status AND ((plSafeChars(news.tags) LIKE "%'.$tag.'%") OR (news.tags LIKE "%'.$tag.'%") OR (plSafeChars(news.subject) LIKE "%'.$tag.'%") OR (news.subject LIKE "%'.$tag.'%"))';
    $range = $from.','.$limit;
    $condtions .= ' ORDER BY news.created_date DESC LIMIT '.$range;
    $params = array(':status'=>1);
    $articleObj = new News();
    $results = $articleObj->getNewses($condtions,$params);
    $results = sortArrayByPromoted($results);
    return $results;
}

function getAllNewses($page,$limit){
    $from = ($page * $limit) - $limit;
    $condtions = '  news.status = :status AND news.verificated = :verificated ';
    $range = $from.','.$limit;
    $condtions .= ' ORDER BY news.created_date DESC LIMIT '.$range;
    $params = array(':status'=>1, ':verificated'=>1);
    $articleObj = new News();
    $results = $articleObj->getNewses($condtions,$params);
    $results = sortArrayByPromoted($results);
    return $results;
}


function getUserNewses($userId,$status,$page,$limit,$sort){
    $userCompanies = getUserCompanies($userId,1);
    $conditions = '((news.user_type = "user" AND news.user_id = :userId AND users.id = :userId ) ';
    $params = array(':status'=>$status,':userId'=>$userId);
    $i = 1;
    foreach($userCompanies as $uc){
        $conditions.=' OR ((news.user_type = "company") AND news.user_id = :companyId'.$i.')';
        $params += [':companyId'.$i=>$uc['companyId']];
        $i++;
    }
//    $conditions .= ' OR (articles.user_type = "deleted") )  AND articles.status = :status ';
    $conditions .= ' )  AND news.status = :status ';
    $from = ($page * $limit) - $limit;
    $range = $from.','.$limit;
    $conditions .= ' ORDER BY '.$sort.' LIMIT '.$range;
    $articleObj = new News();
    $results = $articleObj->getNewses($conditions,$params);

    return $results;
}



function getUserNewsesCount($userId,$status,$page,$limit){
    $userCompanies = getUserCompanies($userId,1);
    $conditions = '((news.user_type = "user" AND news.user_id = :userId AND users.id = :userId ) ';
    $params = array(':status'=>$status,':userId'=>$userId);
    $i = 1;
    foreach($userCompanies as $uc){
        $conditions.=' OR (news.user_type = "company" AND news.user_id = :companyId'.$i.')';
        $params += [':companyId'.$i=>$uc['companyId']];
        $i++;
    }
//    $conditions .= '  OR (news.user_type = "deleted")) AND news.status = :status ';
    $conditions .= '  ) AND news.status = :status ';
    $conditions .= ' ORDER BY news.id DESC ';
    $articleObj = new News();
    $results = $articleObj->getNewses($conditions,$params);
    return (count($results) > 0 ? count($results) : 0);
}


function showNewsUserPagination($userId,$status,$limit,$kindSafe,$type,$sort){
    if(isset($_GET['category'])){
        $category = htmlspecialchars($_GET['category']);
    }


    $link='/moje-konto/wiadomosci'.(!empty($category)?'/'.$category:'');


    $currentPage = (isset($_POST['page'])?(int)htmlspecialchars($_POST['page']):1);
    $pagesCount = ceil((getUserNewsesCount($userId,$status,$currentPage,$limit)>0?getUserNewsesCount($userId,$status,$currentPage,$limit):1) / $limit);
$pagesCount = ($pagesCount>0?$pagesCount:1);
    $allPages = $pagesCount;
    $pageLoop = 1;

    $pagination.= '
    <form method="post" action="">
        <input type="text" hidden readonly name="type" value="'.$type.'">
<input type="text" hidden readonly name="kind" value="'.$kindSafe.'">
<input type="text" hidden readonly name="sort" value="'.$sort.'">
                <ul class="pagination list">
                    <li class="pagination__item">
                        <button class="pagination__link" value="1" type="submit" name="page" title="Przejdź na stronę">
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
                        <'.($currentPage === $pageLoop ? 'span':'button').' class="pagination__link '.($currentPage === $pageLoop ? 'active':'').'" value="'.$pageLoop.'" '.($currentPage === $pageLoop ? '':'type="submit"').' name="page" title="Przejdź na stronę">'.$pageLoop.'</'.($currentPage === $pageLoop ? 'span':'button').'>
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
                        <button class="pagination__link" value="'.$allPages.'" type="submit" name="page" title="Przejdź na stronę">
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


function searchUserNewses($userId,$status,$page,$limit,$sort,$query){
    $userCompanies = getUserCompanies($userId,1);
    $conditions = '((news.user_type = "user" AND news.user_id = :userId AND users.id = :userId ) ';
    $params = array(':status'=>$status,':userId'=>$userId);
    $i = 1;
    foreach($userCompanies as $uc){
        $conditions.=' OR (news.user_type = "company" AND news.user_id = :companyId'.$i.')';
        $params += [':companyId'.$i=>$uc['companyId']];
        $i++;
    }
    $conditions .= ' OR (news.user_type = "deleted") )  AND news.status = :status AND ( news.subject LIKE "%'.$query.'%" OR news.tags LIKE "%'.$query.'%")';
    $from = ($page * $limit) - $limit;
    $range = $from.','.$limit;
    $conditions .= ' ORDER BY '.$sort.' LIMIT '.$range;
    $articleObj = new News();
    $results = $articleObj->getNewses($conditions,$params);

    return $results;
}

function searchUserNewsesCount($userId,$status,$page,$limit,$sort,$query){
    $userCompanies = getUserCompanies($userId,1);
    $conditions = '((news.user_type = "user" AND news.user_id = :userId AND users.id = :userId ) ';
    $params = array(':status'=>$status,':userId'=>$userId);
    $i = 1;
    foreach($userCompanies as $uc){
        $conditions.=' OR (news.user_type = "company" AND news.user_id = :companyId'.$i.')';
        $params += [':companyId'.$i=>$uc['companyId']];
        $i++;
    }
    $conditions .= ' OR (news.user_type = "deleted") )  AND news.status = :status AND ( news.subject LIKE "%'.$query.'%" OR news.tags LIKE "%'.$query.'%")';

    $articleObj = new News();
    $results = $articleObj->getNewses($conditions,$params);
    return count($results);
}

function showSearchNewsUserPagination($userId,$status,$limit,$kindSafe,$type,$sort,$query){


    $currentPage = (isset($_POST['page'])?(int)htmlspecialchars($_POST['page']):1);
    $pagesCount = ceil(searchUserNewsesCount($userId,$status,$currentPage,$limit) / $limit);
    $pagesCount = ($pagesCount>0?$pagesCount:1);
    $allPages = $pagesCount;
    $pageLoop = 1;

    $pagination.= '
    <form method="post" action="">
        <input type="text" hidden readonly name="type" value="'.$type.'">
<input type="text" hidden readonly name="kind" value="'.$kindSafe.'">
<input type="text" hidden readonly name="sort" value="'.$sort.'">
<input type="text" hidden readonly name="query" value="'.$query.'">
                <ul class="pagination list">
                    <li class="pagination__item">
                        <button class="pagination__link" value="1" type="submit" name="page" title="Przejdź na stronę">
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
                        <'.($currentPage === $pageLoop ? 'span':'button').' class="pagination__link '.($currentPage === $pageLoop ? 'active':'').'" value="'.$pageLoop.'" '.($currentPage === $pageLoop ? '':'type="submit"').'  name="page" title="Przejdź na stronę">'.$pageLoop.'</'.($currentPage === $pageLoop ? 'span':'button').'>
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
                        <'.($currentPage === (int)$endPag ? 'span':'button').' class="pagination__link '.($currentPage === (int)$endPag ? 'active':'').'" value="'.$endPag.'" '.($currentPage === (int)$endPag ? '':'type="submit"').'  name="page" title="Przejdź na stronę">'.$endPag.'</'.($currentPage === (int)$endPag ? 'span':'button').'>
                    </li>
                    ';
                }
                $endPag++;
            }
        }
    }
    $pagination.=' 

                    <li class="pagination__item">
                        <button class="pagination__link" value="'.$allPages.'" type="submit" name="page" title="Przejdź na stronę">
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

function getNewsesCount(){
    $articleObj = new News();
    $condtions = '  news.status = :status ';
    $params = array(':status'=>1);
    $count = $articleObj->getNewsesCount($condtions,$params);
    return $count;
}

function getNewsesCountFromCategory($categoryName){
    $articleObj = new News();
    $condtions = '  news.status = :status AND news.verificated = :verificated ';
    $params = array(':status'=>1, ':verificated'=>1);

    if(!is_null($categoryName)){
        $condtions.=' AND plSafeChars(categories.name) = :categoryName';
        $params += [':categoryName'=>$categoryName];
    }
    $count = $articleObj->getNewsesCount($condtions,$params);
    return $count;
}

function getNewsesCountFromUser($userId){
    $articleObj = new News();
    $condtions = '  news.status = :status AND news.verificated = :verificated AND news.user_type = "user" AND news.user_id = :userId ';
    $params = array(':status'=>1,':verificated'=>1,':userId'=>$userId);
    $count = $articleObj->getNewsesCount($condtions,$params);
    return $count;
}

function getNewsesCountFromCompany($userId){
    $articleObj = new News();
    $condtions = '  news.status = :status AND news.verificated = :verificated AND news.user_type = "company" AND news.user_id = :userId ';
    $params = array(':status'=>1,':verificated'=>1,':userId'=>$userId);
    $count = $articleObj->getNewsesCount($condtions,$params);
    return $count;
}


function getNewsesCountFromTag($tag){
    $articleObj = new News();
    $condtions = '  news.status = :status AND news.verificated = :verificated AND ((plSafeChars(news.tags) LIKE "%'.$tag.'%") OR (news.tags LIKE "%'.$tag.'%")) ';
    $params = array(':status'=>1,':verificated'=>1);
    $count = $articleObj->getNewsesCount($condtions,$params);
    return $count;
}

function getNewsesCountFromWordAndTag($tag){
    $articleObj = new News();
    $condtions = '  news.status = :status AND news.verificated = :verificated  AND ((plSafeChars(news.tags) LIKE "%'.$tag.'%") OR (news.tags LIKE "%'.$tag.'%") OR (plSafeChars(news.subject) LIKE "%'.$tag.'%") OR (news.subject LIKE "%'.$tag.'%")) ';
    $params = array(':status'=>1,':verificated'=>1);
    $count = $articleObj->getNewsesCount($condtions,$params);
    return $count;
}

function showNewsPagination($page,$limit){
    if(isset($_GET['category'])){
        $category = htmlspecialchars($_GET['category']);
    }


    $link='/wiadomosci'.(!empty($category)?'/'.$category:'');

    $pagesCount = ceil(getNewsesCount() / $limit);
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


function showNewsPaginationFromCategory($categoryName,$page,$limit){
    if(isset($_GET['category'])){
        $category = htmlspecialchars($_GET['category']);
    }


    $link='/wiadomosci'.(!empty($category)?'/'.$category:'');

    $pagesCount = ceil(getNewsesCountFromCategory($categoryName) / $limit);
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
                        <'.($currentPage === (int)$i ? 'span':'a').' class="pagination__link '.($currentPage === (int)$i ? 'active':'').'" '.($currentPage === (int)$i ? '':' href="'.$link.'/'.$i.'/"').' title="Przejdź na stronę">'.$i.'</'.($currentPage === (int)$i ? 'span':'a').'>
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
                        <'.($currentPage === (int)$endPag ? 'span':'a').' class="pagination__link '.($currentPage === (int)$endPag ? 'active':'').'" href="'.$link.'/'.$endPag.'/" title="Przejdź na stronę">'.$endPag.'</'.($currentPage === (int)$endPag ? 'span':'a').'>
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


function showNewsPaginationFromUser($userId,$userLogin,$page,$limit){


    $link='/profil/'.$userLogin.','.$userId.'/wiadomosci';

    $pagesCount = ceil(getNewsesCountFromUser($userId) / $limit);
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
            $pagination .= '
                    <li class="pagination__item">
                        <'.($currentPage === (int)$i ? 'active':'').' class="pagination__link '.($currentPage === (int)$i ? 'active':'').'" '.($currentPage === (int)$i ? '':'href="'.$link.'/'.$i.'/"').'  title="Przejdź na stronę">'.$i.'</a>
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
                        <'.($currentPage === (int)$endPag ? 'span':'a').' class="pagination__link '.($currentPage === (int)$endPag ? 'active':'').'"  '.($currentPage === (int)$endPag ? '':'href="'.$link.'/'.$endPag.'/"').' title="Przejdź na stronę">'.$endPag.'</'.($currentPage === (int)$endPag ? 'span':'a').'>
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



function showNewsPaginationFromCompany($companyId,$companyName,$page,$limit){


    $link='/firmy/'.$companyName.','.$companyId.'/wiadomosci';

    $pagesCount = ceil(getNewsesCountFromCompany($companyId) / $limit);
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
                        <'.($currentPage === (int)$i ? 'span':'a').' class="pagination__link '.($currentPage === (int)$i ? 'active':'').'" href="'.$link.'/'.$i.'/" title="Przejdź na stronę">'.$i.'</ '.($currentPage === (int)$i ? 'span':'a').'>
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
    return $pagination;
}



function showNewsPaginationFromTag($tag,$page,$limit){
    if(isset($_GET['tag'])){
        $tag = htmlspecialchars($_GET['tag']);
    }


    $link='/tag/wiadomosci'.(!empty($tag)?'/'.$tag:'');

    $pagesCount = ceil(getNewsesCountFromTag($tag) / $limit);
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




function showNewsPaginationFromWordAndTag($word,$page,$limit){

    $link='/szukaj/wiadomosci'.(!empty($word)?'/'.$word:'');

    $pagesCount = ceil(getNewsesCountFromWordAndTag($word) / $limit);
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
                if($endPag>0){
                    $pagination.='
                    <li class="pagination__item">
                        <'.($currentPage === (int)$endPag ? 'span':'a').' class="pagination__link '.($currentPage === (int)$endPag ? 'active':'').'" '.($currentPage === (int)$endPag ? '':'href="'.$link.'/'.$endPag.'/"').' title="Przejdź na stronę">'.$endPag.'</ '.($currentPage === (int)$endPag ? 'span':'a').'>
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



function addNewsView($id){
    $articleObj = new News();
    $articleObj->addView($id);
}