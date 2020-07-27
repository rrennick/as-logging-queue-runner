<?php

/**
 * Class AS_Logging_QueueRunner
 */
class AS_Logging_QueueRunner extends ActionScheduler_QueueRunner {

    /**
     * @var WC_Logger WooCommerce Logger instance
     */
    private $wc_logger;

    /**
     * @var string Log context
     */
    const HANDLE = 'action-scheduler-runner';

	/**
	 * @codeCoverageIgnore
	 */
	public function init() {
        if ( is_null( $this->wc_logger ) ) {
            $this->wc_logger = class_exists( 'WC_Logger' ) ? new WC_Logger() : false;
        }

        parent::init();
	}

	/**
	 * Process a batch of actions pending in the queue.
	 *
	 * This logs the list of claimed actions on each queue run
	 *
	 * @param int $size The maximum number of actions to process in the batch.
	 * @param string $context Optional identifer for the context in which this action is being processed, e.g. 'WP CLI' or 'WP Cron'
	 *        Generally, this should be capitalised and not localised as it's a proper noun.
	 * @return int The number of actions processed.
	 */
	protected function do_batch( $size = 100, $context = '' ) {
	    $this->log( sprintf( '%s staking claim of %d actions', $context, $size ) );
		$claim = $this->store->stake_claim($size);
		$this->monitor->attach($claim);
		$processed_actions = 0;

		// Get the info on the actions before processing any.
		$action_ids = $claim->get_actions();
		$messages = array();
        foreach ( $action_ids as $action_id ) {
            $action = $this->store->fetch_action( $action_id );
            if ( ! is_a( $action, 'ActionScheduler_Action' ) ) {
                continue;
            }
            $schedule = $action->get_schedule();
            $messages[] = sprintf(
                'Action %d is %s scheduled for %s',
                $action_id,
                $this->store->get_status( $action_id ),
                is_a( $schedule, 'ActionScheduler_NullSchedule' ) ? 'async' : $schedule->get_date()->format( 'Y-m-d H:i:s O' )
            );
        }
        $this->log( implode( "\n", $messages ) );

		foreach ( $action_ids as $action_id ) {
		    $this->log( 'Starting ' . $action_id );
			// bail if we lost the claim
			if ( ! in_array( $action_id, $this->store->find_actions_by_claim_id( $claim->get_id() ) ) ) {
				break;
			}
			$this->process_action( $action_id, $context );
			$processed_actions++;

			if ( $this->batch_limits_exceeded( $processed_actions ) ) {
				break;
			}
		}
        $this->log( sprintf( '%s releasing claim of %d actions', $context, $size ) );
		$this->store->release_claim($claim);
		$this->monitor->detach();
		$this->clear_caches();
		return $processed_actions;
	}

    private function log( $message ) {
	    if ( $this->wc_logger ) {
	        $this->wc_logger->add( self::HANDLE, $message );
        } else {
	        error_log( sprintf( '%s: %s', self::HANDLE, $message ) );
        }
    }
}
