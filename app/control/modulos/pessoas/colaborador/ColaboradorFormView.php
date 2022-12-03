<?php
/**
 * ContratoDashboard TSession::getValue('unit_database')
 *
 * @version    2.0
 * @package    Sistema Integrado
 * @subpackage Módulos
 * @author     Jairo Barreto
 * @copyright  Copyright (c) 2021 Nativus Tecnologia. (http://www.nativustecnologia.com.br)
 * 
 */
class ColaboradorFormView extends TPage
{
    protected $form; // form
    protected $detail_list_contratos;
    protected $detail_list_contas;
    
    /**
     * Page constructor
     */
    public function __construct($param)
    {
        parent::__construct();
        
        parent::setTargetContainer('adianti_right_panel');

        $this->form = new BootstrapFormBuilder('form_ColaboradorView');
        $this->form->setFormTitle('Colaborador');
        $this->form->setColumnClasses(2, ['col-sm-3', 'col-sm-9']);
        $this->form->enableCSRFProtection(); // ATIVA PROTEÇÃO CONTA EXECUÇÃO DE JAVA SCRIPT MALICIOSO
        
        $dropdown = new TDropDown('Opções', 'fa:th');
        //$dropdown->addAction(
        $dropdown->addAction( 'Imprimir', new TAction([$this, 'onPrint'], ['key'=>$param['key'], 'static' => '1']), 'far:file-pdf red');
        //$dropdown->addAction( 'Gerar etiqueta', new TAction([$this, 'onGeraEtiqueta'], ['key'=>$param['key'], 'static' => '1']), 'far:envelope purple');
        $dropdown->addAction( 'Editar', new TAction(['ColaboradorForm', 'onEdit'],['key'=>$param['key']]), 'far:edit blue');
        $dropdown->addAction( 'Fechar', new TAction([$this, 'onClose']), 'fa:times red');
        
        $this->form->addHeaderWidget($dropdown);
        
        // vertical box container
        $container = new TVBox;
        $container->style = 'width: 100%'; 
        // $container->add(new TXMLBreadCrumb('menu.xml', 'ColaboradorList'));
        $container->add($this->form);

        parent::add($container);
    }
    
