<?php

namespace RonasIT\ProjectInitializator\DTO;

readonly class DBConnectionDTO
{
    public function __construct(
        public string $driver = 'pgsql',
        public string $host = 'pgsql',
        public string $port = '5432',
        public string $database = 'postgres',
        public string $username = 'postgres',
        public string $password = '',
    ) {
    }

    public function toArray(): array
    {
        return [
            'driver' => $this->driver,
            'host' => $this->host,
            'port' => $this->port,
            'database' => $this->database,
            'username' => $this->username,
            'password' => $this->password,
        ];
    }
}
