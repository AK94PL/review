<?php


class Admin_Rotator extends Database
{

    public function getRotator($conditions,$params){
        $rotators = $this->select(' rotators.* ','rotators',$conditions,$params);
        return $rotators->fetchAll();
    }


    public function updateRotator($rotatorId,$content,$tags,$limit,$status){
        $params = array(':rotatorId'=>$rotatorId,':content'=>$content,':tags'=>$tags,':limit'=>$limit,':status'=>$status);
        $result = $this->update('rotators','content = :content, tags = :tags, views_limit = :limit, status = :status',' rotators.id = :rotatorId',$params);
        return $result;
    }

    public function deleteRotator($id){
        $params = array(':id'=>$id);
        $article = $this->delete('rotators','rotators.id = :id ',$params);
        return $article;
    }

    public function addRotator(){
        $params = array('0');
        $newRotator = $this->insert('rotators','status',$params);
        return $newRotator;
    }

}