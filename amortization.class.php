<?php
class Amortization
{
	
	# AMOUNT OF LOAN
	public $principal;
	
	# CURRENT AMOUNT OF LOAN
	public $currentPrincipal;
	
	public $newPrincipal;
	
	# AMOUNT OF LOAN
	public $interestRate;
	
	# FORMAT FOR CURRANCY TYPE
	public $currency_char = '$';
	
	# FORMAT FOR HOW MANY DECIMAL POINTS TO SHOW
	public $decimals = 2;
	
	# FORMAT FOR DECIMAL POINT
	public $dec_point = '.';
	
	# FORMAT FOR THOUSANDS POSITION
	public $thousands_sep = ',';
	
	# LENGTH OF LOAN IN YEARS
	public $term;
	
	# LENGTH OF LOAN IN YEARS
	public $totalInterest;
	
	# PAYMENT OF MORTGAGE
	public $payment;
	
	# CREDIT LINE FOR HELOC
	public $creditLine;

	# DATE OF LOAN START
	public $loanStart;
	
	# START YEAR OF LOAN
	public $startYear;
	
	# START MONTH OF LOAN
	public $startMonth;
	
	# START DAY OF LOAN
	public $startDay;
	
	# DATE OF LOAN FINISH
	public $finishDate;
	
	# GROUP BY DATE VAR
	public $groupByDate = false;
	
	# PAYMENT OF HELOC
	public $helocPayment;
	
	# YEAR OF LOAN START
	public $accelLength;
	
	# AMOUNT OF ADDTIONAL LOAN
	public $lineCredit;
	
	# INTEREST RATE OF ADDITIONAL LOAN
	public $noteRate;
	
	# REMAINING TERM OF LOAN
	public $remainingTerm;
	
	# COUNT OF REMAINING TERM
	public $termCount;
	
	# PAYOFF LENGTH FOR LOAN
	public $payoffLength;
	
	# INTEREST ON HELOC LOAN
	public $helocInterest;
	
	# INTEREST ON ACCELERATED
	public $accelInterest;
	
	# INTEREST SAVED FOR HELOC
	public $interestSaved;
	
	# TOTAL PAID WITH HELOC
	public $totalHelocPaid;
	
	# TOTAL PAID WITH NORMAL AMORTIZATION
	public $totalAmortPaid;
	
	# COMPELTE AMORTIZATION EXTRA PAYMENT
	public $addtionalPayment = array();
	
	public $showAdditional;
	
	public $heading = 'Current Mortgage';
	
	public $subHeading = 'Current Mortgage';
	
	public $currentBalance;
	
	public $type;
	
	# FREED MONEY AT THE END OF EACH MONTH
	public $surplus;
	
	public $schedule;
	
	public $yearsPayoff;
	
	public $lastPayoffDate;
	
	public $startOver = false;
	
	public $oldResults;
	
	public $change;
	
	public $addPayment;
	
	public $accordion;
	
	public $currentBalDate;


