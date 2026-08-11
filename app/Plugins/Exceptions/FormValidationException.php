<?php
declare(strict_types=1);

namespace App\Plugins\Exceptions;

/**
 * Throw from form.before_validate to stop a core form submission.
 *
 * The listener should store a visitor-safe flash message before throwing.
 * Other plugin exceptions remain isolated and do not interrupt the request.
 */
final class FormValidationException extends \RuntimeException {}
