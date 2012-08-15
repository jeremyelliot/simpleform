SimpleForm
==========

SimpleForm is a PHP class to help you put values into forms.
------------------------------------------------------------


- Takes the work out of setting dynamic values in an HTML form.

- Keeps the presentation (HTML) separate from the logic (PHP).

- Allows easy manipulation of the form using all the features of 
[SimpleXML](http://www.php.net/manual/en/simplexml.examples-basic.php), 
plus a couple of extra methods, specifically for forms.

It's used like this:
--------------------

1. Define your form using HTML and put it into a string variable, 
perhaps from a file.

		$form_html = file_get_contents($form_file);

2. Create a SimpleForm object based on your form HTML.

		$form_object = new SimpleForm($form_html);

3. (optionally) Modify the SimpleForm. For example, add/remove `<option>`s 
	to/from a `<select>` element. See the demo for information.

4. Put values into the form elements.

		$form_object->setValue('firstname', $firstname);

5. Get the HTML from the SimpleForm object and display it.

		<div><?php echo $form_object; ?></div>


To find out more, take a look at the 
[demo site](http://workspace.webtree.co.nz/simpleform/).

