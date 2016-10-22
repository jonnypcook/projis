<?php

namespace Project\Service;

use Project\Entity\Project as Project;

class Model 
{
    function payback(Project $project, $years=12, array $args = array()) {
        //calculate funding options
        $financing = false;
        if (!empty($project->getClient()->getFinanceStatus()) && ($project->getClient()->getFinanceStatus()->getFinanceStatusId()>1)) {
            if (($project->getFinanceYears()->getFinanceYearsId() >0 )) {
                if (!empty($project->getFinanceProvider())){
                    $financing = true;
                }
            }
        } 
        
        $em = $this->getEntityManager();
        $qb = $em->createQueryBuilder();
        
        $forecast = array();
		$overview = array();
		$totals = array();
        
        $dql = 'SELECT COUNT(s) FROM Space\Entity\System s JOIN s.space sp JOIN s.product p JOIN p.type pt WHERE sp.project=:pid AND pt.service=false AND p.eca=true';
        $q = $em->createQuery($dql);
        $q->setParameters(array('pid' => $project->getProjectId()));

        $ecaCompatibile = ($q->getSingleScalarResult()>0);
        
        $qb
            ->select('s.label, s.cpu, s.ppu, s.ippu, s.quantity, s.hours, s.legacyWatts, s.legacyQuantity, s.legacyMcpu, s.lux, s.occupancy, s.locked, s.systemId, s.attributes, '
                    . 'sp.spaceId, sp.name, '
                    . 'b.name, b.buildingId,'
                    . 'ba.postcode,'
                    . 'p.model, p.pwr, p.eca, p.description, p.productId, p.ibppu, p.mcd,'
                    . 'pt.typeId AS productType, pt.service')
            ->from('Space\Entity\System', 's')
            ->join('s.space', 'sp')
            ->leftjoin('sp.building', 'b')
            ->leftjoin('b.address', 'ba')
            ->join('s.product', 'p')
            ->join('p.brand', 'pb')
            ->join('p.type', 'pt')
            ->where('sp.project=?1')
            ->setParameter(1, $project->getProjectId())
            ->add('orderBy', 's.space ASC');
        
        if (!empty($args['spaceId'])) {
            $qb
                ->andWhere('sp.spaceId=?2')
                ->setParameter(2, $args['spaceId']);
        }

        
        $query  = $qb->getQuery();      
        $result = $query->getResult(\Doctrine\ORM\AbstractQuery::HYDRATE_ARRAY);

        $totals = array (
            'legacyMaintenance' => 0,
			'currentElecConsumption' => 0,
			'ledElecConsumption' => 0,
			'co2emmissionreduction' => 0,
			'elec_sav_ach' => 0,
            'productcost' => 0,
            'productcost_base' => 0,
			'price_base' => 0,
			'price' => 0,
			'priceeca' => 0,
            'IBP' => 0,
            'prelim' => 0,
            'overhead' => 0,
            'management' => 0,
            'fee' => 0,
            
            'price_installation' => 0,
            'price_delivery' => 0,
            'price_product' => 0,
            'price_service' => 0,
            'price_access' => 0,
            
            'kwhSave' => 0,
        );
        
        
        
        $discount = $project->getMcd();
        
        $spaces = array();
        foreach ($result as $obj) {
            if (empty($spaces[$obj['spaceId']])) {
                $spaces[$obj['spaceId']] = true;
            }
            
            $led = (($obj['productType'] == 1)||($obj['productType'] == 3)); // type 1 is an LED
            $product = ($obj['service'] == 0);
            $installation = ($obj['productType'] == 100); // type 100 is an installation product
            $delivery = ($obj['productType'] == 101); // type 101 is a delivery product
            $service = ($obj['productType'] == 102); // type 103 is an additional service
            $access = ($obj['productType'] == 103); // type 102 is an access product
            
            // calculate price 
            $priceIncDiscount = round($obj['ppu'] * (1-($discount * $obj['mcd'])),2);
            $price = round(($obj['quantity'] * $priceIncDiscount),2);
            
            if ($product && $project->getIbp()) {
                $totals['IBP']+=round($price * 0.018, 2);
                //$totals['IBP']+=($obj['ibppu'] * $obj['quantity']);
            }
            
            // calculate power savings (if applicable)
            if ($installation) {
                $totals['price_installation']+=$price;
                /*if (!empty($obj['eca'])) {
                    $totals['priceeca']+=$price;
                }/**/
            } elseif ($delivery) {
                $totals['price_delivery']+=$price;
            } elseif ($service) {
                $totals['price_service']+=$price;
            } elseif ($access) {
                $totals['price_access']+=$price;
            } else {
                $pwrSaveLeg = ($obj['legacyWatts']*$obj['legacyQuantity']);
                if ($obj['productType']==3) {
                    $attr = json_decode($obj['attributes']);
                    $ratio = (($attr->dLen * $attr->dUnits)/1000)/$obj['quantity'];
                    $pwrSaveLed = round(($obj['quantity']*$ratio*$obj['pwr']) * (1-($obj['lux']/100)) * (1 - ($obj['occupancy']/100)),0);
                } else {
                    $pwrSaveLed = ($obj['quantity']*$obj['pwr']) * (1-($obj['lux']/100)) * (1 - ($obj['occupancy']/100));
                }
                
                $pwrSave = (!$led||(($obj['legacyWatts'] * $obj['legacyQuantity'])==0))?0:((($pwrSaveLeg-$pwrSaveLed)/($obj['legacyWatts'] * $obj['legacyQuantity'])) * 100);
                $kwHSave = (!$led||(($obj['legacyWatts'] * $obj['legacyQuantity'])==0))?0:((($pwrSaveLeg-$pwrSaveLed)/1000) * $obj['hours'] * 52);

                $currentElecConsumption = round((($obj['legacyQuantity'] * $obj['hours'] * $obj['legacyWatts'] * 52)/1000) * $project->getFuelTariff(),2);
                $ledElecConsumption = round(((100-$pwrSave)/100) * $currentElecConsumption,2);
                $elec_sav_ach = round($currentElecConsumption - $ledElecConsumption, 2);
            
                // calculate co2 savings
                $co2emmissionreduction = round((($elec_sav_ach / $project->getFuelTariff()) * $project->getCo2()) / 1000,2);

                // calculate maintenance cost
                $legacyMaintenance = round($obj['legacyQuantity'] * $obj['legacyMcpu'],2);
                
                // shift totals as per iteration
                $totals['elec_sav_ach']+=$elec_sav_ach;
                $totals['currentElecConsumption']+=$currentElecConsumption;
                $totals['ledElecConsumption']+=$ledElecConsumption;
                $totals['co2emmissionreduction']+= $co2emmissionreduction;
                $totals['legacyMaintenance']+=$legacyMaintenance;
                /*if (!empty($obj['eca'])) {
                    $totals['priceeca']+=$price;
                }/**/
                $totals['price_product']+=$price;
                $totals['productcost']+=($obj['cpu'] * $obj['quantity']);
                $totals['kwhSave']+=$kwHSave;
            }
            
            if ($ecaCompatibile) {
                if (!empty($obj['eca'])) {
                    $totals['priceeca']+=$price;
                }
            } 
            
            

            
            // shift totals as per iteration
            $totals['price']+=$price;
        }
        
        //echo '<pre>', print_r($totals, true), '</pre>'; die();
        
        // adjust legacy maintenance if required
        if($project->getMaintenance()>0) {
            $totals['legacyMaintenance'] = $project->getMaintenance();
        }

        $csav = 0;
        $carbon = 0;
        
        // work out additional fee (if applicable)
        $totals['prelim'] = round($totals['price'] * $project->getFactorPrelim(),2);
        $totals['overhead'] = round(($totals['price'] + $totals['prelim']) * $project->getFactorOverhead(),2);
        $totals['management'] = round(($totals['price'] + $totals['prelim'] + $totals['overhead']) * $project->getFactorManagement(),2);
        $totals['fee'] = round($totals['prelim'] + $totals['overhead'] + $totals['management'],2);

        // total cost
        $total_cost = round($totals['price'] + $totals['fee'] + $totals['IBP'],2);
        
        // cost of financing
        $financing_unsupported=false;
        if ($financing) {
            $finance_data = array();
            $finance_data['amount'] = $total_cost;
            
            $qb2 = $em->createQueryBuilder();
            $qb2
                ->select('f ')
                ->from('Project\Entity\Finance', 'f')
                ->where('f.financeStatus=?1 AND f.financeYears=?2 AND f.min <= ?3 AND f.max >= ?3')
                ->setParameter(1, $project->getClient()->getfinanceStatus()->getFinanceStatusId())
                ->setParameter(2, $project->getFinanceYears()->getFinanceYearsId())
                ->setParameter(3, $total_cost);


            $query  = $qb2->getQuery();      
            $result = $query->getResult(\Doctrine\ORM\AbstractQuery::HYDRATE_ARRAY);
            
            if (empty($result)) {
                $financing = false;
                $financing_unsupported = true;
            } else {
                $ff = array_shift($result);
                $finance_data['repayments'] = round($finance_data['amount'] * (float)$ff['factor'] * 4,0);
                $finance_data['annualrate'] = round((((($finance_data['repayments'] *  $project->getFinanceYears()->getFinanceYearsId()) - $finance_data['amount'])/$finance_data['amount']) / $project->getFinanceYears()->getFinanceYearsId())*100,2);
            }
        }
        
        // calculate eca saving & carbon allowance
        $eca = $totals['priceeca'] * $project->getEca(); // new method based on individual light eca compatibility
        $callow = $project->getCarbon();
        
        $payback = $financing?0:-($totals['price'] + $totals['fee'] + $totals['IBP']);
        $payback_eca = $financing?$eca:-($totals['price'] + $totals['fee'] + $totals['IBP'] - $eca);

        $finance_avg_benefit = 0;
        $finance_avg_repay = 0;
        $finance_avg_netbenefit = 0;
        
        $payback_year = 0;
        $ledMaintenaceOn = (($project->getMaintenanceLed()>0) && ($project->getMaintenanceLedYear()>0));
        for($i=1; $i<=$years; $i++) {
            $legsp = ($totals['currentElecConsumption'] * pow(1 + $project->getEpi(),$i-1));
            $ledsp = ($totals['ledElecConsumption'] * pow(1 + $project->getEpi(),$i-1));
            $cam = ($totals['legacyMaintenance'] * pow(1+$project->getRpi(),$i-1));
            $led_maintenance = ($ledMaintenaceOn?(($project->getMaintenanceLedYear()<=$i)?round($project->getMaintenanceLed() * pow(1+$project->getRpi(),$i-1),2):0):0);
            $carbon+= $totals['co2emmissionreduction'];

            $cost_of_financing = ($financing)?(($project->getFinanceYears()->getFinanceYearsId() >= $i)?$finance_data['repayments']:0):0;
            $cash_benefit = round(($cam + ($legsp-$ledsp)) - $cost_of_financing - $led_maintenance,2);

            $csav+=round($cam + ($legsp-$ledsp) - $led_maintenance,2);

            $payback+=($cam + ($legsp-$ledsp) - $led_maintenance);

            $payback_eca+=($cam + ($legsp-$ledsp) - $led_maintenance + ($callow * $totals['co2emmissionreduction']));

            if ($project->getFinanceYears()->getFinanceYearsId() >= $i) {
                $finance_avg_benefit+=round($cam + ($legsp-$ledsp) - $led_maintenance,2);
                $finance_avg_repay+=$cost_of_financing;
                $finance_avg_netbenefit+=$cash_benefit;
            }

            
            $payback-=$cost_of_financing;
            $payback_eca-=$cost_of_financing;

            $forecast[$i] = array (
                round($legsp,2),
                round($ledsp,2),
                round($legsp-$ledsp,2), // electricity saving 
                round($cam,2),
                round($cam + ($legsp-$ledsp) - $led_maintenance,2),
                round($csav,2),
                round(($legsp-$ledsp)/12,2),
                round($totals['co2emmissionreduction'],2),
                round($payback,2),
                round($payback_eca,2),
                round(($callow * $totals['co2emmissionreduction']),2),
                $cost_of_financing,
                $cash_benefit,
                $led_maintenance,
            );

            if (($payback_eca>0) && empty($payback_year)) {
                $payback_year = $i;
            }
        }
        
        $carballow = ($callow * $carbon);
			

        $figures = array(
            'saving' => round($csav,2),
            'cost_maintenance' => round($totals['legacyMaintenance'],2),
            'cost_install' => $totals['price_installation'], 
            'cost_delivery' => round($totals['price_delivery'],2), 
            'cost_led' => round($totals['price_product'],2), // TO DO
            'margin' => ($totals['price_product']>0)?round((1-($totals['productcost']/$totals['price_product']))*100,2):0,
            'cost' => $total_cost,
            'costvat' => round(($total_cost * 1.2),2),
            'costvateca' => round(($total_cost * 1.2)-$eca,2),
            'vat'=> round(($total_cost * 0.2),2),
            'costeca' => round($total_cost-$eca,2),
            'cost_prelim' => $totals['prelim'],
            'cost_overheads' => $totals['overhead'],
            'cost_management' => $totals['management'],// $total_fee,
            'cost_access' => $totals['price_access'],
            'cost_service' => $totals['price_service'],
            'cost_ibp'=>$totals['IBP'],
            'profit' => round($payback,2),
            'profiteca' => round($payback_eca,2),
            'carbon' => round($carbon,2),
            'eca' => round($eca,2),
            'eca_eligible' => round($totals['priceeca'],2),
            'eca_ineligible' => round($totals['price']-$totals['priceeca'],2),
            'carbonallowance' => $carballow,
            'finance_amount' => ($financing?($finance_data['repayments'] * $project->getFinanceYears()->getFinanceYearsId()):0),
            'finance_years' => ($financing?$project->getFinanceYears()->getFinanceYearsId():0),
            'finance_annual_repayment' => ($financing?$finance_data['repayments']:0),
            'finance_annual_rate' => ($financing?$finance_data['annualrate']:0),
            'finance_avg_benefit' => ($financing?round($finance_avg_benefit/$project->getFinanceYears()->getFinanceYearsId(),2):0),
            'finance_avg_repay' => ($financing?round($finance_avg_repay/$project->getFinanceYears()->getFinanceYearsId(),2):0),
            'finance_avg_netbenefit' => ($financing?round($finance_avg_netbenefit/$project->getFinanceYears()->getFinanceYearsId(),2):0),
            'finance_netbenefit' => ($financing?round($finance_avg_netbenefit,2):0),
            'space_count' => count($spaces),
            'payback_year' => $payback_year,
            'kwhYear' => $totals['kwhSave'],
            'ecacompatible'=>$ecaCompatibile,
        );

        if ($financing) {
            $figures['finance_exceeds'] = (($project->getClient()->getFund() < $total_cost)?1:0);
        }

        if ($financing_unsupported) {
            $figures['finance_unsupported'] = 1;
        }
        /**/
        
        return array (
            'figures' => $figures,
            'forecast' => $forecast
        );
    }
    
    
    /**
     * calculate space performance
     * @param \Project\Entity\Project $project
     * @param array $args
     * @return type
     */
    function spaceBreakdown(Project $project, array $args = array()) {
        $em = $this->getEntityManager();
        $qb = $em->createQueryBuilder();
        
		$breakdown = array();
        
        
        $qb
            ->select('s.label, s.cpu, s.ppu, s.ippu, s.quantity, s.hours, s.legacyWatts, s.legacyQuantity, s.legacyMcpu, s.lux, s.occupancy, s.locked, s.systemId, s.attributes, '
                    . 'sp.spaceId, sp.name AS sName, sp.root,'
                    . 'b.name AS bName, b.buildingId,'
                    . 'ba.postcode,'
                    . 'p.model, p.pwr, p.eca, p.description, p.productId, p.ibppu, p.mcd,'
                    . 'pt.typeId AS productType, '
                    . 'l.legacyId, l.description as legacyDescription '
                    )
            ->from('Space\Entity\System', 's')
            ->join('s.space', 'sp')
            ->leftjoin('sp.building', 'b')
            ->leftjoin('b.address', 'ba')
            ->join('s.product', 'p')
            ->join('p.brand', 'pb')
            ->join('p.type', 'pt')
            ->leftJoin('s.legacy', 'l')
            ->where('sp.project=?1')
            ->setParameter(1, $project->getProjectId())
            ->add('orderBy', 's.space ASC');

        if (!empty($args['spaceId'])) {
            $qb
                ->andWhere('sp.spaceId=?2')
                ->setParameter(2, $args['spaceId']);
        }
        
        if (!empty($args['products'])) {
            $qb->andWhere('pt.service = 0');
        }
        
        $query  = $qb->getQuery();      
        $result = $query->getResult(\Doctrine\ORM\AbstractQuery::HYDRATE_ARRAY);
        
        
        $discount = $project->getMcd();
        
        foreach ($result as $obj) {
            $led = (($obj['productType'] == 1) || ($obj['productType'] == 3)); // type 1 is an LED
            $installation = ($obj['productType'] == 100); // type 100 is an installation product
            $delivery = ($obj['productType'] == 101); // type 101 is a delivery product
            $service = ($obj['productType'] == 102); // type 102 is a service product
            $access = ($obj['productType'] == 103); // type 103 is an access product
            
            if (empty($obj['buildingId'])) {
                $obj['buildingId'] = 0;
            }
            
            if (!isset($breakdown[$obj['buildingId']])) {
                $breakdown [$obj['buildingId']] = array (
                    'name' => $obj['bName'],
                    'postcode' => $obj['postcode'],
                    'spaces' => array ()
                );
            }
            
            if (!isset($breakdown[$obj['buildingId']] ['spaces'] [$obj['spaceId']])) {
                $breakdown [$obj['buildingId']] ['spaces'] [$obj['spaceId']] = array (
                    'name' => $obj['sName'],
                    'root' => !empty($obj['root']),
                    'products' => array ()
                );
            }
            
            
            // calculate price
            $priceIncDiscount = round($obj['ppu'] * (1-($discount * $obj['mcd'])),2);
            $price = round(($obj['quantity'] * $priceIncDiscount),2);
            
            
            // calculate power savings (if applicable)
            if ($installation || $delivery || $access || $service) {
                $breakdown[$obj['buildingId']] ['spaces'] [$obj['spaceId']] ['products'][$obj['systemId']] = array(
					$price,
                    $price,
                    $obj['productType'],
                    $obj['productId'],
                    $obj['model'],
                    $obj['quantity'],
                    0,
                    0,
                    null,
                    null,
                    null,
					0,
					0,
					0,
					0,
                    0,
                    null,
                    null,
                    null,
				);/**/
            } else {
                $pwrSaveLeg = ($obj['legacyWatts']*$obj['legacyQuantity']);
                if ($obj['productType']==3) {
                    $attr = json_decode($obj['attributes']);
                    $ratio = (($attr->dLen * $attr->dUnits)/1000)/$obj['quantity'];
                    $pwrSaveLed = round(($obj['quantity']*$ratio*$obj['pwr']) * (1-($obj['lux']/100)) * (1 - ($obj['occupancy']/100)),0);
                } else {
                    $pwrSaveLed = ($obj['quantity']*$obj['pwr']) * (1-($obj['lux']/100)) * (1 - ($obj['occupancy']/100));
                }
                
                
                $pwrSave = (!$led||($obj['legacyWatts']==0))?0:((($pwrSaveLeg-$pwrSaveLed)/($obj['legacyWatts'] * $obj['legacyQuantity'])) * 100);
                $kwHSave = (!$led||($obj['legacyWatts']==0))?0:((($pwrSaveLeg-$pwrSaveLed)/1000) * $obj['hours'] * 52);

                $currentElecConsumption = round((($obj['legacyQuantity'] * $obj['hours'] * $obj['legacyWatts'] * 52)/1000) * $project->getFuelTariff(),2);
                $ledElecConsumption = round(((100-$pwrSave)/100) * $currentElecConsumption,2);
                $elec_sav_ach = round($currentElecConsumption - $ledElecConsumption, 2);
            
                // calculate co2 savings
                $co2emmissionreduction = round((($elec_sav_ach / $project->getFuelTariff()) * $project->getCo2()) / 1000,2);

                // calculate maintenance cost
                $legacyMaintenance = round($obj['legacyQuantity'] * $obj['legacyMcpu'],2);
                
                // add line data
                $breakdown[$obj['buildingId']] ['spaces'] [$obj['spaceId']] ['products'] [$obj['systemId']] = array(
					$priceIncDiscount,
                    $price,
                    $obj['productType'],
                    $obj['productId'],
                    $obj['model'],
                    $obj['quantity'],
                    $obj['hours'],
                    $obj['pwr'],
                    $obj['description'],
                    $obj['legacyQuantity'],
                    $obj['legacyWatts'],
					$legacyMaintenance,
					round($pwrSave,2),
					$elec_sav_ach,
					$co2emmissionreduction,
                    $kwHSave,
                    $obj['attributes'],
                    $obj['label'],
                    $obj['legacyDescription'],
				);/**/
            }
            
        }

        //echo '<pre>', print_r($breakdown, true), '</pre>';die();
        
        /**/
        
        return $breakdown;
    }
    
