<?php
/**
 * This file is part of Randomizer plugin for FacturaScripts
 * Copyright (C) 2017-2018 Carlos Garcia Gomez <carlos@facturascripts.com>
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Lesser General Public License as
 * published by the Free Software Foundation, either version 3 of the
 * License, or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU Lesser General Public License for more details.
 *
 * You should have received a copy of the GNU Lesser General Public License
 * along with this program. If not, see <http://www.gnu.org/licenses/>.
 */
namespace FacturaScripts\Plugins\Randomizer\Controller;

use FacturaScripts\Core\Base;
use FacturaScripts\Core\Model\User;
use FacturaScripts\Plugins\Randomizer\Lib\RandomDataGenerator;
use Symfony\Component\HttpFoundation\Response;

/**
 * Controller to generate random data
 *
 * @author Carlos García Gómez  <carlos@facturascripts.com>
 * @author Rafael San José      <info@rsanjoseo.com>
 */
class Randomizer extends Base\Controller
{

    /**
     * URL where reload.
     *
     * @var string
     */
    public $urlReload;

    /**
     * Contains the total quantity for each model.
     *
     * @var array
     */
    public $totalCounter = [];

    /**
     * Returns basic page attributes
     *
     * @return array
     */
    public function getPageData()
    {
        $pageData = parent::getPageData();
        $pageData['menu'] = 'admin';
        $pageData['title'] = 'generate-test-data';
        $pageData['icon'] = 'fas fa-magic';

        return $pageData;
    }

    /**
     * Runs the controller's private logic.
     *
     * @param Response                   $response
     * @param User                       $user
     * @param Base\ControllerPermissions $permissions
     */
    public function privateCore(&$response, $user, $permissions)
    {
        parent::privateCore($response, $user, $permissions);

        $option = $this->request->get('gen', '');
        if ($option !== '') {
            $this->execAction($option);
            $this->urlReload = $this->url() . '?gen=' . $option;
        }

        $this->getTotals();
    }

    /**
     * Executes selected action.
     *
     * @param string $option
     */
    private function execAction($option)
    {
        switch ($option) {
            case 'agentes':
                $app = new RandomDataGenerator\Agentes();
                $txt = 'generated-agents';
                break;

            case 'albaranescli':
                $app = new RandomDataGenerator\AlbaranesCliente();
                $txt = 'generated-customer-delivery-notes';
                break;

            case 'albaranesprov':
                $app = new RandomDataGenerator\AlbaranesProveedor();
                $txt = 'generated-supplier-delivery-notes';
                break;

            case 'asientos':
                $app = new RandomDataGenerator\Asientos();
                $txt = 'generated-accounting-entries';
                break;

            case 'clientes':
                $app = new RandomDataGenerator\Clientes();
                $txt = 'generated-customers';
                break;

            case 'contactos':
                $app = new RandomDataGenerator\Contactos();
                $txt = 'generated-contacts';
                break;

            case 'cuentas':
                $app = new RandomDataGenerator\Cuentas();
                $txt = 'generated-accounts';
                break;

            case 'grupos':
                $app = new RandomDataGenerator\Grupos();
                $txt = 'generated-customer-groups';
                break;

            case 'fabricantes':
                $app = new RandomDataGenerator\Fabricantes();
                $txt = 'generated-manufacturers';
                break;

            case 'familias':
                $app = new RandomDataGenerator\Familias();
                $txt = 'generated-families';
                break;

            case 'pedidoscli':
                $app = new RandomDataGenerator\PedidosCliente();
                $txt = 'generated-customer-orders';
                break;

            case 'pedidosprov':
                $app = new RandomDataGenerator\PedidosProveedor();
                $txt = 'generated-supplier-orders';
                break;

            case 'presupuestoscli':
                $app = new RandomDataGenerator\PresupuestosCliente();
                $txt = 'generated-customer-estimations';
                break;

            case 'presupuestosprov':
                $app = new RandomDataGenerator\PresupuestosProveedor();
                $txt = 'generated-supplier-estimations';
                break;

            case 'productos':
                $app = new RandomDataGenerator\Productos();
                $txt = 'generated-products';
                break;

            case 'proveedores':
                $app = new RandomDataGenerator\Proveedores();
                $txt = 'generated-supplier';
                break;

            case 'subcuentas':
                $app = new RandomDataGenerator\Subcuentas();
                $txt = 'generated-subaccounts';
                break;
            case 'servicios':
                $app = new RandomDataGenerator\Servicios();
                $txt = 'generated-services';
                break;

            default:
                $app = false;
                $txt = '';
        }

        if (false !== $app) {
            $this->miniLog->notice($this->i18n->trans($txt, ['%quantity%' => $app->generate()]));
            $this->miniLog->notice($this->i18n->trans('randomizer-generating-more-items'));
        }

        return;
    }

    /**
     * Set totalCounter key for each model.
     */
    private function getTotals()
    {
        $models = [
            'agentes' => '\FacturaScripts\Dinamic\Model\Agente',
            'albaranescli' => '\FacturaScripts\Dinamic\Model\AlbaranCliente',
            'albaranesprov' => '\FacturaScripts\Dinamic\Model\AlbaranProveedor',
            'asientos' => '\FacturaScripts\Dinamic\Model\Asiento',
            'clientes' => '\FacturaScripts\Dinamic\Model\Cliente',
            'contactos' => '\FacturaScripts\Dinamic\Model\Contacto',
            'cuentas' => '\FacturaScripts\Dinamic\Model\Cuenta',
            'grupos' => '\FacturaScripts\Dinamic\Model\GrupoClientes',
            'fabricantes' => '\FacturaScripts\Dinamic\Model\Fabricante',
            'familias' => '\FacturaScripts\Dinamic\Model\Familia',
            'pedidoscli' => '\FacturaScripts\Dinamic\Model\PedidoCliente',
            'pedidosprov' => '\FacturaScripts\Dinamic\Model\PedidoProveedor',
            'presupuestoscli' => '\FacturaScripts\Dinamic\Model\PresupuestoCliente',
            'presupuestosprov' => '\FacturaScripts\Dinamic\Model\PresupuestoProveedor',
            'productos' => '\FacturaScripts\Dinamic\Model\Producto',
            'proveedores' => '\FacturaScripts\Dinamic\Model\Proveedor',
            'subcuentas' => '\FacturaScripts\Dinamic\Model\Subcuenta',
            'servicios' => '\FacturaScripts\Dinamic\Model\Servicio'
        ];

        foreach ($models as $tag => $modelName) {
            $model = new $modelName();
            $this->totalCounter[$tag] = $model->count();
        }
    }
}
