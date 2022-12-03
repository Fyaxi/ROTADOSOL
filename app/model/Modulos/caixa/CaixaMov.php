<?php
/**
 * Caixa Active Record
 * @author  <your-name-here>
 */
class CaixaMov extends TRecord
{
    const TABLENAME = 'caixa_mov';
    const PRIMARYKEY= 'id';
    const IDPOLICY =  'serial'; // {max, serial}
    
    
    /**
     * Constructor method
     */
    public function __construct($id = NULL, $callObjectLoad = TRUE)
    {
        parent::__construct($id, $callObjectLoad);
        parent::addAttribute('caixa_id');
        parent::addAttribute('venda_id');
        parent::addAttribute('mov_valor');
        parent::addAttribute('mov_desconto');
        parent::addAttribute('mov_data');
        parent::addAttribute('mov_baixa');
        parent::addAttribute('mov_total');
        parent::addAttribute('mov_tipo');
        parent::addAttribute('mov_ativo');
    }

    public static function ZerarCaixa($_CaixaID, $_DataF)
    {
        //echo 'Caixa ID: '.$_CaixaID.'<br>';
        //echo 'Data Abertura: '.$_DataA.'<br>';
        //echo 'Data Fechamento: '.$_DataF.'<br>';
        $conn = TTransaction::get();
        $resultCaixa = $conn->query(" UPDATE caixa_mov 
                                      SET mov_baixa = '$_DataF', mov_ativo = 'N'
                                      WHERE caixa_id = $_CaixaID");
    }

    public static function PegarRecebimentoValor($Recebimento)
    {
        try
        {
            TTransaction::open('DBUNIDADE');
            $conn = TTransaction::get();
            $resultCaixa = $conn->query(" SELECT SUM(valor) FROM conta_receber_pg 
                                            WHERE recebimento_id = '$Recebimento' ");
            TTransaction::close();
            if ($resultCaixa)
            {
                foreach ($resultCaixa as $row)
                {
                    $SomaTotalCaixa  = $row[0];
                }
            }
            else
            {
                new TMessage('info', '<span style="font-weight: bold;">ERRO AO BATER O CAIXA</span> <BR><BR>Aviso: Módulo CAIXA.<BR>');
            }

            if (empty($SomaTotalCaixa))
            {
                $SomaTotalCaixa = 0.00;
            }
            
            return $SomaTotalCaixa;
        }
        catch (Exception $e)
        {
            //new TMessage('error','<span style="font-weight: bold;">ERRO</span>');
            new TMessage('error', $e->getMessage());
            TTransaction::rollback();
        }
        
    }

    public static function PegarRecebimento($CaixaID)
    {
        try
        {
            TTransaction::open('DBUNIDADE');
            $conn = TTransaction::get();
            $resultCaixa = $conn->query(" SELECT id FROM conta_receber WHERE id_caixa = '$CaixaID' ");
            TTransaction::close();
            if (isset($resultCaixa))
            {
                foreach ($resultCaixa as $row)
                {
                    $SomaTotalCaixa  = $row[0];
                }
            }
            else
            {
                new TMessage('info', '<span style="font-weight: bold;">ERRO AO BATER O CAIXA</span> <BR><BR>Aviso: Módulo CAIXA.<BR>');
            }
            
            if (empty($SomaTotalCaixa))
            {
                $SomaTotalCaixa = 0.00;
            }

            //echo $SomaTotalCaixa;
            return $SomaTotalCaixa;
        }
        catch (Exception $e)
        {
            //new TMessage('error','<span style="font-weight: bold;">ERRO</span>');
            new TMessage('error', $e->getMessage());
            TTransaction::rollback();
        }
        
    }

    public static function BaterMov($CaixaID, $Data, $DataFechamento)
    {
        try
        {
            TTransaction::open('DBUNIDADE');
            $conn = TTransaction::get();
            $resultCaixa = $conn->query(" SELECT SUM(mov_valor) FROM CAIXA_MOV 
                                            WHERE mov_data BETWEEN '$Data' 
                                            AND '$DataFechamento' 
                                            AND mov_ativo = 'Y'
                                            AND caixa_id = $CaixaID ");
            TTransaction::close();
            
            if ($resultCaixa)
            {
                foreach ($resultCaixa as $row)
                {
                    $SomaTotalCaixa  = $row[0];
                }
            }
            else
            {
                new TMessage('info', '<span style="font-weight: bold;">ERRO AO BATER O CAIXA</span> <BR><BR>Aviso: Módulo CAIXA.<BR>');
            }
            
            return $SomaTotalCaixa;
        }
        catch (Exception $e)
        {
            //new TMessage('error','<span style="font-weight: bold;">ERRO</span>');
            new TMessage('error', $e->getMessage());
            TTransaction::rollback();
        }
        
    }

    public static function BaterValoresLançamentos($CaixaID, $Data, $DataFechamento)
    {
        try
        {
            TTransaction::open('DBUNIDADE');
            $conn = TTransaction::get();
            $resultCaixaDinheiro = $conn->query("   SELECT SUM(PROD_PRECO) FROM CAIXA_MOV
                                            WHERE PED_DATA BETWEEN '$Data' AND '$DataFechamento' 
                                            AND PROD_BAIXA = 'N' ");
            TTransaction::close();
            if ($resultCaixaDinheiro)
            {
                foreach ($resultCaixaDinheiro as $row)
                {
                    $SomaTotalCaixaDinheiro  = $row[0];
                }
            }
            else
            {
                new TMessage('info', '<span style="font-weight: bold;">ERRO AO BATER LANÇAMENTOS</span> <BR><BR>Aviso: Forma Recebimento Inválido.<BR>');
            }
        
            return $SomaTotalCaixaDinheiro;
        }
        catch (Exception $e)
        {
            //new TMessage('error','<span style="font-weight: bold;">ERRO</span>');
            new TMessage('error', $e->getMessage());
            TTransaction::rollback();
        }
        
    }
    
}
