<?php

namespace App\Events;

use App\Models\DiscountRule;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class DiscountApplied
{
    use Dispatchable, SerializesModels;

    public function __construct(
        public DiscountRule $rule,
        public int $amount,
        public int $appliedTo
    ) {}
}
