<?php
class ExoAscii {
    function __construct($start = ',', $end = '|') {
        $startCode = ord($start);
        $endCode = ord($end);
        $asciiArray = [];
        for ($i=$startCode; $i <= $endCode; $i++) {
            $asciiArray[] = chr($i);
        }
        // randomize array
        shuffle($asciiArray);
        // removed last item (random item, since array was randomized before)
        $removed = array_pop($asciiArray);
        echo implode("--",$asciiArray)."<br>";
        echo "the removed item was: $removed";
        /**
         * Note: This is the most efficient method. We could also pick a random item on the array
         * and use then using the unset function we could further remove it. On the method above
         * we store the removed value, which can be shown or not to the final user. Giving the code
         * owner the option to hide it even knowing which element he removed from the array in a faster
         * and smarter way.
         */
    }
}

$exoAscii = new ExoAscii();