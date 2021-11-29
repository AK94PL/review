<?php

function selectAds($place,$categoryId){
    $condtions = ' status = :status AND (views_limit >= views OR views_limit IS NULL OR views_limit = 0) AND place = :place';
    $params = array(':status'=>1, ':place'=>$place);
    if((int)($categoryId) > 0){
        $condtions.=' AND category_id = :categoryId';
        $params += [':categoryId'=>$categoryId];

    }else{
        $condtions.=' AND category_id IS NULL ';
    }
    $adsObj = new Ads();
    $result = $adsObj->getAds($condtions,$params);
    return $result;
}

function getAds($place,$categoryId){
    $result = selectAds($place,$categoryId);
    $viewsLimit = (int)($result[0]['views_limit']);
    $views = (int)$result[0]['views'];
    $status = (int)$result[0]['status'];
    $strTime = strtotime('0000-00-00 00:00:00');
    if(is_null($result[0]['end_date']) || strtotime($result[0]['end_date']) === $strTime){
        if(!empty($result[0]['code']) && $status === 1){
            $ad = $result;
        }else{
            $ad = selectAds($place,0);
        }
    }elseif(strtotime($result[0]['end_date']) > strtotime(date('Y-m-d H:i:s'))){
        if(!empty($result[0]['code'])){
            $ad = $result;
        }else{
            $ad = selectAds($place,0);
        }
    }else{
        $ad = selectAds($place,0);
    }
    if(((int)$ad[0]['views_limit']>=(int)$ad[0]['views']) || ((int)($ad[0]['views_limit']) === 0)){
        addView_Ad($ad[0]['id']);
    }
    return $ad;
}

function addView_Ad($adId){
    $obj = new Ads();
    $result = $obj->addView($adId);
    return $result;
}

?>