# Berlioz PhpDoc

**Berlioz PhpDoc** is a PHP library to read the documentations in code (classes, methods and functions) with advanced annotation interpretation.

## Installation

### Composer

You can install **Berlioz PhpDoc** with [Composer](https://getcomposer.org/), it's the recommended installation.

```bash
$ composer require berlioz/php-doc
```

### Dependencies

* **PHP** >= 7.1
* Packages:
  * **psr/simple-cache**
  * **psr/log**


## Usage

### Basic

```php
$generator = new Generator;

// To get class PhpDoc
$doc = $generator->getClassDoc(ClassOfMyProject::class);

// To get class method PhpDoc
$doc = $generator->getMethodDoc(ClassOfMyProject::class, 'myMethod');

// To get function PhpDoc
$doc = $generator->getMethodDoc('myFunction');
```

### Cache

The library supports PSR-16 (Common Interface for Caching Libraries).

To use it, you need to pass the cache manager in first argument of `Generator` class.

```php
$simpleCacheManager = new MySimpleCacheManager;

$generator = new Generator($simpleCacheManager);
```

So you do disk i/o economies and your application will be faster than if you don't use cache manager.


## Berlioz\PhpDoc\Doc class

A `Doc` class or an array of them are returned when you call these methods of generator:
- `Generator::getClassDocs()` returns an array of `Berlioz\PhpDoc\Doc`
- `Generator::getClassDoc()` returns a `Berlioz\PhpDoc\Doc` object
- `Generator::getMethodDoc()` returns a `Berlioz\PhpDoc\Doc` object
- `Generator::getFunctionDoc()` returns a `Berlioz\PhpDoc\Doc` object

Some methods are available with `Doc` object:
- `Doc::getTitle()` returns the title part in the PhpDoc
- `Doc::getDescription()` returns the description part in the PhpDoc
- `Doc::getTags()` returns all tags presents in the PhpDoc
- `Doc::getTag()` returns a tag present in the PhpDoc
- `Doc::hasTag()` returns if a tag is present in the PhpDoc
- `Doc::getParentName()` returns the parent name of PhpDoc (class name, method name or function name)


## Tags

### Formats

Some tags formats are supported by library and the value returned by `Doc` class is a PHP code and not string.

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

The result of `Doc::getTags()` method is:
```
array(6) {
  ["test"]=>
  array(1) {
    [0]=>
    object(Berlioz\PhpDoc\Tag)#333 (2) {
      ["name":"Berlioz\PhpDoc\Tag":private]=>
      string(4) "test"
      ["value":"Berlioz\PhpDoc\Tag":private]=>
      bool(false)
    }
  }
  ["novalue"]=>
  array(1) {
    [0]=>
    object(Berlioz\PhpDoc\Tag)#322 (2) {
      ["name":"Berlioz\PhpDoc\Tag":private]=>
      string(7) "novalue"
      ["value":"Berlioz\PhpDoc\Tag":private]=>
      NULL
    }
  }
  ["value"]=>
  array(2) {
    [0]=>
    object(Berlioz\PhpDoc\Tag)#323 (2) {
      ["name":"Berlioz\PhpDoc\Tag":private]=>
      string(5) "value"
      ["value":"Berlioz\PhpDoc\Tag":private]=>
      string(9) "Only text"
    }
    [1]=>
    object(Berlioz\PhpDoc\Tag)#335 (2) {
      ["name":"Berlioz\PhpDoc\Tag":private]=>
      string(5) "value"
      ["value":"Berlioz\PhpDoc\Tag":private]=>
      string(11) "Second text"
    }
  }
  ["test2"]=>
  array(1) {
    [0]=>
    object(Berlioz\PhpDoc\Tag)#332 (2) {
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
        object(stdClass)#329 (1) {
          ["test"]=>
          string(4) "test"
        }
      }
    }
  }
  ["jsonTest"]=>
  array(1) {
    [0]=>
    object(Berlioz\PhpDoc\Tag)#334 (2) {
      ["name":"Berlioz\PhpDoc\Tag":private]=>
      string(8) "jsonTest"
      ["value":"Berlioz\PhpDoc\Tag":private]=>
      object(stdClass)#330 (1) {
        ["test"]=>
        string(4) "test"
      }
    }
  }
  ["jsonArrayTest"]=>
  array(1) {
    [0]=>
    object(Berlioz\PhpDoc\Tag)#325 (2) {
      ["name":"Berlioz\PhpDoc\Tag":private]=>
      string(13) "jsonArrayTest"
      ["value":"Berlioz\PhpDoc\Tag":private]=>
      array(2) {
        [0]=>
        object(stdClass)#324 (1) {
          ["test"]=>
          string(4) "test"
        }
        [1]=>
        object(stdClass)#309 (1) {
          ["test2"]=>
          string(5) "test2"
        }
      }
    }
  }
}
```

### Extends

You can extends tags and declare them to the parser.
Yours tags must implements TagInterface interface.

```php
$generator = new Gernetor;
$generator->getParser()->addTagClass('tagName', MyTagClass::class);
```
