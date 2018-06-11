<?php

namespace BitWasp\Bitcoin\Network;

class VTC_NetworkFactory extends CW_NetworkFactory
{

    /**
     * @return Networks\Vertcoin
     */
    public static function vertcoin()
    {
        return new Networks\Vertcoin();
    }

}
