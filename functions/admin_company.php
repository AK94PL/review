<?php



function getAllCompanies_Admin($title,$categoryId,$dateSort,$statusSort){
    $page = (isset($_GET['p'])?(int)htmlspecialchars($_GET['p']):1);
    $limit = 20;
    $from = ($page * $limit) - $limit;

    $conditions = null;
    $params = array();
    $range = $from.','.$limit;

    if($statusSort!=3 && !empty($statusSort)){
        $conditions.=' AND companies.status = :status';
        $params += [':status'=>$statusSort];
    }

    if($categoryId!=1 && !empty($categoryId)){
        $conditions.=' AND companies.category_id = :categoryId';
        $params += [':categoryId'=>$categoryId];
    }


    if(!empty($title)){
        $conditions.=' AND (companies.name LIKE "%'.$title.'%" OR companies.id = :title)';
        $params += [':title'=>$title];
    }


    $conditions .= ' ORDER BY companies.created_date '.(!empty($dateSort)?$dateSort:'DESC');

    if(!empty($dateSort)){
        $conditions.=' LIMIT '.$range;
    }

    $companyObj = new Admin_Company();

    $results = $companyObj->getCompanies($conditions,$params);
    return $results;
}



function getCompany_Admin($id){

    $conditions = ' AND companies.id = :companyId';
    $params = array(':companyId'=>$id);

    $companyObj = new Admin_Company();

    $results = $companyObj->getCompanies($conditions,$params);
    return $results;
}



function updateCompany_Admin($id,$name,$nip,$place,$adress,$website,$foundation,$phone,$companyLogo,$short_description,$description,$show_description,$categoryId,$tags,$pon_o,$pon_z,$wto_o,$wto_z,$sro_o,$sro_z,$czw_o,$czw_z,$pia_o,$pia_z,$sob_o,$sob_z,$nie_o,$nie_z,$show_hours,$status,$verificated,$promoted){
    $companyId = $id;
    $params = array(':name'=>$name,':nip'=>$nip,':place'=>$place,':adress'=>$adress,':website'=>$website,':foundation'=>$foundation,':phone'=>$phone,':logo'=>$companyLogo,':short_description'=>$short_description,':description'=>$description,':show_description'=>$show_description,':categoryId'=>$categoryId,':tags'=>$tags,':pon_o'=>$pon_o,':pon_z'=>$pon_z,':wto_o'=>$wto_o,':wto_z'=>$wto_z,':sro_o'=>$sro_o,':sro_z'=>$sro_z,':czw_o'=>$czw_o,':czw_z'=>$czw_z,':pia_o'=>$pia_o,':pia_z'=>$pia_z,':sob_o'=>$sob_o,':sob_z'=>$sob_z,':nie_o'=>$nie_o,':nie_z'=>$nie_z,':show_hours'=>$show_hours,':status'=>$status,':verificated'=>$verificated,':promoted'=>$promoted);
    $set = ' companies.name = :name, companies.nip = :nip, companies.city = :place, companies.adress = :adress, companies.website = :website, companies.foundation_year = :foundation, companies.phone = :phone, companies.logo = :logo, companies.description = :description, companies.short_description  = :short_description, companies.show_description = :show_description, companies.category_id = :categoryId, companies.keywords = :tags, companies.pon_o = :pon_o, companies.pon_z = :pon_z, companies.wto_o = :wto_o, companies.wto_z = :wto_z, companies.sro_o = :sro_o, companies.sro_z = :sro_z, companies.czw_o = :czw_o, companies.czw_z = :czw_z, companies.pia_o = :pia_o, companies.pia_z = :pia_z, companies.sob_o = :sob_o, companies.sob_z = :sob_z, companies.nie_o = :nie_o, companies.nie_z = :nie_z, companies.show_hours = :show_hours, companies.status = :status, companies.verificated = :verificated, companies.promoted = :promoted';
    $companyObj = new Admin_Company();
    $companyChanges = $companyObj->updateCompany($companyId,$set,$params);
    if($companyChanges){
        return true;
    }else{
        return false;
    }
}



function deleteCompany_Admin($id){
    $obj = new Admin_Company();
    $delete = $obj->deleteCompany($id);
    return $delete;
}