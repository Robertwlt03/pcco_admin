<?php

namespace App\Enum;

enum Status: string
{
    case Todo = 'TODO';
    case InProgress = 'IN_PROGRESS';
    case Review = 'REVIEW';
    case Done = 'DONE';
}
