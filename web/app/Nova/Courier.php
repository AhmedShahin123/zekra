<?php

namespace App\Nova;

use Illuminate\Http\Request;
use Laravel\Nova\Fields\ID;
use Laravel\Nova\Fields\Boolean;
use Laravel\Nova\Fields\Number;
use Laravel\Nova\Fields\BelongsTo;
use Laravel\Nova\Fields\HasMany;
use Laravel\Nova\Http\Requests\NovaRequest;
use Laravel\Nova\Panel;

class Courier extends Resource
{
    /**
     * The model the resource corresponds to.
     *
     * @var string
     */
    public static $model = 'App\Models\Courier';

    public static $displayInNavigation = false;


    /**
     * The single value that should be used to represent the resource when being displayed.
     *
     * @var string
     */
    public static $title = 'id';

    public function title()
    {
        return $this->user->name;
    }
    /**
     * The columns that should be searched.
     *
     * @var array
     */
    public static $search = [
        'id','user_id','city_id','fee','status','default'
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
            Number::make('fee')->sortable(),
            (new Panel('Cash on delivery fees', [
                Number::make('Primary amount', 'cash_delivery_primary_amount')->sortable(),
                Number::make('Primary amount fee', 'cash_delivery_primary_amount_fee')->sortable(),
                Number::make('Additional amount', 'cash_delivery_additional_amount')->sortable(),
                Number::make('Additional amount fee', 'cash_delivery_additional_amount_fee')->sortable(),
            ])),
            Boolean::make('default')
            ->trueValue(1)
            ->falseValue(0),

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
