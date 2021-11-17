<?php

namespace App\Nova;

use Illuminate\Http\Request;
use Laravel\Nova\Fields\ID;
use Laravel\Nova\Fields\Boolean;
use Laravel\Nova\Fields\Number;
use Laravel\Nova\Fields\BelongsTo;
use Laravel\Nova\Panel;

use Khalin\Nova\Field\Link;
use Laravel\Nova\Fields\HasMany;
use OptimistDigital\MultiselectField\Multiselect;

use App\Models\Courier;
use App\Models\User;

use Laravel\Nova\Http\Requests\NovaRequest;

class Partner extends Resource
{
    /**
     * The model the resource corresponds to.
     *
     * @var string
     */
    public static $model = 'App\Models\Partner';

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
        'id','user_id','city_id','status','default'
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
            BelongsTo::make('User'),
            BelongsTo::make('City'),
            Boolean::make('status')->sortable(),
            Number::make('Commission', 'fee')->sortable(),

            Boolean::make('default')
            ->trueValue(1)
            ->falseValue(0),
            BelongsTo::make('Courier')->hideFromIndex(),
            Link::make('Default Courier', function () {
                $courierID = Courier::where('id', $this->courier_id)->pluck('user_id')->first();
                return User::where('id', $courierID)->pluck('email')->first();
            })->url(function () {
                $courierID = Courier::where('id', $this->courier_id)->pluck('id')->first();
                return "/admin/resources/couriers/{$courierID}";
            }),

            (new Panel('Partner Couriers', [
              Multiselect::make('Partner Couriers', 'partner_couriers')
                ->options(
                    Courier::with('user')->get()->pluck('user.name')
                )->placeholder('Choose Couriers For This Partner')
            ])),

            HasMany::make('Orders'),


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
