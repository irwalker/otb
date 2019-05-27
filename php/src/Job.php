<?php

/**
 * Job object. Represents a Job with a code and zero or one dependency.
 * A job may not depend on itself.
 */
class Job
{
    /*
     * @var string job code
     */
    private $code;

    /*
     * @var Job dependency
     */
    private $dependency;

    function __construct($code, Job $dependency = null)
    {
        $this->code = $code;

        if (isset($dependency))
        {
            $this->setDependency($dependency);
        }
    }

    /**
     * Get the Job code
     *
     * @return string
     */
    public function getCode()
    {
        return ($this->code);
    }

    /**
     * Set the Job code
     *
     * @param string $code
     */
    public function setCode($code)
    {
        $this->code = $code;
    }

    /**
     * Get the Job Dependency
     *
     * @return Job
     */
    public function getDependency()
    {
        return ($this->dependency);
    }

    /**
     * Set the job dependency
     *
     * @param Job $dependency
     */
    public function setDependency(Job $dependency)
    {
        if ($dependency->getCode() == $this->code)
        {
            throw new \InvalidArgumentException("Jobs cannot depend on themselves");
        }

        $this->dependency = $dependency;
    }
}