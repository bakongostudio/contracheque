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

use League\Plates\Engine;
use ledat\contracheque\imposto\FGTS;
use ledat\contracheque\imposto\INSS;
use ledat\contracheque\imposto\IRRF;

/**
 * Description of Contracheque
 *
 * @author Leda Ferreira
 */
class Contracheque
{
    /**
     * Ano da competência, no formato YYYY.
     * @var string
     */
    private $ano;

    /**
     * Mês da competência, no formato MM.
     * @var string
     */
    private $mes;

    /**
     * Valor aplicável para FGTS.
     * @var float
     */
    private $base_fgts;

    /**
     * Valor aplicável para INSS.
     * @var float
     */
    private $base_inss;

    /**
     * Valor aplicável para IRRF.
     * @var float
     */
    private $base_irrf;

    /**
     * Plates Template Engine, by The League of Extraordinary Packages.
     * @var \League\Plates\Engine
     */
    private $engine;

    /**
     * NumberFormatter.
     * @var \NumberFormatter
     */
    private $formatter;

    /**
     * Parâmetros do funcionário (nome, código, função, cbo, admissão).
     * @var array
     */
    private $funcionario = [];

    /**
     * Parâmetros do INSS: código, descrição.
     * @var array
     */
    private $inss_params = [];

    /**
     * Parâmetros do IRRF: código, descrição, nº de dependentes.
     * @var array
     */
    private $irrf_params = [];

    /**
     * Lista de vencimentos e descontos.
     * @var ItemInterface[]
     */
    private $itens = [];

    /**
     * Cabeçalho esquerdo. Máximo: 3 linhas.
     * @var array
     */
    private $left_header;

    /**
     * Cabeçalho direito. Máximo: 3 linhas.
     * @var array
     */
    private $right_header;

    /**
     * HTML do contracheque.
     * @var string
     */
    private $output;

    /**
     * Salário base. Se nulo, será considerado como salário base
     * o valor do primeiro vencimento acrescentado ao contracheque.
     * @var float
     */
    private $salario_base;

    /**
     * Template HTML do contracheque.
     * @var string
     */
    private $template;

    /**
     * Caminho absoluto para a pasta onde estão os templates.
     * @var string
     */
    private $templates_path;

    /**
     * Somatório dos vencimentos.
     * @var float
     */
    private $total_vencimentos = 0;

    /**
     * Somatório dos descontos e impostos.
     * @var float
     */
    private $total_descontos = 0;

    /**
     * Salário líquido.
     * @var float
     */
    private $valor_liquido = 0;

    /**
     * @var boolean
     */
    private $writing_pdf = false;

    /**
     * Cria uma nova instância desta classe.
     * @param string $ano Ano da competência, no formato YYYY.
     * @param string $mes Mês da competência, no formato MM.
     * @param float $salario_base Salário base. Se nulo,
     *  será considerado o primeiro vencimento.
     * @return $this
     */
    public function __construct($ano, $mes, $salario_base = null)
    {
        $this->ano = $ano;
        $this->mes = $mes;
        $this->salario_base = $salario_base;
        $this->init();
        return $this;
    }

    /**
     * Retorna uma nova instância da classe.
     * @see Contracheque::__construct
     * @param string $ano
     * @param string $mes
     * @param float $salario_base
     * @return \self
     */
    public static function create($ano, $mes, $salario_base = null)
    {
        return new self($ano, $mes, $salario_base);
    }

    /**
     * Retorna a competência por extenso.
     * @return string
     */
    private function getCompetencia()
    {
        $mes = contracheque_nome_mes($this->mes);
        return "{$mes} de {$this->ano}";
    }

    /**
     * Inicializa o contracheque com valores padrão.
     */
    private function init()
    {
        $this->setINSSParams('998', 'INSS');
        $this->setIRRFParams('999', 'IMPOSTO DE RENDA', 0);
        $this->setRightHeader('RECIBO DE PAGAMENTO DE SALÁRIO', $this->getCompetencia(), '');

        $this->template = 'contracheque.php';
        $this->templates_path = dirname(__DIR__) . '/templates';
        $this->engine = $this->initEngine();
        $this->formatter = $this->initFormatter();
    }

