<?php
require_once __DIR__ . "/../inc/BDConnectionSingleton.php";

/**
 * Class Reserva
 * Clase Modelo para realizar las operaciones necesarias para interacturar con la BD
 * Nunca debe hacer "echo" ni ninguna operación que muestre información. Toda la información
 * será devuelta por el "return" y tratada/utilizada desde el servicio web
 *
 * @since 11/01/2023
 * @author Víctor Sarabia
 * @version 1.0.0
 */
class Reserva
{
    // Atributos
    private $id;
    private $fecha;
    private $fecha_entrada;
    private $fecha_salida;
    private $numero_adultos;
    private $numero_ninyos;
    private $user_id;
    private $fecha_baja;
    private $created_at;
    private $updated_at;

    /**
     * Reserva constructor.
     * @param $id
     * @param $fecha
     * @param $fecha_entrada
     * @param $fecha_salida
     * @param $numero_adultos
     * @param $numero_ninyos
     * @param $user_id
     * @param $fecha_baja
     */
    public function __construct($id=null, $fecha=null, $fecha_entrada=null, $fecha_salida=null, $numero_adultos=null, $numero_ninyos=null, $user_id=null, $fecha_baja=null)
    {
        $this->id = $id;
        $this->fecha = $fecha;
        $this->fecha_entrada = $fecha_entrada;
        $this->fecha_salida = $fecha_salida;
        $this->numero_adultos = $numero_adultos;
        $this->numero_ninyos = $numero_ninyos;
        $this->user_id = $user_id;
        $this->fecha_baja = $fecha_baja;
    }


    /**
     * Devuelve un array asociativo con los datos del objeto
     * @return array
     */
    public function toArray() {
        return [
            "id"=>$this->id,
            "fecha"=>$this->fecha,
            "fecha_entrada"=>$this->fecha_entrada,
            "fecha_salida"=>$this->fecha_salida,
            "numero_adultos"=>$this->numero_adultos,
            "numero_ninyos"=>$this->numero_ninyos,
            "user_id"=>$this->user_id,
            "fecha_baja"=>$this->fecha_baja,
            "created_at"=>$this->created_at,
            "updated_at"=>$this->updated_at
        ];
    }

    /**
     * Realiza búsquedas de reservas en BD por los filtros indicados.
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

            $sql = 'select * FROM reservas where true';
            $sql_count = 'select count(*) as total FROM reservas where true';
            $sql_where = "";

            $params = [];
            if(isset($filters["id"])) {
                $sql_where.=" and id = :id";
                $params[":id"]=$filters["id"];
            }
            if(isset($filters["fecha"])) {
                $sql_where.=" and fecha = :fecha";
                $params[":fecha"]=$filters["fecha"];
            }
            if(isset($filters["fecha_entrada"])) {
                $sql_where.=" and fecha_entrada = :fecha_entrada";
                $params[":fecha_entrada"]=$filters["fecha_entrada"];
            }
            if(isset($filters["fecha_salida"])) {
                $sql_where.=" and fecha_salida = :fecha_salida";
                $params[":fecha_salida"]=$filters["fecha_salida"];
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
            if(isset($filters["user_id"])) {
                $sql_where.=" and user_id = :user_id";
                $params[":user_id"]=$filters["user_id"];
            }

            // Para obtener el total de registros
            $stmt = $pdo->prepare($sql_count.$sql_where);
            $stmt->execute($params);
            $reserva_count = $stmt->fetch(PDO::FETCH_ASSOC);
            $total_registros = $reserva_count["total"];

            // Para obtener los registros
            $sql.= $sql_where;
            $sql.= sprintf(" limit %d, %d", (($pagina-1)*$num_registros), $num_registros);

            $stmt = $pdo->prepare($sql);
            $stmt->execute($params);

            $reserva = $stmt->fetchALL(PDO::FETCH_ASSOC);
            $row=null;
            $stmt=null;
            $pdo = null;

            return ["reservas"=>$reserva, "total_registros"=>$total_registros, "pagina"=>$pagina, "registros_por_pagina"=>$num_registros];
        }
        catch(PDOException $e) {
            // Relanza la excepción para ser tratada en el SW
            throw new Exception($e);
        }
    }


    /**
     * Inserta, el objeto Reserva instanciado, en BD
     *
     * @return mixed
     * @throws Exception
     */
    public function insert() {
        try {
            $pdo = BDConnectionSingleton::getInstance();

            // Email Obligatorio en BD
            if (empty($this->fecha))
                throw new Exception("La fecha es obligatoria.");
            if (empty($this->fecha_entrada))
                throw new Exception("La fecha de entrada es obligatoria.");
            if (empty($this->fecha_salida))
                throw new Exception("La fecha de salida es obligatoria.");
            if (empty($this->numero_adultos))
                throw new Exception("El numero de adultos es obligatorio.");
            if (empty($this->numero_ninyos))
                throw new Exception("El numero de niños es obligatorio.");
            if (empty($this->user_id))
                throw new Exception("El usurio que reserva es obligatorio.");

            $sql = "insert into reservas values (null, :fecha, :fecha_entrada, :fecha_salida, :numero_adultos, :numero_ninyos, :user_id, :fecha_baja, now(), now())";
            $stmt = $pdo->prepare($sql);
            $values = [
                ":fecha"=>$this->fecha,
                ":fecha_entrada"=>$this->fecha_entrada,
                ":fecha_salida"=>$this->fecha_salida,
                ":numero_adultos"=>$this->numero_adultos,
                ":numero_ninyos"=>$this->numero_ninyos,
                ":user_id"=>$this->user_id,
                ":fecha_baja"=>$this->fecha_baja
            ];
            return $stmt->execute($values);

        } catch (Exception $e) {
            throw $e;
        }
    }

