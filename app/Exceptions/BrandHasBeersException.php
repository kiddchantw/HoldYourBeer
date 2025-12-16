<?php

namespace App\Exceptions;

use Exception;

class BrandHasBeersException extends Exception
{
    protected $brand;
    protected $beersCount;

    public function __construct($brand, $beersCount)
    {
        $this->brand = $brand;
        $this->beersCount = $beersCount;
        parent::__construct(__('brands.messages.cannot_delete_has_beers', ['count' => $beersCount]));
    }

    public function render($request)
    {
        return back()->with('error', $this->getMessage());
    }
}
