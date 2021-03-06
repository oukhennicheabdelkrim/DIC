<?php

namespace oukhennicheabdelkrim\DIC\tests\containerTests;

use oukhennicheabdelkrim\DIC\DIC;
use PHPUnit\Framework\TestCase;
use Bar,Foo;

require_once dirname(__DIR__).'/TestClass/bootstrap.php';

class FactoryTest extends TestCase
{

    public function testFactory()
    {
        $dic  = new DIC();
        $bar1 = $dic->getFactory('Bar');
        $bar2 = $dic->getFactory('Bar');
        $this->assertNotEquals($bar1,$bar2);
    }

    public function testFactoryInjectionEqual_1()
    {
        $dic  = new DIC();
        $foo = $dic->get('Foo');
        $bar = $dic->getFactory('Bar');
        $this->assertEquals($foo,$bar->foo);
    }

    public function testFactoryInjectionEqual_2()
    {
        $dic  = new DIC();
        $bar1 = $dic->getFactory('Bar');
        $bar2 = $dic->getFactory('Bar');
        $this->assertEquals($bar1->foo,$bar2->foo);
    }


    public function testSingletonAfterGettingFactory()
    {
        $dic= new DIC();
        $bar0 = $dic->get('Bar');
        $newbar = $dic->getFactory('Bar');
        $bar1 = $dic->get('Bar');
        $this->assertEquals($bar0,$bar1);
    }

    public function testFactoryByResolve_1()
    {
        $dic= new DIC();
        $dic->bind('bar1',function ($dic){
            return new Bar($dic->getFactory('Foo'));
        });
        $this->assertNotEquals($dic->getFactory('bar1'),$dic->getFactory('bar1'));
    }

    public function testFactoryByResolve_2()
    {
        $dic= new DIC();
        $dic->bind('bar1',function ($dic){
            return new Bar($dic->getFactory('Foo'));
        });
        $this->assertNotEquals($dic->getFactory('Bar'),$dic->getFactory('bar1'));
    }

    public function testFactoryByInstnaceInjection()
    {
        $dic= new DIC();
        $foo = new Foo();
        $dic->bind('defaultFaut',$foo);
        $this->assertNotEquals($foo,$dic->getFactory('Foo'));
    }

    public function testFactoryByDicInstnaceInjection()
    {
        $dic= new DIC();
        $dic->bind('foo1',$dic->getFactory('Foo'));
        $dic->bind('foo2',$dic->getFactory('Foo'));
        $this->assertNotEquals($dic->get('foo1'),$dic->get('foo2'));
    }

}
