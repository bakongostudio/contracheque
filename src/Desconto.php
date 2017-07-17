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
 * Description of Desconto
 *
 * @author Leda Ferreira
 */
class Desconto implements ItemInterface
{
    private $codigo;
    private $descricao;
    private $referencia;
    private $valor;

    /**
     *
     * @param string $codigo
     * @param string $descricao
     * @param string $referencia
     * @param float $valor
     */
    public function __construct($codigo, $descricao, $referencia, $valor)
    {
        $this->codigo = $codigo;
        $this->descricao = $descricao;
        $this->referencia = $referencia;
        $this->valor = $valor;
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
    public function getValor()
    {
        return $this->valor;
    }

    /**
     * @inheritdoc
     */
    public function incideFGTS()
    {
        return false;
    }

    /**
     * @inheritdoc
     */
    public function incideINSS()
    {
        return false;
    }

    /**
     * @inheritdoc
     */
    public function incideIRRF()
    {
        return false;
    }

    /**
     * @inheritdoc
     */
    public function isVencimento()
    {
        return false;
    }
}
