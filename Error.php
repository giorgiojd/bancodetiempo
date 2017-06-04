<?php
/**
 * Created by PhpStorm.
 * User: susan
 * Date: 03/06/2017
 * Time: 17:42
 */
require_once("Resource.php");
class Propuestas extends Resource
{
    private $_recurso; //metodo de la clase que es llamado mediante la peticion


    public function __construct()
    {
        /*el constructor inicializa el de REST para las peticiones y abre la conexion a base de datos*/
        parent::__construct();
       // $this->conexionDB();
    }

    protected function error(){
        $this->sent_response(json_encode($this->get_error(0)), 200);
    }
}

$resource = new Resource();
$resource->responderRequest();