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

use FacturaScripts\Dinamic\Model\PresupuestoCliente;

/**
 *  Generate customer budgets with random data.
 *
 * @author Rafael San José      <info@rsanjoseo.com>
 * @author Carlos García Gómez  <carlos@facturascripts.com>
 */
class PresupuestosCliente extends AbstractRandomDocuments
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
        $clientes = $this->randomClientes();
        if (empty($clientes)) {
            return 0;
        }

        $generated = 0;
        $presu = $this->model();

        // start transaction
        $this->dataBase->beginTransaction();

        // main save process
        try {
            while ($generated < $num) {
                $presu->clear();

                $cliente = $this->getOneItem($clientes);
                $this->randomizeDocument($presu, $cliente);
                $presu->finoferta = date('d-m-Y', strtotime($presu->fecha . ' +' . mt_rand(1, 18) . ' months'));
                if (!$presu->save()) {
                    break;
                }

                $this->randomLineas($presu);
                ++$generated;
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
     * @return PresupuestoCliente
     */
    protected function model()
    {
        return new PresupuestoCliente();
    }
}
