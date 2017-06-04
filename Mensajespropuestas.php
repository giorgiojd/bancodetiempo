<?php

/**
 * Created by PhpStorm.
 * User: susan
 * Date: 02/06/2017
 * Time: 16:12
 */
require_once("Resource.php");
class Mensajespropuestas extends Resource
{
    private $_recurso; //metodo de la clase que es llamado mediante la peticion


    public function __construct()
    {
        /*el constructor inicializa el de REST para las peticiones y abre la conexion a base de datos*/
        parent::__construct();
        $this->conexionDB();
    }

    protected function mensajespropuestas($id_mensajespropuestas = -1)
    {
        if ($_SERVER['REQUEST_METHOD'] == "GET") {
            $id_mens = (int)$id_mensajespropuestas;
            if ($id_mens <= 0) { //caso en el que recuperamos todos las mensaje
                $query = $this->_conn->query("SELECT id_mensajespropuestas, id_propuestas, id_usuarios, mensajes, fecha_envio FROM mensajespropuestas Order by fecha_envio DESC");
            } else { //caso en el que recuperamos 1 mensaje
                $query = $this->_conn->prepare("SELECT id_mensajespropuestas, id_propuestas, id_usuarios, mensajes, fecha_envio FROM mensajespropuestas where id_mensajespropuestas = :id_mensajespropuestas Order by fecha_envio DESC");
                $query->bindValue(":id_mensajespropuestas", $id_mens);
                $query->execute();
            }
            $filas = $query->fetchAll(PDO::FETCH_ASSOC);
            $num = count($filas);
            if ($num > 0) {
                $respuesta['estado'] = 'correcto';
                $respuesta['mensajes'] = $filas;
                $this->sent_response(json_encode($respuesta), 200);
            }
            $this->sent_response($this->get_error(2), 204); //peticion sin contenido Not Content
        } elseif ($_SERVER['REQUEST_METHOD'] == "DELETE"){
            $id_mens = (int) $id_mensajespropuestas;
            if ($id_mens >= 0) {
                $query = $this->_conn->prepare("DELETE FROM mensajespropuestas where id_mensajespropuestas =:id_mensajespropuestass");
                $query->bindValue(":id_mensajespropuestas", $id_mens);
                $query->execute();
                $n_deletes = $query->rowCount();
                if ($n_deletes == 1) {
                    $resp = array('estado' => "correcto", "msg" => "mensajes de propuesta borrado.");
                    $this->sent_response(json_encode($resp), 200);
                } else {
                    $this->sent_response(json_encode($this->get_error(33)), 400); //error borrando mensajespropuestas. Bad Request
                }
            }
            $this->sent_response(json_encode($this->get_error(10)), 400);//faltan datos. Bad Request

        } elseif ($_SERVER['REQUEST_METHOD'] == "POST") {
            if (isset( $this->data_request['id_propuestas'], $this->data_request['id_usuarios'], $this->data_request['mensajes'], $this->data_request['fecha_envio'])) {

                $id_propuestas = $this->data_request['id_propuestas'];
                $id_usuarios = $this->data_request['id_usuarios'];
                $mensajes = $this->data_request['mensajes'];
                $fecha_envio = $this->data_request['fecha_envio'];

                $query = $this->_conn->prepare("Insert into mensajespropuestas (id_propuestas, id_usuarios, mensajes, fecha_envio ) VALUES (:id_propuestas, :id_usuarios, :mensajes, :fecha_envio)");
                $query->bindValue(":id_propuestas", $id_propuestas);
                $query->bindValue(":id_usuarios", $id_usuarios);
                $query->bindValue(":mensajes", $mensajes);
                $query->bindValue(":fecha_envio", $fecha_envio);

                $query->execute();
                if ($query->rowCount() == 1) {
                    $id = $this->_conn->lastInsertId();
                    $respuesta['estado'] = 'correcto';
                    $respuesta['msg'] = 'mensaje de propuesta creado correctamente';
                    $respuesta['mensajespropuestas']['id_mensajespropuestas'] = $id;
                    $respuesta['mensajespropuestas']['id_propuestas'] = $id_propuestas;
                    $respuesta['mensajespropuestas']['id_usuarios'] = $id_usuarios;
                    $respuesta['mensajespropuestas']['mensajes'] = $mensajes;
                    $respuesta['mensajespropuestas']['fecha_envio'] = $fecha_envio;


                    $this->sent_response(json_encode($respuesta), 200);//enviamos la respuesta y el estado OK
                } else
                    $this->sent_response(json_encode($this->get_error(34)), 400);//Error en la sentencia de creacion de mensajes a propuesta. Bad Request
            } else {
                $this->sent_response(json_encode($this->get_error(10)), 400);// Faltan datos. Bad Request
            }
        } elseif ($_SERVER['REQUEST_METHOD'] == "PUT" ){//Para actualizar todos los registros de un mensaje de propuesta
            $id_mens = (int) $id_mensajespropuestas;
            if ($id_mens <= 0){
                $this->sent_response(json_encode($this->get_error(10)), 400);
            }

            if (isset( $this->data_request['id_propuestas'], $this->data_request['id_usuarios'], $this->data_request['mensajes'], $this->data_request['fecha_envio'] )) {

                $id_propuestas = $this->data_request['id_propuestas'];
                $id_usuarios = $this->data_request['id_usuarios'];
                $mensajes = $this->data_request['mensajes'];
                $fecha_envio = $this->data_request['fecha_envio'];


                $query = $this->_conn->prepare("Update mensajespropuestas set id_propuestas = :id_propuestas,  mensajes = :mensajes, fecha_envio= :fecha_envio, id_usuarios=:id_usuarios where id_mensajespropuestas = :id_mensajespropuestas");
                $query->bindValue(":id_mensajespropuestas", $id_mens);
                $query->bindValue(":id_propuestas", $id_propuestas);
                $query->bindValue(":id_usuarios", $id_usuarios);
                $query->bindValue(":mensajes", $mensajes);
                $query->bindValue(":fecha_envio", $fecha_envio);

                $query->execute();

                if ($query->rowCount() == 1) {
                    $respuesta['estado'] = 'correcto';
                    $respuesta['msg'] = 'mensaje de propuesta actualizado correctamente';
                    $respuesta['mensajespropuesta']['id_mensajespropuestas'] = $id_mens;
                    $respuesta['mensajespropuesta']['id_propuestas'] = $id_propuestas;
                    $respuesta['mensajespropuesta']['id_usuarios'] = $id_usuarios;
                    $respuesta['mensajespropuesta']['mensajes'] = $mensajes;
                    $respuesta['mensajespropuesta']['fecha_envio'] = $fecha_envio;

                    $this->sent_response(json_encode($respuesta), 200);
                }
                else
                    $this->sent_response(json_encode($this->get_error(34)), 400); //error en la sentencia de actuzliacion de mensaje a propuesta. Bad request
            } else {
                $this->sent_response(json_encode($this->get_error(10)), 400); //Faltan datos. Bad Request
            }

        }elseif ($_SERVER['REQUEST_METHOD'] == "PATCH") {

            $id_mens = (int)$id_mensajespropuestas;
            $select = " Update mensajespropuestas set";

            foreach ($this->data_request as $key => $value) { //montamos la query dinamicamente con los parametros que nos envian
                if ($key !== "request") {
                    $select = $select . " , " . $key . "='" . $value . "'";
                }
            }
            $contador = 0;
            $select = str_replace("mensajespropuestas set ,", "mensajespropuestas set", $select, $contador);
            $select = $select . " where id_mensajespropuestas = " . $id_mens;

            if ($contador > 0) {
                $query = $this->_conn->prepare($select);
                $query->execute();
                $num = $query->rowCount();
                if ($num > 0) {
                    $respuesta['estado'] = 'correcto';
                    $respuesta['msg'] = 'mensaje de propuesta actualizado correctamente';
                    $respuesta['mensajespropuestas']['id'] = $id_mens;

                    $this->sent_response(json_encode($respuesta), 200);
                } else {
                    $this->sent_response(json_encode($this->get_error(34)), 400); //error en la sentencia de actuzliacion de mensaje a propuesta. Bad request
                }
            }
            $this->sent_response(json_encode($this->get_error(10)), 400); //Faltan datos. Bad Request
        }
        $this->sent_response(json_encode($this->get_error(0)), 404); //peticion no encontrada. Bad Request

    }

}
$mensajespropuestas = new Mensajespropuestas();
$mensajespropuestas->responderRequest();