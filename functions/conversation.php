<?php
require_once($_SERVER['DOCUMENT_ROOT'].'/app/init/autoloader.php');

function createConversation($senderId,$recipentId,$senderType,$recipentType){
    $conversationObj = new Conversation();
    $conversationId = $conversationObj->createConversation($senderId,$recipentId,$senderType,$recipentType);
    return $conversationId;
}


function addMessage($conversationId,$userId,$userType,$message){

    if(!canAddNewContent($userId)){
        echo whenCanAddNewContent($userId);
        return false;
    }


    $conversationObj = new Conversation();
    $add = $conversationObj->addMessage($conversationId,$userId,$userType,$message);
    $accountsArray = getUserAccounts($_SESSION['user']->id)  ;
    $conversationsArray  = getConversation($conversationId,$_SESSION['user']->id);
    foreach($conversationsArray as $conversation){
        foreach($accountsArray as $account){
            if(($conversation['user_sender'] === $account['id'] && $conversation['sender_type'] === $account['type']) || ($conversation['user_recipent'] === $account['id'] && $conversation['recipent_type'] === $account['type'])){
                $msgUserId = $account['id'];
                $msgUserType = $account['type'];
                if($conversation['user_sender'] === $msgUserId && $conversation['sender_type'] === $msgUserType){
                    if($conversation['user_sender'] === $conversation['user_recipent']){
                        setSender_Status(1,$conversationId);
                        setRecipent_Status(1,$conversationId);
                    }else{
                        setSender_Status(1,$conversationId);
                        setRecipent_Status(0,$conversationId);
                    }

                }elseif($conversation['user_recipent'] === $msgUserId && $conversation['recipent_type'] === $msgUserType){
                    setRecipent_Status(1,$conversationId);
                    setSender_Status(0,$conversationId);
                }
            }
        }
    }
    updateDateOfConversation($conversationId);
    return $add;
}

function updateDateOfConversation($conversationId){
    $obj = new Conversation();
    $set = ' conversations.created_date = :dateNow';
    $params = array(':dateNow'=>date('Y-m-d H:i:s'));
    $obj->updateConversation($conversationId,$set,$params);
}

function getUnreadMessages($userId){
    $accountsArray = getUserAccounts($userId);
    $iUnread = 0;
    foreach($accountsArray as $acc){
        $conversationUnread = getConversations($acc['id'], $acc['type'], 0, 'nieprzeczytane');
     foreach($conversationUnread as $conv){
         $conversationUnreadArray[$iUnread] = $conv;
         $iUnread++;
     }
    }
    return $conversationUnreadArray;
}

function getReadMessages($userId){
    $accountsArray = getUserAccounts($userId);
    $iRead = 0;
    foreach($accountsArray as $acc){
        $conversationUnread = getConversations($acc['id'], $acc['type'], 1, 'przeczytane');
        foreach($conversationUnread as $conv){
            $conversationUnreadArray[$iRead] = $conv;
            $iRead++;
        }
    }
    return $conversationUnreadArray;
}

function getSendMessages($userId){
    $accountsArray = getUserAccounts($userId);
    $iSend = 0;
    foreach($accountsArray as $acc){
        $conversationUnread = getConversations($acc['id'], $acc['type'], 0, 'wyslane');
        foreach($conversationUnread as $conv){
            $conversationUnreadArray[$iSend] = $conv;
            $iSend++;
        }
    }
    return $conversationUnreadArray;
}

function getConversationByStatusAndType($userId,$status,$msgType){
    $accountsArray = getUserAccounts($userId);
    $i = 0;
    foreach ($accountsArray as $acc) {
        $conversation = getConversations($acc['id'], $acc['type'], $status, $msgType);
            foreach($conversation as $conv){
                $conversationArray[$i] = $conv;
                $i++;
            }

    }
    $keys = array_column($conversationArray, 'created_date');
    array_multisort($keys, SORT_DESC, $conversationArray);
    return $conversationArray;
}

function setSender_Status($status,$conversationId){
    $conversationObj = new Conversation();
    $set = ' sender_status = :status';
    $params = array(':status'=>$status);
    $update = $conversationObj->updateConversation($conversationId,$set,$params);
    return $update;
}

function setRecipent_Status($status,$conversationId){
    $conversationObj = new Conversation();
    $set = ' recipent_status = :status';
    $params = array(':status'=>$status);
    $update = $conversationObj->updateConversation($conversationId,$set,$params);
    return $update;
}

function getConversations($userId,$userType,$messageStatus,$messageType){
    $conversationObj = new Conversation();
    $result = $conversationObj->getUserConversations($userId,$userType,$messageStatus,$messageType);
    return($result);
}

function getConversation($conversationId,$userId){
    $conversationObj = new Conversation();
    $result = $conversationObj->getConversation($conversationId);

    $userCompanies = getUserCompanies($userId);
    $accountsArray = array();
    $i = 0;
    foreach ($userCompanies as $t) {
        $accountsArray[$i] = array('id' => $t['companyId'], 'type' => 'company');
        $i++;
    }
    $accountsArrayCount = intval(count($accountsArray)) + 1;
    $accountsArray[$accountsArrayCount] = array('id' => $userId, 'type' => 'user');
    $resultSender = $result[0]['user_sender'];
    $senderType = $result[0]['sender_type'];

    $resultRecipent = $result[0]['user_recipent'];
    $recipentType = $result[0]['recipent_type'];
    $founded = 0;
    foreach($accountsArray as $account){
        if(($resultSender === $account['id'] && $senderType === $account['type']) || ($resultRecipent === $account['id'] && $recipentType === $account['type'])){
            $founded = 1;
        }
    }

    if($founded){
        return $result;
    }else{
        return null;
    }
}

function getMessages($conversationId){
        $conversationObj = new Conversation();
        $result = $conversationObj->getMessages($conversationId);
        return $result;
}

function isExistConversation($userA,$Atype,$userB,$Btype){
    $conversationObj = new Conversation();
    $result = $conversationObj->isExistConversation($userA,$Atype,$userB,$Btype);
    return $result[0]['id'];
}

function deleteOlderMessage(){
    $obj = new Conversation();
    $result = $obj->deleteOlderMessage();
    return $result;
}