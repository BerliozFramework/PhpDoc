# Berlioz PhpDoc

[![Latest Version](https://img.shields.io/packagist/v/berlioz/php-doc.svg?style=flat-square)](https://github.com/BerliozFramework/PhpDoc/releases)
[![Software license](https://img.shields.io/github/license/BerliozFramework/PhpDoc.svg?style=flat-square)](https://github.com/BerliozFramework/PhpDoc/blob/master/LICENSE)
[![Build Status](https://img.shields.io/travis/com/BerliozFramework/PhpDoc/master.svg?style=flat-square)](https://travis-ci.com/BerliozFramework/PhpDoc)
[![Quality Grade](https://img.shields.io/codacy/grade/ffd9c7991dae48f5bddb701d44285e76/master.svg?style=flat-square)](https://www.codacy.com/manual/BerliozFramework/PhpDoc)
[![Total Downloads](https://img.shields.io/packagist/dt/berlioz/php-doc.svg?style=flat-square)](https://packagist.org/packages/berlioz/php-doc)

**Berlioz PhpDoc** is a PHP library to read the documentations in code (classes, methods and functions) with advanced annotation interpretation.

## Installation

### Composer

You can install **Berlioz PhpDoc** with [Composer](https://getcomposer.org/), it's the recommended installation.

```bash
$ composer require berlioz/php-doc
```

### Dependencies

* **PHP** ^7.1 || ^8.0
* Packages:
  * **psr/simple-cache**
  * **psr/log**


## Usage

### Basic

```php
$phpDocFactory = new PhpDocFactory;

// To get class PhpDoc
$doc = $phpDocFactory->getClassDoc(ClassOfMyProject::class);

// To get class property PhpDoc
$doc = $phpDocFactory->getPropertyDoc(ClassOfMyProject::class, 'myProperty');

// To get class method PhpDoc
$doc = $phpDocFactory->getMethodDoc(ClassOfMyProject::class, 'myMethod');

// To get function PhpDoc
$doc = $phpDocFactory->getFunctionDoc('myFunction');
```

### Cache

The library supports PSR-16 (Common Interface for Caching Libraries).

To use it, you need to pass the cache manager in first argument of `PhpDocFactory` class.

```php
$simpleCacheManager = new MySimpleCacheManager;

$phpDocFactory = new PhpDocFactory($simpleCacheManager);
```

So you do disk i/o economies and your application will be faster than if you don't use cache manager.


## Berlioz\PhpDoc\DocBlock class

A `DocBlock` class or an array of them are returned when you call these methods of factory:
- `PhpDocFactory::getClassDocs()` returns an array of `Berlioz\PhpDoc\DocBlock`
- `PhpDocFactory::getClassDoc()` returns a `Berlioz\PhpDoc\DocBlock\ClassDocBlock` object
- `PhpDocFactory::getPropertyDoc()` returns a `Berlioz\PhpDoc\DocBlock\PropertyDocBlock` object
- `PhpDocFactory::getMethodDoc()` returns a `Berlioz\PhpDoc\DocBlock\MethodDocBlock` object
- `PhpDocFactory::getFunctionDoc()` returns a `Berlioz\PhpDoc\DocBlock\FunctionDocBlock` object

Some methods are available with `DocBlock` object:
- `DocBlock::getTitle()` returns the title part in the PhpDoc
- `DocBlock::getDescription()` returns the description part in the PhpDoc
- `DocBlock::getTags()` returns all tags presents in the PhpDoc
- `DocBlock::getTag()` returns a tag present in the PhpDoc
- `DocBlock::hasTag()` returns if a tag is present in the PhpDoc

Additional methods are available with extended `DocBlock`class:
- `Berlioz\PhpDoc\DocBlock\FunctionDocBlock`:
  - `FunctionDocBlock::getName()`: returns the full name of function
  - `FunctionDocBlock::getShortName()`: returns the short name of function
  - `FunctionDocBlock::getNamespaceName()`: returns the namespace name of function
  - `FunctionDocBlock::getClassName()`: returns the name of class
  - `FunctionDocBlock::isDisabled()`: known if function is disabled
  - `FunctionDocBlock::isUserDefined()`: known if it's user defined function
  - `FunctionDocBlock::isInternal()`: known if function is internal
  - `FunctionDocBlock::isClosure()`: known if function is a closure
  - `FunctionDocBlock::isDeprecated()`: known if function is deprecated
  - `FunctionDocBlock::isGenerator()`: known if function is generator
  - `FunctionDocBlock::isVariatic()`: known if function is variatic
- `Berlioz\PhpDoc\DocBlock\ClassDocBlock`:
  - `ClassDocBlock::getName()`: returns the full name of class
  - `ClassDocBlock::getShortName()`: returns the short name of class
  - `ClassDocBlock::getNamespaceName()`: returns the namespace name of class
  - `ClassDocBlock::isAbstract()`: known if class is abstract
  - `ClassDocBlock::isFinal()`: known if class is final
  - `ClassDocBlock::isInternal()`: known if class is internal
  - `ClassDocBlock::isUserDefined()`: known if it's user defined class
  - `ClassDocBlock::isAnonymous()`: known if class is anonymous
  - `ClassDocBlock::isCloneable()`: known if class is cloneable
  - `ClassDocBlock::isInstantiable()`: known if class is instantiable
  - `ClassDocBlock::isInterface()`: known if class is an interface
  - `ClassDocBlock::isIterable()`: known if class is iterable
  - `ClassDocBlock::isIterateable()`: known if class is iterateable
  - `ClassDocBlock::isTrait()`: known if class is a trait
- `Berlioz\PhpDoc\DocBlock\PropertyDocBlock`:
  - `PropertyDocBlock::getName()`: returns the full name of property
  - `PropertyDocBlock::getShortName()`: returns the short name of property
  - `PropertyDocBlock::getNamespaceName()`: returns the namespace name of class
  - `PropertyDocBlock::getClassName()`: returns the name of class
  - `PropertyDocBlock::isPublic()`: known if property has public visibility
  - `PropertyDocBlock::isProtected()`: known if property has protected visibility
  - `PropertyDocBlock::isPrivate()`: known if property has private visibility
  - `PropertyDocBlock::isStatic()`: known if property is static
  - `PropertyDocBlock::isDefault()`: known if property is default
- `Berlioz\PhpDoc\DocBlock\MethodDocBlock`:
  - `MethodDocBlock::getName()`: returns the full name of method
  - `MethodDocBlock::getShortName()`: returns the short name of method
  - `MethodDocBlock::getNamespaceName()`: returns the namespace name of class
  - `MethodDocBlock::getClassName()`: returns the name of class
  - `MethodDocBlock::isConstructor()`: known if method is constructor
  - `MethodDocBlock::isDestructor()`: known if method is destructor
  - `MethodDocBlock::isPublic()`: known if method has public visibility
  - `MethodDocBlock::isProtected()`: known if method has protected visibility
  - `MethodDocBlock::isPrivate()`: known if method has private visibility
  - `MethodDocBlock::isStatic()`: known if method is static
  - `MethodDocBlock::isAbstract()`: known if method is abstract
  - `MethodDocBlock::isFinal()`: known if method is final
  - `MethodDocBlock::isUserDefined()`: known if it's user defined method
  - `MethodDocBlock::isInternal()`: known if method is internal
  - `MethodDocBlock::isClosure()`: known if method is a closure
  - `MethodDocBlock::isDeprecated()`: known if method is deprecated
  - `MethodDocBlock::isGenerator()`: known if method is generator
  - `MethodDocBlock::isVariatic()`: known if method is variatic

## Tags

### Formats

Some tags formats are supported by library and the value returned by `DocBlock` class is a PHP code and not string.

Example:
```php
/**
 * Test doc.
 *
 * My description of my method.
 * Multi-line.
 *
 * @test false
 * @novalue
 * @value Only text
 * @test2("test", param1=true, param2="test", param3={"test":"test"})
 * @value Second text
 * @jsonTest {"test":"test"}
 * @jsonArrayTest [{"test":"test"}, {"test2":"test2"}]
 */
```

The result of `DocBlock::getTags()` method is:
```
array(6) {
  ["test"]=>
  array(1) {
    [0]=>
    object(Berlioz\PhpDoc\Tag) (2) {
      ["name":"Berlioz\PhpDoc\Tag":private]=>
      string(4) "test"
      ["value":"Berlioz\PhpDoc\Tag":private]=>
      bool(false)
    }
  }
  ["novalue"]=>
  array(1) {
    [0]=>
    object(Berlioz\PhpDoc\Tag) (2) {
      ["name":"Berlioz\PhpDoc\Tag":private]=>
      string(7) "novalue"
      ["value":"Berlioz\PhpDoc\Tag":private]=>
      NULL
    }
  }
  ["value"]=>
  array(2) {
    [0]=>
    object(Berlioz\PhpDoc\Tag) (2) {
      ["name":"Berlioz\PhpDoc\Tag":private]=>
      string(5) "value"
      ["value":"Berlioz\PhpDoc\Tag":private]=>
      string(9) "Only text"
    }
    [1]=>
    object(Berlioz\PhpDoc\Tag) (2) {
      ["name":"Berlioz\PhpDoc\Tag":private]=>
      string(5) "value"
      ["value":"Berlioz\PhpDoc\Tag":private]=>
      string(11) "Second text"
    }
  }
  ["test2"]=>
  array(1) {
    [0]=>
    object(Berlioz\PhpDoc\Tag) (2) {
      ["name":"Berlioz\PhpDoc\Tag":private]=>
      string(5) "test2"
      ["value":"Berlioz\PhpDoc\Tag":private]=>
      array(4) {
        [0]=>
        string(4) "test"
        ["param1"]=>
        bool(true)
        ["param2"]=>
        string(4) "test"
        ["param3"]=>
        object(stdClass) (1) {
          ["test"]=>
          string(4) "test"
        }
      }
    }
  }
  ["jsonTest"]=>
  array(1) {
    [0]=>
    object(Berlioz\PhpDoc\Tag) (2) {
      ["name":"Berlioz\PhpDoc\Tag":private]=>
      string(8) "jsonTest"
      ["value":"Berlioz\PhpDoc\Tag":private]=>
      object(stdClass) (1) {
        ["test"]=>
        string(4) "test"
      }
    }
  }
  ["jsonArrayTest"]=>
  array(1) {
    [0]=>
    object(Berlioz\PhpDoc\Tag) (2) {
      ["name":"Berlioz\PhpDoc\Tag":private]=>
      string(13) "jsonArrayTest"
      ["value":"Berlioz\PhpDoc\Tag":private]=>
      array(2) {
        [0]=>
        object(stdClass) (1) {
          ["test"]=>
          string(4) "test"
        }
        [1]=>
        object(stdClass) (1) {
          ["test2"]=>
          string(5) "test2"
        }
      }
    }
  }
}
```

### Available tags

Some tags are available by default:
- `Berlioz\PhpDoc\Tag\ParamTag`
- `Berlioz\PhpDoc\Tag\ReturnTag`
- `Berlioz\PhpDoc\Tag\VarTag`

### Extends

You can extends tags and declare them to the parser.
Yours tags must implements TagInterface interface.

```php
$phpDocFactory = new PhpDocFactory;
$phpDocFactory->getParser()->addTagClass('tagName', MyTagClass::class);
```
