<?php


class Article extends Database
{
    public function addArticle($subject,$content,$tags,$category_id,$image,$user_id,$user_type,$status,$verificated){
        $values = array($subject,$content,$tags,$category_id,$image,$user_id,$user_type,$status,$verificated,0);
        $addId = $this->insert('articles','subject,content,tags,category_id,image,user_id,user_type,status,verificated,promoted',$values);
        if((int)$addId>0){
            updateBlockadeEnd($user_id,$user_type,'reset');
        }
        return $addId;
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


    public function getArticles($conditions,$params){
        $articles = $this->select(' DISTINCT articles.id, articles.id as articleId, articles.subject, plSafeChars(articles.subject) as subjectPL, articles.content, articles.tags, plSafeChars(articles.tags) as tagsPL, articles.category_id, articles.created_date, articles.image, articles.views, articles.user_id, articles.user_type, articles.status, articles.verificated, articles.promoted, articles.revision, categories.id, categories.name, categories.name as categoryName, plSafeChars(categories.name) as categoryNamePL ','articles, categories, users','  articles.category_id = categories.id AND '.$conditions,$params);
        return $articles->fetchAll();
    }

    public function getArticlesCount($conditions,$params){
        $articles = $this->select(' DISTINCT articles.id, articles.subject, articles.content, articles.tags, articles.category_id, articles.created_date, articles.image, articles.views, articles.user_id, articles.user_type, articles.status, categories.id, categories.name ','articles, categories',' articles.category_id = categories.id  AND '.$conditions,$params);
        return count($articles->fetchAll());
    }

    public function addView($articleId){
        $params = array(':id'=>$articleId);
        $result = $this->update('articles', 'views = views + 1',' articles.id = :id ',$params);
        return $result;
    }





}