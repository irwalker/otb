class JobParser
  OUTPUT_ARRAY = :array
  OUTPUT_STRING = :string

  def load jobs, output = OUTPUT_ARRAY
    return []
  end
end

class JobError < StandardError
end

class JobCircularDependencyError < JobError
end

class JobSelfDependencyError < JobError
end