# ledat/contracheque

Biblioteca para geração de contracheques em HTML e PDF.

## Instalação
```
composer require "ledat/contracheque":"0.1"
```

## Exemplo

```php
$cc = Contracheque::create('2016', '10')
    ->setFuncionario('IGOR TENAZ', 2217, 'GERENTE DE MARKETING', 317110, '01/02/2014')
    ->setLeftHeader('COCA COLA INDUSTRIAS LTDA', '45.997.418/0001-53')
    ->addVencimento(new Vencimento(1, 'HORAS NORMAIS', '220:00', 3000.00))
    ->addVencimento(new Vencimento(204, 'ASSIDUIDADE', '800,00', 800.00))
    ->addDesconto(new Desconto(48, 'AUXÍLIO COMBUSTÍVEL', '6,00', 150.00));

// exibir o contracheque como html
echo $cc->asHtml();

// exibir o contracheque dentro de um iframe
echo $cc->asIframe();

// salvar como PDF
if ($cc->savePDF('/caminho/para/contracheque.pdf')) {
    // arquivo salvo com sucesso.
}

```

## Documentação
**TODO**
