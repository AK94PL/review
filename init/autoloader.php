<?php
if(!isset($_COOKIE['DarkMode'])){
    setcookie("DarkMode", "off", 2147483647,"/");
}else{
    if(isset($_COOKIE['DarkMode']) && $_COOKIE['DarkMode'] === 'on'){
        $styleMode = '<link rel="stylesheet" href="/assets/css/dark-theme.css">';
        $themeModeStatus = 'checked';
    }else{
        $styleMode = '<link rel="stylesheet" href="/assets/css/light-theme.css">';
        $themeModeStatus = '';
    }
}

function autoloader($className){
    include_once($_SERVER['DOCUMENT_ROOT'].'/app/classes/'.$className . '.php');
}

spl_autoload_register('autoloader');
?>