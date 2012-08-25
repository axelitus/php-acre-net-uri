<?php

namespace axelitus\Acre\Net\Uri;

class QueryTest extends \PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        // nothing to do here...
    }

    /**
     * @test
     */
    public function testQueryForge()
    {
        $expected = '?myquery1=myvalue1&myquery2=myvalue2&myquery3=myvalue3';
        $query = Query::forge($expected);
        $output = (string) $query;
        $this->assertEquals($expected, $output);

        $expected = '?myquery1=myvalue1&myquery2=myvalue2&myquery3=';
        $query = Query::forge($expected);
        $output = (string) $query;
        $this->assertEquals($expected, $output);

        $expected = '?myquery1=myvalue1&myquery2=myvalue2&myquery3';
        $query = Query::forge($expected);
        $output = (string) $query;
        $this->assertEquals($expected.'=', $output);
    }
}