    function billitems(Project $project, array $args = array()) {
        $em = $this->getEntityManager();
        //$qb = $em->createQueryBuilder();
        $discount = ($project->getMcd());
        
        $query = $em->createQuery('SELECT p.productId, p.model, p.description, p.eca, pt.service, pt.name AS productType, pt.typeId, pt.service, s.ppu, s.attributes, s.label, '
                . 'SUM(s.quantity) AS quantity, '
                . 'SUM(s.ppu*s.quantity) AS price, '
                . 'SUM(ROUND((s.ppu *  (1 - ('.$discount.' * p.mcd))),2) * s.quantity) AS priceMCD '
                . 'FROM Space\Entity\System s '
                . 'JOIN s.space sp '
                . 'JOIN s.product p '
                . 'JOIN p.type pt '
                . 'WHERE sp.project='.$project->getProjectId().' '
                . ((!empty($args['products']))?'AND pt.service = 0 ':'')
                . 'GROUP BY s.product, s.ppu');
        
        
        return $query->getResult(\Doctrine\ORM\AbstractQuery::HYDRATE_ARRAY);
    }
    
    /**
     * calculate trial breakdown set
     * @param \Project\Entity\Project $project
     * @param array $args
     * @return type
     */
    function trialBreakdown(Project $project, array $args = array()) {
        $em = $this->getEntityManager();
        $qb = $em->createQueryBuilder();
        
		$breakdown = array();
        
        
        $qb
            ->select('s.label, s.quantity, s.hours, s.legacyWatts, s.legacyQuantity, s.legacyMcpu, s.lux, s.occupancy, s.systemId, '
                    . 'sp.spaceId, sp.name AS sName, sp.root,'
                    . 'b.name AS bName, b.buildingId,'
                    . 'ba.postcode,'
                    . 'pt.typeId AS productType, '
                    . 'p.model, p.pwr, p.eca, p.description, p.productId, p.ppu, p.ppuTrial, '
                    . 'l.legacyId, l.description '
                    )
            ->from('Space\Entity\System', 's')
            ->join('s.space', 'sp')
            ->leftjoin('sp.building', 'b')
            ->leftjoin('b.address', 'ba')
            ->join('s.product', 'p')
            ->join('p.brand', 'pb')
            ->join('p.type', 'pt')
            ->leftJoin('s.legacy', 'l')
            ->where('sp.project=?1')
            ->andWhere('pt.service=0')
            ->setParameter(1, $project->getProjectId())
            ->add('orderBy', 's.space ASC');

        
        $query  = $qb->getQuery();      
        $result = $query->getResult(\Doctrine\ORM\AbstractQuery::HYDRATE_ARRAY);
        
        $discount = $project->getMcd();
        
        foreach ($result as $obj) {
            $led = ($obj['productType'] == 1);
            
            if (empty($obj['buildingId'])) {
                $obj['buildingId'] = 0;
            }
            
            if (!isset($breakdown[$obj['buildingId']])) {
                $breakdown [$obj['buildingId']] = array (
                    'name' => $obj['bName'],
                    'postcode' => $obj['postcode'],
                    'spaces' => array ()
                );
            }
            
            if (!isset($breakdown[$obj['buildingId']] ['spaces'] [$obj['spaceId']])) {
                $breakdown [$obj['buildingId']] ['spaces'] [$obj['spaceId']] = array (
                    'name' => $obj['sName'],
                    'root' => !empty($obj['root']),
                    'products' => array ()
                );
            }
            
            
            // calculate price
            $rrp = round($obj['ppu'],2);
            $price = round(($obj['quantity'] * $rrp),2);
            
            
            // calculate power savings (on a per fitting basis)
            $pwrSaveLeg = ($obj['legacyWatts']);
            $pwrSaveLed = ($obj['pwr']) * (1-($obj['lux']/100)) * (1 - ($obj['occupancy']/100));
            $pwrSave = (!$led||($obj['legacyWatts']==0))?0:((($obj['legacyWatts']-$pwrSaveLed)/($obj['legacyWatts'])) * 100);
            $kwHSave = (!$led||($obj['legacyWatts']==0))?0:((($obj['legacyWatts']-$pwrSaveLed)/1000) * $obj['hours'] * 52);


            // add line data
            $breakdown[$obj['buildingId']] ['spaces'] [$obj['spaceId']] ['products'] [$obj['systemId']] = array(
                $rrp,
                $obj['ppuTrial'],
                $price,
                $obj['model'],
                $obj['quantity'],
                $obj['description'],
                $obj['legacyQuantity'],
                $obj['legacyWatts'],
                $obj['productId'],
                $obj['productType'],
                $obj['hours'],
                round($pwrSave,2),
                $kwHSave,
            );/**/
            
        }

        //echo '<pre>', print_r($breakdown, true), '</pre>';die();
        
        /**/
        
        return $breakdown;
    }
    
    
    protected $_configs;
    protected $_maximum;
    
