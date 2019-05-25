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
        $result = array();

        foreach ($converted as $job => $dependency)
        {
            $this->insertSorted($result, $job, $dependency);
        }

        return ($result);
    }

    /**
     * Insert the given job and dependency into the given array
     * in a sensible order
     */
    private function insertSorted(&$arr, $job, $dependency)
    {
        // if a no dependency, and not already inserted; push into array
        // and return

        if (! isset($dependency))
        {
            if (! in_array($job, $arr))
            {
                array_push($arr, $job);
            }

            return;
        }


        // if dependency is not already in the array
        // we can simply insert before the job

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
                // job is already in array; shift back
                // and insert dependency in front

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
                // TODO check for circular dependency here

                $jidx = array_search($job, $arr);
                $didx = array_search($dependency, $arr);

                if ($jidx < $didx)
                {
                    // job is before dependency; needs work
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

                $result[$key] = $value;
            }
        }

        return ($result);
    }
}
