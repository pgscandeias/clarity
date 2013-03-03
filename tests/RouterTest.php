<?php
require_once APP_ROOT . '/router.php';


class RouterTest extends PHPUnit_Framework_TestCase
{
    private $callback;

    public function setUp()
    {
        parent::setUp();

        $this->callback = function(array $params = array()) {
            return $params;
        };
    }

    public function testClass()
    {
        $r = new Router;
        $this->assertInstanceOf('Router', $r);
    }

    public function testMatchPattern()
    {
        // Simple urls
        $this->assertNotEmpty(Router::matchPattern('/', '/'));
        $this->assertNotEmpty(Router::matchPattern('/foo', '/foo'));
        $this->assertEmpty(Router::matchPattern('/foo', '/fooo'));
        $this->assertEmpty(Router::matchPattern('/foo', '/notfoo'));

        // With named parameters
        $this->assertNotEmpty(Router::matchPattern('/foo/:a1', '/foo/bar'));
        $this->assertNotEmpty(Router::matchPattern('/foo/:a1/:a2', '/foo/bar/baz'));
    }

    public function testArguments()
    {
        // Site root
        $expectedArguments = array(
            'match' => true,
            'arguments' => array(),
        );
        $this->assertEquals($expectedArguments, Router::matchPattern('/', '/'));

        // Complex route
        $expectedArguments = array(
            'match' => true,
            'arguments' => array(
                'controller' => 'users',
                'action' => 'login',
                'param' => 'remember',
            )
        );
        $this->assertEquals($expectedArguments,
                            Router::matchPattern(
                                '/:controller/:action/:param',
                                '/users/login/remember'
                            ));
    }
}
