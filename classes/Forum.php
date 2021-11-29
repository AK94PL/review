<?php


class Forum extends Database
{

    public function addReply($theard_id,$content,$user_id,$user_type){
        $values = array($theard_id,$content,0,$user_id,$user_type,1);
        $addId = $this->insert('forum_replies','theard_id,content,rate,user_id,user_type,status',$values);
        updateBlockadeEnd($user_id,$user_type);
        return $addId;
    }

    public function addTheard($subject,$content,$tags,$category_id,$user_id,$user_type,$status,$verificated){
        $values = array($subject,$content,$tags,$category_id,$user_id,$user_type,$status,$verificated,0);
        $addId = $this->insert('forum_theards','subject,content,tags,category_id,user_id,user_type,status,verificated,promoted',$values);
        updateBlockadeEnd($user_id,$user_type);
        return $addId;
    }

    public function getTheards($conditions,$params){
        $theards = $this->select(' DISTINCT forum_theards.id as theardId, forum_theards.subject, plSafeChars(forum_theards.subject) as subjectPL, forum_theards.content, forum_theards.tags, plSafeChars(forum_theards.tags) as tagsPL, forum_theards.category_id, forum_theards.views, forum_theards.rate, forum_theards.created_date, forum_theards.user_id, forum_theards.user_type, forum_theards.status, forum_theards.verificated, forum_theards.promoted,  forum_theards.pinned, categories.id, categories.name, categories.name as categoryName,  plSafeChars(categories.name) as categoryNamePL ','forum_theards, categories, users',' forum_theards.category_id = categories.id  '.$conditions,$params);
        return $theards->fetchAll();
    }


    public function getTheards_List($conditions,$params){
        $theards = $this->select(' DISTINCT forum_theards.id as theardId, forum_theards.subject, plSafeChars(forum_theards.subject) as subjectPL, forum_theards.views, forum_theards.rate, forum_theards.created_date, forum_theards.user_id, forum_theards.user_type, forum_theards.status, forum_theards.promoted, forum_theards.pinned, categories.name, categories.name as categoryName,  plSafeChars(categories.name) as categoryNamePL ','forum_theards, categories',' forum_theards.category_id = categories.id  '.$conditions,$params);
        return $theards->fetchAll();
    }


    public function getReply($conditions,$params){
        $theards = $this->select(' DISTINCT  forum_replies.* ','forum_replies, forum_theards', 'forum_replies.theard_id = forum_theards.id  '.$conditions,$params);
        return $theards->fetchAll();
    }

    public function getTheardsCount($conditions,$params){
        $theards = $this->select(' DISTINCT forum_theards.id as theardId, forum_theards.subject, forum_theards.content, forum_theards.tags, forum_theards.category_id, forum_theards.views, forum_theards.rate, forum_theards.created_date, forum_theards.user_id, forum_theards.user_type, forum_theards.status, categories.id, categories.name ','forum_theards, categories',' forum_theards.category_id = categories.id  '.$conditions,$params);
        return count($theards->fetchAll());
    }

    public function getTheardsReplies($conditions,$params){
        $theards = $this->select(' DISTINCT forum_replies.*, forum_replies.id as replyId, forum_theards.id, categories.id, categories.name as categoryName, plSafeChars(categories.name) as categoryNamePL','forum_replies, forum_theards, categories','forum_replies.theard_id = forum_theards.id AND forum_theards.category_id = categories.id  '.$conditions,$params);
        return $theards->fetchAll();
    }

    public function getTheardsReplies_Count($theardId){
        $params = array(':theardId'=>$theardId,':status'=>1);
        $theards = $this->select(' COUNT(forum_replies.id) as repliesCount','forum_replies','forum_replies.theard_id = :theardId AND forum_replies.status = :status',$params);
        return $theards->fetchAll();
    }

    public function alreadyVoted($userId, $theardId, $rate=null){
        $params = array(':theardId'=>$theardId, ':userId'=>$userId);
        if($rate!=null){
            $conditions =  ' AND rate = :rate ';
            $params +=[':rate'=>$rate];
        }
        $theards = $this->select('forum_theards_rates.*','forum_theards_rates','forum_theards_rates.theard_id = :theardId AND forum_theards_rates.user_id = :userId '.$conditions,$params);
        return $theards->fetchAll();
    }


    public function alreadyReplyVoted($userId, $replyId, $rate=null){
        $params = array(':replyId'=>$replyId, ':userId'=>$userId);
        if($rate!=null){
            $conditions =  ' AND rate = :rate ';
            $params +=[':rate'=>$rate];
        }
        $theards = $this->select('forum_replies_rates.*','forum_replies_rates','forum_replies_rates.reply_id = :replyId AND forum_replies_rates.user_id = :userId '.$conditions,$params);
        return $theards->fetchAll();
    }


