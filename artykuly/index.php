<?php
require_once($_SERVER['DOCUMENT_ROOT'].'/app/init/autoloader.php');
require_once($_SERVER['DOCUMENT_ROOT'].'/app/init/functions.php');
session_start();
if($_SESSION['loggedin']){
    $user = $_SESSION['user'];
}else{
    $user = null;
}


if(isset($_POST['theardId']) && isset($_POST['vote'])){
    if(isLoggedIn()){
        $vote = htmlspecialchars($_POST['vote']);
        $theardId = htmlspecialchars($_POST['theardId']);
        if($vote === '+1' || $vote === '-1'){
            rateTheard($theardId,$vote,$user->id);
        }
    }
}

if(isset($_POST['addFavorite'])){
    $advertisementId = (int)htmlspecialchars($_POST['addFavorite']);
    if(isLoggedIn()){
        addFavorite($advertisementId,$user->id);
    }
}

$category = htmlspecialchars($_GET['category']);

$articleLimit = 20;

?>
<!DOCTYPE html>
<html lang="pl">

<head>
    <?php require_once ($_SERVER['DOCUMENT_ROOT'].'/template/head.php'); ?>
    <title>Informacje <?php echo $setting['title'];?>. <?php echo $setting['title'];?> portal internetowy, informator miejski, rozkład pks, busy</title>
    <meta name="keywords" content="<?php echo $setting['title'];?>, miasto, gmina, wiadomości, informacje, news, kultura, polityka, sport, biznes, ulice, wystawy, artykuły"/>
    <meta name="description" content="<?php echo ucfirst($setting['domain']);?> aktualności i rzetelne źródło wiedzy o mieście i powiecie."/>
</head>

<body class="body">
<div class="page">

    <h1 class="page__heading"><?php echo $setting['title'];?> - portal</h1>

   <?php
foreach(getAds('banner-top',getCategoryId($category)) as $ad){
    $heroList .= html_entity_decode($ad['code']);
}
echo $heroList ;
?>

    <?php require($_SERVER['DOCUMENT_ROOT'] . '/template/nav/nav.php'); ?>
    <div class="content">
        <main class="main">
        <form method="post" action="/szukaj/artykuly/index.php">
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
$articlesCount_Counter = (!empty($category)?getArticlesCountFromCategory($category):getArticlesCount());
?>

    <section>
        <h2 class="main__heading">Znalezione artykuły (<?php echo $articlesCount_Counter;?>)</h2>

            <?php
            echo ($articlesCount_Counter>0?'<div class="panel-art__group">':'');
            $page = (isset($_GET['p'])?(int)htmlspecialchars($_GET['p']):1);
            if(!empty($category) && isCategoryExist($category)){
                $articlesArray = getAllArticlesFromCategory($category,$page,$articleLimit) ;
                if(!empty($articlesArray)){
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
            }else{
                $articlesArray = getAllArticles($page,$articleLimit);
                if(!empty($articlesArray)){
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

            }
            echo $articlesList;
            echo ($articlesCount_Counter>0?'</div>':'');
            ?>


    </section>



<?php
 foreach(getAds('banner-bottom',getCategoryId($category)) as $ad){
                    $betweenList .= html_entity_decode($ad['code']);
                }
                echo $betweenList ;
?>

            <?php
            if(!empty($category) && isCategoryExist($category)){
                showArticlePaginationFromCategory($category,$page,$articleLimit);
            }else{
                showArticlePagination($page,$articleLimit);
            }

            require_once($_SERVER['DOCUMENT_ROOT'].'/template/loginAndRegister.php');

            ?>

        </main>

        <aside class="sidebar">

 <?php require_once ($_SERVER['DOCUMENT_ROOT'].'/template/sidebar.php');?></aside>
    </div>
    <?php require_once ($_SERVER['DOCUMENT_ROOT'].'/template/footer.php'); ?>
</div>





<?php
foreach(getAds('background',getCategoryId(htmlspecialchars($category))) as $ad){
    $backgroundsList.=html_entity_decode($ad['code']);
}
echo $backgroundsList;
?>




<?php require_once($_SERVER['DOCUMENT_ROOT'].'/template/after-footer.php'); ?>
</body>

</html>