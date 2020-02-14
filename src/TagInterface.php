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

/**
 * Interface TagInterface.
 *
 * @package Berlioz\PhpDoc
 */
interface TagInterface
{
    /**
     * TagInterface constructor.
     *
     * @param string $name Name of tag
     * @param null|mixed $value Value of tag (parsed)
     * @param null|string $originalValue Original value (if null, equal to the value parameter)
     */
    public function __construct(string $name, $value = null, ?string $originalValue = null);

    /**
     * Get name.
     *
     * @return string
     */
    public function getName(): string;

    /**
     * Get value.
     *
     * @return mixed
     */
    public function getValue();

    /**
     * Get original value.
     *
     * Must returns the original string value of tag.
     *
     * @return string
     */
    public function getRaw(): string;
}