<?php


class News extends Database
{
    public function addNews($subject,$content,$tags,$category_id,$image,$user_id,$user_type,$status,$verificated){
        $values = array($subject,$content,$tags,$category_id,$image,$user_id,$user_type,$status,$verificated,0);
        $addId = $this->insert('news','subject,content,tags,category_id,image,user_id,user_type,status,verificated,promoted',$values);
        if((int)$addId>0){
            updateBlockadeEnd($user_id,$user_type,'reset');
        }
        return $addId;
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


    public function getNewses($conditions,$params){
        $news = $this->select(' DISTINCT news.id, news.id as newsId, news.subject, plSafeChars(news.subject) as subjectPL, news.content, news.tags, plSafeChars(news.tags) as tagsPL, news.category_id, news.created_date, news.image, news.views, news.user_id, news.user_type, news.status, news.verificated, news.promoted, news.revision, categories.id, categories.name, categories.name as categoryName, plSafeChars(categories.name) as categoryNamePL ','news, categories, users','  news.category_id = categories.id AND '.$conditions,$params);
        return $news->fetchAll();
    }

    public function getNewsesCount($conditions,$params){
        $news = $this->select(' DISTINCT news.id, news.subject, news.content, news.tags, news.category_id, news.created_date, news.image, news.views, news.user_id, news.user_type, news.status, categories.id, categories.name ','news, categories',' news.category_id = categories.id  AND '.$conditions,$params);
        return count($news->fetchAll());
    }

    public function addView($articleId){
        $params = array(':id'=>$articleId);
        $result = $this->update('news', 'views = views + 1',' news.id = :id ',$params);
        return $result;
    }





}