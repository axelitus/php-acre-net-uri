<?php

namespace axelitus\Acre\Net\Uri;

class UriTest extends \PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        // nothing to do here...
    }

    /**
     * Tests Uri::parse()
     *
     * @test
     */
    public function testUriParse()
    {
        $uri = 'http://myuser:mypwd@myhost:80/mypath1/mypath2/myfile.html?myquery1=myvalue1&myquery2=myvalue2&myquery3=#myfragment';
        $output = Uri::parse($uri)->components;
        $expected = array(
            'scheme'    => 'http',
            'authority' => '//myuser:mypwd@myhost:80',
            'path'      => '/mypath1/mypath2/myfile.html',
            'query'     => '?myquery1=myvalue1&myquery2=myvalue2&myquery3=',
            'fragment'  => 'myfragment'
        );
        $this->assertEquals($expected, $output);

        $uri = 'index.html';
        $output = Uri::parse($uri)->components;
        $expected = array(
            'scheme'    => '',
            'authority' => '',
            'path'      => 'index.html',
            'query'     => '',
            'fragment'  => ''
        );
        $this->assertEquals($expected, $output);
    }

    /**
     * Tests Uri::forge()
     *
     * @test
     */
    public function testUriForge()
    {
        $expected = 'http://myuser:mypwd@myhost:80/mypath1/mypath2/myfile.html?myquery1=myvalue1&myquery2=myvalue2&myquery3=#myfragment';
        $uri = Uri::forge($expected);
        $output = (string)$uri;
        $this->assertEquals($expected, $output);

        $expected = '*';
        $uri = Uri::forge($expected);
        $output = (string)$uri;
        $this->assertEquals($expected, $output);
    }

    /**
     * Tests Uri::validate()
     *
     * @test
     */
    public function testUriValidate()
    {

    }

    /**
     * Tests Uri::__toString()
     *
     * @test
     */
    public function testUriToString()
    {
        $expected = 'http://myuser:mypwd@myhost:80/mypath1/mypath2/myfile.html?myquery1=myvalue1&myquery2=myvalue2&myquery3=#myfragment';
        $uri = Uri::forge($expected);
        $output = (string)$uri;
        $this->assertEquals($expected, $output);
    }
}
