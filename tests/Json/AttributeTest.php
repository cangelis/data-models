<?php

use CanGelis\DataModels\JsonModel;
use PHPUnit\Framework\TestCase;

class JSONPost extends JsonModel {

}

class AttributeTest extends TestCase
{
    public function testNewAttributeAdded()
    {
        $post = new JsonModel(['id' => 1]);
        $post->title = 'Foo';
        $this->assertEquals(json_encode(['id' => 1, 'title' => 'Foo']), (string) $post);
    }

    public function testAttributeIsModified()
    {
        $post = new JsonModel(['id' => 1]);
        $post->id = 2;
        $this->assertEquals(json_encode(['id' => 2]), (string) $post);
    }

    public function testAttributeIsUnset()
    {
        $post = new JsonModel(['id' => 1]);
        unset($post->id);
        $this->assertEquals(json_encode([]), (string) $post);
    }

    public function testIsset()
    {
        $post = new JsonModel(['id' => 1]);
        $this->assertTrue(isset($post->id));
        $this->assertFalse(isset($post->foo));
    }
}