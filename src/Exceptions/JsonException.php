<?php

namespace SultanovSolutions\LaravelBase\Exceptions;

use Exception;

class JsonException extends Exception
{
	public function render()
	{
		return response()->json([
			'status' => false,
			'message' => $this->getMessage(),
		], $this->getCode());
	}
}
