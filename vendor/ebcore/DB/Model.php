<?php

namespace ebcore\DB;

abstract class Model
{
    protected static $table;
    protected static $connection;

    public static function setTable($table)
    {
        static::$table = $table;
    }

    protected static function loadDatabaseConfig()
    {
        $configPath = __DIR__ . '/../../../config/database.json';
        if (!file_exists($configPath)) {
            throw new \Exception("Database config file not found.");
        }
        $config = json_decode(file_get_contents($configPath), true);
        return $config['db'];
    }

    public static function getConnection()
    {
        if (!static::$connection) {
            $dbConfig = static::loadDatabaseConfig();

            static::$connection = new \PDO(
                'mysql:host=' . $dbConfig['host'] . ';dbname=' . $dbConfig['database'],
                $dbConfig['username'],
                $dbConfig['password']
            );
            static::$connection->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
        }
        return static::$connection;
    }

    public static function all()
    {
        $stmt = static::getConnection()->prepare("SELECT * FROM " . static::$table);
        $stmt->execute();
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    public static function find($id)
    {
        $stmt = static::getConnection()->prepare("SELECT * FROM " . static::$table . " WHERE ID = " . $id);
        $stmt->execute();
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    public static function create($array)
    {
        try {
            $columns = [];
            $placeholders = [];
            $params = [];
            foreach ($array as $param => $value) {
                $columns[] = $param;
                $placeholders[] = ':' . $param;
                $params[':' . $param] = $value;
            }
            $columnsStr = implode(', ', $columns);
            $placeholdersStr = implode(', ', $placeholders);
            $connection = static::getConnection();
            $sql = "INSERT INTO " . static::$table . " (" . $columnsStr . ") VALUES (" . $placeholdersStr . ")";
            $stmt = $connection->prepare($sql);
            $stmt->execute($params);
            $lastId = $connection->lastInsertId();
            $stmt = $connection->prepare("SELECT * FROM " . static::$table . " WHERE id = :id");
            $stmt->execute([':id' => $lastId]);
            return $stmt->fetch(\PDO::FETCH_ASSOC);
        } catch (\PDOException $e) {
            error_log("Database error: " . $e->getMessage());
            return false;
        }
    }

    public function update($array)
    {
        try {
            $query = '';
            $i=1;
            $params = [];
            foreach ($array[0] as $param => $value) {
                if (count($array[0]) <= $i) {
                    $query .= $param .' = '. ':'.$param;
                } else {
                    $query .= $param .' = '. ':'.$param . ',';
                }
                $i++;
                $params[':' . $param] = $value;
            }
            $connection = static::getConnection();
            $sql = "UPDATE " . static::$table . " SET " . $query . " WHERE " . "id=" . $array[1];
            $stmt = $connection->prepare($sql);
            $stmt->execute($params);
            $stmt = $connection->prepare("SELECT * FROM " . static::$table . " WHERE id = :id");
            $stmt->execute([':id' => $array[1]]);
            return $stmt->fetch(\PDO::FETCH_ASSOC);
        } catch (\PDOException $e) {
            return $e->getMessage();
            error_log("Database error: " . $e->getMessage());
            return false;
        }
    }

    public function delete($id)
    {
        try {
            $sql = "DELETE FROM " . static::$table . " WHERE id =:id";
            $connection = static::getConnection();
            $stmt = $connection->prepare($sql);
            $stmt->bindParam(':id', $id);
            $stmt->execute();
            return true;
        } catch (\PDOException $e) {
            error_log("Database error: " . $e->getMessage());
            return false;
        }
    }

    public function newQuery()
    {
        return new QueryBuilder(static::$table);
    }
}
