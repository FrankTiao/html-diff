<?php

declare(strict_types=1);

namespace htmlDiff\libraries;


class Operation extends Diff
{
    public $action;
    public $startInOld;
    public $endInOld;
    public $startInNew;
    public $endInNew;

    public function __construct(int $action, int $startInOld, int $endInOld, int $startInNew, int $endInNew) {
        $this->action = $action;
        $this->startInOld = $startInOld;
        $this->endInOld = $endInOld;
        $this->startInNew = $startInNew;
        $this->endInNew = $endInNew;
    }
}