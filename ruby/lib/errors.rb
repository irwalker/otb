class JobError < StandardError
end

class JobCircularDependencyError < JobError
end

class JobSelfDependencyError < JobError
end