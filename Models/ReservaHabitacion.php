<?php
require_once __DIR__ . "/../inc/BDConnectionSingleton.php";

/**
 * Class ReservaHabitacion
 * Clase Modelo para realizar las operaciones necesarias para interacturar con la BD
 * Nunca debe hacer "echo" ni ninguna operación que muestre información. Toda la información
 * será devuelta por el "return" y tratada/utilizada desde el servicio web
 *
 * @since 11/01/2023
 * @author Víctor Sarabia
 * @version 1.0.0
 */
class ReservaHabitacion
{
    // Atributos
    private $id;
    private $habitacion_id;
    private $reserva_id;
    private $cantidad;
    private $precio;
    private $created_at;
    private $updated_at;

    /**
     * ReservaHabitacion constructor.
     * @param $id
     * @param $habitacion_id
     * @param $reserva_id
     * @param $cantidad
     * @param $precio
     */
    public function __construct($id=null, $habitacion_id=null, $reserva_id=null, $cantidad=null, $precio=null)
    {
        $this->id = $id;
        $this->habitacion_id = $habitacion_id;
        $this->reserva_id = $reserva_id;
        $this->cantidad = $cantidad;
        $this->precio = $precio;
    }


    /**
     * Devuelve un array asociativo con los datos del objeto
     * @return array
     */
    public function toArray() {
        return [
            "id"=>$this->id,
            "habitacion_id"=>$this->habitacion_id,
            "reserva_id"=>$this->reserva_id,
            "cantidad"=>$this->cantidad,
            "precio"=>$this->precio,
            "created_at"=>$this->created_at,
            "updated_at"=>$this->updated_at
        ];
    }

    /**
     * Realiza búsquedas de habitaciones de reservas en BD por los filtros indicados.
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

            $sql = 'select * FROM reservas_habitaciones where true';
            $sql_count = 'select count(*) as total FROM reservas_habitaciones where true';
            $sql_where = "";

            $params = [];
            if(isset($filters["id"])) {
                $sql_where.=" and id = :id";
                $params[":id"]=$filters["id"];
            }
            if(isset($filters["habitacion_id"])) {
                $sql_where.=" and habitacion_id = :habitacion_id";
                $params[":habitacion_id"]=$filters["habitacion_id"];
            }
            if(isset($filters["reserva_id"])) {
                $sql_where.=" and reserva_id = :reserva_id";
                $params[":reserva_id"]=$filters["reserva_id"];
            }

            // Para obtener el total de registros
            $stmt = $pdo->prepare($sql_count.$sql_where);
            $stmt->execute($params);
            $reservaHabitacionHabitacion_count = $stmt->fetch(PDO::FETCH_ASSOC);
            $total_registros = $reservaHabitacionHabitacion_count["total"];

            // Para obtener los registros
            $sql.= $sql_where;
            $sql.= sprintf(" limit %d, %d", (($pagina-1)*$num_registros), $num_registros);

            $stmt = $pdo->prepare($sql);
            $stmt->execute($params);

            $reservaHabitacion = $stmt->fetchALL(PDO::FETCH_ASSOC);
            $row=null;
            $stmt=null;
            $pdo = null;

            return ["reservas"=>$reservaHabitacion, "total_registros"=>$total_registros, "pagina"=>$pagina, "registros_por_pagina"=>$num_registros];
        }
        catch(PDOException $e) {
            // Relanza la excepción para ser tratada en el SW
            throw new Exception($e);
        }
    }


    /**
     * Relaciona una Habitación con una Reserva en BD
     *
     * @return mixed
     * @throws Exception
     */
    public function insert() {
        try {
            $pdo = BDConnectionSingleton::getInstance();

            // Email Obligatorio en BD
            if (empty($this->habitacion_id))
                throw new Exception("El ID de habitación es obligatorio.");
            if (empty($this->reserva_id))
                throw new Exception("El Id de reserva es obligatorio.");
            if (empty($this->cantidad))
                throw new Exception("La cantidad es obligatoria.");
            if (empty($this->precio))
                throw new Exception("El precio es obligatorio.");

            $sql = "insert into reservas_habitaciones values (null, :habitacion_id, :reserva_id, :cantidad, :precio, now(), now())";
            $stmt = $pdo->prepare($sql);
            $values = [
                ":habitacion_id"=>$this->habitacion_id,
                ":reserva_id"=>$this->reserva_id,
                ":cantidad"=>$this->cantidad,
                ":precio"=>$this->precio
            ];
            return $stmt->execute($values);

        } catch (Exception $e) {
            throw $e;
        }
    }

    /**
     * Modifica, la relación de Habitación con Reserva en BD
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
            if (empty($this->habitacion_id))
                throw new Exception("El ID de habitación es obligatorio.");
            if (empty($this->reserva_id))
                throw new Exception("El Id de reserva es obligatorio.");
            if (empty($this->cantidad))
                throw new Exception("La cantidad es obligatoria.");
            if (empty($this->precio))
                throw new Exception("El precio es obligatorio.");

            $sql = "update reservas_habitaciones set 
                 habitacion_id=:habitacion_id, 
                 reserva_id=:reserva_id, 
                 cantidad=:cantidad, 
                 precio=:precio,  
                 updated_at=now() 
                where id=:id";
            $stmt = $pdo->prepare($sql);
            $values = [
                ":id"=>$this->id,
                ":habitacion_id"=>$this->habitacion_id,
                ":reserva_id"=>$this->reserva_id,
                ":cantidad"=>$this->cantidad,
                ":precio"=>$this->precio
            ];
            return $stmt->execute($values);

        } catch (Exception $e) {
            throw $e;
        }
    }

    /**
     * Borra, la relación de Habitación con Reserva en BD
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

            $sql = "delete from reservas_habitaciones where id=:id";
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
