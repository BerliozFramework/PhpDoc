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

use Berlioz\PhpDoc\Doc;
use Berlioz\PhpDoc\PhpDocFactory;
use Berlioz\PhpDoc\Parser;
use Berlioz\PhpDoc\Tests\files\TestClass;
use PHPUnit\Framework\TestCase;

class PhpDocFactoryTest extends TestCase
{
    public function testGetParser()
    {
        $factory = new PhpDocFactory;
        $parser = $factory->getParser();

        $this->assertInstanceOf(Parser::class, $parser);
        $this->assertEquals($parser, $factory->getParser());
    }

    public function testGetIndex()
    {
        $factory = new PhpDocFactory;
        $factory->getClassDocs(TestClass::class);
        $index = $factory->getIndex();

        $this->assertEquals(['Berlioz\PhpDoc\Tests\files\TestClass',
                             'Berlioz\PhpDoc\Tests\files\TestClass::method1',
                             'Berlioz\PhpDoc\Tests\files\TestClass::method2'],
                            $index);
    }

    public function testGetClassDocs()
    {
        $factory = new PhpDocFactory;
        /** @var \Berlioz\PhpDoc\Doc[] $docs */
        $docs = $factory->getClassDocs(TestClass::class);

        $this->assertCount(3, $docs);

        $this->assertArrayHasKey('Berlioz\PhpDoc\Tests\files\TestClass', $docs);
        $this->assertArrayHasKey('Berlioz\PhpDoc\Tests\files\TestClass::method1', $docs);
        $this->assertArrayHasKey('Berlioz\PhpDoc\Tests\files\TestClass::method2', $docs);

        $this->assertInstanceOf(Doc::class, $docs['Berlioz\PhpDoc\Tests\files\TestClass']);
        $this->assertInstanceOf(Doc::class, $docs['Berlioz\PhpDoc\Tests\files\TestClass::method1']);
        $this->assertInstanceOf(Doc::class, $docs['Berlioz\PhpDoc\Tests\files\TestClass::method2']);

        $this->assertEquals('Class TestClass.', $docs['Berlioz\PhpDoc\Tests\files\TestClass']->getTitle());
        $this->assertEquals('My method.', $docs['Berlioz\PhpDoc\Tests\files\TestClass::method1']->getTitle());
    }

    public function testGetClassDoc()
    {
        $factory = new PhpDocFactory;
        /** @var \Berlioz\PhpDoc\Doc $doc */
        $doc = $factory->getClassDoc(TestClass::class);

        $this->assertInstanceOf(Doc::class, $doc);
        $this->assertEquals('Class TestClass.', $doc->getTitle());
    }

    public function testGetMethodDoc()
    {
        $factory = new PhpDocFactory;
        /** @var \Berlioz\PhpDoc\Doc $docs */
        $doc = $factory->getMethodDoc(TestClass::class, 'method1');

        $this->assertInstanceOf(Doc::class, $doc);
        $this->assertEquals('My method.', $doc->getTitle());
    }

    public function testGetFunctionDoc()
    {
        require_once __DIR__ . '/files/function.php';

        $factory = new PhpDocFactory;
        /** @var \Berlioz\PhpDoc\Doc $docs */
        $doc = $factory->getFunctionDoc('test');

        $this->assertInstanceOf(Doc::class, $doc);
        $this->assertEquals('Function test.', $doc->getTitle());
    }
}
