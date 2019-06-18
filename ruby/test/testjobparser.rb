require "minitest/autorun"
require_relative '../lib/jobparser'

class TestJobParser < MiniTest::Test
  def setup
    @parser = JobParser.new
  end

  def test_simple
    res = @parser.load('a=>')
    assert_equal ['a'], res, 'expected unordered single "a"'
  end

  def test_empty
    res = @parser.load('')
    assert_equal [], res, 'empty string should return empty array'
  end

  def test_nil
    res = @parser.load(nil)
    assert_equal [], res, 'nil should return empty array'
  end

  def test_single_sequnce
    res = @parser.load('a=>b=>c')
    ('a'..'c').each do |char|
      assert res.include?(char), "character #{char} not found in result"
    end
  end

  def test_basic_ordered
    res = @parser.load('
      a=>
      b=>c
      c=>
    ')

    ('a'..'c').each do |char|
      assert res.include?(char), "character #{char} not found in result"
    end

    assert res.index('c') < res.index('b'), 'b should depend on c'
  end

  def test_complex_ordered
    res = @parser.load('
      a =>
      b => c
      c => f
      d => a
      e => b
      f =>
    ')

    ('a'..'f').each do |char|
      assert res.include?(char), "character #{char} not found in result"
    end

    assert res.index('b') < res.index('c')
    assert res.index('c') < res.index('f')
    assert res.index('d') < res.index('a')
    assert res.index('e') < res.index('b')
  end

  def test_self_dependency_fails
    assert_raises JobSelfDependencyError do
      res = @parser.load('
        a =>
        b =>
        c => c
      ')
    end
  end

  def test_circular_dependency_fails
    assert_raises JobCircularDependencyError do
      res = @parser.load('
        a =>
        b => c
        c => f
        d => a
        e =>
        f => b
      ')
    end
  end
end