	function getAmortization()
	{	
	
		$periods  	 = $this->term * 12; # GET PERIOD OF MONTHS
		$balance  	 = $this->principal; # SET BALANCE
		$interest 	 = $this->interestRate / (100 * 12); # CALCULATE INTEREST
		$loanPayment = $this->principal * $interest / (1 - pow((1 + $interest), -$periods)); # CALCULATE PAYMENT
		$creditLine  = $this->creditLine; # CREDIT LINE
		$heloc		 = $this->schedule;
		$balanceDate = date('n/Y',strtotime($this->currentBalDate));
		
		$dateArray = explode('/',$this->loanStart);
		
		$startYear  = $dateArray[2];
		$startMonth = $dateArray[0];
		
		# COUNT FOR ADDING ADDITIONAL PAYMENT
		$count = 1;
		
		# LOOP THROUGH PERIODS TO GET RESULTS
		for($period = 0; $period < $periods; $period++)
		{
		
			$date = $this->getDate($startYear, $startMonth + $period);# GET DATE FOR ARRAY
			
			$yearAccel = end(explode('/',$date));
			
		
			if ($this->type == 'accel' && isset($heloc[$date]) && !empty($heloc[$date]))
			{
			
				$results[$date]['highlight'] = true;
				
				# SET ADDITIONAL PAYMENT
				if ($balance < $heloc[$date]['borrow'])
				{
					$int = $balance * $interest;
					$add = $balance - ($loanPayment - $int);
					
				}
				else
				{
				
					$add = $heloc[$date]['borrow'];
				
				}
				
				unset($heloc[$date]);
				
			}
			else
			{
			
				$add = 0;
			
			}
		
			if ($date == $balanceDate)
			{
			
				if ($this->currentPrincipal > $balance && $this->startOver == false && $this->type != 'accel')
				{
			
					$results[$date]['highlight'] = true;
				
					$split = explode('/',date('n/Y'));
				
					$this->startOver = true;
					$this->newPrincipal = $this->currentPrincipal; # SET BALANCE
					$this->payment = $loanPayment; # SET BALANCE
					//$this->addPayment = $add;
					$this->loanStart = $split[0].'/1/'.$split[1];
					$this->oldResults = $results;
					$results = $this->getNewAmortization();
					break;
				
				
				}
				else
				{
				
					if ($balance > $this->currentPrincipal)
					{
			
						$newBalance = $this->currentPrincipal;
						$currentInt = $newBalance * $interest;
						$dif = $balance - $this->currentPrincipal;
						$this->change = true;
					
					
					}
					else
					{
			
						$currentInt = $balance * $interest;
						$dif = $loanPayment - $currentInt;
						$this->change = false;
					
					}
			
					$results[$date]['date']       = $date;# SET DATE
					$results[$date]['interest']  += $currentInt;# SET INTEREST AMOUNT
					$results[$date]['principal'] += $newPayment = $dif + $add;# SET PAID PRINCIPAL AMOUNT
					$results[$date]['balance']    = $balance = $balance - $newPayment;# SET REMIANING BALANCE
					$results[$date]['extra']  	  = $add;
				
				}
				
				unset($newBalance);
			
			}
			else
			{
			
				$dif = 0;
				$newBalance = $balance;
		
				$results[$date]['date']       = $date;# SET DATE
				$results[$date]['interest']  += $currentInt = $balance * $interest;# SET INTEREST AMOUNT
				$results[$date]['principal'] += $newPayment = ($loanPayment + $add) - $currentInt;# SET PAID PRINCIPAL AMOUNT
				$results[$date]['balance']    = $balance = $newBalance - $newPayment;# SET REMIANING BALANCE
				$results[$date]['extra']  	  = $add;
				$this->change = false;
			
			}
			
			# DON'T WANT NEGATIVE NUMBERS. PLUS END THE PERIOD IF 0.
			if($results[$date]['balance'] <= 0)
			{
		
				$results[$date]['principal'] += $balance; 
				$results[$date]['balance'] = 0;
				break;	
						
			}
			elseif ($results[$date]['balance'] <= 5)
			{
			
				$results[$date]['principal'] += $balance; 
				$results[$date]['balance'] = 0;
				break;	
			
			} 
			
			# COUNT FOR ADDITIONAL PAYMENT
			$count++;
			
		}
		
		# RETURN RESULTS
		return $results;
	
	}# END getAmortization
	
	
	function getNewAmortization()
	{
	
		$periods  	 = $this->term * 12; # GET PERIOD OF MONTHS
		$balance  	 = $this->newPrincipal; # SET BALANCE
		$interest 	 = $this->interestRate / (100 * 12); # CALCULATE INTEREST
		$loanPayment = $this->payment; # CALCULATE PAYMENT
		$creditLine  = $this->creditLine; # CREDIT LINE
		$heloc		 = $this->schedule;
		$this->change = true;
		
		$dateArray = explode('/',$this->loanStart);
		
		$startYear  = $dateArray[2];
		$startMonth = $dateArray[0];
		
		# COUNT FOR ADDING ADDITIONAL PAYMENT
		$count = 1;
		
		# LOOP THROUGH PERIODS TO GET RESULTS
		for($period = 0; $period < $periods; $period++)
		{
		
			$date = $this->getDate($startYear, $startMonth + $period);# GET DATE FOR ARRAY
			
			$yearAccel = end(explode('/',$date));
			
		
			if ($this->type == 'accel' && isset($heloc[$date]))
			{
			
				$results[$date]['highlight'] = true;
				
				# SET ADDITIONAL PAYMENT
				if ($balance < $heloc[$date]['borrow'])
				{
					$int = $balance * $interest;
					$add = $balance - ($loanPayment - $int);
					
				}
				else
				{
				
					$add = $heloc[$date]['borrow'];
				
				}
				
				unset($heloc[$date]);
				
			}
			else
			{
			
				$add = 0;
			
			}
			
			$dif = 0;
			$newBalance = $balance;
	
			$results[$date]['date']       = $date;# SET DATE
			$results[$date]['interest']  += $currentInt = $balance * $interest;# SET INTEREST AMOUNT
			$results[$date]['principal'] += $newPayment = ($loanPayment + $add) - $currentInt;# SET PAID PRINCIPAL AMOUNT
			$results[$date]['balance']    = $balance = $newBalance - $newPayment;# SET REMIANING BALANCE
			$results[$date]['extra']  	  = $add;
			
			# DON'T WANT NEGATIVE NUMBERS. PLUS END THE PERIOD IF 0.
			if($results[$date]['balance'] <= 0)
			{
		
				$results[$date]['principal'] += $balance; 
				$results[$date]['balance'] = 0;
				break;	
						
			} 
			
			# COUNT FOR ADDITIONAL PAYMENT
			$count++;
			
		}
		
		# RETURN RESULTS
		return array_merge($this->oldResults,$results);
		
	}
	
	
	function loanSummary($return = '')
	{
	
		global $future;
	
		$results 	 = ($this->startOver == true) ? $this->getNewAmortization() : $this->getAmortization();
		
		$periods     = $this->term * 12;
		$loanPayment = $this->principal * $this->interestRate / 100 / 12 / (1 - pow((1 + $this->interestRate / 12 / 100), -$periods));# CALCULATE PAYMENT
		
		foreach ($results as $key => $void)
		{
		
			$interestSum[]  = $results[$key]['interest'];
			$principalSum[] = $results[$key]['principal'];
			$extra[]     	= $results[$key]['extra'];
		
		}
		
		$date = end($results);
		
		$split = explode('/',$date['date']);
		
		$endTime = $future->getTimestamp($split[0].'/1/'.$split[1]);
		$nowTime = $future->getTimestamp(date('n/j/Y'));
		
		$dif = ($endTime - $nowTime)/(60*60*24);
		
		$this->yearsPayoff = round($dif/365.25,1);
		
		$this->lastPayoffDate = $split[0].'/1/'.$split[1];
		
		
		if ($return == true)
		{
		
			$content = '
				<tr class="blueBg">
					<td class="blue size16 bold paddingVert2 paddingHort5" colspan="2">'.$this->heading.'</td>
				</tr>
				<tr>
					<td align="left" class="paddingVert2 paddingHort5">Original Loan Amount</td>
					<td align="right" class="paddingVert2 paddingHort5">'.$this->formatNum($this->principal).'</td>
				</tr>
				<tr class="blueBg">
					<td align="left" class="paddingVert2 paddingHort5">Current Balance</td>
					<td align="right" class="paddingVert2 paddingHort5">'.$this->formatNum($this->currentPrincipal).'</td>
				</tr>
				<tr>
					<td align="left" class="paddingVert2 paddingHort5">Interest Rate</td>
					<td align="right" class="paddingVert2 paddingHort5">'.number_format($this->interestRate,3).'%</div>
				</tr>
				<tr class="blueBg">
					<td align="left" class="paddingVert2 paddingHort5">Total Payments</td>
					<td align="right" class="paddingVert2 paddingHort5">'.$this->formatNum($this->principal + array_sum($interestSum)).'</td>
				</tr>
				<tr>
					<td align="left" class="paddingVert2 paddingHort5">Total Interest</td>
					<td align="right" class="paddingVert2 paddingHort5">'.$this->formatNum(array_sum($interestSum)).'</td>
				</tr>
				<tr class="blueBg">
					<td align="left" class="paddingVert2 paddingHort5">Years until paid off</td>
					<td align="right" class="paddingVert2 paddingHort5">'.$this->yearsPayoff.'</td>
				</tr>
				<tr>
					<td align="left" class="paddingVert2 paddingHort5">Monthly payment</td>
					<td align="right" class="paddingVert2 paddingHort5">'.$this->formatNum($loanPayment).'</td>
				</tr>
				<tr class="blueBg">
					<td align="left" class="paddingVert2 paddingHort5">Last Payment Date</td>
					<td align="right" class="paddingVert2 paddingHort5">'.$this->lastPayoffDate.'</td>
				</tr>
				<tr>
					<td class="bold paddingVert2 paddingHort5" colspan="2">'.$this->subHeading.'</td>
				</tr>';
		
			return $content;
		
		}
		else
		{			
		
			$content = '
				<div class="blue size16 bold blueBg paddingVert5 paddingHort5 append-bottom15">'.$this->heading.'</div>
				<div class="paddingVert2 paddingHort5">
					<div class="floatLeft">Original Loan Amount</div>
					<div class="floatRight">'.$this->formatNum($this->principal).'</div>
					<div class="clear"></div>
				</div>
				<div class="clear"></div>
				<div class="blueBg paddingVert2 paddingHort5 prepend-top5">
					<div class="floatLeft">Current Balance</div>
					<div class="floatRight">'.$this->formatNum($this->currentPrincipal).'</div>
					<div class="clear"></div>
				</div>
				<div class="clear"></div>
				<div class="paddingVert2 paddingHort5 prepend-top5">
					<div class="floatLeft">Interest Rate</div>
					<div class="floatRight">'.number_format($this->interestRate,3).'%</div>
					<div class="clear"></div>
				</div>
				<div class="clear"></div>
				<div class="blueBg paddingVert2 paddingHort5 prepend-top5">
					<div class="floatLeft">Total Payments</div>
					<div class="floatRight">'.$this->formatNum($this->principal + array_sum($interestSum)).'</div>
					<div class="clear"></div>
				</div>
				<div class="clear"></div>
				<div class="paddingVert2 paddingHort5 prepend-top5">
					<div class="floatLeft">Total Interest</div>
					<div class="floatRight">'.$this->formatNum(array_sum($interestSum)).'</div>
					<div class="clear"></div>
				</div>
				<div class="clear"></div>
				<div class="blueBg paddingVert2 paddingHort5 prepend-top5">
					<div class="floatLeft">Years until paid off</div>
					<div class="floatRight">'.$this->yearsPayoff.'</div>
					<div class="clear"></div>
				</div>
				<div class="clear"></div>
				<div class="paddingVert2 paddingHort5 prepend-top5">
					<div class="floatLeft">Monthly payment</div>
					<div class="floatRight">'.$this->formatNum($loanPayment).'</div>
					<div class="clear"></div>
				</div>
				<div class="clear"></div>
				<div class="blueBg paddingVert2 paddingHort5 prepend-top5">
					<div class="floatLeft">Last Payment Date</div>
					<div class="floatRight">'.$this->lastPayoffDate.'</div>
					<div class="clear"></div>
				</div>
				<div class="clear"></div>
				<div class="blueBg bold paddingVert5 paddingHort5 append-bottom15 prepend-top15">'.$this->subHeading.'</div>
				<div class="clear"></div>
				<div class="paddingVert2 paddingHort5 prepend-top5">
					<div class="floatLeft red bold width30">Year</div>
					<div class="floatLeft red bold width90 right">Total MTG Debt</div>
					<div class="floatLeft red bold width90 right">Total Debt Paid</div>
					<div class="floatLeft red bold width85 right">Total Interest</div>
					<div class="clear"></div>
				</div>
				<div class="clear"></div>';

		
			echo $content;
		
		}	
		
	}# END loanSummary
	
	
	
