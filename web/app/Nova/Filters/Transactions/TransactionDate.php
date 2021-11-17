<?php

namespace App\Nova\Filters\Transactions;

use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Ampeco\Filters\DateRangeFilter;

class TransactionDate extends DateRangeFilter
{
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
        $from = Carbon::parse($value[0]);
        $to = isset($value[1]) ? Carbon::parse($value[1]) : $from;

        $from = $from->startOfDay()->toDateTimeString();
        $to = $to->endOfDay()->toDateTimeString();
        
        return $query->whereBetween('created_at', [$from, $to]);
    }
}
