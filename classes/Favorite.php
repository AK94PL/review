<?php


class Favorite extends Database
{
    public function alreadyFavorite($advertisementId, $userId){
        $params = array(':advId'=>$advertisementId, ':userId'=>$userId);

        $favorites = $this->select('favorites.*','favorites','favorites.advertisement_id = :advId AND favorites.user_id = :userId ',$params);
        return $favorites->fetchAll();
    }

    public function deleteAlreadyFavorite($advertisementId, $userId){
        $params = array(':userId'=>$userId,':advId'=>$advertisementId);
        $deleteFavorite = $this->delete('favorites',' user_id = :userId AND advertisement_id = :advId',$params);
        return $deleteFavorite;
    }

    public function deleteUserFavorite($userId,$favoriteId){
        $params = array(':userId'=>$userId);
        if(!is_null($favoriteId)){
            $params += [':favId'=>$favoriteId];
        }
        return $this->delete('favorites',' user_id = :userId '.((!is_null($favoriteId))?' AND id = :favId ':''),$params);
    }

    public function addFavorite($advertisementId, $userId){
//        echo 'Id: '.$advertisementId.' user:'.$userId.'<br/>';
        if(!isLoggedIn()){
            return false;
        }else{
            if(!empty(self::alreadyFavorite($advertisementId,$userId))){
                self::deleteAlreadyFavorite($advertisementId,$userId);
                $_SESSION['userInfo'] .= '
            <div class="alert alert--success">
                <span class="alert__content">
                   Usunięto wskazane ogłoszenie z listy obserwowanych.
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
            }else{
                $values =  array($advertisementId,$userId);
                $addFavorite = $this->insert('favorites','advertisement_id, user_id',$values);
                if($addFavorite>0){
                    $_SESSION['userInfo'] .= '
            <div class="alert alert--success">
                <span class="alert__content">
                  Dodano ogłoszenie do listy obserwowanych. <a href="/moje-konto/zapisane/">Przejdź do listy</a>.
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
                    return true;
                }else{
                    $_SESSION['userInfo'] .= '
            <div class="alert alert--danger">
                <span class="alert__content">
                   Wystąpił błąd podczas dodawania ogłoszenia do listy obserwowanych.
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
                    return false;
                }
            }
        }
    }

    public function getUserFavourites($userId,$order = null,$type = null){
        $params = array(':userId'=>$userId);
        if(!is_null($type)){
            $params +=[':type'=>$type];
        }
        $favorites = $this->select('favorites.*, favorites.id as favId, advertisements_types.*, advertisements.id, advertisements.type_id, advertisements.price','favorites, advertisements, advertisements_types',' favorites.user_id = :userId AND favorites.advertisement_id = advertisements.id AND advertisements.type_id = advertisements_types.id '.(!is_null($type)?' AND advertisements_types.type = :type ':'').(!is_null($order)?' ORDER BY '.$order:''),$params);
        return $favorites->fetchAll();
    }

}