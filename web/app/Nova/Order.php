<?php

namespace App\Nova;

use Illuminate\Http\Request;
use Laravel\Nova\Fields\ID;
use Laravel\Nova\Fields\Text;
use Laravel\Nova\Fields\Boolean;
use Laravel\Nova\Fields\Number;
use Laravel\Nova\Fields\Date;
use Laravel\Nova\Fields\DateTime;
use Laravel\Nova\Fields\Select;

use Khalin\Nova\Field\Link;

use Laravel\Nova\Fields\Place;
use Laravel\Nova\Fields\HasMany;
use Laravel\Nova\Fields\BelongsTo;

use App\Nova\Actions\ReadyOrder;

use App\Models\Partner;
use App\Models\Album;
use App\Models\Courier;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Laravel\Nova\Fields\HasOne;
use Laravel\Nova\Http\Requests\NovaRequest;

use Titasgailius\SearchRelations\SearchesRelations;

class Order extends Resource
{
    use SearchesRelations;
    /**
     * The model the resource corresponds to.
     *
     * @var string
     */

    public static $model = 'App\Models\Order';

    public static $displayInNavigation = false;

    /**
     * The single value that should be used to represent the resource when being displayed.
     *
     * @var string
     */
    public static $title = 'name';

    /**
     * The columns that should be searched.
     *
     * @var array
     */
    public static $search = [
        'id','created_at'
    ];

    /**
     * The relationship columns that should be searched.
     *
     * @var array
     */
    public static $searchRelations = [
        'user'                              => ['email'],
        'user.country.country_translation'  => ['country_name'],
        'user.city.city_translation'        => ['city_name']
    ];

    public static function indexQuery(NovaRequest $request, $query)
    {
        return $query->select('*', DB::raw('CONCAT(total, " USD") as total, CONCAT(fee, " USD") as fee, CONCAT(tax, " USD") as tax'))->toSql();
    }

    public static function detailQuery(NovaRequest $request, $query)
    {
        return $query->select('*', DB::raw('CONCAT(total, " USD") as total, CONCAT(fee, " USD") as fee, CONCAT(tax, " USD") as tax'))->toSql();
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
            BelongsTo::make('Customer User', 'user', 'App\Nova\User')->hideWhenUpdating(),        
            Number::make('Shipping', 'fee')->sortable()->hideWhenUpdating(),
            Number::make('Taxes', 'tax')->sortable()->hideWhenUpdating(),
            Number::make('Total', 'total')->sortable()->hideWhenUpdating(),
            
            Text::make('Tracking #', 'id')->sortable()->hideWhenUpdating(),

            Link::make('Receipt', 'receipt_file')->url(function () {
                return $this->receipt_file ? "/storage/receipts/{$this->id}/{$this->receipt_file}" : null;
            })->blank()->hideWhenUpdating(),

            Text::make('Progress Status Date', 'progress_status_date')->sortable()->hideWhenUpdating(),
            Text::make('Delivery Status Date', 'delivery_status_date')->sortable()->hideWhenUpdating(),

            Select::make('Progress Status')->options([
                'Order Received' => 'Order Received',
                'PDF Generated' => 'PDF Generated',
                'Job Assigned to Partner' => 'Job Assigned to Partner',
                'Printed' => 'Printed',
                'Lamination' => 'Lamination',
                'UV coating' => 'UV coating',
                'Cutting' => 'Cutting',
                'Perforation' => 'Perforation',
                'Creasing' => 'Creasing',
                'Binding' => 'Binding',
                'Trimming' => 'Trimming',
                'Packing' => 'Packing',
                'Ready for Pickup' => 'Ready for Pickup',
            ]),
            Select::make('Delivery Status')->options([
                'Ready for Pickup' => 'Ready for Pickup',
                'Out to delivery' => 'Out to delivery',
                'Undelivered' => 'Undelivered',
                'Delivered' => 'Delivered',
            ]),
            Text::make('Payment Status')->hideWhenUpdating(),
            DateTime::make('created at', 'created_at')->hideWhenUpdating(),


            HasOne::make('User'),
            HasOne::make('Partner'),
            HasOne::make('Courier'),
            HasMany::make('Albums'),

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
        return [
            new Filters\OrderDate,
            new Filters\OrderPartner,
            new Filters\OrderCourier,
        ];
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
          new ReadyOrder,

        ];
    }
}
