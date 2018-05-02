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

namespace Berlioz\PhpDoc;

use Berlioz\PhpDoc\DocBlock\ClassDocBlock;
use Berlioz\PhpDoc\DocBlock\FunctionDocBlock;
use Berlioz\PhpDoc\DocBlock\MethodDocBlock;
use Berlioz\PhpDoc\DocBlock\PropertyDocBlock;
use Berlioz\PhpDoc\Exception\PhpDocException;
use Psr\Log\LoggerInterface;
use Psr\Log\LogLevel;
use Psr\SimpleCache\CacheInterface;

class PhpDocFactory
{
    // Filters
    const FILTER_INTERNAL = 1;
    const FILTER_USER_DEFINED = 2;
    const FILTER_METHOD_PRIVATE = 4;
    const FILTER_METHOD_PROTECTED = 8;
    const FILTER_METHOD_PUBLIC = 16;
    const FILTER_METHOD_ABSTRACT = 32;
    const FILTER_METHOD_FINAL = 64;
    const FILTER_METHOD_STATIC = 128;
    const FILTER_METHOD_CONSTRUCTOR = 256;
    const FILTER_METHOD_DESTRUCTOR = 512;
    // Cache
    const CACHE_KEY_INDEX = '_BERLIOZ_PHPDOC';
    /** @var \Psr\SimpleCache\CacheInterface|null Cache */
    private $cacheManager;
    /** @var \Psr\Log\LoggerInterface|null Logger */
    private $logger;
    /** @var \Berlioz\PhpDoc\Parser Parser */
    private $parser;
    /** @var DocBlock[] Cache */
    private $_docs;
    /** @var string[] Cache index */
    private $_index;

    /**
     * PhpDocFactory constructor.
     *
     * @param null|\Psr\SimpleCache\CacheInterface $cacheManager
     * @param null|\Psr\Log\LoggerInterface        $logger
     */
    public function __construct(?CacheInterface $cacheManager = null, ?LoggerInterface $logger = null)
    {
        $this->cacheManager = $cacheManager;
        $this->logger = $logger;
        $this->_docs = [];
    }

    /**
     * PhpDocFactory destructor.
     *
     * @throws \Psr\SimpleCache\InvalidArgumentException
     */
    public function __destruct()
    {
        $this->saveIndex();
    }

    /**
     * __sleep() PHP magic method.
     *
     * @return array
     */
    public function __sleep()
    {
        return [];
    }

    /**
     * Logs with an arbitrary level.
     *
     * @param mixed  $level
     * @param string $message
     * @param array  $context
     *
     * @return void
     */
    private function log($level, string $message, array $context = [])
    {
        if (!is_null($this->logger)) {
            $this->logger->log($level,
                               sprintf('{class} / %s', $message),
                               array_merge($context, ['class' => __CLASS__]));
        }
    }

    /**
     * Get parser.
     *
     * @return \Berlioz\PhpDoc\Parser
     */
    public function getParser(): Parser
    {
        if (is_null($this->parser)) {
            $this->parser = new Parser;
        }

        return $this->parser;
    }

    /**
     * Get index of docs.
     *
     * @return array
     * @throws \Psr\SimpleCache\InvalidArgumentException
     */
    public function getIndex(): array
    {
        if (is_null($this->_index)) {
            $this->_index = [];

            if (!is_null($this->cacheManager)) {
                if (!is_array($this->_index = $this->cacheManager->get(sprintf('%s_INDEX', static::CACHE_KEY_INDEX), []))) {
                    $this->_index = [];

                    // Log
                    $this->log(LogLevel::WARNING, 'Not valid index from cache');
                } else {
                    // Log
                    $this->log(LogLevel::DEBUG, 'Get index from cache');
                }
            }
        }

        return $this->_index;
    }

    /**
     * Save index in cache.
     *
     * @return \Berlioz\PhpDoc\PhpDocFactory
     * @throws \Psr\SimpleCache\InvalidArgumentException
     */
    private function saveIndex(): PhpDocFactory
    {
        if (!is_null($this->cacheManager)) {
            $this->cacheManager->set(sprintf('%s_INDEX', static::CACHE_KEY_INDEX), $this->_index);

            // Log
            $this->log(LogLevel::DEBUG, 'Save index in cache');
        }

        return $this;
    }

