<?php
/**
 * Created by PhpStorm.
 * User: susan
 * Date: 02/06/2017
 * Time: 14:00
 */
/* Esta clase contiene las funciones que usaran todos los recursos:
    leer el request y limpiar la entrada de caracteres no validos.
    obtener conexion a BD
    codigos de errores
    adjuntar cabeceras
    enviar respuesta
    responder peticion
    filtros para selects get

*/

class Resource {
    public $content_type = "application/json"; //indica el tipo de respuesta que se va a dar, si no se pone en las pruebas en vez de verse el Json se veria con un parsed normal
    public $data_request = array(); //almacena los datos de la peticion despues de ser limpiada
    private $_codEstadoHTTP = 200; //Privada _ almacena el estado HTTP de la peticion, que por defecto sera 200 como en HTTP
    protected $_conn = NULL; // variable para guardar la conexion a BD
    const servidor = "localhost";
    const usuBD = "root";
    const passwdBD = "";
    const BD = "bancotiempo";
    protected $_parametros; //argumentos que se pasan en la url para el metodo

    public function __construct() {
        //función constructora, llama a read_request donde se verá el verbo(metodo) y se chekeara la entrada de parametros para limpiarla de caracteres no validos
        $this->read_request();
    }

    protected function conexionDB() {
        //se encarga de la conexion a BD
        $dsnBD = 'mysql:dbname=' . self::BD . ';host=' . self::servidor;
        try {
            $this->_conn = new PDO($dsnBD, self::usuBD, self::passwdBD);
        } catch (PDOException $e) {
            echo 'No hay conexión: ' . $e->getMessage();
        }
    }

    public function sent_response($jsonData, $codigoEstadoHTTP = 200) {
        /* es la funcion que se encarga de enviar la respuesta, para las cabeceras de estas usa set_header. Recibe el Json que se va a enviar
        y el codigo de estado HTTP, en caso de que no haya estado por defecto será 200*/
        $this->set_header();
        echo $jsonData;
        exit;
    }

    private function set_header() {
        /*En esta funcion añadimos las cabeceras a las respuestas, en ellas metemos el estado y su descripcion,
         indicamos que es del tipo json y el protocolo de HTTP.*/
        header("HTTP/1.1 " . $this->_codEstadoHTTP . " " . $this->get_descripcionEstadoHTTP());
        header("Content-Type:" . $this->content_type . ';charset=utf-8');
    }

    private function read_request() {
        /* Esta función es llamada desde el construct. En la funcion se mira que verbo(metodo) que se ha usado en el request, y segun que cas
        recoge los parametros de una manera u otra. Los argumentos de los verbos PATCH Y PUT se leen de la
        misma forma, se recogen como si fuera un fichero proque no hay una variable de php que los contenga explicitamente*/
        $verbo_method = $_SERVER['REQUEST_METHOD'];
        switch ($verbo_method) {
            case "POST":
                $this->data_request = $this->clear_request($_POST);
                break;
            case "GET":
                $this->data_request = $this->clear_request($_GET);
                break;
            case "DELETE": //se borrara mediante el id, asi que no hacen falta parametros
                break;
            case "PATCH"://se dejan vacios porque los trataremos como en el case PUT
            case "PUT":
                parse_str(file_get_contents("php://input"), $this->data_request); //pasamos la entrada a string el flujo de Entrada y metemos en data_request cada par de elementos de input variable = valor como un array
                $this->data_request = $this->clear_request($this->data_request); //llamamos a nuestra funcion clear_request para dejar los parametros
                break;
            default: //en caso de ser otro verbo se devolvera un error porque nuestra API no lo recoge
                $this->sent_response(json_encode($this->get_error(0)), 404);
                break;
        }
    }

