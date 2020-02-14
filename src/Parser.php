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

use Berlioz\PhpDoc\Exception\ParserException;
use Berlioz\PhpDoc\Tag\ParamTag;
use Berlioz\PhpDoc\Tag\ReturnTag;
use Berlioz\PhpDoc\Tag\VarTag;
use InvalidArgumentException;

/**
 * Class Parser.
 *
 * @package Berlioz\PhpDoc
 */
class Parser
{
    /** @var string[] Tags classes */
    protected $tagsClasses = [
        'var' => VarTag::class,
        'param' => ParamTag::class,
        'return' => ReturnTag::class
    ];

    /**
     * Add tag class.
     *
     * @param string $name Tag name
     * @param string $class Class name (must implements TagInterface interface)
     */
    public function addTag(string $name, string $class)
    {
        if (!is_a($class, TagInterface::class, true)) {
            throw new InvalidArgumentException(
                sprintf('Class name must implements %s interface', TagInterface::class)
            );
        }

        $this->tagsClasses[$name] = $class;
    }

    /**
     * Parse doc comment.
     *
     * @param string $doc
     *
     * @return array
     * @throws \Berlioz\PhpDoc\Exception\ParserException
     */
    public function parse(string $doc): array
    {
        $docExploded = $this->explodeDoc($this->removeAsterisks($doc));

        foreach ($docExploded['tags'] as &$tag) {
            $tag = $this->parseTag($tag);
        }

        return $docExploded;
    }

    /**
     * Remove asterisks of doc comment.
     *
     * @param string $str
     *
     * @return string
     */
    private function removeAsterisks(string $str): string
    {
        $str = preg_replace('#^\s*\/+\*+\v*|\h*\*+\/+\s*$|^\h*\*+\h?#m', '', $str);
        $str = trim($str);

        return $str;
    }

    /**
     * Explode doc comment (title, description and tags).
     *
     * @param string $str
     *
     * @return array
     */
    private function explodeDoc(string $str): array
    {
        $result = [
            'title' => [],
            'description' => [],
            'tags' => []
        ];
        $lines = preg_split('/\r\n|\r|\n/', $str);

        $step = 1;
        foreach ($lines as $line) {
            $isTag = preg_match('#^\h*@[\w\-\.\/\\\]#', $line) == 1;
            $emptyLine = empty(trim($line));

            // For tags
            if ($isTag || $step == 3) {
                $step = 3;

                if ($emptyLine) {
                    continue;
                }

                // New tag
                if ($isTag) {
                    $result['tags'][] = $line;
                    continue;
                }

                // Complete tag
                end($result['tags']);
                $result['tags'][key($result['tags'])] .= $line;
                continue;
            }

            if ($emptyLine && $step == 1 && !empty($result['title'])) {
                $step = 2;
                continue;
            }

            if ($step == 1) {
                $result['title'][] = $line;
                continue;
            }

            $result['description'][] = $line;
        }

        $result['title'] = trim(implode("\n", $result['title']) ?? null);
        $result['description'] = trim(implode("\n", $result['description']) ?? null);

        return $result;
    }

    /**
     * Parse tag.
     *
     * @param string $tag
     *
     * @return \Berlioz\PhpDoc\Tag
     * @throws \Berlioz\PhpDoc\Exception\ParserException
     */
    private function parseTag(string $tag): Tag
    {
        $regex_define = <<<'EOD'
(?(DEFINE)
    (?<d_bool> true | false )
    (?<d_null> null )
    (?<d_numeric> \-? \d+(?: \.\d+)? )
    (?<d_quotes> \'(?>[^'\\]++|\\.)*\' | "(?>[^"\\]++|\\.)*" )
    (?<d_value> \g<d_quotes> | \g<d_numeric> | \g<d_bool> | \g<d_null> | \g<d_json_array> | \g<d_json_obj> )

    (?<d_json_obj> { [\s\n]* (?: \g<d_json_obj_element> [\s\n]* , [\s\n]* )* \g<d_json_obj_element> [\s\n]* } )
    (?<d_json_obj_element> \g<d_quotes> [\s\n]* : [\s\n]* \g<d_value> )
    (?<d_json_array> \[ [\s\n]* (?: \g<d_value> [\s\n]* , [\s\n]* )* \g<d_value> [\s\n]* \] )
    (?<d_json> [\s\n]* (?> \g<d_json_obj> | \g<d_json_array> ) [\s\n]* )

    (?<d_tag_type> [\w\-\.\/\\]+ )
    (?<d_tag_value> \g<d_value> )
    (?<d_tag_option> (?: \g<d_tag_type> \s* = \s* )? (?: \g<d_value> ) )
    (?<d_tag_options> (?: \g<d_tag_option> \s* , \s* )* \g<d_tag_option> )
)
EOD;

        $matches = [];
        if (preg_match(
                '~' . $regex_define . '[\n\s]* @(?<name> \g<d_tag_type> ) [\n\s]* (?> \( (?<value1> .* ) \) | (?<value2> .* ) | \V+ )? $~xim',
                $tag,
                $matches
            ) !== 1) {
            throw new ParserException(sprintf('Bad tag format for "%s"', $tag));
        }

        $tagName = $matches['name'];
        $tagValue = $value = $matches['value1'] ?: $matches['value2'] ?: null;
        $tagValueIsArray = !empty($matches['value1']);

        if ($value !== null) {
            $value = trim($value);

            if (preg_match('~' . $regex_define . '^ \g<d_tag_options> $ ~xim', $value) == 1) {
                $matches = [];
                if (preg_match_all(
                        '~' . $regex_define . ' (?:, \s* | ^) (?: (?<name> [\w_]+ )\s*=\s* )? (?> (?<value_quoted> \g<d_quotes> ) | (?<value_bool> \g<d_bool> ) | (?<value_numeric> \g<d_numeric> ) | (?<value_null> \g<d_null> ) | (?<value_json> \g<d_json_array> | \g<d_json_obj> ) ) \s* (?: $)? ~xim',
                        $value,
                        $matches,
                        PREG_SET_ORDER
                    ) > 0) {
                    $value = [];

                    foreach ($matches as $key => $match) {
                        $opt_value = null;
                        if (!empty($match['value_quoted'])) {
                            $opt_value = substr($match['value_quoted'], 1, -1);
                        } elseif (!empty($match['value_bool'])) {
                            $opt_value = $match['value_bool'] == 'true';
                        } elseif (mb_strlen($match['value_numeric']) > 0) {
                            $opt_value = floatval($match['value_numeric']);
                        } elseif (!empty($match['value_null'])) {
                            $opt_value = null;
                        } elseif (!empty($match['value_json'])) {
                            $opt_value = json_decode($match['value_json']);
                        }

                        $value[$match['name'] ?: $key] = $opt_value;
                    }

                    if (!$tagValueIsArray && count($value) == 1) {
                        $value = reset($value);
                    }
                }
            }
        }

        // Create tag object
        $tagClass = $this->tagsClasses[$tagName] ?? Tag::class;
        /** @var \Berlioz\PhpDoc\Tag $tag */
        $tag = new $tagClass($tagName, $value, $tagValue);

        return $tag;
    }
}