<?php
require_once($_SERVER['DOCUMENT_ROOT'].'/app/init/autoloader.php');


function deleteUserAvatar(){
    $oldFile = $_SESSION['user']->avatar;
    $userObj = new User();
    $set = 'users.avatar = null';
    $uid = (int)$_SESSION['user']->id;
    if($userObj->updateUser($uid,$set)){
        unlink($_SERVER['DOCUMENT_ROOT'].'/uploads/avatars/'.$oldFile);
        return true;
    }else{
        return false;
    }
}

function setAvatar_User($fileName){
    $oldFile = $_SESSION['user']->avatar;
    $userObj = new User();
    $params =array(':fileName'=>$fileName);
    $set = 'users.avatar = :fileName';
    $uid = (int)$_SESSION['user']->id;
    if($userObj->updateUser($uid,$set,$params)){
        unlink($_SERVER['DOCUMENT_ROOT'].'/uploads/avatars/'.$oldFile);
        return true;
    }else{
        return false;
    }
}


function setDefault_User($id){
    $userObj = new User();
    $params =array(':default'=>$id);
    $set = 'users.default_author = :default';
    $uid = (int)$_SESSION['user']->id;
    if($userObj->updateUser($uid,$set,$params)){
        return true;
    }else{
        return false;
    }
}

function getUserAccounts($userId){
    $userCompanies = getUserCompanies($userId);
    $accountsArray = array();
    $i = 0;
    foreach ($userCompanies as $t) {
        $accountsArray[$i] = array('id' => $t['companyId'], 'type' => 'company');
        $i++;
    }
    $accountsArrayCount = intval(count($accountsArray)) + 1;
    $accountsArray[$accountsArrayCount] = array('id' => $userId, 'type' => 'user');
    return $accountsArray;
}

function getAvatar_User($uid){
    $userObj = new User();
    $avatar = $userObj->getUserAvatar($uid);
    return $avatar[0]['avatar'];
}

function setPhone_User($phone){
    if( ($phone != $_SESSION['user']->phone) && (is_numeric($phone)) ){
        $userObj = new User();
        $set = 'users.phone = :phone';
        $params = array(':phone'=>$phone);
        $uid = (int)$_SESSION['user']->id;
        return $userObj->updateUser($uid,$set,$params);
    }else{
        return true;
    }
}

function setShowPhone_User($is_on){
    if(($is_on != $_SESSION['user']->show_phone) && (is_numeric($is_on))){
        $userObj = new User();
        $set = 'users.show_phone = :is_on';
        $params = array(':is_on'=>$is_on);
        $uid = (int)$_SESSION['user']->id;
        return $userObj->updateUser($uid,$set,$params);
    }else{
        return true;
    }
}

function setPassword_User($password){
    if(strlen($password) < 7){
        return false;
    }
        $password_hash = password_hash($password,PASSWORD_DEFAULT);
        $userObj = new User();
        $set = 'users.password = :password';
        $params = array(':password'=>$password_hash);
        $uid = (int)$_SESSION['user']->id;
        return $userObj->updateUser($uid,$set,$params);
}

function setDarkMode($status){
    if($status){
        $darkMode = 'on';
    }else{
        $darkMode = 'off';
    }
    setcookie("DarkMode", $darkMode, 2147483647,"/");
    if(isset($_COOKIE['DarkMode'])){
        return TRUE;
    }else{
        return FALSE;
    }
}

function updateUserData(){
    $userObj = new User();
    $_SESSION['user'] = $userObj->getUserByEmail($_SESSION['user']->email);
    return $user = $_SESSION['user'];
}

function addUser($login,$passowrd,$email){
    $userObj = new User();
    return $userObj->addUser($login,$passowrd,$email);
}

function getUserId($email){
    $userObj = new User();
    return $userObj->getUserId($email);
}

function isLoggedIn(){
    if($_SESSION['user'] != null){
        return true;
    }else{
        return false;
    }
}

function getUserByLoginId($login,$uid){
$userObj = new User();
$user = $userObj->getUserByLoginId($login,$uid);
return $user;
}


function getUserContentCount($uid,$userType,$contentType){
    $userObj = new User();
    return count($userObj->getUserContent($uid,$userType,$contentType));
}