    private function clear_request($data_requestini) {
        /* Esta funcion es de tipo recursiva, se encarga de comprobar/arreglar los argumentos que hayan enviado.*/
        $data_requestfin = array();
        if (is_array($data_requestini)) {
            foreach ($data_requestini as $key => $value) {
                $data_requestfin[$key] = $this->clear_request($value);
            }
        } else {
            //eliminamos etiquetas html y php
            $data_requestini = strip_tags($data_requestini);
            //Conviertimos todos los caracteres aplicables a entidades HTML (comillas simples por dobles, etc.)
            $data_requestini = htmlentities($data_requestini);
            $data_requestfin = trim($data_requestini);


        }
        return $data_requestfin;
    }



    protected function get_error($id_error) {
        //array de arrays, con posibles mensajes de errores que se peuden dar en la API
        $array_errores = array(
            /*0*/    array('estado' => "error", "msg" => "No se ha encontrado el tipo de peticion"),
            /*1*/    array('estado' => "error", "msg" => "No se acepta esa peticion"),
            /*2*/    array('estado' => "error", "msg" => "La peticion no tiene contenido"),
            /*3*/    array('estado' => "error", "msg" => "Credenciales incorrectas"),
            /*4*/    array('estado' => "error", "msg" => "Error al borrar el usuario"),
            /*5*/    array('estado' => "error", "msg" => "Error al recoger parámetros"),
            /*6*/    array('estado' => "error", "msg" => "Error de existencia de usuarios"),
            /*7*/    array('estado' => "error", "msg" => "Error en la sentencia de creacion de usuarios"),
            /*8*/    array('estado' => "error", "msg" => "Error en la sentencia de actualizacion de usuarios o todos los atributos son iguales a los que ya tenia el registro"),
            /*9*/    array('estado' => "error", "msg" => "El usuario existe pero no está validado"),
            /*10*/   array('estado' => "error", "msg" => "Faltan datos"),
            /*11*/   array('estado' => "error", "msg" => "Error en la sentencia de creacion de perfil"),
            /*12*/   array('estado' => "error", "msg" => "ya existe un perfil con esa descripcion"),
            /*13*/   array('estado' => "error", "msg" => "falta el perfil a actualizar"),
            /*14*/   array('estado' => "error", "msg" => "Error en la sentencia de actualizazion de perfil"),
            /*15*/   array('estado' => "error", "msg" => "Error actualizando usuario"),
            /*16*/   array('estado' => "error", "msg" => "Es la misma descripcion que ya tenia"),
            /*17*/   array('estado' => "error", "msg" => "error borrando perfiles"),
            /*18*/   array('estado' => "error", "msg" => "error borrando acuerdo"),
            /*19*/   array('estado' => "error", "msg" => "Error en la sentencia de creacion de acuerdos"),
            /*20*/   array('estado' => "error", "msg" => "Error en la sentencia de actualizacion de acuerdos o todos los atributos son iguales a los que ya tenia el registro"),
            /*21*/   array('estado' => "error", "msg" => "No se ha actualizado porque son los mismos atributos"),
            /*22*/   array('estado' => "error", "msg" => "Ya existe otro registro con los mismos atributos"),
            /*23*/   array('estado' => "error", "msg" => "Error en la sentencia de creacion de ambito de propuestas"),
            /*24*/   array('estado' => "error", "msg" => "ya existe un ambito de propuesta con esa descripcion"),
            /*25*/    array('estado' => "error", "msg" => "error borrando el ambito de propuesta"),
            /*26*/   array('estado' => "error", "msg" => "Error en la sentencia de creacion de comentarios"),
            /*27*/   array('estado' => "error", "msg" => "Error en la sentencia de actualizacion de comentarios"),
            /*28*/   array('estado' => "error", "msg" => "Error en la sentencia de creacion de comentarios"),
            /*29*/   array('estado' => "error", "msg" => "ya existe un estado de acuerdo con esa descripcion"),
            /*30*/    array('estado' => "error", "msg" => "error borrando  estado de acuerdo"),
            /*31*/   array('estado' => "error", "msg" => "Error en la sentencia de creacion de eventos"),
            /*32*/   array('estado' => "error", "msg" => "ya existe un evento con esa descripcion"),
            /*33*/    array('estado' => "error", "msg" => "error borrando evento"),
            /*34*/    array('estado' => "error", "msg" => "error borrando mensajes a propuestas"),
            /*35*/   array('estado' => "error", "msg" => "Error en la sentencia de creacion de mensajes a propuestas"),
            /*36*/   array('estado' => "error", "msg" => "Error en la sentencia de actualizacion de mensajes a propuestas"),
            /*37*/    array('estado' => "error", "msg" => "error borrando propuesta"),
            /*38*/   array('estado' => "error", "msg" => "Error en la sentencia de creacion de propuestas"),
            /*39*/   array('estado' => "error", "msg" => "Error en la sentencia de actualizacion de propuestas"),
            /*40*/   array('estado' => "error", "msg" => "Error en la sentencia de creacion de tipos de propuesta"),
            /*41*/   array('estado' => "error", "msg" => "Error en la sentencia de actualizacion de tipos de propuesta"),
            /*42*/   array('estado' => "error", "msg" => "ya existe un tipo de propuesta con esa descripcion"),
            /*43*/    array('estado' => "error", "msg" => "error borrando tipos de propuesta"),
        );
        return $array_errores[$id_error];
    }

