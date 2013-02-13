/*! 
 *  \brief Checks the value of the questiontype on change and sets the form values
 *  
 *  Handles questionTypeChanges to prevent wrong / unnessecary input of parameters
 */
function questionTypeChange()
{
	var qt = document.getElementById('questiontype');
	var qg = document.getElementById('questiongroup');
	
	if( qt.value == 2 )
	{
		qg.enabled = true;
	}
	else
	{
		qg.enabled = false;	
	}
}
