<?php

/**
 *
 */
class Job
{
    /**
     * Turn the given job string into an ordered hash
     *
     * @input String $jobs - list of job codes and dependencies
     * @output array $jobs - the sequence that the jobs should occur in,
     *
     * @throw \Exception if the input job list is invalid
     */
    public static function load($jobs)
    {
        // sanity check

        if ($jobs == null || $jobs == "")
        {
            return array();
        }
    }
}

