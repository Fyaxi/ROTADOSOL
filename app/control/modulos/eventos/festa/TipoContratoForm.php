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
class TipoContratoForm extends TPage
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
        $this->setAfterSaveAction( new TAction(['TipoContratoList', 'onReload'], ['register_state' => 'true']) );
        
        $this->setDatabase(TSession::getValue('unit_database'));              // defines the database
        $this->setActiveRecord('TipoContrato');     // defines the active record
        
        // creates the form
        $this->form = new BootstrapFormBuilder('form_TipoContrato');
        $this->form->setFormTitle('Cadstro Tipo Festa');
        $this->form->setClientValidation(true);
        $this->form->setColumnClasses( 2, ['col-sm-5 col-lg-4', 'col-sm-7 col-lg-8'] );
        $this->form->enableCSRFProtection(); // ATIVA PROTEÇÃO CONTA EXECUÇÃO DE JAVA SCRIPT MALICIOSO

        // create the form fields
        $id   = new TEntry('id');
        $nome = new TEntry('nome');
        $cor  = new TColor('cor');
        $TextoPersonalizado = new TDBUniqueSearch('TextoPersonalizado', TSession::getValue('unit_database'), 'TextoPersonalizado', 'id', 'nome');
        $ValorContrato = new TNumeric('ValorContrato', 2, ',', '.', true);
        $QtdConvidados = new TEntry('QtdConvidados');

        // add the fields
        $this->form->addFields( [ new TLabel('N°') ], [ $id ] );
        $this->form->addFields( [ new TLabel('Nome Contrato') ], [ $nome ] );
        $this->form->addFields( [ new TLabel('Cor Calendário') ], [ $cor ] );
        $this->form->addFields( [ new TLabel('Texto Personalizado') ], [ $TextoPersonalizado ] );
        $this->form->addFields( [ new TLabel('Festa Valor') ], [ $ValorContrato ] );
        $this->form->addFields( [ new TLabel('Convidados Fixos') ], [ $QtdConvidados ] );

        $nome->addValidation('Nome', new TRequiredValidator);
        $cor->addValidation('Cor', new TRequiredValidator);
        //$TextoPersonalizado->addValidation('Texto Personalizado', new TRequiredValidator);
        $ValorContrato->addValidation('Valor Festa', new TRequiredValidator);
        $QtdConvidados->addValidation('Qtd Convidados', new TRequiredValidator); 

        // set sizes
        $id->setSize('100%');
        $nome->setSize('100%');
        $cor->setSize('100%');
        $TextoPersonalizado->setSize('100%');
        $ValorContrato->setSize('100%');
        $QtdConvidados->setSize('100%');
        
        $QtdConvidados->setMask('99999');

        $TextoPersonalizado->setMinLength(0);

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
