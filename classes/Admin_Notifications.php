<?php


class Admin_Notifications extends Database
{
    public function getNotifications($conditions,$params){
        $notifications = $this->select('admin_notifications.*','admin_notifications',$conditions,$params);
        return $notifications->fetchAll();
    }

    public function updateNotification($ntfId,$set,$params){
        $params += [':ntfId'=>$ntfId];
        $notification = $this->update('admin_notifications',$set,' admin_notifications.id =  :ntfId ',$params);
        return $notification;
    }

}