    private function get_descripcionEstadoHTTP() {
        //funcion que contiene las descricpciones de los estados HTTP que usamos
        $estadoHTTP = array(
            200 => 'OK', // Respuesta estandar a peticiones correctas
            201 => 'Created',//la peticion ha sido aceptada y se ha creado un nuevo recurso
            202 => 'Accepted', //La peticion ha sido aceptada pero no se ha completado
            203=> ' Non-Authoritative Information', //operacion obtenida con exito pero de otra fuente-servidor
            204 => 'No Content',//La peticion ha sido procesada pero no ha devuelto contenido
            400 => 'Bad Request', //La solicitud tiene sintaxis erronea
            401 => 'Unauthorized', //Es como la 403 pero cuando no ha habido autenticación o esta ha fallado
            403 => 'Forbidden', //El cliente no tiene privilegios para hacer la petición
            404 => 'Not Found', //Recurso no encontrado
            405 => 'Method Not Allowed', //La URI no soporta el método de solicitud elegido
            406 => 'Not Acceptable', // el servidor no puede devolver la respuesta en un tipo soportado por el cliente.
            500 => 'Internal Server Error');
        $response = ($estadoHTTP[$this->_codEstadoHTTP]) ? $estadoHTTP[$this->_codEstadoHTTP] : $estadoHTTP[500];
        return $response;
    }

    public function responderRequest()
    {
        if (isset($_REQUEST['request'])) {
            $request = explode('/', trim($_REQUEST['request']));//devuelve el request particionado por /
            $request = array_filter($request); //elimina valores false, cadenas vacias y nulls del array y devuelve el array filtrado
            $resource = strtolower(array_shift($request));
            $this->_parametros = $request; //despues del recurso lo que vienen son los parametros

            if ((int)method_exists($this, $resource) > 0) { //comprueba si ese recurso existe, aunque como ha pasado el .htacces deberia existir
                if (count($this->_parametros) > 0) {
                    call_user_func_array(array($this, $resource), $this->_parametros); //llama al recurso pasandole los argumentos
                } else {//si no lo llamamos sin argumentos, al metodo del controlador
                    call_user_func(array($this, $resource));
                }
            } else //si el metodo no existe se devuelve el error 0 y estado 404
                $this->sent_response(json_encode($this->get_error(0)), 404);
        }//si no se ha pasado ningun metodo en la llamada se devuelve el error 0 y estado 404
        $this->sent_response(json_encode($this->get_error(0)), 404);
    }

    protected function filtro($tabla,$filtro,$artributoorderby, $formaOrder )
    {
        if ($_SERVER['REQUEST_METHOD'] == "GET") {

            $filtro = explode('=', trim($filtro));
            $atributo = $filtro[0];
            $valor = $filtro[1];

            if(isset($atributo, $valor, $artributoorderby, $tabla, $formaOrder )) {
                $select = "SELECT * FROM ". $tabla. " WHERE " . $atributo . "=" . $valor . " Order by " . $artributoorderby . " " . $formaOrder ;
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
?>