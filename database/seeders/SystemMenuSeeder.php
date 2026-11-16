<?php

namespace Database\Seeders;

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
        // =========================================================================
        // 1. ROLES DEL SISTEMA (Todos con default_route => '/home')
        // =========================================================================
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
                'default_route' => '/home',
                'description'   => 'Área comercial, prospección y altas de clientes',
            ]
        );

        $clientRole = Role::firstOrCreate(
            ['name' => 'client'],
            [
                'display_name'  => 'Portal Cliente',
                'default_route' => '/home',
                'description'   => 'Portal empresarial para solicitud y consulta de evaluaciones',
            ]
        );

        // Asegurar que todos tengan /home como default_route
        Role::whereIn('name', ['admin', 'psychology', 'social_work', 'sales', 'client'])
            ->update(['default_route' => '/home']);

        // =========================================================================
        // 2. SECCIONES DEL MENÚ (Ópalo Interno)
        // =========================================================================
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

        // =========================================================================
        // 3. SECCIONES DEL MENÚ (Portal Clientes / Empresas)
        // =========================================================================
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

        // =========================================================================
        // 4. MÓDULOS INTERNOS ÓPALO (Admin, Psicología, Trabajo Social, Ventas)
        // =========================================================================
        $itemHome           = MenuItem::updateOrCreate(['route' => '/home'], ['menu_section_id' => $secGeneral->id, 'title' => 'Home', 'icon' => 'mdi-home-outline', 'order_index' => 1]);
        $itemClients        = MenuItem::updateOrCreate(['route' => '/clients'], ['menu_section_id' => $secGeneral->id, 'title' => 'Empresas Clientes', 'icon' => 'mdi-domain', 'order_index' => 2]);
        $itemExaminees      = MenuItem::updateOrCreate(['route' => '/examinees'], ['menu_section_id' => $secGeneral->id, 'title' => 'Evaluados', 'icon' => 'mdi-badge-account-horizontal-outline', 'order_index' => 3]);

        // Citas operativas para especialistas (Psicología / Trabajo Social)
        $itemSpecialistAppts  = MenuItem::updateOrCreate(['route' => '/appointments'], ['menu_section_id' => $secOperations->id, 'title' => 'Mi Agenda de Citas', 'icon' => 'mdi-calendar-clock-outline', 'order_index' => 1]);
        // Programación y asignación de Citas general
        $itemAdminAppts       = MenuItem::updateOrCreate(['route' => '/admin/appointments'], ['menu_section_id' => $secOperations->id, 'title' => 'Citas', 'icon' => 'mdi-calendar-check', 'order_index' => 2]);
        $itemTests            = MenuItem::updateOrCreate(['route' => '/admin/tests-catalog'], ['menu_section_id' => $secOperations->id, 'title' => 'Catálogo de Pruebas', 'icon' => 'mdi-file-document-edit-outline', 'order_index' => 3]);
        $itemPsychSchedules   = MenuItem::updateOrCreate(['route' => '/clinical/psychologists-schedules'], ['menu_section_id' => $secOperations->id, 'title' => 'Agenda de Psicólogos', 'icon' => 'mdi-calendar-account-outline', 'order_index' => 4]);

        // Validación de Expedientes (Trabajo Social, Psicología y Admin)
        $itemDossierReview    = MenuItem::updateOrCreate(['route' => '/social-work/dossiers'], ['menu_section_id' => $secOperations->id, 'title' => 'Validación de Expedientes', 'icon' => 'mdi-folder-check-outline', 'order_index' => 5]);

        // Administración del Sistema
        $itemUsers            = MenuItem::updateOrCreate(['route' => '/users'], ['menu_section_id' => $secAdmin->id, 'title' => 'Usuarios del Sistema', 'icon' => 'mdi-account-multiple-outline', 'order_index' => 1]);

        // Generación de Órdenes de Evaluación para Admin y Ventas
        $itemAdminCreateOrder = MenuItem::updateOrCreate(['route' => '/admin/orders'], ['menu_section_id' => $secAdmin->id, 'title' => 'Generar Orden de Evaluación', 'icon' => 'mdi-cash-plus', 'order_index' => 2]);

        // =========================================================================
        // 5. MÓDULOS PORTAL CLIENTE
        // =========================================================================
        $itemMyExaminees         = MenuItem::updateOrCreate(['route' => '/portal/examinees'], ['menu_section_id' => $secClientOps->id, 'title' => 'Mis Evaluados', 'icon' => 'mdi-account-tie-outline', 'order_index' => 1]);
        $itemTracking            = MenuItem::updateOrCreate(['route' => '/portal/tracking'], ['menu_section_id' => $secClientOps->id, 'title' => 'Avance y Estatus', 'icon' => 'mdi-progress-check', 'order_index' => 2]);
        $itemClientPsychometrics = MenuItem::updateOrCreate(['route' => '/portal/psychometric-tests'], ['menu_section_id' => $secClientOps->id, 'title' => 'Pruebas Psicométricas', 'icon' => 'mdi-file-document-multiple-outline', 'order_index' => 3]);
        $itemValidateCerts       = MenuItem::updateOrCreate(['route' => 'https://www.opalosoft.app/web/login'], ['menu_section_id' => $secClientOps->id, 'title' => 'Validar Certificados', 'icon' => 'mdi-certificate-outline', 'order_index' => 4]);

        $itemCreateOrder         = MenuItem::updateOrCreate(['route' => '/portal/orders'], ['menu_section_id' => $secClientBilling->id, 'title' => 'Generar Orden de Evaluación', 'icon' => 'mdi-cash-plus', 'order_index' => 1]);
        $itemFiscalData          = MenuItem::updateOrCreate(['route' => '/portal/fiscal-data'], ['menu_section_id' => $secClientBilling->id, 'title' => 'Datos Fiscales (CSF)', 'icon' => 'mdi-file-pdf-box', 'order_index' => 2]);
        $itemBankAccounts        = MenuItem::updateOrCreate(['route' => '/portal/payment-methods'], ['menu_section_id' => $secClientBilling->id, 'title' => 'Cuentas Bancarias', 'icon' => 'mdi-bank-outline', 'order_index' => 3]);

        // Depurar rutas obsoletas o redundantes del menú (incluyendo la antigua vista de órdenes y citas general)
        MenuItem::whereIn('route', ['/admin/payment-receipts', '/portal/appointments', '/portal/payment-receipts', '/admin/orders-validation'])->delete();

        // =========================================================================
        // 6. ASIGNACIÓN DE ACCESOS Y PERMISOS POR ROL
        // =========================================================================
        RoleMenuAccess::truncate();

        $assign = function ($roleId, $itemId, $crud = 'C,R,U,D') {
            RoleMenuAccess::create([
                'role_id'      => $roleId,
                'menu_item_id' => $itemId,
                'permissions'  => $crud,
            ]);
        };

        // Administrador: Control total sin el menú redundante de órdenes y citas
        foreach ([
            $itemHome,
            $itemUsers,
            $itemAdminCreateOrder,
            $itemClients,
            $itemAdminAppts,
            $itemTests,
            $itemPsychSchedules,
            $itemDossierReview
        ] as $item) {
            $assign($adminRole->id, $item->id, 'C,R,U,D,I,E,P');
        }

        // Psicología: Home, Agenda personal, Catálogo de Pruebas y Validación de Expedientes
        foreach ([
            $itemHome,
            $itemSpecialistAppts,
            $itemTests,
            $itemDossierReview
        ] as $item) {
            $assign($psychologyRole->id, $item->id, 'C,R,U');
        }

        // Trabajo Social: Home, Agenda personal de citas, Evaluados y Expedientes
        foreach ([$itemHome, $itemSpecialistAppts, $itemDossierReview] as $item) {
            $assign($socialWorkRole->id, $item->id, 'C,R,U');
        }

        // Ventas: Home, Clientes y Generar Orden de Evaluación (sin órdenes y citas)
        foreach ([
            $itemHome,
            $itemClients,
            $itemAdminCreateOrder
        ] as $item) {
            $assign($salesRole->id, $item->id, 'C,R,U');
        }

        // Portal Cliente: Home, Mis Evaluados, Pruebas Psicométricas, Validar Certificados y Facturación
        foreach ([
            $itemHome,
            $itemMyExaminees,
            $itemClientPsychometrics,
            $itemValidateCerts,
            $itemCreateOrder,
            $itemFiscalData,
            $itemBankAccounts
        ] as $item) {
            $assign($clientRole->id, $item->id, 'C,R,U');
        }

        // =========================================================================
        // 7. CUENTAS DE ACCESO DEL STAFF CON DATOS REALES
        // =========================================================================

        // Perfil de Administración
        User::updateOrCreate(
            ['email' => 'administracion@sistemaopalo.com.mx'],
            [
                'name'      => 'Administrador General Ópalo',
                'password'  => Hash::make('password'),
                'role_id'   => $adminRole->id,
                'client_id' => null,
            ]
        );

        // Perfiles de Psicología
        User::updateOrCreate(
            ['email' => 'mayra.rodriguez@sistemaopalo.com.mx'],
            [
                'name'      => 'Lic. Mayra Rodriguez',
                'phone'     => '3322482182',
                'password'  => Hash::make('password'),
                'role_id'   => $psychologyRole->id,
                'client_id' => null,
            ]
        );

        User::updateOrCreate(
            ['email' => 'flor.morales@sistemaopalo.com.mx'],
            [
                'name'      => 'Lic. Flor Morales',
                'phone'     => '3322482259',
                'password'  => Hash::make('password'),
                'role_id'   => $psychologyRole->id,
                'client_id' => null,
            ]
        );

        User::updateOrCreate(
            ['email' => 'mariana.piottowsky@sistemaopalo.com.mx'],
            [
                'name'      => 'Lic. Mariana Piottowsky',
                'phone'     => '5513599872',
                'password'  => Hash::make('password'),
                'role_id'   => $psychologyRole->id,
                'client_id' => null,
            ]
        );
    }
}
