<?php

use CanGelis\DataModels\DataCollection;
use CanGelis\DataModels\XmlModel;
use PHPUnit\Framework\TestCase;

class XmlPlayer extends XmlModel {

    protected $root = 'player';

}

class XmlTeam extends XmlModel {

    protected $hasMany = ['players' => XmlPlayer::class];

}

class XmlHasManyTest extends TestCase
{
    public function testArrayOfInputIsSetAsExpected()
    {
        $team = XmlTeam::fromString('<team></team>');
        $team->players = [['name' => 'Beckham'], ['name' => 'Zidane']];
        $this->assertContains('<team><players><player><name>Beckham</name></player><player><name>Zidane</name></player></players></team>', (string) $team);
        $team->players->add(XmlPlayer::fromArray(['name' => 'Raul']));
        $this->assertContains('<team><players><player><name>Beckham</name></player><player><name>Zidane</name></player><player><name>Raul</name></player></players></team>', (string) $team);
    }

    public function testAddingHasManyXmlInputWorksAsExpected()
    {
        $team = XmlTeam::fromString('<team><players><player><name>Beckham</name></player></players></team>');
        $this->assertContains('<team><players><player><name>Beckham</name></player></players></team>', (string) $team);
        $team->players->add(XmlPlayer::fromArray(['name' => 'Zidane']));
        $this->assertContains('<team><players><player><name>Beckham</name></player><player><name>Zidane</name></player></players></team>', (string) $team);
    }

    public function testSettingCollection()
    {
        $team = XmlTeam::fromString('<team><players/></team>');
        $team->players = new DataCollection([XmlPlayer::fromArray(['name' => 'Zidane']), XmlPlayer::fromArray(['name' => 'Beckham'])]);
        $this->assertContains('<team><players><player><name>Zidane</name></player><player><name>Beckham</name></player></players></team>', (string) $team);
    }
}