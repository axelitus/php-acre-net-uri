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
        // *** Absolute URIs ***
        // Example URIS taken from: http://en.wikipedia.org/wiki/Uniform_resource_identifier#Examples_of_absolute_URIs
        $uri = 'http://example.org/absolute/URI/with/absolute/path/to/resource.txt';
        $output = Uri::validate($uri);
        $this->assertTrue($output);

        $uri = 'ftp://example.org/resource.txt';
        $output = Uri::validate($uri);
        $this->assertTrue($output);

        $uri = 'urn:issn:1535-3613';
        $output = Uri::validate($uri);
        $this->assertTrue($output);

        // *** URI references
        // Example URIS taken from: http://en.wikipedia.org/wiki/Uniform_resource_identifier#Examples_of_URI_references
        $uri = 'http://en.wikipedia.org/wiki/URI#Examples_of_URI_references';
        $output = Uri::validate($uri);
        $this->assertTrue($output);

        $uri = 'http://example.org/absolute/URI/with/absolute/path/to/resource.txt';
        $output = Uri::validate($uri);
        $this->assertTrue($output);

        $uri = '//example.org/scheme-relative/URI/with/absolute/path/to/resource.txt';
        $output = Uri::validate($uri);
        $this->assertTrue($output);

        $uri = '/relative/URI/with/absolute/path/to/resource.txt';
        $output = Uri::validate($uri);
        $this->assertTrue($output);

        $uri = 'relative/path/to/resource.txt';
        $output = Uri::validate($uri);
        $this->assertTrue($output);

        $uri = '../../../resource.txt';
        $output = Uri::validate($uri);
        $this->assertTrue($output);

        $uri = './resource.txt#frag01';
        $output = Uri::validate($uri);
        $this->assertTrue($output);

        $uri = 'resource.txt';
        $output = Uri::validate($uri);
        $this->assertTrue($output);

        $uri = '#frag01';
        $output = Uri::validate($uri);
        $this->assertTrue($output);

        $uri = '';
        $output = Uri::validate($uri);
        $this->assertTrue($output);
    }

    /**
     * Tests Uri::validate() with matches
     *
     * @test
     */
    public function testUriValidateMatch()
    {
        $uri = 'http://en.wikipedia.org/wiki/URI#Examples_of_URI_references';
        $output = Uri::validate($uri, $matches);
        $this->assertTrue($output);

        $expected = 'http';
        $this->assertEquals($expected, $matches['scheme']);

        $expected = 'en.wikipedia.org';
        $this->assertEquals($expected, $matches['authority']);

        $expected = '';
        $this->assertEquals($expected, $matches['userinfo']);

        $expected = 'en.wikipedia.org';
        $this->assertEquals($expected, $matches['host']);

        $expected = '';
        $this->assertEquals($expected, $matches['port']);

        $expected = '/wiki/URI';
        $this->assertEquals($expected, $matches['path']);

        $expected = 'Examples_of_URI_references';
        $this->assertEquals($expected, $matches['fragment']);
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
