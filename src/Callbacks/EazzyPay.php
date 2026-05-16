<?php
	
	namespace Caydeesoft\Payments\Callbacks;
	
	class EazzyPay extends GenericCallback
		{
			protected function providerName()
				{
					return 'eazzypay';
				}
		}
