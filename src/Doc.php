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

class Doc
{
    const PARENT_TYPE_UNKNOWN = 0;
    const PARENT_TYPE_CLASS = 1;
    const PARENT_TYPE_METHOD = 2;
    const PARENT_TYPE_FUNCTION = 3;
    /** @var string|null Title */
    private $title;
    /** @var string|null Description */
    private $description;
    /** @var \Berlioz\PhpDoc\Tag[][] */
    private $tags;
    /** @var int Parent type */
    private $parentType = self::PARENT_TYPE_UNKNOWN;
    /** @var string|null Parent name */
    private $parentName = null;

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
     * Set title.
     *
     * @param null|string $title
     *
     * @return \Berlioz\PhpDoc\Doc
     */
    public function setTitle(?string $title): Doc
    {
        $this->title = $title;

        return $this;
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
     * Set description.
     *
     * @param null|string $description
     *
     * @return \Berlioz\PhpDoc\Doc
     */
    public function setDescription(?string $description): Doc
    {
        $this->description = $description;

        return $this;
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
     * Set tags.
     *
     * @param \Berlioz\PhpDoc\Tag[][] $tags
     *
     * @return \Berlioz\PhpDoc\Doc
     */
    public function setTags(array $tags): Doc
    {
        $this->tags = $tags;

        return $this;
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

    /**
     * Get parent type.
     *
     * @return int Look at constants of class
     */
    public function getParentType(): int
    {
        return $this->parentType;
    }

    /**
     * Set parent type.
     *
     * Look at constants of class.
     *
     * @param int $parentType
     *
     * @return \Berlioz\PhpDoc\Doc
     */
    public function setParentType(int $parentType): Doc
    {
        $this->parentType = $parentType;

        return $this;
    }

    /**
     * Get parent name.
     *
     * @return null|string
     */
    public function getParentName(): ?string
    {
        return $this->parentName;
    }

    /**
     * Set parent name.
     *
     * @param null|string $parentName
     *
     * @return \Berlioz\PhpDoc\Doc
     */
    public function setParentName(?string $parentName): Doc
    {
        $this->parentName = $parentName;

        return $this;
    }
}