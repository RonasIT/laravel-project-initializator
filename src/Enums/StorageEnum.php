<?php

namespace RonasIT\ProjectInitializator\Enums;

use RonasIT\Support\Traits\EnumTrait;

enum StorageEnum: string
{
    use EnumTrait;

    case Gcs = 'gcs';
    case Local = 'local';
    case S3 = 's3';
}
