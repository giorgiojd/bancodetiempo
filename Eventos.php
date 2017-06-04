<?php

/**
 * Created by PhpStorm.
 * User: susan
 * Date: 02/06/2017
 * Time: 10:38
 */
require_once("Resource.php");
class Eventos extends Resource
{
    private $_recurso; //metodo de la clase que es llamado mediante la peticion


    public function __construct()
    {
        /*el constructor inicializa el de REST para las peticiones y abre la conexion a base de datos*/
        parent::__construct();
        $this->conexionDB();
    }

    private function existe_eventos($des, $fecha, $id = -1)
    {//puede que se use con un id, en caso de actualizar y sin id en caso de ser creacion
        $id_eventos = (int)$id;
        if ($id_eventos >= 0) {
            $query = $this->_conn->prepare("SELECT descripcion, fecha_evento from eventos WHERE descripcion = :des and fecha_evento = :fecha_evento  and id_eventos <> :id_eventos Order by fecha_evento DESC");
            $query->bindValue(":id_eventos", $id_eventos);
            $query->bindValue(":fecha_evento", $fecha);
        } else {
            $query = $this->_conn->prepare("SELECT descripcion, fecha_evento from eventos WHERE descripcion = :des and fecha_evento = :fecha_evento  Order by fecha_evento DESC");
        }
        $query->bindValue(":des", $des);
        $query->bindValue(":fecha_evento", $fecha);
        $query->execute();
        if ($query->fetch(PDO::FETCH_ASSOC)) {
            return true;
        } else
            return false;

    }

