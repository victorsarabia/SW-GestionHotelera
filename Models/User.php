<?php
require __DIR__ . "/../inc/BDConnectionSingleton.php";

/**
 * Class User
 * Clase Modelo para realizar las operaciones necesarias para interacturar con la BD
 * Nunca debe hacer "echo" ni ninguna operación que muestre información. Toda la información
 * será devuelta por el "return" y tratada/utilizada desde el servicio web
 *
 * @since 11/01/2023
 * @author Víctor Sarabia
 * @version 1.0.0
 */
class User
{
    // Atributos
    private $email;
    private $password;
    private $token;
    private $fecha_validez_token;
    private $nombre;
    private $apellidos;
    private $telefono;
    private $fecha_baja;
    private $created_at;
    private $updated_at;

    const VALEZ_TOKEN=30;

    /**
     * User constructor.
     * @param $email
     * @param $password
     * @param $nombre;
     * @param $apellidos;
     * @param $telefono;
     * @param $fecha_baja;
     * @param $created_at;
     * @param $updated_at;
     */
    public function __construct($email=null, $password=null, $token=null, $fecha_validez_token=null, $nombre = null, $apellidos = null, $telefono = null, $fecha_baja = null)
    {
        $this->email = $email;
        $this->password = $password;
        $this->token = $token;
        $this->fecha_validez_token = $fecha_validez_token;
        $this->nombre = $nombre;
        $this->apellidos = $apellidos;
        $this->telefono = $telefono;
        $this->fecha_baja = $fecha_baja;
    }

    /**
     * @return mixed|null
     */
    public function getToken()
    {
        return $this->token;
    }

    public function toArray() {
        return [
            "email"=>$this->email,
            "password"=>null,
            "token"=>$this->token,
            "fecha_validez_token"=>$this->fecha_validez_token,
            "nombre"=>$this->nombre,
            "apellidos"=>$this->apellidos,
            "telefono"=>$this->telefono,
            "fecha_baja"=>$this->fecha_baja,
            "created_at"=>$this->created_at,
            "updated_at"=>$this->updated_at
        ];
    }

    /**
     * Realiza búsquedas de usuarios en BD por los filtros indicados.
     *
     * @param array $filters Array Asociativo con los filtros necesxarios
     * @param int Página solicitada
     * @param int Número de registros a devolver por página
     * @return array Array asociativo con el listado de médicos
     * @throws Exception
     */
    public static function find($filters=[], $pagina=1, $num_registros=10){
        //print_r($filters);exit();
        try {
            $pdo = BDConnectionSingleton::getInstance();
            $total_registros=0;

            $sql = 'select * FROM users where true';
            $sql_count = 'select count(*) as total FROM users where true';
            $sql_where = "";

            $params = [];
            if(isset($filters["email"])) {
                $sql_where.=" and email like :email";
                $params[":email"]="%".$filters["email"]."%";
            }
            if(isset($filters["token"])) {
                $sql_where.=" and token = :token";
                $params[":token"]=$filters["token"];
            }
            if(isset($filters["nombre"])) {
                $sql_where.=" and nombre like :nombre";
                $params[":nombre"]="%".$filters["nombre"]."%";
            }
            if(isset($filters["apellidos"])) {
                $sql_where.=" and apellidos like :apellidos";
                $params[":apellidos"]="%".$filters["apellidos"]."%";
            }
            if(isset($filters["telefono"])) {
                $sql_where.=" and telefono like :telefono";
                $params[":telefono"]="%".$filters["telefono"]."%";
            }
            if(isset($filters["fecha_baja"])) {
                $sql_where.=" and fecha_baja = :fecha_baja";
                $params[":fecha_baja"]=$filters["fecha_baja"];
            }
            if(isset($filters["alta"])) {
                $sql_where.=" and fecha_baja is null";
            }
            if(isset($filters["baja"])) {
                $sql_where.=" and fecha_baja is not null";
            }


            // Para obtener el total de registros
            $stmt = $pdo->prepare($sql_count.$sql_where);
            $stmt->execute($params);
            $users_count = $stmt->fetch(PDO::FETCH_ASSOC);
            $total_registros = $users_count["total"];

            // Para obtener los registros
            $sql.= $sql_where;
            $sql.= sprintf(" limit %d, %d", (($pagina-1)*$num_registros), $num_registros);

            $stmt = $pdo->prepare($sql);
            $stmt->execute($params);

            /*while ($row = $stmt->fetch(PDO::FETCH_ASSOC)){
                echo $row["dni"]."<br>";
            }*/
            $users = $stmt->fetchALL(PDO::FETCH_ASSOC);
            $row=null;
            $stmt=null;
            $pdo = null;

            return ["users"=>$users, "total_registros"=>$total_registros, "pagina"=>$pagina, "registros_por_pagina"=>$num_registros];
        }
        catch(PDOException $e) {
            // Relanza la excepción para ser tratada en el SW
            throw new Exception($e);
        }
    }


