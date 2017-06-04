<?php

/**
 * Created by PhpStorm.
 * User: susan
 * Date: 01/06/2017
 * Time: 23:38
 */
require_once("Resource.php");
class Propuestas extends Resource
{
    private $_recurso; //metodo de la clase que es llamado mediante la peticion


    public function __construct()
    {
        /*el constructor inicializa el de REST para las peticiones y abre la conexion a base de datos*/
        parent::__construct();
        $this->conexionDB();
    }



    protected function propuestasusuarios($id_usuarios)
    {
        $filtro = explode('=', trim($_REQUEST['request']));

        if ($_SERVER['REQUEST_METHOD'] == "GET") {
            $id_usu = (int) $id_usuarios;
            if ($id_usu > 0) {
                $query = $this->_conn->prepare("SELECT id_propuestas, id_tipospropuestas, id_usuarios, id_ambitospropuestas, descripcion, fecha_creacion, grupal, fecha_cierre, fecha_propuesta, n_horasdia, fecha_fin, n_dias, n_horastotal FROM propuestas WHERE id_usuarios = :id_usuarios Order by fecha_propuesta DESC");
            } else {
                $this->sent_response(json_encode($this->get_error(10)), 400); //Faltan datos
            }
            $query->bindValue(":id_usuarios", $id_usu);
            $query->execute();
            $filas = $query->fetchAll(PDO::FETCH_ASSOC);
            $num = count($filas);
            if ($num > 0) {
                $respuesta['estado'] = 'correcto';
                $respuesta['propuestas'] = $filas;
                $this->sent_response(json_encode($respuesta), 200);
            }
            $this->sent_response(json_encode($this->get_error(2)), 204); //peticion sin contenido Not Content
        }
        $this->sent_response(json_encode($this->get_error(0)), 404); //Peticion no encontrada
    }


