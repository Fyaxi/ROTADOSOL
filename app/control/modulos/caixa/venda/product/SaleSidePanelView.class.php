<?php
/**
 * SaleSidePanelView
 *
 * @version    1.0
 * @package    DBUNIDADE
 * @subpackage tutor
 * @author     Pablo Dall'Oglio
 * @copyright  Copyright (c) 2006 Adianti Solutions Ltd. (http://www.adianti.com.br)
 * @license    http://www.adianti.com.br/framework-license
 */
class SaleSidePanelView extends TPage
{
    protected $form; // form
    protected $detail_list;
    
    /**
     * Class constructor
     * Creates the page and the registration form
     */
    public function __construct($param)
    {
        parent::__construct();
        
        parent::setTargetContainer('adianti_right_panel');
        
        $this->form = new BootstrapFormBuilder('form_Sale_View');
        
        $this->form->setFormTitle('View Vendas');
        $this->form->setColumnClasses(2, ['col-sm-3', 'col-sm-9']);
        $this->form->addHeaderActionLink( _t('Print'), new TAction([$this, 'onPrint'], ['key'=>$param['key'], 'static' => '1']), 'far:file-pdf red');
        //$this->form->addHeaderActionLink( _t('Edit'), new TAction([$this, 'onEdit'], ['key'=>$param['key'], 'register_state'=>'true']), 'far:edit blue');
        $this->form->addHeaderActionLink( _t('Close'), new TAction([$this, 'onClose']), 'fa:times red');
        
        parent::add($this->form);
    }
    
    /**
     * Load content
     */
    public function onView($param)
    {
        try
        {
            TTransaction::open('DBUNIDADE');
            
            $master_object = new Venda($param['key']);
            
            $label_id = new TLabel('Nº:', '#333333', '12px', 'b');
            $label_date = new TLabel('Data:', '#333333', '12px', 'b');
            $label_total = new TLabel('Total:', '#333333', '12px', 'b');
            $label_customer_id = new TLabel('Cliente:', '#333333', '12px', 'b');
            $label_obs = new TLabel('Obs:', '#333333', '12px', 'b');
    
            $text_id  = new TTextDisplay($master_object->id, '#333333', '12px', '');
            $text_date2 = TDateTime::convertToMask($master_object->date, 'yyyy-mm-dd', 'dd/mm/yyyy');
            $text_date  = new TTextDisplay($text_date2, '#333333', '12px', '');
            $text_total  = new TTextDisplay($master_object->total, '#333333', '12px', '');
            $text_customer_id  = new TTextDisplay(Pessoa::find($master_object->customer_id)->nome_fantasia, '#333333', '12px', '');
            $text_obs  = new TTextDisplay($master_object->obs, '#333333', '12px', '');
    
            $this->form->addFields([$label_id],[$text_id]);
            $this->form->addFields([$label_customer_id],[$text_customer_id]);
            $this->form->addFields([$label_date],[$text_date]);
            $this->form->addFields([$label_total],[$text_total]);
            $this->form->addFields([$label_obs],[$text_obs]);
            
            $this->detail_list = new BootstrapDatagridWrapper( new TDataGrid );
            $this->detail_list->style = 'width:100%';
            $this->detail_list->disableDefaultClick();
            
            $product       = new TDataGridColumn('product->description',  'Produto', 'left');
            $price         = new TDataGridColumn('sale_price',  'Preço',    'right');
            $amount        = new TDataGridColumn('amount',  'Qtd',    'center');
            $discount      = new TDataGridColumn('discount',  'Desconto',    'right');
            $total         = new TDataGridColumn('total',  'Total',    'right');
            
            $this->detail_list->addColumn( $product );
            $this->detail_list->addColumn( $price );
            $this->detail_list->addColumn( $amount );
            $this->detail_list->addColumn( $discount );
            $this->detail_list->addColumn( $total );
            
            $format_value = function($value) {
                if (is_numeric($value)) {
                    return 'R$ '.number_format($value, 2, ',', '.');
                }
                return $value;
            };

            $format_value2 = function($value) {
                if (is_numeric($value)) {
                    return number_format($value, 2, ',', '.');
                }
                return $value;
            };
            
            $total->setTransformer($format_value);
            $price->setTransformer($format_value2);
            $discount->setTransformer($format_value2);
            
            // define totals
            $total->setTotalFunction( function($values) {
                return array_sum((array) $values);
            });
            
            $this->detail_list->createModel();
            
            $items = VendaItem::where('sale_id', '=', $master_object->id)->load();
            $this->detail_list->addItems($items);
            
            $panel = new TPanelGroup('Itens', '#f5f5f5');
            $panel->add($this->detail_list);
            $panel->getBody()->style = 'overflow-x:auto';
            
            $this->form->addContent([$panel]);
            TTransaction::close();
        }
        catch (Exception $e)
        {
            new TMessage('error', $e->getMessage());
        }
    }
    
    public function onPrint($param)
    {
        try
        {
            $this->onView($param);
            
            // string with HTML contents
            $html = clone $this->form;
            $contents = file_get_contents('app/resources/styles-print.html') . $html->getContents();
            
            $options = new \Dompdf\Options();
            $options->setChroot(getcwd());
            
            // converts the HTML template into PDF
            $dompdf = new \Dompdf\Dompdf($options);
            $dompdf->loadHtml($contents);
            $dompdf->setPaper('A4', 'portrait');
            $dompdf->render();
            
            $file = 'app/output/sale-export.pdf';
            
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
     * onEdit
     */
    public static function onEdit($param)
    {
        unset($param['static']);
        AdiantiCoreApplication::loadPage('SaleForm', 'onEdit', $param);
    }
    
    /**
     * Close side panel
     */
    public static function onClose($param)
    {
        TScript::create("Template.closeRightPanel()");
    }
}