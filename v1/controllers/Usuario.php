<?php
require_once 'model/dao/DAOUsuario.php';

/**
 * Created by PhpStorm.
 * User: Mario
 * Date: 06/11/2016
 * Time: 11:37
 */
class Usuario
{
    /**RECURSOS DISPONIBLES PARA REGISTRAR*/
    static $attrdisponibles=array('username','pass','nombre','email','avatar');
    /**RECURSOS OBLIGATORIOS PARA REGISTRAR*/
    static $attroregister=array('username','pass','nombre','email');
    /**RECURSOS DISPONIBLES Y OBLIGATORIOS PARA HACER EL LOGIN*/
    static $attrologin=array('username','pass');


    /**HTTP METHODS*/
    public static function get($peticion){
        if(!empty($peticion[0]) && !empty($peticion[1]) && $peticion[1]==Api::R_TEMAS && empty($peticion[2])){
            //SACAMOS TODOS LOS TEMAS RELACIONADOS CON UN USUARIO
            $id=$peticion[0];
            $temas= self::getTemasbyUserID($id);
            if(!empty($temas)){
                http_response_code(200);
                return ["estado"=>ExceptionApi::EXITO,"temas"=>$temas];
            }else{
                throw new ExceptionApi(ExceptionApi::EMTPY_QUERY, "Sin Resultado", 400);
            }

        }elseif(empty($peticion[1])){
            //SACAMOS UN USUARIO ESPECIFICO
            $id=$peticion[0];
            $usuario= self::getOneUser($id);
            if(!empty($usuario)){
                http_response_code(200);
                return ["estado"=>ExceptionApi::EXITO,"user"=>$usuario];
            }else{
                throw new ExceptionApi(ExceptionApi::EMTPY_QUERY, "Sin resultados", 400);
            }

        }elseif(empty($peticion[0])){
            //OBTENER TODOS LOS USUARIOS DEL FORO
            //LEEMOS LOS GET
            $usuarios=self::getAllUsers();
            if(!empty($usuarios)) {
                http_response_code(200);
                return
                    [
                        "estado" => ExceptionApi::EXITO,
                        "users" => (array)$usuarios
                    ];
            }else{
                throw new ExceptionApi(ExceptionApi::EMTPY_QUERY, "Sin resultados");
            }

        }else{
            throw new ExceptionApi(ExceptionApi::PARAMSINCORRECT, "Url mal formada");
        }
    }
    public static function post($peticion){
        if(!empty($peticion[0]) && !empty($peticion[1]) && $peticion[1]==Api::R_USARIO_AVATAR && empty($peticion[2])) {
            //AVATAR
            $id=$peticion[0];
            //ANTES DE INSERTARLO EN LA TABLA MIRO A VER SI ME HAN SUBIDO UNA FOTO
            if (!empty($_FILES)){
                $key=key($_FILES);
                $file=$_FILES[$key];
                $target_dir = "resources/files/avatar/";
                $target_file = $target_dir .$file["name"];
                $imageFileType = pathinfo($target_file, PATHINFO_EXTENSION);
                $nombrefile=$id."_"."avatar";
                $src=$target_dir.$nombrefile.".".$imageFileType;
                $resultado=self::updateAvatar($src,$id);
            }else{
                throw new ExceptionApi(ExceptionApi::FALLIDO,"No se ha encontrado un archivo valido");
            }
            //Compruebo que se ha subido el avatar correctamente y si es asi... EXITOO
            if($resultado){
                self::createimgandupload($target_file, $nombrefile, $target_dir,$file["tmp_name"]);
                http_response_code(200);
                return ["estado"=>ExceptionApi::EXITO,"pathfile"=>$src];
            }else{
                throw new ExceptionApi(ExceptionApi::DESCONOCIDO,"Error al subir la imagen avatar, intentelo mas tarde");
            }
        }elseif($peticion[0] == Api::R_USUARIO_REGISTER && empty($peticion[1])  ) {
            //REGISTRO
            $cuerpo=file_get_contents('php://input');
            $jsonobj=json_decode($cuerpo);
            //SI LLEGA EL JSON A NULL ESTARA MAL ESTRUCTURADO
            self::checkJson($jsonobj);
            self::limpiarnulljson($jsonobj);
            //CHECKEO SI LOS PARAMETROS QUE ME HAN PASADO ESTAN EN LOS DISPONIBLES Y ME HAN INTRODUCIDO LOS OBLIGATORIOS
            if(($error=self::checkinarrays($jsonobj,self::$attrdisponibles,self::$attroregister))!=ExceptionApi::EXITO)throw new ExceptionApi($error,"Revisa los parametros introducidos");
            //SI NOS HAN SUBIDO LA IMAGEN CODIFICADA PODEMOS RECOGERLA CON ESTE METODO
            self::checkAvatarnotEmptyandUpload($jsonobj);
            //REGISTRAMOS EL USARIO Y SI HA SIDO EXITOSO DEVOLVEMOS EL OBJETO
            $resultado= self::registarUser($jsonobj);
            if($resultado){
                http_response_code(200);
                return ["estado"=>ExceptionApi::EXITO,"user"=>$resultado];
            }else{
                throw new ExceptionApi(ExceptionApi::FALLIDO,"Ha ocurrido un error durante el registro");
            }
        }elseif($peticion[0] == Api::R_USUARIO_LOGIN && empty($peticion[1])){
            //LOGIN
            $cuerpo=file_get_contents('php://input');
            $jsonobj=json_decode($cuerpo);
            //SI LLEGA EL JSON A NULL ESTARA MAL ESTRUCTURADO
            self::checkJson($jsonobj);
            self::limpiarnulljson($jsonobj);
            //CHECKEO SI LOS PARAMETROS QUE ME HAN PASADO ESTAN EN LOS DISPONIBLES Y ME HAN INTRODUCIDO LOS OBLIGATORIOS
            if(($error=self::checkinarrays($jsonobj,self::$attrologin,self::$attrologin))!=ExceptionApi::EXITO)throw new ExceptionApi($error,"Revisa los parametros introducidos");
            //COMPRUEBO QUE EL USUARIO INTRODUCIDO ESTA EN LA TABLA
            $resultado=self::login($jsonobj);
            if($resultado){
                http_response_code(200);
                return ["estado"=>ExceptionApi::EXITO,"mensaje"=>utf8_encode("Login realizado con exito")];
            }else{
                throw new ExceptionApi(ExceptionApi::LOGINFAILED,"Usuario o contraseña invalidos");
            }
        }
        else {
            throw new ExceptionApi(ExceptionApi::ESTADO_URL_INCORRECTA, "Url mal formada", 400);
        }
    }
    public static function put($peticion){
        if (!empty($peticion[0]) && empty($peticion[1]) ){
            //ACTUALIZO USUARIO
            $cuerpo=file_get_contents('php://input');
            $jsonobj=json_decode($cuerpo);
            //SI LLEGA EL JSON A NULL ESTARA MAL ESTRUCTURADO
            self::checkJson($jsonobj);
            self::limpiarnulljson($jsonobj);
            //CHECKEO SI LOS PARAMETROS QUE ME HAN PASADO ESTAN EN LOS DISPONIBLES
            if(($error=self::checkinarray($jsonobj,self::$attrdisponibles))!=ExceptionApi::EXITO)throw new ExceptionApi($error,"Revisa los parametros introducidos");
            $id=$peticion[0];
            $resultado=self::updateUser($jsonobj,$id);
            if($resultado){
                http_response_code(200);
                return ["estado"=>ExceptionApi::EXITO,"user"=>$resultado];
            }else{
                throw new ExceptionApi(ExceptionApi::FALLIDO,"No se han actualizado los datos");
            }
        }
        else {
            throw new ExceptionApi(ExceptionApi::ESTADO_URL_INCORRECTA, "Url mal formada", 400);
        }
    }
    public static function delete($peticion){
        if(!empty($peticion[0]) && !empty($peticion[1]) && $peticion[1]==Api::R_TEMAS && !empty($peticion[2]) && !empty($peticion[3]) && $peticion[3]==Api::R_COMENTARIOS && !empty($peticion[4]) ){
            //AQUI BORRAREMOS UN COMENTARIO RELACIONADO A UN TEMA ESPECIFICO QUE HA ESCRITO A UN USUARIO
            $iduser = $peticion[0];
            $idtema = $peticion[2];
            $idcomentario=$peticion[4];
            $resultado = self::delAsocUserTema($iduser, $idtema,$idcomentario);
            if ($resultado) {
                http_response_code(200);
                return ["estado" => ExceptionApi::EXITO, "mensaje" => utf8_encode("Comentario eliminado con exito")];
            } else {
                throw new ExceptionApi(ExceptionApi::FALLIDO, "Ha ocurrido un error durante la eliminacion");
            }
        }elseif($peticion[1]==Api::R_TEMAS && !empty($peticion[2]) && empty($peticion[3])){
            //AQUI BORRAREMOS UN TEMA ASOCIADO A UN USUARIO
            $iduser = $peticion[0];
            $idtema = $peticion[2];
            $resultado = self::delAsocUserTema($iduser, $idtema,null);
            if ($resultado > 0) {
                http_response_code(200);
                return ["estado" => ExceptionApi::EXITO, "mensaje" => utf8_encode("Tema asocaiado eliminado con exito")];
            } else {
                throw new ExceptionApi(ExceptionApi::FALLIDO, "Ha ocurrido un error durante la eliminacion");
            }
        }elseif($peticion[1]==Api::R_TEMAS && empty($peticion[2])){
            //AQUI BORRAREMOS TODOS TEMAS ASOCIADOS A UN USUARIO
            $iduser = $peticion[0];
            $resultado = self::delAsocUserTema($iduser,null,null);
            if ($resultado) {
                http_response_code(200);
                return ["estado" => ExceptionApi::EXITO, "mensaje" => utf8_encode("Temas asocaiados eliminados con exito")];
            } else {
                throw new ExceptionApi(ExceptionApi::FALLIDO, "Ha ocurrido un error durante la eliminacion");
            }
        }elseif($peticion[1]==Api::R_USARIO_AVATAR && empty($peticion[2])){
            //AQUI BORRAREMOS EL AVATAR ASOCIADO A UN USUARIO
            $iduser = $peticion[0];
            $resultado = self::updateAvatar(null,$iduser);
            if ($resultado) {
                http_response_code(200);
                return ["estado" => ExceptionApi::EXITO, "mensaje" => utf8_encode("Avatar eliminado con exito")];
            } else {
                throw new ExceptionApi(ExceptionApi::FALLIDO, "Ha ocurrido un error durante la eliminacion");
            }
        }elseif (!empty($peticion[0]) && empty($peticion[1])) {
            //USUARIO
            $id=$peticion[0];
            $resultado= self::delUser($id);
            if($resultado){
                http_response_code(200);
                return ["estado"=>ExceptionApi::EXITO,"mensaje"=>utf8_encode("Usuario eliminado con exito")];
            }else{
                throw new ExceptionApi(ExceptionApi::FALLIDO,"Ha ocurrido un error durante la eliminacion");
            }
        }else{
            throw new ExceptionApi(ExceptionApi::ESTADO_URL_INCORRECTA,"Url Mal Formada");
        }
    }

