<?php

namespace axelitus\Acre\Net\Uri;

class PathTest extends \PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        // nothing to do here...
    }

    /**
     * @test
     */
    public function testPathForge()
    {
        $expected = 'path1/path2/path3/file.ext';
        $path = Path::forge($expected);
        $output = (string) $path;
        $this->assertEquals($expected, $output);

        $expected = '/path1/path2/path3/file.ext';
        $path = Path::forge($expected);
        $output = (string) $path;
        $this->assertEquals($expected, $output);

        $expected = 'file.ext';
        $path = Path::forge($expected);
        $output = (string) $path;
        $this->assertEquals($expected, $output);

        $expected = '/file.ext';
        $path = Path::forge($expected);
        $output = (string) $path;
        $this->assertEquals($expected, $output);
    }
}
