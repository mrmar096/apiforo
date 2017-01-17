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
        $userrrpp=null;
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

        return $userrrpp;
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
            $sql='select * from comentarios c where c.tema='.$idtema;
        }else{
            $sql='select * from comentarios id='.$idcomentario;
        }
        $statement=$objPdo->prepare($sql);
        try {
            $statement->execute();
            if(is_null($idcomentario)){
                $resultado= $statement->fetchAll(PDO::FETCH_ASSOC);
            }else{
                $resultado= $statement->fetch(PDO::FETCH_ASSOC);
            }
        } catch (PDOException $e) {
            throw $e;
        }finally{

            $objPdo=NULL;
            $statement=NULL;
        }

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
    public function deloferta($username,$id){
        $conection= new ConnectionDB();
        $objPdo=$conection->getPDO();
        if(is_null($username) && is_null($id)){
            $sql = 'delete from ofertas';
        }elseif(!is_null($username) && is_null($id)){
            $sql = 'delete from ofertas where user_rrpp="'.$username.'"';
        }elseif(!is_null($id) && is_null($username)){
            $sql='delete from ofertas where id='.$id;
        }else{
            $sql='delete from ofertas where user_rrpp="'.$username.'"'.' and id='.$id;
        }
        $statement=$objPdo->prepare($sql);
        try {
            $objPdo->beginTransaction();
            $statement->execute();
            $objPdo->commit();
            return $statement->rowCount();
        } catch (PDOException $e) {
            throw $e;
        }finally{

            $objPdo=NULL;
            $statement=NULL;
        }

    }


    //**DELETE**//
    public function delComentariosFromTema($idcomentario,$idtema){
        $conection= new ConnectionDB();
        $objPdo=$conection->getPDO();
        if(is_null($idcomentario)){
            $sql='delete from comentarios where tema='.$idtema;
        }else{
            $sql='delete from comentarios where id='.$idcomentario;
        }
        $statement=$objPdo->prepare($sql);
        $resultado=null;
        try {
            $objPdo->beginTransaction();
            $statement->execute();
            $objPdo->commit();
            $resultado= $statement->rowCount();
        } catch (PDOException $e) {
            throw $e;
        }finally{

            $objPdo=NULL;
            $statement=NULL;
        }
        return $resultado;
    }

    /** *************************************************/
    /**TABLE COMENTARIOS OFERTAS*/
    //**SELECT**//
    public function getComentariosOferta($idoferta,$id,$start,$limit) {
        $conexion=new ConnectionDB();
        $objPdo=$conexion->getPDO();
        if(is_null($idoferta)&& is_null($id)){
            $sql = 'select * from comentariosofertas';
        }elseif(!is_null($idoferta)&& is_null($id)){
            $sql = 'select * from comentariosofertas where oferta='.$idoferta;
        }elseif(!is_null($id)&& is_null($idoferta)){
            $sql='select * from comentariosofertas where id='.$id;
        }else{
            $sql='select * from comentariosofertas where oferta='.$idoferta.' and id='.$id;
        }
        $sql.=' order by fecha desc';
        if(!is_null($start)&&!is_null($limit)) $sql.=' limit '.$start.','.$limit;
        $statement=$objPdo->prepare($sql);
        $resultado=null;
        try {
            $statement->execute();
            if(!is_null($id)){
                if($row = $statement->fetch(PDO::FETCH_ASSOC)) {
                    $resultado = (object)$row;
                }
            }else {
                $resultado = $statement->fetchAll(PDO::FETCH_ASSOC);
            }

        } catch (PDOException $e) {
            throw $e;
        }finally{

            $objPdo=NULL;
            $statement=NULL;
        }
        return $resultado;

    }
    public function getCountComentariosOfertas($oferta){
        $conexion=new ConnectionDB();
        $objPdo=$conexion->getPDO();
        $sql='select count(*) from comentariosofertas where oferta='.$oferta;
        $statement=$objPdo->prepare($sql);
        try {
            $statement->execute();
            return $statement->fetch(PDO::FETCH_ASSOC)['count(*)'];
        } catch (PDOException $e) {
            throw $e;
        }finally{

            $objPdo=NULL;
            $statement=NULL;
        }


    }
    //**INSERT**//
    public function comentaroferta($idoferta,$obj){
        $tablename="comentariosofertas";
        $obj->{'oferta'}=$idoferta;
        $conection= new ConnectionDB();
        $objPdo=$conection->getPDO();
        $sql=$this->generaCadenaSqlInsert($obj,$tablename);
        $statement=$objPdo->prepare($sql);
        try {
            $objPdo->beginTransaction();
            $resultado=$statement->execute();
            $id=$objPdo->lastInsertId();
            $objPdo->commit();
            if($resultado){
                return $this->getComentariosOferta($idoferta,$id,null,null);
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
    public function delcomentariooferta($idoferta,$id){
        $conection= new ConnectionDB();
        $objPdo=$conection->getPDO();
        if(is_null($idoferta)&& is_null($id)){
            $sql = 'delete from comentariosofertas';
        }elseif(!is_null($idoferta)&& is_null($id)){
            $sql = 'delete from comentariosofertas where oferta='.$idoferta;
        }elseif(!is_null($id)&& is_null($idoferta)){
            $sql='delete from comentariosofertas where id='.$id;
        }else{
            $sql='delete from comentariosofertas where oferta='.$idoferta.' and id='.$id;
        }
        $statement=$objPdo->prepare($sql);
        try {
            $objPdo->beginTransaction();
            $statement->execute();
            $objPdo->commit();
            return $statement->rowCount();
        } catch (PDOException $e) {
            throw $e;
        }finally{

            $objPdo=NULL;
            $statement=NULL;
        }

    }

    /** *************************************************/
    /**TABLE DEVICES*/
    //**SELECT**//
    public function existDeviceid($username, $deviceid){
        $conexion=new ConnectionDB();
        $objPdo=$conexion->getPDO();
        $sql="select * from devicesid where deviceid='$deviceid' and user_rrpp='$username'";
        $statement=$objPdo->prepare($sql);

        try {
            $statement->execute();
            return $statement->rowCount();

        } catch (PDOException $e) {
            throw $e;
        }finally{

            $objPdo=NULL;
            $statement=NULL;
        }
    }
    //**INSERT**//
    public function newDeviceID($username,$deviceid){
        $conection= new ConnectionDB();
        $objPdo=$conection->getPDO();
        $sql='insert into devicesid(deviceid,user_rrpp) values(?,?)';
        $statement=$objPdo->prepare($sql);
        $statement->bindParam('1',$deviceid);
        $statement->bindParam('2',$username);
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

    /** *************************************************/
    /**TABLE SESSIONS*/
    //**SELECT**//
    public function getUserbySession($session_id){
        $conexion=new ConnectionDB();
        $objPdo=$conexion->getPDO();
        $sql="select user_rrpp from sessions where session_id='$session_id'";
        $statement=$objPdo->prepare($sql);
        $username=null;
        try {
            $statement->execute();
            $username=$statement->fetch(PDO::FETCH_ASSOC)['user_rrpp'];

        } catch (PDOException $e) {
            throw $e;
        }finally{

            $objPdo=NULL;
            $statement=NULL;
        }
        return $username;
    }
    public function checkSessionID($session_id){
        $conexion=new ConnectionDB();
        $objPdo=$conexion->getPDO();
        $sql="select session_id from sessions where session_id='$session_id'";
        $statement=$objPdo->prepare($sql);
        $username=null;
        try {
            $statement->execute();
            if($statement->rowCount()){
                $username=$this->getUserbySession($session_id);
            }

        } catch (PDOException $e) {
            throw $e;
        }finally{

            $objPdo=NULL;
            $statement=NULL;
        }
        return $username;
    }
    //**UPDATE**//
    public function newSessionID($username){
        $session_id=$this->generarSession_ID();
        $conection= new ConnectionDB();
        $objPdo=$conection->getPDO();
        $sql='insert into sessions values(null,?,?)';
        $statement=$objPdo->prepare($sql);
        $statement->bindParam('1',$session_id);
        $statement->bindParam('2',$username);
        $resultado=null;
        try {
            $objPdo->beginTransaction();
            if($statement->execute()){
                $resultado=$session_id;
            }
            $objPdo->commit();
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
    public function generarSession_ID(){
        return md5(uniqid(microtime()) . $_SERVER['REMOTE_ADDR'] . $_SERVER['HTTP_USER_AGENT']);

    }
    public function generaCadenaSqlUpdate($obj){
        $values=(array)$obj;
        $sql="";
        $end=count($values);
        $coma=",";
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