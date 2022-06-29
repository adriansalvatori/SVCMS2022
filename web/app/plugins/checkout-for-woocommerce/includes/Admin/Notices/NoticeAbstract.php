<?php

namespace Objectiv\Plugins\Checkout\Admin\Notices;

abstract class NoticeAbstract {
	public function __construct() {}

	public function init() {
		add_action( 'admin_notices', array( $this, 'maybe_show' ) );
	}

	abstract public function maybe_show();
}
