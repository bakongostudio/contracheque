<?php

/*
 * Copyright (C) 2017 Leda Ferreira
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */

namespace ledat\contracheque\imposto;

/**
 * Calcula o FGTS, e realiza tarefas relacionadas.
 *
 * @author Leda Ferreira
 */
class FGTS extends AbstractImposto
{
    const ALIQUOTA = 0.08;

    /**
     * Alíquota.
     * @var float
     */
    private $aliquota;

    /**
     * Base de cálculo.
     * @var float
     */
    private $base;

    /**
     * Imposto calculado.
     * @var float
     */
    private $fgts;

    /**
     * @inheritdoc
     */
    public function __construct($vencimentos, $ano = null, $mes = null)
    {
        parent::__construct($vencimentos, $ano, $mes);
        $this->calcular();
    }

    /**
     * @inheritdoc
     */
    public function aliquota()
    {
        return $this->aliquota;
    }

    /**
     * @inheritdoc
     */
    public function baseDeCalculo()
    {
        return $this->vencimentos;
    }

    /**
     * @inheritdoc
     */
    public function valorImposto()
    {
        return $this->fgts;
    }

    /**
     * Calcula a alíquota, base de cálculo e imposto.
     */
    private function calcular()
    {
        $this->aliquota = self::ALIQUOTA;
        $this->base = $this->vencimentos;
        $this->fgts = bcmul($this->base, $this->aliquota);
    }
}
