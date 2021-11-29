<?php


class Confirmation extends Database
{
    public function createConfirmation($userId,$task,$content = null){
        $permitted_chars = '0123456789abcdefghijklmnopqrstuvwxyz';

        $code = substr(str_shuffle($permitted_chars), 0, 10);
        $values = array($code,$task,$content,$userId);
        $this->insert('confirmations','code,task,content,user_id', $values);
        return $code;
    }

    public function deleteConfirmation($code){
        $params = array(':code'=>$code);
        return $this->delete('confirmations',' code=:code ',$params);
    }

    public function validateConfirmation($code){
        $params = array(':code'=>$code);
        return $this->select('user_id, task, content','confirmations',' confirmations.code = :code AND confirmations.created_date >= NOW() - INTERVAL 1 DAY ',$params);
    }

    public function activateUser($userId){
        $params = array(':userId'=>$userId,':status'=>1);
        return $this->update('users',' status=:status ',' id=:userId ',$params);
    }

    public function updatePassword($password,$userId){
        $params = array(':password'=>$password,':userId'=>$userId);
        return $this->update('users',' password=:password ',' id=:userId ',$params);
    }

    public function updateEmail($new_email,$userId){
        $params = array(':email'=>$new_email,':userId'=>$userId);
        return $this->update('users',' email=:email ',' id=:userId ',$params);
    }

    public function deleteUser($uid){
        $paramsUp = array(':uid'=>$uid, ':userType'=>'user',':userTypeNew'=>'deleted');
        $paramsDel = array(':uid'=>$uid);

        $this->update('articles',' user_type=:userTypeNew ',' articles.user_type=:userType AND articles.user_id = :uid ',$paramsUp);

        $this->update('events',' user_type=:userTypeNew ',' events.user_type=:userType AND events.user_id = :uid ',$paramsUp);
        $this->delete('favorites', 'user_id = :uid  ', $paramsDel);
        deleteUserAdv($uid);
        $this->delete('forum_theards_rates','user_id = :uid ',$paramsDel);
        $this->update('forum_theards',' user_type=:userTypeNew ',' forum_theards.user_type=:userType AND forum_theards.user_id = :uid ',$paramsUp);
        $this->delete('forum_replies_rates','user_id = :uid  ',$paramsDel);
        $this->update('forum_replies',' user_type=:userTypeNew ',' forum_replies.user_type=:userType AND forum_replies.user_id = :uid ',$paramsUp);
        $this->delete('conversations','(user_sender = :uid) OR (user_recipent = :uid)  ',$paramsDel);
        $this->delete('confirmations','user_id = :uid  ',$paramsDel);
        //
        $avatar = getAvatar_User($uid);
        $avatar_file = $avatar['avatar'];
        unlink($_SERVER['DOCUMENT_ROOT'].'/uploads/avatars/'.$avatar_file);
            if($this->delete('users','id = :uid',$paramsDel) > 0){
                $_SESSION['userInfo'] .= '
            <div class="alert alert--success">
                <span class="alert__content">
                  Pomyślnie usunięto Twoje konto.
                </span>
               <button class="alert-button" type="button" title="Ukryj">
                    <svg class="alert-button__svg" xmlns="http://www.w3.org/2000/svg"
                        xmlns:xlink="http://www.w3.org/1999/xlink" version="1.1" viewBox="0 0 24 24">
                        <path
                            d="M19,6.41L17.59,5L12,10.59L6.41,5L5,6.41L10.59,12L5,17.59L6.41,19L12,13.41L17.59,19L19,17.59L13.41,12L19,6.41Z" />
                    </svg>
                </button>
            </div>
            ';

        }else{
                $_SESSION['userInfo'] .= '
            <div class="alert alert--danger">
                <span class="alert__content">
                  Wystąpił błąd podczas usuwania konta. Skontaktuj się w tej sprawie z administracją.
                </span>
               <button class="alert-button" type="button" title="Ukryj">
                    <svg class="alert-button__svg" xmlns="http://www.w3.org/2000/svg"
                        xmlns:xlink="http://www.w3.org/1999/xlink" version="1.1" viewBox="0 0 24 24">
                        <path
                            d="M19,6.41L17.59,5L12,10.59L6.41,5L5,6.41L10.59,12L5,17.59L6.41,19L12,13.41L17.59,19L19,17.59L13.41,12L19,6.41Z" />
                    </svg>
                </button>
            </div>
            ';

            }
    }



}