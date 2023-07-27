<?php

namespace Kiri\Coroutine\Server\Abstracts;

enum StatusEnum
{
	case START;
	case STOP;
	case EXIT;
	case ERROR;

}