    const BOARDLEN_A = 288.25;
    const BOARDLEN_B = 286.75;
    const BOARDLEN_B1 = 104.60;
    const BOARDLEN_C = 288.35;
    const BOARDLEN_GAP = 1;
    const BOARDLEN_ALUM = 2; // changed from 2
    const BOARDLEN_EC = 4; // changed from 2
    
    function getPickListItems($attributes, array &$boards, array &$architectural, array &$phosphor, array &$aluminium) {
        //echo '<pre>',print_r($attributes['dConf'], true), '</pre>';
        $multiplier = empty($attributes['dUnits'])?1:$attributes['dUnits'];
        
        foreach ($attributes['dConf'] as $confId=>$aConfigs) {
            $size = count($aConfigs);
            $current = 0;
            foreach ($aConfigs as $aConfig=>$aQty) {
                $current++;
                $rpLen = 0;
                $lastString = ($current==$size);
                if ($lastString) { // last item
                    $architectural['_ECT'][3]+=(1 * $multiplier);
                    $architectural['_EC'][3]+=(((2*$aQty)-1)*$multiplier);
                } else {
                    $architectural['_EC'][3]+=((2*$aQty)*$multiplier);
                }
                
                $brdBd = explode('-', $aConfig);

                //$rpLen+=self::BOARDLEN_EC*2;
                $rpLen+=self::BOARDLEN_GAP*(count($brdBd)-1);
                $rpLen+=self::BOARDLEN_ALUM*2;
                
                foreach ($brdBd as $brd) {
                    $rpLen+=constant('self::BOARDLEN_'.$brd);

                    if ($brd=='A') {
                        $architectural['_CBL'][3]+=($aQty * $multiplier);
                        $architectural['_WG'][3]+=((2*$aQty) * $multiplier);
                    } elseif ($brd=='C') {
                        $architectural['_CBL'][3]+=($aQty * $multiplier);
                        if ($lastString) {
                            $architectural['_CBL'][3]-=(1 * $multiplier);
                        } 
                    }
                    //$architectural['_'.$brd][3]+=$aQty;
                    $boards['_'.$brd][3]+=($aQty * $multiplier);
                }
                
                $aluLen = $rpLen;
                $rpLen--;
                if (empty($phosphor["{$rpLen}"][$aConfig])) {
                    $phosphor["{$rpLen}"][$aConfig][0] = 0;
                    $phosphor["{$rpLen}"][$aConfig][1] = 0;
                    $aluminium["{$aluLen}"][$aConfig] = 0;
                }
                
                
                $phosphor["{$rpLen}"][$aConfig][0]+=(($lastString)?(($aQty-1) * $multiplier):($aQty * $multiplier));
                $phosphor["{$rpLen}"][$aConfig][1]+=(($lastString)?(1 * $multiplier):0);
                $aluminium["{$aluLen}"][$aConfig]+=($aQty * $multiplier);
                
            }
        }
        
    }
    
    
    function getBuildsheetItems($attributes, $model, array &$build, array &$buildConfig) {
        
        $multiplier = empty($attributes['dUnits'])?1:$attributes['dUnits'];
        foreach ($attributes['dConf'] as $confId=>$aConfigs) {
            $size = count($aConfigs);
            $current = 0;
            foreach ($aConfigs as $aConfig=>$aQty) {
                $addConfig = empty($buildConfig[$aConfig]);

                $current++;
                $rpLen = 0;
                $lastString = ($current==$size);
                
                $brdBd = explode('-', $aConfig);

                if ($addConfig) {
                    $buildConfig[$aConfig] = array(
                        '_A'=>0,
                        '_B'=>0,
                        '_B1'=>0,
                        '_C'=>0,
                        '_CBL'=>0,
                        '_WG'=>0,
                        'LEN'=>(self::BOARDLEN_ALUM*2) + self::BOARDLEN_GAP*(count($brdBd)-1) - 1 // here the -1 is for phosphor reduction
                    );

                    foreach ($brdBd as $brd) {
                        $buildConfig[$aConfig]['LEN']+=constant('self::BOARDLEN_'.$brd);
                        $buildConfig[$aConfig]['_'.$brd]++;
                        if ($brd=='A') {
                            $buildConfig[$aConfig]['_CBL']+=1;
                            $buildConfig[$aConfig]['_WG']+=2;
                        } elseif ($brd=='C') {
                            $buildConfig[$aConfig]['_CBL']+=1;
                        }
                    }
                }
                
                $rpLen = $buildConfig[$aConfig]['LEN'];
                if (empty($build[$model]["{$rpLen}"][$aConfig])) {
                    $build[$model]["{$rpLen}"][$aConfig][0] = 0;
                    $build[$model]["{$rpLen}"][$aConfig][1] = 0;
                }
                
                
                $build[$model]["{$rpLen}"][$aConfig][0]+=(($lastString)?(($aQty-1)*$multiplier):($aQty*$multiplier));
                $build[$model]["{$rpLen}"][$aConfig][1]+=(($lastString)?(1*$multiplier):0);
                
            }
        }

        //echo '<pre>',print_r($build, true), '</pre>';die();
    }
    
