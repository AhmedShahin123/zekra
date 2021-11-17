<?php

namespace Zekra\TransactionsTotal;

use Laravel\Nova\Card;

class TransactionsTotal extends Card
{
    /**
     * The width of the card (1/3, 1/2, or full).
     *
     * @var string
     */
    public $width = 'full';

    /**
     * Get the component name for the element.
     *
     * @return string
     */
    public function component()
    {
        return 'transactions-total';
    }

    public function type($type){
        return $this->withMeta(['type' => $type]);
    }
}
