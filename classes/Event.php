<?php


class Event extends Database
{

    public function addEvent($title,$content,$city,$categoryId,$tags,$dateStart,$timeStart,$dateEnd=null,$timeEnd=null,$userId,$userType,$picture,$status,$verificated){
        $dateTimeStart = $dateStart.' '.$timeStart;
        if(!empty($dateEnd) && !empty($timeEnd)){
            $dateTimeEnd = $dateEnd.' '.$timeEnd;
        }else{
            $dateTimeEnd = null;
        }
        $values = array($title,$content,$city,$tags,$categoryId,$dateTimeStart,$picture,$userId,$userType,$status,$verificated,0);
        $columns = 'subject,content,city,tags,category_id,date_start,image,user_id,user_type,status,verificated,promoted';
        if(!is_null($dateTimeEnd)){
            array_push($values,$dateTimeEnd);
            $columns.=',date_end';
        }
        $add = $this->insert('events',$columns,$values);
        updateBlockadeEnd($userId,$userType);
        return $add;
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

    public function getEvents($conditions,$params){

            $from = 'events, categories, users';
            $events = $this->select(' DISTINCT events.id, events.id as eventId, events.subject, plSafeChars(events.subject) as subjectPL, events.content, events.city as city, plSafeChars(events.city) as cityPL, events.tags, plSafeChars(events.tags) as tagsPL, events.category_id, events.date_start, events.date_end, events.image, events.views, events.user_id, events.user_type, events.status, events.created_date, events.promoted, events.revision, categories.id, categories.name, categories.name as categoryName, plSafeChars(categories.name) as categoryNamePL ', $from,' events.category_id = categories.id AND '.$conditions,$params);
            return $events->fetchAll();
    }

    public function getEventsCount($conditions,$params){
        $events = $this->select(' events.id, events.subject, plSafeChars(events.subject) as subjectPL, events.content, events.tags, events.category_id, events.date_start, events.date_end, events.image, events.views, events.user_id, events.status, categories.id, categories.name ','events, categories',' events.category_id = categories.id AND '.$conditions,$params);
        return count($events->fetchAll());
    }

    public function addEventView($eventId){
        $params = array(':eventId'=>$eventId);
        $result = $this->update('events',' views = views+1 ',' events.id = :eventId',$params);
        return $result;
    }
}
