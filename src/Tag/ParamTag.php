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

namespace Berlioz\PhpDoc\Tag;

class ParamTag extends VarTag
{
    /** @var string|null Variable name */
    protected $varName;

    /**
     * Parse value of tag.
     */
    protected function parseValue()
    {
        $matches = [];
        if (preg_match('/^\s*(?<type>[\w\|\\\[\]]+\b)?(?:\s*(?<name>\$[\w_]+))?(?:\s+(?<title>.+))?\s*$/i', $this->raw, $matches) === 1) {
            $this->varType = $matches['type'] ?? null;
            $this->varName = $matches['name'] ?? null;
            $this->varTitle = $matches['title'] ?? null;
        }
    }

    /**
     * Get variable name.
     *
     * @return null|string
     */
    public function getVarName(): ?string
    {
        return $this->varName;
    }
}