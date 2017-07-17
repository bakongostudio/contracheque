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
 * Calcula o IRRF, e realiza tarefas relacionadas.
 * @link http://idg.receita.fazenda.gov.br/acesso-rapido/tributos/irpf-imposto-de-renda-pessoa-fisica
 * @author Leda Ferreira
 */
class IRRF extends AbstractImposto
{
    /**
     * Tabela de incidência mensal.
     * Formato: ano/mes máximo => [alíquota => [máximo, parcela]].
     * @var array
     */
    private static $incidenciaMensal = [
        '2006-01' => [
            false   => ['1164.00',    false],
            '0.150' => ['2326.00', '174.60'],
            '0.275' => [INF,       '465.35'],
        ],
        '2006-12' => [
            false   => ['1257.12',    false],
            '0.150' => ['2512.08', '188.57'],
            '0.275' => [INF,       '502.58'],
        ],
        '2007-12' => [
            false   => ['1313.69',    false],
            '0.150' => ['2625.12', '197.05'],
            '0.275' => [INF,       '525.19'],
        ],
        '2008-12' => [
            false   => ['1372.81',    false],
            '0.150' => ['2743.25', '205.92'],
            '0.275' => [INF,       '548.82'],
        ],
        '2009-12' => [
            false   => ['1434.59',    false],
            '0.075' => ['2150.00', '107.59'],
            '0.150' => ['2866.70', '268.84'],
            '0.225' => ['3582.00', '483.84'],
            '0.275' => [INF,       '662.94'],
        ],
        '2011-03' => [
            false   => ['1499.15',    false],
            '0.075' => ['2246.75', '112.43'],
            '0.150' => ['2995.70', '280.94'],
            '0.225' => ['3743.19', '505.62'],
            '0.275' => [INF,       '692.78'],
        ],
        '2011-12' => [
            false   => ['1566.61',    false],
            '0.075' => ['2347.85', '117.49'],
            '0.150' => ['3130.51', '293.58'],
            '0.225' => ['3911.63', '528.37'],
            '0.275' => [INF,       '723.95'],
        ],
        '2012-12' => [
            false   => ['1637.11',    false],
            '0.075' => ['2453.50', '122.78'],
            '0.150' => ['3271.38', '306.80'],
            '0.225' => ['4087.65', '552.15'],
            '0.275' => [INF,       '756.53'],
        ],
        '2013-12' => [
            false   => ['1710.78',    false],
            '0.075' => ['2563.91', '128.31'],
            '0.150' => ['3418.59', '320.60'],
            '0.225' => ['4271.59', '577.00'],
            '0.275' => [INF,       '790.58'],
        ],
        '2015-03' => [
            false   => ['1787.77',    false],
            '0.075' => ['2679.29', '134.08'],
            '0.150' => ['3572.43', '335.03'],
            '0.225' => ['4463.81', '602.96'],
            '0.275' => [INF      , '826.15'],
        ],
        '2031-09' => [
            false   => ['1903.98',    false],
            '0.075' => ['2826.65', '142.80'],
            '0.150' => ['3751.05', '354.80'],
            '0.225' => ['4664.68', '636.13'],
            '0.275' => [INF      , '869.36'],
        ],
    ];

    /**
     * Dedução mensal por dependente.
     * Formato: ano/mes máximo => dedução.
     * @var array
     */
    private static $deducaoPorDependente = [
        '2007-12' => '132.05',
        '2008-12' => '137.99',
        '2009-12' => '144.20',
        '2011-03' => '150.69',
        '2011-12' => '157.47',
        '2012-12' => '164.56',
        '2013-12' => '171.97',
        '2015-03' => '179.71',
        '2031-09' => '189.59',
    ];

    /**
     * @var integer
     */
    private $dependentes;

    /**
     * Alíquota.
     * @var float
     */
    private $aliquota;

    /**
     * Parcela a deduzir.
     * @var float
     */
    private $parcela;

    /**
     * Base de cálculo.
     * @var float
     */
    private $base;

    /**
     * Resultado do cálculo de dedução por dependentes.
     * @var float
     */
    private $deducao;

    /**
     * Imposto calculado.
     * @var float
     */
    private $irrf;

    /**
     * Instancia a classe.
     * @param float $vencimentos
     * @param string $ano
     * @param string $mes
     * @param integer $dependentes
     */
    public function __construct($vencimentos, $ano = null, $mes = null, $dependentes = 0)
    {
        parent::__construct($vencimentos, $ano, $mes);
        $this->dependentes = $dependentes;
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
        return $this->irrf;
    }

    /**
     * Retorna a parcela que foi deduzida.
     * @return float
     */
    public function parcela()
    {
        return $this->parcela;
    }

    /**
     * Retornoa o resultado do cálculo de dedução por dependentes.
     * @return float
     */
    public function deducao()
    {
        return $this->deducao;
    }

    /**
     * Retorna a tabela de incidência mensal para o ano de competência.
     * @return array
     */
    private function incidencia()
    {
        foreach (self::$incidenciaMensal as $vigencia => $aliquotas) {
            list($v_ano, $v_mes) = explode('-', $vigencia);
            if ($this->ano <= $v_ano && $this->mes <= $v_mes) {
                return $aliquotas;
            }
        }
    }

    /**
     * Retorna o valor a deduzir por dependente para o ano de competência.
     * @return float
     */
    private function deducaoPorDependente()
    {
        foreach (self::$deducaoPorDependente as $vigencia => $deducao) {
            list($v_ano, $v_mes) = explode('-', $vigencia);
            if ($this->ano <= $v_ano && $this->mes <= $v_mes) {
                return $deducao;
            }
        }
    }

    /**
     * Calcula alíquota, parcela, base de cálculo, dedução e imposto.
     */
    private function calcular()
    {
        $inss = new INSS($this->vencimentos, $this->ano, $this->mes);
        $this->base = bcsub($this->vencimentos, $inss->valorImposto());

        $aliquotas = $this->incidencia($this->base);
        foreach ($aliquotas as $faixa => $params) {
            list($maximo, $parcela) = $params;
            if ($this->base <= $maximo) {
                $this->aliquota = $faixa;
                $this->parcela = $parcela;
                break;
            }
        }

        $valor_deducao = $this->deducaoPorDependente();
        $this->deducao = bcmul($valor_deducao, $this->dependentes);
        $this->irrf = bcsub(bcmul(bcsub($this->base, $this->deducao),  $this->aliquota), $this->parcela);
    }
}
