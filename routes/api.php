<?php

use App\Http\Controllers\AdminAppointmentController;
use App\Http\Controllers\AdminPaymentReceiptController;
use App\Http\Controllers\AppointmentController;
use Illuminate\Http\Request;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\ClientController;
use App\Http\Controllers\ClientPaymentController;
use App\Http\Controllers\ClinicalAppointmentController;
use App\Http\Controllers\ExamineeController;
use App\Http\Controllers\ExamineeDocumentController;
use App\Http\Controllers\ExamineeReferenceController;
use App\Http\Controllers\MenuSectionController;
use App\Http\Controllers\PsychologistScheduleController;
use Illuminate\Support\Facades\Route;

// Route::get('/user', function (Request $request) {
//     return $request->user();
// })->middleware('auth:sanctum');

Route::post('/login', [AuthController::class, 'login']);

Route::middleware('auth:sanctum')->group(function () {
	Route::get('/user', [AuthController::class, 'user']);
	Route::get('/navigation', [MenuSectionController::class, 'navigation']);
	Route::post('/logout', [AuthController::class, 'logout']);

	// Admin Routes ---------------------------------------------
	Route::middleware('role:admin, sales')->group(function () {
		Route::get('/admin/payment-receipts', [AdminPaymentReceiptController::class, 'index']);
		Route::patch('/admin/payment-receipts/{id}/status', [AdminPaymentReceiptController::class, 'updateStatus']);
	});

	// Agenda de Psicólogos (Módulo de Configuración Clínica) ------
	Route::middleware('role:admin')->group(function () {
		Route::get('/admin/psychologists-schedules', [PsychologistScheduleController::class, 'index']);
		Route::get('/admin/psychologists-schedules/{id}', [PsychologistScheduleController::class, 'getByPsychologist']);
		Route::post('/admin/psychologists-schedules/{id}/sync', [PsychologistScheduleController::class, 'sync']);


		// Módulo de Agendamiento de Citas (Administración)
		Route::prefix('admin/appointments')->group(function () {
			Route::get('/form-data', [AdminAppointmentController::class, 'formData']);
			Route::get('/slots', [AdminAppointmentController::class, 'slots']);
			Route::post('/book', [AdminAppointmentController::class, 'store']);

			Route::get('/dt', [AdminAppointmentController::class, 'dt']);
			Route::patch('/{id}/cancel', [AdminAppointmentController::class, 'cancel']);
		});

	});
	// -------------------------------------------------------------

	// Clinical Appointments Routes (Specialist Module) ------------
	Route::middleware('role:psychology,admin')->group(function () {
		Route::get('/clinical/dashboard', [ClinicalAppointmentController::class, 'dashboard']);
		Route::get('/clinical/appointments/list', [ClinicalAppointmentController::class, 'index']);
		Route::patch('/clinical/appointments/{id}/session', [ClinicalAppointmentController::class, 'updateSession']);
	});
	// -------------------------------------------------------------

	// --------------------------------------------------------------

	// Users Routes -----------------------------------------------

	Route::middleware('role:admin')->group(function () {
		Route::get('/users/dt', [\App\Http\Controllers\UserController::class, 'index']);
		Route::post('/users/store', [\App\Http\Controllers\UserController::class, 'store']);
		Route::put('/user/password', [\App\Http\Controllers\UserController::class, 'changePassword']);
		Route::delete('/users/{id}', [\App\Http\Controllers\UserController::class, 'destroy']);
		Route::get('/users/roles', [\App\Http\Controllers\UserController::class, 'roles']);
	});

	// --------------------------------------------------------------

	// Clients Routes ---------------------------------------------

	Route::middleware('role:admin,sales')->group(function () {
		Route::get('/clients', [ClientController::class, 'index']);
		Route::post('/clients/store', [ClientController::class, 'store']);
		Route::delete('/clients/{id}', [ClientController::class, 'destroy']);
		Route::post('/clients/{id}/toggle-status', [ClientController::class, 'toggleStatus']);
	});

	// Examinees Routes ---------------------------------------------
	Route::middleware('role:client')->group(function () {
		Route::get('/portal/examinees', [ExamineeController::class, 'index']);
		Route::get('/portal/examinees/list', [ExamineeController::class, 'index']);
		Route::post('/portal/examinees/store', [ExamineeController::class, 'store']);
		Route::delete('/portal/examinees/{id}', [ExamineeController::class, 'destroy']);

		// Agendar Citas (Portal Cliente) -------------
		Route::get('/portal/appointments/form-data', [AppointmentController::class, 'getFormData']);
		Route::get('/portal/appointments/slots', [AppointmentController::class, 'getSlots']);
		Route::get('/portal/appointments/list', [AppointmentController::class, 'index']);
		Route::post('/portal/appointments/book', [AppointmentController::class, 'store']);
		// -----------------------------------------------------------
	});

	// --------------------------------------------------------------

	Route::get('/clients/payment-accounts', [\App\Http\Controllers\ClientPaymentController::class, 'getPaymentAccounts']);
	Route::get('/clients/payment-receipts', [\App\Http\Controllers\ClientPaymentController::class, 'getReceipts']);
	Route::post('/clients/payment-receipts', [\App\Http\Controllers\ClientPaymentController::class, 'uploadReceipt']);

	// --------------------------------------------------------------

	// Bank Accounts Routes ---------------------------------------

	Route::middleware('role:admin, sales')->group(function () {
		Route::post('settings/bank-accounts/store', [\App\Http\Controllers\BankAccountController::class, 'store']);
	});
	Route::get('settings/bank-accounts', [\App\Http\Controllers\BankAccountController::class, 'index']);

	// --------------------------------------------------------------

	Route::middleware('role:admin,client')->group(function () {
		Route::get('/examinees/{examineeId}/documents', [ExamineeDocumentController::class, 'index']);
		Route::post('/examinees/{examineeId}/documents', [ExamineeDocumentController::class, 'store']);
		Route::patch('/examinee-documents/{id}/status', [ExamineeDocumentController::class, 'updateStatus']);
		Route::delete('/examinee-documents/{id}', [ExamineeDocumentController::class, 'destroy']);
		// -------------------------------------------------------------
		// EXPEDIENTE DIGITAL: REFERENCIAS PERSONALES (CLIENTE / STAFF)
		// -------------------------------------------------------------
		Route::get('/examinees/{examineeId}/references', [ExamineeReferenceController::class, 'index']);
		Route::post('/examinees/{examineeId}/references', [ExamineeReferenceController::class, 'store']);

		// -------------------------------------------------------------
		// AUDITORÍA TRABAJO SOCIAL Y ADMINISTRACIÓN
		// -------------------------------------------------------------
		Route::get('/social-work/dossiers/dt', [ExamineeDocumentController::class, 'dossiersDataTable']);
	});

});
