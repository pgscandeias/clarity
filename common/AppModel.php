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
            // XXX: Do this later but be sure to fix ALL THE THINGS!!!111oneone
            // if ($property == 'created' || $property == 'updated') {
            //     $value = new \DateTime($value);
            // }
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

    public static function findOneBy($field, $value = null)
    {
        if (empty(static::$_table)) return;

        if (!is_array($field)) $input = array($field => $value);
        else $input = $field;

        foreach ($input as $k => $v) {
            @$params[":$k"] = $v;
            @$pairs[] = "$k = :$k";
        }

        $sth = static::$db->prepare('
            SELECT *
            FROM '.static::$_table.'
            WHERE '.implode(' AND ', $pairs)
        );

        if (!$sth->execute($params)) return;

        $row = $sth->fetch(PDO::FETCH_OBJ);
        if (!$row) return;

        $o = new static($row);
        $o->id = $row->id;

        return $o;
    }

    // 'Room' model has its own save()
    public function save($debug = false)
    {
        if (empty(static::$_fields)) {
            if (APP_ENV != 'prod') die('no fields');
            else return;
        }

        $params = $columns = array();
        foreach (static::$_fields as $field) {
            $columns[] = $field;
            if ($this->{$field} instanceof AppModel) {
                die("what");
            }
            $params[':'.$field] = $this->{$field};
        }

        if ($this->id) $this->update($params, $debug);
        else $this->insert($columns, $params, $debug);
    }

    public function delete()
    {
        $q = "DELETE FROM ".static::$_table." WHERE id = :id";
        $sth = static::$db->prepare($q);
        return $sth->execute(array(':id' => $this->id));
    }


    protected function insert(array $columns, array $params, $debug = false)
    {
        $q = 'INSERT INTO '.static::$_table.' ('
           . implode(', ', $columns)
           . ') VALUES ('
           . implode(', ', array_keys($params))
           . ')'
        ;
        $sth = static::$db->prepare($q);
        if (!$sth->execute($params)) {
            if (APP_ENV != 'prod') {
                $error = $sth->errorInfo();
                throw new \Exception("MySQL Err#".$error[0].": ".$error[2]);
                die;
            }
            return;
        }

        $this->id = static::$db->lastInsertId();
    }

    protected function update(array $params, $debug = false)
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