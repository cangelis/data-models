<?php

use CanGelis\DataModels\Cast\BooleanCast;
use CanGelis\DataModels\Cast\DateCast;
use CanGelis\DataModels\Cast\DateTimeCast;
use CanGelis\DataModels\Cast\FloatCast;
use CanGelis\DataModels\Cast\IntegerCast;
use CanGelis\DataModels\Cast\Iso8601Cast;
use CanGelis\DataModels\Cast\StringCast;
use CanGelis\DataModels\JsonModel;
use Carbon\Carbon;
use PHPUnit\Framework\TestCase;

/**
 * Class Player
 *
 * @property bool $has_license
 * @property int $age
 * @property double $rate
 * @property string $license_number
 */
class Player extends JsonModel {

    protected $casts = [
        'rate' => FloatCast::class,
        'age' => IntegerCast::class,
        'has_license' => BooleanCast::class,
        'license_number' => StringCast::class,
        'birth_date' => DateCast::class,
        'created_at' => DateTimeCast::class,
        'updated_at' => Iso8601Cast::class
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

    public function testDate()
    {
        $player = new Player(['birth_date' => '1990-07-18']);
        $this->assertEquals(Carbon::class, get_class($player->birth_date));
        $player->birth_date = $now = Carbon::now();
        $this->assertEquals(Carbon::class, get_class($player->birth_date));
        $this->assertEquals($now->year, $player->birth_date->year);
        $this->assertEquals($now->month, $player->birth_date->month);
        $this->assertEquals($now->day, $player->birth_date->day);
        $this->assertEquals($now->toDateString(), $player->toArray()['birth_date']);
    }

    public function testDateTime()
    {
        $player = new Player(['created_at' => '2019-01-03 12:13:14']);
        $this->assertEquals(Carbon::class, get_class($player->created_at));
        $player->created_at = $now = Carbon::now();
        $this->assertEquals(Carbon::class, get_class($player->created_at));
        $this->assertEquals($now->year, $player->created_at->year);
        $this->assertEquals($now->month, $player->created_at->month);
        $this->assertEquals($now->day, $player->created_at->day);
        $this->assertEquals($now->toDateTimeString(), $player->toArray()['created_at']);
    }

    public function testIso8601()
    {
        $player = new Player(['updated_at' => '2018-11-11T12:58:27+09:00']);
        $this->assertEquals(Carbon::class, get_class($player->updated_at));
        $player->updated_at = $now = Carbon::now();
        $this->assertEquals(Carbon::class, get_class($player->updated_at));
        $this->assertEquals($now->year, $player->updated_at->year);
        $this->assertEquals($now->month, $player->updated_at->month);
        $this->assertEquals($now->day, $player->updated_at->day);
        $this->assertEquals($now->offsetHours, $player->updated_at->offsetHours);
        $this->assertEquals($now->toIso8601String(), $player->toArray()['updated_at']);
    }
}
