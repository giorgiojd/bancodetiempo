<?php

/**
 * Created by PhpStorm.
 * User: susan
 * Date: 02/06/2017
 * Time: 12:51
 */
require_once("Resource.php");
class Estadosacuerdos extends Resource
{


    private $_recurso; //metodo de la clase que es llamado mediante la peticion


    public function __construct()
    {
        /*el constructor inicializa el de REST para las peticiones y abre la conexion a base de datos*/
        parent::__construct();
        $this->conexionDB();
    }

    private function existe_estadosacuerdos($des, $id = -1)
    {//puede que se use con un id, en caso de actualizar y sin id en caso de ser creacion
        $id_estadosacuerdos = (int)$id;
        if ($id_estadosacuerdos >= 0) {
            $query = $this->_conn->prepare("SELECT descripcion from estadosacuerdos WHERE descripcion = :des and id_estadosacuerdos <> :id_estadosacuerdos Order by id_estadosacuerdos ASC");
            $query->bindValue(":id_estadosacuerdos", $id_estadosacuerdos);
        } else {
            $query = $this->_conn->prepare("SELECT descripcion from estadosacuerdos WHERE descripcion = :des Order by id_estadosacuerdos ASC");
        }
        $query->bindValue(":des", $des);
        $query->execute();
        if ($query->fetch(PDO::FETCH_ASSOC)) {
            return true;
        } else
            return false;

    }

