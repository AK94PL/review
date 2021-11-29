<?php
require_once($_SERVER['DOCUMENT_ROOT'].'/app/init/autoloader.php');
require_once($_SERVER['DOCUMENT_ROOT'].'/app/init/functions.php');
session_start();
if($_SESSION['loggedin']){
    $user = $_SESSION['user'];
}else{
    $user = null;
}

if(isset($_GET['category']) && isCategoryExist(htmlspecialchars($_GET['category']))) {
    $category = htmlspecialchars($_GET['category']);
}



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

$companiesLimit = 20;

?>
<!DOCTYPE html>
<html lang="pl">

<head>
    <?php require_once ($_SERVER['DOCUMENT_ROOT'].'/template/head.php'); ?>
    <title><?php echo $setting['title'];?> katalog firm, baza przedsiębiorców, firmy z miasta i powiatu</title>
    <meta name="keywords" content="Katalog firm, baza firm, przedsiębiorcy, usługi, sklepy, fabryki, zakłady pracy, <?php echo $setting['title'];?>, firmy z powiatu, dodaj firmę"/>
    <meta name="description" content="<?php echo $setting['domain']; ?> to internetowy katalog w którym zamieszczamy wizytówki firm wraz z opisem wykonywanych usług. Portal przedsiębiorców i bogata baza firm działających na rynku całego powiatu."/>
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
        <form method="post" action="/szukaj/firmy/index.php">
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
    $page = (isset($_GET['p'])?(int)htmlspecialchars($_GET['p']):1);
    $city = (isset($_GET['m'])?htmlspecialchars($_GET['m']):null);
    if(!empty($category) && isCategoryExist(htmlspecialchars($category))) {
        $companiesArray = getAllCompaniesFromCategory($city, $category, $page, $companiesLimit);
    }else{
        $companiesArray = getAllCompanies($city,$page,$companiesLimit);
    }
    $companiesCount_Counter = count($companiesArray);
    ?>

    <section>
        <h2 class="main__heading">Znalezione firmy (<?php echo $companiesCount_Counter; ?>)</h2>

            <?php
            echo ($companiesCount_Counter>0?'<div class="panel-fir__group">':'');

            if(!empty($companiesArray)){
                foreach($companiesArray as $company){
                    $companiesList.=' <section class="panel-fir '.($company['promoted']?'panel-fir--pro':'').($company['verificated']?' panel-fir--ver':'').'">
                    <div class="panel-fir__wrapper">
                                   '.($company['promoted']?'<div class="bdg">Wyróżniono</div>':'').'
                        <div class="panel-fir-header">
                            <a class="panel-fir-header__photo"  href="/firmy/'.$company['namePL'].','.$company['companyId'].'/" title="Wyświetl profil">
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
                                '.($company['verificated']?'<div class="ver">
            <svg class="ver__svg" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24">
                <path d="M9,20.42L2.79,14.21L5.62,11.38L9,14.77L18.88,4.88L21.71,7.71L9,20.42Z"></path>
            </svg>
            Profil zweryfikowany
        </div>':'').'
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
            }


            echo $companiesList;
            echo ($companiesCount_Counter>0?'</div>':'');
            ?>

    </section>


<?php
 foreach(getAds('banner-bottom',getCategoryId(htmlspecialchars($category))) as $ad){
                    $betweenList .= html_entity_decode($ad['code']);
                }
                echo $betweenList ;
?>

            <?php
            if(!empty($category) &&  isCategoryExist($category)){
                showCompanyPaginationFromCategory($city,$category,$page,$companiesLimit);
            }else{
                showCompanyPagination($city,$page,$companiesLimit);
            }
            require_once($_SERVER['DOCUMENT_ROOT'] . '/template/loginAndRegister.php');
            ?>

        </main>

        <aside class="sidebar">




            <?php require_once ($_SERVER['DOCUMENT_ROOT'].'/template/sidebar.php');?>
        </aside>
    </div>
    <?php require_once ($_SERVER['DOCUMENT_ROOT'].'/template/footer.php'); ?>
</div>





<?php
foreach(getAds('background',getCategoryId($category)) as $ad){
    $backgroundsList.=html_entity_decode($ad['code']);
}
echo $backgroundsList;
?>




<?php require_once($_SERVER['DOCUMENT_ROOT'].'/template/after-footer.php'); ?>
</body>

</html>