    /**
     * onEdit
     */
    public function onEdit($param)
    {
        try
        {
            TTransaction::open(TSession::getValue('unit_database'));
            $master_object = new Colaborador($param['key']);
            
            $label_id = new TLabel('Id:', '#333333', '12px', '');
            $label_nome_fantasia = new TLabel('Nome:', '#333333', '12px', '');
            $label_codigo_nacional = new TLabel('CPF:', '#333333', '12px', '');
            $label_fone = new TLabel('Fone:', '#333333', '12px', '');
            $label_email = new TLabel('Email:', '#333333', '12px', '');
            $label_cidade = new TLabel('Local:', '#333333', '12px', '');
            $label_created_at = new TLabel('Criado em:', '#333333', '12px', '');
            $label_updated_at = new TLabel('Alterado em:', '#333333', '12px', '');
            
            $text_id  = new TTextDisplay($master_object->id, '#333333', '12px', '');
            $text_nome_fantasia  = new TTextDisplay($master_object->nome_fantasia, '#333333', '12px', '');
            $text_codigo_nacional  = new TTextDisplay($master_object->codigo_nacional, '#333333', '12px', '');
            $text_fone  = new THyperLink('<i class="fa fa-phone-square-alt"></i> '.$master_object->fone, 'callto:'.$master_object->fone, '#007bff', '12px', '');
            $text_email  = new THyperLink('<i class="fa fa-envelope"></i> ' . $master_object->email, 'https://mail.google.com/mail/u/0/?view=cm&fs=1&to='.$master_object->email.'&tf=1', '#007bff', '12px', '');
            $link_maps = 'https://www.google.com/maps/place/' . $master_object->logradouro . ',' . 
                                                                $master_object->numero . ', ' .
                                                                $master_object->bairro . ', ' .
                                                                $master_object->cidade->nome . '+' .
                                                                $master_object->cidade->estado->uf;
            $text_cidade  = new THyperLink('<i class="fa fa-map-marker-alt"></i> Link para google maps', $link_maps, '#007bff', '12px', '');
            $text_created_at  = new TTextDisplay(TDateTime::convertToMask($master_object->created_at, 'yyyy-mm-dd hh:ii:ss', 'dd/mm/yyyy hh:ii:ss'), '#333333', '12px', '');
            $text_updated_at  = new TTextDisplay(TDateTime::convertToMask($master_object->updated_at, 'yyyy-mm-dd hh:ii:ss', 'dd/mm/yyyy hh:ii:ss'), '#333333', '12px', '');
            
            $this->form->addFields([$label_id],[$text_id]);
            $this->form->addFields([$label_nome_fantasia],[$text_nome_fantasia]);
            $this->form->addFields([$label_codigo_nacional],[$text_codigo_nacional]);
            $this->form->addFields([$label_fone],[$text_fone]);
            $this->form->addFields([$label_email],[$text_email]);
            $this->form->addFields([$label_cidade],[$text_cidade]);
            $this->form->addFields([$label_created_at],[$text_created_at]);
            $this->form->addFields([$label_updated_at],[$text_updated_at]);
            
            $this->detail_list_contratos = new BootstrapDatagridWrapper( new TDataGrid );
            $this->detail_list_contratos->style = 'width:100%';
            //$this->detail_list_contratos->disableDefaultClick();
            
            $column_tipo = $this->detail_list_contratos->addColumn( new TDataGridColumn('tipo_contrato->nome', 'Tipo', 'left') );
            $column_dt_inicio = $this->detail_list_contratos->addColumn( new TDataGridColumn('dt_inicio', 'Dt Inicio', 'left') );
            $column_dt_fim    = $this->detail_list_contratos->addColumn( new TDataGridColumn('dt_fim', 'Dt Fim', 'left') );
            $column_ativo = $this->detail_list_contratos->addColumn( new TDataGridColumn('ativo', 'Ativo', 'left') );
            
            $column_ativo->setTransformer( function ($value) {
                if ($value == 'Y')
                {
                    $div = new TElement('span');
                    $div->class="label label-success";
                    $div->style="text-shadow:none; font-size:12px";
                    $div->add('Sim');
                    return $div;
                }
                else
                {
                    $div = new TElement('span');
                    $div->class="label label-danger";
                    $div->style="text-shadow:none; font-size:12px";
                    $div->add('Não');
                    return $div;
                }
            });
            
            $column_tipo->setTransformer( function ($value, $object, $row) {
                if ($object->ativo == 'N')
                {
                    $row->style= 'color: silver';
                }
                
                return $value;
            });
            
            $column_dt_inicio->setTransformer( function($value) {
                return TDate::convertToMask($value, 'yyyy-mm-dd', 'dd/mm/yyyy');
            });
            
            $column_dt_fim->setTransformer( function($value, $object) {
                $today = new DateTime(date('Y-m-d'));
                $end   = new DateTime($value);
                $data = TDate::convertToMask($value, 'yyyy-mm-dd', 'dd/mm/yyyy');
                
                if ($object->ativo == 'Y' && !empty($value) && $today >= $end)
                {
                    $div = new TElement('span');
                    $div->class="label label-warning";
                    $div->style="text-shadow:none; font-size:12px";
                    $div->add($data);
                    return $div;
                }
                
                return $data;
            });
            
            
            $action1 = new TDataGridAction(['ContratoForm', 'onEdit'], ['id'=>'{id}']);
            $this->detail_list_contratos->addAction($action1, _t('Edit'),   'far:edit blue');
            
            $this->detail_list_contratos->createModel();
            
            $items = Contrato::where('cliente_id', '=', $master_object->id)->orderBy('id', 'desc')->load();
            $this->detail_list_contratos->addItems($items);
            
            $panel = new TPanelGroup('Últimas Festas', '#f5f5f5');
            $panel->add($this->detail_list_contratos)->style = 'overflow-x:auto';
            //$this->form->addContent([$panel]);
            
            
            
            $this->detail_list_contas = new BootstrapDatagridWrapper( new TDataGrid );
            $this->detail_list_contas->style = 'width:100%';
            $this->detail_list_contas->disableDefaultClick();
            
            $column_dt_emissao = $this->detail_list_contas->addColumn( new TDataGridColumn('dt_emissao', 'Emissao', 'left') );
            $column_dt_vencimento = $this->detail_list_contas->addColumn( new TDataGridColumn('dt_vencimento', 'Vencimento', 'left') );
            $column_dt_pagamento = $this->detail_list_contas->addColumn( new TDataGridColumn('dt_pagamento', 'Pagamento', 'left') );
            $column_valor = $this->detail_list_contas->addColumn( new TDataGridColumn('valor', 'Valor', 'right') );
            
            $column_dt_emissao->setTransformer( function($value, $object, $row) {
                if ($object->ativo == 'N')
                {
                    $row->style= 'color: silver';
                }
                return TDate::convertToMask($value, 'yyyy-mm-dd', 'dd/mm/yyyy');
            });
            
            $column_dt_pagamento->setTransformer( function($value, $object) {
                if ($object->ativo == 'N')
                {
                    return 'cancelada';
                }
                
                if ($value)
                {
                    $value = TDate::convertToMask($value, 'yyyy-mm-dd', 'dd/mm/yyyy');
                    $label = 'success';
                }
                else
                {
                    $value = 'aguardando';
                    $label = 'warning';
                }
                
                
                $div = new TElement('span');
                $div->class="label label-" . $label;
                $div->style="text-shadow:none; font-size:12px";
                $div->add($value);
                return $div;
            });
            
            $column_dt_vencimento->setTransformer( function($value, $object) {
                $today = new DateTime(date('Y-m-d'));
                $end   = new DateTime($value);
                $data = TDate::convertToMask($value, 'yyyy-mm-dd', 'dd/mm/yyyy');
                
                if ($object->ativo == 'Y' && empty($object->dt_pagamento) && !empty($value) && $today >= $end)
                {
                    $div = new TElement('span');
                    $div->class="label label-warning";
                    $div->style="text-shadow:none; font-size:12px";
                    $div->add($data);
                    return $div;
                }
                
                return $data;
            });
            
            $column_valor->setTransformer( function($value) {
                if (is_numeric($value)) {
                    return 'R$&nbsp;'.number_format($value, 2, ',', '.');
                }
                return $value;
            });
            
            $this->detail_list_contas->createModel();
            //$items = ContaReceber::where('pessoa_id', '=', $master_object->id)->orderBy('id', 'desc')->take(10)->load();
            $items = ContaPagar::where('pessoa_id', '=', $master_object->id)->orderBy('id', 'desc')->load();
            $this->detail_list_contas->addItems($items);
            
            $panel = new TPanelGroup('Hístorico de Pagamentos ', '#f5f5f5');
            $panel->add($this->detail_list_contas)->style = 'overflow-x:auto';
            //$this->form->addContent([$panel]);
            
            TTransaction::close();
        }
        catch (Exception $e)
        {
            new TMessage('error', $e->getMessage());
        }
    }
   
