<?php
require_once 'model/connection/connectionDB.php';

/**
 * Created by PhpStorm.
 * User: Mario
 * Date: 06/11/2016
 * Time: 11:36
 */
class daoUsuario
{
    /** *************************************************/
    /**TABLE USER*/
    //**SELECT**//
    public function login($obj){
        $conexion=new ConnectionDB();
        $objPdo=$conexion->getPDO();
        $sql="select pass from usarios where username='".$obj->{'username'}."'";
        $statement=$objPdo->prepare($sql);
        $resultado=null;
        try {
            $statement->execute();
            $row=$statement->fetch(PDO::FETCH_ASSOC);
            $resultado=$this->verificarclasves($obj->{'pass'},$row['pass']);

        } catch (PDOException $e) {
            throw $e;
        }finally{

            $objPdo=NULL;
            $statement=NULL;
        }
        return $resultado;
    }

    public function getAllUsers(){
        $conexion=new ConnectionDB();
        $objPdo=$conexion->getPDO();
        $sql="select u.username,u.avatar,u.nombre,u.email from usuarios";
        $statement=$objPdo->prepare($sql);
        $resultado=array();
        try {
            $statement->execute();
            $resultado=$statement->fetchAll(PDO::FETCH_ASSOC);

        } catch (PDOException $e) {
            throw $e;
        }finally{

            $objPdo=NULL;
            $statement=NULL;
        }

        return $resultado;
    }
    public function getOneUser($id){
        $conexion=new ConnectionDB();
        $objPdo=$conexion->getPDO();
        $sql='select u.username,u.avatar,u.nombre,u.email from usuarios u  where id=?';
        $statement=$objPdo->prepare($sql);
        $statement->bindParam("1", $id);
        $resultado=null;
        try {
            $statement->execute();
            $resultado=$statement->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            throw $e;
        }finally{

            $objPdo=NULL;
            $statement=NULL;
        }

        return $resultado;
    }

    //**INSERT**//
    public function registrar($obj){
        //Miro a ver si tiene alguna key que lleve pass para encriptarla
        $tablename="usuarios";
        if(!empty($obj->{'pass'})){
            $pass=$this->encriptar($obj->{'pass'});
            $obj->{'pass'}=$pass;
        }
        $conection= new ConnectionDB();
        $objPdo=$conection->getPDO();
        $sql=$this->generaCadenaSqlInsert($obj,$tablename);
        $statement=$objPdo->prepare($sql);
        $user=null;
        try {
            $objPdo->beginTransaction();
            $resultado=$statement->execute();
            $id=$objPdo->lastInsertId();
            $objPdo->commit();
            //RETORNO EL OBJETO QUE HE INSERTADO
            if($resultado){
                $user= $this->getOneUser($id);
            }

        } catch (PDOException $e) {
            throw $e;
        }finally{
            $objPdo=NULL;
            $statement=NULL;
        }
        return $user;
    }
    //**UPDATE**//
    public function updateUser($obj,$id){
        if(!empty($obj->{'pass'})){
            $pass=$this->encriptar($obj->{'pass'});
            $obj->{'pass'}=$pass;
        }
        $conection= new ConnectionDB();
        $objPdo=$conection->getPDO();
        $tablename="usuarios";
        $where= array("id"=>$id);
        $sql=$this->generaCadenaSqlUpdate($tablename,$obj,$where);

        $statement=$objPdo->prepare($sql);
        try {
            $objPdo->beginTransaction();
            $resultado=$statement->execute();
            $objPdo->commit();
            if($resultado){
                return $this->getOne($obj->{'username'});
            }
        } catch (PDOException $e) {
            throw $e;
        }finally{

            $objPdo=NULL;
            $statement=NULL;
        }
        return null;
    }
    public function updateAvatar($src,$id){
        $conection= new ConnectionDB();
        $objPdo=$conection->getPDO();
        $sql='update usuarios set avatar=? where id=?';
        $statement=$objPdo->prepare($sql);
        $statement->bindParam('1',$src);
        $statement->bindParam('2',$id);
        $resultado=null;
        try {
            $objPdo->beginTransaction();
            $resultado=$statement->execute();
            $objPdo->commit();
        } catch (PDOException $e) {
            throw $e;
        }finally{

            $objPdo=NULL;
            $statement=NULL;
        }
        return $resultado;
    }
    //**DELETE**//
    public function deleteUser($id){
        $conection= new ConnectionDB();
        $objPdo=$conection->getPDO();
        $tablename="usuarios";
        $sql="delete from usuarios where id=".$id;

        $statement=$objPdo->prepare($sql);
        try {
            $objPdo->beginTransaction();
            $statement->execute();
            $objPdo->commit();
            $resultado= $statement->rowCount()>0;
        } catch (PDOException $e) {
            throw $e;
        }finally{

            $objPdo=NULL;
            $statement=NULL;
        }
        return null;
    }