	function buildChart($return = '')
	{
	
		global $future;
	
		$results = ($this->startOver == true) ? $this->getNewAmortization() : $this->getAmortization();
		
		$group   = ($this->groupByDate == true) ? $this->groupByYear($results) : $results;
		
		$count = 0;
		
		$year = date('Y');
		
		$changeDate = date('n/Y',strtotime($this->currentBalDate));
		
		if ($return == true)
		{
			
			$content .= '<table cellpadding="0" cellspacing="0" border="0" align="center" width="345">
							<tr class="paddingVert2 paddingHort5 prepend-top5">
								<td width="30" class="red bold width30 size12 paddingVert2 paddingHort5">Year</td>
								<td width="90" align="right" class="red bold paddingVert2 paddingHort5">Total MTG Debt</td>
								<td width="90" align="right" class="red bold paddingVert2 paddingHort5">Total Debt Paid</td>
								<td width="85" align="right" class="red bold paddingVert2 paddingHort5">Total Interest</td>
							</tr>';
		
			foreach ($group as $key => $void)
			{
			
				$rowColor = ($count % 2) ? 'blueBg ' : '';
				
				if (!empty($group[$key]))
				{
				
					$highlight = ($group[$key]['highlight'] > 0)?' blue bold':'';
					
					$alter	= ($future->format('n/Y',$group[$key]['date']) == $changeDate || $group[$key]['date'] == $year) ? ' **' : '';
					
					$content .= '
							<tr class="'.$rowColor.'paddingVert2 paddingHort5 prepend-top5">
								<td class="paddingVert2 paddingHort5'.$highlight.'">'.$group[$key]['date'].'</td>
								<td align="right" class="paddingVert2 paddingHort5'.$highlight.'">'.$this->formatNum($group[$key]['balance']).'</td>
								<td align="right" class="paddingVert2 paddingHort5'.$highlight.'">'.$this->formatNum($group[$key]['principal']).$alter.'</td>
								<td align="right" class="paddingVert2 paddingHort5'.$highlight.'">'.$this->formatNum($group[$key]['interest']).'</td>
							</tr>';
						
				
				}
				
				$count++;
				
			}
			
			$content .= '</table>';
		
			return $content;
		
		}
		else
		{
		
			if ($this->accordion == true)
			{
		
				$year = '';
				
				$triggers = $this->groupByYear($results);
				
				$countGroup = count($group);
			
				foreach ($group as $key => $void)
				{
				
					$rowColor = ($count % 2) ? 'blueBg ' : '';
					
					if (!empty($group[$key]))
					{
					
						$prev = explode('/',$group[$key]['date']);
					
						$highlight = ($group[$key]['highlight'] > 0)?' blue bold':'';
						
						if ($this->groupByDate == true)
						{
					
							$split = explode('/',$changeDate);
						
							$alter	= ($split[1] == $year) ? '**' : '';
						
						
						}
						else
						{
							
							$alter	= ($group[$key]['date'] == $changeDate) ? '**' : '';
						
						}
						
						if ($year != $prev[1])
						{
						
							if ($count != 0)
							{
							
								$content .= '</div>';
							
							}
						
							$content .= '
									<div class="paddingVert2 paddingHort5 prepend-top5 bold trigger">
										<div class="arrow"></div>
										<div class="floatLeft width30">'.$prev[1].'</div>
										<div class="floatLeft width85 right">'.$this->formatNum($triggers[$prev[1]]['balance']).'</div>
										<div class="floatLeft width90 right">'.$this->formatNum($triggers[$prev[1]]['principal']).'</div>
										<div class="floatLeft width90 right">'.$this->formatNum($triggers[$prev[1]]['interest']).'</div>
										<div class="clear"></div>
									</div>
									<div class="toggle_container">';
									
						}
						
						$content .= '
								<div class="'.$rowColor.'paddingVert2 paddingHort5 prepend-top5'.$highlight.'">
									<div class="floatLeft width30">'.$group[$key]['date'].'</div>
									<div class="floatLeft width85 right">'.$this->formatNum($group[$key]['balance']).$alter.'</div>
									<div class="floatLeft width90 right">'.$this->formatNum($group[$key]['principal']).'</div>
									<div class="floatLeft width90 right">'.$this->formatNum($group[$key]['interest']).'</div>
									<div class="clear"></div>
								</div>
								<div class="clear"></div>';
						
						$year = $prev[1];
						
						if ($count == ($countGroup - 1))
						{
							
							$content .= '</div>';
						
						}
							
					
					}
					
					$count++;
					
				}
			
			}
			else
			{
			
				foreach ($group as $key => $void)
				{
				
					$rowColor = ($count % 2) ? 'blueBg ' : '';
					
					if (!empty($group[$key]))
					{
										
						$highlight = ($group[$key]['highlight'] > 0)?' blue bold':'';
						
						if ($this->groupByDate == true && $this->currentPrincipal > $group[$key]['balance'])
						{
					
							$split = explode('/',$group[$key]['date']);
						
							$alter	= ($split[1] == $year) ? '**' : '';
						
						
						}
						else
						{
						
							if ($this->currentPrincipal < $group[$key]['balance'])
							{
							
								$alter	= ($group[$key]['date'] == $changeDate) ? '**' : '';
								
							}
						
						}
						
						$content .= '
								<div class="'.$rowColor.'paddingVert2 paddingHort5 prepend-top5'.$highlight.'">
									<div class="floatLeft width30">'.$group[$key]['date'].'</div>
									<div class="floatLeft width85 right">'.$this->formatNum($group[$key]['balance']).$alter.'</div>
									<div class="floatLeft width90 right">'.$this->formatNum($group[$key]['principal']).'</div>
									<div class="floatLeft width90 right">'.$this->formatNum($group[$key]['interest']).'</div>
									<div class="clear"></div>
								</div>
								<div class="clear"></div>';
							
					}
					
					$count++;
					
				}
			
			
			
			}
		
			echo $content;
		
		}	
	
	}# END buildChart
	
	
	function groupByYear($results)
	{
			
		# GROUP BY YEAR
		foreach ($results as $key => $void)
		{
		
			$year = end(explode('/',$results[$key]['date']));
			
			$highlight = ($results[$key]['highlight'] == true && $this->type == 'accel') ? 1 :  0;
			
			$group[$year]['date']       = $results[$key]['date'];# SET DATE
			$group[$year]['interest']  += $results[$key]['interest'];# SET INTEREST AMOUNT
			$group[$year]['principal'] += $results[$key]['principal'];# SET PAID PRINCIPAL AMOUNT
			$group[$year]['balance']    = $results[$key]['balance'];# SET REMIANING BALANCE
			$group[$year]['extra']      = $results[$key]['extra'];# SET REMIANING BALANCE
			$group[$year]['highlight'] += $highlight;# SET REMIANING BALANCE
		
		}
		
		return $group;
	
	}# END groupByYear
	
	

