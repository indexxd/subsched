<?php

namespace App\Annotation;

/**
 * @Annotation
 * @Target({"METHOD"})
 */

class JsonParams
{

	/** @var string */
	public $name;

	/** @var bool */
    public $required = true;

	/** @Enum({"int", "date", "bool", "array"}) */
	public $type;
	
	/** @var string */
    public $entity;

} 
?>