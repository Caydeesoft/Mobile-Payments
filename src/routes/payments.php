<?php
	
	use Caydeesoft\Payments\Http\Controllers\CallbackController;
	use Illuminate\Support\Facades\Route;
	
	Route::group([
		             'prefix'     => config('payments.routes.prefix', 'api'),
		             'middleware' => array_merge(config('payments.routes.middleware', ['api']), ['payments.callback.verify']),
	             ], function ()
		{
			Route::post('payments/callbacks/{provider}/{event}', [CallbackController::class, 'handle'])
			     ->name('payments.callbacks.handle');
			
			Route::post('b2bcallback', [CallbackController::class, 'mpesaB2B'])->name('payments.callbacks.mpesa.b2b');
			Route::post('b2ccallback', [CallbackController::class, 'mpesaB2C'])->name('payments.callbacks.mpesa.b2c');
			Route::post('transstatcallback', [CallbackController::class, 'mpesaTransactionStatus'])->name('payments.callbacks.mpesa.transaction-status');
			Route::post('querystkcallback', [CallbackController::class, 'mpesaStk'])->name('payments.callbacks.mpesa.stk');
			Route::post('reversalcallback', [CallbackController::class, 'mpesaReversal'])->name('payments.callbacks.mpesa.reversal');
			Route::post('accountbalballback', [CallbackController::class, 'mpesaAccountBalance'])->name('payments.callbacks.mpesa.account-balance');
			Route::post('c2bconfirmation', [CallbackController::class, 'mpesaC2BConfirmation'])->name('payments.callbacks.mpesa.c2b-confirmation');
			Route::post('c2bvalidation', [CallbackController::class, 'mpesaC2BValidation'])->name('payments.callbacks.mpesa.c2b-validation');
		});
