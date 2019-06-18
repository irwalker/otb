require "minitest/autorun"
require_relative '../lib/job'
require_relative '../lib/jobcollection'
require_relative '../lib/errors'

class TestJobCollection < MiniTest::Test
  def test_simple
    jobs = [Job.new('a')]
    col = JobCollection.new(jobs)
    assert_equal 'a', col.to_str
  end

  def test_simple_arr
    jobs = [Job.new('a')]
    col = JobCollection.new(jobs)
    assert_equal ['a'], col.to_array
  end

  def test_single_sequence
    jobs = [Job.new('a'), Job.new('b'), Job.new('c')]
    col = JobCollection.new(jobs)
    res = col.to_array

   ('a'..'c').each do |char|
      assert res.include?(char), "character #{char} not found in result"
    end
  end

  def test_basic_ordered
    jobs = [Job.new('a'), Job.new('b', Job.new('c')), Job.new('c')]
    col = JobCollection.new(jobs)
    col.sort

    res = col.to_array
    ('a'..'c').each do |char|
      assert res.include?(char), "character #{char} not found in result"
    end

    assert res.index('c') < res.index('b'), 'b should depend on c'
  end

  def test_complex_ordered
    jobs = [
      Job.new('a'),
      Job.new('b', Job.new('c')),
      Job.new('c', Job.new('f')),
      Job.new('d', Job.new('a')),
      Job.new('e', Job.new('b')),
      Job.new('f')
    ]

    col = JobCollection.new(jobs)
    col.sort

    res = col.to_array

    ('a'..'f').each do |char|
      assert res.include?(char), "character #{char} not found in result"
    end

    assert res.index('b') < res.index('c'), 'b should depend on c'
    assert res.index('c') < res.index('f'), 'c should depend on f'
    assert res.index('d') < res.index('a'), 'd should depend on a'
    assert res.index('e') < res.index('b'), 'e should depend on b'
  end

  def test_self_dependency_fails
    assert_raises JobSelfDependencyError do
      JobCollection.new([
        Job.new('a'),
        Job.new('b'),
        Job.new('c', Job.new('c'))
      ])
    end
  end

  def test_circular_dependency_fails
    assert_raises JobCircularDependencyError do
      JobCollection.new([
        Job.new('a'),
        Job.new('b', Job.new('c')),
        Job.new('c', Job.new('f')),
        Job.new('d', Job.new('a')),
        Job.new('e'),
        Job.new('f', Job.new('b'))
      ])
    end
  end
end