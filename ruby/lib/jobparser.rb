require_relative './job'
require_relative './jobcollection'
require_relative './errors'

class JobParser
  OUTPUT_ARRAY = :array
  OUTPUT_STRING = :string

  def load(job_str, output = OUTPUT_ARRAY)
    if job_str == nil or job_str == ''
      return []
    end

    jobs = parse_job_string(job_str)
    jobs.sort
    jobs
  end

  private

  def parse_job_string job_str
    job_str = clean_job_string job_str
    validate_job_string job_str


  end

  def clean_job_string job_str
    # TODO
  end

  def validate_job_string job_str
    # TODO
  end
end

