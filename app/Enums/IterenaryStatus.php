<?php

namespace App\Enums;

enum IterenaryStatus:String
{
    case PENDING = 'pending';
    case VISITING = 'visiting';
    case VISITED = 'visited';
    case CANCELED = 'canceled';
}
