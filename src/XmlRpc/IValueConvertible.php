<?php

namespace Milo\XmlRpc;


/**
 * Object can be converted to XML-RPC <value> tag.
 *
 * @author Miloslav Hůla (https://github.com/milo)
 */
interface IValueConvertible
{
	/**
	 * @return mixed
	 */
	function getXmlRpcValue();

}
