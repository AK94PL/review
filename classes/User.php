<?php
require_once ($_SERVER['DOCUMENT_ROOT'].'/app/classes/Database.php');
class User {

    public $isAuthenticated;
    public $id;
    public $login;
    public $loginPL;
    public $email;
    public $group_id;
    public $title;
    public $phone;
    public $show_phone;
    public $last_login;
    public $avatar;
    public $default_author;
    public $type;

    private $status;
    private $password;

    public function __construct(){
        if((int)$this->group_id === 1){
            $this->type = 'user';
        }elseif((int)$this->group_id === 2){
            $this->type = 'mod';
        }elseif((int)$this->group_id === 3){
            $this->type = 'admin';

        }elseif((int)$this->group_id === 4){
            $this->type = 'super-admin';

        }elseif((int)$this->group_id === 5){
            $this->type = 'sponsor';
        }elseif((int)$this->group_id === 6){
            $this->type = 'super-admin';
        }
    }

    public function authUser($email,$password){

        $pdo = new Database();

        $stmt = $pdo->select('password','users','email = :email AND status > 0',array(':email'=>$email));

        $result = $stmt->fetch();

        if(password_verify($password,$result['password'])){

            return true;

        }else{

            return false;

        }

    }



    public function getUserId($email){

        $pdo = new Database();

        $result = $pdo->select('id','users','email = :email', array(':email'=>$email));

        return $result->fetch();

    }

    public function getUserAvatar($id){
        $pdo = new Database();
        $result = $pdo->select('avatar','users','users.id = :id', array(':id'=>$id));
        return $result->fetchAll();
    }

    public function getUserByLoginId($login,$uid){

        $pdo = new Database();

        $params = array(':login'=>$login,':id'=>(int)$uid);

        $result = $pdo->select('users.id as userId,users.login, plSafeChars(users.login) as loginPL, users.group_id,users.title,users.phone,users.show_phone, users.last_login,users.avatar,users.points, users.views, users.status,users.created_date,users_groups.id, users_groups.name, users_groups.title ','users, users_groups','(users.login = :login OR plSafeChars(users.login) = :login) AND users.id = :id AND users.group_id = users_groups.id ',$params);

        return $result->fetchAll();

    }

    public function getUserByEmail($email){

        $pdo = new Database();

        $result = $pdo->select('users.*, plSafeChars(users.login) as loginPL ','users','email = :email', array(':email'=>$email));

        return $result->fetchObject('User');

    }

    public function getBlockadeData($uid){

        $pdo = new Database();

        $result = $pdo->select('users.blockade_end, users.blockade_counter ','users','users.id = :uid', array(':uid'=>$uid));

        return $result->fetchAll();

    }

    public function emailReserved($email){

        $pdo = new Database();

        $stmt = $pdo->select('id','users','email = :email', array(':email'=>$email));

        $result = $stmt->fetchAll();

        if(count($result) > 0){

            return true;

        }else{

            return false;

        }

    }

    public function getUserContent($userId, $userType, $contentType){
        if($contentType === 'advertisements'){
            $conditions = ' AND NOW() BETWEEN  advertisements.created_date AND advertisements.end_date AND advertisements.status = 1';
        }elseif($contentType === 'events'){
            $conditions = ' AND events.status = 1 AND date_start <= NOW() AND NOW() <= date_end';
        }elseif($contentType === 'articles'){
            $conditions = ' AND articles.status = 1 ';
        }elseif($contentType === 'forum_theards'){
            $conditions = '  AND forum_theards.status = 1 ';
        }

        $pdo = new Database();

        $params = array(':uid'=>$userId, ':userType'=>$userType);

        $stmt = $pdo->select($contentType.'.*',$contentType,$contentType.'.user_id = :uid AND '.$contentType.'.user_type = :userType '.$conditions,$params);

        return $stmt->fetchAll();

    }

    public function loginReserved($login){

        $pdo = new Database();

        $stmt = $pdo->select('id','users','login = :login', array(':login'=>$login));

        $result = $stmt->fetchAll();

        if(count($result) > 0){

            return true;

        }else{

            return false;

        }

    }


    public function addUser($login,$password,$email){

        $pdo = new Database();

        $login = clean(htmlspecialchars($login),'register');

        $password = htmlspecialchars($password);

        $email = htmlspecialchars($email);



        $values = array($login,$password,$email,1,"Użytkownik",0);



        try{

            $userId = $pdo->insert('users','login,password,email,group_id,title,status',$values);

            return $userId;

        }catch(PDOException $e){

            echo $e->getCode();

            return false;

        }



    }


    public function addView($userId){

        $pdo = new Database();

        $params = array(':id'=>$userId);

        try{

            $result = $pdo->update('users', 'views = views + 1',' users.id = :id ',$params);

            return $result;

        }catch(PDOException $e){

            echo $e->getCode();

            return false;

        }

    }

    public function addUserVisit($uid){

        $pdo = new Database();

        $params = array(':uid'=>$uid);

        try{

            $pdo->update('users',' last_login = now()',' users.id = :uid',$params);

        }catch(PDOException $e){

            echo $e->getCode();

            return null;

        }

    }




    public function getUserLastLogin($uid){

        $pdo = new Database();

        try{

            $params = array(':uid'=>$uid);

            $result = $pdo->select('last_login','users',' users.id = :uid ',$params);

            return $result->fetchAll();

        }catch(PDOException $e){

            echo $e->getCode();

            return null;

        }

    }


    public function getUserCreatedDate($uid){

        $pdo = new Database();

        try{

            $params = array(':uid'=>$uid);

            $result = $pdo->select('created_date','users',' users.id = :uid ',$params);

            return $result->fetchAll();

        }catch(PDOException $e){

            echo $e->getCode();

            return null;

        }

    }


    public function authorData($uid,$userType){

        $pdo = new Database();

        try{

            $params = array(':uid'=>$uid);

            if($userType === 'user'){

                $result = $pdo->select('users.*, plSafeChars(users.login) as loginPL','users','users.id = :uid ',$params);

            }elseif($userType === 'company'){

                $result = $pdo->select('companies.*, plSafeChars(companies.name) as namePL','companies','companies.id = :uid ',$params);

            }

            return $result->fetchAll();

        }catch(PDOException $e){

            echo $e->getCode();

            return null;

        }

    }



    public function updateUser($userId,$set,$params=array()){

        $pdo = new Database();

        try{

            $params += [':id'=>$userId];

            $result = $pdo->update('users',$set,'users.id = :id ',$params);

            updateUserData();

            return $result;

        }catch(PDOException $e){

            echo $e->getCode();

            return false;

        }
    }



}
    //EoC

?>