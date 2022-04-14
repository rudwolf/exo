<?php
class ExoPrimes
{
    public $max;

    function __construct(int $max = 100)
    {
        $this->max = $max;
        echo "<pre>";
        for ($i=1; $i <= $this->max; $i++) {
            echo $i." ".$this->prime_or_divisor($i)."\n";
        }
        echo "</pre>";
    }

    function prime_or_divisor($n)
    {
        $divisors = [];
        if ($n == 1) $divisors[] = 1;
        for ($i = 2; $i <= $n/2; $i++){
            if ($n % $i == 0) {
                $divisors[] = $i;
            }
        }
        if (empty($divisors)) {
            return "[PRIME]";
        }
        return "[" . implode(",",$divisors). "]";
    }
}

$exo = new ExoPrimes();