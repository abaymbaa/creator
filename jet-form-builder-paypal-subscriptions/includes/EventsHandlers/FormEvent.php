<?php


namespace Jet_FB_Paypal\EventsHandlers;


interface FormEvent {

	public function get_event_class(): string;

}