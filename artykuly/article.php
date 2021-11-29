<?php
require_once($_SERVER['DOCUMENT_ROOT'].'/app/init/autoloader.php');
require_once($_SERVER['DOCUMENT_ROOT'].'/app/init/functions.php');
session_start();
if($_SESSION['loggedin']){
    $user = $_SESSION['user'];
}else{
    $user = null;
}


$submodule = 'articles';

if(isset($_GET['title']) && isset($_GET['id'])){
    $id = (int)htmlspecialchars($_GET['id']);
    $title = htmlspecialchars($_GET['title']);
    $articleArray = getArticle($id,$title,1);
    if(empty($articleArray)){
        header('Location: /404.php');
    }

    //$rotator = getRotator($articleArray[0]['tags']);
    $tagsList = explode(',', $articleArray[0]['tags']);
    foreach($tagsList as $tagRotator){
        $rotatorLoop =  getRotator(trim($tagRotator));
        if (!empty($rotatorLoop)){
            $rotator =$rotatorLoop;
        }
    }
    if(isset($_GET['zglos'])){
        if($_SESSION['loggedin']){
            $_SESSION['report']['type'] = 'article';
            $_SESSION['report']['elemid'] = $id;
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

?>
<!DOCTYPE html>
<html lang="pl">

<head>
    <?php require_once ($_SERVER['DOCUMENT_ROOT'].'/template/head.php'); ?>
    <title><?php echo $articleArray[0]['subject']; ?> - Artykuł <?php echo $setting['domain']; ?></title>
    <meta name="description" content="Artykuł '<?php echo $articleArray[0]['subject']; ?>'. Czytaj w portalu regionalnym <?php echo $setting['domain']; ?>"/>
    <meta name="keywords" content="<?php echo $articleArray[0]['tags']; ?>"/>
    <meta property="og:title"   content="Artykuł <?php echo $articleArray[0]['subject']; ?> - <?php echo $setting['domain']; ?>"/>
    <meta property="og:locale"  content="pl_PL"/>
    <meta property="og:type"    content="article"/>
    <meta property="og:url"     content="<?php echo $actual_link;?>"/>
    <meta property="og:site_name" content="<?php echo $setting['domain']; ?>"/>
    <meta property="og:description" content="<?php echo $articleArray[0]['subject']; ?>"/>
    <meta property="og:image" content="<?php echo 'https://'.$setting['domain'].'/uploads/articles/'.$articleArray[0]['image'];?>"/>
    <meta property="twitter:image" content="<?php echo 'https://'.$setting['domain'].'/uploads/articles/'.$articleArray[0]['image'];?>"/>
</head>

<body class="body">
<div class="page">

    <h1 class="page__heading"><?php echo $setting['title'];?> - portal</h1>

<?php
foreach(getAds('banner-top',$articleArray[0]['category_id']) as $ad){
    $heroList .= html_entity_decode($ad['code']);
}
echo $heroList ;
?>
    <?php require($_SERVER['DOCUMENT_ROOT'] . '/template/nav/nav.php'); ?>
    <div class="content">
        <main class="main">
            <?php
            $articleContent.='<ul class="breadcrumbs list">
                <li class="breadcrumbs__item">
                    <button class="breadcrumbs__link" type="button" data-action="back"
                            title="Wróć na poprzednią stronę">
                        Powrót
                    </button>
                </li>
                <li class="breadcrumbs__item">
                    <a href="/artykuly/" class="breadcrumbs__link" title="Przejdź">
                        Artykuły
                    </a>
                </li>';

            if(!empty($articleArray)){
                $id = (int)htmlspecialchars($_GET['id']);
                $title = htmlspecialchars($_GET['title']);
                foreach(getArticle($id,$title,1) as $article){
                    addArticleView($article['articleId']);
                    $articleContent.='<li class="breadcrumbs__item">
                    <a href="/artykuly/'.$article['categoryNamePL'].'/" class="breadcrumbs__link" title="Przejdź">
                        '.$article['categoryName'].'
                    </a>
                </li>
            </ul>

            <article class="panel">
                    <header class="panel__header">
                    <h2 class="panel__heading panel__heading--main">
                       '.$article['subject'].'
                    </h2>';

                if($article['user_type']!='deleted' && $article['user_type']!='deleted-company'){
                    $articleContent.='
                                        <div class="author">
                   <a href="'.($article['user_type'] === 'user'?'/profil/'.getAuthorLoginPL($article['user_id'],$article['user_type']).','.$article['user_id'].'/':'/firmy/'.getAuthorLogin($article['user_id'],$article['user_type']).','.$article['user_id'].'/').'" class="link" title="Profil autora">'.getAuthorLogin($article['user_id'],$article['user_type']).'</a>
                        <span class="author__date">('.time_elapsed_string($article['created_date']).')</span>.
                        Kategoria:
                        <a href="/artykuly/'.$article['categoryNamePL'].'/" class="link" title="Kategoria">
                            '.$article['categoryName'].'</a>.
                    </div>
                    ';
                }else{
                    $articleContent.='
                                        <div class="author">
                        <span class="bolder">Konto usunięte</span>
                        <span class="author__date">('.time_elapsed_string($article['created_date']).')</span>.
                        Kategoria:
                        <a href="/artykuly/'.$article['categoryNamePL'].'/" class="link" title="Kategoria">
                            '.$article['categoryName'].'</a>.
                    </div>
                    ';
                }
                    $articleContent.='
                </header>

                <div class="panel__txt">
                   '. html_entity_decode($article['content']) .'
                </div>

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

                <ul class="tags-list list">
                ';

                    $tags = explode(',',str_replace(' ','',$article['tags']));
                    $i = 0;
                    foreach($tags as $tag){
                        $tagsPL[]=clean($tag);
                        if(!empty($tag)){
                            $articleContent.='<li class="tags-list__item"><a class="tags-list__link" href="/tag/'.$tagsPL[$i].'/">'.$tag.'</a></li>';
                        }
                        $i++;
                    }


                    $articleContent.='</ul>';
                    $articleContent.=(!empty($rotator[0]['content'])?'<div class="rotate">'.html_entity_decode($rotator[0]['content']).'</div>':'');

                $articleContent.='<div class="actions">
                    <div class="actions__el">
                        <svg class="actions__svg" xmlns="http://www.w3.org/2000/svg"
                             xmlns:xlink="http://www.w3.org/1999/xlink" version="1.1" viewBox="0 0 24 24">
                            <path
                                d="M13.5,8H12V13L16.28,15.54L17,14.33L13.5,12.25V8M13,3A9,9 0 0,0 4,12H1L4.96,16.03L9,12H6A7,7 0 0,1 13,5A7,7 0 0,1 20,12A7,7 0 0,1 13,19C11.07,19 9.32,18.21 8.06,16.94L6.64,18.36C8.27,20 10.5,21 13,21A9,9 0 0,0 22,12A9,9 0 0,0 13,3">
                            </path>
                        </svg>
                        <span class="actions__content">
                                Wyświetlenia:
                            </span>
                        <span class="actions__counter">
                                '.$article['views'].'
                            </span>
                    </div>
                </div>

                <a href="./?zglos" class="button button--secondary" title="Powiadom administrację">Zgłoś uwagę</a>
                            ';



                    $linkedArticles = getLinkedArticles($article['tags'],$article['articleId']);

                    if(!empty($linkedArticles))
                    {
                        $linkedArticlesLoop = $linkedArticles;
                        $titleLinkedSection = 'Powiązane artykuły';
                    }else{
                        $linkedArticlesLoop = getLinkedArticles('',$article['articleId'],true);
                        $titleLinkedSection = 'Przykładowe artykuły';
                    }



                }
                echo $articleContent;
            }else{
                echo 'Brak wymaganych parametrów';
            }
            ?>

            </article>
            <?php

            echo' <section>
                <h2 class="main__heading">'.$titleLinkedSection.'</h2>
                <div class="panel-related__group">';

                    foreach ($linkedArticlesLoop as $linkedArticle){
                        echo '
                    <section class="panel-related">
                        <div class="panel-related__wrapper">
                            <h3 class="panel-related__heading">
                                <a href="/artykuly/'.$linkedArticle['subjectPL'].','.$linkedArticle['articleId'].'/" class="panel-related__link">
                                   '.$linkedArticle['subject'].'
                                </a>
                            </h3>';
                             if($linkedArticle['user_type']!='deleted' && $linkedArticle['user_type']!='deleted-company'){
                    echo '
                        <div class="author">
                   <a href="'.($linkedArticle['user_type'] === 'user'?'/profil/'.getAuthorLoginPL($linkedArticle['user_id'],$linkedArticle['user_type']).','.$linkedArticle['user_id'].'/':'/firmy/'.getAuthorLogin($linkedArticle['user_id'],$linkedArticle['user_type']).','.$linkedArticle['user_id'].'/').'" class="link" title="Profil autora">'.getAuthorLogin($linkedArticle['user_id'],$linkedArticle['user_type']).'</a>
                        <span class="author__date">('.time_elapsed_string($linkedArticle['created_date']).')</span>.
                        Kategoria:
                        <a href="/artykuly/'.$linkedArticle['categoryNamePL'].'/" class="link" title="Kategoria">
                            '.$linkedArticle['categoryName'].'</a>.
                    </div>
                        ';
                }else{
                                 echo '
                    <div class="author">
                        <span class="bolder">Konto usunięte</span>
                        <span class="author__date">('.time_elapsed_string($linkedArticle['created_date']).')</span>.
                        Kategoria:
                        <a href="/artykuly/'.$linkedArticle['categoryNamePL'].'/" class="link" title="Kategoria">
                            '.$linkedArticle['categoryName'].'</a>.
                    </div>
                        ';
                }
                      echo '  
                    </div>
                    </section>';

                    }

   echo ' </div>
    </section>';

            ?>

            <?php
            require_once($_SERVER['DOCUMENT_ROOT'].'/template/loginAndRegister.php');
            ?>
        </main>

        <aside class="sidebar">

 <?php require_once ($_SERVER['DOCUMENT_ROOT'].'/template/sidebar.php');?></aside>


    </div>
    <?php require_once ($_SERVER['DOCUMENT_ROOT'].'/template/footer.php'); ?>
</div>






<?php
foreach(getAds('background',$articleArray[0]['category_id']) as $ad){
    $backgroundsList.=html_entity_decode($ad['code']);
}
echo $backgroundsList;
?>




<?php require_once($_SERVER['DOCUMENT_ROOT'].'/template/after-footer.php'); ?>

</body>

</html>