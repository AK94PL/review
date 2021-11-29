<?php

function addFavorite($advertisementId, $userId){
    $favoriteObj = new Favorite();
    $result = $favoriteObj->addFavorite($advertisementId,$userId);
    return $result;
}

function alreadyFavorite($adveritesementId, $userId){
    $favoriteObj = new Favorite();
    $result = $favoriteObj->alreadyFavorite($adveritesementId,$userId);
    if(!empty($result)){
        return true;
    }else{
        return false;
    }
}

function deleteFavorite($userId,$favoriteId){
    $favoriteObj = new Favorite();
    $result = $favoriteObj->deleteUserFavorite($userId,$favoriteId);
    if(intval($result) > 0){
        $_SESSION['userInfo'] .= '
            <div class="alert alert--success">
                <span class="alert__content">
                  Pomyślnie usunięto ogłoszenie/a z listy obserwowanych.
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

function getUserFavorites($userId,$order,$type){
    $favoriteObj = new Favorite();
    $result = $favoriteObj->getUserFavourites($userId,$order,$type);
    return $result;
}

function getUserFavoritesCount($userId){
    $favoriteObj = new Favorite();
    $result = $favoriteObj->getUserFavourites($userId,null,null);
    return count($result);
}