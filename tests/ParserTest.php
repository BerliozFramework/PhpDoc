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
use Berlioz\PhpDoc\Tests\files\SpecialTag;
use Berlioz\PhpDoc\Tests\files\TestClass;
use PHPUnit\Framework\TestCase;

class ParserTest extends TestCase
{
    public function testAddTagClass()
    {
        $parser = new Parser;
        $parser->addTagClass('test2', SpecialTag::class);

        $doc = $parser->fromDocComment(<<<'EOD'
/**
 * Test doc.
 *
 * @test  It's a test
 * @test2 It's a test
 */
EOD
        );
        $this->assertEquals("It's a test", $doc->getTag('test')[0]->getValue());
        $this->assertEquals("test", $doc->getTag('test2')[0]->getValue());
    }

    public function testFromFunction()
    {
        require_once __DIR__ . '/files/function.php';
        require_once __DIR__ . '/files/function2.php';

        $parser = new Parser;

        $doc = $parser->fromFunction('Berlioz\PhpDoc\Tests\files\test');
        $this->assertEquals('Function test.', $doc->getTitle());
        $this->assertNull($doc->getDescription());
        $this->assertCount(2, $doc->getTags());
        $this->assertCount(2, $doc->getTag('param'));
        $this->assertEquals('string $param1', $doc->getTag('param')[0]->getValue());
        $this->assertEquals('int    $param2', $doc->getTag('param')[1]->getValue());

        $doc = $parser->fromFunction('test');
        $this->assertEquals('Function test.', $doc->getTitle());
        $this->assertNull($doc->getDescription());
        $this->assertCount(2, $doc->getTags());
        $this->assertCount(2, $doc->getTag('param'));
        $this->assertEquals('string $param1', $doc->getTag('param')[0]->getValue());
        $this->assertEquals('int    $param2', $doc->getTag('param')[1]->getValue());
    }

    public function testFromFunctionException()
    {
        $this->expectException(\InvalidArgumentException::class);
        $parser = new Parser;
        $parser->fromFunction('unknownFunction');
    }

    public function testFromMethod()
    {
        $parser = new Parser;

        $doc = $parser->fromMethod(TestClass::class, 'method1');
        $this->assertEquals('My method.', $doc->getTitle());
        $this->assertEquals("My description of method.\nAgain.", $doc->getDescription());
        $this->assertCount(2, $doc->getTags());
        $this->assertCount(3, $doc->getTag('param'));
        $this->assertEquals('string            $param1 Parameter 1', $doc->getTag('param')[0]->getValue());
        $this->assertEquals('int               $param2 Parameter 2', $doc->getTag('param')[1]->getValue());
        $this->assertEquals('\SimpleXMLElement $param3 Parameter 3', $doc->getTag('param')[2]->getValue());
        $parser = new Parser;

        $doc = $parser->fromMethod(TestClass::class, 'method2');
        $this->assertEquals('My method 2.', $doc->getTitle());
        $this->assertEquals("My description of method 2.\nAgain.", $doc->getDescription());
        $this->assertCount(4, $doc->getTags());
        $this->assertCount(2, $doc->getTag('param'));
        $this->assertEquals('string $param1 Parameter 1', $doc->getTag('param')[0]->getValue());
        $this->assertEquals('int    $param2 Parameter 2', $doc->getTag('param')[1]->getValue());
        $this->assertEquals([0        => 'test',
                             'param1' => true,
                             'param2' => 'test',
                             'param3' => json_decode('{"test":"test"}')],
                            $doc->getTag('route')[0]->getValue());
    }

    public function testFromClass()
    {
        $parser = new Parser;

        /** @var \Berlioz\PhpDoc\Doc[] $docs */
        $docs = $parser->fromClass(TestClass::class);
        $this->assertCount(3, $docs);
        $this->assertEquals('Class TestClass.', $docs[TestClass::class]->getTitle());
        $this->assertCount(1, $docs[TestClass::class]->getTags());
        $this->assertCount(1, $docs[TestClass::class]->getTag('package'));
        $this->assertEquals('Berlioz\PhpDoc\Tests\files', $docs[TestClass::class]->getTag('package')[0]->getValue());
    }

    public function testFromReflectionFunction()
    {
        $parser = new Parser;

        $doc = $parser->fromReflectionFunction(new \ReflectionMethod(TestClass::class, 'method1'));
        $this->assertEquals('My method.', $doc->getTitle());
        $this->assertEquals("My description of method.\nAgain.", $doc->getDescription());
        $this->assertCount(2, $doc->getTags());
        $this->assertCount(3, $doc->getTag('param'));
        $this->assertEquals('string            $param1 Parameter 1', $doc->getTag('param')[0]->getValue());
        $this->assertEquals('int               $param2 Parameter 2', $doc->getTag('param')[1]->getValue());
        $this->assertEquals('\SimpleXMLElement $param3 Parameter 3', $doc->getTag('param')[2]->getValue());
    }

    public function testFromDocComment()
    {
        $parser = new Parser;

        $doc = $parser->fromDocComment(<<<'EOD'
/**
   *
 *   Function test.  
 *
 * @param string $param1
 * @param int    $param2
 *
 *
 * @return string
 *
 */
EOD
        );
        $this->assertEquals('Function test.', $doc->getTitle());
        $this->assertNull($doc->getDescription());
        $this->assertCount(2, $doc->getTags());
        $this->assertCount(2, $doc->getTag('param'));
        $this->assertEquals('string $param1', $doc->getTag('param')[0]->getValue());
        $this->assertEquals('int    $param2', $doc->getTag('param')[1]->getValue());
    }

    public function testFromDocComment_complex()
    {
        $parser = new Parser;

        $doc = $parser->fromDocComment(<<<'EOD'

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
EOD
        );
        $this->assertEquals('Test doc.', $doc->getTitle());
        $this->assertEquals("My description of my method.\nMulti-line.", $doc->getDescription());
        $this->assertCount(6, $doc->getTags());
    }
}
