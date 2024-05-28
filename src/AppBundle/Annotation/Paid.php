<?php
/**
 * Created by PhpStorm.
 * User: aram
 * Date: 6/9/16
 * Time: 1:17 PM
 */

namespace AppBundle\Annotation;

use LB\PaymentBundle\Entity\Subscriber;

/**
 * @Annotation
 * @Target("METHOD")
 */
class Paid
{
    /**
     * @var
     */
    public $plan;

    /**
     * @param array $data
     */
    public function __construct(array $data)
    {
        // check data
        if(count($data) == 0){
            throw new \InvalidArgumentException(sprintf('Property "plan" does not exist'));
        }

        // check type of plan
        if(!array_key_exists($data['plan'], Subscriber::$PLAN)){
            throw new \InvalidArgumentException(sprintf('Invalid type of plan'));
        }

        $this->plan = $data['plan'];
    }
}