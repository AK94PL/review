<?php
require_once($_SERVER['DOCUMENT_ROOT'].'/app/init/autoloader.php');
require_once($_SERVER['DOCUMENT_ROOT'].'/app/init/functions.php');



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

?>
<!DOCTYPE html>
<html lang="pl">

<head>
    <?php require_once ($_SERVER['DOCUMENT_ROOT'].'/template/head.php'); ?>
    <title>Strona główna - <?php echo $setting['domain']; ?> <?php echo $setting['title'];?> i okolice - Portal internetowy, wiadomości, sport, kultura, rozrywka.</title>
    <meta name="description" content="<?php echo $setting['title'];?> wiadomości, informacje z powiatu i regionu."/>
    <meta name="keywords" content="<?php echo $setting['title'];?>, powiat, informacje, portal, serwis, wiadomości, wydarzenia, forum, firmy, praca, nieruchomości"/>
</head>

<body class="body">
<div class="page">

    <h1 class="page__heading"><?php echo $setting['title'];?> - portal</h1>

    <?php
    foreach(getAds('banner-top',getCategoryId(htmlspecialchars($_GET['category']))) as $ad){
        $heroList .= html_entity_decode($ad['code']);
    }
    echo $heroList ;
    ?>

    <?php require($_SERVER['DOCUMENT_ROOT'] . '/template/nav/nav.php'); ?>
    <div class="content">
        <main class="main">
            <form method="post" action="/szukaj/">
                <div class="search">
                    <div class="search-panel">
                        <input class="search-panel__form" type="text" name="keyword" placeholder="Czego szukasz?">
                        <button class="search-panel__button" type="submit" name="search">
                            <svg class="search-panel__svg" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24">
                                <path
                                        d="M21.71,20.29,18,16.61A9,9,0,1,0,16.61,18l3.68,3.68a1,1,0,0,0,1.42,0A1,1,0,0,0,21.71,20.29ZM11,18a7,7,0,1,1,7-7A7,7,0,0,1,11,18Z" />
                            </svg>
                            Szukaj
                        </button>
                    </div>
            </form>
    </div>

    <?php
    $newsArray = getNewses($setting['elementsCount']);
    if(!empty($newsArray)){
        echo '
        <section>
        <h2 class="main__heading">Ostatnio dodane wiadomości</h2>
        ';

        echo (!empty($newsArray)?'<div class="panel-art__group">':'');

        foreach($newsArray as $news){
            $newsList .='
                    <section class="panel-art">
                    <div class="panel-art__wrapper">
                    '.($news['promoted']?'<div class="bdg">Wyróżniono</div>':'').'
                        <a href="/wiadomosci/'.$news['subjectPL'].','.$news['newsId'].'/" class="panel-art-photo" title="Przejdź do wiadomości">
                            <img class="panel-art-photo__img" src="'.(!empty($news['image'])?'/uploads/articles/'.$news['image']:'/assets/img/panel--art.png').'" alt="'.$news['subject'].'">
                        </a>
                        <div class="panel-art-content">
                            <h3 class="heading">
                                <a href="/wiadomosci/'.$news['subjectPL'].','.$news['newsId'].'/" class="heading__link">
                                    '.$news['subject'].'
                                </a>
                            </h3>
                            <div class="author">';

            if($news['user_type'] !='deleted' && $news['user_type']!='deleted-company'){
                $newsList.='
                                <a href="'.($news['user_type'] === 'user'?'/profil/'.getAuthorLoginPL($news['user_id'],$news['user_type']).','.$news['user_id'].'/':'/firmy/'.getAuthorLogin($news['user_id'],$news['user_type']).','.$news['user_id'].'/').'" class="link" title="Profil autora">'.getAuthorLogin($news['user_id'],$news['user_type']).'</a>
                                ';
            }else{
                $newsList.='
                                <span class="bolder">Konto usunięte</span>
                                ';
            }

            $newsList.='
                                <span class="author__date">('.time_elapsed_string($news['created_date']).')</span>.
                                Kategoria:
                                <a href="/wiadomosci/'.$news['categoryNamePL'].'/" class="link" title="Kategoria">'.$news['name'].'</a>.
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
                                            '.$news['views'].'
                                        </span>
                                </div>
                            </div>
                        </div>
                    </div>
                </section>
                    ';
        }
        echo $newsList;

        echo (!empty($newsArray)?'</div>':'');
        echo '
        <div class="right">
            <a href="/wiadomosci/" class="button button--secondary" title="Pokaż wszystkie">
                Pokaż więcej...
            </a>
        </div>
    </section>
        ';
    }
    ?>

    <?php require($_SERVER['DOCUMENT_ROOT'] . '/template/content/event-homepage.php');?>

    <?php
    $articlesArray = getArticles($setting['elementsCount']);
    if(!empty($articlesArray)){
        echo '
        <section>
        <h2 class="main__heading">Ostatnio dodane artykuły</h2>
        ';

        echo (!empty($articlesArray)?'<div class="panel-art__group">':'');

        foreach($articlesArray as $art){
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
                            </h3>
                            <div class="author">';

                            if($art['user_type'] !='deleted' && $art['user_type']!='deleted-company'){
                                $articlesList.='
                                <a href="'.($art['user_type'] === 'user'?'/profil/'.getAuthorLoginPL($art['user_id'],$art['user_type']).','.$art['user_id'].'/':'/firmy/'.getAuthorLogin($art['user_id'],$art['user_type']).','.$art['user_id'].'/').'" class="link" title="Profil autora">'.getAuthorLogin($art['user_id'],$art['user_type']).'</a>
                                ';
                            }else{
                                $articlesList.='
                                <span class="bolder">Konto usunięte</span>
                                ';
                            }

                                $articlesList.='
                                <span class="author__date">('.time_elapsed_string($art['created_date']).')</span>.
                                Kategoria:
                                <a href="/artykuly/'.$art['categoryNamePL'].'/" class="link" title="Kategoria">'.$art['name'].'</a>.
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
                                            '.$art['views'].'
                                        </span>
                                </div>
                            </div>
                        </div>
                    </div>
                </section>
                    ';
        }
        echo $articlesList;

        echo (!empty($articlesArray)?'</div>':'');
        echo '
        <div class="right">
            <a href="/artykuly/" class="button button--secondary" title="Pokaż wszystkie">
                Pokaż więcej...
            </a>
        </div>
    </section>
        ';
    }
    ?>




    <?php
    $companiesArray = getCompanies($setting['elementsCount']);
        if(!empty($companiesArray)){
            echo '
                <section>
        <h2 class="main__heading">Ostatnio dodane firmy</h2>
            ';

            echo (!empty($companiesArray)?'<div class="panel-fir__group">':'');

            foreach($companiesArray as $company){
                $companiesList.='                <section class="panel-fir '.($company['verificated']?' panel-fir--ver ':'').($company['promoted']?' panel-fir--pro ':'').'">
                    <div class="panel-fir__wrapper">
                          '.($company['promoted']?'<div class="bdg">Wyróżniono</div>':'').'
                        <div class="panel-fir-header">
                            <a class="panel-fir-header__photo" href="/firmy/'.$company['namePL'].','.$company['companyId'].'/" title="Wyświetl profil">
                                <img class="panel-fir-header__img" src="'.(!empty($company['logo'])?'/uploads/companies/'.$company['logo']:'/assets/img/panel--fir.png').'" alt="'.$company['name'].'">
                            </a>
                        </div>
                        <div class="panel-fir-content">
                            <div class="panel-fir-content__header">
                                <h3 class="heading">
                                    <a href="/firmy/'.$company['namePL'].','.$company['companyId'].'/" class="heading__link" title="Wyświetl profil">
                                        '.$company['name'].'
                                    </a>
                                </h3>
                                <div class="panel-fir-content__txt">';
                if(strlen(html_entity_decode(html_entity_decode(strip_tags($company['short_description']))))>120){
                    $companiesList.= substr(strip_tags(html_entity_decode(html_entity_decode($company['short_description']))),0,120).'(...)';
                }else {
                    $companiesList.= html_entity_decode(html_entity_decode(strip_tags($company['short_description'])));
                }
                $companiesList.='  
                                </div>
                            </div>
                            <div class="panel-fir-content__footer"> 
                                <div class="author">
                                    <a href="/firmy/'.$company['namePL'].','.$company['companyId'].'/" class="link" title="Firma">
                                        '.$company['name'].'
                                    </a>
                                    w:
                                    <a href="/firmy/miejscowosc/'.$company['cityPL'].'/" class="link" title="Kategoria">
                                        '.$company['city'].'</a>.
                                    Kategoria:
                                    <a href="/firmy/'.$company['categoryNamePL'].'/" class="link" title="Kategoria">
                                    '.$company['categoryName'].'</a>.
                                </div>
                            </div>
                        </div>
                        <div class="panel-fir-footer">
                            <button class="button button--color" data-call="'.$company['phone'].'">Pokaż numer tel.</button>
                        </div>
                    </div>
                </section>';
            }
            echo $companiesList;
            echo (!empty($companiesArray)?'</div>':'');
            echo '
                    
        <div class="right">
            <a href="/firmy/" class="button button--secondary" title="Pokaż wszystkie">
                Pokaż więcej...
            </a>
        </div>
    </section>

            ';
        }
    ?>


            <?php
            $advertisementsArray = getAdvertisements($setting['elementsCount']);
            if(!empty($advertisementsArray)){
                echo '
                    <section>
        <h2 class="main__heading">Ostatnio dodane ogłoszenia</h2>
                ';

                echo (!empty($advertisementsArray)?'<div class="panel-ogl__group">':'');

                foreach($advertisementsArray as $adv){
                    $advertisementsList.='
                                         <section class="panel-ogl">
                    '.($adv['promoted']?'<div class="bdg">Wyróżniono</div>':'').'   <a href="/ogloszenia/'.$adv['titlePL'].','.$adv['advId'].'/" class="panel-ogl-photo" title="Przejdź do ogłoszenia">
                        <img class="panel-ogl-photo__img" src="';
                    $advImage = getFirstPhoto($adv['advId']);
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
                            <div class="author">';
                    if($adv['user_type'] !='deleted' && $adv['user_type']!='deleted-company'){
                        $advertisementsList.=' <a href="'.($adv['user_type'] === 'user'?'/profil/'.getAuthorLoginPL($adv['user_id'],$adv['user_type']).','.$adv['user_id'].'/':'/firmy/'.getAuthorLogin($adv['user_id'],$adv['user_type']).','.$adv['user_id'].'/').'" class="link" title="Profil autora">'.getAuthorLogin($adv['user_id'],$adv['user_type']).'</a> ';
                    }else{
                        $advertisementsList.=' <span class="bolder">Konto usunięte</span>
                        ';
                    }
                            $advertisementsList.='    <span class="author__date">('.time_elapsed_string($adv['created_date']).')</span>
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
                echo $advertisementsList;
                echo (!empty($advertisementsArray)?'</div>':'');
                echo '
                <div class="right">
                    <a href="/ogloszenia/" class="button button--secondary" title="Pokaż wszystkie">
                        Pokaż więcej...
                    </a>
                </div>
             </section>
                ';
            }

            ?>





            <?php
            $theardsArray = getTheards($setting['elementsCount']);
            if(!empty($theardsArray)){
                echo '
        <h2 class="main__heading">Ostatnio dodane wątki na forum</h2>
            ';
            echo (!empty($theardsArray)?'<div class="panel-for__group">':'');
                foreach($theardsArray as $theard){
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
                            </h3>
                            <div class="author">';
                                if($theard['user_type'] != 'deleted' && $theard['user_type'] != 'deleted-company'){
                                    $theardsList.=' <a href="'.($theard['user_type'] === 'user'?'/profil/'.getAuthorLoginPL($theard['user_id'],$theard['user_type']).','.$theard['user_id'].'/':'/firmy/'.getAuthorLogin($theard['user_id'],$theard['user_type']).','.$theard['user_id'].'/').'" class="link" title="Profil autora">'.getAuthorLogin($theard['user_id'],$theard['user_type']).'</a>';
                                }else{
                                    $theardsList.=' <span class="bolder">Konto usunięte</span>
                                    ';
                                }

                        $theardsList.=' <span class="author__date">('.time_elapsed_string($theard['created_date']).')</span>.
                                Kategoria:
                                <a href="/forum/'.$theard['categoryNamePL'].'/" class="link" title="Kategoria">'.$theard['name'].'</a>.
                            </div>
                        </div>
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

                    $theardsList.='   <div class="actions__el '.((int)(getTheardsRepliesCount($theard['theardId']))>0?' active':'').'">
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
                echo $theardsList;
                echo (!empty($theardsArray)?'</div>':'');
                echo '
        <div class="right">
            <a href="/forum/" class="button button--secondary" title="Pokaż wszystkie">
                Pokaż więcej...
            </a>
        </div>
    </section>

                ';
            }


            ?>




    <?php
    foreach(getAds('banner-bottom',getCategoryId(htmlspecialchars($_GET['category']))) as $ad){
        $betweenList .= html_entity_decode($ad['code']);
    }
    echo $betweenList ;
    ?>

    <?php
    require_once($_SERVER['DOCUMENT_ROOT'].'/template/loginAndRegister.php');
    ?>

    </main>

    <aside class="sidebar">

        <?php require_once ($_SERVER['DOCUMENT_ROOT'].'/template/sidebar.php');?>


    </aside>
</div>
<?php require_once ($_SERVER['DOCUMENT_ROOT'].'/template/footer.php'); ?>
</div>





<?php
foreach(getAds('background',getCategoryId(htmlspecialchars($_GET['category']))) as $ad){
    $backgroundsList.=html_entity_decode($ad['code']);
}
echo $backgroundsList;
?>




<?php require_once($_SERVER['DOCUMENT_ROOT'].'/template/after-footer.php'); ?>
</body>

</html>