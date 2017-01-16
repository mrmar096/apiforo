<?php
    require_once 'model/connection/connectionDB.php';
/**
 * Created by PhpStorm.
 * User: Mario
 * Date: 06/11/2016
 * Time: 11:36
 */
class daoUserApi
{
    public function registrar($userapi){

        $pass=$this->encriptar($userapi->{'pass'});
        $userapi->{'pass'}=$pass;
        $userapi->{'key'}=$this->keyApi();

        $conection= new ConnectionDB();
        $objPdo=$conection->getPDO();
        $sql='insert into userapi values(null,?,?,?,?)';
        $statement=$objPdo->prepare($sql);
        $statement->bindParam('1',$userapi->{'nombre'});
        $statement->bindParam('2',$userapi->{'pass'});
        $statement->bindParam('3',$userapi->{'email'});
        $statement->bindParam('4',$userapi->{'key'});
        try {
            $objPdo->beginTransaction();
            $resultado=$statement->execute();
            $objPdo->commit();
            if($resultado){
                return true;
            }else{
                return false;
            }
        } catch (PDOException $e) {
           throw $e;
        }finally{

            $objPdo=NULL;
            $statement=NULL;
        }

    }
    public function validarKeyApi($key){
        $conexion=new ConnectionDB();
        $objPdo=$conexion->getPDO();
        $sql='select * from userapi where `key`="'.$key.'"';
        $statement=$objPdo->prepare($sql);
        $statement->bindParam("1", $key);

        try {
            $statement->execute();
            $statement->fetch(PDO::FETCH_ASSOC);

            if ($statement->rowCount()>0){
                return true;
            }else{
                return false;
            }


        } catch (PDOException $e) {
            throw $e;
        }finally{

            $objPdo=NULL;
            $statement=NULL;
        }
    }

    private function keyApi(){
        return md5(microtime() . rand());
    }
    private function encriptar($pass){
        if($pass){
            return password_hash($pass,PASSWORD_DEFAULT);
        }
    }
    private function verificarclasves($clave,$claveencriptada){
        return password_verify($clave, $claveencriptada);

    }

}