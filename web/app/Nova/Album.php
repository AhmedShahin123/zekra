<?php

namespace App\Nova;

use Illuminate\Http\Request;
use Laravel\Nova\Fields\ID;
use Laravel\Nova\Fields\BelongsTo;
use Laravel\Nova\Fields\Text;
use Laravel\Nova\Fields\Boolean;
use Laravel\Nova\Fields\HasMany;
use Khalin\Nova\Field\Link;
use Laravel\Nova\Http\Requests\NovaRequest;

class Album extends Resource
{
    /**
     * The model the resource corresponds to.
     *
     * @var string
     */

    public static $model = 'App\Models\Album';

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
        'id','user_id','album_name','album_status','order_id',
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
            Link::make('order_id')
            ->url(function () {
                return "/admin/resources/orders/{$this->order_id}";
            }),
            Text::make('album_name')
                ->sortable()
                ->rules('required', 'max:255'),
            Link::make('Album File', 'album_pdf')
                    ->url(function () {
                        return "/storage/albums/{$this->id}/{$this->album_pdf}";
                    })
                    ->blank(),
            Boolean::make('album_status')->sortable(),
            HasMany::make('AlbumImages'),

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
