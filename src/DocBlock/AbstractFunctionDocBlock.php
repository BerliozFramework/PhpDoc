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
use ReflectionFunctionAbstract;
use ReflectionMethod;

/**
 * Class AbstractFunctionDocBlock.
 *
 * @package Berlioz\PhpDoc\DocBlock
 */
abstract class AbstractFunctionDocBlock extends DocBlock
{
    // Constants
    protected const IS_USER_DEFINED = 1;
    protected const IS_INTERNAL = 2;
    protected const IS_CLOSURE = 4;
    protected const IS_DEPRECATED = 8;
    protected const IS_GENERATOR = 16;
    protected const IS_VARIATIC = 32;
    /** @var int Properties */
    private $properties = 0;
    /** @var string Name of function */
    private $name;
    /** @var string Short name of function */
    private $shortName;
    /** @var string Namespace name of function */
    private $namespaceName;

    /**
     * AbstractFunctionDocBlock constructor.
     *
     * @param \ReflectionFunctionAbstract $reflectionFunction
     * @param null|string $title
     * @param null|string $description
     * @param array $tags
     */
    public function __construct(
        ReflectionFunctionAbstract $reflectionFunction,
        ?string $title,
        ?string $description,
        array $tags = []
    ) {
        parent::__construct($title, $description, $tags);

        if ($reflectionFunction instanceof ReflectionMethod) {
            $this->name = sprintf('%s::%s', $reflectionFunction->class, $reflectionFunction->getName());
            $this->namespaceName = $reflectionFunction->getDeclaringClass()->getNamespaceName();
        } else {
            $this->name = $reflectionFunction->getName();
            if (empty($this->namespaceName = $reflectionFunction->getNamespaceName())) {
                $this->name = sprintf('\%s', $this->name);
            }
        }

        $this->shortName = $reflectionFunction->getShortName();
        $this->properties =
            ($reflectionFunction->isUserDefined() ? AbstractFunctionDocBlock::IS_USER_DEFINED : 0) |
            ($reflectionFunction->isInternal() ? AbstractFunctionDocBlock::IS_INTERNAL : 0) |
            ($reflectionFunction->isClosure() ? AbstractFunctionDocBlock::IS_CLOSURE : 0) |
            ($reflectionFunction->isDeprecated() ? AbstractFunctionDocBlock::IS_DEPRECATED : 0) |
            ($reflectionFunction->isGenerator() ? AbstractFunctionDocBlock::IS_GENERATOR : 0) |
            ($reflectionFunction->isVariadic() ? AbstractFunctionDocBlock::IS_VARIATIC : 0);
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
     * Is user defined function?
     *
     * @return bool
     */
    public function isUserDefined(): bool
    {
        return ($this->properties & AbstractFunctionDocBlock::IS_USER_DEFINED) == AbstractFunctionDocBlock::IS_USER_DEFINED;
    }

    /**
     * Is internal function?
     *
     * @return bool
     */
    public function isInternal(): bool
    {
        return ($this->properties & AbstractFunctionDocBlock::IS_INTERNAL) == AbstractFunctionDocBlock::IS_INTERNAL;
    }

    /**
     * Is closure function?
     *
     * @return bool
     */
    public function isClosure(): bool
    {
        return ($this->properties & AbstractFunctionDocBlock::IS_CLOSURE) == AbstractFunctionDocBlock::IS_CLOSURE;
    }

    /**
     * Is deprecated function?
     *
     * @return bool
     */
    public function isDeprecated(): bool
    {
        return ($this->properties & AbstractFunctionDocBlock::IS_DEPRECATED) == AbstractFunctionDocBlock::IS_DEPRECATED;
    }

    /**
     * Is generator function?
     *
     * @return bool
     */
    public function isGenerator(): bool
    {
        return ($this->properties & AbstractFunctionDocBlock::IS_GENERATOR) == AbstractFunctionDocBlock::IS_GENERATOR;
    }

    /**
     * Is variatic function?
     *
     * @return bool
     */
    public function isVariatic(): bool
    {
        return ($this->properties & AbstractFunctionDocBlock::IS_VARIATIC) == AbstractFunctionDocBlock::IS_VARIATIC;
    }
}