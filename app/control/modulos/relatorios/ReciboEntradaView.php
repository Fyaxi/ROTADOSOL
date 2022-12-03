<?php
/**
 * ReciboEntradaView TSession::getValue('unit_database')
 *
 * @version    2.0
 * @package    Sistema Integrado
 * @subpackage Módulos
 * @author     Jairo Barreto
 * @copyright  Copyright (c) 2021 Nativus Tecnologia. (http://www.nativustecnologia.com.br)
 * 
 */
class ReciboEntradaView extends TPage
{
    private $html;
    
    public function __construct()
    {
        parent::__construct();
        
        $this->html = new THtmlRenderer('app/resources/fatura.html');
        
        $fatura = new stdClass;
        $fatura->id = '13123123';
        $fatura->dt_pedido = date('Y-m-d');
        $fatura->metodo = 'Paypal';
        $fatura->conta = 'john@email.com';
        $fatura->frete = 1300;

        $cliente = new stdClass;
        $cliente->nome = 'Pedro da silva';
        $cliente->endereco = 'Rua do pedrinho, 123';
        $cliente->complemento = 'Apto 123';
        $cliente->cidade = 'Porto Alegre';
        
        $replaces = [];
        $replaces['fatura'] = $fatura;
        $replaces['cliente'] = $cliente;
        
        $replaces['items'] = [
            [ 'id' => '001',
              'descricao' => 'COMBO FESTA NOITE',
              'preco' => 3500,
              'qtde' => 1
            ],
            [ 'id' => '002',
              'descricao' => 'CONVIDADO ADICIONADO',
              'preco' => 50,
              'qtde' => 12
            ],
            [ 'id' => '003',
              'descricao' => 'COMBO ADICIONAL SALGADINHOS',
              'preco' => 350,
              'qtde' => 1
            ],
            [ 'id' => '004',
              'descricao' => 'PROJETO BALÕES',
              'preco' => 350,
              'qtde' => 2
            ]
        ];
        
        $this->html->enableSection('main', $replaces);

        $panel = new TPanelGroup('Fatura');
        $panel->add($this->html);
        
        $panel->addHeaderActionLink('Exportar', new TAction([$this, 'onExportPDF'], ['static'=>'1']), 'fa:save');
        
        parent::add($panel);
    }

    public function onAbrir($param)
    {
        if (isset($param))
        {
            echo '<pre>';
            print_r($param);
            echo '</pre>';
        }
        //$selecao = new TSession::getValue(__CLASS__.'_selecao)
        //$selecao = new TSession::setValue(__CLASS__.'_selecao, selecao)
        $this->onReload($param);
    }

    public function onGerar($param)
    {
        if (isset($param))
        {
            echo '<pre>';
            print_r($param);
            echo '</pre>';
        }
        //$selecao = new TSession::getValue(__CLASS__.'_selecao)
        //$selecao = new TSession::setValue(__CLASS__.'_selecao, selecao)
        $this->onReload($param);
    }
    
    public function onExportPDF($param)
    {
        try
        {
            $this->html = new THtmlRenderer('app/resources/fatura.html');
        
            $fatura = new stdClass;
            $fatura->id = '13123123';
            $fatura->dt_pedido = date('Y-m-d');
            $fatura->metodo = 'Paypal';
            $fatura->conta = 'john@email.com';
            $fatura->frete = 1300;

            $cliente = new stdClass;
            $cliente->nome = 'Pedro da silva';
            $cliente->endereco = 'Rua do pedrinho, 123';
            $cliente->complemento = 'Apto 123';
            $cliente->cidade = 'Porto Alegre';
            
            $replaces = [];
            $replaces['fatura'] = $fatura;
            $replaces['cliente'] = $cliente;
            
            $replaces['items'] = [
                [ 'id' => '001',
                'descricao' => 'COMBO FESTA NOITE',
                'preco' => 3500,
                'qtde' => 1
                ],
                [ 'id' => '002',
                'descricao' => 'CONVIDADO ADICIONADO',
                'preco' => 50,
                'qtde' => 12
                ],
                [ 'id' => '003',
                'descricao' => 'COMBO ADICIONAL SALGADINHOS',
                'preco' => 350,
                'qtde' => 1
                ],
                [ 'id' => '004',
                'descricao' => 'PROJETO BALÕES',
                'preco' => 350,
                'qtde' => 2
                ]
            ];
        
            $this->html->enableSection('main', $replaces);

            // string with HTML contents
            $html = clone $this->html;
            $contents = file_get_contents('app/resources/styles-print.html') . $html->getContents();

            $options = new \Dompdf\Options();
            $options->setChroot(getcwd());

            // converts the HTML template into PDF
            $dompdf = new \Dompdf\Dompdf($options);
            $dompdf->loadHtml($contents);
            $dompdf->setPaper('A4', 'portrait');
            $dompdf->render();
            
            $file = 'app/output/fatura.pdf';
            
            // write and open file
            file_put_contents($file, $dompdf->output());
            
            $window = TWindow::create('Recibo', 0.8, 0.8);
            $object = new TElement('object');
            $object->data  = $file;
            $object->type  = 'application/pdf';
            $object->style = "width: 100%; height:calc(100% - 10px)";
            $window->add($object);
            $window->show();
        }
        catch (Exception $e)
        {
            new TMessage('error', $e->getMessage());
        }
    }
}
