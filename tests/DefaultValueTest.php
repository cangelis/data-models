<?php

use CanGelis\DataModels\Cast\FloatCast;
use CanGelis\DataModels\JsonModel;
use PHPUnit\Framework\TestCase;

/**
 * Class Comment
 *
 * @property string $author
 * @property string $text
 * @property float $rate
 */
class Comment extends JsonModel {

    protected $casts = [
        'rate' => FloatCast::class
    ];

    protected $defaults = [
        'author' => 'Can Gelis',
        'rate' => '0.0'
    ];
}

class DefaultValueTest extends TestCase
{
    public function testDefaultValueIsReturnedWhenItDoesntExist()
    {
        $comment = new Comment([]);
        $this->assertEquals('Can Gelis', $comment->author);
    }

    public function testDefaultValueIsNotReturnedWhenTheValueExists()
    {
        $comment = new Comment(['author' => 'Foo Bar']);
        $this->assertEquals('Foo Bar', $comment->author);
    }

    public function testDefaultValueIsNotReturnedWhenTheValuesIsNull()
    {
        $comment = new Comment(['author' => null]);
        $this->assertNull($comment->author);
    }

    public function testReturnsNullWhenItIsNotDefault()
    {
        $comment = new Comment([]);
        $this->assertNull($comment->text);
    }

    public function testDefaultIsCasted()
    {
        $comment = new Comment([]);
        $this->assertEquals(0.0, $comment->rate);
        $this->assertEquals('double', gettype($comment->rate));
    }
}
