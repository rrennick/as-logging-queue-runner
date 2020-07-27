<?php
/*
 * Plugin Name: Action Scheduler Queue Runner Logger
 * Plugin URI: https://actionscheduler.org
 * Description: This plugin writes a log of queue runner activity for troubleshooting purposes.
 * Author: Automattic
 * Author URI: https://automattic.com/
 * Version: 0.1
 * License: GPLv3
 *
 * Copyright 2019 Automattic, Inc.  (https://automattic.com/contact/)
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <https://www.gnu.org/licenses/>.
 *
 */

add_filter( 'action_scheduler_queue_runner_class', function() {
    require_once( __DIR__ . '/AS_Logging_QueueRunner.php' );
    return 'AS_Logging_QueueRunner';
} );
