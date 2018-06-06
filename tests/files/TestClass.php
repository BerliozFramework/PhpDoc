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


/**
 * Class TestClass.
 *
 * @package Berlioz\PhpDoc\Tests\files
 */
class TestClass
{
    /**
     * Property 1.
     *
     * @var string
     */
    private $property1;
    /** @var int Property 2 */
    public $property2;

    /**
     * TestClass constructor.
     */
    public function __construct()
    {
    }

    /**
     * My method.
     *
     * My description of method.
     * Again.
     *
     * @param string            $param1 Parameter 1
     * @param int               $param2 Parameter 2
     * @param \SimpleXMLElement $param3 Parameter 3
     *
     * @return int
     */
    public function method1(string $param1, int $param2, \SimpleXMLElement $param3)
    {
        return 1;
    }

    /**
     * My method 2.
     *
     * My description of method 2.
     * Again.
     *
     * @param string $param1 Parameter 1
     * @param int    $param2 Parameter 2
     *
     * @return string
     *
     * @throws \Exception if an error occurred.
     * @route("test", param1=true, param2="test", param3={"test":
     *      "test"})
     */
    protected function method2(string $param1, int $param2)
    {
        return '';
    }
}