    protected function estadosacuerdos($id_estadosacuerdos = -1)
    {
        if ($_SERVER['REQUEST_METHOD'] == "GET") {
            $id = (int)$id_estadosacuerdos;
            if ($id <= 0) { //caso en el que queremos devolver todos los estadosacuerdos existentes
                $select = "SELECT id_estadosacuerdos, descripcion FROM estadosacuerdos Order by id_estadosacuerdos";
                $query = $this->_conn->query($select);
            } else { //caso en el que queremos devolver un estadosacuerdos concreto
                $query = $this->_conn->prepare("SELECT id_estadosacuerdos, descripcion FROM estadosacuerdos where id_estadosacuerdos = :id_estadosacuerdos Order by id_estadosacuerdos");
                $query->bindValue(":id_estadosacuerdos", $id);
                $query->execute();
            }
            $filas = $query->fetchAll(PDO::FETCH_ASSOC);
            $num = count($filas);
            if ($num > 0) {
                $respuesta['estado'] = 'correcto';
                $respuesta['estadosacuerdos'] = $filas;
                $this->sent_response(json_encode($respuesta), 200); //enviamos la respuesta y el estado OK
            }
            $this->sent_response(json_encode($this->get_error(2)), 204); //petición sin contenido. No Content //REVISAR

        } elseif ($_SERVER['REQUEST_METHOD'] == "POST") {

            if (isset($this->data_request['descripcion'])) {
                $descripcion = $this->data_request['descripcion'];

                if (!$this->existe_estadosacuerdos($descripcion, -1)) {
                    $query = $this->_conn->prepare("Insert into estadosacuerdos (descripcion) VALUES (:descripcion)");
                    $query->bindValue(":descripcion", $descripcion);
                    $query->execute();
                    if ($query->rowCount() == 1) {
                        $id = $this->_conn->lastInsertId();
                        $respuesta['estado'] = 'correcto';
                        $respuesta['msg'] = 'estadosacuerdos creado correctamente';
                        $respuesta['estadosacuerdos']['id_estadosacuerdos'] = $id;
                        $respuesta['estadosacuerdos']['descripcion'] = $descripcion;
                        $this->sent_response(json_encode($respuesta), 200);//enviamos la respuesta y el estado OK
                    } else
                        $this->sent_response(json_encode($this->get_error(28)), 400);//Error en la sentencia de creacion de estadosacuerdos. Bad Request
                } else
                    $this->sent_response(json_encode($this->get_error(29)), 400);// ya existe un estadosacuerdos con esa descripcion.  Bad Request
            } else {
                $this->sent_response(json_encode($this->get_error(10)), 400);// Faltan datos. Bad Request
            }
        } elseif ($_SERVER['REQUEST_METHOD'] == "PUT" OR $_SERVER['REQUEST_METHOD'] == "PATCH") { //se ponen los dos metodos aqui porque solo tiene 1 campo para actualizar
            $id = (int)$id_estadosacuerdos;
            if ($id <= 0) {
                $this->sent_response(json_encode($this->get_error(10)), 400);//Faltan datos . Bad Request
            }
            if (isset($this->data_request['descripcion'])) {
                $descripcion = $this->data_request['descripcion'];
                if (!$this->existe_estadosacuerdos($descripcion, $id)) {
                    $query = $this->_conn->prepare("Update estadosacuerdos set descripcion=:descripcion where id_estadosacuerdos = :id_estadosacuerdos");
                    $query->bindValue(":id_estadosacuerdos", $id);
                    $query->bindValue(":descripcion", $descripcion);
                    $query->execute();
                    if ($query->rowCount() == 1) {
                        $respuesta['estado'] = 'correcto';
                        $respuesta['msg'] = 'estadosacuerdos actualizado correctamente';
                        $respuesta['estadosacuerdos']['id_estadosacuerdos'] = $id;
                        $respuesta['estadosacuerdos']['descripcion'] = $descripcion;
                        $this->sent_response(json_encode($respuesta), 200);//enviamos la respuesta y el estado OK
                    } else
                        $this->sent_response(json_encode($this->get_error(16)), 400);//Es la misma descripcion que ya tenia. Bad Request
                } else
                    $this->sent_response(json_encode($this->get_error(29)), 400);// ya existe un estadosacuerdos con esa descripcion.  Bad Request
            } else {
                $this->sent_response(json_encode($this->get_error(10)), 400);// Faltan datos . Bad Request
            }
        } elseif ($_SERVER['REQUEST_METHOD'] == "DELETE") {

            $id = (int)$id_estadosacuerdos;
            if ($id >= 0) {
                $query = $this->_conn->prepare("DELETE FROM estadosacuerdos WHERE id_estadosacuerdos =:id_estadosacuerdos");
                $query->bindValue(":id_estadosacuerdos", $id);
                $query->execute();
                $n_deletes = $query->rowCount();
                if ($n_deletes == 1) {
                    $resp = array('estado' => "correcto", "msg" => "estadosacuerdos Borrado");
                    $this->sent_response(json_encode($resp), 200); //enviamos la respuesta y el estado OK
                } else {
                    $this->sent_response(json_encode($this->get_error(30)), 400); // error borrando estadosacuerdos. Bad Request
                }
            }
            $this->sent_response(json_encode($this->get_error(10)), 400);  //Faltan datos . Bad Request
        }
        $this->sent_response(json_encode($this->get_error(0)), 404);  //petición no encontrada . Bad Request


    }

    protected function filtropropio($tabla,$filtro,$artributoorderby, $formaOrder )
    {
        if ($_SERVER['REQUEST_METHOD'] == "GET") {

            $filtro = explode('=', trim($filtro));
            $atributo = $filtro[0];
            $valor = $filtro[1];

            if(isset($atributo, $valor, $artributoorderby, $tabla, $formaOrder )) {
                $select = "SELECT * FROM ". $tabla. " WHERE  id_estadosacuerdos1 =" . $valor . " or id_estadosacuerdos2 =" . $valor . " Order by " . $artributoorderby . " " . $formaOrder ;
                $query = $this->_conn->prepare($select);
                $query->execute();
                $num = $query->rowCount();
                $filas = $query->fetchAll(PDO::FETCH_ASSOC);
                if ($num > 0) {
                    $respuesta['estado'] = 'correcto';
                    $respuesta['filas'] = $filas;
                    $this->sent_response(json_encode($respuesta), 200);
                }
                $this->sent_response(json_encode($this->get_error(2)), 204); //peticion sin contenido Not Content

            }
            $this->sent_response(json_encode($this->get_error(10)), 400); //Faltan datos
        }
        $this->sent_response(json_encode($this->get_error(0)), 404); //Peticion no encontrada
    }



}
$estadosacuerdos = new Estadosacuerdos();
$estadosacuerdos->responderRequest();