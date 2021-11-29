<a href="<?php echo ($_SESSION['loggedin'])?'/moje-konto/dodaj/':'/logowanie/';?>" class="fab" title="Dodaj treści na stronę">

    <svg class="fab__svg" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink"

         version="1.1" viewBox="0 0 24 24">

        <path d="M19,13H13V19H11V13H5V11H11V5H13V11H19V13Z" /></svg>

    <span class="fab__title">

                        Dodaj

                    </span>
</a>
<?php

if(isset($_GET['category'])){
    $categoryName = htmlspecialchars($_GET['category']);
    $categoryId = getCategoryId($categoryName);
}else{
    if($submodule === 'events'){
        if(!empty($eventArray)){
            $categoryId = $eventArray[0]['category_id'];
        }
    }elseif($submodule === 'articles'){
        if(!empty($articleArray)){
            $categoryId = $articleArray[0]['category_id'];
        }
    }elseif($submodule === 'news'){
        if(!empty($articleArray)){
            $categoryId = $articleArray[0]['category_id'];
        }
    }elseif($submodule === 'companies'){
        if(!empty($companyArray)){
            $categoryId = $companyArray[0]['category_id'];
        }
    }elseif($submodule === 'advertisements'){
        if(!empty($advertsArray)){
            $categoryId = $advertsArray[0]['category_id'];
        }
    }elseif($submodule === 'forums'){
        if(!empty($theardsArray)){
            $categoryId = $theardsArray[0]['category_id'];
        }
    }
}

foreach(getAds('sidebar-text',$categoryId) as $ad_text){
    $sidebarText.= html_entity_decode($ad_text['code']);
}
echo $sidebarText;
?>
<?php require_once ($_SERVER['DOCUMENT_ROOT'].'/template/categories/categories-list-sidebar.php');?>

<?php
foreach(getAds('sidebar-static',$categoryId) as $ad_static){
    $sidebarStatic.=html_entity_decode($ad_static['code']);
}
 echo $sidebarStatic;
?>


<?php

foreach(getAds('sidebar-sticky',$categoryId) as $ad_sticky){
    $sidebarSticky.=html_entity_decode($ad_sticky['code']);
}
 echo $sidebarSticky;

?>
