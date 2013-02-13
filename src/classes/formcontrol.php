<?php

/*******************************************************************************
*
*                           I N C L U D E  F I L E S
*
*******************************************************************************/

require_once( "lang/lang-en.php" );
require_once( "logger.php" );

/*******************************************************************************
*
*                               M A I N  C O D E
*
*******************************************************************************/

define("TYPE_TEXTBOX", 0);
define("TYPE_DROPDOWNBOX", 1);
define("TYPE_MULTILINEBOX", 2);
define("TYPE_CHECKBOX", 3);

class EditControl
{
	public $id;
	public $name;
	public $type;
	public $value;
	public $items;
	public $maxlength;
	
	public function EditControl( $id, $name, $type, $value, $items, $maxlength )
	{
		$this->id        = $id;
		$this->name      = $name;
		$this->type      = $type;
		$this->value     = $value;
		$this->items     = $items;
		$this->maxlength = $maxlength;
	}
}

class DetailObject
{
	public $id;
	public $name;
	public $class;
	
	public function DetailObject( $id, $name, $class )
	{
		$this->id        = $id;
		$this->name      = $name;
		$this->class     = $class;
	}
}

/**
 * \brief This control generates html code for an editable object.
 * An editable object should implement the functions: 'remove()', 'GetControls()', 
*/
class FormControl
{
	public $controlObject;
	public $editControls;
	public $detailObjects;
	public $parentObject;
	
	/**
	  * Contructor
	  */
	public function FormControl( $controlObject )
	{
		$this->controlObject = $controlObject;
		$this->editControls  = $controlObject->GetEditControls();
		$this->detailObjects = $controlObject->GetDetailObjects();
		$this->parentObject  = $controlObject->GetParentObject();
		$_SESSION['currentControl'] = $this;
	}

	/**
	  * \brief Retrieves the current control
	  */	
	public static function GetCurrentControl()
	{
		return( $_SESSION['currentControl'] );
	}
	
	/**
	  * \brief Builds the view form with all controls
	  */
	public function BuildViewForm()
	{
		$formData .= "<h2>" . $this->controlObject->GetObjectTitle() . "</h2>\n";
		$formData .= "<form id='viewForm' action='javascript:void(null);' method='get'>\n";
		
		$formData .= "<table class='view'>\n";
		foreach( $this->editControls as $control )
		{
			$formData .= "\t<tr>\n";
			$formData .= "\t\t<td class='name'>$control->name :</td>\n";
			$formData .= "\t\t<td>" . $this->BuildViewControl( $control ) . "</td>\n";
			$formData .= "\t</tr>\n";
		}
		$formData .= "</table>\n";
		$formData .= "</form>\n";
		$formData .= "<hr>\n";
		
		$formData .= "<a href='#' onClick=\"xajax_editControl();\" >" . LOC_EDIT . "</a>\n";
		$formData .= "<a href='#' onClick=\"if(confirm('" . LOC_AREYOUSURE . "')) xajax_deleteControl();\" >" . LOC_DELETE . "</a>\n";
		
		if( $this->parentObject != null )
		{
			$class = $this->parentObject->class;
			$id    = $this->parentObject->id;
			$formData .= "<a href='#' onClick=\"xajax_viewControl( '$class', $id );\">" . LOC_BACK . "</a>\n";
		}
		
		$formData .= "<hr>\n";
		
		$formData .= "<h3>" . LOC_DETAILS . "</h3>\n";

		if( $this->detailObjects != null )
		{
			$formData .= "<table class='detail'>\n";
			foreach( $this->detailObjects as $detailobject )
			{
				$formData .= "\t<tr>\n";
				$formData .= "\t\t<td>" . $detailobject->name . "</td>\n";
				$formData .= "\t\t<td><a href='#' onClick=\"xajax_viewControl('$detailobject->class',$detailobject->id);\">".LOC_DETAILS."</a></td>\n";
				$formData .= "\t</tr>\n";
			}
			$formData .= "</table>\n";
		}
		else
		{
			$formData .= LOC_NODETAIL . "\n";
		}
		
		if( $this->controlObject->GetDetailObjectType() != null )
		{
			$formData .= "<a href='#' onClick=\"xajax_addControl( '".$this->controlObject->GetDetailObjectType()."',".$this->controlObject->id." );\">" . LOC_ADDDETAIL . "</a>\n";
		}

		return( $formData );
	}
	
