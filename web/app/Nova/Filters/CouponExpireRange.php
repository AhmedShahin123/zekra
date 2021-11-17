<?php

namespace App\Nova\Filters;

use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Laravel\Nova\Filters\DateFilter;
use Ampeco\Filters\DateRangeFilter;


class CouponExpireRange extends DateRangeFilter
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
        
        return $query->whereBetween('expire_at', [$from, $to]);
    }
}
