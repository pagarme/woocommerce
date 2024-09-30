<?php
/*
 * PagarmeCoreApiLib
 */
namespace PagarmeCoreApiLib\Models;

use JsonSerializable;
/**
 *The settings for creating a google pay payment
 */
class CreateGooglePayPaymentRequest implements JsonSerializable
{
    /**
     * @required
     * @maps statement_descriptor
     * @var string $statementDescriptor public property
     */
    public $statementDescriptor;

    /**
     * @required
     * @var string $payload public property
     */
    public $payload;
    
    /**
     * @required
     * @var object $card public property
     */
    public $card;
    
    /**
     * Constructor to set initial or default values of member properties
     * @param string                       $statementDescriptor         Initialization value for $this->statementDescriptor
     * @param Object                       $payload                     Initialization value for $this->payload
     * @param Object                       $card                        Initialization value for $this->card
     */
    public function __construct()
    {
        if (3 == func_num_args()) {
            $this->statementDescriptor  = func_get_arg(0);
            $this->payload              = func_get_arg(1);
            $this->card                 = func_get_arg(2);
        }
    }

    /**
     * Encode this object to JSON
     */
    #[\ReturnTypeWillChange]
    public function jsonSerialize()
    {
        $json = array();
        $json['statement_descriptor']   = $this->statementDescriptor;
        $json['payload']                = $this->payload;
        $json['card']                   = $this->card;
        return $json;
    }
}
