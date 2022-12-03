<?php
/**
 * DecoracaoList TSession::getValue('unit_database')
 *
 * @version    2.0
 * @package    Sistema Integrado
 * @subpackage Módulos
 * @author     Jairo Barreto
 * @copyright  Copyright (c) 2021 Nativus Tecnologia. (http://www.nativustecnologia.com.br)
 * 
 */
class DecoracaoList extends TPage
{
    protected $form;     // registration form
    protected $datagrid; // listing
    protected $pageNavigation;
    protected $formgrid;
    protected $deleteButton;
    
    use Adianti\base\AdiantiStandardListTrait;
    
    /**
     * Page constructor
     */
    public function __construct()
    {
        parent::__construct();
        
        $this->setDatabase(TSession::getValue('unit_database'));            // defines the database
        $this->setActiveRecord('Decoracao');   // defines the active record
        $this->setDefaultOrder('id', 'desc');         // defines the default order
        $this->setLimit(50);
        // $this->setCriteria($criteria) // define a standard filter

        $this->addFilterField('id', '=', 'id'); // filterField, operator, formField
        $this->addFilterField('cliente_id', '=', 'cliente_id'); // filterField, operator, formField
        $this->addFilterField('tipo_contrato_id', '=', 'tipo_contrato_id'); // filterField, operator, formField
        $this->addFilterField('ativo', 'like', 'ativo'); // filterField, operator, formField
        $this->addFilterField('agendado', 'like', 'agendado'); // filterField, operator, formField
        $this->addFilterField('mes', '=', 'mes'); // filterField, operator, formField
        $this->addFilterField('ano', '=', 'ano'); // filterField, operator, formField
        $this->addFilterField('dt_inicio', '=', 'dt_inicio'); // filterField, operator, formField
        $this->setOrderCommand('cliente->nome_fantasia', '(SELECT nome_fantasia FROM pessoa WHERE pessoa.id=decoracao.cliente_id)');
        
        // creates the form
        $this->form = new BootstrapFormBuilder('form_search_Contrato');
        $this->form->setFormTitle('Decorações');
        $this->form->enableCSRFProtection(); // ATIVA PROTEÇÃO CONTA EXECUÇÃO DE JAVA SCRIPT MALICIOSO
        

        // create the form fields
        $id = new TEntry('id');
        $cliente_id = new TDBUniqueSearch('cliente_id', TSession::getValue('unit_database'), 'Pessoa', 'id', 'nome_fantasia');
        $tipo_contrato_id = new TDBUniqueSearch('tipo_contrato_id', TSession::getValue('unit_database'), 'TipoDecoracao', 'id', 'nome');
        $ativo = new TRadioGroup('ativo');
        $aniversariante = new TEntry('aniversariante');
        $dt_inicio = new TDate('dt_inicio');

        $mes = new TRadioGroup('mes');
        $ano = new TRadioGroup('ano');
        $current = (int) date('Y');
        $mes->addItems( ['01' => 'Jan', '02' => 'Fev', '03' => 'Mar', '04' => 'Abr', '05' => 'Mai', '06' => 'Jun', '07' => 'Jul', '08' => 'Ago', '09' => 'Set', '10' => 'Out', '11' => 'Nov', '12' => 'Dez'] );
        $ano->addItems( [ ($current -5) => ($current -5), ($current -4) => ($current -4), ($current -3) => ($current -3), ($current -2) => ($current -2), ($current -1) => ($current -1), $current => $current ] );

        $mes->setLayout('horizontal');
        $ano->setLayout('horizontal');

        $cliente_id->setMinLength(0);
        $tipo_contrato_id->setMinLength(0);
        $ativo->addItems( ['Y' => 'Sim', 'N' => 'Não', '' => 'Ambos'] );
        $ativo->setLayout('horizontal');
        
        // add the fields
        $this->form->addFields( [ new TLabel('N°') ], [ $id ] );
        $this->form->addFields( [ new TLabel('Cliente') ], [ $cliente_id ], [ new TLabel('Aniversariante') ], [ $aniversariante ] );
        $this->form->addFields( [ new TLabel('Tipo Festa') ], [ $tipo_contrato_id ], [ new TLabel('Data') ], [ $dt_inicio ]  );
        $this->form->addFields( [ new TLabel('Mes') ], [ $mes ] );
        $this->form->addFields( [ new TLabel('Ano') ], [ $ano ] );
        $this->form->addFields( [ new TLabel('Cancelados?') ], [ $ativo ] );

        // set sizes
        $id->setSize('15%');
        $cliente_id->setSize('100%');
        $aniversariante->setSize('100%');
        $tipo_contrato_id->setSize('100%');
        $ativo->setSize('100%');
        $dt_inicio->setSize('100%');

        //$this->form->addExpandButton('Expandir' , 'fa:search', true);

        
        // keep the form filled during navigation with session data
        $this->form->setData( TSession::getValue(__CLASS__.'_filter_data') );
        
        // add the search form actions
        $btn = $this->form->addAction(_t('Find'), new TAction([$this, 'onSearch']), 'fa:search');
        $btn->class = 'btn btn-sm btn-primary';
        $this->form->addActionLink('<b>Agendar Decoração</b>', new TAction(['DecoracaoForm', 'onEdit'], ['register_state' => 'false']), 'fa:plus green');
        
        // creates a Datagrid
        $this->datagrid = new BootstrapDatagridWrapper(new TDataGrid);
        $this->datagrid->style = 'width: 100%';
        $this->datagrid->datatable = 'true';
        //$this->datagrid->enablePopover('Popover', 'Hi <b> {name} </b>');
        

        // creates the datagrid columns
        $column_id = new TDataGridColumn('id', 'FESTA N°', 'center',  '10%');
        $column_cliente_id = new TDataGridColumn('cliente->nome_fantasia', 'CLIENTE', 'left');
        $column_aniversariante = new TDataGridColumn('aniversariante', 'ANIVERSARIANTE', 'CENTER');
        $column_tipo_contrato_id = new TDataGridColumn('tipo_contrato->nome', 'TIPO', 'CENTER');
        $column_dt_inicio = new TDataGridColumn('dt_inicio', 'DATA', 'CENTER');
        //$column_dt_fim = new TDataGridColumn('dt_fim', 'Dt Fim', 'left');
        $column_ativo = new TDataGridColumn('agendado', 'RESERVADO', 'CENTER');

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
        
        $column_id->setTransformer( function ($value, $object, $row) {
            if ($object->ativo == 'N')
            {
                $row->style= 'color: silver';
            }
            
            return $value;
        });
        
        $column_dt_inicio->setTransformer( function($value) {
            return TDate::convertToMask($value, 'yyyy-mm-dd', 'dd/mm/yyyy');
        });
        /*
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
        */
        // add the columns to the DataGrid
        $this->datagrid->addColumn($column_id);
        $this->datagrid->addColumn($column_cliente_id);
        $this->datagrid->addColumn($column_aniversariante);
        $this->datagrid->addColumn($column_tipo_contrato_id);
        $this->datagrid->addColumn($column_dt_inicio);
        //$this->datagrid->addColumn($column_dt_fim);
        $this->datagrid->addColumn($column_ativo);

        // creates the datagrid column actions
        $column_id->setAction(new TAction([$this, 'onReload']), ['order' => 'id']);
        $column_cliente_id->setAction(new TAction([$this, 'onReload']), ['order' => 'cliente->nome_fantasia']);
        $column_dt_inicio->setAction(new TAction([$this, 'onReload']), ['order' => 'dt_inicio']);
        
        $column_tipo_contrato_id->enableAutoHide(500);
        $column_dt_inicio->enableAutoHide(500);
        //$column_dt_fim->enableAutoHide(500);
        $column_ativo->enableAutoHide(500);
        
        $action1 = new TDataGridAction(['DecoracaoForm', 'onEdit'], ['id'=>'{id}']);
        $action2 = new TDataGridAction([$this, 'onAgendar'], ['id'=>'{id}']);
        $action3 = new TDataGridAction([$this, 'onGerarRecibo'], ['id'=>'{id}']);
        $action4 = new TDataGridAction([$this, 'onDeletarEvento'], ['id'=>'{id}']);
        
        $this->datagrid->addAction($action1, 'Editar Evento',   'far:edit blue');
        $this->datagrid->addAction($action2, 'Marcar/Desmarcar Evento', 'fa:calendar orange');
        $this->datagrid->addAction($action3, 'Gerar Recibo',   'fa:file-invoice-dollar green');
        $this->datagrid->addAction($action4, 'Cancelar Evento', 'far:trash-alt red');
        
        // create the datagrid model
        $this->datagrid->createModel();
        
        // creates the page navigation
        $this->pageNavigation = new TPageNavigation;
        $this->pageNavigation->setAction(new TAction([$this, 'onReload']));
        
        $panel = new TPanelGroup('<small>Relação de decorações</small>', 'white');
        $panel->add($this->datagrid);
        $panel->addFooter($this->pageNavigation);
        
        // header actions
        $dropdown = new TDropDown(_t('Export'), 'fa:list');
        $dropdown->setPullSide('right');
        $dropdown->setButtonClass('btn btn-default waves-effect dropdown-toggle');
        $dropdown->addAction( _t('Save as CSV'), new TAction([$this, 'onExportCSV'], ['register_state' => 'false', 'static'=>'1']), 'fa:table blue' );
        $dropdown->addAction( _t('Save as PDF'), new TAction([$this, 'onExportPDF'], ['register_state' => 'false', 'static'=>'1']), 'far:file-pdf red' );
        $panel->addHeaderWidget( $dropdown );
        
        // vertical box container
        $container = new TVBox;
        $container->style = 'width: 100%';
        // $container->add(new TXMLBreadCrumb('menu.xml', __CLASS__));
        $container->add($this->form);
        $container->add($panel);
        
        parent::add($container);
    }
    
