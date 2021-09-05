<?php

use App\Http\Controllers\AdminController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\InvoiceAttachmentsController;
use App\Http\Controllers\InvoicesController;
use App\Http\Controllers\ProductsController;
use App\Http\Controllers\SectionsController;
use App\Http\Controllers\InvoicesDetailsController;
use App\Http\Controllers\InvoicesReport;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('auth.login');
});

Auth::routes(['register' => false]);

Route::get('/home', [HomeController::class, 'index'])->name('home');

Route::resource('invoices', InvoicesController::class);
Route::resource('sections', SectionsController::class);
Route::resource('products', ProductsController::class);
Route::resource('invoice-attachments', InvoiceAttachmentsController::class);

Route::get('/section/{id}', [InvoicesController::class, 'getproducts']);
Route::get('/invoices/details/{id}', [InvoicesDetailsController::class, 'show']);
Route::get('/invoices/details/view_file/{invoice_number}/{file_name}', [InvoicesDetailsController::class, 'viewFile']);
Route::get('/invoices/details/download_file/{invoice_number}/{file_name}', [InvoicesDetailsController::class, 'downloadFile']);
Route::get('/invoice-edit/{id}', [InvoicesController::class, 'edit']);
Route::get('/invoice-details-edit/{id}', [InvoicesDetailsController::class, 'edit']);
Route::get('/invoice-status/{id}', [InvoicesController::class, 'show'])->name('invoice-status');


Route::get('/paid-invoices', [InvoicesController::class, 'paidInvoices']);
Route::get('/unpaid-invoices', [InvoicesController::class, 'unpaidInvoices']);
Route::get('/partial-invoices', [InvoicesController::class, 'partialInvoices']);
Route::get('/archived-invoices', [InvoicesController::class, 'archivedInvoices']);
Route::get('/invoice-print/{id}', [InvoicesController::class, 'printInvoice']);
Route::get('/invoices-export', [InvoicesController::class, 'export']);

Route::post('/invoices/details/delete_file', [InvoicesDetailsController::class, 'destroy'])->name('delete_file');
Route::post('/invoices/details/update', [InvoicesDetailsController::class, 'update'])->name('updateInvoice_details');
Route::post('/invoice-status/update/{id}', [InvoicesController::class, 'statusUpdate'])->name('invoice-status-update');
Route::patch('/invoice-un-archive', [InvoicesController::class, 'unArchiveInvoice'])->name('invoice-unArchive');


Route::group(['middleware' => ['auth']], function() {
    Route::resource('roles', RoleController::class);
    Route::resource('users', UserController::class);
});

Route::get('/invoices-reports', [InvoicesReport::class, 'index']);
Route::post('/invoices-search', [InvoicesReport::class, 'search']);
Route::get('/mark-all-as-read', [InvoicesController::class, 'markAllAsRead']);

Route::get('/{page}', [AdminController::class, 'index']);
