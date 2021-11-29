<?php
if(!isLoggedIn()){
    echo '
                <div class="panel panel--slim">
                <div class="info">
                    <div class="info__el">
                            <span class="info__title">
                                Rozpocznij dodawanie własnych treści
                            </span>
                    </div>
                    <div class="info__el">
                        <div class="info-group">
                            <div class="info-group__el">
                                <a href="/logowanie/" class="button" title="Przejdź do logowania">Logowanie</a>
                            </div>
                            <div class="info-group__el">
                                <a href="/rejestracja/" class="button button--color"
                                   title="Przejdź do rejestracji">Rejestracja</a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
                ';
}
?>