<?php

declare(strict_types=1);

namespace Milo\XmlRpc;

use DOMDocument;


/**
 * Implementor represents <methodCall> or <methodResponse>.
 */
interface IMethod
{
	function toXml(DOMDocument $doc, Coder $coder): void;
}
