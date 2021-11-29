<?php
require_once ($_SERVER['DOCUMENT_ROOT'].'/app/functions/authorization.php');
require_once ($_SERVER['DOCUMENT_ROOT'].'/app/functions/user.php');
require_once ($_SERVER['DOCUMENT_ROOT'].'/app/functions/confirmation.php');
require_once ($_SERVER['DOCUMENT_ROOT'].'/app/functions/notification.php');
require_once ($_SERVER['DOCUMENT_ROOT'].'/app/functions/category.php');
require_once ($_SERVER['DOCUMENT_ROOT'].'/app/functions/event.php');
require_once ($_SERVER['DOCUMENT_ROOT'].'/app/functions/article.php');
require_once ($_SERVER['DOCUMENT_ROOT'].'/app/functions/company.php');
require_once ($_SERVER['DOCUMENT_ROOT'].'/app/functions/advertisement.php');
require_once ($_SERVER['DOCUMENT_ROOT'].'/app/functions/forum.php');
require_once ($_SERVER['DOCUMENT_ROOT'].'/app/functions/ads.php');
require_once ($_SERVER['DOCUMENT_ROOT'].'/app/functions/favorite.php');
require_once ($_SERVER['DOCUMENT_ROOT'].'/app/functions/city.php');
require_once ($_SERVER['DOCUMENT_ROOT'].'/app/functions/conversation.php');
require_once ($_SERVER['DOCUMENT_ROOT'].'/app/functions/rotator.php');
require_once ($_SERVER['DOCUMENT_ROOT'].'/app/functions/settings.php');
require_once ($_SERVER['DOCUMENT_ROOT'].'/app/functions/news.php');


require_once ($_SERVER['DOCUMENT_ROOT'].'/app/functions/general.php');

$actual_link = "https://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";

$setting['title'] = getSettings('title');
$setting['domain'] = getSettings('site_adress');
$setting['email'] = getSettings('email');

$setting['elementsCount'] = getSettings('elementsCount');

$setting['accept_theards'] = getSettings('accept_theards');
$setting['accept_adverts'] = getSettings('accept_adverts');
$setting['footer_text'] = getSettings('footer_text');
$setting['allow_robots'] = getSettings('allow_robots');
$setting['rules'] = getSettings('rules');
$setting['politics'] = getSettings('politics');


session_start();
if($_SESSION['loggedin'])
{
    $user = $_SESSION['user'];
    if(empty($user->login))
    {
        session_destroy();
        $user = null;
        header('Location:/');
    }
}else{
    $user = null;
}
?>