<?php
/**
 * FormNestedBuilderView
 *
 * @version    1.0
 * @package    samples
 * @subpackage tutor
 * @author     Pablo Dall'Oglio
 * @copyright  Copyright (c) 2006 Adianti Solutions Ltd. (http://www.adianti.com.br)
 * @license    http://www.adianti.com.br/framework-license
 */
class PagTeste extends TPage
{
    private $form;
    
    /**
     * Class constructor
     * Creates the page
     */
    public function __construct()
    {
        parent::__construct();
        
        $this->form = new BootstrapFormBuilder('form_Colaborador');
        $this->form->setFormTitle('Colaborador');
        $this->form->setFieldSizes('100%');
        $this->form->setClientValidation(true);
        
        
        $this->form->appendPage('Dados Pessoais');
        
        // create the form fields
        $id = new TEntry('id');
        $nome = new TEntry('nome');
        $nome_fantasia = new TEntry('nome_fantasia');
        $tipo = new TCombo('tipo');
        $codigo_nacional = new TEntry('codigo_nacional');
        $codigo_estadual = new TEntry('codigo_estadual');
        $codigo_municipal = new TEntry('codigo_municipal');
        $fone = new TEntry('fone');
        $email = new TEntry('email');
        $observacao = new TText('observacao');
        $cep = new TEntry('cep');
        $logradouro = new TEntry('logradouro');
        $numero = new TEntry('numero');
        $complemento = new TEntry('complemento');
        $bairro = new TEntry('bairro');
        
        $filter = new TCriteria;
        $filter->add(new TFilter('id', '<', '0'));
        $cidade_id = new TDBCombo('cidade_id', TSession::getValue('unit_database'), 'Cidade', 'id', 'nome', 'nome', $filter);
        $grupo_id = new TDBRadioGroup('grupo_id', TSession::getValue('unit_database'), 'Grupo', 'id', 'nome');
        $papeis_id = new TDBMultiSearch('papeis_id', TSession::getValue('unit_database'), 'Papel', 'id', 'nome');
        $estado_id = new TDBCombo('estado_id', TSession::getValue('unit_database'), 'Estado', 'id', '{nome} ({uf})');
        
        $estado_id->setChangeAction( new TAction( [$this, 'onChangeEstado'] ) );
        $cep->setExitAction( new TAction([ $this, 'onExitCEP']) );
        $codigo_nacional->setExitAction( new TAction( [$this, 'onExitCNPJ'] ) );
        
        //$grupo_id->setUseButton();
        $grupo_id->setLayout('vertical TDBCheckGroup');

        $cidade_id->enableSearch();
        $estado_id->enableSearch();
        //$grupo_id->setMinLength(0);
        $papeis_id->setMinLength(0);
        $papeis_id->setSize('100%', 60);
        $observacao->setSize('100%', 60);
        $tipo->addItems( ['F' => 'Física', 'J' => 'Jurídica' ] );
        
        $row = $this->form->addFields( [ new TLabel('<br>Id'),     $id ],
                                       [ new TLabel('<br>Nome'),     $nome_fantasia ],
                                       [ new TLabel('<br>Apelido'),   $nome ] );
        $row->layout = ['col-sm-2', 'col-sm-7', 'col-sm-3' ];
        
        $row = $this->form->addFields( [ new TLabel('<br>RG'),  $codigo_municipal ],
                                       [ new TLabel('<br>CPF'),     $codigo_nacional ],
                                       [ new TLabel('<br>Tipo'),    $tipo ],
                                       [ new TLabel('<br>Fone'),   $fone ],
                                       [ new TLabel('<br>Email'),   $email ]);
        $row->layout = ['col-sm-2', 'col-sm-3', 'col-sm-3', 'col-sm-2', 'col-sm-2' ];

        
        $label2 = new TLabel('<br>Observação', '', 12, '');
        $label2->style='text-align:left;border-bottom:1px solid #c0c0c0;width:100%';
        $this->form->addContent( [$label2] );
        
        $row = $this->form->addFields( [ $observacao ] );
        $row->layout = ['col-sm-12' ];
        
        $subform = new BootstrapFormBuilder;
        $subform->setFieldSizes('100%');
        $subform->setProperty('style', 'border:none');
        
        $subform->appendPage( 'Endereço' );
        $row = $subform->addFields( [ new TLabel('<br>Cep'),      $cep ],
                                       [ new TLabel('<br>Logradouro'),       $logradouro ],
                                       [ new TLabel('<br>Numero'), $numero ] );
        $row->layout = ['col-sm-2', 'col-sm-8', 'col-sm-2'];
        
        $row = $subform->addFields( [ new TLabel('<br>Complemento'),  $complemento ],
                                       [ new TLabel('<br>Bairro'),  $bairro ] );
        $row->layout = ['col-sm-6', 'col-sm-6'];

        $row = $subform->addFields( [ new TLabel('<br>Estado'),  $estado_id ],
                                       [ new TLabel('<br>cCidade'),  $cidade_id ] );
        $row->layout = ['col-sm-6', 'col-sm-6'];
        
        $subform->appendPage( 'Financeiro' );
        $row = $subform->addFields( [ new TLabel('Chave Pix'),  $codigo_estadual ] );
        $row->layout = ['col-sm-12'];
        
        $subform->appendPage( 'Vínculos' );
        $row = $subform->addFields( [ new TLabel('Papéis'),  $papeis_id ],
                                       [ new TLabel('Grupo'),  $grupo_id ] );
        $row->layout = ['col-sm-6', 'col-sm-6'];
        
        $this->form->addContent( [$subform] );
        
        // set sizes
        $id->setSize('100%');
        $nome->setSize('100%');
        $nome_fantasia->setSize('100%');
        $tipo->setSize('100%');
        $codigo_nacional->setSize('100%');
        $codigo_estadual->setSize('100%');
        $codigo_municipal->setSize('100%');
        $fone->setSize('100%');
        $email->setSize('100%');
        $observacao->setSize('100%');
        $cep->setSize('100%');
        $logradouro->setSize('100%');
        $numero->setSize('100%');
        $complemento->setSize('100%');
        $bairro->setSize('100%');
        $cidade_id->setSize('100%');
        $grupo_id->setSize('100%');

        $cep->setMask('99.999-999');
        $fone->setMask('(99) 9 9999-9999');
        $numero->setMask('999999');

        $nome->forceUpperCase();
        $nome_fantasia->forceUpperCase();
        $logradouro->forceUpperCase();
        $bairro->forceUpperCase();
        $complemento->forceUpperCase();

        $id->setEditable(FALSE);
        $tipo->setEditable(FALSE);
        $nome->addValidation('Nome', new TRequiredValidator);
        $nome_fantasia->addValidation('Nome Fantasia', new TRequiredValidator);
        $tipo->addValidation('Tipo', new TRequiredValidator);
        $codigo_nacional->addValidation('CPF/CNPJ', new TRequiredValidator);
        $grupo_id->addValidation('Grupo', new TRequiredValidator);
        $fone->addValidation('Fone', new TRequiredValidator);
        $email->addValidation('Email', new TRequiredValidator);
        $email->addValidation('Email', new TEmailValidator);
        $cidade_id->addValidation('Cidade', new TRequiredValidator);
        $cep->addValidation('CEP', new TRequiredValidator);
        $logradouro->addValidation('Logradouro', new TRequiredValidator);
        $numero->addValidation('Número', new TRequiredValidator);
        
        // create the form actions
        //$this->form->addHeaderActionLink( _t('Close'),  new TAction([__CLASS__, 'onClose'], ['static'=>'1']), 'fa:times red');
        $btn = $this->form->addAction(_t('Save'), new TAction([$this, 'onSave']), 'fa:save');
        $btn->class = 'btn btn-sm btn-primary';
        $this->form->addActionLink(_t('New'),  new TAction([$this, 'onEdit']), 'fa:eraser red');
        
        // wrap the page content using vertical box
        $vbox = new TVBox;
        $vbox->style = 'width: 100%';
        $vbox->add(new TXMLBreadCrumb('menu.xml', __CLASS__));
        $vbox->add($this->form);
        parent::add($vbox);
    }
    
