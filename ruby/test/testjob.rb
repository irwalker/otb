require 'minitest/autorun'
require_relative '../lib/job'
require_relative '../lib/errors'

class TestJob < MiniTest::Test
  def test_construct
    job = Job.new('a')
    assert_equal 'a', job.key
  end

  def test_construct_dep
    job = Job.new('a', Job.new('b'))
    assert_equal 'a', job.key
    assert_equal 'b', job.get_dependency.key
  end

  def test_construct_dep_string
    assert_raises JobError do
      job = Job.new('a', 'b')
    end
  end

  def test_construct_self_referencing_fails
    assert_raises JobSelfDependencyError do
      job = Job.new('a', Job.new('a'))
    end
  end

  def test_set_dependency_self_referencing_fails
    job = Job.new('a')

    assert_raises JobSelfDependencyError do
      job.set_dependency(Job.new('a'))
    end
  end
end