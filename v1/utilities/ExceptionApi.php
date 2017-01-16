<?php

/**
 * Created by PhpStorm.
 * User: Mario
 * Date: 05/11/2016
 * Time: 15:55
 */
class ExceptionApi extends Exception
{
    public $estado;
    // Constantes de estado
    const FALLIDO=0;
    const EXITO=1;
    const ESTADO_URL_INCORRECTA = 2;
    const ESTADO_EXISTENCIA_RECURSO = 3;
    const ESTADO_METODO_NO_PERMITIDO = 4;
    const ERRORBD=5;
    const DESCONOCIDO=6;
    const PARAMSINCORRECT=7;
    const PARAMSOB=8;
    const KEYNOTFOUND=9;
    const KEYRESTRINGED=10;
    const EMTPY_QUERY=11;
    const LOGINFAILED=12;
    const USERISREGISTERED=13;

    public function __construct($estado, $mensaje, $codigo = 400)
    {
        $this->estado = $estado;
        $this->message = $mensaje;
        $this->code = $codigo;
    }

}