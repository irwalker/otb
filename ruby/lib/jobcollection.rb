class JobCollection

  def initialize jobs
    @jobs = jobs

    # check jobs are valid by recursing
    populate_dependencies
  end

  def sort
    # TODO
  end

  def to_array
    arr = @jobs.collect { |job| job.key }
  end

  def to_str
    str = @jobs.collect { |job| job.key }.join(',')
  end

  private

  def populate_dependencies
    @jobs.each { |job| recurse_job_dependencies(job, 0) }
  end

  def recurse_job_dependencies job, depth
    if depth > @jobs.length
      raise JobCircularDependencyError, 'circular dependency chain detected'
    end

    dependency = job.get_dependency
    unless dependency.nil?
      dependency_populated = find_job(dependency.key)
      if dependency_populated.nil?
        raise JobError, 'Dependency does not exist'
      end

      unless dependency_populated.get_dependency.nil?
        puts "#{dependency_populated.get_dependency.key}"
        job.set_dependency(dependency_populated)

        recurse_job_dependencies(job, depth + 1)
      end
    end
  end

  def find_job key
    @jobs.find { |job| job.key == key }
  end
end
