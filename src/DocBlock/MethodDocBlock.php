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

use ReflectionMethod;

/**
 * Class MethodDocBlock.
 *
 * @package Berlioz\PhpDoc\DocBlock
 */
class MethodDocBlock extends AbstractFunctionDocBlock
{
    // Constants
    protected const IS_PUBLIC = 1;
    protected const IS_PROTECTED = 2;
    protected const IS_PRIVATE = 4;
    protected const IS_CONSTRUCTOR = 8;
    protected const IS_DESTRUCTOR = 16;
    protected const IS_STATIC = 32;
    protected const IS_ABSTRACT = 64;
    protected const IS_FINAL = 128;
    /** @var int Properties */
    private $properties = 0;
    /** @var string Class name of method */
    private $className;

    /**
     * MethodDocBlock constructor.
     *
     * @param \ReflectionMethod $reflectionMethod
     * @param null|string $title
     * @param null|string $description
     * @param array $tags
     */
    public function __construct(
        ReflectionMethod $reflectionMethod,
        ?string $title,
        ?string $description,
        array $tags = []
    ) {
        parent::__construct($reflectionMethod, $title, $description, $tags);

        $this->className = $reflectionMethod->class;
        $this->properties =
            ($reflectionMethod->isPublic() ? MethodDocBlock::IS_PUBLIC : 0) |
            ($reflectionMethod->isProtected() ? MethodDocBlock::IS_PROTECTED : 0) |
            ($reflectionMethod->isPrivate() ? MethodDocBlock::IS_PRIVATE : 0) |
            ($reflectionMethod->isConstructor() ? MethodDocBlock::IS_CONSTRUCTOR : 0) |
            ($reflectionMethod->isDestructor() ? MethodDocBlock::IS_DESTRUCTOR : 0) |
            ($reflectionMethod->isStatic() ? MethodDocBlock::IS_STATIC : 0) |
            ($reflectionMethod->isAbstract() ? MethodDocBlock::IS_ABSTRACT : 0) |
            ($reflectionMethod->isFinal() ? MethodDocBlock::IS_FINAL : 0);
    }

    /**
     * Get class name.
     *
     * @return string
     */
    public function getClassName(): string
    {
        return $this->className;
    }

    /**
     * Is public method?
     *
     * @return bool
     */
    public function isPublic(): bool
    {
        return ($this->properties & MethodDocBlock::IS_PUBLIC) == MethodDocBlock::IS_PUBLIC;
    }

    /**
     * Is protected method?
     *
     * @return bool
     */
    public function isProtected(): bool
    {
        return ($this->properties & MethodDocBlock::IS_PROTECTED) == MethodDocBlock::IS_PROTECTED;
    }

    /**
     * Is private method?
     *
     * @return bool
     */
    public function isPrivate(): bool
    {
        return ($this->properties & MethodDocBlock::IS_PRIVATE) == MethodDocBlock::IS_PRIVATE;
    }

    /**
     * Is constructor method?
     *
     * @return bool
     */
    public function isConstructor(): bool
    {
        return ($this->properties & MethodDocBlock::IS_CONSTRUCTOR) == MethodDocBlock::IS_CONSTRUCTOR;
    }

    /**
     * Is destructor method?
     *
     * @return bool
     */
    public function isDestructor(): bool
    {
        return ($this->properties & MethodDocBlock::IS_DESTRUCTOR) == MethodDocBlock::IS_DESTRUCTOR;
    }

    /**
     * Is static method?
     *
     * @return bool
     */
    public function isStatic(): bool
    {
        return ($this->properties & MethodDocBlock::IS_STATIC) == MethodDocBlock::IS_STATIC;
    }

    /**
     * Is abstract method?
     *
     * @return bool
     */
    public function isAbstract(): bool
    {
        return ($this->properties & MethodDocBlock::IS_ABSTRACT) == MethodDocBlock::IS_ABSTRACT;
    }

    /**
     * Is final method?
     *
     * @return bool
     */
    public function isFinal(): bool
    {
        return ($this->properties & MethodDocBlock::IS_FINAL) == MethodDocBlock::IS_FINAL;
    }
}