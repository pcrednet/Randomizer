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

use FacturaScripts\Dinamic\Model\Agente;

/**
 * Generate random data for the agents (Agentes) file
 *
 * @author Rafael San José      <info@rsanjoseo.com>
 * @author Carlos García Gómez  <carlos@facturascripts.com>
 */
class Agentes extends AbstractRandomPeople
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
        $agente = $this->model();

        // start transaction
        $this->dataBase->beginTransaction();

        // main save process
        try {
            for ($generated = 0; $generated < $num; ++$generated) {
                $agente->clear();

                $this->setAgenteData($agente);
                if (!$agente->save()) {
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
     * @return Agente
     */
    protected function model()
    {
        return new Agente();
    }

    /**
     * 
     * @param Agente $agente
     */
    private function setAgenteData(Agente &$agente)
    {
        $agente->fechanacimiento = $this->fecha(1970, 1997);
        $agente->fechaalta = $this->fecha(2013, 2016);
        $agente->cifnif = $this->cif();
        $agente->nombre = $this->nombre();
        $agente->apellidos = $this->apellidos();
        $agente->provincia = $this->provincia();
        $agente->ciudad = $this->ciudad();
        $agente->direccion = $this->direccion();
        $agente->codpostal = (string) mt_rand(11111, 99999);
        $agente->fechabaja = (mt_rand(0, 24) == 0) ? date('d-m-Y') : null;
        $agente->telefono1 = (mt_rand(0, 1) == 0) ? $this->telefono() : '';
        $agente->telefono2 = (mt_rand(0, 1) == 0) ? $this->telefono() : '';
        $agente->email = (mt_rand(0, 2) > 0) ? $this->email() : '';
        $agente->cargo = (mt_rand(0, 2) > 0) ? $this->cargo() : '';
        $agente->seg_social = (mt_rand(0, 1) == 0) ? $this->seguridadSocial() : '';
        $agente->porcomision = $this->cantidad(0, 5, 20);
        $agente->banco = mt_rand(0, 5) ? $this->iban() : '';
    }
}
