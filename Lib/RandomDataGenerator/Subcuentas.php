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

/**
 * Generates accounting sub-accounts at random.
 * It may be better to incorporate the accounting plan of your country.
 *
 * @author Rafael San José      <info@rsanjoseo.com>
 * @author Carlos García Gómez  <carlos@facturascripts.com>
 */
class Subcuentas extends AbstractRandomAccounting
{

    /**
     * Generate random data.
     *
     * @param int $num
     *
     * @return int
     */
    public function generate($num = 50)
    {
        $subcuenta = $this->model();
        $this->shuffle($cuentas, new Model\Cuenta());
        $ejercicio = new Model\Ejercicio();

        // start transaction
        $this->dataBase->beginTransaction();

        // main save process
        try {
            for ($generated = 0; $generated < $num; ++$generated) {
                $cuenta = $this->getOneItem($cuentas);
                $ejercicioDetails = $ejercicio->get($cuenta->codejercicio);
                $subcuenta->clear();
                $subcuenta->codcuenta = $cuenta->codcuenta;
                $subcuenta->codejercicio = $cuenta->codejercicio;
                $subcuenta->codsubcuenta = str_pad($cuenta->codcuenta . mt_rand(0, 9999), $ejercicioDetails->longsubcuenta, 0);
                $subcuenta->descripcion = $this->descripcion();
                $subcuenta->idcuenta = $cuenta->idcuenta;
                if (!$subcuenta->save()) {
                    break;
                }
            }

            // confirm data
            $this->dataBase->commit();
        } catch (\Exception $e) {
            $this->miniLog->alert($e->getMessage());
        } finally {
            if ($this->dataBase->inTransaction()) {
                $this->dataBase->rollback();
            }
        }

        return $generated;
    }

    /**
     * 
     * @return Model\Subcuenta
     */
    protected function model()
    {
        return new Model\Subcuenta();
    }
}
