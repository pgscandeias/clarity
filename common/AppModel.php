<?php

abstract class AppModel
{
    // PDO instance
    public static $db;

    public $id;


    public function __construct($data = array())
    {
        $data = (array) $data;
        foreach ($data as $property => $value) {
            $this->{$property} = $value;
        }
    }

    public static function connect()
    {
        $dsn = 'mysql'
             . ':dbname=' . Config::get('db_name') 
             . ';port='. Config::get('db_port')
             . ';host=' . Config::get('db_host');
        
        try {
            $dbh = new PDO($dsn, Config::get('db_username'), Config::get('db_password'));
        } catch (PDOException $e) {
            echo "Connection failed";
            if (Config::get('environment') != 'prod') {
                var_dump($e->getMessage());
            }
            die;
        }

        static::$db = $dbh;
    }

    public static function all()
    {
        if (empty(static::$_table)) return;

        $sth = static::$db->prepare('SELECT * FROM '.static::$_table);
        if (!$sth->execute()) return;

        $rows = $sth->fetchAll(PDO::FETCH_OBJ);
        $results = array();

        foreach ($rows as $row) {
            $o = new static;
            $o->id = $row->id;
            foreach ($row as $field => $value) {
                if (in_array($field, static::$_fields)) {
                    $o->{$field} = $value;
                }
            }

            $results[] = $o;
        }

        return $results;
    }

    public static function find($id)
    {
        return static::findOneBy('id', $id);
    }

    public static function findOneBy($field, $value)
    {
        if (empty(static::$_table)) return;

        $sth = static::$db->prepare('
            SELECT *
            FROM '.static::$_table.'
            WHERE '.$field.' = :value
        ');

        if (!$sth->execute(array(':value' => $value))) return;

        $row = $sth->fetch(PDO::FETCH_OBJ);
        if (!$row) return;

        $o = new static($row);
        $o->id = $row->id;

        return $o;
    }

    public function save()
    {
        if (empty(static::$_fields)) return;

        $params = $columns = array();
        foreach (static::$_fields as $field) {
            $columns[] = $field;
            if ($this->{$field} instanceof AppModel) {
                die("what");
            }
            $params[':'.$field] = $this->{$field};
        }

        if ($this->id) $this->update($params);
        else $this->insert($columns, $params);
    }


    protected function insert(array $columns, array $params)
    {
        $q = 'INSERT INTO '.static::$_table.' ('
           . implode(', ', $columns)
           . ') VALUES ('
           . implode(', ', array_keys($params))
           . ')'
        ;
        $sth = static::$db->prepare($q);
        if (!$sth->execute($params)) {
            if (APP_ENV != 'prod') { var_dump($sth->errorInfo()); die; }
            return;
        }

        $this->id = static::$db->lastInsertId();
    }

    protected function update(array $params)
    {
        $pairs = array();
        foreach ($params as $k=>$v) {
            $pairs[] = trim($k, ':')." = $k"; // Remove leading ':''
        }
        $params[':id'] = $this->id;
        
        $q = 'UPDATE '.static::$_table.' SET '
           . implode(', ', $pairs)
           . ' WHERE id = :id'
        ;

        $sth = static::$db->prepare($q);
        if (!$sth->execute($params)) {
            if (APP_ENV != 'prod') { var_dump($sth->errorInfo()); die; }
            return;
        }
    }
}