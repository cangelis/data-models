<?php

use CanGelis\DataModels\XmlModel;
use PHPUnit\Framework\TestCase;

class XmlPost extends XmlModel {

    protected $attributes = ['title', 'body'];

}

class XmlAttributeTest extends TestCase
{
    public function testAttributeIsAccessedAsExpected()
    {
        $post = XmlPost::fromString('<post title="Foo"/>');
        $this->assertEquals('Foo', $post->title);
    }

    public function testAttributeCanBeModifiedAsExpected()
    {
        $post = XmlPost::fromString('<post title="Foo"/>');
        $this->assertEquals('Foo', $post->title);
        $post->title = 'Bar';
        $this->assertEquals('<post title="Bar"/>', (string) $post);
    }

    public function testNewAttributeCanBeAdded()
    {
        $post = XmlPost::fromString('<post title="Foo"/>');
        $this->assertEquals('Foo', $post->title);
        $post->body = 'Bar';
        $this->assertEquals('<post title="Foo" body="Bar"/>', (string) $post);
    }

    public function testNewChildCanBeAdded()
    {
        $post = XmlPost::fromString('<post title="Foo"/>');
        $post->created_by = 'Foo Bar';
        $this->assertEquals('<post title="Foo"><created_by>Foo Bar</created_by></post>', (string) $post);
    }

    public function testChildIsModified()
    {
        $post = XmlPost::fromString('<post title="Foo"><created_by>Foo Bar</created_by></post>');
        $post->created_by = 'Baz Bazzer';
        $this->assertEquals('<post title="Foo"><created_by>Baz Bazzer</created_by></post>', (string) $post);
    }

    public function testAttributeCanBeUnset()
    {
        $post = XmlPost::fromString('<post title="Foo"/>');
        unset($post->title);
        $this->assertEquals('<post/>', (string) $post);
    }

    public function testChildCanBeUnset()
    {
        $post = XmlPost::fromString('<post title="Foo"><name>Bar</name></post>');
        unset($post->name);
        $this->assertEquals('<post title="Foo"/>', (string) $post);
    }
}
