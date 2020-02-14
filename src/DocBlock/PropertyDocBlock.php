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
use ReflectionProperty;

/**
 * Class PropertyDocBlock.
 *
 * @package Berlioz\PhpDoc\DocBlock
 */
class PropertyDocBlock extends DocBlock
{
    // Constants
    protected const IS_PUBLIC = 1;
    protected const IS_PROTECTED = 2;
    protected const IS_PRIVATE = 4;
    protected const IS_STATIC = 8;
    protected const IS_DEFAULT = 16;
    /** @var int Properties */
    private $properties = 0;
    /** @var string Name of property */
    private $name;
    /** @var string Short name of property */
    private $shortName;
    /** @var string Namespace name of property */
    private $namespaceName;
    /** @var string Class name of property */
    private $className;

    /**
     * PropertyDocBlock constructor.
     *
     * @param \ReflectionProperty $reflectionProperty
     * @param null|string $title
     * @param null|string $description
     * @param array $tags
     */
    public function __construct(
        ReflectionProperty $reflectionProperty,
        ?string $title,
        ?string $description,
        array $tags = []
    ) {
        parent::__construct($title, $description, $tags);

        $this->name = sprintf('%s::$%s', $reflectionProperty->class, $reflectionProperty->getName());
        $this->namespaceName = $reflectionProperty->getDeclaringClass()->getNamespaceName();
        $this->shortName = $reflectionProperty->getName();
        $this->className = $reflectionProperty->class;
        $this->properties =
            ($reflectionProperty->isPublic() ? PropertyDocBlock::IS_PUBLIC : 0) |
            ($reflectionProperty->isProtected() ? PropertyDocBlock::IS_PROTECTED : 0) |
            ($reflectionProperty->isPrivate() ? PropertyDocBlock::IS_PRIVATE : 0) |
            ($reflectionProperty->isStatic() ? PropertyDocBlock::IS_STATIC : 0) |
            ($reflectionProperty->isDefault() ? PropertyDocBlock::IS_DEFAULT : 0);
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
     * Get class name.
     *
     * @return string
     */
    public function getClassName(): string
    {
        return $this->className;
    }

    /**
     * Is public property?
     *
     * @return bool
     */
    public function isPublic(): bool
    {
        return ($this->properties & PropertyDocBlock::IS_PUBLIC) == PropertyDocBlock::IS_PUBLIC;
    }

    /**
     * Is protected property?
     *
     * @return bool
     */
    public function isProtected(): bool
    {
        return ($this->properties & PropertyDocBlock::IS_PROTECTED) == PropertyDocBlock::IS_PROTECTED;
    }

    /**
     * Is private property?
     *
     * @return bool
     */
    public function isPrivate(): bool
    {
        return ($this->properties & PropertyDocBlock::IS_PRIVATE) == PropertyDocBlock::IS_PRIVATE;
    }

    /**
     * Is static property?
     *
     * @return bool
     */
    public function isStatic(): bool
    {
        return ($this->properties & PropertyDocBlock::IS_STATIC) == PropertyDocBlock::IS_STATIC;
    }

    /**
     * Is default property?
     *
     * @return bool
     */
    public function isDefault(): bool
    {
        return ($this->properties & PropertyDocBlock::IS_DEFAULT) == PropertyDocBlock::IS_DEFAULT;
    }
}