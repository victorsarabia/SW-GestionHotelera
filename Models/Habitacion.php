<?php
require_once __DIR__ . "/../inc/BDConnectionSingleton.php";

/**
 * Class Habitacion
 * Clase Modelo para realizar las operaciones necesarias para interacturar con la BD
 * Nunca debe hacer "echo" ni ninguna operación que muestre información. Toda la información
 * será devuelta por el "return" y tratada/utilizada desde el servicio web
 *
 * @since 11/01/2023
 * @author Víctor Sarabia
 * @version 1.0.0
 */
class Habitacion
{
    // Atributos
    private $id;
    private $nombre;
    private $descripcion;
    private $cantidad;
    private $precio;
    private $numero_maximo_personas;
    private $numero_camas;
    private $fecha_baja;
    private $created_at;
    private $updated_at;


    /**
     * Habitacion constructor.
     * @param null $id
     * @param null $nombre
     * @param null $descripcion
     * @param null $cantidad
     * @param null $precio
     * @param null $numero_maximo_personas
     * @param null $numero_camas
     * @param null $fecha_baja
     */
    public function __construct($id=null, $nombre=null, $descripcion=null, $cantidad=null, $precio = null, $numero_maximo_personas = null, $numero_camas = null, $fecha_baja = null)
    {
        $this->id = $id;
        $this->nombre = $nombre;
        $this->descripcion = $descripcion;
        $this->cantidad = $cantidad;
        $this->precio = $precio;
        $this->numero_maximo_personas = $numero_maximo_personas;
        $this->numero_camas = $numero_camas;
        $this->fecha_baja = $fecha_baja;
    }

    /**
     * Devuelve un array asociativo con los datos del objeto
     * @return array
     */
    public function toArray() {
        return [
            "id"=>$this->id,
            "nombre"=>$this->nombre,
            "descripcion"=>$this->descripcion,
            "cantidad"=>$this->cantidad,
            "precio"=>$this->precio,
            "numero_maximo_personas"=>$this->numero_maximo_personas,
            "numero_camas"=>$this->numero_camas,
            "fecha_baja"=>$this->fecha_baja,
            "created_at"=>$this->created_at,
            "updated_at"=>$this->updated_at
        ];
    }

    /**
     * Realiza búsquedas de habitaciones en BD por los filtros indicados.
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

            $sql = 'select * FROM habitaciones where true';
            $sql_count = 'select count(*) as total FROM habitaciones where true';
            $sql_where = "";

            $params = [];
            if(isset($filters["id"])) {
                $sql_where.=" and id = :id";
                $params[":id"]=$filters["id"];
            }
            if(isset($filters["nombre"])) {
                $sql_where.=" and nombre like :nombre";
                $params[":nombre"]="%".$filters["nombre"]."%";
            }
            if(isset($filters["precio_desde"]) && isset($filters["precio_hasta"])) {
                $sql_where.=" and precio between :precio_hasta and :precio_desde";
                $params[":precio_desde"]=$filters["precio_desde"];
                $params[":precio_hasta"]=$filters["precio_hasta"];
            }
            if(isset($filters["precio_desde"]) && !isset($filters["precio_hasta"])) {
                $sql_where.=" and precio >= :precio_desde";
                $params[":precio_desde"]=$filters["precio_desde"];
            }
            if(isset($filters["precio_hasta"]) && !isset($filters["precio_desde"])) {
                $sql_where.=" and precio <= :precio_hasta";
                $params[":precio_hasta"]=$filters["precio_hasta"];
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
            $habitaciones_count = $stmt->fetch(PDO::FETCH_ASSOC);
            $total_registros = $habitaciones_count["total"];

            // Para obtener los registros
            $sql.= $sql_where;
            $sql.= sprintf(" limit %d, %d", (($pagina-1)*$num_registros), $num_registros);

            $stmt = $pdo->prepare($sql);
            $stmt->execute($params);

            $habitaciones = $stmt->fetchALL(PDO::FETCH_ASSOC);
            $row=null;
            $stmt=null;
            $pdo = null;

            return ["habitaciones"=>$habitaciones, "total_registros"=>$total_registros, "pagina"=>$pagina, "registros_por_pagina"=>$num_registros];
        }
        catch(PDOException $e) {
            // Relanza la excepción para ser tratada en el SW
            throw new Exception($e);
        }
    }


    /**
     * Inserta, el objeto Habitación instanciado, en BD
     *
     * @return mixed
     * @throws Exception
     */
    public function insert() {
        try {
            $pdo = BDConnectionSingleton::getInstance();

            // Email Obligatorio en BD
            if (empty($this->nombre))
                throw new Exception("El nombre es obligatorio.");
            if (empty($this->cantidad))
                throw new Exception("La cantidad de habitaciones es obligatorio.");
            if (empty($this->numero_maximo_personas))
                throw new Exception("El número máximo de personas es obligatorio.");
            if (empty($this->numero_camas))
                throw new Exception("El numero de camas es obligatorio.");

            $sql = "insert into habitaciones values (null, :nombre, :descripcion, :cantidad, :precio, :numero_maximo_personas, :numero_camas, :fecha_baja, now(), now())";
            $stmt = $pdo->prepare($sql);
            $values = [
                ":nombre"=>$this->nombre,
                ":descripcion"=>$this->descripcion,
                ":cantidad"=>$this->cantidad,
                ":precio"=>$this->precio,
                ":numero_maximo_personas"=>$this->numero_maximo_personas,
                ":numero_camas"=>$this->numero_camas,
                ":fecha_baja"=>$this->fecha_baja
            ];
            return $stmt->execute($values);

        } catch (Exception $e) {
            throw $e;
        }
    }

    /**
     * Modifica, el objeto Habitación instanciado, en BD
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
            if (empty($this->nombre))
                throw new Exception("El nombre es obligatorio.");
            if (empty($this->cantidad))
                throw new Exception("La cantidad de habitaciones es obligatorio.");
            if (empty($this->numero_maximo_personas))
                throw new Exception("El número máximo de personas es obligatorio.");
            if (empty($this->numero_camas))
                throw new Exception("El numero de camas es obligatorio.");

            $sql = "update habitaciones set 
                 nombre=:nombre, 
                 descripcion=:descripcion, 
                 cantidad=:cantidad, 
                 precio=:precio, 
                 numero_maximo_personas=:numero_maximo_personas, 
                 numero_camas=:numero_camas, 
                 fecha_baja=:fecha_baja, 
                 updated_at=now() 
                where id=:id";
            $stmt = $pdo->prepare($sql);
            $values = [
                ":id"=>$this->id,
                ":nombre"=>$this->nombre,
                ":descripcion"=>$this->descripcion,
                ":cantidad"=>$this->cantidad,
                ":precio"=>$this->precio,
                ":numero_maximo_personas"=>$this->numero_maximo_personas,
                ":numero_camas"=>$this->numero_camas,
                ":fecha_baja"=>$this->fecha_baja
            ];
            return $stmt->execute($values);

        } catch (Exception $e) {
            throw $e;
        }
    }

    /**
     * Borra, el objeto Habitación instanciado, en BD
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

            $sql = "delete from habitaciones where id=:id";
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
