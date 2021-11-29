<?php


class Admin_Company extends Database
{

    public function getCompanies($conditions,$params){
        $companies = $this->select('companies.*, companies.id as companyId, plSafeChars(companies.name) as namePL, categories.name as categoryName, plSafeChars(categories.name) as categoryNamePL, plSafeChars(companies.city) as cityPL, companies.verificated, companies.terminate_start, users.login, users.email as ownerEmail ','companies, users, categories',' companies.user_id = users.id AND companies.category_id = categories.id  '.$conditions,$params);
        return $companies->fetchAll();
    }

    public function updateCompany($companyId,$set,$params=array()){
        try{
            $params += [':id'=>$companyId];
            $result = $this->update('companies',$set,'companies.id = :id ',$params);
            return $result;
        }catch(PDOException $e){
            echo $e->getCode();
            return false;
        }

    }


    public function deleteCompany($id){
        $params = array(':id'=>$id);
        $event = $this->delete('companies','companies.id = :id ',$params);
        return $event;
    }

}