    /**
     * Imprime a view
     */
    public function onPrint($param)
    {
        try
        {
            $this->onEdit($param);
            
            $n = rand(99999, 99999999);

            // string with HTML contents
            $html = clone $this->form;
            $contents = file_get_contents('app/resources/styles-print.html') . $html->getContents();
            
            // converts the HTML template into PDF
            $dompdf = new \Dompdf\Dompdf();
            $dompdf->loadHtml($contents);
            $dompdf->setPaper('A4', 'portrait');
            $dompdf->render();
            
            $file = 'app/output/p'.$n.'.pdf';
            
            // write and open file
            file_put_contents($file, $dompdf->output());
            
            $window = TWindow::create('Export', 0.8, 0.8);
            $object = new TElement('object');
            $object->data  = $file.'?rndval='.uniqid();
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
    
    /**
     * Gera etiqueta
     */
    public function onGeraEtiqueta($param)
    {
        try
        {
            $this->onEdit($param);

            $n = rand(99999, 99999999);
            
            TTransaction::open(TSession::getValue('unit_database'));
            $pessoa = new Colaborador($param['key']);
            
            $replaces = $pessoa->toArray();
            $replaces['cidade'] = $pessoa->cidade;
            $replaces['estado'] = $pessoa->cidade->estado;
            
            // string with HTML contents
            $html = new THtmlRenderer('app/resources/mail-label.html');
            $html->enableSection('main', $replaces);
            $contents = file_get_contents('app/resources/styles-print.html') . $html->getContents();
            
            // converts the HTML template into PDF
            $dompdf = new \Dompdf\Dompdf();
            $dompdf->loadHtml($contents);
            $dompdf->setPaper('A4', 'portrait');
            $dompdf->render();
            
            $file = 'app/output/fav'.$n.'.pdf';
            
            // write and open file
            file_put_contents($file, $dompdf->output());
            
            $window = TWindow::create('Export', 0.8, 0.8);
            $object = new TElement('object');
            $object->data  = $file.'?rndval='.uniqid();
            $object->type  = 'application/pdf';
            $object->style = "width: 100%; height:calc(100% - 10px)";
            $window->add($object);
            $window->show();
            
            TTransaction::close();
        }
        catch (Exception $e)
        {
            new TMessage('error', $e->getMessage());
        }
    }
    
    /**
     * Close side panel
     */
    public static function onClose($param)
    {
        TScript::create("Template.closeRightPanel()");
    }
}
