<?php
require_once APP_ROOT . '/models.php';


class ModelTest extends PHPUnit_Framework_TestCase
{
    private $dummyData = array();
    private $dummyFields = array(
        'created' => 'datetime',
        'views' => 'int',
        'contents' => 'string',
        'isActive' => 'bool',
    );

    public function setUp()
    {
        parent::setUp();
        Model::$db->dummies->drop();

        $this->dummyData = array(
            'created' => new DateTime('2001-02-03 04:05:06'),
            'views' => 99,
            'contents' => 'Lorem ipsum',
            'isActive' => true,
        );
    }

    public function tearDown()
    {
        parent::tearDown();
        Model::$db->dummies->drop();
    }


    public function testConnection()
    {
        $db = Model::$db;
        $this->assertNotEmpty($db);
        $this->assertInstanceOf('MongoDB', $db);
    }

    public function testDefinition()
    {
        foreach ($this->dummyFields as $field => $type) {
            $this->assertEquals($type, Dummy::$_fields[$field]);
        }
    }

    public function testConstructor()
    {
        $d = new Dummy($this->dummyData);

        $this->assertEquals($this->dummyData['created'], $d->created);
        $this->assertEquals($this->dummyData['views'], $d->views);
        $this->assertEquals($this->dummyData['contents'], $d->contents);
    }

    public function testDate2Mongo()
    {
        $phpDate = new DateTime('2009-10-11 12:13:14');
        $mongoDate = new MongoDate(strtotime('2009-10-11 12:13:14'));

        $this->assertEquals(Dummy::date2mongo($phpDate), $mongoDate);
    }

    public function testMongo2Date()
    {
        $phpDate = new DateTime('2009-10-11 12:13:14');
        $mongoDate = new MongoDate(strtotime('2009-10-11 12:13:14'));

        $this->assertEquals($phpDate, Dummy::mongo2date($mongoDate));
    }

    public function testInsert()
    {
        $d = new Dummy($this->dummyData);
        $d->save();

        foreach ($this->dummyData as $field => $value) {
            $this->assertEquals($value, $d->{$field});
        }
    }

    public function testFind()
    {
        $d = new Dummy($this->dummyData);
        $d->save();

        $dbD = Dummy::find($d->_id);

        $this->assertInstanceOf('Dummy', $dbD);
        foreach ($this->dummyFields as $field => $type) {
            $this->assertEquals($this->dummyData[$field], $dbD->{$field});
        }
    }

    public function testDelete()
    {
        $d = new Dummy($this->dummyData);
        $d->save();

        $id = $d->_id;

        $dbD = Dummy::find($id);
        $this->assertInstanceOf('Dummy', $dbD);

        $dbD->delete();
        $dbD2 = Dummy::find($id);
        $this->assertEquals(null, $dbD2);
    }
}


class Dummy extends Model
{
    public static $_collection = 'dummies';
    public static $_fields = array(
        'created' => 'datetime',
        'views' => 'int',
        'contents' => 'string',
        'isActive' => 'bool',
    );
}