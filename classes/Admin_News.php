<?php

class Admin_News extends Database{
    public function getNewses($conditions,$params){
        $news = $this->select(' DISTINCT news.id, news.id as newsId, news.subject, plSafeChars(news.subject) as subjectPL, news.content, news.tags, news.category_id, news.created_date, news.image, news.views, news.user_id, news.user_type, news.status, news.verificated, news.revision, news.promoted, categories.id, categories.name, categories.name as categoryName, plSafeChars(categories.name) as categoryNamePL ','news, categories, users','  news.category_id = categories.id AND '.$conditions,$params);
        return $news->fetchAll();
    }


    public function updateNews($articleId,$set,$params=array()){
        try{
            $params += [':id'=>$articleId];
            $result = $this->update('news',$set,'news.id = :id ',$params);
            return $result;
        }catch(PDOException $e){
            echo $e->getCode();
            return false;
        }
    }


    public function deleteNews($id){
        $params = array(':id'=>$id);
        $article = $this->delete('news','news.id = :id ',$params);
        return $article;
    }

}
?>