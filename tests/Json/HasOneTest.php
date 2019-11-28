<?php

use CanGelis\DataModels\JsonModel;
use PHPUnit\Framework\TestCase;

/**
 * Class Settings
 *
 * @property string $foo
 * @property string $baz
 */
class Settings extends JsonModel {

}

/**
 * Class User
 *
 * @property \Settings $settings
 */
class Team extends JsonModel {

    protected $hasOne = ['settings' => Settings::class];

}

class HasOneTest extends TestCase
{
    public function testRelatedModelReturnsAsExpecteWhenInputIsArray()
    {
        $user = new Team(['settings' => ['foo' => 'bar']]);
        $this->assertEquals('bar', $user->settings->foo);
    }

    public function testRelatedModelReturnsAsExpecteWhenInputIsADataModel()
    {
        $user = new Team([]);
        $user->settings = new Settings(['foo' => 'bar']);
        $this->assertEquals('bar', $user->settings->foo);
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testThrowErrorWhenSetValueIsUnexpected()
    {
        $user = new Team([]);
        $user->settings = 'foo';
    }

    public function testRelatedObjectChangeAsExpected()
    {
        $user = new Team([]);
        $user->settings = new Settings(['foo' => 'bar']);
        $user->settings->baz = 'bazzer';
        $this->assertEquals('bar', $user->settings->foo);
        $this->assertEquals('bazzer', $user->settings->baz);
    }
}
