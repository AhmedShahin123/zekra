<?php

namespace App\Nova\Filters\Transactions;

use Illuminate\Http\Request;
use Laravel\Nova\Filters\Filter;

class TransactionType extends Filter
{
    /**
     * The filter's component.
     *
     * @var string
     */
    public $component = 'select-filter';

    /**
     * Apply the filter to the given query.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @param  mixed  $value
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function apply(Request $request, $query, $value)
    {
        return $query->where('type', $value);
    }

    /**
     * Get the filter's available options.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function options(Request $request)
    {
        return [
            'Zekra Payments'    => 'zekra_payments',
            'Partner Payments'  => 'partner_payments',
            'Taxes'             => 'taxes',
            'Shipping'          => 'shipping',
            'COD fees'          => 'cod',
            'Adjustment'        => 'adjustment'
        ];
    }
}
