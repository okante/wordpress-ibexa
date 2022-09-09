<?php

declare(strict_types=1);

namespace Almaviacx\Bundle\Ibexa\WordPress\Exceptions;
use Exception as BaseException;
use Throwable;

class Exception extends BaseException
{
    protected array $options = [];
    public function __construct(string $url = "default-url", array $options = [], Throwable $previous = null, int $code = 0)
    {
        $message = sprintf('Unable to load %s', $url);
        parent::__construct($message, $code, $previous);
        $this->options = $options;
    }

    /**
     * @return array
     */
    public function getOptions(): array
    {
        return $this->options;
    }

}