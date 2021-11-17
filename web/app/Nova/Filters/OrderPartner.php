<?php

namespace App\Nova\Filters;

use Illuminate\Http\Request;
use Laravel\Nova\Filters\Filter;

use App\Models\User;
use App\Models\Partner;

class OrderPartner extends Filter
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
        $partner = Partner::where('user_id', $value)->first();
        //return $courier;
        return $query->whereHas('partnerOrders', function ($q) use ($partner) {
            $q->where('partner_id', $partner->id);
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
        $partners = User::whereHas('partners')->pluck('id', 'name');
        return $partners;
    }
}
