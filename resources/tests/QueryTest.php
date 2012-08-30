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

        $expected = '?myquery1=myva?lue1&myquery2=myvalue2&myquery3';
        $query = Query::forge($expected);
        $expected = '?myquery1=myva%3Flue1&myquery2=myvalue2&myquery3=';
        $output = (string) $query;
        $this->assertEquals($expected, $output);

        $query = Query::forge();
        $query->set('my?query', 'my%2Avalue');
        $query->set('your%3Fquery', 'your*value');
        $expected = '?my%3Fquery=my%2Avalue&your%3Fquery=your%2Avalue';
        $output = (string) $query;
        $this->assertEquals($expected, $output);

        $expected = '?my?query=my*value&your?query=your*value';
        $output = $query->build(false);
        $this->assertEquals($expected, $output);
    }
}
