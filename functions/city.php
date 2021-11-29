<?php

function getCities(){
    $citiesObj = new City();
   $result = $citiesObj->getCities();
   return $result->fetchAll();
}