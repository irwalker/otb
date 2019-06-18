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
    if depth >= @jobs.length
      raise JobCircularDependencyError, 'circular dependency chain detected'
    end
  end
end