    /**
     * Modifica, el objeto Reserva instanciado, en BD
     *
     * @return mixed
     * @throws Exception
     */
    public function update() {
        try {
            $pdo = BDConnectionSingleton::getInstance();

            // Email Obligatorio en BD
            if (empty($this->id))
                throw new Exception("El ID es obligatorio.");
            if (empty($this->fecha))
                throw new Exception("La fecha es obligatoria.");
            if (empty($this->fecha_entrada))
                throw new Exception("La fecha de entrada es obligatoria.");
            if (empty($this->fecha_salida))
                throw new Exception("La fecha de salida es obligatoria.");
            if (empty($this->numero_adultos))
                throw new Exception("El numero de adultos es obligatorio.");
            if (empty($this->numero_ninyos))
                throw new Exception("El numero de niños es obligatorio.");
            if (empty($this->user_id))
                throw new Exception("El usurio que reserva es obligatorio.");

            $sql = "update reservas set 
                 fecha=:fecha, 
                 fecha_entrada=:fecha_entrada, 
                 fecha_salida=:fecha_salida, 
                 numero_adultos=:numero_adultos, 
                 numero_ninyos=:numero_ninyos, 
                 user_id=:user_id, 
                 fecha_baja=:fecha_baja, 
                 updated_at=now() 
                where id=:id";
            $stmt = $pdo->prepare($sql);
            $values = [
                ":id"=>$this->id,
                ":fecha"=>$this->fecha,
                ":fecha_entrada"=>$this->fecha_entrada,
                ":fecha_salida"=>$this->fecha_salida,
                ":numero_adultos"=>$this->numero_adultos,
                ":numero_ninyos"=>$this->numero_ninyos,
                ":user_id"=>$this->user_id,
                ":fecha_baja"=>$this->fecha_baja
            ];
            return $stmt->execute($values);

        } catch (Exception $e) {
            throw $e;
        }
    }

    /**
     * Borra, el objeto Reserva instanciado, en BD
     *
     * @return mixed
     * @throws Exception
     */
    public function delete() {
        try {
            $pdo = BDConnectionSingleton::getInstance();

            // Email Obligatorio en BD
            if (empty($this->id))
                throw new Exception("El ID es obligatorio.");

            $sql = "delete from reservas where id=:id";
            $stmt = $pdo->prepare($sql);
            $values = [
                ":id"=>$this->id
            ];
            $result = $stmt->execute($values);
            return $result;

        } catch (Exception $e) {
            throw $e;
        }
    }


}
