<?php

namespace App\Nova;

use App\Models\Currency;
use Illuminate\Http\Request;
use Laravel\Nova\Fields\ID;
use Laravel\Nova\Fields\Boolean;
use Laravel\Nova\Fields\HasMany;
use Laravel\Nova\Fields\Select;
use Laravel\Nova\Fields\Text;
use Laravel\Nova\Http\Requests\NovaRequest;

class Country extends Resource
{
    /**
     * The model the resource corresponds to.
     *
     * @var string
     */
    public static $model = 'App\Models\Country';

    public static $displayInNavigation = false;


    /**
     * The single value that should be used to represent the resource when being displayed.
     *
     * @var string
     */
    public static $title = 'country_name';

    /**
     * The columns that should be searched.
     *
     * @var array
     */
    public static $search = [
          'id','country_name','status','code'
    ];

    /**
     * Get the fields displayed by the resource.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function fields(Request $request)
    {
        $currencies = Currency::whereNotNull('name')->pluck('name', 'code')->toArray();
        return [
            ID::make()->sortable(),
            Text::make('country_name')
                ->sortable()
                ->rules('required', 'max:255'),

            Text::make('code')
                    ->sortable()
                    ->rules('required', 'max:255'),

            Boolean::make('status')->sortable(),

            Text::make('locale')
                ->sortable()
                ->rules('required', 'max:255'),
            Select::make('currency')->options($currencies),
            HasMany::make('cities'),

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
