<?php

use App\Enums\Company;
use App\Http\Controllers\Pub\RequestsController;
use App\Http\Controllers\Pub\SecurityController;
use App\Http\Controllers\Pub\TeacherController;
use App\Http\Controllers\Pub\WebReviewController;
use App\Models\Client;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Validation\Rule;

Route::prefix('requests')->controller(RequestsController::class)->group(function () {
    Route::post('/', 'store');
    Route::post('/verify', 'verify');
});

Route::apiResource('/teachers', TeacherController::class)->only('index');

Route::prefix('security')->controller(SecurityController::class)->group(function () {
    Route::post('send-code', 'sendCode');
    Route::post('verify-code', 'verifyCode');
    Route::get('passes', 'getAllPasses');
    Route::post('passes', 'usePass');
    Route::get('history', 'history');
});

Route::apiResource('reviews', WebReviewController::class)->only('index', 'show');

Route::get('macros', function (Request $request) {
    $request->validate([
        'company' => Rule::enum(Company::class),
    ]);
    $companyData = $request->input('company') === Company::ip->value ? [
        'company_name_line_1' => 'Индивидуальный предприниматель Горшкова',
        'company_name_line_2' => 'Анастасия Александровна',
        'inn' => '622709802712',
        'kpp' => '',
        'last_name' => 'Горшкова',
        'first_name' => 'Анастасия',
        'middle_name' => 'Александровна',
    ] : [
        'company_name_line_1' => 'Общество с ограниченной ответственностью',
        'company_name_line_2' => '«ЕГЭ-Центр»',
        'inn' => '9701038111',
        'kpp' => '770301001',
        'last_name' => 'Колбягина',
        'first_name' => 'Анастасия',
        'middle_name' => 'Тимофеевна',
    ];

    return view('print.spravka-'.$request->input('company'), [
        ...$companyData,

        'seq' => '1181',
        'year' => (string) current_academic_year(),
        'parent_inn' => '772647382393',
        'parent_birthday' => '1980-07-01',
        'client_passport_issued_at' => '2021-08-09',
        'client_inn' => '772099259293',
        'sum' => '134400',
        'client' => Client::find(8969),
    ]);
});
