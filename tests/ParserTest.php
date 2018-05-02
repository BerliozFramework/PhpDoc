<?php
/**
 * This file is part of Berlioz framework.
 *
 * @license   https://opensource.org/licenses/MIT MIT License
 * @copyright 2017 Ronan GIRON
 * @author    Ronan GIRON <https://github.com/ElGigi>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code, to the root.
 */

namespace Berlioz\PhpDoc\Tests;

use Berlioz\PhpDoc\Parser;
use Berlioz\PhpDoc\Tag;
use Berlioz\PhpDoc\Tests\files\SpecialTag;
use PHPUnit\Framework\TestCase;

class ParserTest extends TestCase
{
    const DOC = <<<'EOD'
    /**
     * Test doc.
     *
     * My description of my method.
     * Multi-line.
     *
     * @test  false
     * @novalue
     * @value Only text
     * @test2("test", param1=true, param2="test", param3={"test":"test"})
     * @value Second text
     * @jsonTest {"test":"test"}
     * @jsonArrayTest [{"test":"test"}, {"test2":"test2"}]
     */
EOD;
    const DOC2 = <<<'EOD'
    /** Test doc.
     *
     * My description of my method.
     * Multi-line. */
EOD;

    public function testParse()
    {
        $parser = new Parser;
        $doc = $parser->parse(self::DOC);

        $this->assertArrayHasKey('title', $doc);
        $this->assertArrayHasKey('description', $doc);
        $this->assertArrayHasKey('tags', $doc);

        $this->assertEquals('Test doc.', $doc['title']);
        $this->assertEquals("My description of my method.\nMulti-line.", $doc['description']);
        $this->assertCount(7, $doc['tags']);

        foreach ($doc['tags'] as $tag) {
            $this->assertInstanceOf(Tag::class, $tag);
        }
    }

    public function testParse2()
    {
        $parser = new Parser;
        $doc = $parser->parse(self::DOC2);

        $this->assertArrayHasKey('title', $doc);
        $this->assertArrayHasKey('description', $doc);
        $this->assertArrayHasKey('tags', $doc);

        $this->assertEquals('Test doc.', $doc['title']);
        $this->assertEquals("My description of my method.\nMulti-line.", $doc['description']);
        $this->assertCount(0, $doc['tags']);
    }

    public function testAddTag()
    {
        $parser = new Parser;
        $parser->addTag('value', SpecialTag::class);
        $doc = $parser->parse(self::DOC);

        foreach ($doc['tags'] as $tag) {
            $this->assertInstanceOf(Tag::class, $tag);
        }

        $this->assertNotInstanceOf(SpecialTag::class, $doc['tags'][1]);
        $this->assertInstanceOf(SpecialTag::class, $doc['tags'][2]);
        $this->assertInstanceOf(SpecialTag::class, $doc['tags'][4]);
    }
}
