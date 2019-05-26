<?php

class JobParser
{
    /**
     * Turn the given job string into an ordered JobCollection object
     *
     * @param String $jobstr - list of job codes and dependencies
     * @return JobCollection $jobs - an ordered collection of jobs
     *
     * @throws \Exception if the input job list is invalid
     */
    public function parse($jobstr)
    {
        // sanity check

        if ($jobstr == null || $jobstr == "")
        {
            return new JobCollection();
        }


        // convert the string into a JobCollection

        $jobCollection = $this->parseJobString($jobstr);

        try
        {
            $jobCollection->sort();
        }
        catch (\LogicException $e)
        {
            throw new \InvalidArgumentException($e->getMessage());
        }

        return ($jobCollection);
    }

    /**
     * Convert the job string into an unsorted JobCollection
     *
     * @param string $jobstr
     * @return JobCollection an unsorted Job Collection
     *
     * @throws InvalidArgumentException if invalid input detected
     */
    private function parseJobString($jobstr)
    {
        // remove whitespace and check string is valid

        $jobstr = $this->cleanAndValidateJobString($jobstr);


        // split each character into an array so we can iterate more easily

        $jobarr = str_split($jobstr);

        $unsortedJobs = new JobCollection();
        $job = new Job($jobarr[0]);

        for ($i=1;$i<count($jobarr);$i++)
        {
            $char = $jobarr[$i];

            if ($char == "=")
            {
                // increment i to skip '>'

                $i++;

                continue;
            }


            // if this is the last element of the array, then it must be a dependency.
            // so set dependency and exit

            if ($i == count($jobarr) - 1)
            {
                $job->setDependency(new Job($char));
                $unsortedJobs->addJob($job);

                continue;
            }


            // look ahead; if next is '=' then char is a job code.
            // otherwise, char is a dependency.

            if (($jobarr[$i + 1] == "="))
            {
                // start of a new job. add the previous one, if it is not already added

                if (! $unsortedJobs->contains($job->getCode()))
                {
                    $unsortedJobs->addJob($job);
                }

                $job = new Job($char);


                // special case; if i + 3 (past the arrow) is the end of the string we should add the job now
                // as long as it does not already contain the job

                if (! array_key_exists($i + 3, $jobarr))
                {
                    if (! $unsortedJobs->contains($job->getCode()))
                    {
                        $unsortedJobs->addJob($job);
                    }
                }
            }
            else
            {
                $job->setDependency(new Job($char));
                $unsortedJobs->addJob($job);

                continue;
            }
        }


        // catch special case of single, non-associated job

        if (! $unsortedJobs->contains($jobarr[0]))
        {
            $unsortedJobs->addJob(new Job($jobarr[0]));
        }

        return ($unsortedJobs);
    }

    /**
     * Clean the given job string; remove all whitespace
     * and verify the job matches our expected pattern
     *
     * @param string $jobstr
     * @return string $jobstr
     *
     * @throws \IllegalArgumentException if the jobstr does not match the expected pattern
     */
    private function cleanAndValidateJobString($jobstr)
    {
        // remove all whitespace from input string

        $jobstr = preg_replace('/\s*/m', '', $jobstr);


        // check the job string matches regex; if not, fail.
        // regex; match character=>[optional] OR start again (recursive)

        if (preg_match("/[a-zA-Z]=>(?:(?R)|[a-zA-Z]?)(?R)*$/m", $jobstr, $matches))
        {
            if ($matches[0] != $jobstr)
            {
                throw new \InvalidArgumentException("Invalid input");
            }
        }
        else
        {
            throw new \InvalidArgumentException("Invalid input");
        }

        return ($jobstr);
    }
}
