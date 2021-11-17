<?php

namespace App\Nova;

use Illuminate\Http\Request;
use Laravel\Nova\Fields\ID;
use Laravel\Nova\Fields\Boolean;
use Laravel\Nova\Fields\Text;
use Laravel\Nova\Fields\BelongsTo;
use Laravel\Nova\Fields\HasMany;
use Laravel\Nova\Fields\Number;

use Laravel\Nova\Http\Requests\NovaRequest;

class City extends Resource
{
    /**
     * The model the resource corresponds to.
     *
     * @var string
     */
    public static $model = 'App\Models\City';

    public static $displayInNavigation = false;


    /**
     * The single value that should be used to represent the resource when being displayed.
     *
     * @var string
     */
    public static $title = 'city_name';

    /**
     * The columns that should be searched.
     *
     * @var array
     */
    public static $search = [
        'id','city_name','country_id','status'
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
            BelongsTo::make('Country'),
            // Number::make('cart_id')->hideFromIndex(),
            //
            // Number::make('user_id')->hideFromIndex(),

            Boolean::make('status')->sortable(),

            Text::make('city_name')
                ->sortable()
                ->rules('required', 'max:255'),
            Number::make('tax')->sortable(),
            Number::make('shipping')->sortable(),

            Text::make('locale')
                ->sortable()
                ->rules('required', 'max:255'),
            HasMany::make('Couriers'),
            HasMany::make('Partners'),


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
