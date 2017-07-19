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

/* @var $this League\Plates\Template\Template */
/* @var $fgts ledat\contracheque\imposto\FGTS */
/* @var $inss ledat\contracheque\imposto\INSS */
/* @var $irrf ledat\contracheque\imposto\IRRF */
/* @var $left_header array */
/* @var $right_header array */
/* @var $funcionario array */
/* @var $itens array */
/* @var $salario_base float */
/* @var $total_descontos float */
/* @var $total_vencimentos float */
/* @var $valor_liquido float */
/* @var $writing_pdf boolean */

$this->layout('layouts/layout.php', [
    'title' => 'Contracheque',
    'writing_pdf' => $writing_pdf,
]);

list($L1, $L2, $L3) = $left_header;
list($R1, $R2, $R3) = $right_header;
list($nome, $codigo, $funcao, $cbo, $admissao) = $funcionario;

?>

<?php for ($c = 0; $c < 2; $c++) : ?>
<table class="paycheck bordered">
    <tbody>
        <tr>
            <td colspan="5" class="header-container">
                <div class="header">
                    <div class="header-left">
                        <span class="mono"><?= $L1 ?></span>
                        <span class="mono"><?= $L2 ?></span>
                        <span class="mono"><?= $L3 ?></span>
                    </div>
                    <div class="header-right">
                        <span class="mono"><?= $R1 ?></span>
                        <span class="mono"><?= $R2 ?></span>
                        <span class="mono"><?= $R3 ?></span>
                    </div>
                </div>
            </td>
            <td rowspan="7" class="signature-container">
                <div class="signature">
                    <div class="disclaimer">DECLARO TER RECEBIDO A IMPORTÂNCIA LÍQUIDA DISCRIMINADA NESTE RECIBO</div>
                    <div class="user-fill">
                        <div class="date-fill">
                            <span class="fill-space">___/___/_____</span>
                            <span class="fill-description">Data</span>
                        </div>
                        <div class="name-fill">
                            <span class="fill-space">_________________________</span>
                            <span class="fill-description">Assinatura do funcionário</span>
                        </div>
                    </div>
                </div>
            </td>
        </tr>
        <tr>
            <td colspan="5" class="person-container">
                <table class="person not-bordered no-padding">
                    <tbody>
                        <tr class="person-labels">
                            <td class="person-code">Código</td>
                            <td class="person-name">Nome do funcionário</td>
                            <td class="person-cbo">CBO</td>
                            <td class="person-admission">Admissão:</td>
                        </tr>
                        <tr class="person-info">
                            <td class="mono person-code"><?= $codigo ?></td>
                            <td class="mono person-name"><?= $nome ?></td>
                            <td class="mono person-cbo"><?= $cbo ?></td>
                            <td class="mono person-admission"><?= $admissao ?></td>
                        </tr>
                        <tr class="person-info">
                            <td class="person-code"></td>
                            <td class="mono person-name"><?= $funcao?></td>
                            <td class="person-cbo"></td>
                            <td class="person-admission"></td>
                        </tr>
                    </tbody>
                </table>
            </td>
        </tr>
        <tr class="items-headers-container">
            <td class="item-code">Código</td>
            <td class="item-description">Descrição</td>
            <td class="item-reference">Referência</td>
            <td class="item-amount">Vencimentos</td>
            <td class="item-discount">Descontos</td>
        </tr>
        <tr class="items-container">
            <td class="mono item-code"><?= $this->list($itens['codigo']) ?></td>
            <td class="mono item-description"><?= $this->list($itens['descricao']) ?></td>
            <td class="mono item-reference"><?= $this->list($itens['referencia']) ?></td>
            <td class="mono item-amount"><?= $this->list($itens['vencimentos']) ?></td>
            <td class="mono item-discount"><?= $this->list($itens['descontos']) ?></td>
        </tr>
        <tr class="summary-container">
            <td colspan="3"></td>
            <td>
                <span class="summary-label">Total de vencimentos</span>
                <span class="mono summary-value"><?= $this->decimal($total_vencimentos) ?></span>
            </td>
            <td>
                <span class="summary-label">Total de descontos</span>
                <span class="mono summary-value"><?= $this->decimal($total_descontos) ?></span>
            </td>
        </tr>
        <tr class="summary-container summary-2">
            <td colspan="3"></td>
            <td>Valor líquido</td>
            <td class="mono net-salary"><?= $this->decimal($valor_liquido) ?></td>
        </tr>
        <tr>
            <td colspan="5" class="taxes-container">
                <table class="taxes not-bordered no-padding">
                    <tbody>
                        <tr class="taxes-labels">
                            <td>Salário Base</td>
                            <td>Sal. Contr. INSS</td>
                            <td>Base Cálc. FGTS</td>
                            <td>FGTS do mês</td>
                            <td>Base Cálc. IRRF</td>
                            <td>Faixa IRRF</td>
                        </tr>
                        <tr class="taxes-values mono">
                            <td><?= $this->decimal($salario_base) ?></td>
                            <td><?= $this->decimal($inss->baseDeCalculo()) ?></td>
                            <td><?= $this->decimal($fgts->baseDeCalculo()) ?></td>
                            <td><?= $this->decimal($fgts->valorImposto()) ?></td>
                            <td><?= $this->decimal($irrf->baseDeCalculo()) ?></td>
                            <td><?= $this->decimal($irrf->aliquota() * 100) ?></td>
                        </tr>
                    </tbody>
                </table>
            </td>
        </tr>
    </tfoot>
</table>
<div class="cut-here"></div>
<?php endfor; ?>
