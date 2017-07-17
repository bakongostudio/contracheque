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

namespace ledat\contracheque;

/**
 *
 * @author Leda Ferreira
 */
interface ItemInterface
{
    /**
     * Retorna o código numérico para este item.
     * Por exemplo, '0001' para salário normal.
     * @return string
     */
    public function getCodigo();

    /**
     * Retorna a descrição deste item.
     * Por exemplo, 'SALÁRIO NORMAL'.
     * @return string
     */
    public function getDescricao();
    
    /**
     * Retorna a referência deste item.
     * Por exemplo, 15.0
     * @return string
     */
    public function getReferencia();

    /**
     * Retorna o valor deste item.
     * @return float
     */
    public function getValor();

    /**
     * Retorna <b>true</b> se o item for parte da base de cálculo do FGTS,
     * <b>false</b> em caso contrário.
     * @return boolean
     */
    public function incideFGTS();

    /**
     * Retorna <b>true</b> se o item for parte da base de cálculo do INSS,
     * <b>false</b> em caso contrário.
     * @return boolean
     */
    public function incideINSS();

    /**
     * Retorna <b>true</b> se o item for parte da base de cálculo do IRRF,
     * <b>false</b> em caso contrário.
     * @return boolean
     */
    public function incideIRRF();

    /**
     * Este método é usado para determinar em que coluna o valor do item
     * deve aparecer. Se este método retornar <b>true</b>, o valor ficará
     * na coluna <b>Vencimentos</b>; senão, ficará na coluna <b>Descontos</b>.
     * @return boolean
     */
    public function isVencimento();
}
