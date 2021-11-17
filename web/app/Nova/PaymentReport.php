<?php

namespace App\Nova;

use App\Models\Order as ModelsOrder;
use App\Models\Partner;
use Illuminate\Http\Request;
use Laravel\Nova\Fields\ID;
use Khalin\Nova\Field\Link;
use Laravel\Nova\Fields\Number;
use Laravel\Nova\Http\Requests\NovaRequest;

class PaymentReport extends Resource
{
    /**
     * The model the resource corresponds to.
     *
     * @var string
     */
    public static $model = 'App\Models\Payment';

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

    public static function indexQuery(NovaRequest $request, $query)
    {
        return $query->where('purchased_type', ModelsOrder::class)->where('status', 1);
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
            Link::make('Order', function () {
                return 'Order: '.$this->purchased_id;
            })->url(function () {
                return "/admin/resources/orders/{$this->purchased_id}";
            }),

            Link::make('Partner', function () {
                $order = ModelsOrder::find($this->purchased_id);
                $partner = Partner::with('user')->find($order->partner_id);
                return $partner->user->name;
            })->url(function () {
                $order = ModelsOrder::find($this->purchased_id);
                return "/admin/resources/partners/{$order->partner_id}";
            }),

            Number::make('Total Payment', function () {
                return $this->money_amount;
            })
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