function addUserView($uid){
    $userObj = new User();
    $userObj->addView($uid);
}

//function isUserOnline($uid){
//    $userObj = new User();
//    $result = $userObj->getOnline($uid);
//    if(count($result) === 1){
//        return true;
//    }else{
//        return false;
//    }
//}

function addUserVisit(){
    $userObj = new User();
    $userObj->addUserVisit($_SESSION['user']->id);
}

function deleteOffline(){
    $userObj = new User();
    $userObj->deleteOfflineUser();
}


function getUserLastLogin($uid){
    $userObj = new User();
   $result = $userObj->getUserLastLogin($uid);
   return $result[0]['last_login'];
}

function getUserCreatedDate($uid){
    $userObj = new User();
   $result = $userObj->getUserCreatedDate($uid);
   return $result[0]['created_date'];
}

function getAuthorData($uid, $userType){
    $userObj = new User();
    $result = $userObj->authorData($uid,$userType);
    return $result;
}

function getAuthorLogin($uid,$userType){
    if($userType === 'deleted'){
        return 'Konto usunięte';
    }
    if($userType === 'deleted-company'){
        return 'Konto usunięte';
    }
    $result = getAuthorData($uid,$userType);
    if($userType === 'user'){
        return $result[0]['login'];
    }elseif($userType === 'company'){
        return $result[0]['name'];
    }
}


function getAuthorLoginPL($uid,$userType){
    if($userType === 'deleted'){
        return 'Konto usunięte';
    }
    if($userType === 'deleted-company'){
        return 'Konto usunięte';
    }
    $result = getAuthorData($uid,$userType);
    if($userType === 'user'){
        return $result[0]['loginPL'];
    }elseif($userType === 'company'){
        return $result[0]['namePL'];
    }
}

function updateBlockadeEnd($uid){
    //default blockade in min's
    $defaultTime_blockade = 2 ;

    $userObj = new User();
    //get actually data of blockade
    $blockadeData = $userObj->getBlockadeData($uid);
    $blockadeData = $blockadeData[0];

    $counter_Blockade = $blockadeData['blockade_counter'];
    $addedTime_Blockade = $defaultTime_blockade*($counter_Blockade>1?($counter_Blockade*$counter_Blockade):1);

    $dateTime = new DateTime();
    $dateTime->add(new DateInterval('PT'.$addedTime_Blockade.'M'));
    $newDate_blockade = $dateTime->format('Y-m-d H:i:s');

    $set = ' blockade_end = :datetime';
    $params = array(':datetime'=>$newDate_blockade);

    $userObj->updateUser($uid,$set,$params);
}


function increaseBloackadeCounter($userId,$userType){
    $userObj = new User();
    $params = array();
    //get ownerId if userType is company
    if($userType === 'company'){
        $userId = getCompanyOwnerId($userId);
    }
    $set = ' blockade_counter = blockade_counter+1';
    $userObj->updateUser($userId,$set,$params);
}

function resetBlockadeCounter($userId,$userType){
    $userObj = new User();
    $params = array();
    //get ownerId if userType is company
    if($userType === 'company'){
        $userId = getCompanyOwnerId($userId);
    }
    $set = ' blockade_counter = :reset';
    $params += [':reset'=>0];
    $userObj->updateUser($userId,$set,$params);
}

function canAddNewContent($userId){
    //userId should not been companyId
//        $userObj = new User();
//        $blockadeData = $userObj->getBlockadeData($userId);
//        $time = $blockadeData[0]['blockade_end'];
    //check if actually blockade end
//        if(strtotime($time) <= strtotime(date('Y-m-d H:i:s'))){
//            resetBlockadeCounter($userId,'user');
//            updateBlockadeEnd($userId,'user');
//            return true;
//         }else{
//            increaseBloackadeCounter($userId,'user');
//            updateBlockadeEnd($userId,'user');
//            return false;
//         }

    return true;
}

function whenCanAddNewContent(){
    $uid = $_SESSION['user']->id;
    $userObj = new User();
    $blockadeData = $userObj->getBlockadeData($uid);
    $endBlockade = $blockadeData[0]['blockade_end'];
    return $endBlockade;
}