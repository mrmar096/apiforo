<?php
abstract class View{

    // Código de error
    public $estado;

    public abstract function imprimir($cuerpo);
}
?>