<?php

namespace App\Responses;

abstract class JsonResponse
{
    /**
     * Retrieves response's data.
     *
     * @return mixed
     */
    abstract protected function getBody();

    /**
     * Declares response's type.
     *
     * @return string
     */
    abstract protected function getType(): string;

    /**
     * Retrieves JSON response.
     *
     * @return string
     * @throws \JsonException
     */
    public function render(): string
    {
        return json_encode([
            "type" => $this->getType(),
            "body" => $this->getBody()
        ], JSON_THROW_ON_ERROR);
    }
}
