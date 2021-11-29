<?php


class Admin_Advertisement extends Database
{

    public function getAdvertisements($conditions,$params){
        $advertisements = $this->select(' DISTINCT advertisements.id as advId, advertisements.title, plSafeChars(advertisements.title) as titlePL, advertisements.content, advertisements.category_id, advertisements.tags, advertisements.type_id, advertisements.end_date, advertisements.city, plSafeChars(advertisements.city) as cityPL, advertisements.price, advertisements.additional_phone, advertisements.views, advertisements.user_id, advertisements.user_type, advertisements.created_date, advertisements.status, advertisements.verificated, advertisements.revision, advertisements.promoted, advertisements_types.id, advertisements_types.type, categories.id, categories.name as categoryName, plSafeChars(categories.name) as categoryNamePL ','advertisements, advertisements_types, categories, users',' advertisements.category_id = categories.id AND advertisements.type_id = advertisements_types.id AND '.$conditions,$params);
        return $advertisements->fetchAll();
    }

    public function updateAdvertisement($advertisementId,$title,$content,$category_id,$tags,$type,$city,$price,$phone,$status,$verificated,$revision,$promoted){
        $params = array(':advertId'=>$advertisementId,':title'=>$title,':content'=>$content,':categoryId'=>$category_id,':tags'=>$tags,':typeId'=>$type,':city'=>$city,':price'=>$price,':phone'=>$phone, ':status'=>$status, ':verificated'=>$verificated,':revision'=>$revision, ':promoted'=>$promoted);
        $result = $this->update('advertisements','title = :title, content = :content, category_id = :categoryId, tags = :tags, type_id = :typeId, city = :city, price = :price, additional_phone = :phone, status = :status, verificated = :verificated,revision = :revision, promoted = :promoted',' advertisements.id = :advertId',$params);
        return $result;
    }

    public function getAdvertisementTypes($conditions = null, $params = null){
        $advertisements = $this->select(' DISTINCT advertisements_types.* ','advertisements_types',$conditions,$params);
        return $advertisements->fetchAll();
    }


    public function deleteAdvert($id){
        $params = array(':id'=>$id);
        $gallery = $this->delete('advertisements_galleries','advertisements_galleries.advertisement_id = :id ',$params);
        $advert = $this->delete('advertisements','advertisements.id = :id ',$params);
        return $advert;
    }


}