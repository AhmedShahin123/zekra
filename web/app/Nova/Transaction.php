<?php

namespace App\Nova;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Laravel\Nova\Fields\ID;
use Laravel\Nova\Fields\Number;
use Laravel\Nova\Fields\Text;
use Laravel\Nova\Fields\BelongsTo;
use Laravel\Nova\Fields\DateTime;
use Laravel\Nova\Http\Requests\NovaRequest;
use Maatwebsite\LaravelNovaExcel\Actions\DownloadExcel;
use Zekra\TransactionsTotal\TransactionsTotal;

class Transaction extends Resource
{
    /**
     * The model the resource corresponds to.
     *
     * @var string
     */
    public static $model = 'App\Models\Transaction';

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
        'id', 'type', 'amount', 'created_at'
    ];

    public static function indexQuery(NovaRequest $request, $query)
    {
        return $query->select('*', DB::raw('CONCAT(amount, " USD") as amount, REPLACE(type, "_", " ") as type'));
    }

    public static function detailQuery(NovaRequest $request, $query)
    {
        return $query->select('*', DB::raw('CONCAT(amount, " USD") as amount, REPLACE(type, "_", " ") as type'));
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
            BelongsTo::make('Payment')->nullable()->exceptOnForms(),
            Text::make('type', 'type')->sortable()->exceptOnForms(),
            Number::make('amount', 'amount')->sortable(),
            DateTime::make('Date', 'created_at')->sortable(),
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
        return [
            (new TransactionsTotal)->type('transactions')
        ];
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
          new Filters\Transactions\TransactionDate,
          new Filters\Transactions\TransactionType,
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
          (new DownloadExcel)->withHeadings()
      ];
    }
}
