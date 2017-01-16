<?php
require_once 'model/dao/DAOUserApi.php';
require_once 'utilities/ExceptionApi.php';

/**
 * Created by PhpStorm.
 * User: Mario
 * Date: 06/11/2016
 * Time: 11:37
 */

class userApi
{

    static $attrdisponibles=array('nombre','pass','email');
    static $userapi;

    //EN ESTE CONTROLLER SOLO NOS LLEGARAN PETICIONES POST PORQUE SOLO QUEREMOS  REGISTRAR

    public function post($peticion){

        if (!empty($peticion[0])&&$peticion[0] == Api::R_USERAPI_REGISTER && empty($peticion[1])) {
            $cuerpo=file_get_contents('php://input');
            $jsonobj=json_decode($cuerpo);
            self::checkJson($jsonobj);
            //CHECKEO SI LOS PARAMETROS QUE ME HAN PASADO ESTAN EN LOS DISPONIBLES Y ME HAN INTRODUCIDO LOS OBLIGATORIOS
            if(($error=self::checkinarrays($jsonobj,self::$attrdisponibles,self::$attrdisponibles))!=ExceptionApi::EXITO)throw new ExceptionApi($error,"Revisa los parametros introducidos");;
            $resultado= self::registrar($jsonobj);
            if($resultado){
                http_response_code(200);

                return ["estado"=>ExceptionApi::EXITO,"mensaje"=>utf8_encode("Registrado con exito")];

            }else{
                throw new ExceptionApi(ExceptionApi::FALLIDO,"Ha ocurrido un error durante el registro");
            }
            //SI LLEGA AQUI ES PORQUE ALGO HA FALLADO Y NO SE SABE BIEN PORQUE
            throw new ExceptionApi(ExceptionApi::DESCONOCIDO,"Ha ocurrido un error desconocido");

        } else {
            throw new ExceptionApi(ExceptionApi::ESTADO_URL_INCORRECTA, "Url mal formada", 400);
        }
    }

   //FUNCION PARA REGISTRAR A UN USUARIO QUE NOS VIENE POR JSON
    private function registrar($userapijson){
        $dao=new daoUserApi();
        try{
            return $dao->registrar($userapijson);
        }catch(PDOException $e){
            throw new ExceptionApi(ExceptionApi::ERRORBD,$e->getMessage());
        }
    }
    public static function autorizar(){

        $headers=apache_request_headers();

        if(isset($headers['Authorization'])){
            $keyapi=$headers['Authorization'];
            if(userApi::validarKeyApi($keyapi)){

                return true;
            }else{
            throw new ExceptionApi(ExceptionApi::KEYRESTRINGED,"Clave no authorizada");
            }
        }else{
            throw new ExceptionApi(ExceptionApi::KEYNOTFOUND,"Es necesario acceder con una clave");
        }

    }
    public static function validarKeyApi($key){
        $dao=new daoUserApi();
        return $dao->validarKeyApi($key);
    }
    public function checkinarrays($jsonobj,$arraycheck, $arrayo){
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
    private  function checkJson($jsonobj)
    {
        if(is_null($jsonobj))throw new ExceptionApi(ExceptionApi::FALLIDO,"Revisa la estructura json");
    }
}