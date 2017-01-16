<?php

require_once 'view.php';

class ViewJson extends View {

    public function imprimir($cuerpo)
    {
        //COMPROBAMOS Y AÑADIMOS EL ESTADO
        if ($this->estado) {
            http_response_code($this->estado);
        }
        //AÑADIMOS A LA CABEZERA EL FORMATO, EN ESTE CASO JSON
        header('Content-Type: application/json; charset=utf8');
        echo json_encode($cuerpo, JSON_PRETTY_PRINT);
        exit;
    }
}

?>