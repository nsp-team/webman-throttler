<?php
return [
    'enable' => true,
    
    'capacity' => 60, // The number of requests the "bucket" can hold
    'seconds' => 60,  // The time it takes the "bucket" to completely refill
    'cost' => 1, // The number of tokens this action uses.
];