    /**
     * Inicializa e configura o template engine.
     * @return Engine
     */
    private function initEngine()
    {
        $engine = new Engine($this->templates_path, null);

        $engine->registerFunction('decimal', function ($value, $decimals = 2) {
            static $nf;
            if (null === $nf) {
                $nf = $this->initFormatter();
            }
            if ($decimals !== 2) {
                $nf->setAttribute(\NumberFormatter::MAX_FRACTION_DIGITS, $decimals);
                $nf->setAttribute(\NumberFormatter::MIN_FRACTION_DIGITS, $decimals);
            }
            return $nf->format($value);
        });

        $engine->registerFunction('list', function ($elements) {
            $output = '<ul>';
            foreach ($elements as $element) {
                $output .= '<li>' . $element . '</li>';
            }
            $output .= '</ul>';
            return $output;
        });

        return $engine;
    }

    /**
     * Inicializa e configura o formatador de valores numéricos.
     * @return \NumberFormatter
     */
    private function initFormatter()
    {
        $formatter = new \NumberFormatter('pt-BR', \NumberFormatter::DECIMAL);
        $formatter->setAttribute(\NumberFormatter::MAX_FRACTION_DIGITS, 2);
        $formatter->setAttribute(\NumberFormatter::MIN_FRACTION_DIGITS, 2);
        return $formatter;
    }

    /**
     * Acrescenta um Vencimento ao contracheque. Exemplos:
     * <br>salário normal, férias, décimo terceiro, etc.
     * @param \ledat\contracheque\Vencimento $vencimento
     * @return $this
     */
    public function addVencimento(Vencimento $vencimento)
    {
        $this->itens[] = $vencimento;
        return $this;
    }

    /**
     * Acrescenta um Desconto ao contracheque. Exemplos:
     * <br>vale refeição, vale transporte, contribuição sindical, etc.
     * @param \ledat\contracheque\Desconto $desconto
     * @return $this
     */
    public function addDesconto(Desconto $desconto)
    {
        $this->itens[] = $desconto;
        return $this;
    }

    /**
     * Configura os parâmetros do funcionário.
     * @param string $nome
     * @param string $codigo
     * @param string $funcao
     * @param string $cbo
     * @param string $admissao
     * @return $this
     */
    public function setFuncionario($nome, $codigo, $funcao, $cbo, $admissao)
    {
        $this->funcionario = [$nome, $codigo, $funcao, $cbo, $admissao];
        return $this;
    }

    /**
     * Configura os parâmetros para exibição do INSS entre os descontos.
     * @param integer $codigo
     * @param string $descricao
     * @return $this
     */
    public function setINSSParams($codigo, $descricao)
    {
        $this->inss_params = [$codigo, $descricao];
        return $this;
    }

    /**
     * Configura os parâmetros para exibição do IRRF entre os descontos.
     * @param integer $codigo
     * @param string $descricao
     * @param integer $dependentes
     * @return $this
     */
    public function setIRRFParams($codigo, $descricao, $dependentes = 0)
    {
        $this->irrf_params = [$codigo, $descricao, $dependentes];
        return $this;
    }

    /**
     * Especifica as linhas do cabeçalho esquerdo. Normalmente, estas são:
     * <br>- Razão social do empregador;
     * <br>- CNPJ do empregador;
     * <br>- Opcionalmente, endereço do empregador.
     * @param string $line1
     * @param string $line2
     * @param string $line3
     * @return $this
     */
    public function setLeftHeader($line1, $line2, $line3 = '')
    {
        $this->left_header = [$line1, $line2, $line3];
        return $this;
    }

    /**
     * Especifica as linhas do cabeçalho direito. Normalmente, estas são:
     * <br>- Título (folha de pagamento / recibo de pagamento etc);
     * <br>- Competência;
     * <br>- Vazio.
     * @param string $line1
     * @param string $line2
     * @param string $line3
     * @return $this
     */
    public function setRightHeader($line1, $line2, $line3 = '')
    {
        $this->right_header = [$line1, $line2, $line3];
        return $this;
    }

