<?php
if(isset($_GET['logout'])){
    $_SESSION['loggedin'] = false;
    header('Location: /');
    $_SESSION['user'] = null;
    session_destroy();
    exit();
}else{
    if(isset($_POST['default_user'])){
        if(!empty($_POST['default_user'])){
            $defaultUser = (int)htmlspecialchars($_POST['default_user']);
        }else{
            $defaultUser = null;
        }
        if(setDefault_User($defaultUser)){
            $_SESSION['userInfo'] = '
             <div class="alert alert--success">
                <span class="alert__content">
                  Pomyślnie zmieniliśmy predefiniowanego autora Twoich treści. Dodajesz teraz jako <strong>'.(is_null($_SESSION['user']->default_author) ? $user->login : getName_Company((int)$_SESSION['user']->default_author)).'</strong>.
                </span>
                <button class="alert-button" type="button" title="Ukryj">
                    <svg class="alert-button__svg" xmlns="http://www.w3.org/2000/svg"
                        xmlns:xlink="http://www.w3.org/1999/xlink" version="1.1" viewBox="0 0 24 24">
                        <path
                            d="M19,6.41L17.59,5L12,10.59L6.41,5L5,6.41L10.59,12L5,17.59L6.41,19L12,13.41L17.59,19L19,17.59L13.41,12L19,6.41Z" />
                    </svg>
                </button>
            </div>';
        }
    }
    $unreadConversations = getUnreadMessages($user->id);

    addUserVisit();
    updateUserData();
}
?>
<div class="nav-user" id="nav-user">
    <div class="nav-user-panel">
        <a href="<?php echo(is_null($_SESSION['user']->default_author)?'/profil/'.$user->loginPL.','.$user->id.'/':'/firmy/'.getName_Company_PL((int)$_SESSION['user']->default_author).','.$_SESSION['user']->default_author.'/');?>" class="user-avatar" title="Sprawdź profil">
            <img class="user-avatar__img" src="<?php echo (is_null($_SESSION['user']->default_author)?((is_null($user->avatar) ? '/assets/img/avatar--nav.png' : '/uploads/avatars/'.$user->avatar) ):(!empty(getLogo_Company((int)$_SESSION['user']->default_author))?'/uploads/companies/'.(getLogo_Company((int)$_SESSION['user']->default_author)):'/assets/img/avatar--nav.png')); ?>" alt="<?php echo $user->login; ?>">
        </a>
        <div class="nav-user__info">
            <div class="dropdown" data-action="dropdown">
                <button type="button" class="dropdown-button">
                    <?php echo(is_null($_SESSION['user']->default_author) ? $user->login : getName_Company((int)$_SESSION['user']->default_author)); ?>
                    <svg class="dropdown-button__svg" xmlns="http://www.w3.org/2000/svg"
                         xmlns:xlink="http://www.w3.org/1999/xlink" version="1.1" viewBox="0 0 24 24">
                        <path d="M7.41,8.58L12,13.17L16.59,8.58L18,10L12,16L6,10L7.41,8.58Z"></path>
                    </svg>
                </button>
                <ul class="dropdown-list dropdown-list--left dropdown-list--lrg list">
                    <form method="post" action="">
                        <li class="dropdown-list__item">
                            <button type="submit" name="default_user" class="dropdown-list__link <?php echo (is_null($_SESSION['user']->default_author)?'active':''); ?>" value="">
                                <svg class="dropdown-list__svg" xmlns="http://www.w3.org/2000/svg"
                                     viewBox="0 0 24 24" >
                                    <path
                                            d="M14.81,12.28a3.73,3.73,0,0,0,1-2.5,3.78,3.78,0,0,0-7.56,0,3.73,3.73,0,0,0,1,2.5A5.94,5.94,0,0,0,6,16.89a1,1,0,0,0,2,.22,4,4,0,0,1,7.94,0A1,1,0,0,0,17,18h.11a1,1,0,0,0,.88-1.1A5.94,5.94,0,0,0,14.81,12.28ZM12,11.56a1.78,1.78,0,1,1,1.78-1.78A1.78,1.78,0,0,1,12,11.56ZM19,2H5A3,3,0,0,0,2,5V19a3,3,0,0,0,3,3H19a3,3,0,0,0,3-3V5A3,3,0,0,0,19,2Zm1,17a1,1,0,0,1-1,1H5a1,1,0,0,1-1-1V5A1,1,0,0,1,5,4H19a1,1,0,0,1,1,1Z" />
                                </svg>
                                <?php echo $user->login; ?>
                            </button>
                        </li>

                        <?php
                        foreach(getUserCompanies($_SESSION['user']->id,1) as $company){
                            echo '
                       <button type="submit" name="default_user" class="dropdown-list__link  '.((intval($_SESSION['user']->default_author) === intval($company['companyId'])) ? 'active' : '').'"  value="'.$company['companyId'].'">
                            <svg class="dropdown-list__svg" xmlns="http://www.w3.org/2000/svg"
                                 viewBox="0 0 24 24">
                                <path
                                    d="M19,6.5H16v-1a3,3,0,0,0-3-3H11a3,3,0,0,0-3,3v1H5a3,3,0,0,0-3,3v9a3,3,0,0,0,3,3H19a3,3,0,0,0,3-3v-9A3,3,0,0,0,19,6.5Zm-9-1a1,1,0,0,1,1-1h2a1,1,0,0,1,1,1v1H10Zm10,13a1,1,0,0,1-1,1H5a1,1,0,0,1-1-1V13a21.71,21.71,0,0,0,8,1.53A21.75,21.75,0,0,0,20,13Zm0-7.69a19.89,19.89,0,0,1-16,0V9.5a1,1,0,0,1,1-1H19a1,1,0,0,1,1,1Z">
                                </path>
                            </svg>
                            '.$company['name'].'
                    </button>
                            
                            ';
                        }
                        echo'
                        <li class="dropdown-list__item">
                                    <a href="/moje-konto/firmy/dodaj/" class="dropdown-list__link">
                                        <svg class="dropdown-list__svg" xmlns="http://www.w3.org/2000/svg"
                                            viewBox="0 0 24 24">
                                            <path
                                                d="M12,2A10,10,0,1,0,22,12,10,10,0,0,0,12,2Zm0,18a8,8,0,1,1,8-8A8,8,0,0,1,12,20Zm4-9H13V8a1,1,0,0,0-2,0v3H8a1,1,0,0,0,0,2h3v3a1,1,0,0,0,2,0V13h3a1,1,0,0,0,0-2Z" />
                                        </svg>
                                        Dodaj firmę
                                    </a>
                                </li>
                        ';
                        ?>
                    </form>
                </ul>
            </div>
            <div class="dropdown" data-action="dropdown">
                <button type="button" class="dropdown-button">

                    <?php echo $user->email; ?>

                    <svg class="dropdown-button__svg" xmlns="http://www.w3.org/2000/svg"
                         xmlns:xlink="http://www.w3.org/1999/xlink" version="1.1" viewBox="0 0 24 24">
                        <path d="M7.41,8.58L12,13.17L16.59,8.58L18,10L12,16L6,10L7.41,8.58Z"></path>
                    </svg>

                </button>
                <ul class="dropdown-list dropdown-list--left list">
                    <li class="dropdown-list__item">
                        <a href="/moje-konto/wiadomosci/" class="dropdown-list__link">
                            <svg class="dropdown-list__svg" xmlns="http://www.w3.org/2000/svg"
                                 viewBox="0 0 24 24">
                                <path
                                        d="M20 2V4L4 8V6H2V18H4V16L6 16.5V18.5C6 20.4 7.6 22 9.5 22S13 20.4 13 18.5V18.3L20 20V22H22V2H20M11 18.5C11 19.3 10.3 20 9.5 20S8 19.3 8 18.5V17L11 17.8V18.5M20 18L4 14V10L20 6V18Z" />
                            </svg>
                            Wiadomości
                        </a>
                    </li>
                    <li class="dropdown-list__item">
                        <a href="/moje-konto/wydarzenia/" class="dropdown-list__link">
                            <svg xmlns="http://www.w3.org/2000/svg" class="dropdown-list__svg" viewBox="0 0 24 24"><path d="M7,12H9V14H7V12M21,6V20A2,2 0 0,1 19,22H5C3.89,22 3,21.1 3,20V6A2,2 0 0,1 5,4H6V2H8V4H16V2H18V4H19A2,2 0 0,1 21,6M5,8H19V6H5V8M19,20V10H5V20H19M15,14V12H17V14H15M11,14V12H13V14H11M7,16H9V18H7V16M15,18V16H17V18H15M11,18V16H13V18H11Z"></path></svg>
                            Wydarzenia
                        </a>
                    </li>
                    <li class="dropdown-list__item">
                        <a href="/moje-konto/artykuly/" class="dropdown-list__link">
                            <svg class="dropdown-list__svg" xmlns="http://www.w3.org/2000/svg"
                                 viewBox="0 0 24 24">
                                <path
                                        d="M18.5 2H5.5C3.6 2 2 3.6 2 5.5V18.5C2 20.4 3.6 22 5.5 22H16L22 16V5.5C22 3.6 20.4 2 18.5 2M20.1 15H18.6C16.7 15 15.1 16.6 15.1 18.5V20H5.8C4.8 20 4 19.2 4 18.2V5.8C4 4.8 4.8 4 5.8 4H18.3C19.3 4 20.1 4.8 20.1 5.8V15M7 7H17V9H7V7M7 11H17V13H7V11M7 15H13V17H7V15Z" />
                            </svg>
                            Artykuły
                        </a>
                    </li>
                    <li class="dropdown-list__item">
                        <a href="/moje-konto/firmy/" class="dropdown-list__link">
                            <svg class="dropdown-list__svg" xmlns="http://www.w3.org/2000/svg"
                                 viewBox="0 0 24 24">
                                <path
                                        d="M10,2H14A2,2 0 0,1 16,4V6H20A2,2 0 0,1 22,8V13.03C21.5,12.23 20.8,11.54 20,11V8H4V19H10.5C10.81,19.75 11.26,20.42 11.81,21H4C2.89,21 2,20.1 2,19V8C2,6.89 2.89,6 4,6H8V4C8,2.89 8.89,2 10,2M14,6V4H10V6H14M20.31,18.9L23.39,22L22,23.39L18.88,20.32C18.19,20.75 17.37,21 16.5,21C14,21 12,19 12,16.5C12,14 14,12 16.5,12C19,12 21,14 21,16.5C21,17.38 20.75,18.21 20.31,18.9M16.5,19A2.5,2.5 0 0,0 19,16.5A2.5,2.5 0 0,0 16.5,14A2.5,2.5 0 0,0 14,16.5A2.5,2.5 0 0,0 16.5,19Z" />
                            </svg>
                            Firma
                        </a>
                    </li>
                    <li class="dropdown-list__item">
                        <a href="/moje-konto/ogloszenia/" class="dropdown-list__link">
                            <svg class="dropdown-list__svg" xmlns="http://www.w3.org/2000/svg"
                                 viewBox="0 0 24 24">
                                <path
                                        d="M21.41 11.58L12.41 2.58A2 2 0 0 0 11 2H4A2 2 0 0 0 2 4V11A2 2 0 0 0 2.59 12.42L11.59 21.42A2 2 0 0 0 13 22A2 2 0 0 0 14.41 21.41L21.41 14.41A2 2 0 0 0 22 13A2 2 0 0 0 21.41 11.58M13 20L4 11V4H11L20 13M6.5 5A1.5 1.5 0 1 1 5 6.5A1.5 1.5 0 0 1 6.5 5Z" />
                            </svg>
                            Ogłoszenia
                        </a>
                    </li>
                    <li class="dropdown-list__item">
                        <a href="/moje-konto/forum/" class="dropdown-list__link">
                            <svg class="dropdown-list__svg" xmlns="http://www.w3.org/2000/svg"
                                 viewBox="0 0 24 24">
                                <path
                                        d="M12,3C6.5,3 2,6.58 2,11C2.05,13.15 3.06,15.17 4.75,16.5C4.75,17.1 4.33,18.67 2,21C4.37,20.89 6.64,20 8.47,18.5C9.61,18.83 10.81,19 12,19C17.5,19 22,15.42 22,11C22,6.58 17.5,3 12,3M12,17C7.58,17 4,14.31 4,11C4,7.69 7.58,5 12,5C16.42,5 20,7.69 20,11C20,14.31 16.42,17 12,17Z" />
                            </svg>
                            Forum
                        </a>
                    </li>


                    <li class="dropdown-list__item">
                        <a href="/moje-konto/" class="dropdown-list__link">
                            <svg class="dropdown-list__svg" xmlns="http://www.w3.org/2000/svg"
                                 viewBox="0 0 24 24">
                                <path
                                        d="M12,8A4,4 0 0,1 16,12A4,4 0 0,1 12,16A4,4 0 0,1 8,12A4,4 0 0,1 12,8M12,10A2,2 0 0,0 10,12A2,2 0 0,0 12,14A2,2 0 0,0 14,12A2,2 0 0,0 12,10M10,22C9.75,22 9.54,21.82 9.5,21.58L9.13,18.93C8.5,18.68 7.96,18.34 7.44,17.94L4.95,18.95C4.73,19.03 4.46,18.95 4.34,18.73L2.34,15.27C2.21,15.05 2.27,14.78 2.46,14.63L4.57,12.97L4.5,12L4.57,11L2.46,9.37C2.27,9.22 2.21,8.95 2.34,8.73L4.34,5.27C4.46,5.05 4.73,4.96 4.95,5.05L7.44,6.05C7.96,5.66 8.5,5.32 9.13,5.07L9.5,2.42C9.54,2.18 9.75,2 10,2H14C14.25,2 14.46,2.18 14.5,2.42L14.87,5.07C15.5,5.32 16.04,5.66 16.56,6.05L19.05,5.05C19.27,4.96 19.54,5.05 19.66,5.27L21.66,8.73C21.79,8.95 21.73,9.22 21.54,9.37L19.43,11L19.5,12L19.43,13L21.54,14.63C21.73,14.78 21.79,15.05 21.66,15.27L19.66,18.73C19.54,18.95 19.27,19.04 19.05,18.95L16.56,17.95C16.04,18.34 15.5,18.68 14.87,18.93L14.5,21.58C14.46,21.82 14.25,22 14,22H10M11.25,4L10.88,6.61C9.68,6.86 8.62,7.5 7.85,8.39L5.44,7.35L4.69,8.65L6.8,10.2C6.4,11.37 6.4,12.64 6.8,13.8L4.68,15.36L5.43,16.66L7.86,15.62C8.63,16.5 9.68,17.14 10.87,17.38L11.24,20H12.76L13.13,17.39C14.32,17.14 15.37,16.5 16.14,15.62L18.57,16.66L19.32,15.36L17.2,13.81C17.6,12.64 17.6,11.37 17.2,10.2L19.31,8.65L18.56,7.35L16.15,8.39C15.38,7.5 14.32,6.86 13.12,6.62L12.75,4H11.25Z" />
                            </svg>
                            Ustawienia
                        </a>
                    </li>
                    <li class="dropdown-list__item dropdown-list__item--divider">
                        <a href="/?logout" class="dropdown-list__link">
                            <svg class="dropdown-list__svg" xmlns="http://www.w3.org/2000/svg"
                                 viewBox="0 0 24 24">
                                <path
                                        d="M18,20V10H6V20H18M18,8A2,2 0 0,1 20,10V20A2,2 0 0,1 18,22H6C4.89,22 4,21.1 4,20V10A2,2 0 0,1 6,8H15V6A3,3 0 0,0 12,3A3,3 0 0,0 9,6H7A5,5 0 0,1 12,1A5,5 0 0,1 17,6V8H18M12,17A2,2 0 0,1 10,15A2,2 0 0,1 12,13A2,2 0 0,1 14,15A2,2 0 0,1 12,17Z" />
                            </svg>
                            Wyloguj
                        </a>
                    </li>
                </ul>
            </div>
        </div>
    </div>
    <div class="notification-group">
        <a href=/moje-konto/rozmowy/ class="notification" title="Rozmowy">
            <svg class="notification__svg" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24">
                <path
                        d="M19,4H5A3,3,0,0,0,2,7V17a3,3,0,0,0,3,3H19a3,3,0,0,0,3-3V7A3,3,0,0,0,19,4ZM5,6H19a1,1,0,0,1,1,1l-8,4.88L4,7A1,1,0,0,1,5,6ZM20,17a1,1,0,0,1-1,1H5a1,1,0,0,1-1-1V9.28l7.48,4.57a1,1,0,0,0,1,0L20,9.28Z" />
            </svg>
            <?php echo ((intval($unreadConversations)>0)?'<span class="notification__counter">'.count($unreadConversations).'</span>':'');?>
            <span class="notification__title">
                            Rozmowy
                        </span>
        </a>
        <a href="/moje-konto/zapisane/" class="notification" title="Obserwowane ogłoszenia">
            <svg class="notification__svg" xmlns="http://www.w3.org/2000/svg"
                 xmlns:xlink="http://www.w3.org/1999/xlink" version="1.1" viewBox="0 0 24 24">
                <path
                        d="M12.1,18.55L12,18.65L11.89,18.55C7.14,14.24 4,11.39 4,8.5C4,6.5 5.5,5 7.5,5C9.04,5 10.54,6 11.07,7.36H12.93C13.46,6 14.96,5 16.5,5C18.5,5 20,6.5 20,8.5C20,11.39 16.86,14.24 12.1,18.55M16.5,3C14.76,3 13.09,3.81 12,5.08C10.91,3.81 9.24,3 7.5,3C4.42,3 2,5.41 2,8.5C2,12.27 5.4,15.36 10.55,20.03L12,21.35L13.45,20.03C18.6,15.36 22,12.27 22,8.5C22,5.41 19.58,3 16.5,3Z" />
            </svg>
            <?php
            $favoriteCount = getUserFavoritesCount($user->id);
            echo (($favoriteCount>0)?'<span class="notification__counter" data-action="counter">'.$favoriteCount.'</span>':'');
            ?>
            <span class="notification__title">
                            Ulubione
                        </span>
        </a>
    </div>
</div>
