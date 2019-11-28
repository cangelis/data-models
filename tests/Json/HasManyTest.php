<?php

use CanGelis\DataModels\DataCollection;
use CanGelis\DataModels\JsonModel;
use PHPUnit\Framework\TestCase;

class Post extends JsonModel {

}

/**
 * Class User
 *
 * @property DataCollection $posts
 */
class User extends JsonModel {

    protected $hasMany = [
        'posts' => Post::class
    ];

}

class HasManyTest extends TestCase {

    public function testReturnCollectionWhenDataIsArray()
    {
        $user = new User(['posts' => [['foo' => 'bar'], ['foo' => 'baz']]]);
        $this->assertInstanceOf(DataCollection::class, $user->posts);
        $this->assertInstanceOf(Post::class, $user->posts->first());
        $this->assertEquals(2, $user->posts->count());
    }

    public function testReturnEmptyCollectionWhenAttributeDoesNotExist()
    {
        $user = new User([]);
        $this->assertInstanceOf(DataCollection::class, $user->posts);
        $this->assertEquals(0, $user->posts->count());
    }

    public function testReturnEmptyCollectionWhenAttributeIsNotAnArray()
    {
        $user = new User(['posts' => null]);
        $this->assertInstanceOf(DataCollection::class, $user->posts);
        $this->assertEquals(0, $user->posts->count());
    }

    public function testArrayValuesIsSetAsExceptedWhenItIsArrayOfArray()
    {
        $user = new User([]);
        $user->posts = [['foo' => 'bar']];
        $this->assertInstanceOf(DataCollection::class, $user->posts);
        $this->assertEquals('bar', $user->posts->first()->foo);
        $this->assertEquals(1, $user->posts->count());
    }

    public function testModelValuesAreSetAsExpectedWhenItIsArrayOfObjects()
    {
        $user = new User([]);
        $user->posts = [new Post(['foo' => 'bar'])];
        $this->assertInstanceOf(DataCollection::class, $user->posts);
        $this->assertEquals('bar', $user->posts->first()->foo);
        $this->assertEquals(1, $user->posts->count());
        $this->assertEquals(['foo' => 'bar'], $user->toArray()['posts'][0]);
    }

    public function testModelValuesAreSetAsExpectedWhenItIsArrayOfMixedTypes()
    {
        $user = new User([]);
        $user->posts = [new Post(['foo' => 'bar']), ['foo' => 'baz']];
        $this->assertInstanceOf(DataCollection::class, $user->posts);
        $this->assertEquals('bar', $user->posts[0]->foo);
        $this->assertEquals('baz', $user->posts[1]->foo);
        $this->assertEquals(2, $user->posts->count());
        $this->assertEquals(['foo' => 'bar'], $user->toArray()['posts'][0]);
        $this->assertEquals(['foo' => 'baz'], $user->toArray()['posts'][1]);
    }

    public function testModelValuesAreSetAsExpectedWhenValuesAreProvidedAsCollection()
    {
        $user = new User([]);
        $user->posts = new DataCollection([new Post(['foo' => 'bar']), new Post(['foo' => 'baz'])]);
        $this->assertInstanceOf(DataCollection::class, $user->posts);
        $this->assertEquals('bar', $user->posts[0]->foo);
        $this->assertEquals('baz', $user->posts[1]->foo);
        $this->assertEquals(2, $user->posts->count());
        $this->assertEquals(['foo' => 'bar'], $user->toArray()['posts'][0]);
        $this->assertEquals(['foo' => 'baz'], $user->toArray()['posts'][1]);
    }

    public function testCollectionIsAdded()
    {
        $user = new User([]);
        $user->posts = [new Post(['foo' => 'bar'])];
        $user->posts->add(new Post(['foo' => 'baz']));
        $this->assertEquals('baz', $user->posts[1]->foo);
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testHasManyThrowsErrorWhenUnexpectedValueIsProvided()
    {
        $user = new User([]);
        $user->posts = ['foo'];
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testHasManyThrowsErrorWhenNoCollectionIsProvided()
    {
        $user = new User([]);
        $user->posts = 'foo';
    }
}
