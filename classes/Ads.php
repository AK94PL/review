<?php


class Ads extends Database
{
        public function getAds($conditions, $params){
            $ads = $this->select(' ads.* ','ads',$conditions,$params);
            return $ads->fetchAll();
    }


    public function addView($adId){
        $params = array(':adId'=>$adId);
        $result = $this->update('ads','views = views+1',' ads.id = :adId',$params);
        return $result;
    }
}