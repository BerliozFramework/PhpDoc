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

declare(strict_types=1);

namespace Berlioz\PhpDoc;

use Berlioz\PhpDoc\DocBlock\ClassDocBlock;
use Berlioz\PhpDoc\DocBlock\FunctionDocBlock;
use Berlioz\PhpDoc\DocBlock\MethodDocBlock;
use Berlioz\PhpDoc\DocBlock\PropertyDocBlock;
use Berlioz\PhpDoc\Exception\PhpDocException;
use Exception;
use Psr\Log\LoggerInterface;
use Psr\Log\LogLevel;
use Psr\SimpleCache\CacheInterface;
use ReflectionClass;
use ReflectionFunction;
use ReflectionMethod;
use ReflectionProperty;
use Reflector;

/**
 * Class PhpDocFactory.
 *
 * @package Berlioz\PhpDoc
 */
class PhpDocFactory
{
    // Cache
    protected const CACHE_KEY_INDEX = '_BERLIOZ_PHPDOC';
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
     * @param null|\Psr\Log\LoggerInterface $logger
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
     * @param mixed $level
     * @param string $message
     * @param array $context
     *
     * @return void
     */
    private function log($level, string $message, array $context = [])
    {
        if (null === $this->logger) {
            return;
        }

        $this->logger->log(
            $level,
            sprintf('{class} / %s', $message),
            array_merge($context, ['class' => __CLASS__])
        );
    }

