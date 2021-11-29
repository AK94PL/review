<?php

class Admin_Article extends Database{
    public function getArticles($conditions,$params){
        $articles = $this->select(' DISTINCT articles.id, articles.id as articleId, articles.subject, plSafeChars(articles.subject) as subjectPL, articles.content, articles.tags, articles.category_id, articles.created_date, articles.image, articles.views, articles.user_id, articles.user_type, articles.status, articles.verificated, articles.revision, articles.promoted, categories.id, categories.name, categories.name as categoryName, plSafeChars(categories.name) as categoryNamePL ','articles, categories, users','  articles.category_id = categories.id AND '.$conditions,$params);
        return $articles->fetchAll();
    }


    public function updateArticle($articleId,$set,$params=array()){
        try{
            $params += [':id'=>$articleId];
            $result = $this->update('articles',$set,'articles.id = :id ',$params);
            return $result;
        }catch(PDOException $e){
            echo $e->getCode();
            return false;
        }
    }


    public function deleteArticle($id){
        $params = array(':id'=>$id);
        $article = $this->delete('articles','articles.id = :id ',$params);
        return $article;
    }

}
?>