    /**
     * Especifica o arquivo de template do contracheque.<br>
     * Valor padrão: <code>contracheque.php</code>.
     * @param string $filename
     * @return $this
     */
    public function setTemplate($filename)
    {
        $this->template = $filename;
        return $this;
    }

    /**
     * Especifica o diretório dos arquivos de template.<br>
     * Valor padrão: <code>/vendor/ledat/contracheque/templates/</code>.
     * @param string $path
     * @return $this
     * @throws \Exception se o caminho especificado não existir.
     */
    public function setTemplatesPath($path)
    {
        if (!is_dir($path)) {
            throw new \Exception("Caminho informado não é válido.");
        }
        $this->templates_path = $path;
        return $this;
    }

    /**
     * Determina as bases de cálculo do FGTS, do INSS e do IRRF
     * com base na configuração dos vencimentos.
     */
    private function calcularBases()
    {
        $this->base_fgts = 0;
        $this->base_inss = 0;
        $this->base_irrf = 0;

        foreach ($this->itens as $item) {
            if ($item instanceof Vencimento) {
                $valor = $item->getValor();
                if ($item->incideFGTS()) {
                    $this->base_fgts = bcadd($this->base_fgts, $valor);
                }
                if ($item->incideINSS()) {
                    $this->base_inss = bcadd($this->base_inss, $valor);
                }
                if ($item->incideIRRF()) {
                    $this->base_irrf = bcadd($this->base_irrf, $valor);
                }
                if (null === $this->salario_base) {
                    $this->salario_base = $valor;
                }
            }
        }
    }

    /**
     * Calcula o total de vencimentos, descontos e o salário líquido.
     */
    private function calcularTotais()
    {
        $this->total_vencimentos = 0;
        $this->total_descontos = 0;
        $this->valor_liquido = 0;

        foreach ($this->itens as $item) {
            $valor = $item->getValor();
            if ($item instanceof Vencimento) {
                $this->total_vencimentos = bcadd($this->total_vencimentos, $valor);
            } else {
                $this->total_descontos = bcadd($this->total_descontos, $valor);
            }
        }

        $this->valor_liquido = bcsub($this->total_vencimentos, $this->total_descontos);
    }

    /**
     * Processa os vencimentos e descontos, calcula os impostos e os totais.
     * @return imposto\ImpostoInterface[]
     */
    private function preparar()
    {
        $this->calcularBases();

        $ano = $this->ano;
        $mes = $this->mes;
        $dependentes = $this->irrf_params[2];

        $fgts = new FGTS($this->base_fgts, $ano, $mes);

        $inss = new INSS($this->base_inss, $ano, $mes);
        if (($valor = $inss->valorImposto()) > 0) {
            list($cod, $desc) = $this->inss_params;
            $ref = round($inss->aliquota() * 100, 2, PHP_ROUND_HALF_EVEN);
            $ref = $this->formatter->format($ref);
            $this->addDesconto(new Desconto($cod, $desc, $ref, $valor));
        }

        $irrf = new IRRF($this->base_irrf, $ano, $mes, $dependentes);
        if (($valor = $irrf->valorImposto()) > 0) {
            list($cod, $desc) = $this->irrf_params;
            $ref = round($irrf->aliquota() * 100, 2, PHP_ROUND_HALF_EVEN);
            $ref = $this->formatter->format($ref);
            $this->addDesconto(new Desconto($cod, $desc, $ref, $valor));
        }

        $this->calcularTotais();

        return [$fgts, $inss, $irrf];
    }

