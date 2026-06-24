<?php

namespace App\Model;

class ReadingDate
{
    public function __construct(
        public string $date,
        public string $full_date,
        public int $value,
        public float $usage,
        public string $time,
        public int $device_id,
    )
    {

    }

}