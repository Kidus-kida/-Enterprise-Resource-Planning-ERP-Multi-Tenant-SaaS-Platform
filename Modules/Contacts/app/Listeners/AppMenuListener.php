<?php

namespace Modules\Contacts\Listeners;

use App\Events\AppMenuEvent;
use Spatie\Menu\Laravel\Html;
use Spatie\Menu\Laravel\Link;
use Spatie\Menu\Laravel\Menu;

class AppMenuListener
{
    /**
     * Create the event listener.
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     */
    public function handle(AppMenuEvent $event): void
    {
        if (request()->routeIs('tenant.dashboard')) {
            return;
        }

        $menu = $event->menu;
        $activeClass = route_is(['contacts.*', 'customer-loans.*', 'supplier-mappings.*', 'contact-groups.*', 'customer-statements.*', 'customer-payments.*']) ? "active" : "";

        $menu->submenu(
            Html::raw('<a href="#" class="' . $activeClass . '"><i class="la la-address-book"></i> <span>' . __('Contacts') . '</span><span class="menu-arrow"></span></a>'),
            Menu::new()
                ->addParentClass('submenu')
                ->add(Link::toRoute('contacts.index', __('Suppliers'), ['type' => 'supplier'])->addClass((route_is('contacts.index') && request('type') == 'supplier') ? 'active' : ''))
                ->add(Link::toRoute('contacts.index', __('Customer'), ['type' => 'customer'])->addClass((route_is('contacts.index') && request('type') == 'customer') ? 'active' : ''))
                ->add(Link::toRoute('customer-loans.index', __('List Customer Loans'))->addClass(route_is('customer-loans.*') ? 'active' : ''))
                ->add(Link::toRoute('contacts.index', __('Settings'), ['todo' => 'settings'])->addClass(request('todo') == 'settings' ? 'active' : ''))
                ->add(Link::toRoute('supplier-mappings.index', __('List Supplier Map Products'))->addClass(route_is('supplier-mappings.*') ? 'active' : ''))
                ->add(Link::toRoute('supplier-mappings.create', __('Add Supplier Map Products'))->setAttribute('data-ajax-modal', 'true')->setAttribute('data-size', 'md')->setAttribute('data-title', 'Add Supplier Map Product'))
                ->add(Link::toRoute('contact-groups.index', __('Contact Groups'))->addClass(route_is('contact-groups.*') ? 'active' : ''))
                ->add(Link::toRoute('contacts.index', __('Customer Reference'), ['todo' => 'reference'])->addClass(request('todo') == 'reference' ? 'active' : ''))
                ->add(Link::toRoute('customer-statements.index', __('Customer Statements'))->addClass(route_is('customer-statements.*') ? 'active' : ''))
                ->add(Link::toRoute('contacts.index', __('Customer Statements - Pmts'), ['todo' => 'statements_pmts'])->addClass(request('todo') == 'statements_pmts' ? 'active' : ''))
                ->add(Link::toRoute('customer-payments.index', __('Customer Payments'))->addClass(route_is('customer-payments.*') ? 'active' : ''))
                ->add(Link::toRoute('contacts.index', __('Outstanding Received'), ['todo' => 'outstanding'])->addClass(request('todo') == 'outstanding' ? 'active' : ''))
                ->add(Link::toRoute('contacts.index', __('Import Opening Balance'), ['todo' => 'import_balance'])->addClass(request('todo') == 'import_balance' ? 'active' : ''))
                ->add(Link::toRoute('contacts.index', __('Issued Payment Details'), ['todo' => 'issued_payments'])->addClass(request('todo') == 'issued_payments' ? 'active' : ''))
                ->add(Link::toRoute('contacts.index', __('Returned Cheque Details'), ['todo' => 'cheques'])->addClass(request('todo') == 'cheques' ? 'active' : ''))
                ->add(Link::toRoute('contacts.index', __('All Contacts'))->addClass((route_is('contacts.index') && !request()->has('type') && !request()->has('todo')) ? 'active' : ''))
        );
    }
}
