<?php
/**
 * Created by PhpStorm.
 * User: susan
 * Date: 03/06/2017
 * Time: 17:04
 */
require_once("Resource.php");
class Acuerdos extends Resource
{
    private $_recurso; //metodo de la clase que es llamado mediante la peticion


    public function __construct()
    {
        /*el constructor inicializa el de REST para las peticiones y abre la conexion a base de datos*/
        parent::__construct();
        $this->conexionDB();
    }


    protected function acuerdos($id_acuerdos = -1)
    {
        if ($_SERVER['REQUEST_METHOD'] == "GET") {
            $id = (int)$id_acuerdos;
            if ($id <= 0) { //caso en el que recuperamos todos las acuerdos
                $query = $this->_conn->query("SELECT id_acuerdos, id_propuestas, id_usuarios2, id_estadosacuerdos1, id_estadosacuerdos2, n_horastotal FROM acuerdos  Order by id_acuerdos DESC");
            } else { //caso en el que recuperamos 1 acuerdos
                $query = $this->_conn->prepare("SELECT id_acuerdos, id_propuestas, id_usuarios2, id_estadosacuerdos1, id_estadosacuerdos2, n_horastotal FROM acuerdos where id_acuerdos = :id_acuerdos Order by id_acuerdos DESC");
                $query->bindValue(":id_acuerdos", $id);
                $query->execute();
            }
            $filas = $query->fetchAll(PDO::FETCH_ASSOC);
            $num = count($filas);
            if ($num > 0) {
                $respuesta['estado'] = 'correcto';
                $respuesta['acuerdos'] = $filas;
                $this->sent_response(json_encode($respuesta), 200);
            }
            $this->sent_response($this->get_error(2), 204);//peticion sin contenido. No Content
        } elseif ($_SERVER['REQUEST_METHOD'] == "DELETE"){
            $id_acuer = (int) $id_acuerdos;
            if ($id_acuer >= 0) {
                $query = $this->_conn->prepare("DELETE FROM acuerdos where id_acuerdos =:id_acuerdos");
                $query->bindValue(":id_acuerdos", $id_acuer);
                $query->execute();
                $n_deletes = $query->rowCount();
                if ($n_deletes == 1) {
                    $resp = array('estado' => "correcto", "msg" => "acuerdo borrado.");
                    $this->sent_response(json_encode($resp), 200);
                } else {
                    $this->sent_response(json_encode($this->get_error(18)), 400); //error borrando acuerdo. Bad Request
                }
            }
            $this->sent_response(json_encode($this->get_error(10)), 400); //faltan datos. Bad Request
        } elseif ($_SERVER['REQUEST_METHOD'] == "POST") {
            if (isset( $this->data_request['id_propuestas'], $this->data_request['id_usuarios2'], $this->data_request['id_estadosacuerdos1'], $this->data_request['id_estadosacuerdos2'],
                $this->data_request['n_horastotal'])) {

                $id_propuestas = $this->data_request['id_propuestas'];
                $id_usuarios2 = $this->data_request['id_usuarios2'];
                $id_estadosacuerdos1 = $this->data_request['id_estadosacuerdos1'];
                $id_estadosacuerdos2 = $this->data_request['id_estadosacuerdos2'];
                $n_horastotal = $this->data_request['n_horastotal'];


                $query = $this->_conn->prepare("Insert into acuerdos (id_propuestas, id_usuarios2, id_estadosacuerdos1, id_estadosacuerdos2, n_horastotal ) VALUES (:id_propuestas,:id_usuarios2,:id_estadosacuerdos1,:id_estadosacuerdos2,:n_horastotal)");
                $query->bindValue(":id_propuestas", $id_propuestas);
                $query->bindValue(":id_usuarios2", $id_usuarios2);
                $query->bindValue(":id_estadosacuerdos1", $id_estadosacuerdos1);
                $query->bindValue(":id_estadosacuerdos2", $id_estadosacuerdos2);
                $query->bindValue(":n_horastotal", $n_horastotal);

                $query->execute();
                if ($query->rowCount() == 1) {
                    $id = $this->_conn->lastInsertId();
                    $respuesta['estado'] = 'correcto';
                    $respuesta['msg'] = 'acuerdo creado correctamente';
                    $respuesta['acuerdos']['id_acuerdos'] = $id;
                    $respuesta['acuerdos']['id_propuestas'] = $id_propuestas;
                    $respuesta['acuerdos']['id_usuarios2'] = $id_usuarios2;
                    $respuesta['acuerdos']['id_estadosacuerdos1'] = $id_estadosacuerdos1;
                    $respuesta['acuerdos']['id_estadosacuerdos2'] = $id_estadosacuerdos2;
                    $respuesta['acuerdos']['n_horastotal'] = $n_horastotal;


                    $this->sent_response(json_encode($respuesta), 200);//enviamos la respuesta y el estado OK
                } else
                    $this->sent_response(json_encode($this->get_error(19)), 400);//Error en la sentencia de creacion de propuesta. Bad Request
            } else {
                $this->sent_response(json_encode($this->get_error(10)), 400);// Faltan datos. Bad Request
            }
        } elseif ($_SERVER['REQUEST_METHOD'] == "PUT" ){//Para actualizar todos los atributos de una acuerdo
            $id_acu = (int) $id_acuerdos;
            if ($id_acu <= 0){
                $this->sent_response(json_encode($this->get_error(10)), 400); // Faltan datos. Bad Request
            }

            if (isset($this->data_request['id_propuestas'], $this->data_request['id_usuarios2'], $this->data_request['id_estadosacuerdos1'],
                $this->data_request['id_estadosacuerdos2'], $this->data_request['n_horastotal'] )) {

                $id_propuestas = $this->data_request['id_propuestas'];
                $id_usuarios2 = $this->data_request['id_usuarios2'];
                $id_estadosacuerdos1 = $this->data_request['id_usuarios2'];
                $id_estadosacuerdos2 = $this->data_request['id_estadosacuerdos2'];
                $n_horastotal = $this->data_request['n_horastotal'];
                //  if (!existe_acuerdos($id_acu, $id_propuestas,$id_usuarios2,$id_estadosacuerdos1, $id_estadosacuerdos2, $n_horastotal  )){
                $query = $this->_conn->prepare("Update acuerdos set id_propuestas = :id_propuestas,  id_usuarios2 = :id_usuarios2, id_estadosacuerdos1= :id_estadosacuerdos1, id_estadosacuerdos2=:id_estadosacuerdos2, n_horastotal = :n_horastotal where id_acuerdos = :id_acuerdos");
                $query->bindValue(":id_acuerdos", $id_acu);
                $query->bindValue(":id_propuestas", $id_propuestas);
                $query->bindValue(":id_usuarios2", $id_usuarios2);
                $query->bindValue(":id_estadosacuerdos1", $id_estadosacuerdos1);
                $query->bindValue(":id_estadosacuerdos2", $id_estadosacuerdos2);
                $query->bindValue(":n_horastotal", $n_horastotal);

                $query->execute();

                if ($query->rowCount() == 1) {
                    $respuesta['estado'] = 'correcto';
                    $respuesta['msg'] = 'acuerdo actualizado correctamente';
                    $respuesta['acuerdo']['id'] = $id_acu;
                    $respuesta['acuerdo']['id_propuestas'] = $id_propuestas;
                    $respuesta['acuerdo']['id_propuestas'] = $id_propuestas;
                    $respuesta['acuerdo']['id_usuarios2'] = $id_usuarios2;
                    $respuesta['acuerdo']['id_estadosacuerdos1'] = $id_estadosacuerdos1;
                    $respuesta['acuerdo']['id_estadosacuerdos2'] = $id_estadosacuerdos2;
                    $respuesta['acuerdo']['n_horastotal'] = $n_horastotal;

                    $this->sent_response(json_encode($respuesta), 200);
                } else
                    $this->sent_response(json_encode($this->get_error(20)), 400); //Error en la sentencia de actualizacion de acuerdos o todos los atributos son iguales a los que ya tenia el registro. Bad Request
                /* } else
                     $this->sent_response(json_encode($this->get_error(21)), 400);//No se ha actualizado porque son los mismos atributos. Bad Request*/
            } else {
                $this->sent_response(json_encode($this->get_error(10)), 400);//faltan datos. Bad Request
            }

        }elseif ($_SERVER['REQUEST_METHOD'] == "PATCH") {

            $id_acu = (int)$id_acuerdos;
            $select = " Update acuerdos set";

            foreach ($this->data_request as $key => $value) { //montamos la query dinamicamente con los parametros que nos envian
                if ($key !== "request") {
                    $select = $select . " , " . $key . "='" . $value . "'";
                }
            }
            $contador = 0;
            $select = str_replace("acuerdos set ,", "acuerdos set", $select, $contador);
            $select = $select . " where id_acuerdos = " . $id_acu;

            if ($contador > 0) {
                $query = $this->_conn->prepare($select);
                $query->execute();
                $num = $query->rowCount();
                if ($num > 0) {
                    $respuesta['estado'] = 'correcto';
                    $respuesta['msg'] = 'acuerdos actualizada correctamente';
                    $respuesta['acuerdos']['id'] = $id_acu;

                    $this->sent_response(json_encode($respuesta), 200);
                } else {
                    $this->sent_response(json_encode($this->get_error(20)), 400);//Error en la sentencia de actualizacion de acuerdos o todos los atributos son iguales a los que ya tenia el registro. Bad Request
                }
            }
            $this->sent_response(json_encode($this->get_error(10)), 400);//faltan datos. Bad Request
        }
        $this->sent_response(json_encode($this->get_error(0)), 404);  //petición no encontrada . Bad Request
    }

   /* protected function filtropropio($tabla,$filtro,$artributoorderby, $formaOrder )
    {
        if ($_SERVER['REQUEST_METHOD'] == "GET") {

            $filtro = explode('=', trim($filtro));
            $atributo = $filtro[0];
            $valor = $filtro[1];

            if(isset($atributo, $valor, $artributoorderby, $tabla, $formaOrder )) {
                $select = "SELECT DISTINCT acu.* FROM acuerdos acu, propuestas pro WHERE acu.id_propuestas = pro.id_propuestas AND (acu.id_usuarios2 = " . $valor . " or pro.id_usuarios=" . $valor .")  Order by " . $artributoorderby . " " . $formaOrder;
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
    }*/

}
$acuerdos = new Acuerdos();
$acuerdos->responderRequest();