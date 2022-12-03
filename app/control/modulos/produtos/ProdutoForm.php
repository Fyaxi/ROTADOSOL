<?php
/**
 * ContratoDashboard 'DBUNIDADE'
 *
 * @version    2.0
 * @package    Sistema Integrado
 * @subpackage Módulos
 * @author     Jairo Barreto
 * @copyright  Copyright (c) 2021 Nativus Tecnologia. (http://www.nativustecnologia.com.br)
 * 
 */
class ProdutoForm extends TPage
{
    protected $form; // form
    
    use Adianti\Base\AdiantiStandardFormTrait; // Standard form methods
    
    /**
     * Class constructor
     * Creates the page and the registration form
     */
    function __construct()
    {
        parent::__construct();
        
        parent::setTargetContainer('adianti_right_panel');
        $this->setAfterSaveAction( new TAction(['ProdutoList', 'onReload'], ['register_state' => 'true']) );

        $this->setDatabase('DBUNIDADE');              // defines the database
        $this->setActiveRecord('Produto');     // defines the active record
        
        // creates the form
        $this->form = new BootstrapFormBuilder('form_Produto');
        $this->form->setFormTitle('Produto');
        $this->form->setClientValidation(true);
        $this->form->setColumnClasses( 2, ['col-sm-5 col-lg-4', 'col-sm-7 col-lg-8'] );
        $this->form->enableCSRFProtection(); // ATIVA PROTEÇÃO CONTA EXECUÇÃO DE JAVA SCRIPT MALICIOSO
        

        // create the form fields
        $id = new TEntry('id');
        $description = new TEntry('description');
        $sale_price = new TNumeric('sale_price', 2, ',', '.', true);
        $custo = new TNumeric('custo', 2, ',', '.', true);
        $tipo_servico_id = new TDBUniqueSearch('tipo_servico_id', 'DBUNIDADE', 'TipoProduto', 'id', 'nome');
        $ativo = new TRadioGroup('ativo');


        // add the fields
        $this->form->addFields( [ new TLabel('Id') ], [ $id ] );
        $this->form->addFields( [ new TLabel('Nome') ], [ $description ] );
        $this->form->addFields( [ new TLabel('Valor') ], [ $sale_price ] );
        $this->form->addFields( [ new TLabel('Custo') ], [ $custo ] );
        $this->form->addFields( [ new TLabel('Tipo Produto') ], [ $tipo_servico_id ] );
        $this->form->addFields( [ new TLabel('Ativo') ], [ $ativo ] );

        $description->addValidation('Nome', new TRequiredValidator);
        $sale_price->addValidation('Valor', new TRequiredValidator);
        $sale_price->addValidation('Custo', new TRequiredValidator);
        $tipo_servico_id->addValidation('Tipo de serviço', new TRequiredValidator);
        $ativo->addValidation('Ativo', new TRequiredValidator);


        // set sizes
        $id->setSize('100%');
        $description->setSize('100%');
        $sale_price->setSize('100%');
        $custo->setSize('100%');
        $tipo_servico_id->setSize('100%');
        $ativo->setSize('100%');
        $ativo->addItems( ['Y' => 'Sim', 'N' => 'Não'] );
        $ativo->setLayout('horizontal');
        $tipo_servico_id->setMinLength(0);
        $ativo->setValue('Y');
        
        $id->setEditable(FALSE);

        $description->forceUpperCase();
        
        // create the form actions
        $btn = $this->form->addAction(_t('Save'), new TAction([$this, 'onSave']), 'fa:save');
        $btn->class = 'btn btn-sm btn-primary';
        $this->form->addActionLink(_t('New'),  new TAction([$this, 'onEdit']), 'fa:eraser red');
        $this->form->addHeaderActionLink( _t('Close'), new TAction([$this, 'onClose']), 'fa:times red');
        
        // vertical box container
        $container = new TVBox;
        $container->style = 'width: 100%';
        // $container->add(new TXMLBreadCrumb('menu.xml', __CLASS__));
        $container->add($this->form);
        
        parent::add($container);
    }
    /**
     * Close side panel
     */
    public static function onClose($param)
    {
        TScript::create("Template.closeRightPanel()");
    }
}
