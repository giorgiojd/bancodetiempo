<?php

/**
 * Created by PhpStorm.
 * User: susan
 * Date: 01/06/2017
 * Time: 19:55
 */
require_once("Resource.php");
class Usuarios extends Resource
{
    private $_recurso; //metodo de la clase que es llamado mediante la peticion


    public function __construct()
    {
        /*el constructor inicializa el de REST para las peticiones y abre la conexion a base de datos*/
        parent::__construct();
        $this->conexionDB();
    }

    private function existe_usuario($email, $id = -1, $usuario, $dni ) {//sera -1 cuando no tenga id, estemos usandola en la creacion de usuarios
        if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
            if($id<=0){
                $query = $this->_conn->prepare("SELECT email from usuarios WHERE email = :email");
                $query->bindValue(":email", $email);
            }else{
                $id_usuario = (int) $id;
                $query = $this->_conn->prepare("SELECT email from usuarios WHERE (email = :email or nombre = :usuario or dni=:dni) and id_usuarios <> :id_usuario");
                $query->bindValue(":email", $email);
                $query->bindValue(":usuario", $usuario);
                $query->bindValue(":dni", $dni);
                $query->bindValue(":id_usuario", $id_usuario);
            }
            $query->execute();
            if ($query->fetch(PDO::FETCH_ASSOC)) {
                return true;
            }
        }
        else
            return false;
    }


    protected function usuarios($id_usuarios = -1)
    {
        if ($_SERVER['REQUEST_METHOD'] == "GET") {
            $id = (int)$id_usuarios;
            if ($id <= 0) { //caso en el que recuperamos todos los usuarios
                $query = $this->_conn->query("SELECT id_usuarios, usuario, pwd, nombre, apellidos, email, telefono, validado, dni FROM usuarios Order by id_usuarios ASC");
            } else { //caso en el que recuperamos 1 usuario
                $query = $this->_conn->prepare("SELECT id_usuarios, usuario, pwd, nombre, apellidos, email, telefono, validado, dni FROM usuarios where id_usuarios = :id_usuarios Order by id_usuarios ASC");
                $query->bindValue(":id_usuarios", $id);
                $query->execute();
            }
            $filas = $query->fetchAll(PDO::FETCH_ASSOC);
            $num = count($filas);
            if ($num > 0) {
                $respuesta['estado'] = 'correcto';
                $respuesta['usuarios'] = $filas;
                $this->sent_response(json_encode($respuesta), 200);
            }
            $this->sent_response($this->get_error(2), 204);
        } elseif ($_SERVER['REQUEST_METHOD'] == "DELETE"){ //OJO!!! NO BORRA, solo invalida el usuario, se mantiene historico
            $id_usu = (int) $id_usuarios;
            if ($id_usu >= 0) {
                $query = $this->_conn->prepare("update usuarios set validado = 0 WHERE id_usuarios =:id_usuarios");
                $query->bindValue(":id_usuarios", $id_usu);
                $query->execute();
                $n_deletes = $query->rowCount();
                if ($n_deletes == 1) {
                    $resp = array('estado' => "correcto", "msg" => "usuario invalidado correctamente.");
                    $this->sent_response(json_encode($resp), 200);
                } else {
                    $this->sent_response(json_encode($this->get_error(4)), 400);

                }
            }
            $this->sent_response(json_encode($this->get_error(3)), 400);

        }elseif ($_SERVER['REQUEST_METHOD'] == "POST") {
            if (isset( $this->data_request['usuario'], $this->data_request['pwd'], $this->data_request['nombre'], $this->data_request['apellidos'],
                $this->data_request['email'], $this->data_request['validado'], $this->data_request['id_perfiles'], $this->data_request['dni'])) {

                $usuario = $this->data_request['usuario'];
                $pwd = $this->data_request['pwd'];
                $nombre = $this->data_request['nombre'];
                $apellidos = $this->data_request['apellidos'];
                $email = $this->data_request['email'];
                $validado = $this->data_request['validado'];
                $id_perfiles = $this->data_request['id_perfiles'];
                $dni = $this->data_request['dni'];
                $telefono = $this->data_request['telefono']? $this->data_request['telefono']:null;

                if (!$this->existe_Usuario($email,-1, $nombre, $dni )) {
                    $query = $this->_conn->prepare("Insert into usuarios (usuario,  pwd ,  nombre,  apellidos, email, telefono, validado,  id_perfiles, dni) VALUES (:usuario,:pwd,:nombre,:apellidos,:email,:telefono,:validado,:id_perfiles,:dni)");
                    $query->bindValue(":usuario", $usuario);
                    $query->bindValue(":pwd", $pwd);
                    $query->bindValue(":nombre", $nombre);
                    $query->bindValue(":apellidos", $apellidos);
                    $query->bindValue(":email", $email);
                    $query->bindValue(":telefono", $telefono);
                    $query->bindValue(":validado", $validado);
                    $query->bindValue(":id_perfiles", $id_perfiles);
                    $query->bindValue(":dni", $dni);
                    $query->execute();
                    if ($query->rowCount() == 1) {
                        $id = $this->_conn->lastInsertId();
                        $respuesta['estado'] = 'correcto';
                        $respuesta['msg'] = 'usuario creado correctamente';
                        $respuesta['usuario']['id_perfil'] = $id;
                        $respuesta['usuario']['usuario'] = $usuario;
                        $respuesta['usuario']['pwd'] = $pwd;
                        $respuesta['usuario']['nombre'] = $nombre;
                        $respuesta['usuario']['apellidos'] = $apellidos;
                        $respuesta['usuario']['email'] = $email;
                        $respuesta['usuario']['telefono'] = $telefono;
                        $respuesta['usuario']['validado'] = $validado;
                        $respuesta['usuario']['id_perfiles'] = $id_perfiles;
                        $respuesta['usuario']['dni'] = $dni;

                        $this->sent_response(json_encode($respuesta), 200);//enviamos la respuesta y el estado OK
                    } else
                        $this->sent_response(json_encode($this->get_error(11)), 400);//Error en la sentencia de creacion de usuario. Bad Request
                } else
                    $this->sent_response(json_encode($this->get_error(12)), 400);// ya existe un usuario con esa descripcion.  Bad Request
            } else {
                $this->sent_response(json_encode($this->get_error(10)), 400);// Faltan datos. Bad Request
            }
        } elseif ($_SERVER['REQUEST_METHOD'] == "PUT" ){//Para actualizar todos los registros de 1 usuario menos su id
            $id_usu = (int) $id_usuarios;
            if ($id_usu <= 0){
                $this->sent_response(json_encode($this->get_error(10)), 400);
            }
            if (isset($this->data_request['nombre'], $this->data_request['apellidos'], $this->data_request['email'],
                $this->data_request['pwd'], $this->data_request['usuario'],  $this->data_request['id_perfiles'],
                $this->data_request['validado'])) {
                $nombre = $this->data_request['nombre'];
                $pwd = $this->data_request['pwd'];
                $email = $this->data_request['email'];
                $usuario = $this->data_request['usuario'];
                $apellidos = $this->data_request['apellidos'];
                $telefono = $this->data_request['telefono']? $this->data_request['telefono']:null;
                $id_perfiles = $this->data_request['id_perfiles'];
                $validado = $this->data_request['validado'];
                $dni = $this->data_request['dni'];

                if (!$this->existe_Usuario($email,$id_usu,$nombre, $dni )) {//comprobamos los campos que son unicos en la tabla
                    $query = $this->_conn->prepare("Update usuarios set usuario = :usuario,  pwd = :pwd, nombre= :nombre, apellidos=:apellidos, email = :email, telefono=:telefono, validado= :validado, id_perfiles=:id_perfiles, dni=:dni where id_usuarios = :id_usuarios");
                    $query->bindValue(":id_usuarios", $id_usu);
                    $query->bindValue(":usuario", $usuario);
                    $query->bindValue(":pwd", sha1($pwd));
                    $query->bindValue(":nombre", $nombre);
                    $query->bindValue(":apellidos", $apellidos);
                    $query->bindValue(":email", $email);
                    $query->bindValue(":telefono", $telefono);
                    $query->bindValue(":validado", $validado);
                    $query->bindValue(":id_perfiles", $id_perfiles);
                    $query->bindValue(":dni", $dni);
                    $query->execute();

                    if ($query->rowCount() == 1) {
                        // $id = $this->_conn->lastUpdateId();
                        $respuesta['estado'] = 'correcto';
                        $respuesta['msg'] = 'usuario actualizado correctamente';
                        $respuesta['usuario']['id'] = $id_usu;
                        $respuesta['usuario']['usuario'] = $usuario;
                        $respuesta['usuario']['pwd'] = $pwd;
                        $respuesta['usuario']['nombre'] = $nombre;
                        $respuesta['usuario']['apellidos'] = $apellidos;
                        $respuesta['usuario']['email'] = $email;
                        $respuesta['usuario']['telefono'] = $telefono;
                        $respuesta['usuario']['validado'] = $validado;
                        $respuesta['usuario']['id_perfiles'] = $id_perfiles;
                        $respuesta['usuario']['dni'] = $dni;

                        $this->sent_response(json_encode($respuesta), 200);
                    }
                    else
                        $this->sent_response(json_encode($this->get_error(7)), 400);
                }
                else
                    $this->sent_response(json_encode($this->get_error(8)), 400);
            } else {
                $this->sent_response(json_encode($this->get_error(10)), 400);
            }

        }elseif ($_SERVER['REQUEST_METHOD'] == "PATCH") {

            $id = (int)$id_usuarios;
            $select = " Update usuarios set";

            foreach ($this->data_request as $key => $value) { //montamos la query dinamicamente con los parametros que nos envian
                if ($key !== "request") {
                    $select = $select . " , " . $key . "='" . $value . "'";
                }
            }
            $contador = 0;
            $select = str_replace("usuarios set ,", "usuarios set", $select, $contador);
            $select = $select . " where id_usuarios = " . $id;

            if ($contador > 0) {
                $query = $this->_conn->prepare($select);
                $query->execute();
                $num = $query->rowCount();
                if ($num > 0) {
                    $respuesta['estado'] = 'correcto';
                    $respuesta['msg'] = 'usuario actualizado correctamente';
                    $respuesta['usuario']['id'] = $id;

                    $this->sent_response(json_encode($respuesta), 200);
                } else {
                    $this->sent_response(json_encode($this->get_error(8)), 404);
                }
            }
            $this->sent_response(json_encode($this->get_error(10)), 400);//Faltan datos. Bad Request
        }
        $this->sent_response(json_encode($this->get_error(0)), 404);//Peticion no encontrada. Not Found
    }

}
$usuarios = new Usuarios();
$usuarios->responderRequest();
