<?php

namespace Database\Seeders;

use App\Models\Client;
use App\Models\MenuItem;
use App\Models\MenuSection;
use App\Models\Role;
use App\Models\RoleMenuAccess;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class SystemMenuSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Roles del Sistema
        $adminRole = Role::firstOrCreate(
            ['name' => 'admin'],
            [
                'display_name'  => 'Administración',
                'default_route' => '/home',
                'description'   => 'Administración y control total de la plataforma',
            ]
        );

        $psychologyRole = Role::firstOrCreate(
            ['name' => 'psychology'],
            [
                'display_name'  => 'Psicología',
                'default_route' => '/home',
                'description'   => 'Evaluación de candidatos, pruebas psicométricas y entrevistas',
            ]
        );

        $socialWorkRole = Role::firstOrCreate(
            ['name' => 'social_work'],
            [
                'display_name'  => 'Trabajo Social',
                'default_route' => '/home',
                'description'   => 'Estudios socioeconómicos e investigación de entorno',
            ]
        );

        $salesRole = Role::firstOrCreate(
            ['name' => 'sales'],
            [
                'display_name'  => 'Ventas',
                'default_route' => '/clients',
                'description'   => 'Área comercial, prospección y altas de clientes',
            ]
        );

        $clientRole = Role::firstOrCreate(
            ['name' => 'client'],
            [
                'display_name'  => 'Portal Cliente',
                'default_route' => '/portal/examinees',
                'description'   => 'Portal empresarial para solicitud y consulta de evaluaciones',
            ]
        );

        // 2. Secciones del Menú (Ópalo Interno)
        $secGeneral = MenuSection::firstOrCreate(
            ['title' => 'General'],
            [
                'order_index' => 1,
                'icon'        => 'mdi-view-dashboard-outline',
            ]
        );

        $secOperations = MenuSection::firstOrCreate(
            ['title' => 'Atención y Pruebas'],
            [
                'order_index' => 2,
                'icon'        => 'mdi-clipboard-text-outline',
            ]
        );

        $secAdmin = MenuSection::firstOrCreate(
            ['title' => 'Administración'],
            [
                'order_index' => 3,
                'icon'        => 'mdi-cog-outline',
            ]
        );

        // 3. Secciones del Menú (Portal Empresas)
        $secClientOps = MenuSection::firstOrCreate(
            ['title' => 'Mis Evaluaciones'],
            [
                'order_index' => 10,
                'icon'        => 'mdi-account-group-outline',
            ]
        );

        $secClientBilling = MenuSection::firstOrCreate(
            ['title' => 'Facturación y Pagos'],
            [
                'order_index' => 11,
                'icon'        => 'mdi-cash-multiple',
            ]
        );

        // 4. Módulos Internos Ópalo
        $itemHome              = MenuItem::updateOrCreate(['route' => '/home'], ['menu_section_id' => $secGeneral->id, 'title' => 'Inicio', 'icon' => 'mdi-view-dashboard-outline', 'order_index' => 1]);
        $itemClients           = MenuItem::updateOrCreate(['route' => '/clients'], ['menu_section_id' => $secGeneral->id, 'title' => 'Empresas Clientes', 'icon' => 'mdi-domain', 'order_index' => 2]);
        $itemExaminees         = MenuItem::updateOrCreate(['route' => '/examinees'], ['menu_section_id' => $secGeneral->id, 'title' => 'Evaluados', 'icon' => 'mdi-badge-account-horizontal-outline', 'order_index' => 3]);

        // Citas para Especialistas (Psicología / Trabajo Social)
        $itemSpecialistAppts   = MenuItem::updateOrCreate(['route' => '/appointments'], ['menu_section_id' => $secOperations->id, 'title' => 'Mi Agenda de Citas', 'icon' => 'mdi-calendar-clock-outline', 'order_index' => 1]);
        // Programación de Citas exclusiva para Administración
        $itemAdminAppts        = MenuItem::updateOrCreate(['route' => '/admin/appointments'], ['menu_section_id' => $secOperations->id, 'title' => 'Programar Citas', 'icon' => 'mdi-calendar-check', 'order_index' => 2]);
        $itemTests             = MenuItem::updateOrCreate(['route' => '/tests'], ['menu_section_id' => $secOperations->id, 'title' => 'Catálogo de Pruebas', 'icon' => 'mdi-file-document-edit-outline', 'order_index' => 3]);
        $itemPsychSchedules    = MenuItem::updateOrCreate(['route' => '/clinical/psychologists-schedules'], ['menu_section_id' => $secOperations->id, 'title' => 'Agenda de Psicólogos', 'icon' => 'mdi-calendar-account-outline', 'order_index' => 4]);

        // NUEVO: Módulo para Validación de Expedientes (Trabajo Social y Admin)
        $itemDossierReview     = MenuItem::updateOrCreate(['route' => '/social-work/dossiers'], ['menu_section_id' => $secOperations->id, 'title' => 'Validación de Expedientes', 'icon' => 'mdi-folder-check-outline', 'order_index' => 5]);

        $itemUsers             = MenuItem::updateOrCreate(['route' => '/users'], ['menu_section_id' => $secAdmin->id, 'title' => 'Usuarios del Sistema', 'icon' => 'mdi-account-multiple-outline', 'order_index' => 1]);
        $itemPaymentValidation = MenuItem::updateOrCreate(['route' => '/admin/payment-receipts'], ['menu_section_id' => $secAdmin->id, 'title' => 'Validación de Pagos', 'icon' => 'mdi-cash-check', 'order_index' => 2]);

        // 5. Módulos Portal Cliente
        $itemMyExaminees  = MenuItem::updateOrCreate(['route' => '/portal/examinees'], ['menu_section_id' => $secClientOps->id, 'title' => 'Mis Evaluados', 'icon' => 'mdi-account-tie-outline', 'order_index' => 1]);
        $itemTracking     = MenuItem::updateOrCreate(['route' => '/portal/tracking'], ['menu_section_id' => $secClientOps->id, 'title' => 'Avance y Estatus', 'icon' => 'mdi-progress-check', 'order_index' => 2]);
        $itemFiscalData   = MenuItem::updateOrCreate(['route' => '/portal/fiscal-data'], ['menu_section_id' => $secClientBilling->id, 'title' => 'Datos Fiscales (CSF)', 'icon' => 'mdi-file-pdf-box', 'order_index' => 1]);
        $itemBankAccounts = MenuItem::updateOrCreate(['route' => '/portal/payment-methods'], ['menu_section_id' => $secClientBilling->id, 'title' => 'Cuentas Bancarias', 'icon' => 'mdi-bank-outline', 'order_index' => 2]);
        $itemReceipts     = MenuItem::updateOrCreate(['route' => '/portal/payment-receipts'], ['menu_section_id' => $secClientBilling->id, 'title' => 'Comprobantes de Pago', 'icon' => 'mdi-file-upload-outline', 'order_index' => 3]);

        // Eliminar del menú la ruta antigua del cliente
        MenuItem::where('route', '/portal/appointments')->delete();

        // Limpiar accesos previos para reasignar ordenadamente
        RoleMenuAccess::truncate();

        // Helper de asignación
        $assign = function ($roleId, $itemId, $crud = 'C,R,U,D') {
            RoleMenuAccess::create([
                'role_id'      => $roleId,
                'menu_item_id' => $itemId,
                'permissions'  => $crud,
            ]);
        };

        // 6. Asignación de Permisos por Rol
        // Administrador: Acceso total incluyendo la validación de expedientes
        foreach ([$itemHome, $itemUsers, $itemPaymentValidation, $itemClients, $itemExaminees, $itemAdminAppts, $itemTests, $itemPsychSchedules, $itemDossierReview] as $item) {
            $assign($adminRole->id, $item->id, 'C,R,U,D,I,E,P');
        }

        // Psicología: Inicio, Su Agenda de Citas (/appointments), Evaluados y Pruebas
        foreach ([$itemHome, $itemSpecialistAppts, $itemExaminees, $itemTests] as $item) {
            $assign($psychologyRole->id, $item->id, 'C,R,U');
        }

        // Trabajo Social: Inicio, Su Agenda de Citas (/appointments), Evaluados y NUEVA Validación de Expedientes
        foreach ([$itemHome, $itemSpecialistAppts, $itemExaminees, $itemDossierReview] as $item) {
            $assign($socialWorkRole->id, $item->id, 'C,R,U');
        }

        // Ventas: Clientes, Evaluados y Pagos
        foreach ([$itemHome, $itemClients, $itemExaminees, $itemPaymentValidation] as $item) {
            $assign($salesRole->id, $item->id, 'C,R,U');
        }

        // Portal Cliente: Registra candidatos, llena expediente, ve semáforo y sube comprobante de pago
        foreach ([$itemMyExaminees, $itemTracking, $itemFiscalData, $itemBankAccounts, $itemReceipts] as $item) {
            $assign($clientRole->id, $item->id, 'C,R,U');
        }

        // 7. Cuentas de Acceso Iniciales
        User::firstOrCreate(
            ['email' => 'admin@opalo.com'],
            [
                'name'      => 'Administrador Ópalo',
                'password'  => Hash::make('password'),
                'role_id'   => $adminRole->id,
                'client_id' => null,
            ]
        );

        User::firstOrCreate(
            ['email' => 'ventas@opalo.com'],
            [
                'name'      => 'Asesor Ventas Ópalo',
                'password'  => Hash::make('password'),
                'role_id'   => $salesRole->id,
                'client_id' => null,
            ]
        );

        User::firstOrCreate(
            ['email' => 'psicologia@opalo.com'],
            [
                'name'      => 'Lic. Mariana Vázquez Gómez',
                'password'  => Hash::make('password'),
                'role_id'   => $psychologyRole->id,
                'client_id' => null,
            ]
        );

        User::firstOrCreate(
            ['email' => 'psicologia2@opalo.com'],
            [
                'name'      => 'Lic. Roberto Sánchez Ramos',
                'password'  => Hash::make('password'),
                'role_id'   => $psychologyRole->id,
                'client_id' => null,
            ]
        );

        // Cuenta demo para Trabajo Social
        User::firstOrCreate(
            ['email' => 'trabajosocial@opalo.com'],
            [
                'name'      => 'Lic. Laura Elena Peña',
                'password'  => Hash::make('password'),
                'role_id'   => $socialWorkRole->id,
                'client_id' => null,
            ]
        );

        // 8. Cliente Demo
        $demoClient1 = Client::firstOrCreate(
            ['rfc' => 'LTN180512AB3'],
            [
                'company_name'    => 'Logística y Transportes del Norte S.A. de C.V.',
                'trade_name'      => 'TransNorte',
                'contact_email'   => 'rh@transnorte.com',
                'contact_phone'   => '3312345678',
                'fiscal_regime'   => '601 - General de Ley Personas Morales',
                'fiscal_zip_code' => '45100',
            ]
        );

        User::firstOrCreate(
            ['email' => 'rh@transnorte.com'],
            [
                'name'      => 'Recursos Humanos TransNorte',
                'password'  => Hash::make('password'),
                'role_id'   => $clientRole->id,
                'client_id' => $demoClient1->id,
            ]
        );
    }
}
