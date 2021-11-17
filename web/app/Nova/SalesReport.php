<?php

namespace App\Nova;

use Illuminate\Http\Request;
use Laravel\Nova\Fields\ID;
use Laravel\Nova\Fields\Number;
use Laravel\Nova\Fields\Text;
use Coroowicaksono\ChartJsIntegration\DoughnutChart;
use App\Models\Country;
use App\Models\Order;
use Illuminate\Support\Facades\DB;
use Laravel\Nova\Http\Requests\NovaRequest;
use Maatwebsite\LaravelNovaExcel\Actions\DownloadExcel;
use Laravel\Nova\Fields\DateTime;
use Zekra\TransactionsTotal\TransactionsTotal;

class SalesReport extends Resource
{

    /**
     * The model the resource corresponds to.
     *
     * @var string
     */
    public static $model = 'App\Models\SalesReport';

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
        return $query->where('type', '=', 'zekra_payments')->select('*', DB::raw('CONCAT(amount, " USD") as amount'))->toSql();
    }

    public static function detailQuery(NovaRequest $request, $query)
    {
        return $query->where('type', '=', 'zekra_payments')->select('*', DB::raw('CONCAT(amount, " USD") as amount'))->toSql();
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
            Number::make('amount', 'amount')->sortable(),
            Text::make('Country', function () {
                if(!$this->payment){
                    return null;
                }
                return $this->payment->purchased->shippingAddress->city->country->country_name;
            }),
            Text::make('City', function () {
                if(!$this->payment){
                    return null;
                }
                return $this->payment->purchased->shippingAddress->city->city_name;
            }),
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
        $countries = Country::all();
        $data = [];
        foreach ($countries as $country) {
            $randomColor[] = '#'.dechex(rand(0x000000, 0xFFFFFF));
            $countrySalesSum = Order::whereHas('shippingAddress.city', function($query) use ($country){
                $query->where('country_id', $country->id);
            })->where('payment_status', 'Paid')->get()->sum('successPayment.zekraPaymentsTransaction.amount');
            $data[] = $countrySalesSum;
        }

        $countries = $countries->pluck('country_name')->toArray();


        return [
            (new DoughnutChart())
                ->title('Sales By Region')
                ->series(array([
                    'data' => $data,
                    'backgroundColor' => $randomColor,
                ]))
                ->options([
                    'xaxis' => [
                        'categories' => $countries
                    ],
                ])->width('full'),

            (new TransactionsTotal)->type('sales-reports'),
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
