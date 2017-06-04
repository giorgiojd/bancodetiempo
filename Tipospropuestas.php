<?php

/**
 * Created by PhpStorm.
 * User: susan
 * Date: 02/06/2017
 * Time: 10:33
 */
require_once("Resource.php");
class Tipospropuestas extends Resource
{

    private $_recurso; //metodo de la clase que es llamado mediante la peticion


    public function __construct()
    {
        /*el constructor inicializa el de REST para las peticiones y abre la conexion a base de datos*/
        parent::__construct();
        $this->conexionDB();
    }

    private function existe_tipospropuestas($des, $id = -1)
    {//puede que se use con un id, en caso de actualizar y sin id en caso de ser creacion
        $id_tipospropuestas = (int)$id;
        if ($id_tipospropuestas >= 0) {
            $query = $this->_conn->prepare("SELECT descripcion from tipospropuestas WHERE descripcion = :des and id_tipospropuestas <> :id_tipospropuestas Order by id_tipospropuestas ASC");
            $query->bindValue(":id_tipospropuestas", $id_tipospropuestas);
        } else {
            $query = $this->_conn->prepare("SELECT descripcion from tipospropuestas WHERE descripcion = :des Order by id_tipospropuestas ASC");
        }
        $query->bindValue(":des", $des);
        $query->execute();
        if ($query->fetch(PDO::FETCH_ASSOC)) {
            return true;
        } else
            return false;

    }

    protected function tipospropuestas($id_tipospropuestas = -1)
    {
        if ($_SERVER['REQUEST_METHOD'] == "GET") {
            $id = (int)$id_tipospropuestas;
            if ($id <= 0) { //caso en el que queremos devolver todos los tipospropuestas existentes
                $select = "SELECT id_tipospropuestas, descripcion FROM tipospropuestas Order by id_tipospropuestas";
                $query = $this->_conn->query($select);
            } else { //caso en el que queremos devolver un tipospropuestas concreto
                $query = $this->_conn->prepare("SELECT id_tipospropuestas, descripcion FROM tipospropuestas where id_tipospropuestas = :id_tipospropuestas Order by id_tipospropuestas");
                $query->bindValue(":id_tipospropuestas", $id);
                $query->execute();
            }
            $filas = $query->fetchAll(PDO::FETCH_ASSOC);
            $num = count($filas);
            if ($num > 0) {
                $respuesta['estado'] = 'correcto';
                $respuesta['tipospropuestas'] = $filas;
                $this->sent_response(json_encode($respuesta), 200); //enviamos la respuesta y el estado OK
            }
            $this->sent_response(json_encode($this->get_error(2)), 204); //petición sin contenido. No Content //REVISAR

        } elseif ($_SERVER['REQUEST_METHOD'] == "POST") {

            if (isset($this->data_request['descripcion'])) {
                $descripcion = $this->data_request['descripcion'];

                if (!$this->existe_tipospropuestas($descripcion, -1)) {
                    $query = $this->_conn->prepare("Insert into tipospropuestas (descripcion) VALUES (:descripcion)");
                    $query->bindValue(":descripcion", $descripcion);
                    $query->execute();
                    if ($query->rowCount() == 1) {
                        $id = $this->_conn->lastInsertId();
                        $respuesta['estado'] = 'correcto';
                        $respuesta['msg'] = 'tipospropuestas creado correctamente';
                        $respuesta['tipospropuestas']['id_tipospropuestas'] = $id;
                        $respuesta['tipospropuestas']['descripcion'] = $descripcion;
                        $this->sent_response(json_encode($respuesta), 200);//enviamos la respuesta y el estado OK
                    } else
                        $this->sent_response(json_encode($this->get_error(39)), 400);//Error en la sentencia de creacion de tipospropuestas. Bad Request
                } else
                    $this->sent_response(json_encode($this->get_error(41)), 400);// ya existe un tipospropuestas con esa descripcion.  Bad Request
            } else {
                $this->sent_response(json_encode($this->get_error(10)), 400);// Faltan datos. Bad Request
            }
        } elseif ($_SERVER['REQUEST_METHOD'] == "PUT" OR $_SERVER['REQUEST_METHOD'] == "PATCH") { //se ponen los dos metodos aqui porque solo tiene 1 campo para actualizar
            $id = (int)$id_tipospropuestas;
            if ($id <= 0) {
                $this->sent_response(json_encode($this->get_error(10)), 400);//Faltan datos . Bad Request
            }
            if (isset($this->data_request['descripcion'])) {
                $descripcion = $this->data_request['descripcion'];
                if (!$this->existe_tipospropuestas($descripcion, $id)) {
                    $query = $this->_conn->prepare("Update tipospropuestas set descripcion=:descripcion where id_tipospropuestas = :id_tipospropuestas");
                    $query->bindValue(":id_tipospropuestas", $id);
                    $query->bindValue(":descripcion", $descripcion);
                    $query->execute();
                    if ($query->rowCount() == 1) {
                        $respuesta['estado'] = 'correcto';
                        $respuesta['msg'] = 'tipospropuestas actualizado correctamente';
                        $respuesta['tipospropuestas']['id_tipospropuestas'] = $id;
                        $respuesta['tipospropuestas']['descripcion'] = $descripcion;
                        $this->sent_response(json_encode($respuesta), 200);//enviamos la respuesta y el estado OK
                    } else
                        $this->sent_response(json_encode($this->get_error(40)), 400);//Es la misma descripcion que ya tenia. Bad Request
                } else
                    $this->sent_response(json_encode($this->get_error(41)), 400);// ya existe un tipospropuestas con esa descripcion . Bad Request
            } else {
                $this->sent_response(json_encode($this->get_error(10)), 400);// Faltan datos . Bad Request
            }
        } elseif ($_SERVER['REQUEST_METHOD'] == "DELETE") {
            $id = (int)$id_tipospropuestas;
            if ($id >= 0) {
                $query = $this->_conn->prepare("DELETE FROM tipospropuestas WHERE id_tipospropuestas =:id_tipospropuestas");
                $query->bindValue(":id_tipospropuestas", $id);
                $query->execute();
                $n_deletes = $query->rowCount();
                if ($n_deletes == 1) {
                    $resp = array('estado' => "correcto", "msg" => "tipospropuestas Borrado");
                    $this->sent_response(json_encode($resp), 200); //enviamos la respuesta y el estado OK
                } else {
                    $this->sent_response(json_encode($this->get_error(42)), 400); // error borrando tipospropuestas. Bad Request
                }
            }
            $this->sent_response(json_encode($this->get_error(10)), 400);  //Faltan datos . Bad Request
        }

        $this->sent_response(json_encode($this->get_error(0)), 404);  //Peticion no encontrada. Bad Request
    }
}
$tipospropuestas = new Tipospropuestas();
$tipospropuestas->responderRequest();