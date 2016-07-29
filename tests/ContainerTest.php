<?php

namespace Jsonor;



class ContainerTest extends \PHPUnit_Framework_TestCase {

    public $changes = 0;

    public function assertSameData($expected, $actual) {
        $this->assertEquals(
            $expected,
            json_decode(json_encode($actual), true)
        );
    }

    public function getData() {
        return [
            "a" => 5,
            "b" => ["one", "two"],
            "c" => [2,4,8],
            "d" => [
                [
                    "name" => "Orange",
                ],
                [
                    "name" => "Potato"
                ]
            ]
        ];
    }

    public function testConverting() {
        $original = $this->getData();
        $c = new Container($original);

        $this->assertSameData($original, $c);

        // check count
        $this->assertEquals(count($original), count($c));

        // check json encoding/decoding
        $this->assertSameData(
            $original,
            json_decode(json_encode($c, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES), true)
        );

        // check serialize
        $this->assertSameData(
            $original,
            unserialize(serialize($c))
        );

        // check iterator
        $this->assertSameData(
            $original,
            iterator_to_array($c)
        );
    }

    public function testGetSetUnset() {
        $o = $this->getData();
        $c = new Container($o);

        $this->assertCount(2, $c["d"]);
        $this->assertEquals("Potato", $c["d"][1]["name"]);
        $c["d"][1]["name"] = "Banana";
        $this->assertEquals("Banana", $c["d"][1]["name"]);
        $o["d"][1]["name"] = "Banana";
        $this->assertSameData($o, $c);

        $c["d"][1]["desc"] = "It's fruit";
        $this->assertEquals("It's fruit", $c["d"][1]["desc"]);
        $o["d"][1]["desc"] = "It's fruit";
        $this->assertSameData($o, $c);

        $c["d"][] = [
            "name" => "Apple"
        ];
        $this->assertCount(3, $c["d"]);
        $this->assertEquals("Apple", $c["d"][2]["name"]);
        $o["d"][] = [
            "name" => "Apple"
        ];
        $this->assertSameData($o, $c);

        unset($c["b"]);
        $this->assertArrayNotHasKey("b", $c);
        unset($o["b"]);
        $this->assertSameData($o, $c);
    }

    public function testSave() {
        $o = $this->getData();
        $c = new Container($o);
        $c->onChange(function (Container $container) use (&$o) {
            $o["e"] = "changes";
            $this->assertSameData($o, $container);
            $this->changes++;
        });

        $c["e"] = "changes";

        $c->onChange(function (Container $container) use (&$o) {
            $o["d"][1]["name"] = "Banana";
            $this->assertSameData($o, $container);
            $this->changes++;
        });

        $c["d"][1]["name"] = "Banana";

        $this->assertSameData($o, $c);
        $this->assertEquals(2, $this->changes);
    }
}