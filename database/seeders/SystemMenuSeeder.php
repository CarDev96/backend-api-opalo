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
        $adminRole = Role::create([
            'name' => 'admin',
            'display_name' => 'Administración',
            'default_route' => '/users',
            'description' => 'Administración total de la plataforma',
        ]);

        $psychologyRole = Role::create([
            'name' => 'psychology',
            'display_name' => 'Psicología',
            'default_route' => '/appointments',
            'description' => 'Pruebas psicométricas y entrevistas',
        ]);

        $socialWorkRole = Role::create([
            'name' => 'social_work',
            'display_name' => 'Trabajo Social',
            'default_route' => '/appointments',
            'description' => 'Entrevistas socioeconómicas e investigaciones de entorno',
        ]);

        $salesRole = Role::create([
            'name' => 'sales',
            'display_name' => 'Ventas',
            'default_route' => '/clients',
            'description' => 'Comercial, prospectos y altas de empresas',
        ]);

        $clientRole = Role::create([
            'name' => 'client',
            'display_name' => 'Portal Cliente',
            'default_route' => '/portal/examinees',
            'description' => 'Portal de autoservicio para empresas contratantes',
        ]);

        // 2. Secciones del Menú (Ópalo Interno)
        $secGeneral = MenuSection::create([
            'title' => 'General',
            'order_index' => 1,
            'icon' => 'mdi-view-dashboard-outline',
        ]);

        $secClinical = MenuSection::create([
            'title' => 'Atención y Pruebas',
            'order_index' => 2,
            'icon' => 'mdi-clipboard-text-outline',
        ]);

        $secAdmin = MenuSection::create([
            'title' => 'Administración',
            'order_index' => 3,
            'icon' => 'mdi-cog-outline',
        ]);

        // 3. Secciones del Menú (Portal Empresas)
        $secClientOps = MenuSection::create([
            'title' => 'Mis Evaluaciones',
            'order_index' => 10,
            'icon' => 'mdi-account-group-outline',
        ]);

        $secClientBilling = MenuSection::create([
            'title' => 'Facturación y Pagos',
            'order_index' => 11,
            'icon' => 'mdi-cash-multiple',
        ]);

        // 4. Módulos Internos Ópalo
        $itemUsers        = MenuItem::create(['menu_section_id' => $secAdmin->id, 'title' => 'Usuarios', 'route' => '/users', 'icon' => 'mdi-account-multiple-outline', 'order_index' => 1]);
        $itemClients      = MenuItem::create(['menu_section_id' => $secGeneral->id, 'title' => 'Clientes', 'route' => '/clients', 'icon' => 'mdi-domain', 'order_index' => 1]);
        $itemExaminees    = MenuItem::create(['menu_section_id' => $secGeneral->id, 'title' => 'Evaluados', 'route' => '/examinees', 'icon' => 'mdi-badge-account-horizontal-outline', 'order_index' => 2]);
        $itemAppointments = MenuItem::create(['menu_section_id' => $secClinical->id, 'title' => 'Citas', 'route' => '/appointments', 'icon' => 'mdi-calendar-clock-outline', 'order_index' => 1]);
        $itemTests        = MenuItem::create(['menu_section_id' => $secClinical->id, 'title' => 'Pruebas', 'route' => '/tests', 'icon' => 'mdi-file-document-edit-outline', 'order_index' => 2]);

        // 5. Módulos Portal Cliente
        $itemMyExaminees  = MenuItem::create(['menu_section_id' => $secClientOps->id, 'title' => 'Mis Evaluados', 'route' => '/portal/examinees', 'icon' => 'mdi-account-tie-outline', 'order_index' => 1]);
        $itemTracking     = MenuItem::create(['menu_section_id' => $secClientOps->id, 'title' => 'Avance y Estatus', 'route' => '/portal/tracking', 'icon' => 'mdi-progress-check', 'order_index' => 2]);
        $itemBookAppt     = MenuItem::create(['menu_section_id' => $secClientOps->id, 'title' => 'Agendar Citas', 'route' => '/portal/appointments', 'icon' => 'mdi-calendar-plus', 'order_index' => 3]);
        $itemFiscalData   = MenuItem::create(['menu_section_id' => $secClientBilling->id, 'title' => 'Datos Fiscales (CSF)', 'route' => '/portal/fiscal-data', 'icon' => 'mdi-file-pdf-box', 'order_index' => 1]);
        $itemPayments     = MenuItem::create(['menu_section_id' => $secClientBilling->id, 'title' => 'Cuentas y Pagos', 'route' => '/portal/payment-methods', 'icon' => 'mdi-bank-outline', 'order_index' => 2]);

        // Helper de asignación
        $assign = function ($roleId, $itemId, $crud = 'C,R,U,D') {
            RoleMenuAccess::create([
                'role_id' => $roleId,
                'menu_item_id' => $itemId,
                'permissions' => $crud,
            ]);
        };

        // 6. Asignación de Permisos

        // Administración Ópalo: Todos los módulos operativos internos
        foreach ([$itemUsers, $itemClients, $itemExaminees, $itemAppointments, $itemTests] as $item) {
            $assign($adminRole->id, $item->id, 'C,R,U,D,I,E,P');
        }

        // Psicología: Citas, Evaluados y Pruebas
        foreach ([$itemAppointments, $itemExaminees, $itemTests] as $item) {
            $assign($psychologyRole->id, $item->id, 'C,R,U');
        }

        // Trabajo Social: Citas y Evaluados
        foreach ([$itemAppointments, $itemExaminees] as $item) {
            $assign($socialWorkRole->id, $item->id, 'C,R,U');
        }

        // Ventas: Clientes y Evaluados
        foreach ([$itemClients, $itemExaminees] as $item) {
            $assign($salesRole->id, $item->id, 'C,R,U');
        }

        // Clientes: Sus 5 módulos exclusivos de autoservicio
        foreach ([$itemMyExaminees, $itemTracking, $itemBookAppt, $itemFiscalData, $itemPayments] as $item) {
            $assign($clientRole->id, $item->id, 'C,R,U');
        }

        // 7. Cuentas Iniciales

        // Usuario Administrador Interno
        User::create([
            'name' => 'Administrador Ópalo',
            'email' => 'admin@opalo.com',
            'password' => Hash::make('password'),
            'role_id' => $adminRole->id,
            'client_id' => null,
        ]);

        // Empresa Cliente de Prueba
        $demoClient = Client::create([
            'company_name' => 'Logística y Transportes del Norte S.A. de C.V.',
            'trade_name' => 'TransNorte',
            'rfc' => 'LTN180512AB3',
            'contact_email' => 'contacto@transnorte.com',
            'contact_phone' => '3312345678',
            'fiscal_regime' => '601 - General de Ley Personas Morales',
            'fiscal_zip_code' => '45100',
        ]);

        // Usuario Empresa Cliente
        User::create([
            'name' => 'Recursos Humanos TransNorte',
            'email' => 'rh@transnorte.com',
            'password' => Hash::make('password'),
            'role_id' => $clientRole->id,
            'client_id' => $demoClient->id,
        ]);
    }
}
