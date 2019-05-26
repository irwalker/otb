<?php

/**
 * Encapsulates a collection of Job objects. The Jobs are 'unique' i.e. a Job
 * cannot be inserted twice.
 */
class JobCollection
{
    /*
     * @var array - the internal array of jobs
     */
    private $jobs;

    public function __construct($jobs = array())
    {
        $this->jobs = $jobs;
    }

    /**
     * Sort the list of jobs
     */
    public function sort()
    {
        // make sure jobs have their dependencies populated

        $this->populateJobDependencies();


        // sorted job array

        $sortedJobs = array();

        for ($i=0;$i<count($this->jobs);$i++)
        {
            $this->sortDependencyChain($sortedJobs, $this->jobs[$i]);
        }

        $this->jobs = $sortedJobs;
    }

    /**
     * Add the given job onto the end of the collection.
     *
     * @throws \InvalidArgumentException if the job already exists in the array
     */
    public function addJob(Job $job)
    {
        if ($this->contains($job->getCode()))
        {
            throw new \InvalidArgumentException("Cannot add a job that already exists");
        }

        array_push($this->jobs, $job);
    }

    /**
     * Check whether the collection contains the given job code
     *
     * @param String $code 
     * @return true if the collection contains the code, otherwise false
     */
    public function contains($code)
    {
        foreach ($this->jobs as $job)
        {
            if ($job->getCode() == $code)
            {
                return (true);
            }
        }

        return (false);
    }

    /**
     * Return the full Job object from the collection, if one with the given code exists
     *
     * @param string $code
     * @return Job
     */
    public function findJob($code)
    {
        foreach ($this->jobs as $job)
        {
            if ($job->getCode() == $code)
            {
                return ($job);
            }
        }

        return (null);
    }

    /**
     * Follow job => dependency chain through. Make sure dependencies
     * for each job are fully mapped Job objects. Should be able to follow through
     * a Job, generating a dependency chain. This allows us to check for circular
     * dependencies, and guarantee that following a chain through will end in correct ordering.
     *
     * @throws \LogicException if a circular dependency is found or dependency is not an existing job
     */
    public function populateJobDependencies()
    {
        foreach ($this->jobs as $job)
        {
            $this->populateDependencyChain($job, 0);
        }
    }

    /**
     * Returns the ordered job code array
     */
    public function getArrayOutput()
    {
        $out = array();
        for ($i=0;$i<count($this->jobs);$i++)
        {
            array_push($out, $this->jobs[$i]->getCode());
        }

        return ($out);
    }

    /**
     * Returns the ordered job array in a simple string output
     */
    public function getStringOutput()
    {
        $out = "";
        for($i=0;$i<count($this->jobs);$i++)
        {
            $out .= $this->jobs[$i]->getCode();
        }

        return ($out);
    }

    /**
     * Populate dependency chain for the given job
     *
     * @param Job $job
     * @param integer $depth
     */
    private function populateDependencyChain($job, $depth)
    {
        // if we reach a depth gte the number of jobs we must be in a circular chain

        if ($depth >= count($this->jobs))
        {
            // circular dependency; logic exceptin

            throw new \LogicException("Circular dependency chain detected");
        }

        $dep = $job->getDependency();

        if (isset($dep))
        {
            // get job representation of dependency

            $j = $this->findJob($dep->getCode());

            if (! isset($j))
            {
                throw new \LogicException("Dependency must be a job that exists");
            }

            if ($j->getDependency() != null)
            {
                $dep->setDependency($j->getDependency());
                $job->setDependency($dep);

                $this->populateDependencyChain($j, $depth + 1);
            }
        }
    }

    /**
     * Recursively insert the given Job into the sorted array of jobs.
     *
     * <b>Assumes the array has been checked for circular
     * dependencies, otherwise we could get stuck in an infinite loop.</b>
     *
     * @param array $sortedJobs the array of jobs we are sorting
     * @param Job $job the job to insert into the array
     */
    private function sortDependencyChain(&$sortedJobs, Job $job)
    {
        $dependency = $job->getDependency();

        if (isset($dependency))
        {
            $this->sortDependencyChain($sortedJobs, $dependency);
        }


        // check if job has already been inserted into the array

        for ($i=0;$i<count($sortedJobs);$i++)
        {
            $insertedJob = $sortedJobs[$i];

            if ($insertedJob->getCode() == $job->getCode())
            {
                // job already inserted, return

                return;
            }
        }

        array_push($sortedJobs, $job);
    }
}