    /**
     * Get name of doc in cache.
     *
     * @param string $name
     *
     * @return string
     */
    private function getDocCacheName(string $name): string
    {
        return sprintf('%s_%s', static::CACHE_KEY_INDEX, $name);
    }

    /**
     * Get doc from cache.
     *
     * @param string $name
     *
     * @return \Berlioz\PhpDoc\DocBlock|null
     * @throws \Psr\SimpleCache\CacheException
     */
    private function getDocFromCache(string $name): ?DocBlock
    {
        if (array_key_exists($name, $this->_docs)) {
            return $this->_docs[$name];
        }

        if (!is_null($this->cacheManager)) {
            $cacheKey = $this->getDocCacheName($name);
            if ($this->cacheManager->has($cacheKey)) {
                if (($doc = $this->cacheManager->get($cacheKey)) instanceof DocBlock) {
                    // Log
                    $this->log(LogLevel::DEBUG, sprintf('Get doc "%s" from cache', $name));

                    return $doc;
                }
            }
        }

        return null;
    }

    /**
     * Save doc in cache.
     *
     * @param string                        $name
     * @param \Berlioz\PhpDoc\DocBlock|null $value
     *
     * @return \Berlioz\PhpDoc\PhpDocFactory
     * @throws \Psr\SimpleCache\CacheException
     */
    private function saveDocToCache(string $name, ?DocBlock $value): PhpDocFactory
    {
        if (!is_null($this->cacheManager)) {
            $cacheKey = $this->getDocCacheName($name);

            if (!is_null($value)) {
                $this->cacheManager->set($cacheKey, $value);

                // Log
                $this->log(LogLevel::DEBUG, sprintf('Save doc "%s" in cache', $name));
            } else {
                $this->cacheManager->has($cacheKey) && $this->cacheManager->delete($cacheKey);
            }
        }

        // Save doc in index
        if (!in_array($name, $this->getIndex())) {
            $this->_index[] = $name;
        }

        $this->_docs[$name] = $value;

        return $this;
    }


    ///////////////////////////////////////////////////////////////////////////
    ///                GETTERS TO CONSTRUCT DOC BLOCKS CLASSES                ///
    ///////////////////////////////////////////////////////////////////////////

    /**
     * Get function PhpDoc.
     *
     * @param string $function Function name
     *
     * @return \Berlioz\PhpDoc\DocBlock\FunctionDocBlock
     * @throws \Berlioz\PhpDoc\Exception\PhpDocException
     * @throws \Psr\SimpleCache\CacheException
     */
    public function getFunctionDoc(string $function): FunctionDocBlock
    {
        // Get from cache
        if (($doc = $this->getDocFromCache($function)) === false || !($doc instanceof FunctionDocBlock)) {
            try {
                $reflection = new \ReflectionFunction($function);
            } catch (\Exception $e) {
                throw new PhpDocException(sprintf('Unable to do reflection of function "%s"', $function));
            }
            $doc = $this->getFromReflection($reflection);
        }

        return $doc;
    }

    /**
     * Get class PhpDoc.
     *
     * @param string $class Class name
     *
     * @return \Berlioz\PhpDoc\DocBlock\ClassDocBlock
     * @throws \Berlioz\PhpDoc\Exception\PhpDocException
     * @throws \Psr\SimpleCache\CacheException
     */
    public function getClassDoc(string $class): ClassDocBlock
    {
        // Get from cache
        if (($doc = $this->getDocFromCache($class)) === false || !($doc instanceof ClassDocBlock)) {
            try {
                $reflection = new \ReflectionClass($class);
            } catch (\Exception $e) {
                throw new PhpDocException(sprintf('Unable to do reflection of class "%s"', $class));
            }
            $doc = $this->getFromReflection($reflection);

            // Get all properties
            foreach ($reflection->getProperties() as $reflectionProperty) {
                $this->getFromReflection($reflectionProperty);
            }

            // Get all methods
            foreach ($reflection->getMethods() as $reflectionMethod) {
                $this->getFromReflection($reflectionMethod);
            }
        }

        return $doc;
    }

    /**
     * Get property PhpDoc.
     *
     * @param string $class    Class name
     * @param string $property Property name
     *
     * @return \Berlioz\PhpDoc\DocBlock\PropertyDocBlock
     * @throws \Berlioz\PhpDoc\Exception\PhpDocException
     * @throws \Psr\SimpleCache\CacheException
     */
    public function getPropertyDoc(string $class, string $property): PropertyDocBlock
    {
        $fullName = sprintf('%s::$%s', $class, $property);

        // Get from cache
        if (($doc = $this->getDocFromCache($fullName)) === false || !($doc instanceof PropertyDocBlock)) {
            try {
                $reflection = new \ReflectionProperty($class, $property);
            } catch (\Exception $e) {
                throw new PhpDocException(sprintf('Unable to do reflection of property "%s"', $fullName));
            }
            $doc = $this->getFromReflection($reflection);
        }

        return $doc;
    }

