<?php

namespace Pronamic\WordPress\Pay\Gateways\Nocks;

use Pronamic\WordPress\Pay\Core\Statuses as Core_Statuses;

/**
 * Title: Nocks statuses
 * Description:
 * Copyright: Copyright (c) 2005 - 2018
 * Company: Pronamic
 *
 * @author Reüel van der Steege
 * @version 1.0.0
 * @since 1.0.0
 */
class Statuses {
	/**
	 * Cancelled
	 *
	 * @var string
	 */
	const CANCELLED = 'cancelled';

	/**
	 * Completed
	 *
	 * @var string
	 */
	const COMPLETED = 'completed';

	/**
	 * Failed
	 *
	 * @var string
	 */
	const FAILED = 'failed';

	/**
	 * Pending
	 *
	 * @var string
	 */
	const PENDING = 'pending';

	/**
	 * Processing
	 *
	 * @var string
	 */
	const PROCESSING = 'processing';

	/////////////////////////////////////////////////

	/**
	 * Transform a Nocks status to Pronamic Pay status.
	 *
	 * @param string $status
	 *
	 * @return string|null
	 */
	public static function transform( $status ) {
		switch ( $status ) {
			case self::CANCELLED:
				return Core_Statuses::CANCELLED;

			case self::COMPLETED:
				return Core_Statuses::SUCCESS;

			case self::PENDING:
				return Core_Statuses::OPEN;

			default:
				return null;
		}
	}
}
