<?php

if (!defined('BASEPATH')) exit('No direct script access allowed');

/* SimpleForm.php 
 * 
 * note: The CI standard for class and method naming is underscore_separated
 *    but this class used camelCase because it extends a php class
 *    that uses camelCase.
 */

/* class for manipulating XHTML forms 
 * 
 * The basic use of this class is to load an HTML form from a file or string,
 * set new values into the form, then get the form HTML (with new values) to
 * be displayed.
 * 
 * It extends SimpleXMLElement, so it also allows the form to be 
 * traversed and modified in all sorts of other ways.
 */

class SimpleForm extends SimpleXMLElement {

	/**
	 * Returns the value of the named control (form element)
   * 
	 * If the control has multiple selected values, or there are multiple
   * controls with the same name (array notation eg. phone[]),
   * then they are returned in an array.
	 * 
   * If the named control is not found in the form, an empty array is returned.
   * 
	 * @param string $controlName name attribute of the form element(s)
	 * @return array one or more values
	 */
	public function getValue($controlName)
	{
		$result = array();
		foreach ($this->getControlsByName($controlName) as $control)
		{
			$tagname = strtolower($control->getName());
			switch ($tagname)
			{
				case 'input':
					$result[] = (string) $control['value'];
					break;
				case 'select':
					foreach ($control->getElementsByTagName('option') as $elem)
					{
						if ($elem['selected'] == 'selected')
						{
							$result[] = (string) $elem['value'];
						}
					}
					break;
				case 'textarea':
					$result[] = (string) $control;
					break;
				default:
					break;
			}
		}
		return $result;
	}

	/**
	 * Sets the value(s) of the named control.
   * 
	 * If the control takes multiple values, or there are multiple
   * controls with the same name (array notation eg. phone[]),
   * $value should be an array of values.
   * 
   * NOTE: Any values that are set in the control will be unset (not ignored)
   * if they do not exist in the $value array. This behaviour allows you
   * to use a cleaned & validated version of the request (eg. $_POST)
   * to fill the form
   * 
	 * @param string $controlName name attribute of the form element(s)
   * @param string|array $value new value(s) to set
	 * @return \SimpleForm $this
	 */
	public function setValue($controlName, $value)
	{
		$value = (is_array($value))
				? $value
				: array($value);
		foreach ($this->getControlsByName($controlName) as $control)
		{
			$tagname = strtolower($control->getName());
			switch ($tagname)
			{
				case 'input':
					SimpleForm::setInputValue($control, $value);
					break;
				case 'select':
					SimpleForm::setSelectValue($control, $value);
					break;
				case 'textarea':
					$control[0] = $value[0];
					break;
				default:
					break;
			}
		}
		return $this;
	}

	/**
	 * Takes an array of values and inserts them into the form.
   * 
	 * This method can take an array in the same form as the $_REQUEST array
	 * created by the submission of $this form. For example:
	 * 
	 *    $form_file = file_get_contents('my_form.tpl');
	 *    $my_form = new SimpleForm($form_file);
	 * 
	 *    $my_form->setValues(clean_and_validate($_POST));
	 * 
	 *    ...
	 * 
	 *    <div class="form-container"><?php echo $my_form; ?></div>
	 * 
	 * @param type $values
	 * @return \SimpleForm 
	 */
	public function setValues($values)
	{
		if (!empty($values))
		{
			foreach ($values as $name => $value)
			{
				$this->setValue($name, $value);
			}
		}
		return $this;
	}

	/**
	 * Sets the value of an <input> control, used by the setValue method.
   * 
   * NOTE: Any checkbox or radio will be unset (not ignored) if its value
   * does not exist in the $value array. 
   * 
   * For other <input> types, the new value will come from $value in the
   * case of a string or number, or from $value[0] in the case of an array. 
   * 
	 * @param SimpleXMLElement $control the <input> element
   * @param string|array $value new value(s)
	 */
	public static function setInputValue(SimpleXMLElement $control, $value)
	{
		$value = (is_array($value))
				? $value
				: array($value);
		switch (strtolower($control['type']))
		{
			// two special cases
			case 'radio':
			case 'checkbox':
				if (in_array($control['value'], $value))
				{
					$control['checked'] = 'checked';
				}
				else
				{
					unset($control['checked']);
				}
				break;
			// all other <input>s
			default:
				$control['value'] = $value[0];
				break;
		}
	}