    /**
     * Save form data
     * @param $param Request
     */
    public function onSave( $param )
    {
        try
        {
            TTransaction::open(TSession::getValue('unit_database')); // open a transaction
            
            $this->form->validate(); // validate form data
            $data = $this->form->getData(); // get form data as array
            
            $object = new Colaborador;  // create an empty object
            $object->fromArray( (array) $data); // load the object with data
            $object->store(); // save the object
            
            PessoaPapel::where('pessoa_id', '=', $object->id)->delete();
            
            if ($data->papeis_id)
            {
                foreach ($data->papeis_id as $papel_id)
                {
                    $pp = new PessoaPapel;
                    $pp->pessoa_id = $object->id;
                    $pp->papel_id  = $papel_id;
                    $pp->store();
                }
            }
            
            // get the generated id
            $data->id = $object->id;
            
            $this->form->setData($data); // fill form data
            TTransaction::close(); // close the transaction
            
            $pos_action = new TAction(['ColaboradorList', 'onReload']);
            new TMessage('info', AdiantiCoreTranslator::translate('Record saved'), $pos_action);
        }
        catch (Exception $e) // in case of exception
        {
            new TMessage('error', $e->getMessage()); // shows the exception error message
            $this->form->setData( $this->form->getData() ); // keep form data
            TTransaction::rollback(); // undo all pending operations
        }
    }
    
    /**
     * Clear form data
     * @param $param Request
     */
    public function onClear( $param )
    {
        $this->form->clear(TRUE);
    }
    
