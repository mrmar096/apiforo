<?php

require_once 'database.php';

class ConnectionDB{

    public function getPDO(){
        try{
            $base=new PDO('mysql:host='.HOSTNAME.';dbname='.DB, USER, PASS,array(PDO::ATTR_PERSISTENT=>true));
            $base->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $base->exec("set names utf8");
            return $base;
        }catch(PDOException $e) {
            $_SESSION['errorconexion']=$e->getMessage();
            throw $e;
        }
    }
}

?>