    /** *************************************************/
    /**TEMAS ASOCIADOS*/
    //**SELECT**//
    public function getTemasbyUserID($id){
        $conexion=new ConnectionDB();
        $objPdo=$conexion->getPDO();
        $sql='select t.* from temas t, comentarios c where t.id=c.id and c.usuario=? oder by t.fecha';
        $statement=$objPdo->prepare($sql);
        $statement->bindParam("1",$id);
        $resultado=null;
        try {
            $statement->execute();
            $resultado = $statement->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            throw $e;
        }finally{

            $objPdo=NULL;
            $statement=NULL;
        }
        return $resultado;
    }
    //**DELETE**//
    public function delAsocUserTema($iduser,$idtema,$idcomentario){
        $conection= new ConnectionDB();
        $objPdo=$conection->getPDO();
        if(is_null($idtema)&& is_null($idcomentario)){
            $sql = 'delete from comentarios where usuario='.$iduser;
        }elseif(!is_null($idtema)&& is_null($idcomentario)){
            $sql = 'delete from comentarios where tema='.$idtema.' and usuario='.$iduser;
        }else{
            $sql='delete from comentarios where tema='.$idtema.' and usuario='.$iduser.' and id='.$idcomentario;
        }
        $statement=$objPdo->prepare($sql);
        $resultado=null;
        try {
            $objPdo->beginTransaction();
            $statement->execute();
            $objPdo->commit();
            $resultado= $statement->rowCount()>0;
        } catch (PDOException $e) {
            throw $e;
        }finally{

            $objPdo=NULL;
            $statement=NULL;
        }
        return $resultado;
    }


    /** *************************************************/
    /**OTHER FUNCTIONS*/
    private function encriptar($pass){
        if($pass){
            return password_hash($pass,PASSWORD_DEFAULT);
        }
    }
    private function verificarclasves($clave,$claveencriptada){
        return password_verify($clave, $claveencriptada);

    }
    public function generaCadenaSqlUpdate($tablename,$obj,$where){
        $values=(array)$obj;
        $sql="update usuarios set ";
        $end=count($values);
        $coma=",";
        $and="and";
        $i=1;
        foreach($values as $k => $v) {
            if ($i==$end) {
                $coma = "";
            }
            if(is_string($v)){
                $sql.=$k."='".$v."'".$coma;
            }else{
                $sql.=$k."=".$v.$coma;
            }
        }
        $sql.=" where ";
        //Ponemos el where
        foreach($where as $k => $v) {
            if ($i==$end) {
                $and = "";
            }
            if(is_string($v)){
                $sql.=$k."='".$v."'".$and;
            }else{
                $sql.=$k."=".$v.$and;
            }
        }
        return $sql;
    }
    public function generaCadenaSqlInsert($obj,$tablename){
        $values=(array)$obj;
        $sql="insert into ".$tablename." (";
        $coma=",";
        $end=count($values);
        $i=1;
        //GENERAR INSERT INTO
        foreach($values as $k => $v) {
            if ($i==$end) {
                $coma = "";
            }
            $sql.=$k.=$coma;
            $i++;
        }

        //GENERAR INSERT VALUES
        $i=1;
        $coma=",";
        $sql.=") values(";
        foreach($values as $k => $v) {
            if ($i==$end) {
                $coma = "";
            }
            if(is_string($v)){
                $sql.="'".$v."'".$coma;
            }else{
                $sql.=$v.$coma;
            }
            $i++;
        }
        $sql.=");";
        return $sql;
    }
}