    /**
     * Turn on/off an user
     */
    public function onDeletarEvento($param)
    {
        try
        {
            TTransaction::open(TSession::getValue('unit_database'));
            $decoracao = Decoracao::find($param['id']);
            
            if ($decoracao instanceof Decoracao)
            {
                $decoracao->ativo = $decoracao->ativo == 'Y' ? 'N' : 'Y';
                $decoracao->agendado = $decoracao->agendado == 'N';
                $decoracao->store();
                Evento::where('decoracao_id', '=', $decoracao->id)->delete();
            }
            
            TTransaction::close();
            
            $this->onReload($param);
        }
        catch (Exception $e)
        {
            new TMessage('error', $e->getMessage());
            TTransaction::rollback();
        }
    }

    /**
     * Agendar Decoração
     */
    public function onAgendar($param)
    {
        try
        {
            TTransaction::open(TSession::getValue('unit_database'));
            $decoracao = Decoracao::find($param['id']);

            if( $decoracao->ativo == 'Y')
            {
                if ($decoracao instanceof Decoracao)
                {
                    $decoracao->agendado = $decoracao->agendado == 'Y' ? 'N' : 'Y';
                    $decoracao->store();
                }

                if($decoracao->agendado == 'Y')
                {
                    $evento_criado  = Evento::where('decoracao_id', '=', $decoracao->id)->load();
                    //print_r($evento_criado);
                    if(!$evento_criado)
                    {
                        $tipo_contrato = new TipoDecoracao($decoracao->tipo_contrato_id);

                        $evento = new Evento;
                        $evento->cor            = $tipo_contrato->cor;
                        $evento->titulo         = 'Decoração '.$decoracao->aniversariante.' | '.$decoracao->convidados.' Conv.';
                        $evento->descricao      = $decoracao->obs;
                        $evento->inicio         = $decoracao->dt_inicio;
                        $evento->fim            = $decoracao->dt_fim;
                        $evento->decoracao_id   = $decoracao->id;
                        $evento->system_user_id = TSession::getValue('userid');
                        $evento->store();
                    }
                }
                else
                {
                    Evento::where('decoracao_id', '=', $decoracao->id)->delete();
                }
            }
            else
            {
                $pos_action = new TAction(['DecoracaoList', 'onReload']);
                new TMessage('danger', 'Não é possível agendar uma decoração cancelada!', $pos_action);
            }

            TTransaction::close();
            $this->onReload($param);
        }
        catch (Exception $e)
        {
            new TMessage('error', $e->getMessage());
        }
    }

