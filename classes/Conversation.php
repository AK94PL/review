<?php

 class Conversation extends Database
 {

     public function createConversation($senderId,$recipentId,$senderType,$recipentType){
         $values = array($senderId,$senderType,$recipentId,$recipentType);
         $conversationId = $this->insert('conversations','user_sender,sender_type,user_recipent,recipent_type',$values);
         // ogranicznyik czasowy
         updateBlockadeEnd($senderId,$senderType);

         return $conversationId;
     }

     public function addMessage($conversationId,$userId,$userType,$message){
         $values = array($userId,$userType,$message,$conversationId);
         $conversationId = $this->insert('conversation_messages','user_sender,sender_type,content,conversation_id',$values);
         // ogranicznyik czasowy
         return $conversationId;
     }

     public function updateConversation($conversationId,$set,$params){
         $params += [':conversationId'=>$conversationId];
         $update = $this->update('conversations',$set,' conversations.id = :conversationId',$params);
         if((int)$update > 0){
             return true;
         }else{
             return false;
         }
     }

     public function isExistConversation($userA,$Atype,$userB,$Btype){
         $params = array(':userA'=>$userA, ':Atype'=>$Atype, ':userB'=>$userB, ':Btype'=> $Btype);
         $result = $this->select('id','conversations',' ( conversations.user_sender = :userA AND conversations.sender_type = :Atype AND conversations.user_recipent = :userB AND conversations.recipent_type = :Btype ) OR ( conversations.user_recipent = :userA AND conversations.recipent_type = :Atype AND conversations.user_sender = :userB AND conversations.sender_type = :Btype )',$params);

        return $result->fetchAll();
     }

     public function getUserConversations($userId,$userType,$messageStatus,$messagesType=null){
        if($messagesType === 'wyslane'){
            $params = array(':userId'=>$userId,':userType'=>$userType);
            $result = $this->select(' DISTINCT conversations.*','conversations',' (conversations.user_sender = :userId AND conversations.sender_type = :userType)  ORDER BY created_date DESC ',$params);
        }elseif($messagesType === 'nieprzeczytane'){
            $messageStatus = 0;
            $params = array(':userId'=>$userId,':userType'=>$userType,':messageStatus'=>$messageStatus);
            $result = $this->select(' DISTINCT conversations.*','conversations',' (conversations.user_sender = :userId AND conversations.sender_type = :userType AND conversations.sender_status = :messageStatus) OR (conversations.user_recipent = :userId AND conversations.recipent_type = :userType AND conversations.recipent_status = :messageStatus)  ORDER BY created_date DESC ',$params);
        }elseif($messagesType === 'przeczytane'){
            $messageStatus = 1;
            $params = array(':userId'=>$userId,':userType'=>$userType,':messageStatus'=>$messageStatus);
            $result = $this->select(' DISTINCT conversations.*','conversations',' (conversations.user_sender = :userId AND conversations.sender_type = :userType AND conversations.sender_status = :messageStatus) OR (conversations.user_recipent = :userId AND conversations.recipent_type = :userType AND conversations.recipent_status = :messageStatus)  ORDER BY created_date DESC ',$params);
        }
        return $result->fetchAll();
     }

     public function getConversation($convesationId){
         $params = array(':conversationId'=>$convesationId);
         $result = $this->select('conversations.*','conversations','conversations.id = :conversationId',$params);
         return $result->fetchAll();
     }


    public function getMessages($conversationId){
         $params = array(':conversationId'=>$conversationId);
         $result = $this->select('conversation_messages.*','conversation_messages','conversation_id = :conversationId ORDER BY conversation_messages.id DESC',$params);
         return $result->fetchAll();
    }

    public function deleteOlderMessage(){
//         count of days after which message is deleted
         $lengthOfLife = 60;
         $result = $this->delete('conversation_messages','conversation_messages.created_date < NOW() - INTERVAL '.$lengthOfLife.' DAY; ');
         return $result;
     }


 }
?>