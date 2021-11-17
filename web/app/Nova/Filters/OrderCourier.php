<?php

namespace App\Nova\Filters;

use Illuminate\Http\Request;
use Laravel\Nova\Filters\Filter;
use App\Models\User;
use App\Models\Courier;

class OrderCourier extends Filter
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
        $courier = Courier::where('user_id', $value)->first();
        //return $courier;
        return $query->whereHas('courierOrders', function ($q) use ($courier) {
            $q->where('courier_id', $courier->id);
        })->get();
    }

    /**
     * Get the filter's available options.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function options(Request $request)
    {
        $couriers = User::whereHas('couriers')->pluck('id', 'name');
        return $couriers;
    }
}
