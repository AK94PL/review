<?php
require_once($_SERVER['DOCUMENT_ROOT'].'/app/init/autoloader.php');
require_once($_SERVER['DOCUMENT_ROOT'].'/app/init/functions.php');
session_start();
if($_SESSION['loggedin']){
    $user = $_SESSION['user'];
}else{
    $user = null;
}

$submodule = 'companies';

if(isset($_POST['theardId']) && isset($_POST['vote'])){
    if(isLoggedIn()){
        $vote = htmlspecialchars($_POST['vote']);
        $theardId = htmlspecialchars($_POST['theardId']);
        if($vote === '+1' || $vote === '-1'){
            rateTheard($theardId,$vote,$user->id);
        }
    }else{
        $_SESSION['userInfo'] .= '
            <div class="alert alert--danger">
<span class="alert__content">
   Musisz być zalogowany aby korzystać w pełni z funkcjonalności portalu. <a href="/logowanie/">Przejdź do logowania</a>.
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

if(isset($_POST['addFavorite'])){
    $advertisementId = (int)htmlspecialchars($_POST['addFavorite']);
    if(isLoggedIn()){
        addFavorite($advertisementId,$user->id);
    }else{
        $_SESSION['userInfo'] .= '
            <div class="alert alert--danger">
<span class="alert__content">
   Musisz być zalogowany aby korzystać w pełni z funkcjonalności portalu. <a href="/logowanie/">Przejdź do logowania</a>.
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



if(isset($_GET['id']) && isset($_GET['name'])){
    $companyId = (int)htmlspecialchars($_GET['id']);
    $companyName = htmlspecialchars(trim($_GET['name']));
    $companyArray = getCompanyById($companyId,$companyName);
    if(empty($companyArray)){
        header('Location: /404.php');
    }


    if(isset($_GET['zglos'])){
        if($_SESSION['loggedin']){
            $_SESSION['report']['type'] = 'company';
            $_SESSION['report']['elemid'] = $companyId;
            $_SESSION['report']['sender'] = $user->id;
            header('Location: /zglos/');
        }else{
            $_SESSION['userInfo'] .= '
            <div class="alert alert--danger">
<span class="alert__content">
   Musisz być zalogowany aby korzystać w pełni z funkcjonalności portalu. <a href="/logowanie/">Przejdź do logowania</a>.
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

if(isset($_GET['module'])){
    $module = htmlspecialchars($_GET['module']);
    if($module != 'ogloszenia' && $module != 'artykuly' && $module != 'wydarzenia' && $module != 'posty'){
        $module = null;
    }

    if($module === 'ogloszenia'){
        $fullNameModule = 'Ogłoszenia';
    }elseif($module === 'artykuly'){
        $fullNameModule = 'Artykuły';
    }elseif($module === 'wydarzenia'){
        $fullNameModule = 'Wydarzenia';
    }elseif($module === 'posty'){
        $fullNameModule = 'Posty';
    }
}

$companiesLimit = null;



if(isset($_POST['sendMessage'])){

    $message = htmlspecialchars($_POST['message']);
    $ownerId = (int)htmlspecialchars($_POST['ownerId']);
    $ownerType = 'company';
    $author_id = (htmlspecialchars($_POST['author']) === 'default'?null:(int)htmlspecialchars($_POST['author']));

    if(!empty($author_id)){
        $author_type = 'company';
    }else{
        $author_id = $user->id;
        $author_type = 'user';
    }
    $conversationId = isExistConversation($author_id,$author_type,$ownerId,$ownerType);
    if((int)$conversationId > 0){
        $add = addMessage($conversationId,$author_id,$author_type,$message);
    }else{
        $conversationId = createConversation($author_id,$ownerId,$author_type,$ownerType);
        $add = addMessage($conversationId,$author_id,$author_type,$message);
    }
    if($add){
        $_SESSION['userInfo'] .= '
            <div class="alert alert--success">
                <span class="alert__content">
                    Pomyślnie przesłaliśmy użytkownikowi Twoją wiadomość.
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
                    Wystąpił błąd podczas przesyłania Twojej wiadomości. Spróbuj ponownie za chwilę.
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
    <title>Profil firmy <?php echo $companyArray[0]['name']; ?> - <?php echo $setting['domain']; ?></title>
    <meta name="description" content="Profil firmy <?php echo $companyArray[0]['name']; ?>. Skontakuj się z firmą i zapoznaj się z ofertą."/>
    <meta name="keywords" content="<?php echo $companyArray[0]['keywords']; ?>"/>
    <meta property="og:title" content="Firma <?php echo $companyArray[0]['name']; ?> - <?php echo $setting['domain']; ?>"/>
    <meta property="og:locale" content="pl_PL"/>
    <meta property="og:type" content="website"/>
    <meta property="og:url" content="<?php echo $actual_link;?>"/>
    <meta property="og:site_name" content="<?php echo $setting['domain']; ?>"/>
    <meta property="og:description" content="<?php echo $companyArray[0]['name']; ?>"/>
    <meta property="og:image" content="<?php echo 'https://'.$setting['domain'].'/uploads/companies/'.$companyArray[0]['logo'];?>"/>
    <meta property="twitter:image" content="<?php echo 'https://'.$setting['domain'].'/uploads/companies/'.$companyArray[0]['logo'];?>"/>
    <script src="https://cdn.tiny.cloud/1/p8ifqws5hhul7j023fr3bao0f9wvi8dzbq2x34goluss86it/tinymce/5/tinymce.min.js" referrerpolicy="origin"></script>
</head>

<body class="body">
<div class="page">

    <h1 class="page__heading"><?php echo $setting['title'];?> - portal</h1>

   <?php
foreach(getAds('banner-top',$companyArray[0]['category_id']) as $ad){
    $heroList .= html_entity_decode($ad['code']);
}
echo $heroList ;
?>

    <?php require($_SERVER['DOCUMENT_ROOT'] . '/template/nav/nav.php'); ?>
    <div class="content">

            <main class="main">
                <?php
                if(!empty($companyArray)){
                foreach( $companyArray as $company){
                    $advertisementsCount_Counter = getAdvertisementsCountFromCompany($companyId);
                    $articlesCount_Counter = getArticlesCountFromCompany($companyId);
                    $eventsCount_Counter = getEventsCountCompany($companyId);
                    $theardsCount_Counter = getTheardsCountFromCompany($companyId);



                    $companyContent .='
                      <ul class="breadcrumbs list">
                    <li class="breadcrumbs__item">
                        <button class="breadcrumbs__link" type="button" data-action="back"
                                title="Wróć na poprzednią stronę">
                            Powrót
                        </button>
                    </li>
                    <li class="breadcrumbs__item">
                        <a href="/firmy/" class="breadcrumbs__link" title="Przejdź">
                            Firmy
                        </a>
                    </li>
                    <li class="breadcrumbs__item">
                        <a href="/firmy/'.$company['categoryNamePL'].'/" class="breadcrumbs__link" title="Przejdź">
                            '.$company['categoryName'].'
                        </a>
                    </li>
                    <li class="breadcrumbs__item">
                        <a href="/firmy/'.$company['categoryNamePL'].'/'.$company['cityPL'].'/" class="breadcrumbs__link" title="Przejdź">
                            '.$company['city'].'
                        </a>
                    </li>
                </ul>
                              <section class="profile" id="szczegoly">

                    <div class="profile-header">
                        <div class="profile-header__avatar">
                            <img class="profile-header__img" src="'.(!empty($company['logo'])?'/uploads/companies/'.$company['logo']:'/assets/img/avatar--profil.png').'" alt="'.$company['name'].'">
                        </div>
                    </div>


                    <header class="profile-user">
                        <h2 class="profile-user__name">
                        '.$company['name'].'
                        </h2>
                         '.($company['verificated']?'<div class="ver">
            <svg class="ver__svg" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24">
                <path d="M9,20.42L2.79,14.21L5.62,11.38L9,14.77L18.88,4.88L21.71,7.71L9,20.42Z"></path>
            </svg>
            Profil zweryfikowany
        </div>':'').'
                        
                        '.((int)$company['status'] === 2 ? '<div class="panel__txt">Firma przeznaczona do usunięcia - jeśli masz uwagi dotyczące tej firmy, skontaktuj się z administracją serwisu.</div>':'').'
                        '.(strtotime(getUserLastLogin($company['user_id'])) > strtotime("-15 minutes")?'<span class="status status--online">Dostępny: <span class="status__highlight">teraz</span></span>':' <span class="status status--offline">Dostępny: <span class="status__highlight">'.time_elapsed_string(getUserLastLogin($company['user_id'])).'</span></span>').'
                        <span class="profile-user__type">
                        Firma
                        </span>
                        <button class="button button--color" data-call="'.$company['phone'].'"> Pokaż numer tel.</button>
                        <ul class="profile-content list">
                            <li class="profile-content__item">
                                        <span class="profile-content__option">
                                Wydarzenia
                                        </span>
                                        <span class="profile-content__info">
                                '.($eventsCount_Counter>0?'<a href="/firmy/'.$company['name'].','.$company['companyId'].'/wydarzenia/" class="profile-content__link" title="Przeglądaj">'.$eventsCount_Counter.'</a>':$eventsCount_Counter).'
                                </span>
                            </li>
                            <li class="profile-content__item">
                                <span class="profile-content__option">
                        Artykuły
                                </span>
                                <span class="profile-content__info">
                         '.($articlesCount_Counter>0?'<a href="/firmy/'.$company['name'].','.$company['companyId'].'/artykuly/" class="profile-content__link" title="Przeglądaj">'.$articlesCount_Counter.'</a>':$articlesCount_Counter).'
                        </span>
                            </li>
                            <li class="profile-content__item">
                                <span class="profile-content__option">
                        Ogłoszenia
                                </span>
                                <span class="profile-content__info">
                        
                        '.($advertisementsCount_Counter>0?'<a href="/firmy/'.$company['name'].','.$company['companyId'].'/ogloszenia/" class="profile-content__link" title="Przeglądaj">'.$advertisementsCount_Counter.'</a>':$advertisementsCount_Counter).'
                
                                </span>
                            </li>
                            <li class="profile-content__item">
                                <span class="profile-content__option">
                        Wątki
                                </span>
                                <span class="profile-content__info">
                                   
                        '.($theardsCount_Counter>0?'<a href="/firmy/'.$company['name'].','.$company['companyId'].'/posty/" class="profile-content__link" title="Przeglądaj">'.$theardsCount_Counter.'</a>':$theardsCount_Counter).'

                        
                                </span>
                            </li>
                        </ul>
                    </header>

                    <footer class="profile-footer">
                        <div class="right">
                            <ul class="share list">
                                  <li class="share__item">
                            <a target="_blank" class="share__link share__link--facebook" href="https://www.facebook.com/sharer/sharer.php?u='.$actual_link.'"
                                    title="Udostępnij na Facebooku">
                                <svg class="share__svg" xmlns="http://www.w3.org/2000/svg"
                                     xmlns:xlink="http://www.w3.org/1999/xlink" version="1.1" viewBox="0 0 24 24">
                                    <path
                                        d="M17,2V2H17V6H15C14.31,6 14,6.81 14,7.5V10H14L17,10V14H14V22H10V14H7V10H10V6A4,4 0 0,1 14,2H17Z">
                                    </path>
                                </svg>
                            </a>
                        </li>
                        <li class="share__item">
                            <a target="_blank" href="https://twitter.com/share?url='.$actual_link.'" class="share__link share__link--twitter" 
                                    title="Udostępnij na Twitterze">
                                <svg class="share__svg" xmlns="http://www.w3.org/2000/svg"
                                     xmlns:xlink="http://www.w3.org/1999/xlink" version="1.1" viewBox="0 0 24 24">
                                    <path
                                        d="M22.46,6C21.69,6.35 20.86,6.58 20,6.69C20.88,6.16 21.56,5.32 21.88,4.31C21.05,4.81 20.13,5.16 19.16,5.36C18.37,4.5 17.26,4 16,4C13.65,4 11.73,5.92 11.73,8.29C11.73,8.63 11.77,8.96 11.84,9.27C8.28,9.09 5.11,7.38 3,4.79C2.63,5.42 2.42,6.16 2.42,6.94C2.42,8.43 3.17,9.75 4.33,10.5C3.62,10.5 2.96,10.3 2.38,10C2.38,10 2.38,10 2.38,10.03C2.38,12.11 3.86,13.85 5.82,14.24C5.46,14.34 5.08,14.39 4.69,14.39C4.42,14.39 4.15,14.36 3.89,14.31C4.43,16 6,17.26 7.89,17.29C6.43,18.45 4.58,19.13 2.56,19.13C2.22,19.13 1.88,19.11 1.54,19.07C3.44,20.29 5.7,21 8.12,21C16,21 20.33,14.46 20.33,8.79C20.33,8.6 20.33,8.42 20.32,8.23C21.16,7.63 21.88,6.87 22.46,6Z">
                                    </path>
                                </svg>
                            </a>
                        </li>
                            </ul>
                        </div>

                        <a href="./?zglos" class="button button--secondary" title="Powiadom administrację">Zgłoś uwagę</a>

                    </footer>
                </section>

                ';

                    if($module === 'ogloszenia'){
                        $companyContent.='<section>';


                        $companyContent.=' <h2 class="main__heading">Znalezione ogłoszenia ('.$advertisementsCount_Counter.')</h2>';
                        $companyContent.=($advertisementsCount_Counter>0?'<div class="panel-ogl__group">':'');

                        $page = (isset($_GET['p'])?(int)htmlspecialchars($_GET['p']):1);

                        if(isset($_GET['module']) && isset($_GET['id']) ) {
                            $advertisementsLimit = 20;
                            foreach(getAllAdvertisementsFromCompany($companyId,$page,$advertisementsLimit) as $adv){
                                $advertisementsList.='
                                         <section class="panel-ogl">
                    '.($adv['promoted']?'<div class="bdg">Wyróżniono</div>':'').'   <a href="/ogloszenia/'.$adv['titlePL'].','.$adv['advId'].'/" class="panel-ogl-photo" title="Przejdź do ogłoszenia">
                        <img class="panel-ogl-photo__img" src="';
                                $advImage = getFirstPhoto($adv['advId'],1);
                                if($advImage != null){
                                    $advertisementsList.='/uploads/annoucent/'.$advImage[0]['source'];
                                }else{
                                    $advertisementsList.='/assets/img/panel--ogl.png';
                                }
                                $advertisementsList.='" alt="'.$adv['title'].'">
                    </a>
                    <div class="panel-ogl-content">
                        <div class="panel-ogl-header">
                            <h3 class="heading" title="Przejdź do ogłoszenia">
                                <a class="heading__link" href="/ogloszenia/'.$adv['titlePL'].','.$adv['advId'].'/">
                                   '.$adv['title'].'
                                </a>
                            </h3>
                            <div class="author">

                                <a href="'.($adv['user_type'] === 'user'?'/profil/'.getAuthorLoginPL($adv['user_id'],$adv['user_type']).','.$adv['user_id'].'/':'/firmy/'.getAuthorLogin($adv['user_id'],$adv['user_type']).','.$adv['user_id'].'/').'" class="link" title="Profil autora">'.getAuthorLogin($adv['user_id'],$adv['user_type']).'</a>

                                <span class="author__date">('.time_elapsed_string($adv['created_date']).')</span>
                                     w: <a href="/ogloszenia/'.$adv['categoryNamePL'].'/'.$adv['cityPL'].'/" class="link" title="Przejdź">
                        '.$adv['city'].'</a>.

                                Kategoria: <a href="/ogloszenia/'.$adv['categoryNamePL'].'/" class="link" title="Kategoria">'.$adv['categoryName'].'</a>.
                            </div>
                            <span class="badge">
                                    '.$adv['type'].'
                                </span>
                        </div>
                        <div class="panel-ogl-footer">
                                <span class="price price--sm">
                                    '.$adv['price'].' zł
                                </span>
                             <form method="POST" action="">   
                            <button class="wishlist ' . (alreadyFavorite($adv['advId'], $user->id) ? 'active' : '') . '" type="submit" name="addFavorite" value="' . $adv['advId'] . '" title="Obserwuj to ogłoszenie">

                                <svg class="wishlist__svg" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24">

                                    <path d="M12.1,18.55L12,18.65L11.89,18.55C7.14,14.24 4,11.39 4,8.5C4,6.5 5.5,5 7.5,5C9.04,5 10.54,6 11.07,7.36H12.93C13.46,6 14.96,5 16.5,5C18.5,5 20,6.5 20,8.5C20,11.39 16.86,14.24 12.1,18.55M16.5,3C14.76,3 13.09,3.81 12,5.08C10.91,3.81 9.24,3 7.5,3C4.42,3 2,5.41 2,8.5C2,12.27 5.4,15.36 10.55,20.03L12,21.35L13.45,20.03C18.6,15.36 22,12.27 22,8.5C22,5.41 19.58,3 16.5,3Z"></path>

                                </svg>

                            </button>
                            </form>
                        </div>
                    </div>
                </section>
                    ';
                            }
                        }
                        if(!empty($advertisementsList)){
                            $companyContent.= $advertisementsList;
                            $companyContent.=($advertisementsCount_Counter>0?'</div>':'');
                            $companyContent.= showAdvertisementPaginationFromCompany($companyId,$companyName,$page,20);
                        }




                        $companyContent.='</section>';

                    }elseif($module === 'artykuly'){
                        $companyContent.='<section>';
                        $companyContent.='<h2 class="main__heading">Znalezione artykuły ('.$articlesCount_Counter.')</h2>';
                       $companyContent.=($articlesCount_Counter>0?'<div class="panel-art__group">':'');

                        $page = (isset($_GET['p'])?(int)htmlspecialchars($_GET['p']):1);
                        if(isset($_GET['module']) && isset($_GET['id'])){

                            foreach(getAllArticlesFromCompany($companyId,$page,20) as $art){
                                $articlesList .='
                    <section class="panel-art">
                    <div class="panel-art__wrapper">
                    '.($art['promoted']?'<div class="bdg">Wyróżniono</div>':'').'
                        <a href="/artykuly/'.$art['subjectPL'].','.$art['articleId'].'/" class="panel-art-photo" title="Przejdź do artykułu">
                            <img class="panel-art-photo__img" src="'.(!empty($art['image'])?'/uploads/articles/'.$art['image']:'/assets/img/panel--art.png').'" alt="'.$art['subject'].'">
                        </a>
                        <div class="panel-art-content">
                            <h3 class="heading">
                                <a href="/artykuly/'.$art['subjectPL'].','.$art['articleId'].'/" class="heading__link">
                                    '.$art['subject'].'
                                </a>
                            </h3>';

                        if($art['user_type'] != 'deleted' && $art['user_type'] != 'deleted-company'){
                            $articlesList.='
                        <div class="author">
                                <a href="'.($art['user_type'] === 'user'?'/profil/'.getAuthorLoginPL($art['user_id'],$art['user_type']).','.$art['user_id'].'/':'/firmy/'.getAuthorLogin($art['user_id'],$art['user_type']).','.$art['user_id'].'/').'" class="link" title="Profil autora">'.getAuthorLogin($art['user_id'],$art['user_type']).'</a>
                                <span class="author__date">('.time_elapsed_string($art['created_date']).')</span>.
                        Kategoria:
                                <a href="/artykuly/'.$art['categoryNamePL'].'/" class="link" title="Kategoria">'.$art['name'].'</a>.
                            </div>
                        ';
                        }else{
                            $articlesList.='
                            <div class="author">
                                <span class="bolder">Konto usunięte</span>
                                <span class="author__date">('.time_elapsed_string($art['created_date']).')</span>.
                                Kategoria:
                                        <a href="/artykuly/'.$art['categoryNamePL'].'/" class="link" title="Kategoria">'.$art['name'].'</a>.
                                    </div>
                            ';
                        }


                                $articlesList.='<div class="actions">
                                <div class="actions__el">
                                    <svg class="actions__svg" xmlns="http://www.w3.org/2000/svg"
                                         xmlns:xlink="http://www.w3.org/1999/xlink" version="1.1"
                                         viewBox="0 0 24 24">
                                        <path
                                                d="M13.5,8H12V13L16.28,15.54L17,14.33L13.5,12.25V8M13,3A9,9 0 0,0 4,12H1L4.96,16.03L9,12H6A7,7 0 0,1 13,5A7,7 0 0,1 20,12A7,7 0 0,1 13,19C11.07,19 9.32,18.21 8.06,16.94L6.64,18.36C8.27,20 10.5,21 13,21A9,9 0 0,0 22,12A9,9 0 0,0 13,3">
                                        </path>
                                    </svg>
                                    <span class="actions__content">
                                            Wyświetlenia:
                                        </span>
                                    <span class="actions__counter">
                                            '.$art['views'].'
                                        </span>
                                </div>
                            </div>
                        </div>
                    </div>
                </section>
                    ';
                            }
                        }
                        if(!empty($articlesList)){
                            $companyContent.= $articlesList;
                            $companyContent.=($articlesCount_Counter>0?'</div>':'');
                            $companyContent.=showArticlePaginationFromCompany($companyId,$companyName,$page,20);
                        }
                        $companyContent.='</section>';
                    }elseif($module === 'wydarzenia'){
                        $companyContent.='<section>';
                        $companyContent.='
            <h2 class="main__heading">Znalezione wydarzenia ('.$eventsCount_Counter.')</h2>';

                        $companyContent.=($eventsCount_Counter>0?'<div class="panel-art__group">':'');

                        $page = (isset($_GET['p'])?(int)htmlspecialchars($_GET['p']):1);

                        if(isset($_GET['module'])  && isset($_GET['id'])){
                            $eventsLimit = null;
                            foreach(getAllEventsFromCompany($companyId,$page,20) as $event){
                                $eventsList.='
                               <section class="panel-art">
                                      <div class="panel-art__wrapper">
                    '.($event['promoted']?'<div class="bdg">Wyróżniono</div>':'').'   <a href="/wydarzenia/'.$event['subjectPL'].','.$event['eventId'].'/" class="panel-art-photo" title="Przejdź do artykułu">
                            <img class="panel-art-photo__img" src="'.(!empty($event['image'])?'/uploads/events/'.$event['image']:'/assets/img/panel--art.png').'" alt="'.$event['subject'].'">

                        </a>
                        <div class="panel-art-content">
                            <h3 class="heading">
                                <a href="/wydarzenia/'.$event['subjectPL'].','.$event['eventId'].'/" class="heading__link">
                                    '.$event['subject'].'
                                </a>
                            </h3>
                            <div class="author">
                                <a href="'.($event['user_type'] === 'user'?'/profil/'.getAuthorLoginPL($event['user_id'],$event['user_type']).','.$event['user_id'].'/':'/firmy/'.getAuthorLogin($event['user_id'],$event['user_type']).','.$event['user_id'].'/').'" class="link" title="Profil autora">'.getAuthorLogin($event['user_id'],$event['user_type']).'</a>
                                <span class="author__date">('.time_elapsed_string($event['created_date']).')</span>
                                w:
                                <a href="/wydarzenia/miejscowosc/'.$event['cityPL'].'/" class="link" title="Wydarzenia z '.$event['city'].'">'.$event['city'].'</a>.
                                Kategoria:
                                <a href="/wydarzenia/'.$event['categoryNamePL'].'/'.$event['cityPL'].'/" class="link" title="'.$event['categoryName'].' w '.$event['city'].'">'.$event['categoryName'].'</a>.
                            </div>
                            <div class="panel-art-date">
                                <div class="panel-art-date__elm">
                                    <span class="panel-art-date__title">Rozpoczęcie:</span>
                                    <span class="panel-art-date__time">'.substr($event['date_start'], 0, 16).'</span>
                                </div>
                                <div class="panel-art-date__elm">
                                    <span class="panel-art-date__title">Zakończenie:</span>
                                    <span class="panel-art-date__time">'.(!empty($event['date_end'])?substr($event['date_end'], 0, 16):'---').'</span>
                                </div>
                            </div>
                            <div class="actions">
                                <div class="actions__el">
                                    <svg class="actions__svg" xmlns="http://www.w3.org/2000/svg"
                                         xmlns:xlink="http://www.w3.org/1999/xlink" version="1.1"
                                         viewBox="0 0 24 24">
                                        <path
                                                d="M13.5,8H12V13L16.28,15.54L17,14.33L13.5,12.25V8M13,3A9,9 0 0,0 4,12H1L4.96,16.03L9,12H6A7,7 0 0,1 13,5A7,7 0 0,1 20,12A7,7 0 0,1 13,19C11.07,19 9.32,18.21 8.06,16.94L6.64,18.36C8.27,20 10.5,21 13,21A9,9 0 0,0 22,12A9,9 0 0,0 13,3">
                                        </path>
                                    </svg>
                                    <span class="actions__content">
                                            Wyświetlenia:
                                        </span>
                                    <span class="actions__counter">
                                            '.$event['views'].'
                                        </span>
                                </div>
                            </div>
                        </div>
                    </div>
                </section>
               ';
                            }
                        }

                        if(!empty($eventsList)){
                            $companyContent.= $eventsList;
                            $companyContent.=($eventsCount_Counter>0?'</div>':'');
                            $companyContent.=showEventPaginationCompany($companyId,$companyName,$page,20);
                        }
                        $companyContent.='</section>';
                    }elseif($module === 'posty'){
                        $companyContent.='<section>';
                        $companyContent.='<h2 class="main__heading">Znalezione wątki na forum ('.$theardsCount_Counter.')</h2>';

                        $companyContent.=($theardsCount_Counter>0?'<div class="panel-for__group">':'');

                        $page = (isset($_GET['p'])?(int)htmlspecialchars($_GET['p']):1);
                        if(isset($_GET['module']) && isset($_GET['id'])){
                            foreach(getAllTheardsFromCompany($companyId,$page,20) as $theard){
                                $theardsList.='
                        <form method="POST" action="">
<input type="text" name="theardId" value="'.$theard['theardId'].'" hidden readonly>
                                               <section class="panel-for">
                    '.($theard['promoted']?'<div class="bdg">Wyróżniono</div>':'').' <div class="panel-for-content">  
                        <div class="panel-for-content__header">
                            <h3 class="heading">
                                <a href="/forum/'.$theard['subjectPL'].','.$theard['theardId'].'/" class="heading__link">
                                    '.$theard['subject'].'
                                </a>
                            </h3>';

                                if($theard['user_type'] != 'deleted' && $theard['user_type'] != 'deleted-company' ){
                                    $theardsList.='<div class="author">
                                <a href="'.($theard['user_type'] === 'user'?'/profil/'.getAuthorLoginPL($theard['user_id'],$theard['user_type']).','.$theard['user_id'].'/':'/firmy/'.getAuthorLogin($theard['user_id'],$theard['user_type']).','.$theard['user_id'].'/').'" class="link" title="Profil autora">'.getAuthorLogin($theard['user_id'],$theard['user_type']).'</a>
                                <span class="author__date">('.time_elapsed_string($theard['created_date']).')</span>.
                                Kategoria:
                                <a href="/forum/'.$theard['categoryNamePL'].'/" class="link" title="Kategoria">'.$theard['name'].'</a>.
                            </div>';
                                }else{

                                    $theardsList.='
                     <span class="bolder">Konto usunięte</span>
                        ';

                                }

                                $theardsList.='</div>
                       <div class="actions">';
                                if($theard['pinned']){
                                    $theardsList.='
                            <div class="actions__el pin">
                <svg class="actions__svg" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" version="1.1" viewBox="0 0 24 24">
<path d="M16,12V4H17V2H7V4H8V12L6,14V16H11.2V22H12.8V16H18V14L16,12M8.8,14L10,12.8V4H14V12.8L15.2,14H8.8Z" />                </svg>
                <span class="actions__content">
                    Przypięto
                </span>
            </div>
                            ';
                                }
 if((int)$theard['status'] === 2){
                            $theardsList.='<div class="actions__el ">
                                <svg class="actions__svg" xmlns="http://www.w3.org/2000/svg"
                                     xmlns:xlink="http://www.w3.org/1999/xlink" version="1.1" viewBox="0 0 24 24">
                                  <path d="M17,9V7A5,5,0,0,0,7,7V9a3,3,0,0,0-3,3v7a3,3,0,0,0,3,3H17a3,3,0,0,0,3-3V12A3,3,0,0,0,17,9ZM9,7a3,3,0,0,1,6,0V9H9Zm9,12a1,1,0,0,1-1,1H7a1,1,0,0,1-1-1V12a1,1,0,0,1,1-1H17a1,1,0,0,1,1,1Z"/></svg>
                                <span class="actions__content">
                                        Zamknięto
                                    </span>

                            </div>';
                        }

$theardsList.='<div class="actions__el '.(intval(getTheardsRepliesCount($theard['theardId']))>0?' active':'').'">
                                <svg class="actions__svg" xmlns="http://www.w3.org/2000/svg"
                                     xmlns:xlink="http://www.w3.org/1999/xlink" version="1.1" viewBox="0 0 24 24">
                                    <path
                                            d="M12,3C6.5,3 2,6.58 2,11C2.05,13.15 3.06,15.17 4.75,16.5C4.75,17.1 4.33,18.67 2,21C4.37,20.89 6.64,20 8.47,18.5C9.61,18.83 10.81,19 12,19C17.5,19 22,15.42 22,11C22,6.58 17.5,3 12,3M12,17C7.58,17 4,14.31 4,11C4,7.69 7.58,5 12,5C16.42,5 20,7.69 20,11C20,14.31 16.42,17 12,17Z" />
                                </svg>
                                <span class="actions__content">
                                        Odpowiedzi:
                                    </span>
                                <span class="actions__counter">
                                        ';
                                $theardsList.= getTheardsRepliesCount($theard['theardId']);
                                $theardsList.=' </span>
                            </div>
                            <div class="actions__el">
                                <svg class="actions__svg" xmlns="http://www.w3.org/2000/svg"
                                     xmlns:xlink="http://www.w3.org/1999/xlink" version="1.1" viewBox="0 0 24 24">
                                    <path
                                            d="M13.5,8H12V13L16.28,15.54L17,14.33L13.5,12.25V8M13,3A9,9 0 0,0 4,12H1L4.96,16.03L9,12H6A7,7 0 0,1 13,5A7,7 0 0,1 20,12A7,7 0 0,1 13,19C11.07,19 9.32,18.21 8.06,16.94L6.64,18.36C8.27,20 10.5,21 13,21A9,9 0 0,0 22,12A9,9 0 0,0 13,3" />
                                </svg>
                                <span class="actions__content">
                                        Wyświetlenia:
                                    </span>
                                <span class="actions__counter">
                                        '.$theard['views'].'
                                    </span>
                            </div>
                        </div>
                    </div>
                    <div class="points">
                        <button class="points__button points__button--positive '.(alreadyVoted($user->id,$theard['theardId'],'+1')?' active ':'').'" type="submit" name="vote" value="+1" title="+1">
                            <svg class="points__svg" xmlns="http://www.w3.org/2000/svg"
                                 xmlns:xlink="http://www.w3.org/1999/xlink" version="1.1" viewBox="0 0 24 24">
                                <path d="M7.41,15.41L12,10.83L16.59,15.41L18,14L12,8L6,14L7.41,15.41Z" /></svg>
                        </button>
                        <span class="points__counter" title="Liczba punktów">
                                '.($theard['rate'] > 0 ? '+'.$theard['rate']:$theard['rate']).'
                            </span>
                        <button type="submit" name="vote" value="-1" class="points__button points__button--negative '.(alreadyVoted($user->id,$theard['theardId'],'-1')?' active ':'').'" title="-1">
                            <svg class="points__svg" xmlns="http://www.w3.org/2000/svg"
                                 xmlns:xlink="http://www.w3.org/1999/xlink" version="1.1" viewBox="0 0 24 24">
                                <path d="M7.41,8.58L12,13.17L16.59,8.58L18,10L12,16L6,10L7.41,8.58Z" /></svg>
                        </button>
                    </div>
                </section>
                        </form>';
                            }
                        }

                        if(!empty($theardsList)){
                            $companyContent.= $theardsList;
                            $companyContent.=($theardsCount_Counter>0?'</div>':'');
                            $companyContent.=showTheardsPaginationFromCompany($companyId,$companyName,$page,20);
                        }
                        $companyContent.='</section>';
                    }else{

                        if((int)$company['show_description'] === 1){
                        $companyContent.=' <section class="panel">
                    <h2 class="panel__heading">
                        O firmie
                        </h2>
                    <div class="panel__txt">
                        '.html_entity_decode(html_entity_decode($company['description'])).'
                        </div>
                </section>';
                        }


                        $companyContent.='
                <section class="panel">
                    <h2 class="panel__heading">
                        Szczegółowe dane
                        </h2>
                    <ul class="simple-list list">
                        <li class="simple-list__item">
                            <span class="simple-list__option">
                        Adres:
                            </span>
                            <span class="simple-list__info">
                                '.(!empty($company['adress'])?'<a target="_blank" href="https://google.com/maps/?q='.$company['adress'].','.$company['city'].'" " class="link" title="Mapa">':'').'
                            '.(!empty($company['adress'])?$company['adress']:'-').'
                        </a>
                            </span>
                        </li>
                        <li class="simple-list__item">
                            <span class="simple-list__option">
                        Miejscowość:
                            </span>
                            <span class="simple-list__info">
                                <a href="/firmy/miejscowosc/'.$company['cityPL'].'/" class="link" title="Miejscowość">
                            '.$company['city'].'
                        </a>
                            </span>
                        </li>
                        <li class="simple-list__item">
                            <span class="simple-list__option">
                        Strona internetowa:
                            </span>
                            <span class="simple-list__info">
                        '.(!empty($company['website'])?$company['website']:'-').'
                        </span>
                        </li>
                        <li class="simple-list__item">
                            <span class="simple-list__option">
                        NIP:
                            </span>
                            <span class="simple-list__info">
                        '.$company['nip'].'
                        </span>
                        </li>
                        <li class="simple-list__item">
                            <span class="simple-list__option">
                        Na rynku od:
                            </span>
                            <span class="simple-list__info">
                        '.((int)$company['foundation_year']>0?$company['foundation_year'].' r.':'-').'
                            </span>
                        </li>
                        <li class="simple-list__item">
                            <span class="simple-list__option">
                        Utworzenie profilu:
                            </span>
                            <span class="simple-list__info">
                        '.time_elapsed_string(getUserCreatedDate($company['user_id'])).'
                        </span>
                        </li>
                        <li class="simple-list__item">
                            <span class="simple-list__option">
                        Wyświetlenia profilu:
                            </span>
                            <span class="simple-list__info">
                        '.$company['views'].'
                        </span>
                        </li>
                        <li class="simple-list__item">
                            <span class="simple-list__option">
                        Kategoria:
                            </span>

                            <span class="simple-list__info">
                                <a href="/firmy/'.$company['categoryNamePL'].'/" class="link" title="Kategoria">
                            '.$company['categoryName'].'
                        </a>
                            </span>
                        </li>
                        <li class="simple-list__item">
                            <span class="simple-list__option">
                        Słowa kluczowe:
                            </span>
                            <span class="simple-list__info">
                                <ul class="tags-list list">';
                        $tags = explode(',',str_replace(' ','',$company['keywords']));
                        $i = 0;
                        foreach($tags as $tag)
                        {
                            $tagsPL[]=clean($tag);
                            if(!empty($tag)){
                                $companyContent.='<li class="tags-list__item"><a class="tags-list__link" href="/tag/'.$tagsPL[$i].'/">'.$tag.'</a></li>';
                            }
                            $i++;
                        }

                              $companyContent.='  </ul>
                            </span>
                        </li>
                    </ul>
                </section>';

                    if($company['show_hours']){
                        $companyContent.= '
                        <section class="panel">
                    <h2 class="panel__heading">
                        Godziny pracy
                        </h2>
                    <ul class="simple-list list">
                        <li class="simple-list__item">
                                <span class="simple-list__option">
                        Poniedziałek
                                </span>
                        '.($company['pon_o'] || $company['pon_z'] != null ? ' <span class="simple-list__info"><span class="list-hours-start">'.$company['pon_o'].'</span><span class="list-hours-end"> — '.$company['pon_z'].'</span></span>':' <span class="simple-list__info">nieczynne</span>').'
                        </li>
                        <li class="simple-list__item">
                                <span class="simple-list__option">
                        Wtorek
                                </span>
                        '.($company['wto_o'] && $company['wto_z'] != null ? ' <span class="simple-list__info"><span class="list-hours-start">'.$company['wto_o'].'</span><span class="list-hours-end"> — '.$company['wto_z'].'</span></span>':' <span class="simple-list__info">nieczynne</span>').'

                        </li>
                        <li class="simple-list__item">
                                <span class="simple-list__option">
                        Środa
                                </span>
                        '.($company['sro_o'] && $company['sro_z'] != null ? ' <span class="simple-list__info"><span class="list-hours-start">'.$company['sro_o'].'</span><span class="list-hours-end"> — '.$company['sro_z'].'</span></span>':' <span class="simple-list__info">nieczynne</span>').'
                        </li>
                        <li class="simple-list__item">
                                <span class="simple-list__option">
                        Czwartek
                                </span>
                        '.($company['czw_o'] && $company['czw_z'] != null ? ' <span class="simple-list__info"><span class="list-hours-start">'.$company['czw_o'].'</span><span class="list-hours-end"> — '.$company['czw_z'].'</span></span>':' <span class="simple-list__info">nieczynne</span>').'
                        </li>
                         <li class="simple-list__item">
                                <span class="simple-list__option">
                        Piątek
                                </span>
                        '.($company['pia_o'] && $company['pia_z'] != null ? ' <span class="simple-list__info"><span class="list-hours-start">'.$company['pia_o'].'</span><span class="list-hours-end"> — '.$company['pia_z'].'</span></span>':' <span class="simple-list__info">nieczynne</span>').'
                        </li>
                        <li class="simple-list__item">
                                <span class="simple-list__option">
                        Sobota
                                </span>
                        '.($company['sob_o'] && $company['sob_z'] != null ? ' <span class="simple-list__info"><span class="list-hours-start">'.$company['sob_o'].'</span><span class="list-hours-end"> — '.$company['sob_z'].'</span></span>':' <span class="simple-list__info">nieczynne</span>').'
                        </li>
                         <li class="simple-list__item">
                                <span class="simple-list__option">
                        Niedziela
                                </span>
                        '.($company['nie_o'] && $company['nie_z'] != null ? ' <span class="simple-list__info"><span class="list-hours-start">'.$company['nie_o'].'</span><span class="list-hours-end"> — '.$company['nie_z'].'</span></span>':' <span class="simple-list__info">nieczynne</span>').'
                        </li>
                    </ul>
                </section>';
                    }

                if($_SESSION['loggedin']){
                    $companyContent.='<section class="panel">
                    <h2 class="panel__heading">
                        Wyślij
                        </h2>
                        <form method="post" action="/firmy/'.$companyName.','.$company['companyId'].'/">
                                        
                        <input type="hidden" name="ownerId" readonly value="'.$company['companyId'].'"/>

                    <div class="form-group">
                        <div class="form">
                                <span class="form__title">Wiadomość:</span>
                                <textarea class="form__textarea" rows="6" name="message" ></textarea>
                        </div>
                    </div>
               <div class="form">
        <label class="form__label">
            <select name="author" class="form__select">
                <option value="default" '.(is_null($_SESSION['user']->default_author)?'selected':'').'>'.$_SESSION['user']->login.'</option>';

                    foreach(getUserCompanies($_SESSION['user']->id,1) as $companyLoop){

                        $companyContent.= '<option value="'.$companyLoop['companyId'].'" '.(((int)$_SESSION['user']->default_author === (int)$companyLoop['companyId'])?'selected':'').'>'.$companyLoop['name'].'</option>';
                    }

                    $companyContent.= '</select>
            <span class="form__title">Autor:</span>
        </label>
    </div>
                    <div class="right">
                        <button type="submit" name="sendMessage" class="button button--color">Wyślij</button>
  </div>
                    </form>
                </section>

                        ';
                }
                    }

                    addCompanyView($company['companyId']);
                }
                echo $companyContent;
                }
                ?>



            <?php
            require_once($_SERVER['DOCUMENT_ROOT'] . '/template/loginAndRegister.php');
            ?>

            </main>

        <aside class="sidebar">
 <?php require_once ($_SERVER['DOCUMENT_ROOT'].'/template/sidebar.php');?></aside>
    </div>
    <?php require_once ($_SERVER['DOCUMENT_ROOT'].'/template/footer.php'); ?>
</div>





<?php
foreach(getAds('background',$companyArray[0]['category_id']) as $ad){
    $backgroundsList.=html_entity_decode($ad['code']);
}
echo $backgroundsList;
?>




<?php require_once($_SERVER['DOCUMENT_ROOT'].'/template/after-footer.php'); ?>

<?php require_once ($_SERVER['DOCUMENT_ROOT'].'/template/tinymce.php');?>

</body>

</html>