	/**
	 * Sets the value(s) of a <select> control.
   * 
	 * @param SimpleXMLElement $control the <select> element
   * @param string|array $value new value(s) to set
	 */
	public static function setSelectValue(SimpleXMLElement $control, $value)
	{
		$value = (is_array($value))
				? $value
				: array($value);
		foreach ($control->option as $option)
		{
			if (in_array($option['value'], $value))
			{
				$option['selected'] = 'selected';
			}
			else
			{
				unset($option['selected']);
			}
		}
	}

	/**
	 * Adds <option> elements to a <select> element.
   * 
   * This method has no effect if this element is not a select
	 *
	 * @param string $value value of the option
	 * @param string $label display label for the option
	 * @return \SimpleForm 
	 */
	public function addOption($value, $label = false)
	{
		if ($this->getName() == 'select')
		{
			$label = ($label !== false)
					? $label
					: $value;
			$this->addChild('option', htmlentities($label))
					->addAttribute('value', htmlentities($value));
		}
		return $this;
	}

	/**
	 * Adds <option>s to this <select> element
	 *
   * This method has no effect if this element is not a select
	 *
	 * @param array $options options to be added - array('value' => 'label', ...)
	 * @return \SimpleForm $this
	 */
	public function addOptions($options)
	{
		if ($this->getName() == 'select')
		{
			foreach ($options as $value => $label)
			{
				$this->addOption($value, $label);
			}
		}
		return $this;
	}

	/**
	 * Gets a single SimpleForm element by its id attribute 
	 *
	 * @param string $elemId id attribute of element
	 * @return \SimpleForm identified element
	 */
	public function getElementById($elemId)
	{
		return current($this->xpath("//*[@id='$elemId']"));
	}

	/**
	 * Gets an array of SimpleForm elements by tagname
   * 
	 * @param string $tagName eg. 'input' or 'textarea'
	 * @return array<SimpleForm> array of elements 
	 */
	public function getElementsByTagName($tagName)
	{
		return $this->xpath("//$tagName");
	}

	/**
	 * Returns an array of the form elements that have name="$controlname"
	 * Handles square-bracket notation for array members ie. name="somename[]"
	 *
	 * @param string $controlName name attribute of the form elements
	 * @return array<SimpleForm> array of elements 
	 */
	public function getControlsByName($controlName)
	{
		$xpquery = "//*[(@name='$controlName') or (@name='{$controlName}[]')]";
		return $this->xpath($xpquery);
	}

	/**
	 * Returns this as an xml fragment string. Like SimpleXMLElement->asXML,
	 * but without xml declaration tag <?xml ... ?>
	 * 
	 * @return string the form as an XHTML string
	 */
	public function html()
	{
		// removes xml declaration from beginning of string
		return preg_replace('/<\?.*?\?>/', '', $this->asXML());
	}

	/**
	 * same as html()
	 * 
	 * @return type 
	 */
	public function __toString()
	{
		return $this->html();
	}

	/**
	 * wrapper for SimpleXMLElement::addAttribute($n, $v [, $ns]), 
	 * returns $this so that multiple calls to addAttribute can be 
	 * chained together.
	 * e.g 
	 * $html->addChild('a' 'click this')
	 *      ->addAttribute('href', '/page.php')
	 *      ->addAttribute('class', 'sweet-as')
	 *      ->addAttribute('id', 'thelink');
	 *
	 * @param string $name name of the attribute
	 * @param string $value value of the attribute
	 * @param string $namespace attribute namespace
	 * @return \SimpleForm $this
	 */
	public function addAttribute($name, $value, $namespace = null)
	{
		parent::addAttribute($name, $value, $namespace);
		return $this;
	}

	/**
	 * Tries set to the action attribute of this form.
	 * 
	 * @param string $url
	 * @return boolean TRUE if the <form> element was found and modified
	 */
	public function setAction($url)
	{
		$form = $this->getElementsByTagName('form');
		if (is_array($form))
		{
			$form = $form[0];
		}
		else
		{
			return false;
		}
		$form['action'] = $url;
    return TRUE;
	}

}

/* End of file SimpleForm.php */ 