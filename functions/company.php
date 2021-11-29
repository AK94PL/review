<?php

function addCompany($userId,$name,$nip,$city,$categoryId,$short_description,$phone){
    $companyObj = new Company();
    $companyId = $companyObj->addCompany($userId,$name,$nip,$city,$categoryId,$short_description,$phone);
    return $companyId;
}


function getLogo_Company($companyId){
    $conditions = null;
    $params = null;

    $conditions .= ' AND companies.id = :id  ';
    $params = array(':id'=>$companyId);

    $companyObj = new Company();
    $result = $companyObj->getCompanies($conditions,$params);
    return $result[0]['logo'];
}

function getOwnerName_Company($companyId){
    $company = getCompanyById($companyId);
    $ownerId = $company[0]['user_id'];
    $ownerLogin = getAuthorLogin($ownerId,'user');
    $ownerLoginPL = getAuthorLoginPL($ownerId,'user');
    $owner = array('ownerId'=>$ownerId,'ownerLogin'=>$ownerLogin, 'ownerLoginPL'=>$ownerLoginPL);
    return $owner;
}

function getName_Company($companyId){
    $conditions = null;
    $params = null;

    $conditions .= ' AND companies.id = :id  ';
    $params = array(':id'=>$companyId);

    $companyObj = new Company();
    $result = $companyObj->getCompanies($conditions,$params);
    return $result[0]['name'];
}


function getName_Company_PL($companyId){
    $conditions = null;
    $params = null;

    $conditions .= ' AND companies.id = :id  ';
    $params = array(':id'=>$companyId);

    $companyObj = new Company();
    $result = $companyObj->getCompanies($conditions,$params);
    return $result[0]['namePL'];
}

function isCompanyVerificated($companyId){
    $companyObj = new Company();
    $conditions = ' AND companies.id = :companyId AND companies.verificated = :verificated ';
    $params = array(':companyId'=>$companyId,':verificated'=>1);
    $result = $companyObj->getCompanies($conditions,$params);
    return count($result);
}

function setLogo_Company($fileName,$companyId){
    $companyObj = new Company();
    $params =array(':fileName'=>$fileName);
    $set = 'companies.logo = :fileName';
    $companyData = getCompanyById($companyId);
    $oldLogo = $companyData[0]['logo'];
    if($companyObj->updateCompany($companyId,$set,$params)){
        if(!empty($oldLogo))
        {
            unlink($_SERVER['DOCUMENT_ROOT'].'/uploads/companies/'.$oldLogo);
        }
        return true;
    }else{
        return false;
    }
}

function getCompanyByNIP($nip)
{
    $conditions = null;
    $params = null;
    $conditions .= '  companies.nip = :nip  ';
    $params = array(':nip'=>$nip);
    $companyObj = new Company();
    $result = $companyObj->getAllCompanies($conditions,$params);
    return $result;
}

function getCompanyById($companyId,$companyName){
    $conditions = null;
    $params = null;

    $conditions .= ' AND companies.id = :id  ';
    $params = array(':id'=>$companyId);
    if($companyName!=null){
        $conditions.=' AND plSafeChars(companies.name) = :companyName';
        $params +=[':companyName'=>$companyName];
    }

    $companyObj = new Company();
    $result = $companyObj->getCompanies($conditions,$params);
    return $result;
}

function getCompanies($limit,$category){
    if($category!=null){
        $category = trim($category);
    }

    $conditions = null;
    $conditions .=' AND companies.status = :status';
    $params = array(':status'=>1);
    if($category!=null && isCategoryExist($category)){
        $conditions .= ' AND plSafeChars(categories.name) = :category ';
    }
    if($limit != null){
        $conditions .= ' ORDER BY companies.id DESC LIMIT '.$limit;
    }

    if($category!=null){
        $params += [':category'=>$category];
    }
    $companyObj = new Company();
    $results = $companyObj->getCompanies($conditions,$params);
    $results = sortArrayByVerificatedAndPromoted($results);
    return $results;
}


function getCompanies_Tag($limit,$tag){

    $conditions = null;

    $conditions .= ' AND companies.status = :status AND ((plSafeChars(companies.keywords) LIKE "%'.$tag.'%" )  OR (companies.keywords LIKE "%'.$tag.'%" )) ';
    $params = array(':status'=>1);
    if($limit != null){
        $conditions .= ' ORDER BY companies.id DESC LIMIT '.$limit;
    }

    $companyObj = new Company();
    $results = $companyObj->getCompanies($conditions,$params);
    $results = sortArrayByVerificatedAndPromoted($results);
    return $results;
}