	/**
	  * \brief Builds the edit form with all controls
	  */
	public function BuildEditForm()
	{
		$formData .= "<form id='editForm' action='javascript:void(null);' method='get'>\n";
		
		$formData .= "<table class='edit'>\n";
		foreach( $this->editControls as $control )
		{
			$formData .= "\t<tr>\n";
			$formData .= "\t\t<td class='name'>$control->name :</td>\n";
			$formData .= "\t\t<td>" . $this->BuildEditControl( $control ) . "</td>\n";
			$formData .= "\t</tr>\n";
		}
		$formData .= "</table>\n";
		$formData .= "<hr>\n";
		$formData .= "<a href='#' onClick=\"xajax_cancelEdit();\" >" . LOC_CANCEL . "</a>\n";
		$formData .= "<a href='#' onClick=\"xajax_saveEdit(xajax.getFormValues('editForm'));\" >" . LOC_SAVE . "</a>\n";
		$formData .= "</form>\n";

		return( $formData );
	}
	
	/**
	 * \brief Removes the control
	 */
	public function DeleteControl()
	{
		$this->controlObject->Remove();
	}
	
	/**
	 * \brief Sends the form data to the control which is controlled
	 */
	public function SaveControl( $formData )
	{
		Logger::Write( "~~FormControl::SaveControl()" );
		$this->controlObject->SaveEditControls( $formData );
		$this->editControls  = $this->controlObject->GetEditControls();
		Logger::Write( "~~FormControl::SaveControl()-FINISHED" );
	}
	
	/**
	 * \brief Builds the control for viewing
	 */
	private function BuildViewControl( $control )
	{
		if( $control->type == TYPE_TEXTBOX )
		{
			$formData .= "$control->value";
		}
		else if( $control->type == TYPE_DROPDOWNBOX )
		{
			foreach( $control->items as $dropdownItem )
			{
				if( $dropdownItem[0] == $control->value )
					$formData .= "$dropdownItem[1]";
			}
		}
		else if( $control->type == TYPE_MULTILINEBOX )
		{
			$formData .= "$control->value";
		}
		else if( $control->type == TYPE_CHECKBOX )
		{
			if( $control->value == true )
				$formData .= LOC_ON;
			else
				$formData .= LOC_OFF;
		}

		return( $formData );
	}
	
	/**
	 * \brief Builds the control for editing
	 */
	private function BuildEditControl( $control )
	{
		if( $control->type == TYPE_TEXTBOX )
		{
			$formData .= "<input type='textbox' id='$control->id' name='$control->id' value='$control->value' maxlength='$control->maxlength' size='100'>";
		}
		else if( $control->type == TYPE_DROPDOWNBOX )
		{
			$formData .= "<select id='$control->id' name='$control->id'>";
			foreach( $control->items as $dropdownItem )
			{
				if( $dropdownItem[0] == $control->value )
					$formData .= "<option value='$dropdownItem[0]' selected>$dropdownItem[1]</option>";
				else
					$formData .= "<option value='$dropdownItem[0]' >$dropdownItem[1]</option>";
			}
			$form_data .= "</select>\n";
		}
		else if( $control->type == TYPE_MULTILINEBOX )
		{
			$formData .= "<textarea id='$control->id' name='$control->id' cols='75' rows='5' maxlength='$control->maxlength'>$control->value</textarea>";
		}
		else if( $control->type == TYPE_CHECKBOX )
		{
			if( $control->value == true )
				$formData .= "<input type='checkbox' id='$control->id' name='$control->id' checked>";
			else
				$formData .= "<input type='checkbox' id='$control->id' name='$control->id'>";
		}
		

		return( $formData );
	}
}

?>