<?php

declare(strict_types=1);

namespace htmlDiff\libraries;


class Operation extends Diff
{
    public $action = 0;
    public $startInOld = 0;
    public $endInOld = 0;
    public $startInNew = 0;
    public $endInNew = 0;

    public function __construct(int $action, int $startInOld, int $endInOld, int $startInNew, int $endInNew) {
        $this->action = $action;
        $this->startInOld = $startInOld;
        $this->endInOld = $endInOld;
        $this->startInNew = $startInNew;
        $this->endInNew = $endInNew;
    }
}