	function searchArray($array, $searchKey, $searchValue)
	{
	
		$results = array();
	
		if (is_array($array))
		{
		
			if ($array[$searchKey] == $searchValue)
			{
			
				$results[] = $array;
			
			}
			
			foreach ($array as $subarray)
			{
			
				$results = array_merge($results, $this->searchArray($subarray, $searchKey, $searchValue));
			
			}
			
		}
		
	
		return $results;
		
	}# END searchBorrow
	
	
	
	function removeMonth($date)
	{
	
		$array = explode('/',$date);
		
		return $array[1];
	
	}# END removeYear
	
	
	
	function getDate($year, $month)
	{
	
		# MONTH ARRAY
		$month_names = array(1=>1,2,3,4,5,6,7,8,9,10,11,12);
		
		# SET YEAR
		$year  = $year + intval($month / 12);
		
		# SET MONTH
		$month = $month % 12;
		
		# CHECK IF MONTH IS EQUAL TO 0 IF TRUE SET IT TO THE LAST MONTH
		if($month == 0)
		{
		
			$month = 12;
			$year -= 1;
			
		}
		
		# RETURN CONTENT
		return str_replace(array('M','Y'), array($month_names[$month], $year), 'M/Y');
		
	}# END getDate
	
	
	
	function formatNum($num)
	{
	
		# CHECK IF VALUE IS NUMERIC	
		if (is_numeric($num))
		{
			
			#ADD FORMAT ELEMENTS
			$num = $this->currency_char.number_format($num, $this->decimals, $this->dec_point, $this->thousands_sep);
		
		}
		else
		{
		
			# NOT NUMERIC JUST SEND IT BACK
			$num = $num;
			
		}
		
		# RETURN CONTENT
		return $num;
	
	}
	
	
	
