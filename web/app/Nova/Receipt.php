<?php

namespace App\Nova;

use Illuminate\Http\Request;
use Laravel\Nova\Fields\ID;
use Khalin\Nova\Field\Link;
use Laravel\Nova\Fields\BelongsTo;

use Laravel\Nova\Http\Requests\NovaRequest;

class Receipt extends Resource
{
    /**
     * The model the resource corresponds to.
     *
     * @var string
     */
    public static $model = 'App\Models\Receipt';

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
        'id',
    ];

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
            Link::make('Receipt PDF', function () {
                $pdf = Receipt::where('id', $this->id)->pluck('receipt')->first();
                return $pdf;
            })->url(function () {
                $receipt = Receipt::where('id', $this->id)->first();
                return empty($receipt) ? null : "/storage/receipts/{$receipt->order_id}/{$receipt->receipt}";
            })->blank(),
            Link::make('Receipt Order', function () {
                $orderId =Receipt::where('id', $this->id)->pluck('order_id')->first();

                return $orderId;
            })->url(function () {
                $orderId =Receipt::where('id', $this->id)->pluck('order_id')->first();
                return "/admin/resources/orders/{$orderId}";
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
        return [];
    }
}
