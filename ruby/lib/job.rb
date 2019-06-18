require_relative './errors'

class Job
  attr_reader :key

  def initialize key, dependency = nil
    @key = key
    set_dependency(dependency)
  end

  def set_dependency(dependency)
    if (dependency != nil)
      unless dependency.is_a? Job
        raise JobError, 'Job dependency must be a Job instance'
      end

      if dependency.key == @key
        raise JobSelfDependencyError, 'Job cannot depend on itself'
      end

      @dependency = dependency
    end
  end

  def get_dependency
    @dependency
  end
end