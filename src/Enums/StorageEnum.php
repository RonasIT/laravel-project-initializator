<?php

namespace RonasIT\ProjectInitializator\Enums;

use RonasIT\Support\Traits\EnumTrait;

enum StorageEnum: string
{
    use EnumTrait;

    case Gcs = 'gcs';
    case S3 = 's3';
    case Local = 'local';
}
