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

use Berlioz\PhpDoc\Exception\PhpDocException;
use Psr\Log\LoggerInterface;
use Psr\Log\LogLevel;

class Parser
{
    /** @var \Psr\Log\LoggerInterface|null Logger */
    private $logger;
    /** @var string[] Tags classes */
    private $tagsClasses;

    /**
     * Parser constructor.
     *
     * @param null|\Psr\Log\LoggerInterface $logger
     */
    public function __construct(?LoggerInterface $logger = null)
    {
        $this->logger = $logger;
        $this->tagsClasses = [];
    }

    /**
     * Logs with an arbitrary level.
     *
     * @param mixed  $level
     * @param string $message
     * @param array  $context
     *
     * @return void
     */
    private function log($level, string $message, array $context = [])
    {
        if (!is_null($this->logger)) {
            $this->logger->log($level,
                               sprintf('{class} / %s', $message),
                               array_merge($context, ['class' => __CLASS__]));
        }
    }

    /**
     * Add tag class.
     *
     * @param string $name  Tag name
     * @param string $class Class name (must implements TagInterface interface)
     */
    public function addTagClass(string $name, string $class)
    {
        if (is_a($class, TagInterface::class, true)) {
            $this->tagsClasses[$name] = $class;
        } else {
            throw new \InvalidArgumentException(sprintf('Class name must implements %s interface', TagInterface::class));
        }
    }

    /**
     * Parse doc comment of function.
     *
     * @param string|\Closure $function
     *
     * @return \Berlioz\PhpDoc\Doc|null
     * @throws \Berlioz\PhpDoc\Exception\PhpDocException
     */
    public function fromFunction($function): ?Doc
    {
        try {
            $reflectionFunction = new \ReflectionFunction($function);
            $doc = $this->fromReflectionFunction($reflectionFunction);

            // Log
            $this->log(LogLevel::DEBUG, sprintf('Parse doc comment of function "%s"', $reflectionFunction->getName()));
        } catch (\ReflectionException $e) {
            throw new \InvalidArgumentException('Argument of method must be a valid function name or a closure', 0, $e);
        }

        return $doc;
    }

    /**
     * Parse doc comment of method.
     *
     * @param string $class  Class name
     * @param string $method Method name
     *
     * @return \Berlioz\PhpDoc\Doc|null
     * @throws \Berlioz\PhpDoc\Exception\PhpDocException
     */
    public function fromMethod(string $class, string $method): ?Doc
    {
        $class = ltrim($class, '\\');
        $docs = $this->fromClass($class);

        // Log
        $this->log(LogLevel::DEBUG, sprintf('Parse doc comment of method "%s::%s"', $class, $method));

        return $docs[sprintf('%s::%s', $class, $method)];
    }

    /**
     * Parse doc comment of class and methods.
     *
     * @param string|object $class Class name or object
     *
     * @return array
     * @throws \Berlioz\PhpDoc\Exception\PhpDocException
     */
    public function fromClass($class): array
    {
        $docs = [];

        // Reflection of class
        if (is_object($class)) {
            $reflectionClass = new \ReflectionObject($class);
        } else {
            try {
                $reflectionClass = new \ReflectionClass($class);
            } catch (\ReflectionException $e) {
                throw new \InvalidArgumentException('Argument of method must be a valid class name or an object', 0, $e);
            }
        }

        // Class
        $doc = null;
        if ($classDoc = $reflectionClass->getDocComment()) {
            if (!is_null($doc = $this->fromDocComment($classDoc))) {
                $doc->setParentType(Doc::PARENT_TYPE_CLASS);
                $doc->setParentName($reflectionClass->getName());
            }
        }
        $docs[$class] = $doc;

        // Log
        $this->log(LogLevel::DEBUG, sprintf('Parse doc comment of class "%s"', $reflectionClass->getName()));

        // Methods
        foreach ($reflectionClass->getMethods() as $reflectionMethod) {
            $doc = $this->fromReflectionFunction($reflectionMethod);
            $docs[sprintf('%s::%s', $reflectionMethod->class, $reflectionMethod->name)] = $doc;

            // Log
            $this->log(LogLevel::DEBUG, sprintf('Parse doc comment of class "%s", parse method "%s"', $reflectionMethod->class, $reflectionMethod->name));
        }

        return $docs;
    }

    /**
     * Generate Doc from function/method.
     *
     * @param \ReflectionFunctionAbstract $reflectionFunction
     *
     * @return \Berlioz\PhpDoc\Doc|null
     * @throws \Berlioz\PhpDoc\Exception\PhpDocException
     */
    public function fromReflectionFunction(\ReflectionFunctionAbstract $reflectionFunction): ?Doc
    {
        if ($functionDoc = $reflectionFunction->getDocComment()) {
            $doc = $this->fromDocComment($functionDoc);

            if ($reflectionFunction instanceof \ReflectionMethod) {
                $doc->setParentType(Doc::PARENT_TYPE_METHOD);
                $doc->setParentName(sprintf('%s::%s', $reflectionFunction->class, $reflectionFunction->getName()));
            } else {
                $doc->setParentType(Doc::PARENT_TYPE_FUNCTION);
                $doc->setParentName($reflectionFunction->getName());
            }

            return $doc;
        }

        return null;
    }