    public function deleteAlreadyVoted($userId,$theardId){
        $params = array(':userId'=>$userId,':theardId'=>$theardId);
        $deleteVoted = $this->delete('forum_theards_rates',' user_id = :userId AND theard_id = :theardId',$params);
        return $deleteVoted;
    }

    public function deleteReplyAlreadyVoted($userId,$replyId){
        $params = array(':userId'=>$userId,':replyId'=>$replyId);
        $deleteVoted = $this->delete('forum_replies_rates',' user_id = :userId AND reply_id = :replyId',$params);
        return $deleteVoted;
    }

    public function unrateTheard($theardId, $rate, $userId){
        $params = array(':id'=>$theardId);
        if($rate != '-1'){
            $rate = '-1';
        }else{
            $rate = '+1';
        }
        $changeVote = $this->update('forum_theards',' rate = rate '.$rate, 'id = :id',$params);
        $deleteVote = self::deleteAlreadyVoted($userId, $theardId);
        if($changeVote && $deleteVote){
            return true;
        }else{
            return false;
        }
    }

    public function unrateReply($replyId, $rate, $userId){
        $params = array(':id'=>$replyId);
        if($rate != '-1'){
            $rate = '-1';
        }else{
            $rate = '+1';
        }
        $changeVote = $this->update('forum_replies',' rate = rate '.$rate, 'id = :id',$params);
        $deleteVote = self::deleteReplyAlreadyVoted($userId, $replyId);
        if($changeVote && $deleteVote){
            return true;
        }else{
            return false;
        }
    }

    public function rateTheard($theardId, $rate, $userId){
        if(!isLoggedIn()){
            return false;
        }else{
            $alreadyVoted = self::alreadyVoted($userId,$theardId);
        if(!is_null($alreadyVoted[0]['rate'])){
            if($alreadyVoted[0]['rate'] === $rate){
                $_SESSION['userInfo'] .= '
            <div class="alert alert--success">
                <span class="alert__content">
                  Pomyślnie wycofano Twoją ocenę.
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
                self::unrateTheard($theardId,$rate,$userId);
                return false;
            }else{
                $params = array(':id'=>$theardId);
                self::deleteAlreadyVoted($userId,$theardId);
                $this->update('forum_theards',' rate = rate '.$rate, 'id = :id',$params);
            }
        }

        $params = array(':id'=>$theardId);
        $vote = $this->update('forum_theards',' rate = rate '.$rate, 'id = :id',$params);
        $values =  array($theardId,$userId,$rate);
        $addVote = $this->insert('forum_theards_rates','theard_id, user_id, rate',$values);
        if($vote && ($addVote>0)){
            $_SESSION['userInfo'] .= '
            <div class="alert alert--success">
                <span class="alert__content">
                  Pomyślnie oceniono wątek.
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
            return false;
        }
    }
    }


    public function rateReply($replyId, $rate, $userId){
        if(!isLoggedIn()){
            return false;
        }else{
            $alreadyVoted = self::alreadyReplyVoted($userId,$replyId);
            if(!is_null($alreadyVoted[0]['rate'])){
                if($alreadyVoted[0]['rate'] === $rate){
                    $_SESSION['userInfo'] .= '
            <div class="alert alert--success">
                <span class="alert__content">
                  Pomyślnie wycofano Twoją ocenę.
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
                    self::unrateReply($replyId,$rate,$userId);
                    return false;
                }else{
                    $params = array(':id'=>$replyId);
                    self::deleteReplyAlreadyVoted($userId,$replyId);
                    $this->update('forum_replies',' rate = rate '.$rate, 'id = :id',$params);
                }
            }

            $params = array(':id'=>$replyId);
            $vote = $this->update('forum_replies',' rate = rate '.$rate, 'id = :id',$params);
            $values =  array($replyId,$userId,$rate);
            $addVote = $this->insert('forum_replies_rates','reply_id, user_id, rate',$values);
            if($vote && ($addVote>0)){
                $_SESSION['userInfo'] .= '
            <div class="alert alert--success">
                <span class="alert__content">
                  Pomyślnie oceniono odpowiedź.
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
                return false;
            }
        }
    }


    public function addView($theardId){
        $params = array(':id'=>$theardId);
        $result = $this->update('forum_theards', 'views = views + 1',' forum_theards.id = :id ',$params);
        return $result;
    }

    public function updateContent_Reply($replyId,$newContent){
        $params = array(':id'=>$replyId, ':content'=>$newContent);
        $result = $this->update('forum_replies', 'content = :content ',' forum_replies.id = :id ',$params);
        return $result;
    }

    public function updateContent_Theard($theardId,$newContent){
        $params = array(':id'=>$theardId, ':content'=>$newContent);
        $result = $this->update('forum_theards', 'content = :content ',' forum_theards.id = :id ',$params);
        return $result;
    }
}