    /**
     * Inserta, el objeto Usuario instanciado, en BD
     *
     * @return mixed
     * @throws Exception
     */
    public function insert() {
        try {
            $pdo = BDConnectionSingleton::getInstance();

            // Email Obligatorio en BD
            if (empty($this->email))
                throw new Exception("El email es obligatorio.");
            // Password Obligatorio en BD
            if (empty($this->password))
                throw new Exception("El password es obligatorio.");

            $sql = "insert into users values (:email, :password, null, null, :nombre, :apellidos, :telefono, :fecha_baja, now(), now())";
            $stmt = $pdo->prepare($sql);
            $values = [
                ":email"=>$this->email,
                ":password"=>$this->password,
                ":nombre"=>$this->nombre,
                ":apellidos"=>$this->apellidos,
                ":telefono"=>$this->telefono,
                ":fecha_baja"=>$this->fecha_baja
                ];
            return $stmt->execute($values);

        } catch (Exception $e) {
            throw $e;
        }
    }

    /**
     * Modifica, el objeto Usuario instanciado, en BD
     *
     * @return mixed
     * @throws Exception
     */
    public function update() {
        try {
            $pdo = BDConnectionSingleton::getInstance();

            // Email Obligatorio en BD
            if (empty($this->email))
                throw new Exception("El email es obligatorio.");
            // El password sólo se modifica con un método específico

            $sql = "update users set 
                 email=:email, 
                 token=:token, 
                 fecha_validez_token=:fecha_validez_token, 
                 nombre=:nombre, 
                 apellidos=:apellidos, 
                 telefono=:telefono, 
                 fecha_baja=:fecha_baja, 
                 updated_at=now() 
                where email=:pk";
            $stmt = $pdo->prepare($sql);
            $values = [
                ":email"=>$this->email,
                ":token"=>$this->token,
                ":fecha_validez_token"=>$this->fecha_validez_token,
                ":nombre"=>$this->nombre,
                ":apellidos"=>$this->apellidos,
                ":telefono"=>$this->telefono,
                ":fecha_baja"=>$this->fecha_baja,
                ":pk"=>$this->email,
            ];
            return $stmt->execute($values);

        } catch (Exception $e) {
            throw $e;
        }
    }

    /**
     * Borra, el objeto Usuario instanciado, en BD
     *
     * @return mixed
     * @throws Exception
     */
    public function delete() {
        try {
            $pdo = BDConnectionSingleton::getInstance();

            // Email Obligatorio en BD
            if (empty($this->email))
                throw new Exception("El email es obligatorio.");

            $sql = "delete from users where email=:email";
            $stmt = $pdo->prepare($sql);
            $values = [
                ":email"=>$this->email
            ];
            $result = $stmt->execute($values);
            return $result;

        } catch (Exception $e) {
            throw $e;
        }
    }

    /**
     * Realiza login y devuelve el objeto usuario con el token
     *
     * @return User Usuario logeado
     * @throws Exception
     */
    public function login() {
        try {
            $pdo = BDConnectionSingleton::getInstance();

            $sql = 'select * FROM users where email=:email and password=:password and fecha_baja is null';
            $stmt = $pdo->prepare($sql);

            $params[":email"]=$this->email;
            $params[":password"]=$this->password;
            $stmt->execute($params);

            /*while ($row = $stmt->fetch(PDO::FETCH_ASSOC)){
                echo $row["dni"]."<br>";
            }*/
            $arrUser = $stmt->fetch(PDO::FETCH_ASSOC);
            $user = null;
            if ($arrUser) {
                $user = new User($arrUser["email"], null, null, null, $arrUser["nombre"], $arrUser["apellidos"], $arrUser["telefono"], null);
                $opciones = [
                    'cost' => 11,
                    'salt' => md5(time())
                ];
                // Genera un token aleatorio
                $user->token = password_hash(Date("Y-m-d h:i:s"), PASSWORD_BCRYPT, $opciones);
                $fecha_actual = date("Y-m-d h:i:s");
                $user->fecha_validez_token = date("Y-m-d h:i:s",strtotime($fecha_actual."+ ". self::VALEZ_TOKEN ." days"));

                if (!$user->update())
                    $user = null;
            }
            $row=null;
            $stmt=null;
            $pdo = null;

            return $user;
        } catch (Exception $e) {
            throw $e;
        }
    }

    /**
     * Finaliza la sesión eliminando el token
     *
     * @return bool
     * @throws Exception
     */
    public function logout() {
        try {
            $pdo = BDConnectionSingleton::getInstance();

            //TODO Se podría hacer una búsqueda previa para verificar que el mail existe y no devuelva true en caso contrario

            $sql = 'update users set token=null where email=:email';
            $stmt = $pdo->prepare($sql);

            $params[":email"]=$this->email;
            $result = $stmt->execute($params);

            $row=null;
            $stmt=null;
            $pdo = null;

            return $result;
        } catch (Exception $e) {
            throw $e;
        }
    }

    /**
     * Devuelve el objeto usuario si el token es válido
     *
     * @param $token
     * @return User
     * @throws Exception
     */
    public static function getUserLogued($token) {
        try {
            $pdo = BDConnectionSingleton::getInstance();

            $sql = 'select * FROM users where token=:token and fecha_baja is null and fecha_validez_token>=now()';
            $stmt = $pdo->prepare($sql);

            $params[":token"]=$token;
            $stmt->execute($params);

            $arrUser = $stmt->fetch(PDO::FETCH_ASSOC);
            $user = new User();
            if ($arrUser) {
                $user = new User($arrUser["email"], null, null, null, $arrUser["nombre"], $arrUser["apellidos"], $arrUser["telefono"], null);
            }
            $row=null;
            $stmt=null;
            $pdo = null;

            return $user;
        } catch (Exception $e) {
            throw $e;
        }
    }

}
