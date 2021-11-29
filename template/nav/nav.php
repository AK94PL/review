

<nav class="nav">
    <div class="nav-brand">
            <a href="/" class="nav-brand__link" title="<?php echo $setting['title'];?>">
                <?php
                $domainSplit = explode('.',$setting['domain']);
                $i=0;
                foreach ($domainSplit as $ds){
                    if($i === 0){
                        $domainName.=$ds;
                    }else{
                        $domainSubdomain .= '.'.$ds;

                    }
                    $i++;
                }
                //$domainText = $domainName.'<span class="nav-brand__suffix">'.$domainSubdomain.'</span>';
                //echo $domainText;
                if(!empty($themeModeStatus)){
                    $themeLogo = '--dark';
                }else{
                    $themeLogo = '--light';
                }
                if($domainName=='test'){
                    $domainName = 'belchatow';
                    $domainSubdomain = '.net';
                }
                $domainLogo =  '<img class="nav-brand__logo" src="/assets/img/logo/'.strtolower(clean($domainName)).$themeLogo.'.svg" alt="'.$setting['title'].'">';
                echo $domainLogo;
                ?>
            </a>
        <button class="nav-brand__button" id="nav-brand__button" title="Otwórz menu">
            <svg class="nav-brand__svg" xmlns="http://www.w3.org/2000/svg"
                 xmlns:xlink="http://www.w3.org/1999/xlink" version="1.1" viewBox="0 0 24 24">
                <path d="M3,6H21V8H3V6M3,11H21V13H3V11M3,16H21V18H3V16Z" /></svg>
        </button>
    </div>
    <?php

        if($_SESSION['loggedin']){
            require_once ($_SERVER['DOCUMENT_ROOT'].'/template/nav/nav-logged-in.php');
        }else{
            require_once ($_SERVER['DOCUMENT_ROOT'].'/template/nav/nav-logged-out.php');
        }
    if(strpos($_SERVER['REQUEST_URI'], 'wydarzenia')){
        $actually = 'wydarzenia';
    }elseif(strpos($_SERVER['REQUEST_URI'], 'wiadomosci')){
        $actually = 'wiadomosci';
    }elseif(strpos($_SERVER['REQUEST_URI'], 'artykuly')){
        $actually = 'artykuly';
    }elseif(strpos($_SERVER['REQUEST_URI'], 'firmy')){
        $actually = 'firmy';
    }elseif(strpos($_SERVER['REQUEST_URI'], 'ogloszenia')){
        $actually = 'ogloszenia';
    }elseif(strpos($_SERVER['REQUEST_URI'], 'forum')){
        $actually = 'forum';
    }elseif($_SERVER['REQUEST_URI'] == '/'){

        $actually = 'glowna';
    }
    ?>
    <ul class="nav-list list" id="nav-list">
        <li class="nav-list__item">
            <a href="/" class="nav-list__link <?php echo ($actually === 'glowna'?'active':'') ?> ">
                <svg class="nav-list__svg" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24">
                    <path d="M14,10H2V12H14V10M14,6H2V8H14V6M2,16H10V14H2V16M21.5,11.5L23,13L16,20L11.5,15.5L13,14L16,17L21.5,11.5Z" />
                </svg>
                Start
            </a>
        </li>
        <li class="nav-list__item">
            <a href="/wiadomosci/" class="nav-list__link  <?php echo ($actually === 'wiadomosci'?'active':'') ?>">
                <svg class="nav-list__svg" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24">
                    <path
                            d="M20 2V4L4 8V6H2V18H4V16L6 16.5V18.5C6 20.4 7.6 22 9.5 22S13 20.4 13 18.5V18.3L20 20V22H22V2H20M11 18.5C11 19.3 10.3 20 9.5 20S8 19.3 8 18.5V17L11 17.8V18.5M20 18L4 14V10L20 6V18Z" />

                </svg>
                Wiadomości
            </a>
        </li>
        <li class="nav-list__item">
            <a href="/wydarzenia/" class="nav-list__link  <?php echo ($actually === 'wydarzenia'?'active':'') ?>">
                <svg xmlns="http://www.w3.org/2000/svg" class="nav-list__svg" viewBox="0 0 24 24"><path
                            d="M7,12H9V14H7V12M21,6V20A2,2 0 0,1 19,22H5C3.89,22 3,21.1 3,20V6A2,2 0 0,1 5,4H6V2H8V4H16V2H18V4H19A2,2 0 0,1 21,6M5,8H19V6H5V8M19,20V10H5V20H19M15,14V12H17V14H15M11,14V12H13V14H11M7,16H9V18H7V16M15,18V16H17V18H15M11,18V16H13V18H11Z" />
                </svg>
                Wydarzenia
            </a>
        </li>
        <li class="nav-list__item">
            <a href="/artykuly/" class="nav-list__link <?php echo ($actually === 'artykuly'?'active':'') ?>">
                <svg class="nav-list__svg" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24">
                    <path
                            d="M18.5 2H5.5C3.6 2 2 3.6 2 5.5V18.5C2 20.4 3.6 22 5.5 22H16L22 16V5.5C22 3.6 20.4 2 18.5 2M20.1 15H18.6C16.7 15 15.1 16.6 15.1 18.5V20H5.8C4.8 20 4 19.2 4 18.2V5.8C4 4.8 4.8 4 5.8 4H18.3C19.3 4 20.1 4.8 20.1 5.8V15M7 7H17V9H7V7M7 11H17V13H7V11M7 15H13V17H7V15Z" />
                </svg>
                Artykuły
            </a>
        </li>
        <li class="nav-list__item">
            <a href="/firmy/" class="nav-list__link <?php echo ($actually === 'firmy'?'active':'') ?>">
                <svg class="nav-list__svg" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24">
                    <path
                            d="M10,2H14A2,2 0 0,1 16,4V6H20A2,2 0 0,1 22,8V13.03C21.5,12.23 20.8,11.54 20,11V8H4V19H10.5C10.81,19.75 11.26,20.42 11.81,21H4C2.89,21 2,20.1 2,19V8C2,6.89 2.89,6 4,6H8V4C8,2.89 8.89,2 10,2M14,6V4H10V6H14M20.31,18.9L23.39,22L22,23.39L18.88,20.32C18.19,20.75 17.37,21 16.5,21C14,21 12,19 12,16.5C12,14 14,12 16.5,12C19,12 21,14 21,16.5C21,17.38 20.75,18.21 20.31,18.9M16.5,19A2.5,2.5 0 0,0 19,16.5A2.5,2.5 0 0,0 16.5,14A2.5,2.5 0 0,0 14,16.5A2.5,2.5 0 0,0 16.5,19Z" />
                </svg>
                Firmy
            </a>
        </li>
        <li class="nav-list__item">
            <a href="/ogloszenia/" class="nav-list__link <?php echo ($actually === 'ogloszenia'?'active':'') ?>">
                <svg class="nav-list__svg" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24">
                    <path
                            d="M21.41 11.58L12.41 2.58A2 2 0 0 0 11 2H4A2 2 0 0 0 2 4V11A2 2 0 0 0 2.59 12.42L11.59 21.42A2 2 0 0 0 13 22A2 2 0 0 0 14.41 21.41L21.41 14.41A2 2 0 0 0 22 13A2 2 0 0 0 21.41 11.58M13 20L4 11V4H11L20 13M6.5 5A1.5 1.5 0 1 1 5 6.5A1.5 1.5 0 0 1 6.5 5Z" />
                </svg>
                Ogłoszenia
            </a>
        </li>
        <li class="nav-list__item">
            <a href="/forum/" class="nav-list__link <?php echo ($actually === 'forum'?'active':'') ?>">
                <svg class="nav-list__svg" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24">
                    <path
                            d="M12,3C6.5,3 2,6.58 2,11C2.05,13.15 3.06,15.17 4.75,16.5C4.75,17.1 4.33,18.67 2,21C4.37,20.89 6.64,20 8.47,18.5C9.61,18.83 10.81,19 12,19C17.5,19 22,15.42 22,11C22,6.58 17.5,3 12,3M12,17C7.58,17 4,14.31 4,11C4,7.69 7.58,5 12,5C16.42,5 20,7.69 20,11C20,14.31 16.42,17 12,17Z" />
                </svg>
                Forum
            </a>
        </li>
        <?php
        foreach(getAds('navigation',getCategoryId(htmlspecialchars($_GET['category']))) as $ad){
            $navList.=html_entity_decode($ad['code']);
        }
        echo $navList;
        ?>
    </ul>
</nav>
