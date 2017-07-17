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
 * Implementação de ImpostoInterface.
 * Objetos devem ser imutáveis.
 * @author Leda Ferreira
 */
abstract class AbstractImposto implements ImpostoInterface
{
    /**
     * Total dos vencimentos para os quais este imposto é aplicável.
     * @var float
     */
    protected $vencimentos;

    /**
     * Ano da competencia, no formato YYYY.
     * @var string
     */
    protected $ano;

    /**
     * Mês da competência, no formato MM.
     * @var type
     */
    protected $mes;

    /**
     * Instancia a classe.
     * @param float $vencimentos
     * @param string $ano
     * @param string $mes
     */
    public function __construct($vencimentos, $ano = null, $mes = null)
    {
        $this->vencimentos = $vencimentos;
        $this->ano = $ano ? $this->verificarAno($ano) : date('Y');
        $this->mes = $mes ? $this->verificarMes($mes) : date('m');
    }

    /**
     * Verifica se o ano da competência é válido.
     * Deve estar no formato YYYY.
     * @param string $ano
     * @return string
     * @throws \InvalidArgumentException
     */
    private function verificarAno($ano)
    {
        if (preg_match('/^(\d{4})$/', $ano) !== 1) {
            throw new \InvalidArgumentException('Ano informado não é válido.');
        }
        return $ano;
    }

    /**
     * Verifica se o mês da competência é válido.
     * Deve estar no formato MM.
     * @param string $mes
     * @return string
     * @throws \InvalidArgumentException
     */
    private function verificarMes($mes)
    {
        $mes = str_pad($mes, 2, '0', STR_PAD_LEFT);
        $meses = [
            '01', '02', '03', '04', '05', '06',
            '07', '08', '09', '10', '11', '12',
        ];
        if (!in_array($mes, $meses, true)) {
            throw new \InvalidArgumentException('Mês informado não é válido.');
        }
        return $mes;
    }
}
