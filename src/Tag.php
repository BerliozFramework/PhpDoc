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

class Tag implements TagInterface
{
    /** @var string Name */
    protected $name;
    /** @var mixed Value */
    protected $value;
    /** @var string Original value */
    protected $originalValue;

    /**
     * Tag constructor.
     *
     * @param string      $name          Name of tag
     * @param null|mixed  $value         Value of tag (parsed)
     * @param null|string $originalValue Original value (if null, equal to the value parameter)
     */
    public function __construct(string $name, $value = null, ?string $originalValue = null)
    {
        $this->name = $name;
        $this->value = $value;
        $this->originalValue = $originalValue;
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
     * Get value.
     *
     * @return mixed
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * Get original value.
     *
     * @return string
     */
    public function getOriginalValue(): string
    {
        return $this->originalValue;
    }
}