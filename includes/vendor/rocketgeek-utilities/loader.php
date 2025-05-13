<?php
/**
 * This is a library of utility functions for use in building WordPress plugins
 * (and other applications). These are used in various RocketGeek WordPress
 * plugins, but useage is not limited to those plugins. They are useful in any
 * WordPress application.
 *
 * This library is open source and Apache-2.0 licensed. I hope you find it 
 * useful for your project(s). Attribution is appreciated ;-)
 *
 * @package    RocketGeek_Utilities
 * @version    1.1.0
 *
 * @link       https://github.com/rocketgeek/rocketgeek-utilities/
 * @author     Chad Butler <https://butlerblog.com>
 * @author     RocketGeek <https://rocketgeek.com>
 * @copyright  Copyright (c) 2025 Chad Butler
 * @license    Apache-2.0
 *
 * Copyright [2025] Chad Butler, RocketGeek
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 * 
 *     https://www.apache.org/licenses/LICENSE-2.0
 * 
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 * 
 * 1.0.6 Adds rktgk_get_sanitized()
 */
require_once 'includes/utilities.php';
require_once 'includes/arrays.php';
require_once 'includes/dates.php';
require_once 'includes/forms.php';
require_once 'includes/strings.php';
require_once 'includes/db.php';
if ( class_exists( 'woocommerce' ) ) {
    require_once 'includes/woocommerce.php';
}
if ( true == WP_DEBUG ) {
    require_once 'includes/debug.php';
}