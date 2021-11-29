<?php

class City
{
    private $dbnameCity = '8860_miasta';
    private $dbhostCity = 'hadimperium.atthost24.pl';
    private $dbpassCity = 'asmarketingu123';
    public $dbCity;


    public function __construct()
    {
        try {
            $this->dbCity= new PDO("mysql:host=".$this->dbhostCity.";dbname=".$this->dbnameCity.";charset=utf8", $this->dbnameCity, $this->dbpassCity);
//            $this->dbCity->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $this->dbCity->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_SILENT);
            $this->dbCity->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
//            $this->dbCity->setAttribute(PDO::MYSQL_ATTR_USE_BUFFERED_QUERY, false);
        } catch (PDOException $e) {
            echo $e->getMessage();
            die();
        }
    }

    /* general function */
    public function run($sql, $params = [])
    {
        try{
            $stmt = $this->dbCity->prepare($sql);
            $stmt->execute($params);
            return $stmt;
        }catch(PDOException $e){
            echo $e->getMessage();
        }
    }
    /**/

    public function select($what,$from,$where,$params){
        $sql = 'SELECT '.$what.' FROM '.$from;
        if(!is_null($where)){
            $sql.=' WHERE '.$where;
        }
        $sql.=' ;';
        $result = $this->run($sql,$params);
        return $result;
    }

    public function getCities($area){
        if(is_null($area)){
            $areaFromSettings = getSettings('area');
            $area = $areaFromSettings;
        }
        $params = array(':powiat'=>$area);
        $result = $this->select('DISTINCT Gmina as name','miasta_polska',' miasta_polska.Powiat = :powiat',$params);
        return $result;
    }



}

?>