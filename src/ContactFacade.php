<?php

namespace Virtualorz\Contact;

use Illuminate\Support\Facades\Facade;

/**
 * @see Virtualorz\Contact\Contact
 */
class ContactFacade extends Facade {

    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor() {
        return 'contact';
    }

}
