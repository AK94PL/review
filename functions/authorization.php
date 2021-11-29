<?php
require_once($_SERVER['DOCUMENT_ROOT'].'/app/init/autoloader.php');

/* validate user datas */
function loginUser($email,$password){
    if(session_status() != PHP_SESSION_ACTIVE){
        session_start();
    }
    $email = htmlspecialchars($email);
    $password = htmlspecialchars($password);

    $userObj = new User();
    $user = $userObj->authUser($email,$password);
    if($user === TRUE){
        session_regenerate_id();
        $_SESSION['loggedin'] = true;
        $_SESSION['user'] = $userObj->getUserByEmail($email);
        header('Location: /');
    }else{
        $_SESSION['loggedin'] = false;
        $_SESSION['userInfo'] = ' 
        <div class="alert alert--danger">
                <span class="alert__content">
                    Nie udało się zalogować! Podałeś błędny email bądź hasło lub Twoje konto nie jest jeszcze aktywne.
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
        return false;
    }
}


/* check email exist in database */
function emailReserved($email){
    $userObj = new User();
    return $userObj->emailReserved($email);
}

/* check login exist in database */
function loginReserved($login){
    $userObj = new User();
    return $userObj->loginReserved($login);
}