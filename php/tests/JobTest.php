<?php

use PHPUnit\Framework\TestCase;

class JobTest extends TestCase
{
    /**
     * Test Blank string returns empty array
     */
    public function testBlank()
    {
        $parser = new JobParser();
        $res = $parser->parse("");
        $this->assertEquals(array(), $res);
    }

    /**
     * Check null returns empty array
     */
    public function testNull()
    {
        $parser = new JobParser();
        $res = $parser->parse(null);
        $this->assertEquals(array(), $res);
    }

    /**
     * Test a => returns sequence of job a
     */
    public function testSimple()
    {
        $parser = new JobParser();
        $res = $parser->parse("a=>");

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
        $parser = new JobParser();
        $res = $parser->parse("a=>b=>c=>");

        $this->assertEquals(count($res), 3);
        $this->assertTrue(in_array("a", $res));
        $this->assertTrue(in_array("b", $res));
        $this->assertTrue(in_array("c", $res));
    }

    /**
     * Test basic structure but with weird spacing
     */
    public function testNoDependenciesSpacing()
    {
        $parser = new JobParser();
        $res = $parser->parse("
            a =>
            b =>
        ");

        $this->assertEquals(count($res), 2);
        $this->assertTrue(in_array("a", $res));
        $this->assertTrue(in_array("b", $res));
    }

    /**
     * Test structure with one dependency - c before b containing abc
     * @Ignore
     */
    public function testOneDependency()
    {
        $parser = new JobParser();
        $res = $parser->parse("
            a =>
            b => c
            c =>
        ");

        $cidx = array_search("c", $res);
        $bidx = array_search("b", $res);
        $aidx = array_search("a", $res);

        $this->assertTrue($cidx < $bidx);
        $this->assertNotNull($aidx);
        $this->assertEquals(count($res), 3);
    }

    /**
     * Test a more complex structure
     */
    public function testMultipleDependencies()
    {
        $parser = new JobParser();
        $res = $parser->parse("
            a =>
            b => c
            c => f
            d => a
            e => b
            f =>
        ");

        $aidx = array_search("a", $res);
        $bidx = array_search("b", $res);
        $cidx = array_search("c", $res);
        $didx = array_search("d", $res);
        $eidx = array_search("e", $res);
        $fidx = array_search("f", $res);

        $this->assertTrue($fidx < $cidx);
        $this->assertTrue($cidx < $bidx);
        $this->assertTrue($bidx < $eidx);
        $this->assertTrue($aidx < $didx);
        $this->assertEquals(count($res), 6);
    }

    /**
     * Test that input with self-referencing job returns an error
     */
    public function testSelfDepencyFails()
    {
        $this->expectException(\InvalidArgumentException::class);

        $parser = new JobParser();
        $res = $parser->parse("
            a =>
            b =>
            c => c
        ");
    }

    /**
     * Test that input which ends in a circular depency returns an error
     */
    public function testCircularDepencyFails()
    {
        $this->expectException(\InvalidArgumentException::class);

        $parser = new JobParser();
        $res = $parser->parse("
            a =>
            b => c
            c => f
            d => a
            e =>
            f => b
        ");
    }
}
