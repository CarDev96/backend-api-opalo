<?php

use App\Http\Controllers\AdminAppointmentController;
use App\Http\Controllers\AdminDashboardController;
use App\Http\Controllers\AdminEvaluationOrderController;
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
use App\Http\Controllers\UserController;
use App\Http\Controllers\BankAccountController;
use App\Http\Controllers\ClientFiscalDocumentController;
use App\Http\Controllers\EvaluationOrderController;
use App\Http\Controllers\PsychologistScheduleController;
use App\Http\Controllers\PsychometricTestController;
use Illuminate\Support\Facades\Route;

Route::post('/login', [AuthController::class, 'login']);

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/user', [AuthController::class, 'user']);
    Route::get('/navigation', [MenuSectionController::class, 'navigation']);
    Route::post('/logout', [AuthController::class, 'logout']);

    // Catálogo y Administración de Tipos de Evaluación
    Route::get('/evaluation-types', [\App\Http\Controllers\EvaluationTypeController::class, 'index']);
    Route::post('/evaluation-types', [\App\Http\Controllers\EvaluationTypeController::class, 'store']);
    Route::get('/evaluation-types/{id}', [\App\Http\Controllers\EvaluationTypeController::class, 'show']);
    Route::put('/evaluation-types/{id}', [\App\Http\Controllers\EvaluationTypeController::class, 'update']);
    Route::delete('/evaluation-types/{id}', [\App\Http\Controllers\EvaluationTypeController::class, 'destroy']);
    Route::patch('/evaluation-types/{id}/toggle-status', [\App\Http\Controllers\EvaluationTypeController::class, 'toggleStatus']);

    // Órdenes de Evaluación y Comprobantes (Portal Cliente)
    Route::get('/portal/evaluation-orders', [\App\Http\Controllers\EvaluationOrderController::class, 'index']);
    Route::post('/portal/evaluation-orders', [\App\Http\Controllers\EvaluationOrderController::class, 'store']);
    Route::post('/portal/evaluation-orders/{id}/receipt', [\App\Http\Controllers\EvaluationOrderController::class, 'uploadReceipt']);

    // Validación de Pagos (Administración)
    Route::patch('/admin/evaluation-orders/{id}/status', [\App\Http\Controllers\EvaluationOrderController::class, 'updatePaymentStatus']);

    // Admin & Sales Routes ---------------------------------------------
    Route::middleware('role:admin, sales')->group(function () {
        Route::get('/admin/payment-receipts', [AdminPaymentReceiptController::class, 'index']);
        Route::patch('/admin/payment-receipts/{id}/status', [AdminPaymentReceiptController::class, 'updateStatus']);
    });

    // Agenda de Psicólogos (Módulo de Configuración Clínica) ------
    Route::middleware('role:admin')->group(function () {
        Route::get('/admin/dashboard/stats', [AdminDashboardController::class, 'stats']);

        Route::get('/admin/psychologists-schedules', [PsychologistScheduleController::class, 'index']);
        Route::get('/admin/psychologists-schedules/{id}', [PsychologistScheduleController::class, 'getByPsychologist']);
        Route::post('/admin/psychologists-schedules/{id}/sync', [PsychologistScheduleController::class, 'sync']);

        Route::put('/clients/{id}', [App\Http\Controllers\ClientController::class, 'update']);
    });

    // Clinical Appointments Routes (Specialist Module) ------------
    Route::middleware('role:psychology,admin')->group(function () {
        Route::get('/clinical/dashboard', [ClinicalAppointmentController::class, 'dashboard']);
        Route::get('/clinical/appointments/list', [ClinicalAppointmentController::class, 'index']);
        Route::patch('/clinical/appointments/{id}/session', [ClinicalAppointmentController::class, 'updateSession']);
        Route::patch('/examinee-documents/{id}/status', [ExamineeDocumentController::class, 'updateStatus']);
        Route::patch('/examinee-psychometric-tests/{id}/status', [ExamineeDocumentController::class, 'updatePsychometricStatus']);

        // Catálogo de Pruebas Psicométricas (Administración)
        Route::prefix('admin/psychometric-tests')->group(function () {
            Route::get('/', [PsychometricTestController::class, 'index']);
            Route::post('/', [PsychometricTestController::class, 'store']);
            Route::post('/{id}', [PsychometricTestController::class, 'update']);
            Route::patch('/{id}/toggle-status', [PsychometricTestController::class, 'toggleStatus']);
            Route::delete('/{id}', [PsychometricTestController::class, 'destroy']);
        });
    });

    // Users Routes -----------------------------------------------
    Route::middleware('role:admin')->group(function () {
        Route::get('/users/dt', [\App\Http\Controllers\UserController::class, 'index']);
        Route::post('/users/store', [\App\Http\Controllers\UserController::class, 'store']);
        Route::put('/users/{id}', [App\Http\Controllers\UserController::class, 'update']);
        Route::put('/user/password', [\App\Http\Controllers\UserController::class, 'changePassword']);
        Route::delete('/users/{id}', [\App\Http\Controllers\UserController::class, 'destroy']);
        Route::get('/users/roles', [\App\Http\Controllers\UserController::class, 'roles']);
    });

    // Clients, Orders & Appointments Routes (Admin y Ventas) -------
    Route::middleware('role:admin,sales')->group(function () {
        Route::get('/clients', [ClientController::class, 'index']);
        Route::post('/clients/store', [ClientController::class, 'store']);
        Route::delete('/clients/{id}', [ClientController::class, 'destroy']);
        Route::post('/clients/{id}/toggle-status', [ClientController::class, 'toggleStatus']);

        // Módulo Administrativo: Generación de Órdenes, Listado, Stream S3 y Validación
        Route::get('/admin/evaluation-orders/form-data', [AdminEvaluationOrderController::class, 'formData']);
        Route::get('/admin/evaluation-orders', [AdminEvaluationOrderController::class, 'index']);
        Route::post('/admin/evaluation-orders', [AdminEvaluationOrderController::class, 'store']);
        Route::post('/admin/evaluation-orders/{id}/receipt', [AdminEvaluationOrderController::class, 'uploadReceipt']);
        Route::get('/admin/evaluation-orders/{id}/receipt/stream', [AdminEvaluationOrderController::class, 'streamReceipt']);
        Route::post('/admin/evaluation-orders/{id}/validate-payment', [AdminEvaluationOrderController::class, 'validatePayment']);

        // Módulo de Agendamiento de Citas (Administración y Ventas)
        Route::prefix('admin/appointments')->group(function () {
            Route::get('/form-data', [AdminAppointmentController::class, 'formData']);
            Route::get('/slots', [AdminAppointmentController::class, 'slots']);
            Route::post('/book', [AdminAppointmentController::class, 'store']);
            Route::get('/eligible-examinees', [\App\Http\Controllers\AppointmentController::class, 'getEligibleExaminees']);
            Route::get('/dt', [AdminAppointmentController::class, 'dt']);
            Route::patch('/{id}/cancel', [AdminAppointmentController::class, 'cancel']);
        });
    });

    // Examinees & Portal Cliente Routes ---------------------------
    Route::middleware('role:client')->group(function () {
        Route::get('/portal/examinees', [ExamineeController::class, 'index']);
        Route::get('/portal/examinees/list', [ExamineeController::class, 'index']);
        Route::post('/portal/examinees/store', [ExamineeController::class, 'store']);
        Route::delete('/portal/examinees/{id}', [ExamineeController::class, 'destroy']);

        // Agendar Citas (Portal Cliente)
        Route::get('/portal/appointments/form-data', [AppointmentController::class, 'getFormData']);
        Route::get('/portal/appointments/slots', [AppointmentController::class, 'getSlots']);
        Route::get('/portal/appointments/list', [AppointmentController::class, 'index']);
        Route::post('/portal/appointments/book', [AppointmentController::class, 'store']);

        // Módulo Datos Fiscales (Portal Cliente)
        Route::get('/portal/fiscal-data', [\App\Http\Controllers\ClientFiscalDocumentController::class, 'show']);
        Route::post('/portal/fiscal-data', [\App\Http\Controllers\ClientFiscalDocumentController::class, 'store']);

        // Modulo de psicometricos bajar pruebas
        Route::get('/portal/psychometric-tests', [\App\Http\Controllers\ClientPsychometricTestController::class, 'index']);

        Route::prefix('portal/examinees/{examineeId}/psychometric-tests')->group(function () {
            Route::get('/', [\App\Http\Controllers\ExamineePsychometricTestController::class, 'index']);
            Route::post('/{testId}/upload', [\App\Http\Controllers\ExamineePsychometricTestController::class, 'upload']);
        });
    });

    Route::get('/clients/payment-accounts', [\App\Http\Controllers\ClientPaymentController::class, 'getPaymentAccounts']);
    Route::get('/clients/payment-receipts', [\App\Http\Controllers\ClientPaymentController::class, 'getReceipts']);
    Route::post('/clients/payment-receipts', [\App\Http\Controllers\ClientPaymentController::class, 'uploadReceipt']);

    // Bank Accounts Routes ---------------------------------------
    Route::middleware('role:admin, sales')->group(function () {
        Route::post('settings/bank-accounts/store', [\App\Http\Controllers\BankAccountController::class, 'store']);
    });
    Route::get('settings/bank-accounts', [\App\Http\Controllers\BankAccountController::class, 'index']);

    // Expedientes Digitales, Recordatorios y Cierre de Evaluación --
    Route::middleware('role:admin,client,psychology,social_work')->group(function () {
        Route::get('/examinees/{examineeId}/documents', [ExamineeDocumentController::class, 'index']);
        Route::post('/examinees/{examineeId}/documents', [ExamineeDocumentController::class, 'store']);
        Route::patch('/examinee-documents/{id}/status', [ExamineeDocumentController::class, 'updateStatus']);
        Route::delete('/examinee-documents/{id}', [ExamineeDocumentController::class, 'destroy']);

        Route::get('/examinees/{examineeId}/references', [ExamineeReferenceController::class, 'index']);
        Route::post('/examinees/{examineeId}/references', [ExamineeReferenceController::class, 'store']);

        Route::get('/social-work/dossiers/dt', [ExamineeDocumentController::class, 'dossiersDataTable']);
        Route::patch('/examinees/{examineeId}/complete', [ExamineeDocumentController::class, 'completeEvaluation']);
        Route::post('/examinees/{examineeId}/remind-dossier', [ExamineeDocumentController::class, 'sendDossierReminder']);
    });
});
