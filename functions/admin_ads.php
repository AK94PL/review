<?php


function getAd_Admin($place,$categoryId){
    $conditions = ' ads.place = :place ';
    $params = array(':place'=>$place);

    if(!isset($categoryId) || empty($categoryId)){
        $conditions.=' AND ads.category_id IS NULL';

    }else{
        $conditions.=' AND ads.category_id = :categoryId';
        $params  += [':categoryId'=>$categoryId];
    }
    $obj = new Admin_Ads();
    $result = $obj->getAds($conditions,$params);
    return $result;
}


function updateAd_Admin($adId,$content,$dateStart,$dateEnd,$limit,$status){
    $obj = new Admin_Ads();
    $result = $obj->updateAd($adId,$content,$dateStart,$dateEnd,$limit,$status);
    return $result;
}