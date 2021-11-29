<?php

function setDisabled_Article($articleId){
    $obj = new Article();
    $set = 'articles.status = :status';
    $params = array(':status'=>2);
    $result = $obj->updateArticle($articleId,$set,$params);
    return $result;
}

function getAuthor_Article($id,$title){
    $article = getArticle($id,$title);
    $articleArray = ['author'=>$article[0]['user_id'],'author_type'=>$article[0]['user_type']];
    return $articleArray;
}

function addArticle($subject,$content,$tags,$category_id,$image,$user_id,$user_type,$status,$verificated){
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
   $articleObj = new Article();
   $result = $articleObj->addArticle(trim($subject),trim($content),trim($tagList),$category_id,$image,$user_id,$user_type,$status,$verificated);
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


function updateArticle($subject,$content,$tags,$category_id,$image,$user_id,$user_type,$verificated,$revision,$articleId){
    $articleObj = new Article();
    $set = ' articles.subject = :subject, articles.content = :content, articles.tags = :tags,  articles.category_id = :categoryId, articles.user_id = :userId, articles.user_type = :userType ';
    $params = array(':subject'=>$subject, ':content'=>$content, ':tags'=>$tags, ':categoryId'=>$category_id, ':userId'=>$user_id, ':userType'=>$user_type);



        $set .= ' , articles.revision = :revision ';
        $params += [':revision'=>$revision];



    if(!empty($verificated)){
        $set .= ' , articles.verificated = :verificated ';
        $params += [':verificated'=>$verificated];
    }

    if($image != null){
        $set .= ' , articles.image = :image ';
        $params += [':image'=>$image];
    }


   $result = $articleObj->updateArticle($articleId,$set,$params);
    return $result;
}

function getArticle($id,$title,$status){
    $condtions = '  articles.id = :id';
    $params = array(':id'=>$id);
    if($title != null){
        $condtions.=' AND plSafeChars(articles.subject) = :title ';
        $params += [':title'=>$title];
    }

    if($status != null){
        $condtions .=' AND articles.status = :status';
        $params += [':status'=>$status];
    }
    $articleObj = new Article();
    $results = $articleObj->getArticles($condtions,$params);
    return $results;
}


function getArticles($limit,$category){
    $conditions = null;
    if($category!=null){
        $conditions .=' plSafeChars(categories.name) = :category AND ';
    }
    $conditions .= '  articles.status = :status AND articles.verificated = :verificated ';
    if($limit != null){
        $conditions .= ' ORDER BY articles.created_date DESC LIMIT '.$limit;
    }
    $params = array(':status'=>1,':verificated'=>1);
    if($category!=null){
        $params += [':category'=>$category];
    }
    $articleObj = new Article();
    $results = $articleObj->getArticles($conditions,$params);
    $results = sortArrayByPromoted($results);
    return $results;
}

function getArticles_Tag($limit,$tag){
    $conditions = null;

    $conditions .=' ( (plSafeChars(articles.tags) LIKE "%'.$tag.'%") OR (articles.tags LIKE "%'.$tag.'%")) AND ';
    $conditions .= '  articles.status = :status ';
    if($limit != null){
        $conditions .= ' ORDER BY articles.created_date DESC LIMIT '.$limit;
    }
    $params = array(':status'=>1);
    $articleObj = new Article();
    $results = $articleObj->getArticles($conditions,$params);
    $results = sortArrayByPromoted($results);
    return $results;
}

function getArticles_WordAndTag($limit,$keyword){
    $conditions = null;
    $conditions .=' ( (plSafeChars(articles.subject) LIKE "%'.$keyword.'%") OR (articles.subject LIKE "%'.$keyword.'%") OR  (plSafeChars(articles.content) LIKE "%'.$keyword.'%") OR (articles.content LIKE "%'.$keyword.'%")) AND ';
    $conditions .= '  articles.status = :status ';
    if($limit != null){
        $conditions .= ' ORDER BY articles.created_date DESC LIMIT '.$limit;
    }
    $params = array(':status'=>1);
    $articleObj = new Article();
    $results = $articleObj->getArticles($conditions,$params);
    $results = sortArrayByPromoted($results);
    return $results;
}


function getAllArticlesFromCategory($categoryName,$page,$limit){
    $from = ($page * $limit) - $limit;
    $condtions = '  articles.status = :status AND plSafeChars(categories.name) = :categoryName';
    $range = $from.','.$limit;
    $condtions .= ' ORDER BY articles.created_date DESC LIMIT '.$range;
    $params = array(':status'=>1,':categoryName'=>$categoryName);
    $articleObj = new Article();
    $results = $articleObj->getArticles($condtions,$params);
    $results = sortArrayByPromoted($results);
    return $results;
}

function getAllArticlesFromUser($userId,$page,$limit){
    $condtions = '  articles.status = :status AND articles.user_type = "user" AND articles.user_id = :userId';
    if($limit != null){
        $from = ($page * $limit) - $limit;
        $range = $from.','.$limit;
        $condtions .= ' ORDER BY articles.created_date DESC LIMIT '.$range;
    }else{
        $condtions .= ' ORDER BY articles.id DESC ';
    }
    $params = array(':status'=>1,':userId'=>$userId);
    $articleObj = new Article();
    $results = $articleObj->getArticles($condtions,$params);
    $results = sortArrayByPromoted($results);
    return $results;
}


function getAllArticlesFromCompany($userId,$page,$limit){
    $condtions = '  articles.status = :status AND articles.user_type = "company" AND articles.user_id = :userId';
    if($limit != null){
        $from = ($page * $limit) - $limit;
        $range = $from.','.$limit;
        $condtions .= ' ORDER BY articles.created_date DESC LIMIT '.$range;
    }else{
        $condtions .= ' ORDER BY articles.id DESC ';
    }
    $params = array(':status'=>1,':userId'=>$userId);
    $articleObj = new Article();
    $results = $articleObj->getArticles($condtions,$params);
    $results = sortArrayByPromoted($results);
    return $results;
}

function getLinkedArticles($tags,$articleId,$random,$limit=2){
    $i = 0;
    $x = 0;
    $tagsArray = explode(',',str_replace(' ','',$tags));
    foreach ($tagsArray as $tag){
        $articlesArray[$i]=getAllArticlesFromTag_NoLimit($tag,$articleId,$random);
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

function getAllArticlesFromTag_NoLimit($tag,$articleId,$random){
    $condtions = '  articles.status = :status ';
    if(!empty($tag)){
        $condtions.=' AND ((plSafeChars(articles.tags) LIKE "%'.$tag.'%") OR (articles.tags LIKE "%'.$tag.'%"))';
    }
        $condtions .= ' AND articles.id <> :articleId';
    if(!$random){
        $condtions .= ' ORDER BY articles.created_date DESC ';
    }else{
        $condtions.=  ' ORDER BY RAND() ';
    }

    $params = array(':status'=>1,':articleId'=>$articleId);
    $articleObj = new Article();
    $results = $articleObj->getArticles($condtions,$params);
    if(!$random){
        $results = sortArrayByPromoted($results);
    }
    return $results;
}



function getAllArticlesFromTag($tag,$page,$limit){
    $from = ($page * $limit) - $limit;
    $condtions = '  articles.status = :status AND ((plSafeChars(articles.tags) LIKE "%'.$tag.'%") OR (articles.tags LIKE "%'.$tag.'%"))';
    $range = $from.','.$limit;
    $condtions .= ' ORDER BY articles.created_date DESC LIMIT '.$range;
    $params = array(':status'=>1);
    $articleObj = new Article();
    $results = $articleObj->getArticles($condtions,$params);
    $results = sortArrayByPromoted($results);
    return $results;
}

function getAllArticlesFromWordAndTag($tag,$page,$limit){
    $from = ($page * $limit) - $limit;
    $condtions = '  articles.status = :status AND ((plSafeChars(articles.tags) LIKE "%'.$tag.'%") OR (articles.tags LIKE "%'.$tag.'%") OR (plSafeChars(articles.subject) LIKE "%'.$tag.'%") OR (articles.subject LIKE "%'.$tag.'%"))';
    $range = $from.','.$limit;
    $condtions .= ' ORDER BY articles.created_date DESC LIMIT '.$range;
    $params = array(':status'=>1);
    $articleObj = new Article();
    $results = $articleObj->getArticles($condtions,$params);
    $results = sortArrayByPromoted($results);
    return $results;
}

function getAllArticles($page,$limit){
    $from = ($page * $limit) - $limit;
    $condtions = '  articles.status = :status AND articles.verificated = :verificated ';
    $range = $from.','.$limit;
    $condtions .= ' ORDER BY articles.created_date DESC LIMIT '.$range;
    $params = array(':status'=>1, ':verificated'=>1);
    $articleObj = new Article();
    $results = $articleObj->getArticles($condtions,$params);
    $results = sortArrayByPromoted($results);
    return $results;
}

function getUserArticles($userId,$status,$page,$limit,$sort){
    $userCompanies = getUserCompanies($userId,1);
    $conditions = '((articles.user_type = "user" AND articles.user_id = :userId AND users.id = :userId ) ';
    $params = array(':status'=>$status,':userId'=>$userId);
    $i = 1;
    foreach($userCompanies as $uc){
        $conditions.=' OR ((articles.user_type = "company") AND articles.user_id = :companyId'.$i.')';
        $params += [':companyId'.$i=>$uc['companyId']];
        $i++;
    }
//    $conditions .= ' OR (articles.user_type = "deleted") )  AND articles.status = :status ';
    $conditions .= ' )  AND articles.status = :status ';
    $from = ($page * $limit) - $limit;
    $range = $from.','.$limit;
    $conditions .= ' ORDER BY '.$sort.' LIMIT '.$range;
    $articleObj = new Article();
    $results = $articleObj->getArticles($conditions,$params);

    return $results;
}



function getUserArticlesCount($userId,$status,$page,$limit){
    $userCompanies = getUserCompanies($userId,1);
    $conditions = '((articles.user_type = "user" AND articles.user_id = :userId AND users.id = :userId ) ';
    $params = array(':status'=>$status,':userId'=>$userId);
    $i = 1;
    foreach($userCompanies as $uc){
        $conditions.=' OR (articles.user_type = "company" AND articles.user_id = :companyId'.$i.')';
        $params += [':companyId'.$i=>$uc['companyId']];
        $i++;
    }
//    $conditions .= '  OR (articles.user_type = "deleted")) AND articles.status = :status ';
    $conditions .= '  ) AND articles.status = :status ';
    $conditions .= ' ORDER BY articles.id DESC ';
    $articleObj = new Article();
    $results = $articleObj->getArticles($conditions,$params);
    return (count($results) > 0 ? count($results) : 0);
}


function showArticleUserPagination($userId,$status,$limit,$kindSafe,$type,$sort){
    if(isset($_GET['category'])){
        $category = htmlspecialchars($_GET['category']);
    }


    $link='/moje-konto/artykuly'.(!empty($category)?'/'.$category:'');


    $currentPage = (isset($_POST['page'])?(int)htmlspecialchars($_POST['page']):1);
    $pagesCount = ceil((getUserArticlesCount($userId,$status,$currentPage,$limit)>0?getUserArticlesCount($userId,$status,$currentPage,$limit):1) / $limit);
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


function searchUserArticles($userId,$status,$page,$limit,$sort,$query){
    $userCompanies = getUserCompanies($userId,1);
    $conditions = '((articles.user_type = "user" AND articles.user_id = :userId AND users.id = :userId ) ';
    $params = array(':status'=>$status,':userId'=>$userId);
    $i = 1;
    foreach($userCompanies as $uc){
        $conditions.=' OR (articles.user_type = "company" AND articles.user_id = :companyId'.$i.')';
        $params += [':companyId'.$i=>$uc['companyId']];
        $i++;
    }
    $conditions .= ' OR (articles.user_type = "deleted") )  AND articles.status = :status AND ( articles.subject LIKE "%'.$query.'%" OR articles.tags LIKE "%'.$query.'%")';
    $from = ($page * $limit) - $limit;
    $range = $from.','.$limit;
    $conditions .= ' ORDER BY '.$sort.' LIMIT '.$range;
    $articleObj = new Article();
    $results = $articleObj->getArticles($conditions,$params);

    return $results;
}

function searchUserArticlesCount($userId,$status,$page,$limit,$sort,$query){
    $userCompanies = getUserCompanies($userId,1);
    $conditions = '((articles.user_type = "user" AND articles.user_id = :userId AND users.id = :userId ) ';
    $params = array(':status'=>$status,':userId'=>$userId);
    $i = 1;
    foreach($userCompanies as $uc){
        $conditions.=' OR (articles.user_type = "company" AND articles.user_id = :companyId'.$i.')';
        $params += [':companyId'.$i=>$uc['companyId']];
        $i++;
    }
    $conditions .= ' OR (articles.user_type = "deleted") )  AND articles.status = :status AND ( articles.subject LIKE "%'.$query.'%" OR articles.tags LIKE "%'.$query.'%")';

    $articleObj = new Article();
    $results = $articleObj->getArticles($conditions,$params);
    return count($results);
}

function showSearchArticleUserPagination($userId,$status,$limit,$kindSafe,$type,$sort,$query){


    $currentPage = (isset($_POST['page'])?(int)htmlspecialchars($_POST['page']):1);
    $pagesCount = ceil(searchUserArticlesCount($userId,$status,$currentPage,$limit) / $limit);
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

function getArticlesCount(){
    $articleObj = new Article();
    $condtions = '  articles.status = :status ';
    $params = array(':status'=>1);
    $count = $articleObj->getArticlesCount($condtions,$params);
    return $count;
}

function getArticlesCountFromCategory($categoryName){
    $articleObj = new Article();
    $condtions = '  articles.status = :status AND articles.verificated = :verificated ';
    $params = array(':status'=>1, ':verificated'=>1);

    if(!is_null($categoryName)){
        $condtions.=' AND plSafeChars(categories.name) = :categoryName';
        $params += [':categoryName'=>$categoryName];
    }
    $count = $articleObj->getArticlesCount($condtions,$params);
    return $count;
}

function getArticlesCountFromUser($userId){
    $articleObj = new Article();
    $condtions = '  articles.status = :status AND articles.verificated = :verificated AND articles.user_type = "user" AND articles.user_id = :userId ';
    $params = array(':status'=>1,':verificated'=>1,':userId'=>$userId);
    $count = $articleObj->getArticlesCount($condtions,$params);
    return $count;
}

function getArticlesCountFromCompany($userId){
    $articleObj = new Article();
    $condtions = '  articles.status = :status AND articles.verificated = :verificated AND articles.user_type = "company" AND articles.user_id = :userId ';
    $params = array(':status'=>1,':verificated'=>1,':userId'=>$userId);
    $count = $articleObj->getArticlesCount($condtions,$params);
    return $count;
}


function getArticlesCountFromTag($tag){
    $articleObj = new Article();
    $condtions = '  articles.status = :status AND articles.verificated = :verificated AND ((plSafeChars(articles.tags) LIKE "%'.$tag.'%") OR (articles.tags LIKE "%'.$tag.'%")) ';
    $params = array(':status'=>1,':verificated'=>1);
    $count = $articleObj->getArticlesCount($condtions,$params);
    return $count;
}

function getArticlesCountFromWordAndTag($tag){
    $articleObj = new Article();
    $condtions = '  articles.status = :status AND articles.verificated = :verificated  AND ((plSafeChars(articles.tags) LIKE "%'.$tag.'%") OR (articles.tags LIKE "%'.$tag.'%") OR (plSafeChars(articles.subject) LIKE "%'.$tag.'%") OR (articles.subject LIKE "%'.$tag.'%")) ';
    $params = array(':status'=>1,':verificated'=>1);
    $count = $articleObj->getArticlesCount($condtions,$params);
    return $count;
}

function showArticlePagination($page,$limit){
    if(isset($_GET['category'])){
        $category = htmlspecialchars($_GET['category']);
    }


    $link='/artykuly'.(!empty($category)?'/'.$category:'');

    $pagesCount = ceil(getArticlesCount() / $limit);
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


function showArticlePaginationFromCategory($categoryName,$page,$limit){
    if(isset($_GET['category'])){
        $category = htmlspecialchars($_GET['category']);
    }


    $link='/artykuly'.(!empty($category)?'/'.$category:'');

    $pagesCount = ceil(getArticlesCountFromCategory($categoryName) / $limit);
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


function showArticlePaginationFromUser($userId,$userLogin,$page,$limit){


    $link='/profil/'.$userLogin.','.$userId.'/artykuly';

    $pagesCount = ceil(getArticlesCountFromUser($userId) / $limit);
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



function showArticlePaginationFromCompany($companyId,$companyName,$page,$limit){


    $link='/firmy/'.$companyName.','.$companyId.'/artykuly';

    $pagesCount = ceil(getArticlesCountFromCompany($companyId) / $limit);
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



function showArticlePaginationFromTag($tag,$page,$limit){
    if(isset($_GET['tag'])){
        $tag = htmlspecialchars($_GET['tag']);
    }


    $link='/tag/artykuly'.(!empty($tag)?'/'.$tag:'');

    $pagesCount = ceil(getArticlesCountFromTag($tag) / $limit);
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




function showArticlePaginationFromWordAndTag($word,$page,$limit){

    $link='/szukaj/artykuly'.(!empty($word)?'/'.$word:'');

    $pagesCount = ceil(getArticlesCountFromWordAndTag($word) / $limit);
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



function addArticleView($id){
    $articleObj = new Article();
    $articleObj->addView($id);
}