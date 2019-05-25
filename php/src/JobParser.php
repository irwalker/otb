<?php

class JobParser
{
    /**
     * Turn the given job string into an ordered hash
     *
     * @input String $jobstr - list of job codes and dependencies
     * @output array $jobs - the sequence that the jobs should occur in,
     *
     * @throw \Exception if the input job list is invalid
     */
    public function parse($jobstr)
    {
        // sanity check

        if ($jobstr == null || $jobstr == "")
        {
            return array();
        }


        // convert the string into an array we can follow through

        $converted = $this->parseInput($jobstr);


        // order the array into something sensible

        return ($converted);
    }

    /**
     * convert the input string into an array of 'job' => 'dependency'
     */
    private function parseInput($jobstr)
    {
        // remove all whitespace from input string

        $jobstr = preg_replace('/\s*/m', '', $jobstr);
        $expl = explode("=>", $jobstr);


        // remove empty values from array

        $result = array_filter($expl);

        return ($result);
    }
}
