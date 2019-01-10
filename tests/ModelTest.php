<?php

namespace Pop\Test;

use Pop\Test\TestAsset\TestModel;
use PHPUnit\Framework\TestCase;

class ModelTest extends TestCase
{

    public function testModel()
    {
        $model = new TestModel([
            'foo' => 'bar',
            'baz' => 123
        ]);
        $model->something = 'else';
        $model['another'] = 'thing';
        $this->assertInstanceOf('Pop\Model\AbstractModel', $model);
        $this->assertEquals('bar', $model['foo']);
        $this->assertEquals('bar', $model->foo);
        $this->assertEquals('else', $model->something);
        $this->assertEquals('thing', $model['another']);
        $this->assertTrue(isset($model->another));
        $this->assertTrue(isset($model['something']));
        unset($model->another);
        unset($model['something']);
        $this->assertFalse(isset($model->another));
        $this->assertFalse(isset($model['something']));
        $this->assertEquals(2, count($model));
        $this->assertEquals(2, count($model->toArray()));
        $i = 0;
        foreach ($model as $data) {
            $i++;
        }
        $this->assertEquals(2, $i);
    }

}