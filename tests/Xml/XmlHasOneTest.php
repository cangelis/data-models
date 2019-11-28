<?php

use CanGelis\DataModels\XmlModel;
use PHPUnit\Framework\TestCase;

class DetailedXmlSettings extends XmlModel {

    public static $name = 'detailed_settings';

}

class XmlSettings extends XmlModel {

    public static $name = 'settings';

    protected $hasOne = [
        'detailed_settings' => DetailedXmlSettings::class
    ];

    protected $attributes = ['blog_url'];

}

class XmlUser extends XmlModel {

    public static $name = 'user';

    protected $hasOne = [
        'settings' => XmlSettings::class
    ];
}

class XmlHasOneTest extends TestCase
{
    public function testRelationIsSetWhenInputIsArray()
    {
        $user = XmlUser::fromString('<user></user>');
        $user->settings = ['url' => 'https://foo.bar'];
        $this->assertEquals($user->settings->url, 'https://foo.bar');
        $this->assertContains('<user><settings><url>https://foo.bar</url></settings></user>', (string)$user);
        $user->settings->foo = 'Bar';
        $this->assertContains('<user><settings><url>https://foo.bar</url><foo>Bar</foo></settings></user>', (string)$user);
    }

    public function testRelationIsSetWhenInputIsXmlModel()
    {
        $user = XmlUser::fromString('<user></user>');
        $settings = XmlSettings::fromString('<settings><name>Can</name></settings>');
        $user->settings = $settings;
        $this->assertEquals($user->settings->name, 'Can');
        $this->assertContains('<user><settings><name>Can</name></settings></user>', (string)$user);
        $user->settings->surname = 'Gelis';
        $this->assertContains('<user><settings><name>Can</name><surname>Gelis</surname></settings></user>', (string)$user);
    }

    public function testRelationIsSetWhenInputIsXmlElement()
    {
        $user = XmlUser::fromString('<user></user>');
        $xmlSettings = new SimpleXMLElement('<settings><foo>Bar</foo><baz>Bazzer</baz></settings>');
        $user->settings = $xmlSettings;
        $this->assertEquals($user->settings->foo, 'Bar');
        $this->assertContains('<user><settings><baz>Bazzer</baz><foo>Bar</foo></settings></user>', (string)$user);
        $user->settings->bazzer = 'Fooer';
        $this->assertContains('<user><settings><baz>Bazzer</baz><foo>Bar</foo><bazzer>Fooer</bazzer></settings></user>', (string)$user);
    }

    public function testMultipleHasOneRelationships()
    {
        $user = XmlUser::fromString('<user></user>');
        $user->settings = ['foo' => 'bar'];
        $user->settings->detailed_settings = ['baz' => 'bazzer'];
        $this->assertContains('<settings><foo>bar</foo><detailed_settings><baz>bazzer</baz></detailed_settings></settings>', (string)$user);
    }

    public function testHasOneAttributesAreSetAsExpected()
    {
        $user = XmlUser::fromString('<user></user>');
        $user->settings = ['blog_url' => 'http://foo.bar', 'foo' => 'bar'];
        $this->assertContains('<user><settings blog_url="http://foo.bar"><foo>bar</foo></settings></user>', (string) $user);
    }
}
