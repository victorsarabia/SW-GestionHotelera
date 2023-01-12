<?php
/**
 * Class BDConnectionSingleton
 * Permite tener una única instancia de conexión a BD para toda la aplicación.
 * No será necesario tener más de una conexión abierta en la aplicación puesto que todas las
 * peticiones tienen una respuesta muy corta.
 * USO POR CUESTIONES DIDÁCTICAS. Una aplicación debería de utilizar todas las conexiones
 * necesarias optimizándolas.
 *
 * @since 10/11/2022
 * @author Víctor Sarabia
 * @version 1.0.0
 */
class BDConnectionSingleton
{
    private static $pdo;

    /**
     * BDConnectionSingleton constructor.
     * @throws Exception
     */
    private function __construct(){
        try {
            require __DIR__ . "/../conf.php";

            # MySQL con PDO_MYSQL
            # Para que la conexion al mysql utilice las collation UTF-8 añadir charset=utf8 al string de la conexion.
            self::$pdo = new PDO("mysql:host=".DB_HOST.";dbname=".DB_NAME.";charset=utf8", DB_USER, DB_PASS);

            # Para que genere excepciones a la hora de reportar errores.
            self::$pdo->setAttribute( PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION );
        } catch (Exception $e) {
            throw new Exception($e);
        }

    }

    /**
     * Obtiene una instanacia de PDO
     * @return PDO
     */
    public static function getInstance(){
        if (!self::$pdo instanceof PDO) {
            new BDConnectionSingleton();
        }

        return self::$pdo;
    }
}
