<?php


class Company extends Database
{
    public function getCompanies($conditions,$params){
        $companies = $this->select(' companies.*, plSafeChars(companies.keywords) as tagsPL, companies.id as companyId, plSafeChars(companies.name) as namePL, categories.name as categoryName, plSafeChars(categories.name) as categoryNamePL, plSafeChars(companies.city) as cityPL, companies.verificated, companies.show_description, companies.show_hours, users.login, users.email ','companies, users, categories',' companies.user_id = users.id AND companies.category_id = categories.id  '.$conditions,$params);
        return $companies->fetchAll();
    }

    public function getAllCompanies($conditions,$params){
        $companies = $this->select(' companies.*','companies',$conditions,$params);
        return $companies->fetchAll();
    }


    public function updateCompany($companyId,$set,$params=array())
    {
        try {
            $params += [':id'=>$companyId];
            $params += [':userId'=>$_SESSION['user']->id];
            $result = $this->update('companies',$set,'companies.id = :id AND companies.user_id = :userId ',$params);
            updateUserData();
            return $result;
        }catch(PDOException $e){
            echo $e->getCode();
            return false;
        }

    }

    public function deleteCompany($companyId){

        $paramsUp = array(':uid'=>$companyId, ':userType'=>'company',':userTypeNew'=>'deleted-company');
        $paramsDel = array(':uid'=>$companyId, ':userId'=>$_SESSION['user']->id);

        $this->update('articles',' user_type=:userTypeNew ',' articles.user_type=:userType AND articles.user_id = :uid ',$paramsUp);

        $this->update('events',' user_type=:userTypeNew ',' events.user_type=:userType AND events.user_id = :uid ',$paramsUp);
        deleteCompanyAdv($companyId);
        $this->update('forum_theards',' user_type=:userTypeNew ',' forum_theards.user_type=:userType AND forum_theards.user_id = :uid ',$paramsUp);
        $this->update('forum_replies',' user_type=:userTypeNew ',' forum_replies.user_type=:userType AND forum_replies.user_id = :uid ',$paramsUp);


        $conditions = ' companies.id = :uid AND companies.user_id = :userId';
        $deleteCompany = $this->delete('companies',$conditions,$paramsDel);
        if(count($deleteCompany)>0){
            return true;
        }else{
            return false;
        }

    }

    public function getCompaniesCount($conditions = null, $params = null){
        $companies = $this->select('companies.*, categories.id, categories.name as categoryName, plSafeChars(categories.name) as categoryNamePL, companies.verificated, users.login ','companies, users, categories',' companies.user_id = users.id AND companies.category_id = categories.id '.$conditions,$params);
        return count($companies->fetchAll());
    }

    public function getCompanyContent($uid,$contentType){
        $pdo = new Database();
        $params = array(':uid'=>$uid);
        $stmt = $pdo->select($contentType.'.*',$contentType,$contentType.'.user_id = :uid AND user_type = "company" ',$params);
        return $stmt->fetchAll();
    }

    public function addView($companyId){
        $params = array(':companyId'=>$companyId);
        $this->update('companies',' views = views + 1',' companies.id = :companyId ',$params);
    }

    public function addCompany($userId,$name,$nip,$city,$categoryId,$short_description,$phone){
        $values = array($userId,$name,$nip,$city,$categoryId,$short_description,$phone);
        $columns = 'user_id,name,nip,city,category_id,short_description,phone';
        $companyId = $this->insert('companies',$columns,$values);
        return $companyId;
    }
}