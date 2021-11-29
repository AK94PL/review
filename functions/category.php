<?php

function getCategories($conditions,$params){
    $categoryObj = new Category();
    return $categoryObj->getCategories($conditions,$params);
}


function getCategoryId($categoryName)
{
    $categoryObj = new Category();
    $condition = ' plSafeChars(categories.name) = :categoryName';
    $params = array(':categoryName' => $categoryName);
    $result = $categoryObj->getCategories($condition, $params);
    if (!empty($result)) {
        return $result[0]['id'];
    } else {
        return 0;
    }
}


function isCategoryExist($categoryName)
{
    $categoryObj = new Category();
    $condition = ' plSafeChars(categories.name) = :categoryName';
    $params = array(':categoryName' => $categoryName);
    $result = $categoryObj->getCategories($condition, $params);
    if (!empty($result)) {
        return true;
    } else {
        return false;
    }
}


function getCategoryNameBySafeName($categoryName)
{
    $categoryObj = new Category();
    $condition = ' plSafeChars(categories.name) = :categoryName';
    $params = array(':categoryName' => $categoryName);
    $result = $categoryObj->getCategories($condition, $params);
    if (!empty($result)) {
        return $result[0]['name'];
    } else {
        return false;
    }
}


function isCategoryExist_Id($categoryId)
{
    $categoryObj = new Category();
    $condition = ' categories.id = :categoryId';
    $params = array(':categoryId' => $categoryId);
    $result = $categoryObj->getCategories($condition, $params);
    if (!empty($result)) {
        return true;
    } else {
        return false;
    }
}


function getCategoryName_ById($categoryId)
{
    $categoryObj = new Category();
    $condition = ' categories.id = :categoryId';
    $params = array(':categoryId' => $categoryId);
    $result = $categoryObj->getCategories($condition, $params);
    if (!empty($result)) {
        return $result;
    } else {
        return null;
    }
}

function getElementsCountInCategory($categoryId,$elementType){
    $params = null;
    $categoryObj = new Category();
    $conditions = null;
    $score = 0;
    if($elementType === 'events'){
        if(intval($categoryId) === 1){
            $conditions = ' events.category_id = events.category_id ';
        }else{
            $conditions = ' events.category_id = :categoryId ';
        }
        $params = array(':categoryId'=>$categoryId);
        $result = $categoryObj->getElementsCountInCategory($elementType,$conditions,$params);
        $score = count($result);
    }elseif($elementType === 'articles'){
        if(intval($categoryId) === 1){
            $conditions = ' articles.category_id = articles.category_id ';
        }else{
            $conditions = ' articles.category_id = :categoryId ';
        }
        $params = array(':categoryId'=>$categoryId);
        $result = $categoryObj->getElementsCountInCategory($elementType,$conditions,$params);
        $score = count($result);
    }elseif($elementType === 'companies'){
        if(intval($categoryId) === 1){
            $conditions = ' companies.category_id = companies.category_id ';
        }else{
            $conditions = ' companies.category_id = :categoryId ';
        }
        $params = array(':categoryId'=>$categoryId);
        $result = $categoryObj->getElementsCountInCategory($elementType,$conditions,$params);
        $score = count($result);
    }elseif($elementType === 'advertisements'){
        if(intval($categoryId) === 1){
            $conditions = ' advertisements.category_id = advertisements.category_id ';
        }else{
            $conditions = ' advertisements.category_id = :categoryId ';
        }
        $params = array(':categoryId'=>$categoryId);
        $result = $categoryObj->getElementsCountInCategory($elementType,$conditions,$params);
        $score = count($result);
    }elseif($elementType === 'forum_theards'){
        if(intval($categoryId) === 1){
            $conditions = ' forum_theards.category_id = forum_theards.category_id ';
        }else{
            $conditions = ' forum_theards.category_id = :categoryId ';
        }
        $params = array(':categoryId'=>$categoryId);
        $result = $categoryObj->getElementsCountInCategory($elementType,$conditions,$params);
        $score = count($result);
    }elseif($elementType === 'kategoria'){
//        $result = $categoryObj->getElementsCountInCategory($elementType,$conditions,$params);
//        $score = count($result);
        if(intval($categoryId) === 1){
            $conditions = ' events.category_id = events.category_id ';
            $score += count($categoryObj->getElementsCountInCategory('events', $conditions, $params));
        }else{
            $conditions = ' events.category_id = :categoryId ';
            $params = array(':categoryId'=>$categoryId);
            $score += count($categoryObj->getElementsCountInCategory('events', $conditions, $params));
        }

        if(intval($categoryId) === 1){
            $conditions = ' articles.category_id = articles.category_id ';
            $score += count($categoryObj->getElementsCountInCategory('articles', $conditions, $params));
        }else{
            $conditions = ' articles.category_id = :categoryId ';
            $params = array(':categoryId'=>$categoryId);
            $score += count($categoryObj->getElementsCountInCategory('articles', $conditions, $params));
        }

        if(intval($categoryId) === 1){
            $conditions = ' companies.category_id = companies.category_id ';
            $score += count($categoryObj->getElementsCountInCategory('companies', $conditions, $params));
        }else{
            $conditions = ' companies.category_id = :categoryId ';
            $params = array(':categoryId'=>$categoryId);
            $score += count($categoryObj->getElementsCountInCategory('companies', $conditions, $params));
        }

        if(intval($categoryId) === 1){
            $conditions = ' advertisements.category_id = advertisements.category_id ';
            $score += count($categoryObj->getElementsCountInCategory('advertisements', $conditions, $params));
        }else{
            $conditions = ' advertisements.category_id = :categoryId ';
            $params = array(':categoryId'=>$categoryId);
            $score += count($categoryObj->getElementsCountInCategory('advertisements', $conditions, $params));
        }

        if(intval($categoryId) === 1){
            $conditions = ' forum_theards.category_id = forum_theards.category_id ';
            $score += count($categoryObj->getElementsCountInCategory('forum_theards', $conditions, $params));
        }else{
            $conditions = ' forum_theards.category_id = :categoryId ';
            $params = array(':categoryId'=>$categoryId);
            $score += count($categoryObj->getElementsCountInCategory('forum_theards', $conditions, $params));
        }

    }

    return $score;
}

