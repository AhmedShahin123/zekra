<?php

namespace App\Nova;

use App\Models\Coupon;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Laravel\Nova\Fields\Date;
use Laravel\Nova\Fields\ID;
use Laravel\Nova\Fields\Number;
use Laravel\Nova\Fields\Text;
use Laravel\Nova\Http\Requests\NovaRequest;
use Maatwebsite\LaravelNovaExcel\Actions\DownloadExcel;

class CouponsReport extends Resource
{

    /**
     * The model the resource corresponds to.
     *
     * @var string
     */
    public static $model = 'App\Models\CouponReport';

    public static $displayInNavigation = false;


    /**
     * The single value that should be used to represent the resource when being displayed.
     *
     * @var string
     */
    public static $title = 'id';

    /**
     * The columns that should be searched.
     *
     * @var array
     */
    public static $search = [
        'id', 'code'
    ];

    /**
     * Build an "index" query for the given resource.
     *
     * @param  \Laravel\Nova\Http\Requests\NovaRequest  $request
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public static function indexQuery(NovaRequest $request, $query)
    {
        return $query->where('type', 'discount');
    }

    /**
     * Get the fields displayed by the resource.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */

    public function fields(Request $request)
    {
        return [
            ID::make()->sortable(),
            Text::make('code'),
            Number::make('Times Used', function () {
                // get the times this coupon has used with orders
                $orders = $this->orders;
                $count = count($orders);
                return $count;
            }),
            Number::make('Total discounted', function () {
                // get the total value that has discounted with this code
                $orders = $this->orders;
                $total = $orders->sum('discount_value');
                return $total;
            }),
            Date::make('Expire date', 'expire_at'),
            Number::make('Remaining Days', function () {
                // get the remaining days in this code till it is expired
                $today = Carbon::parse(date('Y-m-d'));
                $expire_date = Carbon::parse($this->expire_at);
                $remaining = $today->greaterThan($expire_date) ? 0 : $today->diffInDays($expire_date);
                return $remaining;
            }),
            Number::make('Number of unique users', function () {
                // get the total value that has discounted with this code
                $users = $this->orders->pluck('user')->pluck('email')->toArray();
                $users = array_unique($users);
                $count = count($users);
                return $count;
            }),
        ];
    }

    /**
     * Get the cards available for the request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function cards(Request $request)
    {
        return [];
    }

    /**
     * Get the filters available for the resource.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function filters(Request $request)
    {
        return [];
    }

    /**
     * Get the lenses available for the resource.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function lenses(Request $request)
    {
        return [];
    }

    /**
     * Get the actions available for the resource.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function actions(Request $request)
    {
        return [
            (new DownloadExcel)->withHeadings()
        ];
    }
}
