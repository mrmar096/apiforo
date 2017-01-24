<?php
require_once 'model/connection/connectionDB.php';
/**
 * Created by PhpStorm.
 * User: Mario
 * Date: 06/11/2016
 * Time: 11:36
 */
class DaoTema
{
    /** *************************************************/
    /**TABLE TEMAS*/
    //**SELECT**//
    public function getAllTemas(){
        $conexion=new ConnectionDB();
        $objPdo=$conexion->getPDO();
        $sql="select * from temas order by fecha";
        $statement=$objPdo->prepare($sql);
        $resultado=array();
        try {
            $statement->execute();

            while(($row=$statement->fetch(PDO::FETCH_ASSOC))){
                $tema=(object)$row;
                //OBTENGO EL AUTOR ASOCIADO A CADA TEMA
                //HABLO CON EL DAO DE LOS USUARIOS PARA QUE ME BUSQUE UN USUARIO
                $daouser=new DaoUsuario();
                $autor=$daouser->getOneUser($row['autor']);
                $tema->{'autor'}=$autor;
                $resultado[]=$tema;
            }

        } catch (PDOException $e) {
            throw $e;
        }finally{

            $objPdo=NULL;
            $statement=NULL;
        }

        return $resultado;
    }
    public function getOneTema($idtema){
        $conexion=new ConnectionDB();
        $objPdo=$conexion->getPDO();
        $sql="select * from temas where id=".$idtema;
        $statement=$objPdo->prepare($sql);
        $tema=null;
        try {
            $statement->execute();
            $resultado=$statement->fetch(PDO::FETCH_ASSOC);
            if($resultado!=null){
                $tema=(object)$resultado;
                //OBTENGO EL AUTOR ASOCIADO A CADA TEMA
                //HABLO CON EL DAO DE LOS USUARIOS PARA QUE ME BUSQUE UN USUARIO
                $daouser=new DaoUsuario();
                $autor=$daouser->getOneUser($resultado['autor']);
                $tema->{'autor'}=$autor;
            }
        } catch (PDOException $e) {
            throw $e;
        }finally{

            $objPdo=NULL;
            $statement=NULL;
        }

        return $tema;
    }

    //**INSERT**//
    public function registrarTema($obj){
        $tablename="temas";
        $conection= new ConnectionDB();
        $objPdo=$conection->getPDO();
        $sql=$this->generaCadenaSqlInsert($obj,$tablename);
        $statement=$objPdo->prepare($sql);
        try {
            $objPdo->beginTransaction();
            $resultado=$statement->execute();
            $id=$objPdo->lastInsertId();
            $objPdo->commit();
            //RETORNO EL OBJETO QUE HE INSERTADO
            if($resultado){
                return $this->getOneTema($id);
            }
        } catch (PDOException $e) {
            throw $e;
        }finally{
            $objPdo=NULL;
            $statement=NULL;
        }
        return null;
    }

    //**DELETE**//
    public function delTema($id){
        $conection= new ConnectionDB();
        $objPdo=$conection->getPDO();
        $sql='delete from temas where id=?';
        $statement=$objPdo->prepare($sql);
        $statement->bindParam('1',$id);
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
    /**COMENTARIOS ASOCIADOS A TEMAS*/
    //**SELECT**//
    public function getComentariosFromTema($idcomentario,$idtema){
        $conexion=new ConnectionDB();
        $objPdo=$conexion->getPDO();
        if(is_null($idcomentario)){
            $sql='select * from comentarios where tema='.$idtema;
        }else{
            $sql='select * from comentarios where tema='.$idtema.' and id='.$idcomentario;
        }

        $statement=$objPdo->prepare($sql);
        $resultado=null;
        try {
            $statement->execute();
            //PRESENTO EL OBJETO USER PARA CADA COMENTARIO
            $daouser=new DaoUsuario();
            if(is_null($idcomentario)){
                while(($row=$statement->fetch(PDO::FETCH_ASSOC))){
                    $comentario=(object)$row;
                    $usuario=$daouser->getOneUser($row['usuario']);
                    $comentario->{'usuario'}=$usuario;
                    $resultado[]=(array)$comentario;
                }

            }else{
                $row=$statement->fetch(PDO::FETCH_ASSOC);
                $resultado=(object)$row;
                $usuario=$daouser->getOneUser($row['usuario']);
                $resultado->{'usuario'}=$usuario;
            }
        } catch (PDOException $e) {
            throw $e;
        }finally{

            $objPdo=NULL;
            $statement=NULL;
        }
        return $resultado;

    }

    //**INSERT**//
    public function registrarComentarioenTema($obj,$idtema){
        $tablename="comentarios";
        $obj->{'tema'}=$idtema;
        $conection= new ConnectionDB();
        $objPdo=$conection->getPDO();
        $sql=$this->generaCadenaSqlInsert($obj,$tablename);
        $statement=$objPdo->prepare($sql);
        $resultado=null;
        $comentario=null;
        try {
            $objPdo->beginTransaction();
            $resultado=$statement->execute();
            $idcomentario=$objPdo->lastInsertId();
            $objPdo->commit();
            if($resultado){
                $comentario= $this->getComentariosFromTema($idcomentario,$idtema);
            }
        } catch (PDOException $e) {
            throw $e;
        }finally{
            $objPdo=NULL;
            $statement=NULL;
        }

        return $comentario;
    }

    //**DELETE**//
    public function delComentariosFromTema($idcomentario,$idtema){
        $conection= new ConnectionDB();
        $objPdo=$conection->getPDO();
        if(is_null($idcomentario)){
            $sql='delete from comentarios where tema='.$idtema;
        }else{
            $sql='delete from comentarios where tema='.$idtema.' and id='.$idcomentario;
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
            if(is_numeric($v)){
                $sql.=$v.$coma;
            }else{
                $sql.="'".$v."'".$coma;
            }
            $i++;
        }
        $sql.=");";
        return $sql;
    }
}
