<?php

	function update($verb)
	{
		$args   = func_get_args();
		
		switch ($verb)
		{
			case 'hsrv':
				switch ($args[1][0][0])
				{
					case 0:
					break;
					
					case 1:
					break;
				}
			break;

			case 'hsrvbusy':
				switch ($args[1][0][0])
				{
					case 0:
					break;
					
					case 1:
					break;
				}
			break;
		}
	}

?>