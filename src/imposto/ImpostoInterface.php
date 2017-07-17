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
 * Interface para classes que calculam impostos.
 * @author Leda Ferreira
 */
interface ImpostoInterface
{
    /**
     * Retorna a alíquota aplicável, em valores decimais:
     * se a alíquota for 8%, este método retorna 0.08.
     * @return float
     */
    public function aliquota();

    /**
     * Retorna a base de cálculo do imposto.
     * Este método existe porque nem sempre a base de cálculo
     * corresponde ao valor total dos recebimentos.
     * @return float
     */
    public function baseDeCalculo();

    /**
     * Retorna o valor calculado para o imposto.
     * @return float
     */
    public function valorImposto();
}
