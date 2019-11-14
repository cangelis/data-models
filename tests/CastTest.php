<?php

use CanGelis\DataModels\Cast\BooleanCast;
use CanGelis\DataModels\Cast\FloatCast;
use CanGelis\DataModels\Cast\IntegerCast;
use CanGelis\DataModels\Cast\StringCast;
use CanGelis\DataModels\DataModel;
use PHPUnit\Framework\TestCase;

/**
 * Class Player
 *
 * @property bool $has_license
 * @property int $age
 * @property double $rate
 * @property string $license_number
 */
class Player extends DataModel {

    protected $casts = [
        'rate' => FloatCast::class,
        'age' => IntegerCast::class,
        'has_license' => BooleanCast::class,
        'license_number' => StringCast::class
    ];

}

class CastTest extends TestCase
{
    public function testBoolean()
    {
        $player = new Player(['has_license' => 'false']);
        $this->assertEquals('boolean', gettype($player->has_license));
        $this->assertFalse($player->has_license);
        $player = new Player(['has_license' => null]);
        $this->assertEquals('boolean', gettype($player->has_license));
        $this->assertFalse($player->has_license);
        $player = new Player(['has_license' => false]);
        $this->assertEquals('boolean', gettype($player->has_license));
        $this->assertFalse($player->has_license);
        $player = new Player(['has_license' => 0]);
        $this->assertEquals('boolean', gettype($player->has_license));
        $this->assertFalse($player->has_license);
        $player = new Player(['has_license' => 'true']);
        $this->assertEquals('boolean', gettype($player->has_license));
        $this->assertTrue($player->has_license);
        $player = new Player(['has_license' => true]);
        $this->assertEquals('boolean', gettype($player->has_license));
        $this->assertTrue($player->has_license);
        $player = new Player(['has_license' => 1]);
        $this->assertEquals('boolean', gettype($player->has_license));
        $this->assertTrue($player->has_license);
    }

    public function testInteger()
    {
        $player = new Player(['age' => 10]);
        $this->assertEquals('integer', gettype($player->age));
        $this->assertEquals(10, $player->age);
        $player = new Player(['age' => '10']);
        $this->assertEquals('integer', gettype($player->age));
        $this->assertEquals(10, $player->age);
        $player = new Player(['age' => '10.0']);
        $this->assertEquals('integer', gettype($player->age));
        $this->assertEquals(10, $player->age);
        $player = new Player(['age' => 10.0]);
        $this->assertEquals('integer', gettype($player->age));
        $this->assertEquals(10, $player->age);
    }

    public function testString()
    {
        $player = new Player(['license_number' => 1234]);
        $this->assertEquals('string', gettype($player->license_number));
        $this->assertEquals('1234', $player->license_number);
        $player = new Player(['license_number' => '1234']);
        $this->assertEquals('string', gettype($player->license_number));
        $this->assertEquals('1234', $player->license_number);
        $player = new Player(['license_number' => null]);
        $this->assertEquals('string', gettype($player->license_number));
        $this->assertEquals('', $player->license_number);
    }

    public function testFloat()
    {
        $player = new Player(['rate' => 10]);
        $this->assertEquals('double', gettype($player->rate));
        $this->assertEquals(10.0, $player->rate);
        $player = new Player(['rate' => '10']);
        $this->assertEquals('double', gettype($player->rate));
        $this->assertEquals(10.0, $player->rate);
        $player = new Player(['rate' => '10.1']);
        $this->assertEquals('double', gettype($player->rate));
        $this->assertEquals(10.1, $player->rate);
        $player = new Player(['rate' => 10.1]);
        $this->assertEquals('double', gettype($player->rate));
        $this->assertEquals(10.1, $player->rate);
        $player = new Player(['rate' => null]);
        $this->assertEquals('double', gettype($player->rate));
        $this->assertEquals(0.0, $player->rate);
    }
}
