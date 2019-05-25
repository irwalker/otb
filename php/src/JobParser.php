<?php

class JobParser
{
    /**
     * Turn the given job string into an ordered hash
     *
     * @param String $jobstr - list of job codes and dependencies
     * @return array $jobs - the sequence that the jobs should occur in,
     *
     * @throws \Exception if the input job list is invalid
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
        $result = array();

        foreach ($converted as $job => $dependency)
        {
            $this->insertSorted($result, $job, $dependency, $converted);
        }

        return ($result);
    }

    /**
     * Insert the given job and dependency into the given array
     * in a sensible order
     *
     * @param array &$arr the output array
     * @param string $job the job code
     * @param string $dependency the dependency, if one exists
     * @param array the input array
     */
    private function insertSorted(&$arr, $job, $dependency, $input)
    {
        // sanity check

        if ($job == $dependency)
        {
            throw new \InvalidArgumentException("Jobs cant depend on themselves");
        }

        if (isset ($dependency) && (! array_key_exists($dependency, $input)))
        {
            throw new \InvalidArgumentException("Dependency must be a job that exists");
        }


        // if no dependency and job not already inserted; push into array and return

        if (! isset($dependency))
        {
            if (! in_array($job, $arr))
            {
                array_push($arr, $job);
            }

            return;
        }


        // if dependency is not already in the array can simply insert before the given job

        if (! in_array($dependency, $arr))
        {
            if (! in_array($job, $arr))
            {
                array_push($arr, $dependency);
                array_push($arr, $job);

                return;
            }
            else
            {
                // job is already in array; shift backwards, inserting dependency before job

                $idx = array_search($job, $arr);
                for ($i = count($arr);$i>$idx;$i--)
                {
                    $arr[$i] = $arr[$i - 1];
                }

                $arr[$idx] = $dependency;

                return;
            }
        }
        else
        {
            // dependency already in array

            if (! in_array($job, $arr))
            {
                // job is not in array yet; simply push job onto the end of the array

                array_push($arr, $job);
            }
            else
            {
                // job and dependency both in array already

                $jidx = array_search($job, $arr);
                $didx = array_search($dependency, $arr);

                if ($jidx < $didx)
                {
                    // shift dependency down in front of job, moving jobs in between
                    // up the array

                    for ($i = $didx;$i>$jidx;$i--)
                    {
                        $arr[$i] = $arr[$i-1];
                    }

                    $arr[$jidx] = $dependency;


                    // check if we are breaking the rules by doing this; if so, a circular dependency exists.

                    for ($i = 0;$i<count($arr);$i++)
                    {
                        $jidx = $i;
                        $depend = $input[$arr[$jidx]];

                        if (isset($depend))
                        {
                            for ($j=$i;$j<count($arr);$j++)
                            {
                                // check if dependency is in array after job

                                if ($arr[$j] == $depend)
                                {
                                    throw new \InvalidArgumentException("Circular dependency");
                                }
                            }
                        }
                    }
                }
            }
        }
    }

    /**
     * Convert the input string into an array of 'job' => 'dependency'
     *
     * @param string $jobstr
     * @return array, unsorted job array of job => dependency
     */
    private function parseInput($jobstr)
    {
        // remove all whitespace from input string

        $jobstr = preg_replace('/\s*/m', '', $jobstr);


        // check the input string matches regex; if not, fail.
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

        $jobarr = str_split($jobstr);

        $result = array();
        $key    = $jobarr[0];
        $value  = null;

        for ($i=0;$i<count($jobarr);$i++)
        {
            $char = $jobarr[$i];
            if ($char == "=")
            {
                // increment i to skip '>'
                $i++;

                continue;
            }


            // look-ahead. if next is '=' then current is a 'key'; otherwise
            // current char is a 'value' (dependency)

            if (array_key_exists(($i + 1), $jobarr))
            {
                $next = $jobarr[$i + 1];
                if ($next == "=")
                {
                    // special case; if we are at => null, push now

                    if (! array_key_exists(($i + 3), $jobarr))
                    {
                        // push existing key/value

                        $result[$key] = $value;


                        // push current char as a key

                        $result[$char] = null;

                        continue;
                    }

                    $result[$key] = $value;
                    $key    = $char;
                    $value  = null;

                    continue;
                }
                else
                {
                    $value = $char;

                    continue;
                }
            }
            else
            {
                // if we get here; must be end of string
                // so add to array regardless

                $result[$key] = $char;
            }
        }

        return ($result);
    }
}