	function getActuals()
	{
	
		global $future;
	
		$results = ($this->startOver == true) ? $this->getNewAmortization() : $this->getAmortization();
		
		foreach ($results as $key => $void)
		{
		
			$interestSum[]  = $results[$key]['interest'];
			$totalPaid[]    = abs($results[$key]['principal']);
		
		}
		
		$date = end($results);
		
		$split = explode('/',$date['date']);
		
		$endTime = $future->getTimestamp($split[0].'/1/'.$split[1]);
		$nowTime = $future->getTimestamp(date('n/j/Y'));
		
		$dif = ($endTime - $nowTime)/(60*60*24);
		
		$term = $dif/365.25;
		
		$array['endDate']   = $split[0].'/1/'.$split[1];
		$array['payoff']   = $term;
		$array['interest'] = array_sum($interestSum);
		$array['totalPaid'] = array_sum($totalPaid);
		
		return $array;
	
	}# END getActuals
	
	
	
	function getInterestSaved($standard = array(), $accel = array(), $heloc = array())
	{
	
		$stanInt  = (!empty($standard)) ? $standard['interest']   : 0;
		$actInt   = (!empty($accel))    ? $accel['interest']      : 0;
		$helocInt = (!empty($heloc))    ? $heloc['interestTotal'] : 0;
		
		
		$stanPO  = (!empty($standard)) ? $standard['payoff']   : 0;
		$actPO   = (!empty($accel))    ? $accel['payoff']      : 0;
	
		$this->interestSaved = $this->formatNum($stanInt - $actInt - $helocInt);
		$length = number_format($stanPO - $actPO,1);
		$this->payoffLength = ($length > 0) ? $length : 0;
		$this->helocInterest = $this->formatNum($helocInt);
		$this->accelInterest = $this->formatNum($stanInt - $actInt);
		
	
	}# END getInterestSaved
	
	
	function getStatus()
	{
	
		$this->getAmortization;
		
		$status = ($this->startOver == true)?true:false;
		
		return $status;
	
	}# END getStatus
	
	
	function getPayment()
	{
	
		$periods     = $this->term * 12;
		$loanPayment = $this->principal * $this->interestRate / 100 / 12 / (1 - pow((1 + $this->interestRate / 12 / 100), -$periods));# CALCULATE PAYMENT
		
		return number_format($loanPayment,2);
	
	}# END getStatus
	
	
}
?>