function getCompanies_WordAndTag($limit,$word){

    $conditions = null;
    $conditions .= ' AND companies.status = :status AND ((plSafeChars(companies.name) LIKE "%'.$word.'%" )  OR (companies.name LIKE "%'.$word.'%" ) OR (plSafeChars(companies.keywords) LIKE "%'.$word.'%" )  OR (companies.keywords LIKE "%'.$word.'%" )) ';

    if($limit != null){
        $conditions .= ' ORDER BY companies.id DESC LIMIT '.$limit;
    }

    $params = array(':status'=>1);
    $companyObj = new Company();
    $results = $companyObj->getCompanies($conditions,$params);
    $results = sortArrayByVerificatedAndPromoted($results);

    return $results;
}

function getCompanyOwnerId($companyId){
    $company = getCompanyById($companyId,null);
    return $company[0]['user_id'];
}


function searchUserCompanies($userId, $status, $query){
    $conditions = null;
    $params = null;
    $conditions .= ' AND companies.user_id = :userId AND (companies.name LIKE "%'.$query.'%" OR companies.keywords LIKE "%'.$query.'%" ) ';
    $params = array(':userId'=>$userId);
    if(isset($status)){
        $conditions .='  AND companies.status = :status ';
        $params +=[':status'=>$status];
    }
    $companyObj = new Company();
    $result = $companyObj->getCompanies($conditions,$params);
    return $result;
}


function getUserCompanies($userId, $status,$page,$sort){

    $limit = 20;

    $conditions = null;
    $params = null;
    $conditions .= ' AND companies.user_id = :userId ';
    $params = array(':userId'=>$userId);
    if(isset($status)){
        $conditions .='  AND companies.status = :status ';
        $params +=[':status'=>$status];
    }

    if(!empty($sort)){
        $conditions .= ' ORDER BY '.$sort;
    }

    if(!empty($page)){
        $from = ($page * $limit) - $limit;
        $range = $from.','.$limit;
        $conditions.=' LIMIT '.$range;
    }

    $companyObj = new Company();
    $result = $companyObj->getCompanies($conditions,$params);
    return $result;
}

function isUserOwner_Company($companyId){
    $company = getCompanyById($companyId);
    if((int)$company[0]['user_id'] === (int)$_SESSION['user']->id){
        return true;
    }else{
        return false;
    }
}

function getOwnCompany($companyId,$companyName){
    $conditions = null;
    $params = null;
    $conditions .= ' AND companies.id = :companyId AND companies.user_id = :userId AND (companies.status = :statusActive OR companies.status = :statusDeactive) ';
    $params = array(':companyId'=>$companyId,':userId'=>$_SESSION['user']->id,':statusActive'=>1, ':statusDeactive'=>2);
    if($companyName != null){
        $conditions.=' AND plSafeChars(companies.name) = :companyName';
        $params += [':companyName'=>$companyName];
    }
    $companyObj = new Company();
    $result = $companyObj->getCompanies($conditions,$params);
    return $result;
}

function getUserCompaniesCount($userId,$status){
    $conditions = null;
    $params = null;
    $conditions .= ' AND companies.user_id = :userId AND companies.status = :status';
    $params = array(':userId'=>$userId, ':status'=>$status);
    $companyObj = new Company();
    $result = $companyObj->getCompanies($conditions,$params);
    return (count($result) > 0 ? count($result) : 0);
}


function getSearchUserCompaniesCount($userId,$status,$query){
    $conditions = null;
    $params = null;
    $conditions .= ' AND companies.user_id = :userId AND companies.status = :status AND (companies.name LIKE "%'.$query.'%" OR companies.keywords LIKE "%'.$query.'%")';
    $params = array(':userId'=>$userId, ':status'=>$status);
    $companyObj = new Company();
    $result = $companyObj->getCompanies($conditions,$params);
    return (count($result) > 0 ? count($result) : 1);
}


