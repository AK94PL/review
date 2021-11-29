<?php

class Category extends Database{
    public function getCategories($conditions=null,$params=null){
        $categories = $this->select('id, name, plSafeChars(name) as namePL,  icon','categories', $conditions, $params);
        return $categories->fetchAll();
    }

    public function getElementsCountInCategory($elementType,$conditions=null,$params=null){
            $what =  $elementType.'.id';
            $from = $elementType;
        $elements = $this->select($what,$from,$conditions,$params);
        return $elements->fetchAll();
}

}

