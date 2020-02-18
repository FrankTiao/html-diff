<?php

declare(strict_types=1);

namespace htmlDiff\libraries;


class Match extends Diff
{
    public $startInOld;
    public $startInNew;
    public $size;

    /**
     * Match constructor.
     * @param int $startInOld
     * @param int $startInNew
     * @param int $size
     */
    public function __construct(int $startInOld, int $startInNew, int $size) {
        $this->startInOld = $startInOld;
        $this->startInNew = $startInNew;
        $this->size = $size;

    }

    /**
     * @return int
     */
    public function endInOld(): int {
        return $this->startInOld + $this->size;
    }

    /**
     * @return int
     */
    public function endInNew(): int {
        return $this->startInNew + $this->size;
    }
}