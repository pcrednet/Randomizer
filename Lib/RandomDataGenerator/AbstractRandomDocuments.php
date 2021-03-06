<?php
/**
 * This file is part of Randomizer plugin for FacturaScripts
 * Copyright (C) 2016-2018 Carlos Garcia Gomez <carlos@facturascripts.com>
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
namespace FacturaScripts\Plugins\Randomizer\Lib\RandomDataGenerator;

use FacturaScripts\Dinamic\Model;
use FacturaScripts\Dinamic\Lib\BusinessDocumentTools;

/**
 * Abstract class that contains the methods that generate random documents
 * for clients and suppliers, such as orders, delivery notes and invoices. 
 *
 * @author Rafael San José      <info@rsanjoseo.com>
 * @author Carlos García Gómez  <carlos@facturascripts.com>
 */
abstract class AbstractRandomDocuments extends AbstractRandomPeople
{

    /**
     * List of warehouses.
     *
     * @var Model\Almacen[]
     */
    protected $almacenes;

    /**
     * List of currencies.
     *
     * @var Model\Divisa[]
     */
    protected $divisas;

    /**
     *
     * @var BusinessDocumentTools
     */
    protected $docTools;

    /**
     * List of payment methods.
     *
     * @var Model\FormaPago[]
     */
    protected $formasPago;

    /**
     * List of series.
     *
     * @var Model\Serie[]
     */
    protected $series;

    /**
     * AbstractRandomDocuments constructor.
     */
    public function __construct()
    {
        parent::__construct();
        $this->docTools = new BusinessDocumentTools();

        $this->shuffle($this->almacenes, new Model\Almacen());
        $this->shuffle($this->divisas, new Model\Divisa());
        $this->shuffle($this->formasPago, new Model\FormaPago());
        $this->shuffle($this->series, new Model\Serie());
    }

    /**
     * Generates a random document
     *
     * @param Model\Base\BusinessDocument $doc
     * @param Model\Cliente               $cliente
     * @param Model\Proveedor             $proveedor
     */
    protected function randomizeDocument(&$doc, $cliente = false, $proveedor = false)
    {
        $fecha = $this->fecha();
        $hora = mt_rand(10, 20) . ':' . mt_rand(10, 59) . ':' . mt_rand(10, 59);
        $doc->setDate($fecha, $hora);

        $doc->codpago = $this->formasPago[0]->codpago;
        $doc->codalmacen = (mt_rand(0, 2) == 0) ? $this->almacenes[0]->codalmacen : $doc->codalmacen;
        $doc->codserie = (mt_rand(0, 2) == 0) ? $this->series[0]->codserie : $doc->codserie;
        $doc->codagente = mt_rand(0, 4) && !empty($this->agentes) ? $this->agentes[0]->codagente : null;
        $doc->coddivisa = (mt_rand(0, 2) == 0) ? $this->divisas[0]->coddivisa : $doc->coddivisa;
        foreach ($this->divisas as $div) {
            if ($div->coddivisa == $doc->coddivisa) {
                $doc->tasaconv = $div->tasaconv;
                break;
            }
        }

        if (mt_rand(0, 2) == 0) {
            $doc->observaciones = $this->observaciones();
        }

        if (isset($doc->numero2) && mt_rand(0, 4) == 0) {
            $doc->numero2 = mt_rand(10, 99999);
        } elseif (isset($doc->numproveedor) && mt_rand(0, 4) == 0) {
            $doc->numproveedor = mt_rand(10, 99999);
        }

        $option = mt_rand(0, 14);
        if ($cliente && $option == 0) {
            $doc->cifnif = $this->cif();
            $doc->nombrecliente = $this->empresa();
        } elseif ($cliente) {
            $doc->setSubject($cliente);
        } elseif ($proveedor && $option == 0) {
            $doc->cifnif = $this->cif();
            $doc->nombre = $this->empresa();
        } elseif ($proveedor) {
            $doc->setSubject($proveedor);
        }
    }

    /**
     * Generates random document lines
     *
     * @param Model\Base\BusinessDocument $doc
     */
    protected function randomLineas(&$doc)
    {
        $productos = $this->randomProductos();

        /// 1 out of 5 times we use negative quantities
        $modcantidad = (mt_rand(0, 4) == 0) ? -1 : 1;

        $numlineas = (int) $this->cantidad(0, 10, 200);
        while ($numlineas > 0) {
            if (isset($productos[$numlineas]) && $productos[$numlineas]->sevende) {
                $lin = $doc->getNewProductLine($productos[$numlineas]->referencia);
            } else {
                $lin = $doc->getNewLine();
                $lin->descripcion = $this->descripcion();
                $lin->pvpunitario = $this->precio(1, 49, 699);
            }

            $lin->cantidad = $modcantidad * $this->cantidad(1, 3, 19);
            if (mt_rand(0, 4) == 0) {
                $lin->dtopor = $this->cantidad(0, 33, 100);
            }

            $lin->pvpsindto = $lin->pvpunitario * $lin->cantidad;
            $lin->pvptotal = $lin->pvpunitario * $lin->cantidad * (100 - $lin->dtopor) / 100;
            $lin->save();
            --$numlineas;
        }

        /// recalculate
        $this->docTools->recalculate($doc);
        $doc->save();
    }
}