    /**USER*/
    //**SELECT**//
    public static function login($jsonobj){
        $dao=new DaoUsuario();
        try{
            return $dao->login($jsonobj);
        }catch(PDOException $e){
            throw new ExceptionApi(ExceptionApi::ERRORBD,$e->getMessage());
        }
    }
    public static function getAllUsers(){
        $dao= new DaoUsuario();
        try{
            $resultados=$dao->getAllUsers();
        }catch(PDOException $e){
            throw new ExceptionApi(ExceptionApi::ERRORBD,$e->getMessage());
        }
        return $resultados;
    }
    public static function getOneUser($id){
        $dao= new DaoUsuario();
        try{
            $resultado=$dao->getOneUser($id);
        }catch(PDOException $e){
            throw new ExceptionApi(ExceptionApi::ERRORBD,$e->getMessage());
        }
        return $resultado;
    }

    //**INSERT**//
    public static function registarUser($jsonobj){
        $dao=new DaoUsuario();
        try{
            return $dao->registarUser($jsonobj);
        }catch(PDOException $e){
            throw new ExceptionApi(ExceptionApi::ERRORBD,$e->getMessage());
        }
    }
    //**UPDATE**//
    public static function updateUser($jsonobj, $id){
        $dao=new DaoUsuario();
        try{
            return $dao->updateupdateUser($jsonobj,$id);
        }catch(PDOException $e){
            throw new ExceptionApi(ExceptionApi::ERRORBD,$e->getMessage());
        }
    }
    public static function updateAvatar($src, $id){
        $dao=new DaoUsuario();
        try{
            return $dao->updateAvatar($src,$id);
        }catch(PDOException $e){
            throw new ExceptionApi(ExceptionApi::ERRORBD,$e->getMessage());
        }
    }

