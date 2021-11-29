<?php
require_once($_SERVER['DOCUMENT_ROOT'].'/app/init/autoloader.php');

function createConfirmation($userId,$task,$content){
    $confirmationObj = new Confirmation();
    $code = $confirmationObj->createConfirmation($userId,$task,$content);
    return $code;
}

function validateConfirmation($code){
    $confirmationObj = new Confirmation();
    $result = $confirmationObj->validateConfirmation($code);
    $resultArr = $result->fetch();
    return $resultArr;
}

function executeTask($code){
    $err = 0;
    $confirmationObj = new Confirmation();
    $result = $confirmationObj->validateConfirmation($code);
    $resultArr = $result->fetch();
    switch($resultArr['task']){
        case 'register':
            if(!$confirmationObj->activateUser($resultArr['user_id'])){
                $err = 1;
            }
        break;
        case 'password':
            if(!$confirmationObj->updatePassword($resultArr['content'],$resultArr['user_id'])){
                $err = 1;
            }
        break;
        case 'email':
            if(!$confirmationObj->updateEmail($resultArr['content'],$resultArr['user_id'])){
                $err = 1;
            }
        break;
        case 'delete':
            if(!$confirmationObj->deleteUser($resultArr['user_id'])){
                $err = 1;
            }

    }

    if($err===0){
        return true;
    }else{
        return false;
    }
}

function deleteConfirmation($code){
    $confirmationObj = new Confirmation();
    return $confirmationObj->deleteConfirmation($code);
}