    /**
     * Generate Doc from doc comment.
     *
     * @param string $doc
     *
     * @return \Berlioz\PhpDoc\Doc
     * @throws \Berlioz\PhpDoc\Exception\PhpDocException
     */
    public function fromDocComment(string $doc): Doc
    {
        $docExploded = $this->explodeDoc($this->removeAsterisks($doc));

        $doc = new Doc;
        $doc->setTitle($docExploded['title']);
        $doc->setDescription($docExploded['description'] ?: null);

        $tags = [];
        foreach ($docExploded['tags'] as $tag) {
            $tag = $this->parseTag($tag);
            $tags[$tag->getName()][] = $tag;
        }

        $doc->setTags($tags);

        return $doc;
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
        $str = preg_replace('#^\s*\/+\*+\s*$|\h*\*+\/+\s*$|^\h*\*+\h?#m', '', $str);
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
        $result = ['title'       => [],
                   'description' => [],
                   'tags'        => []];
        $lines = preg_split('/\r\n|\r|\n/', $str);

        $step = 1;
        foreach ($lines as $line) {
            $isTag = preg_match('#^\h*@[\w\-\.\/\\\]#', $line) == 1;
            $emptyLine = empty(trim($line));

            // For tags
            if ($isTag || $step == 3) {
                $step = 3;

                if (!$emptyLine) {
                    // New tag
                    if ($isTag) {
                        $result['tags'][] = $line;
                    } // Complete tag
                    else {
                        end($result['tags']);
                        $result['tags'][key($result['tags'])] = $line;
                    }
                }
            } else {
                if ($emptyLine && $step == 1 && !empty($result['title'])) {
                    $step = 2;
                } else {
                    if ($step == 1) {
                        $result['title'][] = $line;
                    } else {
                        $result['description'][] = $line;
                    }
                }
            }
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
     * @throws \Berlioz\PhpDoc\Exception\PhpDocException
     */
    private function parseTag(string $tag): Tag
    {
        $regex_define = <<<'EOD'
(?(DEFINE)
    (?<d_bool> true | false )
    (?<d_null> null )
    (?<d_numeric> \d+(?: \.\d+)? )
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
        if (preg_match('~' . $regex_define . '[\n\s]* @(?<name> \g<d_tag_type> ) [\n\s]* (?> \( (?<value1> .* ) \) | (?<value2> .* ) | \V+ )? $~xim', $tag, $matches) == 1) {
            $tagName = $matches['name'];
            $tagValue = $value = $matches['value1'] ?: $matches['value2'] ?: null;

            if ($value !== null) {
                $value = trim($value);

                if (preg_match('~' . $regex_define . '^ \g<d_tag_options> $ ~xim', $value) == 1) {
                    $matches = [];
                    if (preg_match_all('~' . $regex_define . ' (?:, \s* | ^) (?: (?<name> [\w_]+ )\s*=\s* )? (?> (?<value_quoted> \g<d_quotes> ) | (?<value_bool> \g<d_bool> ) | (?<value_numeric> \g<d_numeric> ) | (?<value_null> \g<d_null> ) | (?<value_json> \g<d_json_array> | \g<d_json_obj> ) ) \s* (?: $)? ~xim', $value, $matches, PREG_SET_ORDER) > 0) {
                        $value = [];

                        foreach ($matches as $key => $match) {
                            $opt_value = null;
                            if (!empty($match['value_quoted'])) {
                                $opt_value = substr($match['value_quoted'], 1, -1);
                            } elseif (!empty($match['value_bool'])) {
                                $opt_value = $match['value_bool'] == 'true';
                            } elseif (!empty($match['value_numeric'])) {
                                $opt_value = floatval($match['value_numeric']);
                            } elseif (!empty($match['value_null'])) {
                                $opt_value = null;
                            } elseif (!empty($match['value_json'])) {
                                $opt_value = json_decode($match['value_json']);
                            }

                            $value[$match['name'] ?: $key] = $opt_value;
                        }

                        if (count($value) == 1) {
                            $value = reset($value);
                        }
                    }
                }
            }

            if (array_key_exists($tagName, $this->tagsClasses)) {
                return new $this->tagsClasses[$tagName]($tagName, $value, $tagValue);
            } else {
                return new Tag($tagName, $value, $tagValue);
            }
        } else {
            throw new PhpDocException(sprintf('Bad tag format for "%s"', $tag));
        }
    }
}