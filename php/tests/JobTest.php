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
        $col = $parser->parse("");
        $res = $col->getArrayOutput();
        $this->assertEquals(array(), $res);
    }

    /**
     * Check null returns empty array
     */
    public function testNull()
    {
        $parser = new JobParser();
        $col = $parser->parse(null);
        $res = $col->getArrayOutput();
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
        $col = $parser->parse("a=>");

        $res = $col->getArrayOutput();

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
        $col = $parser->parse("a=>b=>c=>");
        $res = $col->getArrayOutput();

        $this->assertEquals(3, count($res));
        $this->assertTrue(in_array("a", $res));
        $this->assertTrue(in_array("b", $res));
        $this->assertTrue(in_array("c", $res));
    }

    /**
     * Test that a job with multiple dependencies fails elegantly
     */
    public function testJobMultipleDependenciesFails()
    {
        $this->expectException(\InvalidArgumentException::class);

        $parser = new JobParser();
        $col = $parser->parse("
            a=>b
            b=>c
            c=>
            g=>
            a=>g
        ");
    }

    /**
     * Test basic structure but with weird spacing
     */
    public function testNoDependenciesSpacing()
    {
        $parser = new JobParser();
        $col = $parser->parse("
            a =>
            b =>
        ");

        $res = $col->getArrayOutput();

        $this->assertEquals(2, count($res));
        $this->assertTrue(in_array("a", $res));
        $this->assertTrue(in_array("b", $res));
    }

    /**
     * Test structure with one dependency - c before b containing abc
     */
    public function testOneDependency()
    {
        $parser = new JobParser();
        $res = $parser->parse("
            a =>
            b => c
            c =>
        ");

        $res = $res->getArrayOutput();

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

        $res = $res->getArrayOutput();

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
     * Test a consistently ordered string output matches expected result
     */
    public function testStringOutput()
    {
        $parser = new JobParser();
        $col = $parser->parse("
            a => b
            b => c
            c =>
        ");

        $res = $col->getStringOutput();
        $this->assertEquals("cba", $res);
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

        $res = $res->getArrayOutput();

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
     * Test a job input that is entirely a chain of dependencies
     */
    public function testEntireDependencyChain()
    {
        $parser = new JobParser();
        $col = $parser->parse("
            a => b
            b => c
            c => d
            d => e
            e => f
            f => g
            g =>
        ");

        $res = $col->getStringOutput();
        $this->assertEquals("gfedcba", $res);
    }

    /**
     * Test that input with a dependency that doesn't exist returns an error
     */
    public function testNonexistantDependencyFails()
    {
        $this->expectException(\InvalidArgumentException::class);

        $parser = new JobParser();
        $res = $parser->parse("
            a => g
            b =>
            c =>
        ");
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
