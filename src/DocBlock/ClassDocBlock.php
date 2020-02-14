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

namespace Berlioz\PhpDoc\DocBlock;

use Berlioz\PhpDoc\DocBlock;
use ReflectionClass;

/**
 * Class ClassDocBlock.
 *
 * @package Berlioz\PhpDoc\DocBlock
 */
class ClassDocBlock extends DocBlock
{
    // Constants
    protected const IS_ABSTRACT = 1;
    protected const IS_FINAL = 2;
    protected const IS_INTERNAL = 4;
    protected const IS_USER_DEFINED = 8;
    protected const IS_ANONYMOUS = 16;
    protected const IS_CLONEABLE = 32;
    protected const IS_INSTANTIABLE = 64;
    protected const IS_INTERFACE = 128;
    protected const IS_ITERATEABLE = 256;
    protected const IS_TRAIT = 512;
    protected const IS_ITERABLE = 1024;
    /** @var int Properties */
    private $properties = 0;
    /** @var string Name of class */
    private $name;
    /** @var string Short name of class */
    private $shortName;
    /** @var string Namespace name of class */
    private $namespaceName;

    /**
     * ClassDocBlock constructor.
     *
     * @param ReflectionClass $reflectionClass
     * @param null|string $title
     * @param null|string $description
     * @param array $tags
     */
    public function __construct(
        ReflectionClass $reflectionClass,
        ?string $title,
        ?string $description,
        array $tags = []
    ) {
        parent::__construct($title, $description, $tags);

        $this->name = $reflectionClass->getName();
        $this->shortName = $reflectionClass->getShortName();
        $this->namespaceName = $reflectionClass->getNamespaceName();
        $this->properties =
            ($reflectionClass->isAbstract() ? ClassDocBlock::IS_ABSTRACT : 0) |
            ($reflectionClass->isFinal() ? ClassDocBlock::IS_FINAL : 0) |
            ($reflectionClass->isInternal() ? ClassDocBlock::IS_INTERNAL : 0) |
            ($reflectionClass->isUserDefined() ? ClassDocBlock::IS_USER_DEFINED : 0) |
            ($reflectionClass->isAnonymous() ? ClassDocBlock::IS_ANONYMOUS : 0) |
            ($reflectionClass->isCloneable() ? ClassDocBlock::IS_CLONEABLE : 0) |
            ($reflectionClass->isInstantiable() ? ClassDocBlock::IS_INSTANTIABLE : 0) |
            ($reflectionClass->isInterface() ? ClassDocBlock::IS_INTERFACE : 0) |
            ($reflectionClass->isIterateable() ? ClassDocBlock::IS_ITERATEABLE : 0) |
            ($reflectionClass->isTrait() ? ClassDocBlock::IS_TRAIT : 0);

        // PHP 7.2
        if (version_compare(PHP_VERSION, '7.2.0', '>=')) {
            $this->properties = $this->properties | ($reflectionClass->isIterable() ? ClassDocBlock::IS_ITERABLE : 0);
        }
    }

    /**
     * Get name.
     *
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * Get short name.
     *
     * @return string
     */
    public function getShortName(): string
    {
        return $this->shortName;
    }

    /**
     * Get namespace name.
     *
     * @return string
     */
    public function getNamespaceName(): string
    {
        return $this->namespaceName;
    }

    /**
     * Is abstract class?
     *
     * @return bool
     */
    public function isAbstract(): bool
    {
        return ($this->properties & ClassDocBlock::IS_ABSTRACT) == ClassDocBlock::IS_ABSTRACT;
    }

    /**
     * Is final class?
     *
     * @return bool
     */
    public function isFinal(): bool
    {
        return ($this->properties & ClassDocBlock::IS_FINAL) == ClassDocBlock::IS_FINAL;
    }

    /**
     * Is internal class?
     *
     * @return bool
     */
    public function isInternal(): bool
    {
        return ($this->properties & ClassDocBlock::IS_INTERNAL) == ClassDocBlock::IS_INTERNAL;
    }

    /**
     * Is user defined class?
     *
     * @return bool
     */
    public function isUserDefined(): bool
    {
        return ($this->properties & ClassDocBlock::IS_USER_DEFINED) == ClassDocBlock::IS_USER_DEFINED;
    }

    /**
     * Is anonymous class?
     *
     * @return bool
     */
    public function isAnonymous(): bool
    {
        return ($this->properties & ClassDocBlock::IS_ANONYMOUS) == ClassDocBlock::IS_ANONYMOUS;
    }

    /**
     * Is cloneable class?
     *
     * @return bool
     */
    public function isCloneable(): bool
    {
        return ($this->properties & ClassDocBlock::IS_CLONEABLE) == ClassDocBlock::IS_CLONEABLE;
    }

    /**
     * Is instantiable class?
     *
     * @return bool
     */
    public function isInstantiable(): bool
    {
        return ($this->properties & ClassDocBlock::IS_INSTANTIABLE) == ClassDocBlock::IS_INSTANTIABLE;
    }

    /**
     * Is interface class?
     *
     * @return bool
     */
    public function isInterface(): bool
    {
        return ($this->properties & ClassDocBlock::IS_INTERFACE) == ClassDocBlock::IS_INTERFACE;
    }

    /**
     * Is iterable class?
     *
     * @return bool
     */
    public function isIterable(): bool
    {
        return ($this->properties & ClassDocBlock::IS_ITERABLE) == ClassDocBlock::IS_ITERABLE;
    }

    /**
     * Is iterateable class?
     *
     * @return bool
     */
    public function isIterateable(): bool
    {
        return ($this->properties & ClassDocBlock::IS_ITERATEABLE) == ClassDocBlock::IS_ITERATEABLE;
    }

    /**
     * Is trait class?
     *
     * @return bool
     */
    public function isTrait(): bool
    {
        return ($this->properties & ClassDocBlock::IS_TRAIT) == ClassDocBlock::IS_TRAIT;
    }
}