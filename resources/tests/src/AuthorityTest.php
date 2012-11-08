<?php

namespace axelitus\Acre\Net\Uri;

class AuthorityTest extends \PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        // nothing to do here...
    }

    /**
     * @test
     */
    public function testAuthorityForge()
    {
        $expected = 'myusr:mypwd@myhost:80';
        $authority = Authority::forge('myhost', 80, 'myusr:mypwd');
        $output = (string) $authority;
        $this->assertEquals('//'.$expected, $output);
    }

    public function testAuthorityParse()
    {
        $expected = 'myusr:mypwd@myhost:80';
        $authority = Authority::parse($expected);
        $output = (string) $authority;
        $this->assertEquals('//'.$expected, $output);
    }
}
