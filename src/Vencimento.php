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
 * Description of Vencimento
 *
 * @author Leda Ferreira
 */
class Vencimento implements ItemInterface
{
    private $codigo;
    private $descricao;
    private $referencia;
    private $valor;
    private $fgts;
    private $inss;
    private $irrf;

    /**
     *
     * @param string $codigo
     * @param string $descricao
     * @param string $referencia
     * @param float $valor
     * @param boolean $fgts
     * @param boolean $inss
     * @param boolean $irrf
     */
    public function __construct($codigo, $descricao, $referencia, $valor, $fgts = true, $inss = true, $irrf = true)
    {
        $this->codigo = $codigo;
        $this->descricao = $descricao;
        $this->referencia = $referencia;
        $this->valor = $valor;
        $this->fgts = $fgts;
        $this->inss = $inss;
        $this->irrf = $irrf;
    }

    /**
     * @inheritdoc
     */
    public function getCodigo()
    {
        return $this->codigo;
    }

    /**
     * @inheritdoc
     */
    public function getDescricao()
    {
        return $this->descricao;
    }

    /**
     * @inheritdoc
     */
    public function getReferencia()
    {
        return $this->referencia;
    }

    /**
     * @inheritdoc
     */
    public function getValor()
    {
        return $this->valor;
    }

    /**
     * @inheritdoc
     */
    public function incideFGTS()
    {
        return $this->fgts;
    }

    /**
     * @inheritdoc
     */
    public function incideINSS()
    {
        return $this->inss;
    }

    /**
     * @inheritdoc
     */
    public function incideIRRF()
    {
        return $this->irrf;
    }

    /**
     * @inheritdoc
     */
    public function isVencimento()
    {
        return true;
    }
}
