<?php

namespace Milo\XmlRpc;

use DOMDocument;


/**
 * Object represents <methodCall> or <methodResponse>.
 *
 * @author Miloslav Hůla (https://github.com/milo)
 */
interface IMethod
{
	/**
	 * @return void
	 */
	function toXml(DOMDocument $doc, Coder $coder);

}