    public function onGerarRecibo($param)
    {
        try
        {
            $this->html = new THtmlRenderer('app/resources/relat/ReciboEntradaDecoracao.html');
        
            TTransaction::open(TSession::getValue('unit_database'));

            $contrato = Decoracao::find($param['id']);
            $pessoa = Pessoa::find($contrato->cliente_id);
            $contrato_item = DecoracaoItem::where('contrato_id', '=', $param['key'])->load();   
            
            // Configurações GLOBAIS
            $relatorio = new stdClass;
            $relatorio->dt_atual = date('Y-m-d');

            // Informações sobre o evento
            $evento                 = new stdClass;
            $evento->id             = $contrato->id;
            $evento->data           = $contrato->dt_inicio;
            $evento->valor_entrada  = $contrato->ValorEntrada;
            $evento->valor_base     = $contrato->ValorContrato;

            // Informações sobre os itens do evento
            if ($contrato_item)
            {
                $relat_itens = array();
                foreach($contrato_item as $item)
                {
                    array_push($relat_itens, array( 
                        "id"        => $item->id, 
                        "descricao" => $item->servico_id, 
                        "preco"     => $item->valor, 
                        "qtde"      => $item->quantidade
                    ));
                }
            } 

            // Informações sobre o cliente
            $cliente                = new stdClass;
            $cliente->nome          = $pessoa->nome_fantasia;
            $cliente->cpf           = $pessoa->codigo_nacional;
            
            // Substituição das variáveis no html relatório
            $replaces = []; 
            $replaces['relatorio']  = $relatorio;
            $replaces['evento']     = $evento;
            $replaces['cliente']    = $cliente;
            $replaces['items']      = $relat_itens;

            //echo '<pre>';
            //print_r($replaces);
            //echo '</pre>';
        
            // Execução do replace
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
            
            $window = TWindow::create('Recibo Decoração', 0.8, 0.8);
            $object = new TElement('object');
            $object->data  = $file;
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
}
