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
 * Calcula o INSS, e realiza tarefas relacionadas.
 * @link http://www.previdencia.gov.br/servicos-ao-cidadao/todos-os-servicos/gps/tabela-contribuicao-mensal/
 * @link http://www.previdencia.gov.br/servicos-ao-cidadao/todos-os-servicos/gps/tabela-contribuicao-mensal/tabela-de-contribuicao-historico/
 * @author Leda Ferreira
 */
class INSS extends AbstractImposto
{
    /**
     * Tabela de contribuição mensal.
     * Formato: ano => [alíquota => máximo].
     * @var array
     */
    private static $contribuicaoMensal = [
        2012 => [
            '0.08' => '1174.86',
            '0.09' => '1958.10',
            '0.11' => '3916.20',
        ],
        2013 => [
            '0.08' => '1247.70',
            '0.09' => '2079.50',
            '0.11' => '4159.00',
        ],
        2014 => [
            '0.08' => '1317.07',
            '0.09' => '2195.12',
            '0.11' => '4390.24',
        ],
        2015 => [
            '0.08' => '1399.12',
            '0.09' => '2331.88',
            '0.11' => '4663.75',
        ],
        2016 => [
            '0.08' => '1556.94',
            '0.09' => '2594.92',
            '0.11' => '5189.82',
        ],
        2017 => [
            '0.08' => '1659.38',
            '0.09' => '2765.66',
            '0.11' => '5531.31',
        ],
    ];

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
    private $inss;

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
        return $this->base;
    }

    /**
     * @inheritdoc
     */
    public function valorImposto()
    {
        return $this->inss;
    }

    /**
     * Retorna a tabela de contribuição mensal para o ano de competência.
     * @return array
     */
    private function contribuicao()
    {
        return self::$contribuicaoMensal[$this->ano];
    }

    /**
     * Calcula a alíquota, base de cálculo e imposto.
     */
    private function calcular()
    {
        $aliquotas = $this->contribuicao();
        foreach ($aliquotas as $aliquota => $maximo) {
            if ($this->vencimentos <= $maximo) {
                $this->aliquota = $aliquota;
                $this->base = $this->vencimentos;
            }
        }

        $teto = max($aliquotas);
        if ($this->vencimentos > $teto) {
            $this->aliquota = $aliquota;
            $this->base = $teto;
        }

        $this->inss = bcmul($this->base, $this->aliquota);
    }
}
