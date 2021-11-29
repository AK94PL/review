<div class="categories-checkbox__wrapper">
                            <span class="help">
                                Ogranicz wyszukiwanie do wybranych kategorii:
                            </span>
    <ul class="categories-checkbox-list list">

        <?php
        if(strpos($_SERVER['REQUEST_URI'], 'wydarzenia')){
            $moduleSearch = 'events';
        }elseif(strpos($_SERVER['REQUEST_URI'], 'artykuly')){
            $moduleSearch = 'articles';
        }elseif(strpos($_SERVER['REQUEST_URI'], 'firmy')){
            $moduleSearch = 'companies';
        }elseif(strpos($_SERVER['REQUEST_URI'], 'ogloszenia')){
            $moduleSearch = 'advertisements';
        }elseif(strpos($_SERVER['REQUEST_URI'], 'forum')){
            $moduleSearch = 'forum_theards';
        }else{
            $moduleSearch = 'kategoria';
        }
            foreach(getCategories() as $category){
                $list.= '        
        <li class="categories-checkbox-list__item">
            <input class="categories-checkbox-list__input" type="checkbox" id="'.$category['id'].'">
            <label for="'.$category['id'].'" class="categories-checkbox-list__label">
                                        <span class="categories-checkbox-list__title icon '.$category['icon'].'">
                                           '.$category['name'].' (wyśw.:'.$ad['views'].', limit: '.((int)$ad['views_limit']>0?$ad['views_limit']:0).')
                                    </span>
                <span class="categories-checkbox-list__counter">
                                            ';
$list.=getElementsCountInCategory($category['id'],$moduleSearch);
                       $list.='                 </span>
            </label>
        </li>
        ';
            }

            echo $list;
        ?>
    </ul>
</div>