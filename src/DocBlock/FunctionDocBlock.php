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

use ReflectionFunction;

/**
 * Class FunctionDocBlock.
 *
 * @package Berlioz\PhpDoc\DocBlock
 */
class FunctionDocBlock extends AbstractFunctionDocBlock
{
    /**
     * FunctionDocBlock constructor.
     *
     * @param \ReflectionFunction $reflectionFunction
     * @param null|string $title
     * @param null|string $description
     * @param array $tags
     */
    public function __construct(
        ReflectionFunction $reflectionFunction,
        ?string $title,
        ?string $description,
        array $tags = []
    ) {
        parent::__construct($reflectionFunction, $title, $description, $tags);
    }
}