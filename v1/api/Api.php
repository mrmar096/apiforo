<?php

class Api{
    //DEFINIMOS LOS RECURSOS PRINCIPALES A LOS QUE PODEMOS HACER PETICIONES A LA API EN UN ARRAY
    static $R_Principales            = array('tema','usuario','userapi');
    //DEFINIMOS RECURSOS QUE PUEDEN SER ACCESIBLES Y TRATADOS POR LA API
    const  R_USUARIO_LOGIN           = "login";
    const  R_USERAPI_REGISTER        = "registrar";
    const  R_USUARIO_REGISTER        = "registrar";
    const  R_USARIO_AVATAR           = "avatar";
    const  R_USUARIOS                = "usuarios";
    const  R_COMENTARIOS             = "comentarios";
    const  R_TEMAS                   = "temas";
    const  ROOT_DIR                  = "/apiforo/v1/";
}

?>