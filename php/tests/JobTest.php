<?php

use PHPUnit\Framework\TestCase;

class JobTest extends TestCase
{
    /**
     * Test Blank string returns empty array
     */
    public function testBlank()
    {
        $res = Job::load("");
        $this->assertEquals(array(), $res);
    }

    /**
     * Check null returns empty array
     */
    public function testNull()
    {
        $res = Job::load(null);
        $this->assertEquals(array(), $res);
    }

    /**
     * Test a => returns sequence of job a
     */
    public function testSimple()
    {
        $res = Job::load("a=>");
        $this->assertEquals(
            array("a"),
            $res
        );
    }

    /**
     * Test basic structure with no dependencies - order is not important
     */
    public function testNoDependencies()
    {
        $res = Job::load("a=>b=>c=>");
        $this->assertTrue(in_array("a", $res));
        $this->assertTrue(in_array("b", $res));
        $this->assertTrue(in_array("c", $res));
    }

    /**
     * Test structure with one dependency - c before b containing abc
     */
    public function testOneDependency()
    {
        $res = Job::load("a=>b=>cc=>");
        $cidx = key("c");
        $bidx = key("b");
        $aidx = key("a");
        $this->assertTrue($cidx < $bidx);
        $this->assertNotNull($aidx);
    }
}
