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

namespace Berlioz\PhpDoc\Tag;

use Berlioz\PhpDoc\Tag;

/**
 * Class VarTag.
 *
 * @package Berlioz\PhpDoc\Tag
 */
class VarTag extends Tag
{
    /** @var string|null Variable title */
    protected $varTitle;
    /** @var string|null Variable type */
    protected $varType;

    /**
     * @inheritdoc
     */
    public function __construct(string $name, $value = null, ?string $raw = null)
    {
        parent::__construct($name, $value, $raw);
        $this->parseValue();
    }

    /**
     * Parse value of tag.
     */
    protected function parseValue()
    {
        $matches = [];
        if (preg_match('/^\s*(?<type>[\w\|\\\[\]]+)(?:\s+(?<title>.+))?\s*$/i', $this->raw, $matches) !== 1) {
            return;
        }

        $this->varType = $matches['type'];
        $this->varTitle = $matches['title'] ?? null;

        if (stripos($this->varType, '|') !== false) {
            $this->varType = explode('|', $this->varType);
        }
    }

    /**
     * Get variable title.
     *
     * @return null|string
     */
    public function getVarTitle(): ?string
    {
        return $this->varTitle;
    }

    /**
     * Get variable type.
     *
     * @return null|string|string[]
     */
    public function getVarType()
    {
        return $this->varType;
    }
}