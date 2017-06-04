<?php
/**
 * Created by PhpStorm.
 * User: susan
 * Date: 02/06/2017
 * Time: 14:00
 */

require_once("Resource.php");
class Ambitospropuestas extends Resource
{


    private $_recurso; //metodo de la clase que es llamado mediante la peticion


    public function __construct()
    {
        /*el constructor inicializa el de REST para las peticiones y abre la conexion a base de datos*/
        parent::__construct();
        $this->conexionDB();
    }

    private function existe_ambitospropuestas($des, $id = -1)
    {//puede que se use con un id, en caso de actualizar y sin id en caso de ser creacion
        $id_ambitospropuestas = (int)$id;
        if ($id_ambitospropuestas >= 0) {
            $query = $this->_conn->prepare("SELECT descripcion from ambitospropuestas WHERE descripcion = :des and id_ambitospropuestas <> :id_ambitospropuestas Order by id_ambitospropuestas ASC");
            $query->bindValue(":id_ambitospropuestas", $id_ambitospropuestas);
        } else {
            $query = $this->_conn->prepare("SELECT descripcion from ambitospropuestas WHERE descripcion = :des Order by id_ambitospropuestas ASC");
        }
        $query->bindValue(":des", $des);
        $query->execute();
        if ($query->fetch(PDO::FETCH_ASSOC)) {
            return true;
        } else
            return false;

    }

    protected function ambitospropuestas($id_ambitospropuestas = -1)
    {
        if ($_SERVER['REQUEST_METHOD'] == "GET") {
            $id = (int)$id_ambitospropuestas;
            if ($id <= 0) { //caso en el que queremos devolver todos los ambitospropuestas existentes
                $select = "SELECT id_ambitospropuestas, descripcion FROM ambitospropuestas Order by id_ambitospropuestas";
                $query = $this->_conn->query($select);
            } else { //caso en el que queremos devolver un ambitospropuestas concreto
                $query = $this->_conn->prepare("SELECT id_ambitospropuestas, descripcion FROM ambitospropuestas where id_ambitospropuestas = :id_ambitospropuestas Order by id_ambitospropuestas");
                $query->bindValue(":id_ambitospropuestas", $id);
                $query->execute();
            }
            $filas = $query->fetchAll(PDO::FETCH_ASSOC);
            $num = count($filas);
            if ($num > 0) {
                $respuesta['estado'] = 'correcto';
                $respuesta['ambitospropuestas'] = $filas;
                $this->sent_response(json_encode($respuesta), 200); //enviamos la respuesta y el estado OK
            }
            $this->sent_response(json_encode($this->get_error(2)), 204); //petición sin contenido. No Content //REVISAR

        } elseif ($_SERVER['REQUEST_METHOD'] == "POST") {

            if (isset($this->data_request['descripcion'])) {
                $descripcion = $this->data_request['descripcion'];

                if (!$this->existe_ambitospropuestas($descripcion, -1)) {
                    $query = $this->_conn->prepare("Insert into ambitospropuestas (descripcion) VALUES (:descripcion)");
                    $query->bindValue(":descripcion", $descripcion);
                    $query->execute();
                    if ($query->rowCount() == 1) {
                        $id = $this->_conn->lastInsertId();
                        $respuesta['estado'] = 'correcto';
                        $respuesta['msg'] = 'ambitospropuestas creado correctamente';
                        $respuesta['ambitospropuestas']['id_ambitospropuestas'] = $id;
                        $respuesta['ambitospropuestas']['descripcion'] = $descripcion;
                        $this->sent_response(json_encode($respuesta), 200);//enviamos la respuesta y el estado OK
                    } else
                        $this->sent_response(json_encode($this->get_error(23)), 400);//Error en la sentencia de creacion de ambitospropuestas. Bad Request
                } else
                    $this->sent_response(json_encode($this->get_error(24)), 400);// ya existe un ambitospropuestas con esa descripcion.  Bad Request
            } else {
                $this->sent_response(json_encode($this->get_error(10)), 400);// Faltan datos. Bad Request
            }
        } elseif ($_SERVER['REQUEST_METHOD'] == "PUT" OR $_SERVER['REQUEST_METHOD'] == "PATCH") { //se ponen los dos metodos aqui porque solo tiene 1 campo para actualizar
            $id = (int)$id_ambitospropuestas;
            if ($id <= 0) {
                $this->sent_response(json_encode($this->get_error(10)), 400);//Faltan datos . Bad Request
            }
            if (isset($this->data_request['descripcion'])) {
                $descripcion = $this->data_request['descripcion'];
                if (!$this->existe_ambitospropuestas($descripcion, $id)) {
                    $query = $this->_conn->prepare("Update ambitospropuestas set descripcion=:descripcion where id_ambitospropuestas = :id_ambitospropuestas");
                    $query->bindValue(":id_ambitospropuestas", $id);
                    $query->bindValue(":descripcion", $descripcion);
                    $query->execute();
                    if ($query->rowCount() == 1) {
                        $respuesta['estado'] = 'correcto';
                        $respuesta['msg'] = 'ambitospropuestas actualizado correctamente';
                        $respuesta['ambitospropuestas']['id_ambitospropuestas'] = $id;
                        $respuesta['ambitospropuestas']['descripcion'] = $descripcion;
                        $this->sent_response(json_encode($respuesta), 200);//enviamos la respuesta y el estado OK
                    } else
                        $this->sent_response(json_encode($this->get_error(16)), 400);//Es la misma descripcion que ya tenia. Bad Request
                } else
                    $this->sent_response(json_encode($this->get_error(24)), 400);// ya existe un ambitospropuestas con esa descripcion . Bad Request
            } else {
                $this->sent_response(json_encode($this->get_error(10)), 400);// Faltan datos . Bad Request
            }
        } elseif ($_SERVER['REQUEST_METHOD'] == "DELETE") {
            $id = (int)$id_ambitospropuestas;
            if ($id >= 0) {
                $query = $this->_conn->prepare("DELETE FROM ambitospropuestas WHERE id_ambitospropuestas =:id_ambitospropuestas");
                $query->bindValue(":id_ambitospropuestas", $id);
                $query->execute();
                $n_deletes = $query->rowCount();
                if ($n_deletes == 1) {
                    $resp = array('estado' => "correcto", "msg" => "ambitospropuestas Borrado");
                    $this->sent_response(json_encode($resp), 200); //enviamos la respuesta y el estado OK
                } else {
                    $this->sent_response(json_encode($this->get_error(25)), 400); // error borrando ambitospropuestas. Bad Request
                }
            }
            $this->sent_response(json_encode($this->get_error(10)), 400);  //petición no encontrada . Bad Request
        }
        $this->sent_response(json_encode($this->get_error(0)), 404);  //petición no encontrada . Bad Request

    }
}
$ambito = new Ambitospropuestas();
$ambito->responderRequest();