    /**
     * Get parser.
     *
     * @return \Berlioz\PhpDoc\Parser
     */
    public function getParser(): Parser
    {
        if (null === $this->parser) {
            $this->parser = new Parser();
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
        if (null !== $this->_index) {
            return $this->_index;
        }

        if (null === $this->cacheManager) {
            return [];
        }

        // Log
        $this->log(LogLevel::DEBUG, 'Get index from cache');

        $this->_index = $this->cacheManager->get(sprintf('%s_INDEX', static::CACHE_KEY_INDEX), []);

        if (!is_array($this->_index)) {
            $this->_index = [];

            // Log
            $this->log(LogLevel::WARNING, 'Not valid index from cache');
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
        if (null === $this->cacheManager) {
            return $this;
        }

        $this->cacheManager->set(sprintf('%s_INDEX', static::CACHE_KEY_INDEX), $this->_index);

        // Log
        $this->log(LogLevel::DEBUG, 'Save index in cache');

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

        if (null === $this->cacheManager) {
            return null;
        }

        $cacheKey = $this->getDocCacheName($name);
        if (!$this->cacheManager->has($cacheKey)) {
            return null;
        }

        if (($doc = $this->cacheManager->get($cacheKey)) instanceof DocBlock) {
            // Log
            $this->log(LogLevel::DEBUG, sprintf('Get doc "%s" from cache', $name));

            return $doc;
        }

        return null;
    }

    /**
     * Save doc in cache.
     *
     * @param string $name
     * @param \Berlioz\PhpDoc\DocBlock|null $value
     *
     * @return \Berlioz\PhpDoc\PhpDocFactory
     * @throws \Psr\SimpleCache\CacheException
     */
    private function saveDocToCache(string $name, ?DocBlock $value): PhpDocFactory
    {
        if (null !== $this->cacheManager) {
            $cacheKey = $this->getDocCacheName($name);

            if (null === $value) {
                $this->cacheManager->has($cacheKey) && $this->cacheManager->delete($cacheKey);
            }

            if (null !== $value) {
                $this->cacheManager->set($cacheKey, $value);

                // Log
                $this->log(LogLevel::DEBUG, sprintf('Save doc "%s" in cache', $name));
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
    ///                GETTERS TO CONSTRUCT DOC BLOCKS CLASSES              ///
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
        if (null !== ($doc = $this->getDocFromCache($function))) {
            if ($doc instanceof FunctionDocBlock) {
                return $doc;
            }
        }

        try {
            $reflection = new ReflectionFunction($function);
        } catch (Exception $e) {
            throw new PhpDocException(sprintf('Unable to do reflection of function "%s"', $function));
        }

        /** @var \Berlioz\PhpDoc\DocBlock\FunctionDocBlock $doc */
        $doc = $this->getFromReflection($reflection);

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
        if (null !== ($doc = $this->getDocFromCache($class))) {
            if ($doc instanceof ClassDocBlock) {
                return $doc;
            }
        }

        try {
            $reflection = new ReflectionClass($class);
        } catch (Exception $e) {
            throw new PhpDocException(sprintf('Unable to do reflection of class "%s"', $class));
        }

        /** @var \Berlioz\PhpDoc\DocBlock\ClassDocBlock $doc */
        $doc = $this->getFromReflection($reflection);

        // Get all properties
        foreach ($reflection->getProperties() as $reflectionProperty) {
            $this->getFromReflection($reflectionProperty);
        }

        // Get all methods
        foreach ($reflection->getMethods() as $reflectionMethod) {
            $this->getFromReflection($reflectionMethod);
        }

        return $doc;
    }

    /**
     * Get property PhpDoc.
     *
     * @param string $class Class name
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
        if (null !== ($doc = $this->getDocFromCache($fullName))) {
            if ($doc instanceof PropertyDocBlock) {
                return $doc;
            }
        }

        try {
            $reflection = new ReflectionProperty($class, $property);
        } catch (Exception $e) {
            throw new PhpDocException(sprintf('Unable to do reflection of property "%s"', $fullName));
        }

        /** @var \Berlioz\PhpDoc\DocBlock\PropertyDocBlock $doc */
        $doc = $this->getFromReflection($reflection);

        return $doc;
    }

    /**
     * Get method PhpDoc.
     *
     * @param string $class Class name
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
        if (null !== ($doc = $this->getDocFromCache($fullName))) {
            if ($doc instanceof MethodDocBlock) {
                return $doc;
            }
        }

        try {
            $reflection = new ReflectionMethod($class, $method);
        } catch (Exception $e) {
            throw new PhpDocException(sprintf('Unable to do reflection of method "%s"', $fullName));
        }

        /** @var \Berlioz\PhpDoc\DocBlock\MethodDocBlock $doc */
        $doc = $this->getFromReflection($reflection);

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
            if (sprintf('%s::', $classDoc->getName()) != substr($indexEntry, 0, $classNameLength + 2)) {
                continue;
            }

            $docs[$indexEntry] = $this->getDocFromCache($indexEntry);
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
    public function getFromReflection(Reflector $reflection): DocBlock
    {
        $reflectionClass = get_class($reflection);
        $docBlockClass = null;

        switch ($reflectionClass) {
            case ReflectionFunction::class:
                $docBlockClass = FunctionDocBlock::class;
                /** @var \ReflectionFunction $reflection */
                $name = sprintf('%s\%s', $reflection->getNamespaceName(), $reflection->getName());
                break;
            case ReflectionClass::class:
                $docBlockClass = ClassDocBlock::class;
                /** @var \ReflectionClass $reflection */
                $name = $reflection->getName();
                break;
            case ReflectionProperty::class:
                $docBlockClass = PropertyDocBlock::class;
                /** @var \ReflectionMethod $reflection */
                $name = sprintf('%s::$%s', $reflection->class, $reflection->getName());
                break;
            case ReflectionMethod::class:
                $docBlockClass = MethodDocBlock::class;
                /** @var \ReflectionProperty $reflection */
                $name = sprintf('%s::%s', $reflection->class, $reflection->getName());
                break;
            default:
                throw new PhpDocException(sprintf('Unable to treat "%s" reflection class', $reflectionClass));
        }

        // Get from cache
        if (null !== ($doc = $this->getDocFromCache($name))) {
            if ($doc instanceof DocBlock) {
                return $doc;
            }
        }

        // Get doc comment
        $docComment = $reflection->getDocComment();

        // Parse doc comment
        $docCommentParsed = $this->getParser()->parse($docComment ?: '');

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

        return $doc;
    }
}