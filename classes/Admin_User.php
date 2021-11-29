<?php


class Admin_User extends Database
{
    public function getUsers($conditions,$params){
        $result = $this->select('users.*, users.id as userId, plSafeChars(users.login) as loginPL','users',$conditions,$params);

        return $result->fetchAll();
    }

    public function updateUser($companyId,$set,$params=array()){
        try{
            $params += [':id'=>$companyId];
            $result = $this->update('users',$set,'users.id = :id ',$params);
            return $result;
        }catch(PDOException $e){
            echo $e->getCode();
            return false;
        }

    }


    public function deleteUser($id){
        $params = array(':id'=>$id);
        $result = $this->delete('users','users.id = :id ',$params);
        return $result;
    }

}