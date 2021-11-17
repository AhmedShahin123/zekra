<?php

namespace App\Nova;

use Illuminate\Http\Request;
use Laravel\Nova\Fields\ID;
use Laravel\Nova\Fields\Text;
use Laravel\Nova\Fields\Number;
use App\Models\Order;
use Laravel\Nova\Http\Requests\NovaRequest;

class CustomerActivity extends Resource
{
    public static $group = 'Analytics';
    /**
     * The model the resource corresponds to.
     *
     * @var string
     */
    public static $model = 'App\Models\User';

    public static $displayInNavigation = false;

    public static $defaultSort = 'id';


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

    public static function label()
    {
        return 'Activites';
    }

    public static function indexQuery(NovaRequest $request, $query)
    {
        $query->whereHas('orders', function ($query) use ($request) {
            $query->where('payment_status', 'Paid');
            return $query->orderBy(static::$defaultSort);
        });
        return $query;
    }

    public static function authorizedToCreate(Request $request)
    {
        return false;
    }
    public function authorizedToDelete(Request $request)
    {
        return false;
    }
    public function authorizedToUpdate(Request $request)
    {
        return false;
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
            Text::make('Customer Email', function () {
                return $this->email;
            })->sortable(),
            Text::make('Customer Name', function () {
                return $this->name;
            })->sortable(),
            Text::make('Customer Country', function () {
                return $this->country ? $this->country->country_name : '';
            })->sortable(),
            Text::make('Customer City', function () {
                return $this->city->city_name;
            })->sortable(),
            Number::make('Number of orders', function () {
                return count(Order::where('user_id', $this->id)->where('payment_status', 'Paid')->get());
            })->sortable('desc'),
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
