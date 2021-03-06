<?php


namespace oukhennicheabdelkrim\DIC\tests\containerTests;



use oukhennicheabdelkrim\DIC\DIC;
use PHPUnit\Framework\TestCase;
use Foo,Bar;

require_once dirname(__DIR__).'/TestClass/bootstrap.php';

class InstanceTest  extends TestCase
{
    public function testInstanceOf()
    {
        $dic= new DIC();
        $bar=$dic->get('Bar');
        $this->assertInstanceOf('Bar',$bar);
    }


    public function testInstanceOfByAlias()
    {
        $dic= new DIC();

        $dic->bind('myBar',function ($dic){
            return new Bar($dic->get('Foo'));
        });
        $this->assertInstanceOf('Bar',$dic->get('myBar'));

    }

    public function testInstanceOfByInjection()
    {
        $dic= new DIC();
        $foo = new Foo();
        $dic->bind('myFoo',$foo);
        $this->assertInstanceOf('Foo',$dic->get('myFoo'));
    }

    public function testInstanceOfByResolve()
    {
        $dic= new DIC();
        $dic->bind('myFoo',function ($dic){
            return $dic->get('Foo');
        });
        $this->assertInstanceOf('Foo',$dic->get('myFoo'));
    }

    public function testEqualByInjection()
    {
        $dic= new DIC();
        $foo = new Foo();
        $dic->bind('myfoo',$foo);
        $this->assertEquals($foo,$dic->get('myfoo'));
    }



    public function testSingletonInstance1()
    {
        $dic= new DIC();
        $bar=$dic->get('Bar');
        $this->assertEquals($bar->foo,$dic->get('Foo'));

    }

    public function testSingletonInstance2()
    {
        $dic= new DIC();
        $id=$dic->get('Bar')->id;
        $this->assertEquals($id,$dic->get('Bar')->id);
    }

    public function testSingletonByAlias()
    {
        $dic= new DIC();
        $dic->bind('myFoo',function (){
            return new Foo();

        })->bind('MyBar',function ($dic){

            return new Bar($dic->get('myFoo'));
        });

        $foo = $dic->get('myFoo');

        $this->assertEquals($foo,$dic->get('MyBar')->foo);

    }

    public function testInitParams()
    {
        $dic= new DIC();
        $this->assertEquals(44,$dic->get('Bar')->foo->input);
    }


    public function testInstanceWithOutConstuct()
    {
        $dic= new DIC();
        $this->assertInstanceOf('C',$dic->get('C'));

    }



    /****************

    A
    |   |
    B   C
    |
    D
    | |
    E C



     *****/

    public function testDeepResolveSingleton1()
    {
        $dic= new DIC();
        $a = $dic->get('A');
        $this->assertEquals($a->b->d->c,$dic->get('C'));

    }




    public function testDeepResolveSingleton2()
    {
        $dic= new DIC();
        $a = $dic->get('A');
        $this->assertEquals($a->b->d->c,$a->c);

    }


    public function testDeepResolveSingleton3()
    {
        $dic= new DIC();
        $a = $dic->get('A');
        $this->assertEquals($a->b->d,$dic->get('B')->d);
    }


    public function testDeepResolveFactory()
    {
        $dic= new DIC();
        $a = $dic->get('A');
        $this->assertNotTrue($a===$dic->getFactory('A'));
    }









}
