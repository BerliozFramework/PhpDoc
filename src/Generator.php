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

use Psr\Log\LoggerInterface;
use Psr\Log\LogLevel;
use Psr\SimpleCache\CacheInterface;

class Generator
{
    const CACHE_KEY_INDEX = '_BERLIOZ_PHPDOC';
    /** @var \Psr\SimpleCache\CacheInterface|null Cache */
    private $cacheManager;
    /** @var \Psr\Log\LoggerInterface|null Logger */
    private $logger;
    /** @var \Berlioz\PhpDoc\Parser Parser */
    private $parser;
    /** @var Doc[] Cache */
    private $_docs;
    /** @var string[] Cache index */
    private $_index;

    /**
     * Generator constructor.
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
     * Generator destructor.
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
            $this->parser = new Parser($this->logger);
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
     * @return \Berlioz\PhpDoc\Generator
     * @throws \Psr\SimpleCache\InvalidArgumentException
     */
    private function saveIndex(): Generator
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
     * @return \Berlioz\PhpDoc\Doc|null
     * @throws \Psr\SimpleCache\InvalidArgumentException
     */
    private function getDocFromCache(string $name): ?Doc
    {
        if (array_key_exists($name, $this->_docs)) {
            return $this->_docs[$name];
        }

        if (!is_null($this->cacheManager)) {
            $cacheKey = $this->getDocCacheName($name);
            if ($this->cacheManager->has($cacheKey)) {
                if (($doc = $this->cacheManager->get($cacheKey)) instanceof Doc) {
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
     * @param string                   $name
     * @param \Berlioz\PhpDoc\Doc|null $value
     *
     * @return \Berlioz\PhpDoc\Generator
     * @throws \Psr\SimpleCache\InvalidArgumentException
     */
    private function saveDocToCache(string $name, ?Doc $value): Generator
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

    /**
     * Get docs of given class, methods too.
     *
     * Returns an array with name of methods and class in keys.
     *
     * @param string $class
     *
     * @return array
     * @throws \Berlioz\PhpDoc\Exception\PhpDocException
     * @throws \Psr\SimpleCache\InvalidArgumentException
     */
    public function getClassDocs(string $class): array
    {
        $docs = [];

        // Get from cache
        if (is_null($doc = $this->getDocFromCache($class)) || !($doc instanceof Doc)) {
            // Generate new Doc
            $docs = $this->getParser()->fromClass($class);

            // Save in cache
            foreach ($docs as $key => $doc) {
                $this->saveDocToCache($key, $doc);
            }
        }

        return $docs;
    }

    /**
     * Get doc of given class.
     *
     * @param string $class
     *
     * @return \Berlioz\PhpDoc\Doc|null
     * @throws \Berlioz\PhpDoc\Exception\PhpDocException
     * @throws \Psr\SimpleCache\InvalidArgumentException
     */
    public function getClassDoc(string $class): ?Doc
    {
        // Get from cache
        if (is_null($doc = $this->getDocFromCache($class)) || !($doc instanceof Doc)) {
            $docs = $this->getClassDocs($class);
            $doc = $docs[$class] ?? null;
        }

        return $doc;
    }

    /**
     * Get doc of given method.
     *
     * @param string $class
     * @param string $method
     *
     * @return \Berlioz\PhpDoc\Doc|null
     * @throws \Berlioz\PhpDoc\Exception\PhpDocException
     * @throws \Psr\SimpleCache\InvalidArgumentException
     */
    public function getMethodDoc(string $class, string $method): ?Doc
    {
        $methodName = sprintf('%s::%s', $class, $method);

        // Get from cache
        if (is_null($doc = $this->getDocFromCache($methodName)) || !($doc instanceof Doc)) {
            $docs = $this->getClassDocs($class);
            $doc = $docs[$methodName] ?? null;
        }

        return $doc;
    }

    /**
     * Get doc of given function.
     *
     * @param string $function
     *
     * @return \Berlioz\PhpDoc\Doc|null
     * @throws \Berlioz\PhpDoc\Exception\PhpDocException
     * @throws \Psr\SimpleCache\InvalidArgumentException
     */
    public function getFunctionDoc(string $function): ?Doc
    {
        // Get from cache
        if (is_null($doc = $this->getDocFromCache($function)) || !($doc instanceof Doc)) {
            $doc = $this->getParser()->fromFunction($function);
            $this->saveDocToCache($function, $doc);
        }

        return $doc;
    }
}