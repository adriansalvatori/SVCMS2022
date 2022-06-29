<?php

namespace Objectiv\Plugins\Checkout\Interfaces;

interface SettingsGetterInterface {
	public function get_setting( string $setting, array $keys = array() );
}
