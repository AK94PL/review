<?php


class Admin_Ads extends Database
{
    public function getAds($conditions, $params){
        $ads = $this->select(' ads.* ','ads',$conditions,$params);
        return $ads->fetchAll();
    }


    public function updateAd($adId,$content,$dateStart,$dateEnd,$limit,$status){
        $params = array(':adId'=>$adId,':content'=>$content,':dateStart'=>$dateStart,':dateEnd'=>$dateEnd,':limit'=>$limit,':status'=>$status);
        $result = $this->update('ads','code = :content, start_date = :dateStart, end_date = :dateEnd, views_limit = :limit, status = :status',' ads.id = :adId',$params);
        return $result;
    }
}