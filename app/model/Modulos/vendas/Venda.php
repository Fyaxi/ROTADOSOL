<?php
/**
 * Venda Active Record
 * @author  <your-name-here>
 */
class Venda extends TRecord
{
    const TABLENAME = 'venda';
    const PRIMARYKEY= 'id';
    const IDPOLICY =  'serial'; // {max, serial}
    
    private $customer;
    private $sale_items;

    /**
     * Constructor method
     */
    public function __construct($id = NULL, $callObjectLoad = TRUE)
    {
        parent::__construct($id, $callObjectLoad);
        parent::addAttribute('mes');
        parent::addAttribute('ano');
        parent::addAttribute('total');
        parent::addAttribute('financeiro_gerado');
        parent::addAttribute('ativo');
        parent::addAttribute('caixa');
        parent::addAttribute('origem');

        parent::addAttribute('date');
        parent::addAttribute('obs');
        parent::addAttribute('customer_id');
    }

    /**
     * Method set_customer
     * Sample of usage: $sale->customer = $object;
     * @param $object Instance of Customer
     */
    public function set_customer(Pessoa $object)
    {
        $this->customer = $object;
        $this->customer_id = $object->id;
    }
    
    /**
     * Method get_customer
     * Sample of usage: $sale->customer->attribute;
     * @returns Customer instance
     */
    public function get_customer()
    {
        // loads the associated object
        if (empty($this->customer))
            $this->customer = new Pessoa($this->customer_id);
    
        // returns the associated object
        return $this->customer;
    }
    
    /**
     * Method get_customer_name
     */
    public function get_customer_name()
    {
        return $this->get_customer()->name;
    }

    public function getSaleItems()
    {
        return SaleItem::where('sale_id', '=', $this->id)->load();
    }
    
    /**
     * Delete the object and its aggregates
     * @param $id object ID
     */
    public function delete($id = NULL)
    {
        // delete the related SaleItem objects
        $id = isset($id) ? $id : $this->id;
        SaleItem::where('sale_id', '=', $id)->delete();
        
        // delete the object itself
        parent::delete($id);
    }

    public static function getCustomerSales($customer_id)
    {
        $repository = new TRepository('Sale');
        return $repository->where('customer_id', '=', $customer_id)->load();
    }
    
    public function get_cliente()
    {
        return Pessoa::find($this->cliente_id);
    }
}