    /**
     * Load object to form data
     * @param $param Request
     */
    public function onEdit( $param )
    {
        try
        {
            if (isset($param['key']))
            {
                $key = $param['key'];
                TTransaction::open(TSession::getValue('unit_database'));
                $object = new Colaborador($key);
                
                $object->papeis_id = PessoaPapel::where('pessoa_id', '=', $object->id)->getIndexedArray('papel_id');
                
                $this->form->setData($object);
                
                // force fire events
                $data = new stdClass;
                $data->estado_id = $object->cidade->estado->id;
                $data->cidade_id = $object->cidade_id;
                TForm::sendData('form_Colaborador', $data);
                
                TTransaction::close();
            }
            else
            {
                $this->form->clear(TRUE);
            }
        }
        catch (Exception $e) // in case of exception
        {
            new TMessage('error', $e->getMessage()); // shows the exception error message
            TTransaction::rollback(); // undo all pending operations
        }
    }
    
    /**
     * Action to be executed when the user changes the state
     * @param $param Action parameters
     */
    public static function onChangeEstado($param)
    {
        try
        {
            TTransaction::open(TSession::getValue('unit_database'));
            if (!empty($param['estado_id']))
            {
                $criteria = TCriteria::create( ['estado_id' => $param['estado_id'] ] );
                
                // formname, field, database, model, key, value, ordercolumn = NULL, criteria = NULL, startEmpty = FALSE
                TDBCombo::reloadFromModel('form_Colaborador', 'cidade_id', TSession::getValue('unit_database'), 'Cidade', 'id', '{nome} ({id})', 'nome', $criteria, TRUE);
            }
            else
            {
                TCombo::clearField('form_Colaborador', 'cidade_id');
            }
            
            TTransaction::close();
        }
        catch (Exception $e)
        {
            new TMessage('error', $e->getMessage());
        }
    }
    
    /**
     * Autocompleta outros campos a partir do CNPJ
     */
    public static function onExitCNPJ($param)
    {
        session_write_close();
        
        try
        {
            $cnpj = preg_replace('/[^0-9]/', '', $param['codigo_nacional']);
            $url  = 'http://receitaws.com.br/v1/cnpj/'.$cnpj;
            
            $content = @file_get_contents($url);
            
            if ($content !== false)
            {
                $cnpj_data = json_decode($content);
                
                
                $data = new stdClass;
                if (is_object($cnpj_data) && $cnpj_data->status !== 'ERROR')
                {
                    $data->tipo = 'J';
                    $data->nome = $cnpj_data->nome;
                    $data->nome_fantasia = !empty($cnpj_data->fantasia) ? $cnpj_data->fantasia : $cnpj_data->nome;
                    
                    if (empty($param['cep']))
                    {
                        $data->cep = $cnpj_data->cep;
                        $data->numero = $cnpj_data->numero;
                    }
                    
                    if (empty($param['fone']))
                    {
                        $data->fone = $cnpj_data->telefone;
                    }
                    
                    if (empty($param['email']))
                    {
                        $data->email = $cnpj_data->email;
                    }
                    
                    TForm::sendData('form_Colaborador', $data, false, true);
                }
                else
                {
                    $data->nome = '';
                    $data->nome_fantasia = '';
                    $data->cep = '';
                    $data->numero = '';
                    $data->telefone = '';
                    $data->email = '';
                    TForm::sendData('form_Colaborador', $data, false, true);
                }
            }
        }
        catch (Exception $e)
        {
            new TMessage('error', $e->getMessage());
        }
    }
    
    /**
     * Autocompleta outros campos a partir do CEP
     */
    public static function onExitCEP($param)
    {
        session_write_close();
        
        try
        {
            $cep = preg_replace('/[^0-9]/', '', $param['cep']);
            $url = 'https://viacep.com.br/ws/'.$cep.'/json/unicode/';
            
            $content = @file_get_contents($url);
            
            if ($content !== false)
            {
                $cep_data = json_decode($content);
                
                $data = new stdClass;
                if (is_object($cep_data) && empty($cep_data->erro))
                {
                    TTransaction::open(TSession::getValue('unit_database'));
                    $estado = Estado::where('uf', '=', $cep_data->uf)->first();
                    $cidade = Cidade::where('codigo_ibge', '=', $cep_data->ibge)->first();
                    TTransaction::close();
                    
                    $data->logradouro  = $cep_data->logradouro;
                    $data->complemento = $cep_data->complemento;
                    $data->bairro      = $cep_data->bairro;
                    $data->estado_id   = $estado->id ?? '';
                    $data->cidade_id   = $cidade->id ?? '';
                    
                    TForm::sendData('form_Colaborador', $data, false, true);
                }
                else
                {
                    $data->logradouro  = '';
                    $data->complemento = '';
                    $data->bairro      = '';
                    $data->estado_id   = '';
                    $data->cidade_id   = '';
                    
                    TForm::sendData('form_Colaborador', $data, false, true);
                }
            }
        }
        catch (Exception $e)
        {
            new TMessage('error', $e->getMessage());
        }
    }

    /**
     * Closes window
     */
    public static function onClose()
    {
        parent::closeWindow();
    }
}
