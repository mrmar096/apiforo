<?php
require_once 'model/dao/DAOTema.php';


/**
 * Created by PhpStorm.
 * User: Mario
 * Date: 06/11/2016
 * Time: 11:37
 */
class Tema
{
    /**RECURSOS DISPONIBLES PARA REGISTRAR*/
    static $attrdisponibles=array('nombre','detalle','autor');
    /**RECURSOS OBLIGATORIOS PARA REGISTRAR*/
    static $attroregister=array('nombre','autor');
    /**RECURSOS DISPONIBLES Y OBLIGATORIOS PARA COMENTAR*/
    static $attrcom=array('usuario','comentario');

    /**HTTP METHODS*/
    public static function get($peticion){
        if(!empty($peticion[0]) && !empty($peticion[1]) && $peticion[1]==Api::R_COMENTARIOS && !empty($peticion[2])){
            //SACAMOS UN COMENTARIO RELACIONADO CON UN TEMA
            $idtema=$peticion[0];
            $idcomentario=$peticion[1];
            $comentario= self::getComentariosFromTema($idcomentario,$idtema);
            if(!empty($temas)){
                http_response_code(200);
                return ["estado"=>ExceptionApi::EXITO,"comentario"=>$comentario];
            }else{
                throw new ExceptionApi(ExceptionApi::EMTPY_QUERY, "Sin Resultado", 400);
            }

        }elseif($peticion[1]==Api::R_COMENTARIOS && empty($peticion[2])){
            //SACAMOS TODOS LOS COMENTARIOS RELACIONADOS CON UN TEMA
            $idtema=$peticion[0];
            $comentarios= self::getComentariosFromTema(null,$idtema);
            if(!empty($temas)){
                http_response_code(200);
                return ["estado"=>ExceptionApi::EXITO,"comentarios"=>$comentarios];
            }else{
                throw new ExceptionApi(ExceptionApi::EMTPY_QUERY, "Sin Resultado", 400);
            }

        }elseif(empty($peticion[1])){
            //SACAMOS UN TEMA ESPECIFICO
            $idtema=$peticion[0];
            $tema= self::getOneTema($idtema);
            if(!empty($tema)){
                http_response_code(200);
                return ["estado"=>ExceptionApi::EXITO,"tema"=>$tema];
            }else{
                throw new ExceptionApi(ExceptionApi::EMTPY_QUERY, "Sin resultados", 400);
            }

        }elseif(empty($peticion[0])){
            //OBTENER TODOS LOS TEMAS DEL FORO
            //LEEMOS LOS GET
            $temas=self::getAllTemas();
            if(!empty($temas)) {
                http_response_code(200);
                return
                    [
                        "estado" => ExceptionApi::EXITO,
                        "temas" => $temas
                    ];
            }else{
                throw new ExceptionApi(ExceptionApi::EMTPY_QUERY, "Sin resultados");
            }

        }else{
            throw new ExceptionApi(ExceptionApi::PARAMSINCORRECT, "Url mal formada");
        }
    }
    public static function post($peticion){
        if(!empty($peticion[0]) && !empty($peticion[1]) && $peticion[1]==Api::R_COMENTARIOS && empty($peticion[2])) {
            //REGISTRO NUEVO COMENTARIO
            $idtema=$peticion[0];
            $cuerpo=file_get_contents('php://input');
            $jsonobj=json_decode($cuerpo);
            //SI LLEGA EL JSON A NULL ESTARA MAL ESTRUCTURADO
            self::checkJson($jsonobj);
            self::limpiarnulljson($jsonobj);
            //CHECKEO SI LOS PARAMETROS QUE ME HAN PASADO ESTAN EN LOS DISPONIBLES Y ME HAN INTRODUCIDO LOS OBLIGATORIOS
            if(($error=self::checkinarrays($jsonobj,self::$attrcom,self::$attrcom))!=ExceptionApi::EXITO)throw new ExceptionApi($error,"Revisa los parametros introducidos");
            //REGISTRAMOS EL USARIO Y SI HA SIDO EXITOSO DEVOLVEMOS EL OBJETO
            $resultado= self::registrarComentarioenTema($jsonobj,$idtema);
            if($resultado){
                http_response_code(200);
                return ["estado"=>ExceptionApi::EXITO,"comentario"=>$resultado];
            }else{
                throw new ExceptionApi(ExceptionApi::FALLIDO,"Ha ocurrido un error durante el registro");
            }

        }elseif(empty($peticion[0])) {
            //REGISTRO NUEVO TEMA
            $cuerpo=file_get_contents('php://input');
            $jsonobj=json_decode($cuerpo);
            //SI LLEGA EL JSON A NULL ESTARA MAL ESTRUCTURADO
            self::checkJson($jsonobj);
            self::limpiarnulljson($jsonobj);
            //CHECKEO SI LOS PARAMETROS QUE ME HAN PASADO ESTAN EN LOS DISPONIBLES Y ME HAN INTRODUCIDO LOS OBLIGATORIOS
            if(($error=self::checkinarrays($jsonobj,self::$attrdisponibles,self::$attroregister))!=ExceptionApi::EXITO)throw new ExceptionApi($error,"Revisa los parametros introducidos");
            //REGISTRAMOS EL TEMA Y SI HA SIDO EXITOSO DEVOLVEMOS EL OBJETO
            $resultado= self::registrarTema($jsonobj);
            if($resultado){
                http_response_code(200);
                return ["estado"=>ExceptionApi::EXITO,"tema"=>$resultado];
            }else{
                throw new ExceptionApi(ExceptionApi::FALLIDO,"Ha ocurrido un error durante el registro");
            }
        }else {
            throw new ExceptionApi(ExceptionApi::ESTADO_URL_INCORRECTA, "Url mal formada", 400);
        }
    }
    public static function delete($peticion){
        if (!empty($peticion[0]) && empty($peticion[1])) {
            //TEMA
            $id=$peticion[0];
            $resultado= self::delTema($id);
            if($resultado){
                http_response_code(200);
                return ["estado"=>ExceptionApi::EXITO,"mensaje"=>utf8_encode("Tema eliminado con exito")];
            }else{
                throw new ExceptionApi(ExceptionApi::FALLIDO,"Ha ocurrido un error durante la eliminacion");
            }
        }else{
            throw new ExceptionApi(ExceptionApi::ESTADO_URL_INCORRECTA,"Url Mal Formada");
        }
    }

