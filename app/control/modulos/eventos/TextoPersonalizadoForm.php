<?php
/**
 * TextoPersonalizado TSession::getValue('unit_database')
 *
 * @version    2.0
 * @package    Sistema Integrado
 * @subpackage Módulos
 * @author     Jairo Barreto
 * @copyright  Copyright (c) 2021 Nativus Tecnologia. (http://www.nativustecnologia.com.br)
 * 
 */
class TextoPersonalizadoForm extends TWindow
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
        parent::setSize(0.8, null);
        parent::removePadding();
        parent::removeTitleBar();
        //parent::disableEscape();
        
        //parent::setTargetContainer('adianti_right_panel');
        $this->setAfterSaveAction( new TAction(['TextoPersonalizadoList', 'onReload'], ['register_state' => 'true']) );
        
        $this->setDatabase(TSession::getValue('unit_database'));              // defines the database
        $this->setActiveRecord('TextoPersonalizado');     // defines the active record
        
        // creates the form
        $this->form = new BootstrapFormBuilder('form_TextoPersonalizado');
        $this->form->setFormTitle('Texto Personalizado');
        $this->form->setClientValidation(true);
        //$this->form->setColumnClasses( 2, ['col-sm-5 col-lg-4', 'col-sm-7 col-lg-8'] );
        $this->form->enableCSRFProtection(); // ATIVA PROTEÇÃO CONTA EXECUÇÃO DE JAVA SCRIPT MALICIOSO

        // create the form fields
        $id = new TEntry('id');
        $nome = new TDBUniqueSearch('nome', TSession::getValue('unit_database'), 'TipoDecoracao', 'id', 'nome');
        $Texto = new THtmlEditor('Texto');


        // add the fields
        $this->form->addFields( [ new TLabel('N°') ], [ $id ] );
        $this->form->addFields( [ new TLabel('Nome') ], [ $nome ] );

        $label_description = new TLabel('Texto Personalizado');
        $label_description->setFontStyle('b');
        $label_description->style.=';float:left';
        
        $this->form->addContent( [$label_description] );
        $this->form->addFields( [$Texto] );

        $nome->addValidation('Nome', new TRequiredValidator);
        $Texto->addValidation('Texto', new TRequiredValidator);


        // set sizes
        $id->setSize('100%');
        $nome->setSize('100%');
        $Texto->setSize('100%', 400);
        $nome->setMinLength(0);

        $id->setEditable(FALSE);
        
        // create the form actions
        $btn = $this->form->addAction(_t('Save'), new TAction([$this, 'onSave']), 'fa:save');
        $btn->class = 'btn btn-sm btn-primary';
        $this->form->addActionLink(_t('New'),  new TAction([$this, 'onEdit']), 'fa:eraser red');
        
        //$this->form->addHeaderActionLink( _t('Close'),  new TAction([__CLASS__, 'onClose'], ['static'=>'1']), 'fa:times red');
        
        // vertical box container
        $container = new TVBox;
        $container->style = 'width: 100%';
        // $container->add(new TXMLBreadCrumb('menu.xml', __CLASS__));
        $container->add($this->form);
        
        parent::add($container);
    }
}
