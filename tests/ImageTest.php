<?php
namespace Sholomka\Image\Test;

use Sholomka\Image\Image;

/**
 * Class ImageTest for the test
 * @package Sholomka\Image\Test
 */
class ImageTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Name of the site for grabbing
     */
    const SITE = "http://pattaya.zagranitsa.com/";

    /**
     * Test a connection to the site
     */
    public function testGrabbingSite()
    {
        $class = new Image();
        $this->assertInternalType("string", $class->grabbingSite(self::SITE));
    }

    /**
     * Test check file
     */
    public function testFileExists()
    {
        $class = new Image();
        $this->assertInternalType("boolean", $class->fileExists(self::SITE . DIRECTORY_SEPARATOR . "test"));
    }

    /**
     * Parsing website code
     */
    public function testParseCode()
    {
        $class = new Image();
        $code = $class->grabbingSite(self::SITE);
        $this->assertInternalType("array", $class->parseCode($code));
    }
}
