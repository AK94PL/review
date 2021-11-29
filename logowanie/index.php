<?php
require_once ($_SERVER['DOCUMENT_ROOT'].'/app/init/autoloader.php');
require_once ($_SERVER['DOCUMENT_ROOT'].'/app/init/functions.php');
require_once ($_SERVER['DOCUMENT_ROOT'].'/app/functions/settings.php');
require_once($_SERVER['DOCUMENT_ROOT'].'/app/init/reCaptcha/autoload.php');

session_start();
$recaptchaSecret = getSettings('recaptcha-secret');


if(isset($_GET['c']) ){
    $_SESSION['loggedin'] = FALSE;
    $_SESSION = array();
    $code = htmlspecialchars($_GET['c']);
    $requestArray = validateConfirmation($code);
    $userId = $requestArray['user_id'];
    $taskType = $requestArray['task'];
    if($userId!=null){
        if(executeTask($code)){
            deleteConfirmation($code);
            switch($taskType){
                case "register":
                    $_SESSION['userInfo'] = ' 
        <div class="alert alert--success">
                <span class="alert__content">
                   Twoje konto zostało aktywowane!
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
                    break;
                case "password":
                    $_SESSION['userInfo'] = ' 
        <div class="alert alert--success">
                <span class="alert__content">
                   Twoje hasło zostało zmienione!
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
                    break;
            }

        }else{
            switch ($taskType){
                case "register":
                    $_SESSION['userInfo'] = ' 
        <div class="alert alert--danger">
                <span class="alert__content">
                    Wystąpił błąd podczas aktywacji Twojego kotna. Spróbuj jeszcze raz, a jeśli problem wystąpi ponownie, skontaktuj sie z administratorem.
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
                    break;
                case "password":
                    $_SESSION['userInfo'] = ' 
        <div class="alert alert--danger">
                <span class="alert__content">
                    Wystąpił błąd podczas zmiany Twojego hasła. Spróbuj jeszcze raz, a jeśli problem wystąpi ponownie, skontaktuj sie z administratorem.
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
                    break;
            }

        }
    }else{
        $_SESSION['userInfo'] = ' 
        <div class="alert alert--danger">
                <span class="alert__content">
                   Link potwierdzający nie istnieje lub wygasł. Pamiętaj że linki potwierdzające działają tylko przez <strong>48 godzin</strong> od ich wygenerowania.
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

if ($_SESSION['loggedin']) {
    header('Location: /loggedin.php');
}

if(isset($_POST['email'])) {
    if(isset($_POST['g-recaptcha-response']) ){

        $errors = array();      // array to hold validation errors
        $data = array();      // array to pass back data


// validate the variables ======================================================
// if any of these variables don't exist, add an error to our $errors array


        if (!isset($_POST['g-recaptcha-response']) && (isset($_POST['email']))) {
            $errors['reCaptcha'] = 'ReCaptcha jest wymagana';
        }else{
            $recaptcha = new \ReCaptcha\ReCaptcha($recaptchaSecret, new \ReCaptcha\RequestMethod\CurlPost());

// we validate the ReCaptcha field together with the user's IP address

            $response = $recaptcha->verify($_POST['g-recaptcha-response'], $_SERVER['REMOTE_ADDR']);

            if (!$response->isSuccess() && (isset($_POST['email']))) {
                $errors['reCaptcha'] = 'ReCaptcha jest wymagana';
                $_SESSION['userInfo'] = ' 
        <div class="alert alert--danger">
                <span class="alert__content">
                   reCaptcha jest niepoprawna.
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


// return a response ===========================================================

// if there are any errors in our errors array, return a success boolean of false
            if (!empty($errors['reCaptcha'])) {
                // if there are items in our errors array, return those errors
                $data['success'] = false;
                $data['errors'] = $errors;
            } else {
                $email = htmlspecialchars($_POST['email']);
                $password = htmlspecialchars($_POST['password']);
                loginUser($email, $password);
            }
        }

    }else{
        $errors['reCaptcha'] = 'ReCatpcha jest wymagana';
        $_SESSION['userInfo'] = ' 
        <div class="alert alert--danger">
                <span class="alert__content">
                   reCaptcha jest wymagana.
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
?>
<!DOCTYPE html>
<html lang="pl">

<head>
    <?php require_once ($_SERVER['DOCUMENT_ROOT'].'/template/head.php'); ?>
    <title>Logowanie</title>
    <meta name="description" content="Panel logowania - portal <?php echo $setting['domain'];?> informacje z regionu i powiatu."/>
    <meta name="keywords" content="<?php echo $setting['title'];?>, powiat, gmina, informacje, portal, serwis, wiadomości, wydarzenia, forum, firmy, praca, nieruchomości"/>
    <script src="https://www.google.com/recaptcha/api.js" async defer></script>
</head>

<body class="body body--sm">
<div class="subpage">


    <div class="subpage__wrapper">

        <h1 class="panel__heading">
            Logowanie
        </h1>
        <form method="post" action="">
            <div class="form-group">
                <div class="form">
                    <label class="form__label">
                        <input class="form__text" type="email" name="email" placeholder="Wprowadź email"  required>
                        <span class="form__title">Twój email:</span>
                    </label>
                </div>

                <div class="form">
                    <label class="form__label">
                        <input class="form__text" type="password" pattern=".{8,}" name="password" placeholder="Wprowadź hasło" title="Minimum 8 znaków" required>
                        <span class="form__title">Twoje hasło:</span>
                    </label>
                </div>

                <div class="form">
                    <a href="/zmiana-hasla/" class="button button--secondary" title="Zmień hasło">Zmień hasło</a>
                </div>
            </div>

            <div class="left">
                <div class="g-recaptcha" data-sitekey="<?php echo getSettings('recaptcha');?>"></div>
            </div>


            <button type="submit" class="button button--color button--block">Zaloguj mnie</button>

        </form>
        <div class="subpage__footer">
            <a href="/" class="button button--secondary">Strona główna</a> |
            <a href="/rejestracja/" class="button">Rejestracja</a>
        </div>

    </div>
</div>







<?php

foreach(getAds('background-login',getCategoryId(htmlspecialchars($_GET['category']))) as $ad){
    $backgroundsList.=html_entity_decode($ad['code']);
}
echo $backgroundsList;
?>

<?php require_once($_SERVER['DOCUMENT_ROOT'].'/template/after-footer.php'); ?>
</body>

</html>