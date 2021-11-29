<?php


class Admin_Event extends Database
{
    public function getEvents($conditions,$params){
        $events = $this->select(' DISTINCT events.id, events.id as eventId, events.subject, plSafeChars(events.subject) as subjectPL, events.content, events.city as city, plSafeChars(events.city) as cityPL, events.tags, events.category_id, events.date_start, events.date_end, events.image, events.views, events.user_id, events.user_type, events.status, events.verificated, events.revision, events.promoted, categories.id, categories.name, categories.name as categoryName, plSafeChars(categories.name) as categoryNamePL ','events, categories, users',' events.category_id = categories.id AND '.$conditions,$params);
        return $events->fetchAll();
    }



    public function updateEvent($eventId,$set,$params=array()){
        try{

            $params += [':id'=>$eventId];
            $result = $this->update('events',$set,'events.id = :id ',$params);

            return $result;

        }catch(PDOException $e){

            echo $e->getCode();

            return false;

        }
    }


    public function deleteEvent($id){
        $params = array(':id'=>$id);
        $event = $this->delete('events','events.id = :id ',$params);
        return $event;
    }

}