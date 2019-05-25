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
     * Test that input not matching the expected pattern fails elegantly
     */
    public function testInvalidArgumentFails()
    {
        $this->expectException(\InvalidArgumentException::class);

        $parser = new JobParser();
        $res = $parser->parse("
            a=>bc
            d=>a
        ");
    }

    /**
     * Test that random input string fails elegantly
     */
    public function testRandomStrFails()
    {
        $this->expectException(\InvalidArgumentException::class);

        $parser = new JobParser();
        $res = $parser->parse("not a valid string");
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

        $this->assertEquals(3, count($res));
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

        $this->assertEquals(2, count($res));
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
        $this->assertEquals(3, count($res));
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
        $this->assertEquals(6, count($res));
    }

    /**
     * Test a chain of dependencies
     */
    public function testDependencyChain()
    {
        $parser = new JobParser();
        $res = $parser->parse("
            a => f
            b => a
            c => a
            d =>
            e => a
            f => d
        ");

        $aidx = array_search("a", $res);
        $bidx = array_search("b", $res);
        $cidx = array_search("c", $res);
        $didx = array_search("d", $res);
        $eidx = array_search("e", $res);
        $fidx = array_search("f", $res);

        $this->assertTrue($fidx < $aidx);
        $this->assertTrue($aidx < $bidx);
        $this->assertTrue($aidx < $cidx);
        $this->assertTrue($aidx < $eidx);
        $this->assertTrue($didx < $fidx);
        $this->assertTrue($didx < $aidx);
    }

    /**
     * Test that input with self-referencing job returns an error
     */
    public function testSelfDependencyFails()
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