    static function cmp($a, $b) {
        $aF = (float)$a;
        $bF = (float)$b;
        //echo print_r($a, true).'>'.print_r($b, true).'<br />';
        if ($aF == $bF) {
            return 0;
        }
        return ($aF < $bF) ? -1 : 1;
    }
    
    
    function findOptimumArchitectural(\Product\Entity\Product $product, $length, $mode, array $args=array()) {
        try {
            $alternativeConfigs = !empty($args['alts']);
            $alternativeThreshold = $length-self::BOARDLEN_B1;
            
            $data = array(
                'dLen'=>0,
                'dUnits'=>1,
                'dBill'=>0,
                'dBillU'=>0,
                'dBillTU'=>0,
                'dCost'=>0,
                'dConf'=>0,
            );
            
            if (!empty($args['units'])) {
                if (preg_match('/^[\d]+$/',$args['units'])) {
                    $args['units'] = (int)$args['units'];
                    if ($args['units']>0) {
                        $data['dUnits'] = $args['units'];
                    }
                }
            }
            
            $curLen = 0;
            $RemotePhosphorMax = 1800; // this is a moveable target- NEED TO CLARIFY
            $maxunitlength = !empty($args['maxunitlen'])?$args['maxunitlen']:5000;  // this is a moveable target- NEED TO CLARIFY
            $fplRange = 50; // fewest phosphor lengths range
            
            // find architectural details
            $attributes = $product->getAttributes();
            if (!empty($attributes)) {
                $attributes = json_decode($attributes, true);
                if (!empty($attributes['arch']['phmax'])) {
                    $RemotePhosphorMax = (int)$attributes['arch']['phmax'];
                    if (($RemotePhosphorMax<1000) || ($RemotePhosphorMax>3000)) {
                        $RemotePhosphorMax = 1800;
                    }
                }
            }
            
            $boardConfigs = array (
                'A' => self::BOARDLEN_A,
                'B' => self::BOARDLEN_B,
                'B1' => self::BOARDLEN_B1,
                'C' => self::BOARDLEN_C,
                
                'GAP' => self::BOARDLEN_GAP,
                'EC'  => self::BOARDLEN_EC,
                'ALUM'  => self::BOARDLEN_ALUM,
            );

            $midBoardTypes = array (
                'B'=>$boardConfigs['B'],
            );

            // find maximum and configs array if not available
            if (empty($this->_maximum) || empty($this->_configs)) {
                $startLen = $boardConfigs['EC'] + $boardConfigs['ALUM'] + $boardConfigs['A'] + $boardConfigs['ALUM'] + $boardConfigs['EC'];  // this is the minimum length of any board
                $this->_configs['A'] = array ($startLen, 'A', false); // this is the start configuration of every board
                $this->_maximum = 0;
                $this->architecturalIterate($startLen, 'A', $boardConfigs['B'], 'B', $RemotePhosphorMax, $boardConfigs['GAP'], $boardConfigs['C'], $boardConfigs['B1'], $this->_configs, $this->_maximum);
            }

            // Note: in the past we iterated board types here - see beta site code for example
            $data+= array (
                'sLen' => $length,
                'maxBoardPerRP' => $this->_configs[$this->_maximum][0],
                'maxBoardPerRPB' => $this->_maximum,
                'maximumUnitLength' => $maxunitlength,
            );
            
            // work out the maximum length
            $maximumCnt = floor($data['maximumUnitLength']/$this->_configs[$this->_maximum][0]);
            $remainder = $data['maximumUnitLength'] - ($maximumCnt * $this->_configs[$this->_maximum][0]);

            $optimumConfig = array($this->_maximum=>$maximumCnt);

            $chosenRem = 0;
            // work out optimum configuration for remainder
            foreach ($this->_configs as $type=>$length) {
                if ($length[0]<=$remainder) {
                    if (empty($chosenRem)) {
                        $chosenRem = $type;
                    } elseif ($length[0]>$this->_configs[$chosenRem][0]) {
                        $chosenRem = $type;
                    }
                }
            }

            if (!empty($chosenRem)) {
                if (!empty($optimumConfig[$chosenRem])) {
                    $optimumConfig[$chosenRem]++;
                } else {
                    $optimumConfig[$chosenRem] = 1;
                }
            }
            
            // optimum length is the optimum length achievable
            $data+= array(
                'remotePhosphorMax' => $RemotePhosphorMax,
                'optimumConfig' => $optimumConfig,
                'optimumLength' => 0
            );
            
            foreach ($optimumConfig as $type=>$cnt) {
                $data['optimumLength']+=$this->_configs[$type][0] * $cnt;
            }
            

            // calculate the number of optimum lengths in required length
            $setup = array();
            $fullLengths = floor($data['sLen']/$data['optimumLength']);
            $data['dLen'] = $fullLengths * $data['optimumLength'];
            $remainder = $data['sLen'] - ($fullLengths * $data['optimumLength']);

            // can't have a remainder that is less than minimum config 
            if ($remainder<$this->_configs['A']) {
                // do something!!!
            }

            //echo '<pre>',   print_r($optimumConfig, true),'</pre>';
            for ($i=0; $i<$fullLengths; $i++) {
                $setup[] = $optimumConfig;
            }
//echo '<pre>',print_r($setup, true), '</pre>';
            // now work out optimum configuration for remainder
            $csetup = array();
            $this->architecturalFindLength($this->_configs, $remainder, array(), 0, 0, $csetup);
            
            $alternatives = array();
            $tmpClosestIdx = false;
            if (!empty($csetup)) {
                foreach ($csetup as $idx=>$csData) {
                    if ($tmpClosestIdx ===false) {
                        $tmpClosestIdx = $idx;
                    } elseif ($csetup[$tmpClosestIdx][0]<$csData[0]) {
                        $tmpClosestIdx = $idx;
                    }
                    
                    if ($alternativeConfigs) {
                        $lenStr = $csData[0]+$data['dLen'];
                        if ($lenStr<$alternativeThreshold) {
                            continue;
                        }
                        $str = '';
                        foreach ($csData[1] as $cf=>$qtty) {
                            for ($j=0; $j<$qtty; $j++) {
                                $str.=(!empty($str)?'|':'')."[{$cf}]";
                            }
                        }
                        $alternatives["{$lenStr}"][$idx]=$str;
                    }
                    
                }

                if ($mode==1) { // closest length mode
                    if (!empty($args['altId']) && !empty($args['altSz'])) {
                        if (!empty($csetup[$args['altId']])) {
                            if (round($csetup[$args['altId']][0]+$data['dLen'],2)==round($args['altSz'],2)) {
                                $tmpClosestIdx = $args['altId'];
                            }
                        }
                    }
                    $data['dLen']+=$csetup[$tmpClosestIdx][0];
                    $setup[] = $csetup[$tmpClosestIdx][1];
                    
                    if ($alternativeConfigs) {
                        $data['dAltsId'] = $tmpClosestIdx;
                    }
                } else {
                    $tmpClosestIdx2 = $tmpClosestIdx;
                    $tmpIteration = $csetup[$tmpClosestIdx][2];
                    foreach ($csetup as $idx=>$csData) {
                        if (($csetup[$tmpClosestIdx][0]-$csData[0]) <= $fplRange) {
                            if ($tmpIteration>$csData[2]) {
                                $tmpClosestIdx2=$idx;
                            } elseif ($tmpIteration==$csData[0]) {
                                if ($csetup[$tmpClosestIdx2][0]<$csData[0]) {
                                    $tmpClosestIdx2=$idx;
                                }
                            }
                        } 
                    }
                    $data['dLen']+=$csetup[$tmpClosestIdx2][0];
                    $setup[] = $csetup[$tmpClosestIdx2][1];
                    if ($alternativeConfigs) {
                        $data['dAltsId'] = $tmpClosestIdx2;
                    }
                }
            }
            
            
            if ($alternativeConfigs) {
                uksort($alternatives, array('self','cmp'));
                $data['dAlts'] = $alternatives;
            }
            
            
            $data['dBillU'] = ceil($data['dLen']/1000);
            $data['dBill'] = $data['dBillU'] * 1000;
            $data['dCost'] = $data['dBillU'] * $product->getPPU();
            
            $data['dBillTU'] = $data['dBillU']*$data['dUnits'];
            $data['dBillT'] = $data['dBillTU'] * 1000;
            $data['dCostT'] = $data['dBillTU'] * $product->getPPU();
            $data['dConf'] = $setup;

            return $data;
        } catch (\Exception $ex) {
            return array();
        }
    }
    
    
    /**
     * find set of board configs available
     * @param int $curLen
     * @param string $currConf
     * @param int $boardLen
     * @param string $boardName
     * @param int $maxlen
     * @param int $boardGap
     * @param int $boardC
     * @param int $boardB1
     * @param array $config
     * @param type $maximum
     */
    function architecturalIterate($curLen, $currConf, $boardLen, $boardName, $maxlen, $boardGap, $boardC, $boardB1, array &$config, &$maximum) {
        $len = ($curLen+$boardGap+$boardC);
        $conf = $currConf.'-C';
        if ($len < $maxlen) {
            $config[$conf] = array ($len, $conf, true);
            if (empty($maximum) || ($len>$config[$maximum][0])) $maximum = $conf;
        }
        
        $len = ($curLen+$boardGap+$boardB1);
        $conf = $currConf.'-B1';
        if ($len < $maxlen) {
            $config[$conf] = array ($len, $conf, false);
            //if (empty($maximum) || ($len>$config[$maximum][0])) $maximum = $conf;
        }
        
        $len = ($curLen+$boardGap+$boardB1+$boardGap+$boardC);
        $conf = $currConf.'-B1-C';
        if ($len < $maxlen) {
            $config[$conf] = array ($len, $conf, true);
            if (empty($maximum) || ($len>$config[$maximum][0])) $maximum = $conf;
        }
        
        $len = ($curLen+$boardGap+$boardB1+$boardGap+$boardB1);
        $conf = $currConf.'-B1-B1';
        if ($len < $maxlen) {
            $config[$conf] = array ($len, $conf, false);
            //if (empty($maximum) || ($len>$config[$maximum][0])) $maximum = $conf;
        }
        
        $len = ($curLen+$boardGap+$boardB1+$boardGap+$boardB1+$boardGap+$boardC);
        $conf = $currConf.'-B1-B1-C';
        if ($len < $maxlen) {
            $config[$conf] = array ($len, $conf, true);
            if (empty($maximum) || ($len>$config[$maximum][0])) $maximum = $conf;
        }
        
        $len = $curLen+$boardGap+$boardLen;
        if ($len < $maxlen) {
            $currConf = $currConf.'-'.$boardName;
            $config[$currConf] = array ($len, $currConf, false);
            //if (empty($maximum) || ($len>$config[$maximum][0])) $maximum = $currConf;
            $this->architecturalIterate($len, $currConf, $boardLen, $boardName, $maxlen, $boardGap, $boardC, $boardB1, $config, $maximum);
        } 
        
        
    }
    
    /**
     * find architectural optimum length
     * @param array $configs
     * @param int $MAXLEN
     * @param array $configuration
     * @param int $cLen
     * @param int $iteration
     * @param array $csetup
     * @return void
     */
    function architecturalFindLength($configs, $MAXLEN, $configuration, $cLen, $iteration, &$csetup) {
        if ($iteration>=4) {
            return;
        }
        
        foreach ($configs as $type=>$config) {
            // if this is a linkable component
            if (($cLen+$config[0])>$MAXLEN) {
                continue;
            }
            
            $conf = $configuration;
            
            if (isset($conf[$type])) {
                $conf[$type]+=1;
            } else {
                $conf[$type]=1;
            }
            
            $csetup[] = array(
                $cLen+$config[0],
                $conf,
                $iteration+1
            );
            
            if ($config[2]===true) {
                $this->architecturalFindLength($configs, $MAXLEN, $conf, $cLen+$config[0], $iteration+1, $csetup);
            } 
            
        }
    }
    
    
    // factory involkable methods
    protected $em;
    
    function setEntityManager(\Doctrine\ORM\EntityManager $em) {
        $this->em = $em;
    }
    
    public function getEntityManager() {
        return $this->em;
    }


    
}

