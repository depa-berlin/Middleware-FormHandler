<?php

declare(strict_types=1);

namespace depa\FormularHandlerMiddleware;

/**
 * Interface AdapterInterface.
 */
interface AdapterInterface
{
    /**
     * Verarbeitet die Daten basierend auf dem benutzten Adapter.
     *
     * @return mixed
     */
    public function handleData();
}
