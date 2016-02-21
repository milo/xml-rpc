<?php

namespace Milo\XmlRpc;


/**
 * Object can be converted to XML-RPC <value> tag.
 */
interface IValueConvertible
{

	/**
	 * @return mixed
	 */
	function getXmlRpcValue();

}
