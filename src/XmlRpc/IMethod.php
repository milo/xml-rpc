<?php

namespace Milo\XmlRpc;

use DOMDocument;


/**
 * Implementor represents <methodCall> or <methodResponse>.
 */
interface IMethod
{
	/**
	 * @param  DOMDocument $doc
	 * @param  Coder $coder
	 * @return void
	 */
	function toXml(DOMDocument $doc, Coder $coder);
}
