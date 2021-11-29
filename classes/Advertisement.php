<?php


class Advertisement extends Database
{

    public function updateAdvertisement($advertisementId,$title,$content,$category_id,$tags,$type,$city,$price,$phone,$user_id,$user_type,$verificated,$revision,$status){
            $params = array(':advertId'=>$advertisementId,':title'=>$title,':content'=>$content,':categoryId'=>$category_id,':tags'=>$tags,':typeId'=>$type,':city'=>$city,':price'=>$price,':phone'=>$phone,':userId'=>$user_id,':userType'=>$user_type,':verificated'=>$verificated,':revision'=>$revision,':status'=>$status);
        $result = $this->update('advertisements','title = :title, content = :content, category_id = :categoryId, tags = :tags, type_id = :typeId, city = :city, price = :price, additional_phone = :phone, user_id = :userId, user_type = :userType, verificated = :verificated, revision = :revision, status = :status',' advertisements.id = :advertId',$params);
        updateBlockadeEnd($user_id,$user_type);
        return $result;
    }

    public function addAdvertisement($title,$content,$category_id,$tags,$type,$city,$price,$phone,$user_id,$user_type,$status,$verificated){

        $end_date = date('Y-m-d', strtotime(' + 30 days'));
        $values = array($title,$content,$category_id,$tags,$type,$city,$price,$phone,0,$user_id,$user_type,$end_date,$status,$verificated);
        $addId = $this->insert('advertisements','title,content,category_id,tags,type_id,city,price,additional_phone,views,user_id,user_type,end_date,status,verificated',$values);
        updateBlockadeEnd($user_id,$user_type);
        return $addId;
    }

    public function getAdvertisementTypes($conditions = null, $params = null){
        $advertisements = $this->select(' DISTINCT advertisements_types.* ','advertisements_types',$conditions,$params);
        return $advertisements->fetchAll();
    }


    public function getAdvertisements($conditions,$params){
        $advertisements = $this->select(' DISTINCT advertisements.id as advId, advertisements.title, plSafeChars(advertisements.title) as titlePL, advertisements.content, advertisements.category_id, advertisements.tags, plSafeChars(advertisements.tags) as tagsPL, advertisements.type_id, advertisements.end_date, advertisements.city, plSafeChars(advertisements.city) as cityPL, advertisements.price, advertisements.additional_phone, advertisements.views, advertisements.user_id, advertisements.user_type, advertisements.created_date, advertisements.verificated, advertisements.promoted, advertisements.revision, advertisements_types.id, advertisements_types.type, categories.id, categories.name as categoryName, plSafeChars(categories.name) as categoryNamePL ','advertisements, advertisements_types, categories, users',' advertisements.category_id = categories.id AND advertisements.type_id = advertisements_types.id AND '.$conditions,$params);
        return $advertisements->fetchAll();
    }


    public function getAdvertisementsCount($conditions,$params){
        $advertisements = $this->select(' DISTINCT advertisements.id as advId, advertisements.title, advertisements.content, advertisements.category_id, advertisements.tags, advertisements.type_id, advertisements.city, advertisements.price, advertisements.additional_phone, advertisements.views, advertisements.user_id, advertisements.created_date, advertisements_types.id, advertisements_types.type, categories.id, categories.name ','advertisements, advertisements_types, categories',' advertisements.category_id = categories.id AND advertisements.type_id = advertisements_types.id AND '.$conditions,$params);
        return count($advertisements->fetchAll());
    }


    public function getGallery($conditions,$params){
        $gallery = $this->select(' advertisements_galleries.id, advertisements_galleries.advertisement_id, advertisements_galleries.order_place, advertisements_galleries.source','advertisements, advertisements_galleries',' advertisements_galleries.advertisement_id = advertisements.id AND '.$conditions,$params);
        return $gallery->fetchAll();
    }

    public function updatePhotoOrder($photoId, $set, $params){
        $params +=[':photoId'=>$photoId];
        $update = $this->update('advertisements_galleries',$set,' advertisements_galleries.id = :photoId',$params);
        return $update;
    }

    public function deleteGallery($conditions,$params){
        $gallery = $this->delete('advertisements_galleries',$conditions,$params);
        return $gallery;
    }

    public function addPhoto($filename,$order,$advertisementId){
        $values = array($advertisementId,$order,$filename);
        $add = $this->insert('advertisements_galleries','advertisement_id,order_place,source',$values);
        if($add > 0){
            return true;
        }else{
            return false;
        }
    }

    public function deletePhoto($photoId){
        $params = array(':photoId'=>$photoId);
        $delete = $this->delete('advertisements_galleries',' advertisements_galleries.id = :photoId',$params);
        return $delete;
    }

    public function addView($advertId){
        $params = array(':id'=>$advertId);
        $result = $this->update('advertisements', 'views = views + 1',' advertisements.id = :id ',$params);
        return $result;
    }

    public function isAdvertisementTypeExist($type){
        $params = array(':typeName'=>$type);
        $advertisementType = $this->select('id','advertisements_types','type = :typeName ',$params);
        if((int)$advertisementType->rowCount() > 0){
            return true;
        }else{
            return false;
        }
    }
}