    protected function eventos($id_eventos = -1)
    {
        if ($_SERVER['REQUEST_METHOD'] == "GET") {
            $id = (int)$id_eventos;
            if ($id <= 0) { //caso en el que queremos devolver todos los eventos existentes
                $select = "SELECT id_eventos, descripcion, fecha_evento FROM eventos Order by id_eventos";
                $query = $this->_conn->query($select);
            } else { //caso en el que queremos devolver un eventos concreto
                $query = $this->_conn->prepare("SELECT id_eventos, descripcion, fecha_evento FROM eventos where id_eventos = :id_eventos Order by id_eventos");
                $query->bindValue(":id_eventos", $id);
                $query->execute();
            }
            $filas = $query->fetchAll(PDO::FETCH_ASSOC);
            $num = count($filas);
            if ($num > 0) {
                $respuesta['estado'] = 'correcto';
                $respuesta['eventos'] = $filas;
                $this->sent_response(json_encode($respuesta), 200); //enviamos la respuesta y el estado OK
            }
            $this->sent_response(json_encode($this->get_error(2)), 204); //petición sin contenido. No Content //REVISAR

        } elseif ($_SERVER['REQUEST_METHOD'] == "POST") {

            if (isset($this->data_request['descripcion'], $this->data_request['fecha_evento'], $this->data_request['id_usuarios'])) {
                $descripcion = $this->data_request['descripcion'];
                $fecha_evento = $this->data_request['fecha_evento'];
                $id_usuarios = $this->data_request['id_usuarios'];

                if (!$this->existe_eventos($descripcion, $fecha_evento, -1)) {
                    $query = $this->_conn->prepare("Insert into eventos (descripcion, fecha_evento, id_usuarios) VALUES (:descripcion, :fecha_evento, :id_usuarios)");
                    $query->bindValue(":descripcion", $descripcion);
                    $query->bindValue(":fecha_evento", $fecha_evento);
                    $query->bindValue(":id_usuarios", $id_usuarios);
                    $query->execute();
                    if ($query->rowCount() == 1) {
                        $id = $this->_conn->lastInsertId();
                        $respuesta['estado'] = 'correcto';
                        $respuesta['msg'] = 'evento creado correctamente';
                        $respuesta['evento']['id_evento'] = $id;
                        $respuesta['evento']['descripcion'] = $descripcion;
                        $respuesta['evento']['fecha_evento'] = $fecha_evento;
                        $respuesta['evento']['id_usuarios'] = $id_usuarios;

                        $this->sent_response(json_encode($respuesta), 200);//enviamos la respuesta y el estado OK
                    } else
                        $this->sent_response(json_encode($this->get_error(31)), 400);//Error en la sentencia de creacion de evento. Bad Request
                } else
                    $this->sent_response(json_encode($this->get_error(32)), 400);// ya existe un evento con esa descripcion.  Bad Request
            } else {
                $this->sent_response(json_encode($this->get_error(10)), 400);// Faltan datos. Bad Request
            }
        } elseif ($_SERVER['REQUEST_METHOD'] == "PUT") { //se ponen los dos metodos aqui porque solo tiene 1 campo para actualizar
            $id = (int)$id_eventos;
            if ($id <= 0) {
                $this->sent_response(json_encode($this->get_error(10)), 400);//Faltan datos . Bad Request
            }
            if (isset($this->data_request['descripcion'],  $this->data_request['fecha_evento'], $this->data_request['id_usuarios'])) {
                $descripcion = $this->data_request['descripcion'];
                $fecha_evento = $this->data_request['fecha_evento'];
                $id_usuarios = $this->data_request['id_usuarios'];
                if (!$this->existe_evento($descripcion, $fecha_evento,  $id)) {
                    $query = $this->_conn->prepare("Update evento set id_usuarios=:id_usuarios, descripcion=:descripcion, fecha_evento = :fecha_evento where id_evento = :id_evento");
                    $query->bindValue(":id_evento", $id);
                    $query->bindValue(":descripcion", $descripcion);
                    $query->bindValue(":id_usuarios", $id_usuarios);
                    $query->execute();
                    if ($query->rowCount() == 1) {
                        $respuesta['estado'] = 'correcto';
                        $respuesta['msg'] = 'evento actualizado correctamente';
                        $respuesta['evento']['id_evento'] = $id;
                        $respuesta['evento']['descripcion'] = $descripcion;
                        $respuesta['evento']['fecha_evento'] = $fecha_evento;
                        $respuesta['evento']['id_usuarios'] = $id_usuarios;
                        $this->sent_response(json_encode($respuesta), 200);//enviamos la respuesta y el estado OK
                    } else
                        $this->sent_response(json_encode($this->get_error(16)), 400);//Es la misma descripcion que ya tenia. Bad Request
                } else
                    $this->sent_response(json_encode($this->get_error(32)), 400);// ya existe un evento con esa descripcion . Bad Request
            } else {
                $this->sent_response(json_encode($this->get_error(10)), 400);// Faltan datos . Bad Request
            }
        }elseif ($_SERVER['REQUEST_METHOD'] == "PATCH") {

            $id = (int)$id_eventos;
            $select = " Update evento set";

            foreach ($this->data_request as $key => $value) { //montamos la query dinamicamente con los parametros que nos envian
                if ($key !== "request") {
                    $select = $select . " , " . $key . "='" . $value . "'";
                }
            }
            $contador = 0;
            $select = str_replace("evento set ,", "evento set", $select, $contador);
            $select = $select . " where id_evento = " . $id;

            if ($contador > 0) {
                $query = $this->_conn->prepare($select);
                $query->execute();
                $num = $query->rowCount();
                if ($num > 0) {
                    $respuesta['estado'] = 'correcto';
                    $respuesta['msg'] = 'evento actualizado correctamente';
                    $respuesta['evento']['id'] = $id;

                    $this->sent_response(json_encode($respuesta), 200);
                } else {
                    $this->sent_response(json_encode("no se ha encontrado registro con atributos diferentes para ese identificador"), 404);
                }
            }
            $this->sent_response(json_encode($this->get_error(10)), 400);// Faltan datos . Bad Request
        } elseif ($_SERVER['REQUEST_METHOD'] == "DELETE") {
            $id = (int)$id_eventos;
            if ($id >= 0) {
                $query = $this->_conn->prepare("DELETE FROM evento WHERE id_evento =:id_evento");
                $query->bindValue(":id_evento", $id);
                $query->execute();
                $n_deletes = $query->rowCount();
                if ($n_deletes == 1) {
                    $resp = array('estado' => "correcto", "msg" => "evento Borrado");
                    $this->sent_response(json_encode($resp), 200); //enviamos la respuesta y el estado OK
                } else {
                    $this->sent_response(json_encode($this->get_error(33)), 400); // error borrando evento. Bad Request
                }
            }
            $this->sent_response(json_encode($this->get_error(10)), 400);  //petición no encontrada . Bad Request
        }
        $this->sent_response(json_encode($this->get_error(0)), 404);  //petición no encontrada . Bad Request
    }
}
$eventos = new Eventos();
$eventos->responderRequest();