<?php

namespace App\Utilities;

use HTMLPurifier;
use HTMLPurifier_Config;

class Purifier
{
    protected HTMLPurifier $purifier;

    public function __construct()
    {
        $this->purifier = new HTMLPurifier(HTMLPurifier_Config::createDefault());
    }

    /**
     * Purifies provided string from scripts and html tags.
     *
     * @param string $stringToPurify
     * @return string
     */
    public function purify(string $stringToPurify): string
    {
        return $this->purifier->purify($stringToPurify);
    }
}
