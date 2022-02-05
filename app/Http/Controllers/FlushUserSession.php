<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;

/**
 * Destroy user session to realod quiz
 */
class FlushUserSession extends Controller
{
    /**
     * @return void
     */
    public function execute(): void
    {
        session()->invalidate();
    }
}
