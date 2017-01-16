<?php

require_once "view.php";

/**
 * Clase para imprimir en la salida respuestas con formato XML
 */
class ViewXMl extends View
{

    /**
     * Imprime el cuerpo de la respuesta y setea el código de respuesta
     * @param mixed $cuerpo de la respuesta a enviar
     */
    public function imprimir($cuerpo)
    {
        if ($this->estado) {
            http_response_code($this->estado);
        }

        header('Content-Type: text/xml');

        $xml = new SimpleXMLElement('<respuesta/>');
        self::parsearArreglo($cuerpo, $xml);
        print $xml->asXML();

        exit;
    }

    /**
     * Convierte un array a XML
     * @param array $data arreglo a convertir
     * @param SimpleXMLElement $xml_data elemento raíz
     */
    public function parsearArreglo($data, &$xml_data)
    {
        //LE PASAREMOS UN OBJETO A ESTE BUCLE SEA UN ARRAY O SEA UN SIMPLE OBJETO CON ATTR
        //SACAMOS EL KEY QUE SERIA EL ATTR Y TAMBIEN EL VALUE PARA IR AÑADIENDOLO AL XML
        foreach ($data as $key => $value) {
            //COMPRUEBO SI ES UN ARRAY PARA METERME DENTRO DE EL Y PODER SACARLE SUS HIJOS
            if (is_array($value)) {
                //MIRO SI LA KEY ES NUMERICA SIMPLEMENTE PARA CONCATERNARLA CON ITEM
                if (is_numeric($key)) {
                    $key = 'item' . $key;
                }
                //AÑADO AL XML LA KEY SACADA Y ME LO GUARDO EN SUBNODE PARA VOLVER A PASARSELO A ESTE METODO Y QUE SIGA EL XML A PARTIR DE DONDE SE DEJO
                $subnode = $xml_data->addChild($key);
                self::parsearArreglo($value, $subnode);
            } else {
                //CUANDO EL ELEMENTO SACADO NO SEA ARRAY LE VOY AÑADDIENDO LOS HIJOS CON LAS KEYS Y LOS DATOS
                $xml_data->addChild("$key", htmlspecialchars("$value"));
            }
        }
    }
}