function showCompaniesUserPagination($userId,$status,$limit,$kindSafe,$type,$sort){


    $currentPage = (isset($_POST['page'])?(int)htmlspecialchars($_POST['page']):1);

    $pagesCount = ceil((getUserCompaniesCount($userId,$status)>0?getUserCompaniesCount($userId,$status):1) / $limit);

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
                        <'.($currentPage === (int)$i ? 'span':'button').' class="pagination__link '.($currentPage === (int)$i ? 'active':'').'" value="'.$i.'" name="page" type="submit" title="Przejdź na stronę">'.$i.'</'.($currentPage === (int)$i ? 'span':'button').'>
                    </li>
                    ';
        }
    }else{
        if($currentPage <= 2 && $currentPage>0){
            while($pagesCount > 0 && $pageLoop<=5){
                $pagination.='
                    <li class="pagination__item">
                        <'.($currentPage === $pageLoop ? 'span':'button').' class="pagination__link '.($currentPage === $pageLoop ? 'active':'').'" value="'.$pageLoop.'" name="page" type="submit" title="Przejdź na stronę"> '.$pageLoop.'</'.($currentPage === $pageLoop ? 'span':'button').'>
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
                        <'.($currentPage === (int)$endPag ? 'span':'button').' class="pagination__link '.($currentPage === (int)$endPag ? 'active':'').'" value="'.$endPag.'" name="page" type="submit" title="Przejdź na stronę">'.$endPag.'</'.($currentPage === (int)$endPag ? 'span':'button').'>
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


function showSearchCompaniesUserPagination($userId,$status,$limit,$kindSafe,$type,$sort,$query){

    $currentPage = (isset($_GET['page'])?(int)htmlspecialchars($_GET['page']):1);
    $pagesCount = ceil(getSearchUserCompaniesCount($userId,$status,$query) / $limit);
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
                        <'.($currentPage === (int)$i ? 'span':'button').' class="pagination__link '.($currentPage === (int)$i ? 'active':'').'" value="'.$i.'" name="page" type="submit" title="Przejdź na stronę">'.$i.'</'.($currentPage === (int)$i ? 'span':'button').'>
                    </li>
                    ';
        }
    }else{
        if($currentPage <= 2 && $currentPage>0){
            while($pagesCount > 0 && $pageLoop<=5){
                $pagination.='
                    <li class="pagination__item">
                        <'.($currentPage === $pageLoop ? 'span':'button').' class="pagination__link '.($currentPage === $pageLoop ? 'active':'').'" value="'.$pageLoop.'" name="page" type="submit" title="Przejdź na stronę"> '.$pageLoop.'</'.($currentPage === $pageLoop ? 'span':'button').'>
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
                        <'.($currentPage === (int)$endPag ? 'span':'button').' class="pagination__link '.($currentPage === (int)$endPag ? 'active':'').'" value="'.$endPag.'" name="page" type="submit" title="Przejdź na stronę">'.$endPag.'</'.($currentPage === (int)$endPag ? 'span':'button').'>
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



function getAllCompanies($city, $page,$limit){
    if($city!=null){
        $city = trim($city);
    }

    $params = null;
    $conditions = null;
    $from = ($page * $limit) - $limit;
    $range = $from.','.$limit;
    $conditions.=' AND companies.status = :status';
    $params = array(':status'=>1);
    if($city!=null){
        $conditions.=' AND plSafeChars(companies.city) = :city ';
        $params +=[':city'=>$city];
    }
    $conditions .= ' ORDER BY companies.created_date  DESC ';
    if(!empty($limit)){$conditions.='LIMIT '.$range;}

    $companyObj = new Company();
    $results = $companyObj->getCompanies($conditions,$params);
    $results = sortArrayByVerificatedAndPromoted($results);
    return $results;
}

function getAllCompaniesFromCategory($city, $categoryName,$page,$limit){
    if($city!=null){
     $city = trim($city);
    }

    if($categoryName!=null && isCategoryExist($categoryName)){
        $categoryName = trim($categoryName);
    }

    $from = ($page * $limit) - $limit;
    $range = $from.','.$limit;
    $conditions = null;
    if($city!=null){
        $conditions.=' AND plSafeChars(companies.city) = :city ';
    }
    if(isCategoryExist($categoryName)){
        $conditions.=' AND plSafeChars(categories.name) = :categoryName ';
    }
    $conditions .= ' AND companies.status = :status';
    $conditions .= ' ORDER BY companies.id DESC LIMIT '.$range;
    $params = array(':status'=>1);
    if($city!=null){
        $params += [':city'=>$city];
    }
    if(isCategoryExist($categoryName)){
        $params += [':categoryName'=>$categoryName];
    }
    $companyObj = new Company();
    $results = $companyObj->getCompanies($conditions,$params);
    $results = sortArrayByVerificatedAndPromoted($results);
    return $results;
}

function getAllCompaniesFromTag($tag,$page,$limit){
    if($tag!=null){
        $tag = trim($tag);
    }
    $params = array();
    $from = ($page * $limit) - $limit;
    $range = $from.','.$limit;
    $conditions = null;
    $conditions .= ' AND companies.status = :status AND ( (plSafeChars(companies.keywords) LIKE "%'.$tag.'%") OR (companies.keywords LIKE "%'.$tag.'%") )';
    $conditions .= ' ORDER BY companies.id DESC LIMIT '.$range;
    $params += [':status'=>1];
    $companyObj = new Company();
    $results = $companyObj->getCompanies($conditions,$params);
    $results = sortArrayByVerificatedAndPromoted($results);
    return $results;
}


function getAllCompaniesFromWordAndTag($tag,$page,$limit){
    if($tag!=null){
        $tag = trim($tag);
    }
    $from = ($page * $limit) - $limit;
    $range = $from.','.$limit;
    $conditions = null;
    $conditions .= ' AND companies.status = :status AND ( (plSafeChars(companies.keywords) LIKE "%'.$tag.'%") OR (companies.keywords LIKE "%'.$tag.'%") OR (plSafeChars(companies.name) LIKE "%'.$tag.'%") OR (companies.name LIKE "%'.$tag.'%"))';
    $conditions .= ' ORDER BY companies.id DESC LIMIT '.$range;

    $params = array(':status'=>1);

    $companyObj = new Company();
    $results = $companyObj->getCompanies($conditions,$params);
    $results = sortArrayByVerificatedAndPromoted($results);
    return $results;
}


function getCompaniesCountFromCategory($city,$categoryName){
    if($categoryName!=null){
        $categoryName = trim($categoryName);
    }
    $companyObj = new Company();
    $conditions = null;
    $params = array(':status'=>1);

    if($city!=null){
        $conditions .=' AND plSafeChars(companies.city) = :city ';
    }
    if($categoryName!=null && isCategoryExist($categoryName)){
        $conditions .=' AND plSafeChars(categories.name) = :categoryName  ';
        $params +=[':categoryName'=>$categoryName];
    }
    $conditions .= '  AND companies.status = :status';
    if($city!=null){
        $params += [':city'=>$city];
    }
    $count = $companyObj->getCompaniesCount($conditions,$params);
    return $count;
}


function getCompaniesCountFromTag($tag){
    $params = array();
    $companyObj = new Company();
    $conditions = null;
    $conditions .= ' AND ( (plSafeChars(companies.keywords) LIKE "%'.$tag.'%") OR (companies.keywords LIKE "%'.$tag.'%") ) AND companies.status = :status ';
    $params += [':status'=>1];
    $count = $companyObj->getCompaniesCount($conditions,$params);
    return $count ;
}

function getCompaniesCountFromWordAndTag($tag){
    $params = array();
    $companyObj = new Company();
    $conditions = null;
    $conditions .= ' AND companies.status = :status AND ( (plSafeChars(companies.keywords) LIKE "%'.$tag.'%") OR (companies.keywords LIKE "%'.$tag.'%") OR (plSafeChars(companies.name) LIKE "%'.$tag.'%") OR (companies.name LIKE "%'.$tag.'%")) ';
    $params += [':status'=>1];
    $count = $companyObj->getCompaniesCount($conditions,$params);
    return $count ;
}


function getCompaniesCount($city){
    $companyObj = new Company();
    $conditions = null;
    $params = array();
    if($city!=null){
        $conditions .= ' AND plSafeChars(companies.city)=:city ';
        $params += [':city'=>$city];
    }
    $conditions .= ' AND companies.status = :status';
    $params += [':status'=>1];
    $count = $companyObj->getCompaniesCount($conditions,$params);
    return $count;
}


function showCompanyPagination($city,$page,$limit){
    if(isset($_GET['category'])){
        $category = htmlspecialchars($_GET['category']);
    }
    if(isset($_GET['m'])){
        $m = htmlspecialchars($_GET['m']);
    }

    $link='/firmy'.(!empty($category)?'/'.$category:'').(!empty($m)?'/'.$m:'');

    $pagesCount = ceil(getCompaniesCount($city) / $limit);
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
                        <'.($currentPage === (int)$i ? 'span':'a').' class="pagination__link '.($currentPage === (int)$i ? 'active':'').'"  '.($currentPage === (int)$i ? '':'href="'.$link.'/'.$i.'/"').'  title="Przejdź na stronę">'.$i.'</'.($currentPage === (int)$i ? 'span':'a').'>
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


function showCompanyPaginationFromCategory($city,$category,$page,$limit){
    if(isset($_GET['category'])){
        $category = htmlspecialchars($_GET['category']);
    }
    if(isset($_GET['m'])){
        $m = htmlspecialchars($_GET['m']);
    }

    $link='/firmy'.(!empty($category)?'/'.$category:'').(!empty($m)?'/'.$m:'');


    $pagesCount = ceil(getCompaniesCountFromCategory($city,$category) / $limit);
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
                        <'.($currentPage === (int)$i ? 'span':'a').' class="pagination__link '.($currentPage === (int)$i ? 'active':'').'"  '.($currentPage === (int)$i ? '':'href="'.$link.'/'.$i.'/"').'  title="Przejdź na stronę">'.$i.'</'.($currentPage === (int)$i ? 'span':'a').'>
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


function showCompanyPaginationFromTag($tag,$page,$limit){


    $link='/tag/firmy'.(!empty($tag)?'/'.$tag:'');

    $pagesLimit = 5;
    $pagesCount = ceil(getCompaniesCountFromTag($tag) / $limit);
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
                        <'.($currentPage === (int)$i ? 'span':'a').' class="pagination__link '.($currentPage === (int)$i ? 'active':'').'"  '.($currentPage === (int)$i ? '':'href="'.$link.'/'.$i.'/"').'  title="Przejdź na stronę">'.$i.'</'.($currentPage === (int)$i ? 'span':'a').'>
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


function showCompanyPaginationFromWordAndTag($tag,$page,$limit){


    $link='/szukaj/firmy'.(!empty($tag)?'/'.$tag:'');

    $pagesLimit = 5;
    $pagesCount = ceil(getCompaniesCountFromWordAndTag($tag) / $limit);
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
                        <'.($currentPage === (int)$i ? 'span':'a').' class="pagination__link '.($currentPage === (int)$i ? 'active':'').'"  '.($currentPage === (int)$i ? '':'href="'.$link.'/'.$i.'/"').'  title="Przejdź na stronę">'.$i.'</'.($currentPage === (int)$i ? 'span':'a').'>
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

function addCompanyView($companyId){
    $companyObj = new Company();
    $companyObj->addView($companyId);
}


function terminateCompany($companyId){
    $companyObj = new Company();
    $set = 'status = :status, terminate_start = :today ';
    $params = array(':status'=>2,':today'=>date('Y-m-d H:i:s'));
    $terminateCompany = $companyObj->updateCompany($companyId,$set,$params);
    if($terminateCompany){
        return true;
    }else{
        return false;
    }
}

function activateCompany($companyId){
    $companyObj = new Company();
    $set = 'status = :status, terminate_start = :today ';
    $params = array(':status'=>0,':today'=>null);
    $terminateCompany = $companyObj->updateCompany($companyId,$set,$params);
    if($terminateCompany){
        return true;
    }else{
        return false;
    }
}

function deleteCompany($companyId){
    $companyObj = new Company();
    $result = $companyObj->deleteCompany($companyId);
    if($result>0){
        return true;
    }else{
        return false;
    }
}

function setCity_Company($city,$companyId){
    $city = htmlspecialchars($city);
    $companyObj = new Company();
    $set = 'city = :city ';
    $params = array(':city'=>$city);
    $cityCompany = $companyObj->updateCompany($companyId,$set,$params);
    if($cityCompany){
        return true;
    }else{
        return false;
    }
}

function setAdress_Company($adress,$companyId){
    $adress = htmlspecialchars($adress);
    $companyObj = new Company();
    $set = 'adress = :adress ';
    $params = array(':adress'=>$adress);
    $companyChanges = $companyObj->updateCompany($companyId,$set,$params);
    if($companyChanges){
        return true;
    }else{
        return false;
    }
}

function setWebsite_Company($website,$companyId){
    $website = htmlspecialchars($website);
    $companyObj = new Company();
    $set = 'website = :website ';
    $params = array(':website'=>$website);
    $companyChanges = $companyObj->updateCompany($companyId,$set,$params);
    if($companyChanges){
        return true;
    }else{
        return false;
    }
}


function setPhone_Company($phone,$companyId){
    $phone = htmlspecialchars($phone);
    $companyObj = new Company();
    $set = ' phone = :phone ';
    $params = array(':phone'=>$phone);

    $companyChanges = $companyObj->updateCompany($companyId,$set,$params);
    if($companyChanges){
        return true;
    }else{
        return false;
    }
}


function deleteLogo_Company($companyId){
    $companyObj = new Company();
    $set = ' logo = :logo ';
    $params = array(':logo'=>null);
    $companyChanges = $companyObj->updateCompany($companyId,$set,$params);
    if($companyChanges){
        return true;
    }else{
        return false;
    }
}

function setFoundationYear_Company($year, $companyId){
    $year = (int)$year;
    $companyObj = new Company();
    $set = 'foundation_year = :year ';
    $params = array(':year'=>$year);
    $companyChanges = $companyObj->updateCompany($companyId,$set,$params);
    if($companyChanges){
        return true;
    }else{
        return false;
    }
}


function setShortDescription_Company($description, $companyId){
    $description = htmlspecialchars($description);
    $companyObj = new Company();
    $set = 'short_description = :description ';
    $params = array(':description'=>$description);
    $companyChanges = $companyObj->updateCompany($companyId,$set,$params);
    if($companyChanges){
        return true;
    }else{
        return false;
    }
}


function setLongDescription_Company($description, $companyId){
    $description = htmlspecialchars($description);
    $companyObj = new Company();
    $set = 'description = :description ';
    $params = array(':description'=>$description);
    $companyChanges = $companyObj->updateCompany($companyId,$set,$params);
    if($companyChanges){
        return true;
    }else{
        return false;
    }
}


function setShowDescription_Company($show, $companyId){
    $show = (int)$show;
    $companyObj = new Company();
    $set = 'show_description = :showdescription ';
    $params = array(':showdescription'=>$show);
    $companyChanges = $companyObj->updateCompany($companyId,$set,$params);
    if($companyChanges){
        return true;
    }else{
        return false;
    }
}

function setCategory_Company($categoryId, $companyId){
    $categoryId = (int)$categoryId;
    $companyObj = new Company();
    $set = 'category_id = :categoryId ';
    $params = array(':categoryId'=>$categoryId);
    $companyChanges = $companyObj->updateCompany($companyId,$set,$params);
    if($companyChanges){
        return true;
    }else{
        return false;
    }
}

function setTags_Company($tags, $companyId){
    $i = 0;
    $tagList = null;
    foreach($tags as $tag){
        if($i != 3){
            $i++;
            $tagList.=$tag;
            if($i<3){
                $tagList.=',';
            }
        }
    }
    $tagList = rtrim($tagList, ',');
    $companyObj = new Company();
    $set = 'keywords = :keywords ';
    $params = array(':keywords'=>$tagList);
    $companyChanges = $companyObj->updateCompany($companyId,$set,$params);
    if($companyChanges){
        return true;
    }else{
        return false;
    }
}

function setHours_Company($days,$openHours,$closeHours,$companyId){
    $daysOpen = $days.'_o';
    $daysClose = $days.'_z';

    $companyObj = new Company();
    $set = $daysOpen.' = :openHours, '.$daysClose.' = :closeHours ';
    $params = array(':openHours'=>$openHours, ':closeHours'=>$closeHours);
    $companyChanges = $companyObj->updateCompany($companyId,$set,$params);
    if($companyChanges){
        return true;
    }else{
        return false;
    }
}

function setHoursVisibility_Company($show,$companyId){
    $companyObj = new Company();
    $set = ' show_hours = :show';
    $params = array(':show'=>$show);
    $companyChanges = $companyObj->updateCompany($companyId,$set,$params);
    if($companyChanges){
        return true;
    }else{
        return false;
    }
}