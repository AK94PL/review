<?php


function getAllUsers_Admin($login,$groupId,$dateSort,$statusSort){
    $page = (isset($_GET['p'])?(int)htmlspecialchars($_GET['p']):1);
    $limit = 20;
    $from = ($page * $limit) - $limit;

    $conditions = null;

    $params = array();
    $range = $from.','.$limit;


        if($groupId === 0){
            $conditions.='  users.group_id =  users.group_id';
        }else{
            $conditions.='  users.group_id = :groupId ';
            $params += [':groupId'=>$groupId];
        }


    if($statusSort!=3){
        $conditions.=' AND users.status = :status';
        $params += [':status'=>$statusSort];
    }

    if(!empty($login)){
        $conditions.=' AND (users.login LIKE "%'.$login.'%" OR users.id = :login)';
        $params += [':login'=>$login];
    }

    $conditions .= ' ORDER BY users.created_date '.(!empty($dateSort)?$dateSort:'DESC').' LIMIT '.$range;

    $companyObj = new Admin_User();

    $results = $companyObj->getUsers($conditions,$params);
    return $results;
}

function getAllUsers_Admin_Count($login,$groupId,$dateSort,$statusSort){


    $conditions = null;

    $params = array();



    if($groupId === 0){
        $conditions.='  users.group_id =  users.group_id';
    }else{
        $conditions.='  users.group_id = :groupId ';
        $params += [':groupId'=>$groupId];
    }


    if($statusSort!=3){
        $conditions.=' AND users.status = :status';
        $params += [':status'=>$statusSort];
    }

    if(!empty($login)){
        $conditions.=' AND users.login LIKE "%'.$login.'%"';
    }


    $companyObj = new Admin_User();

    $results = $companyObj->getUsers($conditions,$params);
    return count($results);
}

function getUsersPages_Admin($title,$categoryId,$dateSort,$statusSort){
    $limit = 20;
    $count = getAllUsers_Admin_Count($title,$categoryId,$dateSort,$statusSort);
    $pagesCount = ceil($count / $limit);
    return $pagesCount;
}



function getUserById_Admin($userId){
    $obj = new Admin_User();
    $conditions = "users.id = :uid";
    $params = array(':uid'=>$userId);
    $result = $obj->getUsers($conditions,$params);
    return $result;
}




function updateUser_Admin($id,$login,$group,$phone,$show_phone,$mail,$avatar){
    $set = ' users.login = :login, users.email = :email, users.phone = :phone, users.show_phone = :showPhone, users.group_id = :groupId, users.avatar = :avatar ';
    $params = array(':login'=>$login, ':email'=>$mail, ':phone'=>$phone, ':showPhone'=>$show_phone, ':groupId'=>$group, ':avatar'=>$avatar);
    $companyObj = new Admin_User();
    $companyChanges = $companyObj->updateUser($id,$set,$params);
    if($companyChanges){
        return true;
    }else{
        return false;
    }
}


function deleteUser_Admin($id){
    $obj = new Admin_User();
    $delete = $obj->deleteUser($id);
    return $delete;
}