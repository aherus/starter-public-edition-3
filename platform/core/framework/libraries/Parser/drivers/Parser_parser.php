<?php
/**
 * CodeIgniter
 *
 * An open source application development framework for PHP 5.2.4 or newer
 *
 * NOTICE OF LICENSE
 *
 * Licensed under the Open Software License version 3.0
 *
 * This source file is subject to the Open Software License (OSL 3.0) that is
 * bundled with this package in the files license.txt / license.rst.  It is
 * also available through the world wide web at this URL:
 * http://opensource.org/licenses/OSL-3.0
 * If you did not receive a copy of the license and are unable to obtain it
 * through the world wide web, please send an email to
 * licensing@ellislab.com so we can send you a copy immediately.
 *
 * @package		CodeIgniter
 * @author		EllisLab Dev Team
 * @copyright	Copyright (c) 2008 - 2013, EllisLab, Inc. (http://ellislab.com/)
 * @license		http://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 * @link		http://codeigniter.com
 * @since		Version 1.0
 * @filesource
 */
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Parser Class
 *
 * @package		CodeIgniter
 * @subpackage	Libraries
 * @category	Parser
 * @author		EllisLab Dev Team
 * @link		http://codeigniter.com/user_guide/libraries/parser.html
 */
class CI_Parser_parser extends CI_Parser_driver {

	/**
	 * Left delimeter character for psuedo vars
	 *
	 * @var string
	 */
	public $l_delim = '{';

	/**
	 * Right delimeter character for psuedo vars
	 *
	 * @var string
	 */
	public $r_delim = '}';

	/**
	 * Reference to CodeIgniter instance
	 *
	 * @var object
	 */
	protected $CI;

	// --------------------------------------------------------------------

	/**
	 * Class constructor
	 *
	 * @return	void
	 */
	public function __construct()
	{
		$this->CI =& get_instance();
	}

	// --------------------------------------------------------------------

	/**
	 * Parse a template
	 *
	 * Parses pseudo-variables contained in the specified template view,
	 * replacing them with the data in the second param
	 *
	 * @param	string
	 * @param	array
	 * @param	bool
	 * @return	string
	 */
	// Modified by Ivan Tcholakov, 27-DEC-2013.
	//public function parse($template, $data = array(), $return = FALSE)
	public function parse($template, $data = array(), $return = FALSE, $config = array())
	//
	{
		if (!is_array($data))
		{
			$data = array();
		}

		// Adaptation for HMVC by wiredesignz.
		//$template = $this->CI->load->view($template, $data, TRUE);

		$ci = $this->CI;

		foreach (debug_backtrace() as $item) {
			$object = isset($item['object']) ? $item['object'] : null;
			if (is_object($object) && @ is_a($object, 'MX_Controller')) {
				$ci = $object;
				break;
			}
		}

		$template = $ci->load->view($template, $data, TRUE);
		//

		// Modified by Ivan Tcholakov, 27-DEC-2013.
		//return $this->_parse($template, $data, $return);
		return $this->_parse($template, $data, $return, $config);
		//
	}

	// --------------------------------------------------------------------

	/**
	 * Parse a String
	 *
	 * Parses pseudo-variables contained in the specified string,
	 * replacing them with the data in the second param
	 *
	 * @param	string
	 * @param	array
	 * @param	bool
	 * @return	string
	 */
	// Modified by Ivan Tcholakov, 27-DEC-2013.
	// Modified by Ivan Tcholakov, 27-DEC-2013.
	public function parse_string($template, $data = array(), $return = FALSE, $config = array())
	// Modified by Ivan Tcholakov, 27-DEC-2013.
	{
		if (!is_array($data))
		{
			$data = array();
		}

		// Modified by Ivan Tcholakov, 27-DEC-2013.
		//return $this->_parse($template, $data, $return);
		return $this->_parse($template, $data, $return, $config);
		//
	}

	// --------------------------------------------------------------------

	/**
	 * Parse a template
	 *
	 * Parses pseudo-variables contained in the specified template,
	 * replacing them with the data in the second param
	 *
	 * @param	string
	 * @param	array
	 * @param	bool
	 * @return	string
	 */
	// Modified by Ivan Tcholakov, 27-DEC-2013.
	//protected function _parse($template, $data, $return = FALSE)
	protected function _parse($template, $data, $return = FALSE, $config = array())
	//
	{
		if ($template === '')
		{
			return FALSE;
		}

		foreach ($data as $key => $val)
		{
			$template = is_array($val)
					? $this->_parse_pair($key, $val, $template)
					: $template = $this->_parse_single($key, (string) $val, $template);
		}

		if ($return === FALSE)
		{
			$this->CI->output->append_output($template);
		}

		return $template;
	}

	// --------------------------------------------------------------------

	/**
	 * Set the left/right variable delimiters
	 *
	 * @param	string
	 * @param	string
	 * @return	void
	 */
	public function set_delimiters($l = '{', $r = '}')
	{
		$this->l_delim = $l;
		$this->r_delim = $r;
	}

	// --------------------------------------------------------------------

	/**
	 * Parse a single key/value
	 *
	 * @param	string
	 * @param	string
	 * @param	string
	 * @return	string
	 */
	protected function _parse_single($key, $val, $string)
	{
		return str_replace($this->l_delim.$key.$this->r_delim, (string) $val, $string);
	}

	// --------------------------------------------------------------------

	/**
	 * Parse a tag pair
	 *
	 * Parses tag pairs: {some_tag} string... {/some_tag}
	 *
	 * @param	string
	 * @param	array
	 * @param	string
	 * @return	string
	 */
	protected function _parse_pair($variable, $data, $string)
	{
		if (FALSE === ($match = $this->_match_pair($string, $variable)))
		{
			return $string;
		}

		$str = '';
		foreach ($data as $row)
		{
			$temp = $match[1];
			foreach ($row as $key => $val)
			{
				$temp = is_array($val)
						? $this->_parse_pair($key, $val, $temp)
						: $this->_parse_single($key, $val, $temp);
			}

			$str .= $temp;
		}

		return str_replace($match[0], $str, $string);
	}

	// --------------------------------------------------------------------

	/**
	 * Matches a variable pair
	 *
	 * @param	string
	 * @param	string
	 * @return	mixed
	 */
	protected function _match_pair($string, $variable)
	{
		return preg_match('|'.preg_quote($this->l_delim).$variable.preg_quote($this->r_delim).'(.+?)'.preg_quote($this->l_delim).'/'.$variable.preg_quote($this->r_delim).'|s',
					$string, $match)
			? $match : FALSE;
	}

}

/* End of file Parser.php */
/* Location: ./system/libraries/Parser/drivers/Parser_parser.php */