    /**
     * Get method PhpDoc.
     *
     * @param string $class  Class name
     * @param string $method Method name
     *
     * @return \Berlioz\PhpDoc\DocBlock\MethodDocBlock
     * @throws \Berlioz\PhpDoc\Exception\PhpDocException
     * @throws \Psr\SimpleCache\CacheException
     */
    public function getMethodDoc(string $class, string $method): MethodDocBlock
    {
        $fullName = sprintf('%s::%s', $class, $method);

        // Get from cache
        if (($doc = $this->getDocFromCache($fullName)) === false || !($doc instanceof MethodDocBlock)) {
            try {
                $reflection = new \ReflectionMethod($class, $method);
            } catch (\Exception $e) {
                throw new PhpDocException(sprintf('Unable to do reflection of method "%s"', $fullName));
            }
            $doc = $this->getFromReflection($reflection);
        }

        return $doc;
    }

    /**
     * Get docs of a class and its methods.
     *
     * @param string $class Class name
     *
     * @return \Berlioz\PhpDoc\DocBlock[]
     * @throws \Berlioz\PhpDoc\Exception\PhpDocException
     * @throws \Psr\SimpleCache\CacheException
     */
    public function getClassDocs(string $class): array
    {
        $classDoc = $this->getClassDoc($class);
        $classNameLength = mb_strlen($classDoc->getName());
        $docs = [$classDoc->getName() => $classDoc];

        foreach ($this->getIndex() as $indexEntry) {
            if (sprintf('%s::', $classDoc->getName()) == substr($indexEntry, 0, $classNameLength + 2)) {
                $docs[$indexEntry] = $this->getDocFromCache($indexEntry);
            }
        }

        return $docs;
    }

    /**
     * Get PhpDoc from reflection.
     *
     * @param \Reflector $reflection
     *
     * @return \Berlioz\PhpDoc\DocBlock
     * @throws \Berlioz\PhpDoc\Exception\PhpDocException
     * @throws \Psr\SimpleCache\CacheException
     */
    public function getFromReflection(\Reflector $reflection): DocBlock
    {
        $reflectionClass = get_class($reflection);
        $docBlockClass = null;

        switch ($reflectionClass) {
            case \ReflectionFunction::class:
                $docBlockClass = FunctionDocBlock::class;
                /** @var \ReflectionFunction $reflection */
                $name = sprintf('%s\%s', $reflection->getNamespaceName(), $reflection->getName());
                break;
            case \ReflectionClass::class:
                $docBlockClass = ClassDocBlock::class;
                /** @var \ReflectionClass $reflection */
                $name = $reflection->getName();
                break;
            case \ReflectionProperty::class:
                $docBlockClass = PropertyDocBlock::class;
                /** @var \ReflectionMethod $reflection */
                $name = sprintf('%s::$%s', $reflection->class, $reflection->getName());
                break;
            case \ReflectionMethod::class:
                $docBlockClass = MethodDocBlock::class;
                /** @var \ReflectionProperty $reflection */
                $name = sprintf('%s::%s', $reflection->class, $reflection->getName());
                break;
            default:
                throw new PhpDocException(sprintf('Unable to treat "%s" reflection class', $reflectionClass));
        }

        // Get from cache
        if (($doc = $this->getDocFromCache($name)) === false || !($doc instanceof DocBlock)) {
            // Get doc comment
            $docComment = $reflection->getDocComment();

            // Parse doc comment
            $docCommentParsed = $this->getParser()->parse($docComment);

            // Group tags
            $tags = [];
            /** @var \Berlioz\PhpDoc\Tag $tag */
            foreach ($docCommentParsed['tags'] as $tag) {
                $tags[$tag->getName()][] = $tag;
            }

            // Create DocBlock class
            $doc = new $docBlockClass($reflection, $docCommentParsed['title'], $docCommentParsed['description'], $tags);

            // Save to cache
            $this->saveDocToCache($name, $doc);
        }

        return $doc;
    }
}