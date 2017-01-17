<?php

require_once 'api/Api.php';
require_once 'views/viewjson.php';
require_once 'views/viewxml.php';
require_once 'controllers/UserApi.php';
require_once 'controllers/Usuario.php';
require_once 'controllers/Tema.php';
require_once 'utilities/ExceptionApi.php';


//OBTENEMOS EL RECURSO QUE NOS HAN SOLICITADO
$peticion=$_GET['PATH_INFO'];
//CONEVERTIMOS EN UN ARRAY LA URL SEPARADA POR /
$peticion=explode("/",$peticion);

//NOS QUEDAMOS CON EL PRIMER ELEMENTO SACANDOLO DEL ARRAY
$recurso_pedido=array_shift($peticion);

//LEEMOS SI NOS HAN PEDIDO UN FORMATO O OTRO

$formato=isset($_GET['format'])?$_GET['format']:'json';

switch ($formato){

    case 'xml':
        $vista= new ViewXMl();
        break;
    case 'json':
    default:
        $vista = new ViewJson();
        break;

}

//MANEJAMOS LAS EXCEPCIONES QUE SE PUEDEN DAR AL CARGAR UNA VISTA


set_exception_handler(function ($exception) use ($vista) {
    $cuerpo = array(
        "estado" => $exception->estado,
        "mensaje" => $exception->getMessage()
    );
    if ($exception->getCode()) {
        $vista->estado = $exception->getCode();
    } else {
        $vista->estado = 500;
    }

    $vista->imprimir($cuerpo);
}
);

//COMPROBAMOS QUE ESE RECURSO QUE NOS HAN SOLICITADO ESTA EN NUESTROS RESCURSOS ALMACENADOS

if(!in_array($recurso_pedido,Api::$R_Principales)){
    throw new ExceptionApi(ExceptionApi::ESTADO_EXISTENCIA_RECURSO,"No existe el recurso especificado");
}


//LEEMOS EL TIPO DE PETICION QUE NOS HA LLEGADO
$metodo=strtolower($_SERVER['REQUEST_METHOD']);


switch ($metodo) {
    case 'get':
    case 'post':
    case 'put':
    case 'delete':
        if($recurso_pedido!='userapi'){
            userApi::autorizar();
        }
        if (method_exists($recurso_pedido, $metodo)) {
            $respuesta = call_user_func(array($recurso_pedido, $metodo), $peticion);
            $vista->imprimir($respuesta);
            break;
        } else {
            throw new ExceptionApi(ExceptionApi::ESTADO_METODO_NO_PERMITIDO, "No existe el metodo especificado");
        }

        break;
    default:
        // Método no aceptado
}


?>