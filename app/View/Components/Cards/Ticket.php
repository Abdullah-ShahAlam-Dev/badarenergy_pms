<?php

namespace App\View\Components\Cards;

use Illuminate\View\Component;

class Ticket extends Component
{

    public $message;
    public $user;
    public $ccUserIds;

    /**
     * Create a new component instance.
     *
     * @return void
     */
    public function __construct($message, $user, $ccUserIds = [])
    {
        $this->message = $message;
        $this->user = $user;
        $this->ccUserIds = $ccUserIds;
    }

    /**
     * Get the view / contents that represent the component.
     *
     * @return \Illuminate\Contracts\View\View|\Closure|string
     */
    public function render()
    {
        return view('components.cards.ticket');
    }
    
}
