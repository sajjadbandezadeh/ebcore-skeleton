<?php

namespace ebcore\DB;

class QueryBuilder
{
    protected $query;
    protected $bindings = [];
    protected $table;

    public function __construct($table)
    {
        $this->table = $table;
        $this->query = "SELECT * FROM $table";
    }

    public function where($field, $operator, $value)
    {
        if (strpos($this->query, 'WHERE') === false) {
            $this->query .= " WHERE $field $operator ?";
        } else {
            $this->query .= " AND $field $operator ?";
        }

        $this->bindings[] = $value;
        return $this;
    }

    public function orWhere($field, $operator, $value)
    {
        if (strpos($this->query, 'WHERE') === false) {
            $this->query .= " WHERE $field $operator ?";
        } else {
            $this->query .= " OR $field $operator ?";
        }

        $this->bindings[] = $value;
        return $this;
    }

    public function get()
    {
        $stmt = Model::getConnection()->prepare($this->query);
        $stmt->execute($this->bindings);
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }
}