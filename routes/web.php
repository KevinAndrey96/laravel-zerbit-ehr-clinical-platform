<?php

use App\Http\Controllers\Admin;
use App\Http\Controllers\AppointmentController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\PasswordController;
use App\Http\Controllers\Clinical\ClinicalHistoryController;
use App\Http\Controllers\Clinical\EvolutionController;
use App\Http\Controllers\Clinical\SignatureController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\LabSampleController;
use App\Http\Controllers\PatientController;
use App\Http\Controllers\PortalController;
use App\Models\Appointment;
use App\Models\ClinicalHistory;
use Illuminate\Support\Facades\Route;

Route::view('/', 'welcome')->name('home');

Route::middleware('guest')->group(function (): void {
    Route::get('/ingresar', [LoginController::class, 'create'])->name('login');
    Route::post('/ingresar', [LoginController::class, 'store'])->middleware('throttle:login');
});

Route::prefix('portal')->name('portal.')->controller(PortalController::class)->group(function (): void {
    Route::get('/', 'login')->name('login');
    Route::post('/enlace', 'sendLink')->middleware('throttle:portal-link')->name('link');
    Route::get('/entrar/{patient}', 'enter')->middleware('signed')->name('enter');
    Route::middleware('portal')->group(function (): void {
        Route::get('/inicio', 'home')->name('home');
        Route::get('/resultados/{sample}', 'download')->name('download');
        Route::post('/salir', 'logout')->name('logout');
    });
});

Route::middleware('auth')->group(function (): void {
    Route::post('/salir', [LoginController::class, 'destroy'])->name('logout');
    Route::get('/cuenta/clave', [PasswordController::class, 'edit'])->name('password.edit');
    Route::put('/cuenta/clave', [PasswordController::class, 'update'])->name('password.update');
});

Route::middleware(['auth', 'role'])->group(function (): void {
    Route::get('/panel', DashboardController::class)->name('dashboard');

    Route::resource('pacientes', PatientController::class)
        ->except('destroy')->names('patients')->parameters(['pacientes' => 'patient']);

    Route::get('/agenda', [AppointmentController::class, 'index'])->name('appointments.index');
    Route::patch('/citas/{appointment}/estado', [AppointmentController::class, 'status'])
        ->name('appointments.status')->can('update', 'appointment');
});

Route::middleware(['auth', 'role:administrador'])->group(function (): void {
    Route::get('/pacientes/{patient}/accesos', [PatientController::class, 'accessLog'])->name('patients.access-log');

    Route::get('/citas/nueva', [AppointmentController::class, 'create'])->name('appointments.create')->can('create', Appointment::class);
    Route::post('/citas', [AppointmentController::class, 'store'])->name('appointments.store')->can('create', Appointment::class);
    Route::post('/citas/{appointment}/recordatorio', [AppointmentController::class, 'remind'])
        ->name('appointments.remind')->can('remind', 'appointment');

    Route::prefix('laboratorio')->name('lab.')->controller(LabSampleController::class)->group(function (): void {
        Route::get('/', 'index')->name('index');
        Route::get('/nueva', 'create')->name('create');
        Route::post('/', 'store')->name('store');
        Route::get('/{sample}', 'show')->name('show');
        Route::post('/{sample}/analisis', 'analyse')->name('analyse');
        Route::post('/{sample}/resultado', 'result')->name('result');
        Route::post('/{sample}/entrega', 'deliver')->name('deliver');
        Route::get('/{sample}/resultado', 'download')->name('download');
    });

    Route::prefix('admin')->name('admin.')->group(function (): void {
        Route::resource('personal', Admin\StaffController::class)
            ->except(['show', 'destroy'])->names('staff')->parameters(['personal' => 'staff']);
        Route::resource('aseguradoras', Admin\InsurerController::class)
            ->except(['show', 'destroy'])->names('insurers')->parameters(['aseguradoras' => 'insurer']);
    });
});

Route::middleware(['auth', 'role:fisioterapeuta'])->group(function (): void {
    Route::get('/historias', [ClinicalHistoryController::class, 'index'])->name('histories.index')->can('viewAny', ClinicalHistory::class);
    Route::get('/pacientes/{patient}/historias/nueva', [ClinicalHistoryController::class, 'create'])->name('histories.create')->can('create', ClinicalHistory::class);
    Route::post('/pacientes/{patient}/historias', [ClinicalHistoryController::class, 'store'])->name('histories.store')->can('create', ClinicalHistory::class);
    Route::get('/historias/{history}', [ClinicalHistoryController::class, 'show'])->name('histories.show')->can('view', 'history');
    Route::get('/historias/{history}/pdf', [ClinicalHistoryController::class, 'pdf'])->name('histories.pdf')->can('view', 'history');
    Route::get('/historias/{history}/editar', [ClinicalHistoryController::class, 'edit'])->name('histories.edit')->can('update', 'history');
    Route::put('/historias/{history}', [ClinicalHistoryController::class, 'update'])->name('histories.update')->can('update', 'history');
    Route::post('/historias/{history}/alta', [ClinicalHistoryController::class, 'discharge'])->name('histories.discharge')->can('discharge', 'history');

    Route::post('/historias/{history}/sesiones', [EvolutionController::class, 'store'])->name('evolutions.store')->can('evolve', 'history');
    Route::post('/historias/{history}/sesiones/{evolution}/firma', [EvolutionController::class, 'sign'])
        ->name('evolutions.sign')->scopeBindings()->can('sign', 'evolution');

    Route::get('/mi-firma', [SignatureController::class, 'edit'])->name('signature.edit');
    Route::put('/mi-firma', [SignatureController::class, 'update'])->name('signature.update');
});

Route::permanentRedirect('/login', '/ingresar');
Route::permanentRedirect('/home', '/panel');
