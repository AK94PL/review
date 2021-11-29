<?php


class Admin_Forum extends Database
{
    public function getTheards($conditions,$params){
        $theards = $this->select(' DISTINCT forum_theards.id as theardId, forum_theards.subject, plSafeChars(forum_theards.subject) as subjectPL, forum_theards.content, forum_theards.tags, forum_theards.category_id, forum_theards.views, forum_theards.rate, forum_theards.created_date, forum_theards.user_id, forum_theards.user_type, forum_theards.status, forum_theards.verificated, forum_theards.promoted, forum_theards.pinned, categories.id, categories.name, categories.name as categoryName,  plSafeChars(categories.name) as categoryNamePL ','forum_theards, categories, users',' forum_theards.category_id = categories.id  '.$conditions,$params);
        return $theards->fetchAll();
    }

    public function getReplies($conditions, $params){
        $replies = $this->select('forum_replies.*, forum_replies.id as replyId','forum_replies',' forum_replies.theard_id IS NOT NULL AND '.$conditions,$params);
        return $replies->fetchAll();
    }


    public function deleteTheard($id){
        $params = array(':id'=>$id);
        $this->delete('forum_replies_rates,forum_replies','forum_replies_rates.reply_id = forum_replies.id AND forum_replies.theard_id = :id ',$params);
        $this->delete('forum_replies','forum_replies.theard_id = :id ',$params);
        $this->delete('forum_theards_rates','forum_theards_rates.theard_id = :id ',$params);
        $theard = $this->delete('forum_theards','forum_theards.id = :id ',$params);
        return $theard;
    }

    public function turnOff_Theard($id){
        $params = array(':id'=>$id, ':status'=>2);
        $result = $this->update('forum_theards', 'status = :status',' forum_theards.id = :id ',$params);
        return $result;
    }

    public function update_Theard($theardId,$title,$content,$categoryId,$tags,$status,$verificated,$promoted,$pinned){
        $params = array(':id'=>$theardId, ':title'=>$title, ':content'=>$content, ':categoryId'=>$categoryId, ':tags'=>$tags, ':status'=>$status, ':verificated'=>$verificated, ':promoted'=>$promoted,':pinned'=>$pinned);
        $result = $this->update('forum_theards', '  subject = :title, content = :content, tags = :tags, category_id = :categoryId, status = :status, verificated = :verificated, promoted = :promoted, pinned = :pinned',' forum_theards.id = :id ',$params);
        return $result;
    }

    public function update_Reply($replyId,$content,$status){
        $params = array(':id'=>$replyId, ':content'=>$content,':status'=>$status);
        $result = $this->update('forum_replies', '  content = :content, status = :status',' forum_replies.id = :id ',$params);
        return $result;
    }
}