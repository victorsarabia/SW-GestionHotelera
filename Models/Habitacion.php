<?php
require __DIR__ . "/../inc/BDConnectionSingleton.php";

/**
 * Class Medico
 * Clase Modelo  de ejemplo para realizar las operaciones necesarias para interacturar con la BD
 * Nunca debe hacer "echo" ni ninguna operación que muestre información. Toda la información
 * será devuelta por el "return" y tratada/utilizada desde el servicio web
 *
 * @since 10/11/2022
 * @author Víctor Sarabia
 * @version 1.0.0
 */
class Habitacion
{
    // Atributos
    private $sip;
    private $nombre;

    /**
     * Medico constructor.
     * @param $sip
     * @param $nombre
     */
    public function __construct($sip=null, $nombre=null)
    {
        $this->sip = $sip;
        $this->nombre = $nombre;
    }

    /**
     * Realiza búsquedas de médicos en BD por los filtros indicados.
     *
     * @param array $filters Array Asociativo con los filtros necesxarios
     * @param int Página solicitada
     * @param int Número de registros a devolver por página
     * @return array Array asociativo con el listado de médicos
     * @throws Exception
     */
    public static function find($filters=[], $apgina=0, $num_registros=10){
        try {
            $pdo = BDConnectionSingleton::getInstance();

            $sql = 'select * FROM pacientes where true';
            $params = [];
            if(isset($filters["sip"])) {
                $sql.=" and sip like :sip";
                $params[":sip"]="%".$filters["sip"]."%";
            }
            if(isset($filters["nombre"])) {
                $sql.=" and nombre like :nombre";
                $params[":nombre"]="%".$filters["nombre"]."%";
            }
            $sql.=" limit 0, 10";
            $stmt = $pdo->prepare($sql);
            $stmt->execute($params);

            /*while ($row = $stmt->fetch(PDO::FETCH_ASSOC)){
                echo $row["dni"]."<br>";
            }*/
            $pacientes = $stmt->fetchALL(PDO::FETCH_ASSOC);
            $row=null;
            $stmt=null;
            $pdo = null;

            return $pacientes;
        }
        catch(PDOException $e) {
            // Relanza la excepción para ser tratada en el SW
            throw new Exception($e);
        }
    }


    /**
     * Inserta el objeto médico instanciado en BD
     *
     * @return mixed
     * @throws Exception
     */
    public function insert() {
        try {
            $pdo = BDConnectionSingleton::getInstance();

            // Nombre Obligatorio en BD
            if (empty($this->nombre))
                throw new Exception("El nombre es obligatorio.");

            //TODO por definir
            $sql = "insert into medico values (null, :nombre ...)";
            $stmt = $pdo->prepare($sql);
            $values = ["ID"=>$this->id, ":nombre"=>$this->name];
            return $stmt->execute($values);

        } catch (Exception $e) {
            throw $e;
        }
    }

}
