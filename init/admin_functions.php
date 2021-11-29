<?php
require_once ($_SERVER['DOCUMENT_ROOT'].'/app/functions/admin_notifications.php');
require_once ($_SERVER['DOCUMENT_ROOT'].'/app/functions/admin_article.php');
require_once ($_SERVER['DOCUMENT_ROOT'].'/app/functions/admin_event.php');
require_once ($_SERVER['DOCUMENT_ROOT'].'/app/functions/admin_advertisement.php');
require_once ($_SERVER['DOCUMENT_ROOT'].'/app/functions/admin_forum.php');
require_once ($_SERVER['DOCUMENT_ROOT'].'/app/functions/admin_company.php');
require_once ($_SERVER['DOCUMENT_ROOT'].'/app/functions/admin_user.php');
require_once ($_SERVER['DOCUMENT_ROOT'].'/app/functions/admin_ads.php');
require_once ($_SERVER['DOCUMENT_ROOT'].'/app/functions/admin_rotator.php');
require_once ($_SERVER['DOCUMENT_ROOT'].'/app/functions/admin_settings.php');
require_once ($_SERVER['DOCUMENT_ROOT'].'/app/functions/admin_news.php');



$actual_link = "https://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";

session_start();
if($_SESSION['loggedin'] && (intval($_SESSION['user']->group_id) === 4 || intval($_SESSION['user']->group_id) === 6)){
    $user = $_SESSION['user'];
}else{
    $user = null;
    header('Location: /');
}
?>