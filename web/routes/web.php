<?php

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/


Route::get('/', function () {
    return view('welcome');
});

Route::get('/createPdf', function () {
    $pdf = App::make('dompdf.wrapper');
    $pdf->setOptions(['isHtml5ParserEnabled' => true, 'isRemoteEnabled' => true])->loadHTML('<tr><td rowspan="5"><img src="'. storage_path('app/public') .'/4VYjmS5avZimage_1577650966.png" alt="Logo" height="580px"></td></tr>');
    Storage::put('public/pdf/invoice.pdf', $pdf->output());
    return $pdf->stream();
});

Route::get('/barcode', function () {
    return DNS1D::getBarcodeHTML("order1", 'C39');
});




Route::get('/test-mail', function () {
    Mail::to('abdallabagsmp@gmail.com')->send(new App\Mail\TestAmazonSes('hello From Ses!'));
});