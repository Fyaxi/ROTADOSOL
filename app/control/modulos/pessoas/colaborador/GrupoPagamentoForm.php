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
class GrupoPagamentoForm extends TPage
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
        $this->setAfterSaveAction( new TAction(['GrupoPagamentoList', 'onReload'], ['register_state' => 'true']) );
        
        $this->setDatabase('DBUNIDADE');              // defines the database
        $this->setActiveRecord('GrupoPagamento');     // defines the active record
        
        // creates the form
        $this->form = new BootstrapFormBuilder('form_GrupoPg');
        $this->form->setFormTitle('Grupo de Pagamento');
        $this->form->setClientValidation(true);
        $this->form->setColumnClasses( 2, ['col-sm-5 col-lg-4', 'col-sm-7 col-lg-8'] );
        $this->form->enableCSRFProtection(); // ATIVA PROTEÇÃO CONTA EXECUÇÃO DE JAVA SCRIPT MALICIOSO

        // create the form fields
        $id = new TEntry('id');
        $nome = new TEntry('nome');
        $ValorPagamento = new TNumeric('ValorPagamento', 2, ',', '.');
        $ContaPagamento = new TDBUniqueSearch('ContaPagamento ', 'DBUNIDADE', 'ContaFinanceira', 'id', 'nome');


        // add the fields
        $this->form->addFields( [ new TLabel('N°') ], [ $id ] );
        $this->form->addFields( [ new TLabel('Nome') ], [ $nome ] );
        $this->form->addFields( [ new TLabel('Valor Pagamento') ], [ $ValorPagamento ]  );
        $this->form->addFields( [ new TLabel('Conta Pagamento') ], [ $ContaPagamento ]  );

        $nome->addValidation('Nome', new TRequiredValidator);
        $ValorPagamento->addValidation('ValorPagamento', new TRequiredValidator);
        $ContaPagamento->addValidation('ValorPagamento', new TRequiredValidator);


        // set sizes
        $id->setSize('100%');
        $nome->setSize('100%');
        $ValorPagamento->setSize('100%');
        $ContaPagamento->setSize('100%');

        $nome->forceUpperCase();
        $ContaPagamento->setMinLength(0);

        $id->setEditable(FALSE);
        
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