    protected function propuestas($id_propuestas = -1)
    {
        if ($_SERVER['REQUEST_METHOD'] == "GET") {
            $id = (int)$id_propuestas;
            if ($id <= 0) { //caso en el que recuperamos todos las propuestas
                $query = $this->_conn->query("SELECT id_propuestas, id_tipospropuestas, id_usuarios, id_ambitospropuestas, descripcion, fecha_creacion, grupal, fecha_cierre, fecha_propuesta, n_horasdia, fecha_fin, n_dias, n_horastotal FROM propuestas Order by fecha_propuesta DESC");
            } else { //caso en el que recuperamos 1 propuesta
                $query = $this->_conn->prepare("SELECT id_propuestas, id_tipospropuestas, id_usuarios, id_ambitospropuestas, descripcion, fecha_creacion, grupal, fecha_cierre, fecha_propuesta, n_horasdia, fecha_fin, n_dias, n_horastotal FROM propuestas where id_propuestas = :id_propuestas Order by fecha_propuesta DESC");
                $query->bindValue(":id_propuestas", $id);
                $query->execute();
            }
            $filas = $query->fetchAll(PDO::FETCH_ASSOC);
            $num = count($filas);
            if ($num > 0) {
                $respuesta['estado'] = 'correcto';
                $respuesta['propuestas'] = $filas;
                $this->sent_response(json_encode($respuesta), 200);
            }
            $this->sent_response(json_encode($this->get_error(2)), 204); //peticion sin contenido Not Content
        } elseif ($_SERVER['REQUEST_METHOD'] == "DELETE"){
            $id_prop = (int) $id_propuestas;
            if ($id_prop >= 0) {
                $query = $this->_conn->prepare("DELETE FROM propuestas where id_propuestas =:id_propuestas");
                $query->bindValue(":id_propuestas", $id_prop);
                $query->execute();
                $n_deletes = $query->rowCount();
                if ($n_deletes == 1) {
                    $resp = array('estado' => "correcto", "msg" => "propuesta borrada.");
                    $this->sent_response(json_encode($resp), 200);
                } else {
                    $this->sent_response(json_encode($this->get_error(4)), 400);// error borrando Propuesta. Bad Request
                }
            }
            $this->sent_response(json_encode($this->get_error(10)), 400);//Faltan datos. Bad Request

        } elseif ($_SERVER['REQUEST_METHOD'] == "POST") {
            if (isset( $this->data_request['id_usuarios'],$this->data_request['id_tipospropuestas'], $this->data_request['id_ambitospropuestas'], $this->data_request['descripcion'],
                $this->data_request['fecha_creacion'], $this->data_request['grupal'], $this->data_request['fecha_cierre'],
                $this->data_request['fecha_propuesta'], $this->data_request['n_horasdia'], $this->data_request['fecha_fin'],
                $this->data_request['n_dias'],$this->data_request['n_horastotal'])) {

                $id_tipospropuestas = $this->data_request['id_tipospropuestas'];
                $id_usuarios = $this->data_request['id_usuarios'];
                $id_ambitospropuestas = $this->data_request['id_ambitospropuestas'];
                $descripcion = $this->data_request['descripcion'];
                $fecha_creacion = $this->data_request['fecha_creacion'];
                $grupal = $this->data_request['grupal'];
                $fecha_cierre = $this->data_request['fecha_cierre'];
                $fecha_propuesta = $this->data_request['fecha_propuesta'];
                $n_horasdia = $this->data_request['n_horasdia'];
                $fecha_fin = $this->data_request['fecha_fin'];
                $n_dias = $this->data_request['n_dias'];
                $n_horastotal = $this->data_request['n_horastotal'];


                $query = $this->_conn->prepare("Insert into propuestas (id_tipospropuestas,  id_usuarios ,  id_ambitospropuestas,  descripcion, fecha_creacion, grupal, fecha_cierre,  fecha_propuesta, n_horasdia, fecha_fin, n_dias, n_horastotal ) VALUES (:id_tipospropuestas, :id_usuarios, :id_ambitospropuestas, :descripcion, :fecha_creacion, :grupal, :fecha_cierre, :fecha_propuesta,:n_horasdia,:fecha_fin, :n_dias, :n_horastotal)");
                $query->bindValue(":id_tipospropuestas", $id_tipospropuestas);
                $query->bindValue(":id_usuarios", $id_usuarios);
                $query->bindValue(":id_ambitospropuestas", $id_ambitospropuestas);
                $query->bindValue(":descripcion", $descripcion);
                $query->bindValue(":fecha_creacion", $fecha_creacion);
                $query->bindValue(":grupal", $grupal);
                $query->bindValue(":fecha_cierre", $fecha_cierre);
                $query->bindValue(":fecha_propuesta", $fecha_propuesta);
                $query->bindValue(":n_horasdia", $n_horasdia);
                $query->bindValue(":fecha_fin", $fecha_fin);
                $query->bindValue(":n_dias", $n_dias);
                $query->bindValue(":n_horastotal", $n_horastotal);
                $query->execute();
                if ($query->rowCount() == 1) {
                    $id = $this->_conn->lastInsertId();
                    $respuesta['estado'] = 'correcto';
                    $respuesta['msg'] = 'propuestas creado correctamente';
                    $respuesta['propuestas']['id_propuestas'] = $id;
                    $respuesta['propuestas']['id_tipospropuestas'] = $id_tipospropuestas;
                    $respuesta['propuestas']['id_usuarios'] = $id_usuarios;
                    $respuesta['propuestas']['id_ambitospropuestas'] = $id_ambitospropuestas;
                    $respuesta['propuestas']['descripcion'] = $descripcion;
                    $respuesta['propuestas']['grupal'] = $grupal;
                    $respuesta['propuestas']['fecha_creacion'] = $fecha_creacion;
                    $respuesta['propuestas']['fecha_cierre'] = $fecha_cierre;
                    $respuesta['propuestas']['fecha_propuesta'] = $fecha_propuesta;
                    $respuesta['propuestas']['n_horasdia'] = $n_horasdia;
                    $respuesta['propuestas']['fecha_fin'] = $fecha_fin;
                    $respuesta['propuestas']['fecha_fin'] = $n_dias;
                    $respuesta['propuestas']['n_horastotal'] = $n_horastotal;

                    $this->sent_response(json_encode($respuesta), 200);//enviamos la respuesta y el estado OK
                } else
                    $this->sent_response(json_encode($this->get_error(37)), 400);//Error en la sentencia de creacion de propuesta. Bad Request
            } else {
                $this->sent_response(json_encode($this->get_error(10)), 400);// Faltan datos. Bad Request
            }
        } elseif ($_SERVER['REQUEST_METHOD'] == "PUT" ){//Para actualizar todos los registros de una propuesta
            $id_prop = (int) $id_propuestas;
            if ($id_prop <= 0){
                $this->sent_response(json_encode($this->get_error(10)), 400);
            }
            if (isset($this->data_request['id_tipospropuestas'], $this->data_request['id_usuarios'], $this->data_request['id_ambitospropuestas'],
                $this->data_request['descripcion'], $this->data_request['fecha_creacion'],  $this->data_request['grupal'],
                $this->data_request['fecha_cierre'], $this->data_request['n_horastotal'], $this->data_request['fecha_propuesta'], $this->data_request['n_horasdia'],
                $this->data_request['fecha_fin'], $this->data_request['n_horastotal'])) {

                $id_propuestas = $this->data_request['id_propuestas'];
                $id_tipospropuestas = $this->data_request['id_tipospropuestas'];
                $id_usuarios = $this->data_request['id_usuarios'];
                $id_ambitospropuestas = $this->data_request['id_ambitospropuestas'];
                $descripcion = $this->data_request['descripcion'];
                $fecha_creacion = $this->data_request['fecha_creacion'];//? $this->data_request['telefono']:null;
                $grupal = $this->data_request['grupal'];
                $fecha_cierre = $this->data_request['fecha_cierre'];
                $fecha_propuesta = $this->data_request['fecha_propuesta'];
                $n_horasdia = $this->data_request['n_horasdia'];
                $fecha_fin = $this->data_request['fecha_fin'];
                $n_dias = $this->data_request['n_dias'];
                $n_horastotal = $this->data_request['n_horastotal'];

                $query = $this->_conn->prepare("Update propuestas set id_tipospropuestas = :id_tipospropuestas,  id_usuarios = :id_usuarios, id_ambitospropuestas= :id_ambitospropuestas, descripcion=:descripcion, fecha_creacion = :fecha_creacion, grupal=:grupal, fecha_cierre= :fecha_cierre, fecha_propuesta=:fecha_propuesta, n_horasdia=:n_horasdia,fecha_fin=:fecha_fin , n_dias=:n_dias, n_horastotal=:n_horastotal where id_propuestas = :id_propuestas");
                $query->bindValue(":id_propuestas", $id_propuestas);
                $query->bindValue(":id_tipospropuestas", $id_tipospropuestas);
                $query->bindValue(":id_usuarios", $id_usuarios);
                $query->bindValue(":id_ambitospropuestas", $id_ambitospropuestas);
                $query->bindValue(":descripcion", $descripcion);
                $query->bindValue(":fecha_creacion", $fecha_creacion);
                $query->bindValue(":grupal", $grupal);
                $query->bindValue(":fecha_cierre", $fecha_cierre);
                $query->bindValue(":fecha_propuesta", $fecha_propuesta);
                $query->bindValue(":n_horasdia", $n_horasdia);
                $query->bindValue(":fecha_fin", $fecha_fin);
                $query->bindValue(":n_dias", $n_dias);
                $query->bindValue(":n_horastotal", $n_horastotal);
                $query->execute();

                if ($query->rowCount() == 1) {
                    // $id = $this->_conn->lastUpdateId();
                    $respuesta['estado'] = 'correcto';
                    $respuesta['msg'] = 'propuesta actualizada correctamente';
                    $respuesta['propuesta']['id'] = $id_prop;
                    $respuesta['propuesta']['descripcion'] = $descripcion;

                    $this->sent_response(json_encode($respuesta), 200);
                }
                else
                    $this->sent_response(json_encode($this->get_error(38)), 400); // error en la sentencia de actuaclizacion. Bad Request
            } else {
                $this->sent_response(json_encode($this->get_error(10)), 400);
            }

        }elseif ($_SERVER['REQUEST_METHOD'] == "PATCH") {

            $id_prop = (int)$id_propuestas;
            $select = " Update propuestas set";

            foreach ($this->data_request as $key => $value) { //montamos la query dinamicamente con los parametros que nos envian
                if ($key !== "request") {
                    $select = $select . " , " . $key . "='" . $value . "'";
                }
            }
            $contador = 0;
            $select = str_replace("propuestas set ,", "propuestas set", $select, $contador);
            $select = $select . " where id_propuestas = " . $id_prop;

            if ($contador > 0) {
                $query = $this->_conn->prepare($select);
                $query->execute();
                $num = $query->rowCount();
                if ($num > 0) {
                    $respuesta['estado'] = 'correcto';
                    $respuesta['msg'] = 'propuesta actualizada correctamente';
                    $respuesta['propuestas']['id'] = $id_prop;

                    $this->sent_response(json_encode($respuesta), 200);
                } else {
                    $this->sent_response(json_encode($this->get_error(38)), 400); // error en la sentencia de actuaclizacion. Bad Request
                }
            }
            $this->sent_response(json_encode($this->get_error(10)), 400);//Faltan datos. Bad Reques
        }
        $this->sent_response(json_encode($this->get_error(0)), 404);//Peticion no encontrada. Bad Request
    }

}
$propuestas = new Propuestas();
$propuestas->responderRequest();