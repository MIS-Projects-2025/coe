
<?php

use App\Http\Controllers\CoeRecordController;
use App\Http\Middleware\AuthMiddleware;
use Illuminate\Support\Facades\Route;

$app_name = env('APP_NAME', '');

Route::redirect('/', "/$app_name");

Route::prefix($app_name)->middleware(AuthMiddleware::class)->group(function () {
    // COE Records Index (Table View)
    Route::get('/coe-records', [CoeRecordController::class, 'index'])->name('coe-records.index');

    // COE Request Form
    Route::get('/coe-records/create', [CoeRecordController::class, 'create'])->name('coe-records.create');

    // Store COE Request
    Route::post('/coe-record', [CoeRecordController::class, 'store'])->name('coe-record.store');

    // Update COE Status
    Route::put('/coe-record/{id}/status', [CoeRecordController::class, 'updateStatus'])->name('coe-record.update-status');

    // Delete COE Request
    Route::delete('/coe-record/{id}', [CoeRecordController::class, 'destroy'])->name('coe-record.destroy');

    // Fetch employee/salary data needed to preview & print a COE
    Route::get('/coe-record/{id}/generate-data', [CoeRecordController::class, 'generateData'])->name('coe-record.generate-data');

    // Get attachments for a COE record
    Route::get('/coe-record/{id}/attachments', [CoeRecordController::class, 'getAttachments'])->name('coe-record.attachments');

    // Bulk update status for multiple COE records
    Route::put('/coe-records/bulk-status', [CoeRecordController::class, 'bulkUpdateStatus'])->name('coe-records.bulk-status');
});
