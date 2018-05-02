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

namespace Berlioz\PhpDoc\DocBlock;

class FunctionDocBlock extends AbstractFunctionDocBlock
{
    // Constants
    const IS_DISABLED = 1;
    /** @var int Properties */
    private $properties = 0;

    /**
     * FunctionDocBlock constructor.
     *
     * @param \ReflectionFunction $reflectionFunction
     * @param null|string         $title
     * @param null|string         $description
     * @param array               $tags
     */
    public function __construct(\ReflectionFunction $reflectionFunction, ?string $title, ?string $description, array $tags = [])
    {
        parent::__construct($reflectionFunction, $title, $description, $tags);

        $this->properties = ($reflectionFunction->isDisabled() ? FunctionDocBlock::IS_DISABLED : 0);
    }

    /**
     * Is disabled function?
     *
     * @return bool
     */
    public function isDisabled(): bool
    {
        return ($this->properties & FunctionDocBlock::IS_DISABLED) == FunctionDocBlock::IS_DISABLED;
    }
}