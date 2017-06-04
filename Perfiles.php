<?php

/**
 * Created by PhpStorm.
 * User: susan
 * Date: 01/06/2017
 * Time: 18:39
 */
require_once("Resource.php");
class Perfiles extends Resource
{
    private $_recurso; //metodo de la clase que es llamado mediante la peticion

    public function __construct()
    {
        /*el constructor inicializa el de REST para las peticiones y abre la conexion a base de datos*/
        parent::__construct();
        $this->conexionDB();
    }

    private function existe_perfil($des, $id = -1)
    {//puede que se use con un id, en caso de actualizar y sin id en caso de ser creacion
        $id_perfil = (int)$id;
        if ($id_perfil >= 0) {
            $query = $this->_conn->prepare("SELECT descripcion from perfiles WHERE descripcion = :des and id_perfiles <> :id_perfil Order by id_perfiles ASC");
            $query->bindValue(":id_perfil", $id_perfil);
        } else {
            $query = $this->_conn->prepare("SELECT descripcion from perfiles WHERE descripcion = :des Order by id_perfiles ASC");
        }
        $query->bindValue(":des", $des);
        $query->execute();
        if ($query->fetch(PDO::FETCH_ASSOC)) {
            return true;
        } else
            return false;

    }

    protected function perfiles($id_perfil = -1)
    {
        if ($_SERVER['REQUEST_METHOD'] == "GET") {
            $id = (int)$id_perfil;
            if ($id <= 0) { //caso en el que queremos devolver todos los perfiles existentes
                $select = "SELECT id_perfiles, descripcion FROM perfiles Order by id_perfiles";
                $query = $this->_conn->query($select);
            } else { //caso en el que queremos devolver un perfil concreto
                $query = $this->_conn->prepare("SELECT id_perfiles, descripcion FROM perfiles where id_perfiles = :id_perfiles Order by id_perfiles");
                $query->bindValue(":id_perfiles", $id);
                $query->execute();
            }
            $filas = $query->fetchAll(PDO::FETCH_ASSOC);
            $num = count($filas);
            if ($num > 0) {
                $respuesta['estado'] = 'correcto';
                $respuesta['perfiles'] = $filas;
                $this->sent_response(json_encode($respuesta), 200); //enviamos la respuesta y el estado OK
            }
            $this->sent_response(json_encode($this->get_error(2)), 204); //petición sin contenido. No Content //REVISAR

        } elseif ($_SERVER['REQUEST_METHOD'] == "POST") {

            if (isset($this->data_request['descripcion'])) {
                $descripcion = $this->data_request['descripcion'];

                $query = $this->_conn->prepare("Insert into perfiles (descripcion) VALUES (:descripcion)");
                $query->bindValue(":descripcion", $descripcion);
                $query->execute();
                if ($query->rowCount() == 1) {
                    $id = $this->_conn->lastInsertId();
                    $respuesta['estado'] = 'correcto';
                    $respuesta['msg'] = 'perfil creado correctamente';
                    $respuesta['perfil']['id_perfil'] = $id;
                    $respuesta['perfil']['descripcion'] = $descripcion;
                    $this->sent_response(json_encode($respuesta), 200);//enviamos la respuesta y el estado OK
                } else
                    $this->sent_response(json_encode($this->get_error(11)), 400);//Error en la sentencia de creacion de perfil. Bad Request
            } else {
                $this->sent_response(json_encode($this->get_error(10)), 400);// Faltan datos. Bad Request
            }
        } elseif ($_SERVER['REQUEST_METHOD'] == "PUT" OR $_SERVER['REQUEST_METHOD'] == "PATCH") { //se ponen los dos metodos aqui porque solo tiene 1 campo para actualizar
            $id = (int)$id_perfil;
            if ($id <= 0) {
                $this->sent_response(json_encode($this->get_error(10)), 400);//Faltan datos . Bad Request
            }
            if (isset($this->data_request['descripcion'])) {
                $descripcion = $this->data_request['descripcion'];
                if (!$this->existe_perfil($descripcion, $id)) {
                    $query = $this->_conn->prepare("Update perfiles set descripcion=:descripcion where id_perfiles = :id_perfiles");
                    $query->bindValue(":id_perfiles", $id);
                    $query->bindValue(":descripcion", $descripcion);
                    $query->execute();
                    if ($query->rowCount() == 1) {
                        $respuesta['estado'] = 'correcto';
                        $respuesta['msg'] = 'perfil actualizado correctamente';
                        $respuesta['perfil']['id_perfil'] = $id;
                        $respuesta['perfil']['descripcion'] = $descripcion;
                        $this->sent_response(json_encode($respuesta), 200);//enviamos la respuesta y el estado OK
                    } else
                        $this->sent_response(json_encode($this->get_error(16)), 400);//Es la misma descripcion que ya tenia. Bad Request
                } else
                    $this->sent_response(json_encode($this->get_error(12)), 400);// ya existe un perfil con esa descripcion . Bad Request
            } else {
                $this->sent_response(json_encode($this->get_error(10)), 400);// Faltan datos . Bad Request
            }
        } elseif ($_SERVER['REQUEST_METHOD'] == "DELETE") {
            $id = (int)$id_perfil;
            if ($id >= 0) {
                $query = $this->_conn->prepare("DELETE FROM perfiles WHERE id_perfiles =:id_perfiles");
                $query->bindValue(":id_perfiles", $id);
                $query->execute();
                $n_deletes = $query->rowCount();
                if ($n_deletes == 1) {
                    $resp = array('estado' => "correcto", "msg" => "Perfil Borrado");
                    $this->sent_response(json_encode($resp), 200); //enviamos la respuesta y el estado OK
                } else {
                    $this->sent_response(json_encode($this->get_error(17)), 400); // error borrando perfiles. Bad Request
                }
            }
            $this->sent_response(json_encode($this->get_error(0)), 404);  //petición no encontrada . Bad Request
        }


    }
}
$perfiles = new Perfiles();
$perfiles->responderRequest();