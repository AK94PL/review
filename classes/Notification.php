<?php


class Notification extends Database
{
   public function createNotification($type,$contentId,$content,$sender_type,$sender){
       $params = array($type,$contentId,$content,$sender_type,$sender,0);
        $ntfId = $this->insert('admin_notifications','type,content_id,content,sender_type,sender,status',$params);
        return $ntfId;
   }

}