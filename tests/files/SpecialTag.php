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

namespace Berlioz\PhpDoc\Tests\files;

use Berlioz\PhpDoc\Tag;

class SpecialTag extends Tag
{
    public function __construct(string $name, $value = null, ?string $originalValue = null)
    {
        parent::__construct($name, $value, $originalValue);

        $this->value = 'test';
    }
}