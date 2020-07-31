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

use Berlioz\PhpDoc\DocBlock\ClassDocBlock;
use Berlioz\PhpDoc\DocBlock\FunctionDocBlock;
use Berlioz\PhpDoc\DocBlock\MethodDocBlock;
use Berlioz\PhpDoc\DocBlock\PropertyDocBlock;
use Berlioz\PhpDoc\Parser;
use Berlioz\PhpDoc\PhpDocFactory;
use Berlioz\PhpDoc\Tag\ParamTag;
use Berlioz\PhpDoc\Tag\ReturnTag;
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
                             'Berlioz\PhpDoc\Tests\files\TestClass::$property1',
                             'Berlioz\PhpDoc\Tests\files\TestClass::$property2',
                             'Berlioz\PhpDoc\Tests\files\TestClass::__construct',
                             'Berlioz\PhpDoc\Tests\files\TestClass::method1',
                             'Berlioz\PhpDoc\Tests\files\TestClass::method2'],
                            $index);
    }

    public function testGetFunctionDoc()
    {
        require_once __DIR__ . '/files/function.php';
        require_once __DIR__ . '/files/function2.php';

        $factory = new PhpDocFactory;

        $doc = $factory->getFunctionDoc('test');
        $this->assertInstanceOf(FunctionDocBlock::class, $doc);
        $this->assertEquals('Function test.', $doc->getTitle());
        $this->assertEquals('\test', $doc->getName());
        $this->assertFalse($doc->isClosure());
        $this->assertFalse($doc->isDeprecated());
        $this->assertFalse($doc->isGenerator());
        $this->assertFalse($doc->isInternal());
        $this->assertTrue($doc->isUserDefined());
        $this->assertFalse($doc->isVariatic());

        $doc = $factory->getFunctionDoc('\Berlioz\PhpDoc\Tests\files\test');
        $this->assertInstanceOf(FunctionDocBlock::class, $doc);
        $this->assertEquals('Function test 2.', $doc->getTitle());
        $this->assertEquals('Berlioz\PhpDoc\Tests\files\test', $doc->getName());
        $this->assertEquals('Berlioz\PhpDoc\Tests\files', $doc->getNamespaceName());
        $this->assertEquals('test', $doc->getShortName());
        $this->assertFalse($doc->isClosure());
        $this->assertFalse($doc->isDeprecated());
        $this->assertFalse($doc->isGenerator());
        $this->assertFalse($doc->isInternal());
        $this->assertTrue($doc->isUserDefined());
        $this->assertFalse($doc->isVariatic());
    }

    public function testGetMethodDoc()
    {
        $factory = new PhpDocFactory;
        $doc = $factory->getMethodDoc(TestClass::class, 'method1');
        $this->assertInstanceOf(MethodDocBlock::class, $doc);
        $this->assertEquals('My method.', $doc->getTitle());
        $this->assertEquals('Berlioz\PhpDoc\Tests\files\TestClass', $doc->getClassName());
        $this->assertTrue($doc->isPublic());
        $this->assertFalse($doc->isPrivate());
        $this->assertFalse($doc->isProtected());
        $this->assertFalse($doc->isAbstract());
        $this->assertFalse($doc->isConstructor());
        $this->assertFalse($doc->isDestructor());
        $this->assertFalse($doc->isStatic());
        $this->assertFalse($doc->isFinal());

        /** @var \Berlioz\PhpDoc\Tag\ParamTag[] $tags */
        $this->assertCount(3, $tags = $doc->getTag('param'));
        $this->assertInstanceOf(ParamTag::class, $tags[0]);
        $this->assertEquals('Parameter 1', $tags[0]->getVarTitle());
        $this->assertEquals('string', $tags[0]->getVarType());
        $this->assertEquals('$param1', $tags[0]->getVarName());

        /** @var \Berlioz\PhpDoc\Tag\ReturnTag[] $tags */
        $this->assertCount(1, $tags = $doc->getTag('return'));
        $this->assertInstanceOf(ReturnTag::class, $tags[0]);
        $this->assertNull($tags[0]->getVarTitle());
        $this->assertEquals('int', $tags[0]->getVarType());
    }

    public function testGetClassDoc()
    {
        $factory = new PhpDocFactory;
        $doc = $factory->getClassDoc(TestClass::class);
        $this->assertInstanceOf(ClassDocBlock::class, $doc);
        $this->assertEquals('Class TestClass.', $doc->getTitle());
        $this->assertEquals('Berlioz\PhpDoc\Tests\files\TestClass', $doc->getName());
        $this->assertEquals('Berlioz\PhpDoc\Tests\files', $doc->getNamespaceName());
        $this->assertEquals('TestClass', $doc->getShortName());
        $this->assertFalse($doc->isAbstract());
        $this->assertFalse($doc->isFinal());
        $this->assertFalse($doc->isInternal());
        $this->assertTrue($doc->isUserDefined());
        $this->assertFalse($doc->isAnonymous());
        $this->assertTrue($doc->isCloneable());
        $this->assertTrue($doc->isInstantiable());
        $this->assertFalse($doc->isInterface());
        $this->assertFalse($doc->isIterable());
        $this->assertFalse($doc->isIterateable());
        $this->assertFalse($doc->isTrait());
    }

    public function testGetFromReflection()
    {
        $factory = new PhpDocFactory;
        $doc = $factory->getFromReflection(new \ReflectionMethod(TestClass::class, 'method1'));
        $this->assertEquals('My method.', $doc->getTitle());
        $this->assertEquals("My description of method.\nAgain.", $doc->getDescription());
        $this->assertCount(2, $doc->getTags());
        $this->assertCount(3, $doc->getTag('param'));
        $this->assertEquals('string            $param1 Parameter 1', $doc->getTag('param')[0]->getValue());
        $this->assertEquals('int               $param2 Parameter 2', $doc->getTag('param')[1]->getValue());
        $this->assertEquals('\SimpleXMLElement $param3 Parameter 3', $doc->getTag('param')[2]->getValue());
    }

    public function testGetPropertyDoc()
    {
        $factory = new PhpDocFactory;
        $doc = $factory->getPropertyDoc(TestClass::class, 'property1');
        $this->assertInstanceOf(PropertyDocBlock::class, $doc);
        $this->assertEquals('Property 1.', $doc->getTitle());
        $this->assertEquals('Berlioz\PhpDoc\Tests\files\TestClass::$property1', $doc->getName());
        $this->assertEquals('Berlioz\PhpDoc\Tests\files\TestClass', $doc->getClassName());
        $this->assertEquals('Berlioz\PhpDoc\Tests\files', $doc->getNamespaceName());
        $this->assertEquals('property1', $doc->getShortName());
        $this->assertFalse($doc->isStatic());
        $this->assertFalse($doc->isPublic());
        $this->assertFalse($doc->isProtected());
        $this->assertTrue($doc->isPrivate());
        $this->assertTrue($doc->isDefault());
    }

    public function testGetClassDocs()
    {
        $factory = new PhpDocFactory;
        /** @var \Berlioz\PhpDoc\DocBlock[] $docs */
        $docs = $factory->getClassDocs(TestClass::class);

        $this->assertCount(6, $docs);
        $this->assertArrayHasKey('Berlioz\PhpDoc\Tests\files\TestClass', $docs);
        $this->assertArrayHasKey('Berlioz\PhpDoc\Tests\files\TestClass::__construct', $docs);
        $this->assertArrayHasKey('Berlioz\PhpDoc\Tests\files\TestClass::$property1', $docs);
        $this->assertArrayHasKey('Berlioz\PhpDoc\Tests\files\TestClass::$property2', $docs);
        $this->assertArrayHasKey('Berlioz\PhpDoc\Tests\files\TestClass::method1', $docs);
        $this->assertArrayHasKey('Berlioz\PhpDoc\Tests\files\TestClass::method2', $docs);
    }
}
