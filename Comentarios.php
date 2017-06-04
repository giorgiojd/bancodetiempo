<?php

/**
 * Created by PhpStorm.
 * User: susan
 * Date: 02/06/2017
 * Time: 14:00
 */
require_once("Resource.php");
class Comentarios extends Resource
{
    private $_recurso; //metodo de la clase que es llamado mediante la peticion


    public function __construct()
    {
        /*el constructor inicializa el de REST para las peticiones y abre la conexion a base de datos*/
        parent::__construct();
        $this->conexionDB();
    }


    protected function comentarios($id_comentarios = -1)
    {
        if ($_SERVER['REQUEST_METHOD'] == "GET") {
            $id = (int)$id_comentarios;
            if ($id <= 0) { //caso en el que recuperamos todos las comentarios
                $query = $this->_conn->query("SELECT id_comentarios, id_acuerdos, id_usuarios, descripcion, fecha_comentario FROM comentarios Order by fecha_comentario DESC");
            } else { //caso en el que recuperamos 1 acuerdos
                $query = $this->_conn->prepare("SELECT id_comentarios, id_acuerdos, id_usuarios, descripcion, fecha_comentario FROM comentarios where id_comentarios = :id_comentarios Order by fecha_comentario DESC");
                $query->bindValue(":id_comentarios", $id);
                $query->execute();
            }
            $filas = $query->fetchAll(PDO::FETCH_ASSOC);
            $num = count($filas);
            if ($num > 0) {
                $respuesta['estado'] = 'correcto';
                $respuesta['comentarios'] = $filas;
                $this->sent_response(json_encode($respuesta), 200);
            }
            $this->sent_response($this->get_error(2), 204);
        } elseif ($_SERVER['REQUEST_METHOD'] == "DELETE"){
            $id_comen = (int) $id_comentarios;
            if ($id_comen >= 0) {
                $query = $this->_conn->prepare("DELETE FROM comentarios where id_comentarios =:id_comentarios");
                $query->bindValue(":id_comentarios", $id_comen);
                $query->execute();
                $n_deletes = $query->rowCount();
                if ($n_deletes == 1) {
                    $resp = array('estado' => "correcto", "msg" => "comentarios borrada.");
                    $this->sent_response(json_encode($resp), 200);
                } else {
                    $this->sent_response(json_encode($this->get_error(4)), 400); /*JDSP*/
                }
            }
            $this->sent_response(json_encode($this->get_error(3)), 400);

        } elseif ($_SERVER['REQUEST_METHOD'] == "POST") {
            if (isset( $this->data_request['id_acuerdos'], $this->data_request['id_usuarios'], $this->data_request['descripcion'], $this->data_request['fecha_comentario'])) {

                $id_acuerdos = $this->data_request['id_acuerdos'];
                $id_usuarios = $this->data_request['id_usuarios'];
                $descripcion = $this->data_request['descripcion'];
                $fecha_comentario = $this->data_request['fecha_comentario'];

                $query = $this->_conn->prepare("Insert into comentarios (id_acuerdos, id_usuarios, descripcion, fecha_comentario ) VALUES (:id_acuerdos, :id_usuarios, :descripcion, :fecha_comentario)");
                $query->bindValue(":id_acuerdos", $id_acuerdos);
                $query->bindValue(":id_usuarios", $id_usuarios);
                $query->bindValue(":descripcion", $descripcion);
                $query->bindValue(":fecha_comentario", $fecha_comentario);

                $query->execute();
                if ($query->rowCount() == 1) {
                    $id = $this->_conn->lastInsertId();
                    $respuesta['estado'] = 'correcto';
                    $respuesta['msg'] = 'comentario creado correctamente';
                    $respuesta['comentario']['id_comentario'] = $id;
                    $respuesta['comentario']['id_acuerdos'] = $id_acuerdos;
                    $respuesta['comentario']['id_usuarios'] = $id_usuarios;
                    $respuesta['comentario']['descripcion'] = $descripcion;
                    $respuesta['comentario']['fecha_comentario'] = $fecha_comentario;


                    $this->sent_response(json_encode($respuesta), 200);//enviamos la respuesta y el estado OK
                } else
                    $this->sent_response(json_encode($this->get_error(26)), 400);//Error en la sentencia de creacion de propuesta. Bad Request
            } else {
                $this->sent_response(json_encode($this->get_error(10)), 400);// Faltan datos. Bad Request
            }
        } elseif ($_SERVER['REQUEST_METHOD'] == "PUT" ){//Para actualizar todos los registros de una comentario
            $id_comen = (int) $id_comentarios;
            if ($id_comen <= 0){
                $this->sent_response(json_encode($this->get_error(10)), 400);
            }

            if (isset($this->data_request['id_acuerdos'], $this->data_request['id_usuarios'], $this->data_request['descripcion'],
                $this->data_request['fecha_comentario'] )) {

                $id_acuerdos = $this->data_request['id_acuerdos'];
                $id_usuarios = $this->data_request['id_usuarios'];
                $descripcion = $this->data_request['descripcion'];
                $fecha_comentario = $this->data_request['fecha_comentario'];

                $query = $this->_conn->prepare("Update comentarios set id_acuerdos = :id_acuerdos,  id_usuarios = :id_usuarios, descripcion= :descripcion, fecha_comentario=:fecha_comentario where id_comentarios = :id_comentarios");
                $query->bindValue(":id_comentarios", $id_comen);
                $query->bindValue(":id_acuerdos", $id_acuerdos);
                $query->bindValue(":id_usuarios", $id_usuarios);
                $query->bindValue(":descripcion", $descripcion);
                $query->bindValue(":fecha_comentario", $fecha_comentario);

                $query->execute();

                if ($query->rowCount() == 1) {
                    $respuesta['estado'] = 'correcto';
                    $respuesta['msg'] = 'comentario actualizado correctamente';
                    $respuesta['comentario']['id_comentarios'] = $id_comen;
                    $respuesta['comentario']['id_acuerdos'] = $id_acuerdos;
                    $respuesta['comentario']['id_usuarios'] = $id_usuarios;
                    $respuesta['comentario']['descripcion'] = $descripcion;
                    $respuesta['comentario']['fecha_comentario'] = $fecha_comentario;

                    $this->sent_response(json_encode($respuesta), 200);
                }
                else
                    $this->sent_response(json_encode($this->get_error(27)), 400);//esrror en la sentencia de actauzlizacion de comentarios. Bad Request
            } else {
                $this->sent_response(json_encode($this->get_error(10)), 400); //Faltan datos. Bad Request
            }

        }elseif ($_SERVER['REQUEST_METHOD'] == "PATCH") {

            $id_comen = (int)$id_comentarios;
            $select = " Update comentarios set";

            foreach ($this->data_request as $key => $value) { //montamos la query dinamicamente con los parametros que nos envian
                if ($key !== "request") {
                    $select = $select . " , " . $key . "='" . $value . "'";
                }
            }
            $contador = 0;
            $select = str_replace("comentarios set ,", "comentarios set", $select, $contador);
            $select = $select . " where id_comentarios = " . $id_comen;

            if ($contador > 0) {
                $query = $this->_conn->prepare($select);
                $query->execute();
                $num = $query->rowCount();
                if ($num > 0) {
                    $respuesta['estado'] = 'correcto';
                    $respuesta['msg'] = 'comentario actualizada correctamente';
                    $respuesta['comentarios']['id'] = $id_comen;

                    $this->sent_response(json_encode($respuesta), 200);
                } else {
                    $this->sent_response(json_encode($this->get_error(27)), 400);//error al actualizar comentarios. Bad Request
                }
            }
            $this->sent_response(json_encode($this->get_error(10)), 400); //Faltan datos. Bad Request
        }
        $this->sent_response(json_encode($this->get_error(0)), 404);  //petición no encontrada . Bad Request
    }

}
$comentarios = new Comentarios();
$comentarios->responderRequest();