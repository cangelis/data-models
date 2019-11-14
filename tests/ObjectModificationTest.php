<?php

use CanGelis\DataModels\Cast\AbstractCast;
use CanGelis\DataModels\DataCollection;
use CanGelis\DataModels\DataModel;
use PHPUnit\Framework\TestCase;

// casts the array to datacollection
class DataCollectionCaster extends AbstractCast
{
    public function uncast($value)
    {
        if ($value instanceof DataCollection) {
            return $value->toArray();
        }
        return $value;
    }

    public function cast($value)
    {
        return new DataCollection($value);
    }
}

class Menu extends DataModel {

    protected $casts = [
        'sub_menus' => DataCollectionCaster::class
    ];

    protected $hasOne = [
        'one_menu' => Menu::class
    ];

    protected $hasMany = [
        'many_menus' => Menu::class
    ];

}

class ObjectModificationTest extends TestCase
{
    public function testObjectIsModifiedAfterAccessed()
    {
        $menu = new Menu([
            'id' => 1,
            'sub_menus' => [
                new Menu(['id' => 2])
            ]
        ]);
        $menu->sub_menus->add(new Menu(['id' => 3]));
        $this->assertEquals(2, $menu->sub_menus->count());
        $this->assertEquals(2, $menu->sub_menus[0]->id);
        $this->assertEquals(3, $menu->sub_menus[1]->id);
    }

    public function testHasOneRelationModificationTakesAffect()
    {
        $menu = new Menu(['id' => 1, 'one_menu' => ['id' => 2]]);
        $menu->one_menu->id = 3;
        $this->assertEquals(3, $menu->toArray()['one_menu']['id']);
    }

    public function testHasManyRelationModificationTakesAffect()
    {
        $menu = new Menu(['id' => 1, 'many_menus' => [['id' => 2]]]);
        $menu->many_menus->add(new Menu(['id' => 5]));
        $menu->many_menus->add(new Menu(['id' => 7]));
        $this->assertEquals(3, count($menu->toArray()['many_menus']));
        $this->assertEquals(2, $menu->toArray()['many_menus'][0]['id']);
        $this->assertEquals(5, $menu->toArray()['many_menus'][1]['id']);
        $this->assertEquals(7, $menu->toArray()['many_menus'][2]['id']);
    }
}