    /** *************************************************/
    /**TEMAS*/
    //**SELECT**//
    public static function getAllTemas(){
        $dao= new DaoTema();
        try{
            $resultados=$dao->getAllTemas();
        }catch(PDOException $e){
            throw new ExceptionApi(ExceptionApi::ERRORBD,$e->getMessage());
        }
        return $resultados;
    }
    public static function getOneTema($idtema){
        $dao= new DaoTema();
        try{
            $resultado=$dao->getOneTema($idtema);
        }catch(PDOException $e){
            throw new ExceptionApi(ExceptionApi::ERRORBD,$e->getMessage());
        }
        return $resultado;
    }
    //**INSERT**//
    public static function registrarTema($jsonobj){
        $dao=new DaoTema();
        try{
            return $dao->registrarTema($jsonobj);
        }catch(PDOException $e){
            throw new ExceptionApi(ExceptionApi::ERRORBD,$e->getMessage());
        }
    }
    //**DELETE**//
    public static function delTema($id){
        $dao=new DaoTema();
        try{
            return $dao->delTema($id);
        }catch(PDOException $e){
            throw new ExceptionApi(ExceptionApi::ERRORBD,$e->getMessage());
        }
    }


    /** *************************************************/
    /**COMENTARIOS ASOCIADOS A TEMAS*/
    //**SELECT**//
    public static function getComentariosFromTema($idcomentario,$idtema){
        $dao= new DaoTema();
        try{
            $resultado=$dao->getComentariosFromTema($idcomentario,$idtema);
        }catch(PDOException $e){
            throw new ExceptionApi(ExceptionApi::ERRORBD,$e->getMessage());
        }
        return $resultado;
    }
    //**INSERT**//
    public static function registrarComentarioenTema($jsonobj,$idtema){
        $dao=new DaoTema();
        try{
            return $dao->registrarComentarioenTema($jsonobj,$idtema);
        }catch(PDOException $e){
            throw new ExceptionApi(ExceptionApi::ERRORBD,$e->getMessage());
        }
    }
    public static function delComentariosFromTema($idcomentario,$idtema){
        $dao=new DaoTema();
        try{
            return $dao->delComentariosFromTema($idcomentario,$idtema);
        }catch(PDOException $e){
            throw new ExceptionApi(ExceptionApi::ERRORBD,$e->getMessage());
        }
    }
    /** *************************************************/
    /**OTHER FUNCTIONS*/
    public static function checkinarray($jsonobj,$arraycheck){

        //MIRA A VER SI NOS HAN INTRODUCIDO ALGUNA QUE NO ESTE DISPONIBLE
        while ($obj = current($jsonobj)) {
            $key = key($jsonobj);
            if (is_bool(($index = array_search($key, $arraycheck)))) {
                return ExceptionApi::PARAMSINCORRECT;
            }
            next($jsonobj);
        }
        return ExceptionApi::EXITO;
    }
    public static function checkinarrays($jsonobj,$arraycheck, $arrayo){
        //MIRA A VER SI NOS HAN INTRODUCIDO ALGUNA QUE NO ESTE DISPONIBLE
        while ($obj = current($jsonobj)) {
            $key=key($jsonobj);
            if(is_bool(($index=array_search($key,$arraycheck)))){
                return ExceptionApi::PARAMSINCORRECT;
            }
            next($jsonobj);
        }

        //COMPROBAMOS SI NOS HAN INTRODUCITO LOS OBLIGATORIOS
        foreach ($arrayo as $item){
            if(!property_exists($jsonobj,$item)){
                return ExceptionApi::PARAMSOB;
            }
        }
        return ExceptionApi::EXITO;
    }
    public static function checkJson($jsonobj)
    {
        if(is_null($jsonobj))throw new ExceptionApi(ExceptionApi::FALLIDO,"Revisa la estructura json");
    }
    public static function refreshlocation($username, $jsonobj){
        if(!is_numeric($jsonobj->{'lat'}) || !is_numeric($jsonobj->{'lon'})){
            throw new ExceptionApi(ExceptionApi::PARAMSINCORRECT,"La latitud y la longitud debe ser numerica");
        }
        $dao=new DaoTema();
        try{
            if($dao->updateLocation($username,$jsonobj)>0)return true;
        }catch(PDOException $e){
            throw new ExceptionApi(ExceptionApi::ERRORBD,$e->getMessage());
        }

        return false;
    }
    public static function limpiarnulljson(&$obj){
        $obj = (object) array_filter((array) $obj, function ($val) {
            return !is_null($val);
        });
    }

}