    //**DELETE**//
    public static function delUser($id){
        $dao=new DaoUsuario();
        try{
            return $dao->delUser($id);
        }catch(PDOException $e){
            throw new ExceptionApi(ExceptionApi::ERRORBD,$e->getMessage());
        }
    }


    /** *************************************************/
    /**TEMAS ASOCIADOS*/
    //**SELECT**//
    public static function getTemasbyUserID($id){
        $dao= new DaoUsuario();
        try{
            $resultado=$dao->getTemasbyUserID($id);
        }catch(PDOException $e){
            throw new ExceptionApi(ExceptionApi::ERRORBD,$e->getMessage());
        }
        return $resultado;
    }

    //**DELETE**//
    public static function delAsocUserTema($iduser, $idtema,$idcomentario){
        $dao=new DaoUsuario();
        try{
            return $dao->delAsocUserTema($iduser,$idtema,$idcomentario);
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
    public static function limpiarnulljson(&$obj){
        $obj = (object) array_filter((array) $obj, function ($val) {
            return !is_null($val);
        });
    }
    public static function createimgandupload($target_file, $nombrefile,$target_dir,$imgtmp) {

        $imageFileType = pathinfo($target_file, PATHINFO_EXTENSION);

        self::checkImg($imageFileType);
        //Subo la foto con el nombre que ami me interesa
        $imgupload = $target_dir . $nombrefile . "." . $imageFileType;
        self::uploadImg($imgupload,$imgtmp);
        return $imgupload;
    }
    public static function checkImg($imageFileType) {
// Check if image file is a actual image or fake image
        $imageFileType = strtolower($imageFileType);
        if ($imageFileType != "jpg" && $imageFileType != "png" && $imageFileType != "jpeg" && $imageFileType != "gif") {
            throw new ExceptionApi(ExceptionApi::DESCONOCIDO,'Solo son permitidos los formatos: JPEG JPG PNG GIF');
        }
        return true;
    }
    public static function uploadImg($target_file,$imgtmp) {

        if (move_uploaded_file($imgtmp, $target_file)) {
            //die( "The file ". basename( $_FILES["fotocontacto"]["name"]). " has been uploaded.");
        } else {
            throw new ExceptionApi(ExceptionApi::DESCONOCIDO,'Parece que ha habido algun error,vuelva a intentarlo mas tarde');

        }
    }
    public static function checkAvatarnotEmptyandUpload(&$jsonobj){

        if(!empty($jsonobj->{'avatar'})){
            $path='resources/files/avatar/'.$jsonobj->{'username'}.'_avatar.png';
            if(self::uploadImgStringEncoded($path,$jsonobj->{'avatar'})) {
                $jsonobj->{'avatar'}=$path;
            }else{
                throw new ExceptionApi(ExceptionApi::DESCONOCIDO,"Error al subir el avatar encoded, intentelo mas tarde");
            }
        }

    }
    public static function uploadImgStringEncoded($path,$encodedimg){
        return file_put_contents($path,base64_decode($encodedimg));
    }

}