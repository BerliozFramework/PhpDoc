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
 * Class DocBlock.
 *
 * @package Berlioz\PhpDoc
 */
class DocBlock
{
    /** @var string|null Title */
    protected $title;
    /** @var string|null Description */
    protected $description;
    /** @var \Berlioz\PhpDoc\Tag[][] */
    protected $tags;

    public function __construct(?string $title, ?string $description, array $tags = [])
    {
        $this->title = $title;
        $this->description = $description;
        $this->tags = $tags;
    }

    /**
     * Get title.
     *
     * @return null|string
     */
    public function getTitle(): ?string
    {
        return $this->title;
    }

    /**
     * Get description.
     *
     * @return null|string
     */
    public function getDescription(): ?string
    {
        return $this->description;
    }

    /**
     * Get tags.
     *
     * @return \Berlioz\PhpDoc\Tag[]
     */
    public function getTags(): array
    {
        return $this->tags;
    }

    /**
     * Has tag?
     *
     * @param string $name
     *
     * @return bool
     */
    public function hasTag(string $name): bool
    {
        return isset($this->tags[$name]);
    }

    /**
     * Get tag.
     *
     * @param string $name
     *
     * @return \Berlioz\PhpDoc\Tag[]|null
     */
    public function getTag(string $name): ?array
    {
        return $this->tags[$name] ?? null;
    }
}