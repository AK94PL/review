<div class="sidebar__el">
    <h2 class="sidebar__heading">Kategorie</h2>
    <button class="categories-menu-button" id="categories-menu-button" title="Pokaż kategorie">
        <svg class="categories-menu-button__svg" viewBox="0 0 24 24">
            <path d="M15.41,16.58L10.83,12L15.41,7.41L14,6L8,12L14,18L15.41,16.58Z" />
        </svg>
    </button>
    <ul class="categories-menu list" id="categories-menu">

        <?php
        if(strpos($_SERVER['REQUEST_URI'], 'wydarzenia')){
            $module = 'wydarzenia';
        }elseif(strpos($_SERVER['REQUEST_URI'], 'artykuly')){
            $module = 'artykuly';
        }elseif(strpos($_SERVER['REQUEST_URI'], 'wiadomosci')){
            $module = 'wiadomosci';
        }elseif(strpos($_SERVER['REQUEST_URI'], 'firmy')){
            $module = 'firmy';
        }elseif(strpos($_SERVER['REQUEST_URI'], 'ogloszenia')){
            $module = 'ogloszenia';
        }elseif(strpos($_SERVER['REQUEST_URI'], 'forum')){
            $module = 'forum';
        }else{
            $module = 'kategoria';
        }

        foreach (getCategories() as $categoryElem){
            echo '        
        <li class="categories-menu__item">
            <a href="/'.$module.($categoryElem['namePL']!='wszystkie-kategorie'?'/'.$categoryElem['namePL'].'/':'/').'" class="categories-menu__link">
                <span class="icon '.$categoryElem['icon'].'">'.$categoryElem['name'].'</span>
            </a>
        </li>';
        }
        ?>
    </ul>
</div>