    /**
     * Prepara os vencimentos e descontos para exibição no contracheque.
     * @return array
     */
    private function prepararItens()
    {
        $vencimentos = array_filter($this->itens, function ($item) {
            return $item instanceof Vencimento;
        });
        $descontos = array_filter($this->itens, function ($item) {
            return $item instanceof Desconto;
        });
        $itens = array_map(function (ItemInterface $item) {
            $valor = round($item->getValor(), 2, PHP_ROUND_HALF_EVEN);
            $valor = $this->formatter->format($valor);
            return [
                'codigo' => $item->getCodigo(),
                'descricao' => $item->getDescricao(),
                'referencia' => $item->getReferencia(),
                'vencimentos' => $item instanceof Vencimento ? $valor : '',
                'descontos' => $item instanceof Desconto ? $valor : '',
            ];
        }, array_merge($vencimentos, $descontos));

        return [
            'codigo' => array_column($itens, 'codigo'),
            'descricao' => array_column($itens, 'descricao'),
            'referencia' => array_column($itens, 'referencia'),
            'vencimentos' => array_column($itens, 'vencimentos'),
            'descontos' => array_column($itens, 'descontos'),
        ];
    }

    /**
     * MOER.BAT
     * @return $this
     * @throws \Exception se os parâmetros do funcionário não estiverem definidos.
     */
    public function generate()
    {
        if (empty($this->funcionario)) {
            throw new \Exception('Parâmetros do funcionário não informados.');
        }

        list($fgts, $inss, $irrf) = $this->preparar();
        $itens = $this->prepararItens();

        $params = [
            'name' => 'Jonathan',
            'fgts' => $fgts,
            'inss' => $inss,
            'irrf' => $irrf,
            'left_header' => $this->left_header,
            'right_header' => $this->right_header,
            'funcionario' => $this->funcionario,
            'itens' => $itens,
            'salario_base' => $this->salario_base,
            'total_descontos' => $this->total_descontos,
            'total_vencimentos' => $this->total_vencimentos,
            'valor_liquido' => $this->valor_liquido,
            'writing_pdf' => $this->writing_pdf,
        ];
        $this->output = $this->engine->render($this->template, $params);
        return $this;
    }

    /**
     * Retorna o HTML do contracheque.
     * @return string
     */
    public function asHTML()
    {
        if (null === $this->output) {
            $this->generate();
        }
        return $this->output;
    }

    /**
     * Gera um iframe contendo o contracheque.
     * @param array $attributes
     * @return string
     */
    public function asIframe(array $attributes = [])
    {
        $base64_uri = urlencode(base64_encode($this->asHTML()));
        $attributes = array_filter(array_merge([
            'height' => '600',
            'width' => '800',
        ], $attributes));

        $attributes['src'] = "data:text/html;charset=UTF-8;base64,{$base64_uri}";
        $attributes = trim(implode(' ', array_map(function ($k, $v) {
            return sprintf('%s="%s"', $k, $v);
        }, array_keys($attributes), $attributes)));

        return sprintf('<iframe %s></iframe>', $attributes);
    }

    /**
     * Salva o contracheque como PDF.
     * @param string $filename
     * @return boolean true se a operação tiver sucesso; false em caso de erro.
     */
    public function savePDF($filename)
    {
        $this->writing_pdf = true;
        $this->generate();
        $this->writing_pdf = false;

        $root = dirname(__DIR__);
        $arch = $this->getArchitecture();
        $prog = "{$root}/vendor/h4cc/wkhtmltopdf-{$arch}/bin/wkhtmltopdf-{$arch}";
        $args = '-B 0 -L 0 -R 0 -T 0 --no-outline --disable-javascript';
        $html = $this->asHTML();

        $cmd = <<<NOWDOC
cat <<EOF | {$prog} {$args} - {$filename} 2>&1 &
{$html}
EOF
NOWDOC;

        ob_start();
        passthru($cmd);
        $output = trim(ob_get_clean());

        return strpos($output, 'Done') !== false;
    }

    /**
     * Determina se a arquitetura do processador é 32bit ou 64bit.
     * @link https://gist.github.com/h4cc/6318527 Fonte
     * @return string
     */
    private function getArchitecture()
    {
        // Switch architecture if needed
        if (2147483647 == PHP_INT_MAX) {
            $architecture = 'i386';
        } else {
            $architecture = 'amd64';
        }
        return $architecture;
    }
}
