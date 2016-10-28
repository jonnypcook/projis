<?php

namespace Project\Service;

use Doctrine\Common\Collections\ArrayCollection;
use Product\Entity\BoardChain;
use Product\Entity\BoardConfigurationBoardChain;
use Product\Entity\BoardConfiguration;
use Project\Entity\Project as Project;

class Model
{
    /**
     * calculate payback and other forecast figures
     * @param Project $project
     * @param int $years
     * @param array $args
     * @return array
     */
    function payback(Project $project, $years = 12, array $args = array())
    {
        //calculate funding options
        $financing = false;
        if (!empty($project->getClient()->getFinanceStatus()) && ($project->getClient()->getFinanceStatus()->getFinanceStatusId() > 1)) {
            if (($project->getFinanceYears()->getFinanceYearsId() > 0)) {
                if (!empty($project->getFinanceProvider())) {
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

        $ecaCompatibile = ($q->getSingleScalarResult() > 0);

        $qb
            ->select('s.label, s.cpu, s.ppu, s.ippu, s.quantity, s.hours, s.legacyWatts, s.legacyQuantity, s.legacyMcpu, s.lux, s.occupancy, s.locked, s.systemId, s.attributes, '
                . 'sp.spaceId, sp.name, sp.quantity AS sQuantity, '
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


        $query = $qb->getQuery();
        $result = $query->getResult(\Doctrine\ORM\AbstractQuery::HYDRATE_ARRAY);

        $totals = array(
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
            'legacyQuantity' => 0,
            'ledQuantity' => 0,
        );


        $discount = $project->getMcd();

        $spaces = array();
        foreach ($result as $obj) {
            if (empty($spaces[$obj['spaceId']])) {
                $spaces[$obj['spaceId']] = true;
            }

            $led = (($obj['productType'] == 1) || ($obj['productType'] == 3)); // type 1 is an LED
            $product = ($obj['service'] == 0);
            $installation = ($obj['productType'] == 100); // type 100 is an installation product
            $delivery = ($obj['productType'] == 101); // type 101 is a delivery product
            $service = ($obj['productType'] == 102); // type 103 is an additional service
            $access = ($obj['productType'] == 103); // type 102 is an access product
            $spaceQuantity = empty($obj['sQuantity']) ? 1 : $obj['sQuantity'];

            // calculate price 
            $priceIncDiscount = round($obj['ppu'] * (1 - ($discount * $obj['mcd'])), 2);
            $price = round(($obj['quantity'] * $priceIncDiscount * $spaceQuantity), 2);

            if ($product && $project->getIbp()) {
                $totals['IBP'] += round($price * 0.018, 2);
                //$totals['IBP']+=($obj['ibppu'] * $obj['quantity']);
            }

            // calculate power savings (if applicable)
            if ($installation) {
                $totals['price_installation'] += $price;
                /*if (!empty($obj['eca'])) {
                    $totals['priceeca']+=$price;
                }/**/
            } elseif ($delivery) {
                $totals['price_delivery'] += $price;
            } elseif ($service) {
                $totals['price_service'] += $price;
            } elseif ($access) {
                $totals['price_access'] += $price;
            } else {
                $totals['legacyQuantity'] += ($obj['legacyQuantity'] * $spaceQuantity);
                $pwrSaveLeg = ($obj['legacyWatts'] * $obj['legacyQuantity']);
                if ($obj['productType'] === 1) {
                    $totals['ledQuantity'] += ($obj['quantity'] * $spaceQuantity);
                }

                if ($obj['productType'] == 3) {
                    $attr = json_decode($obj['attributes']);
                    $ratio = (($attr->dLen * $attr->dUnits) / 1000) / $obj['quantity'];
                    $pwrSaveLed = round(($obj['quantity'] * $ratio * $obj['pwr']) * (1 - ($obj['lux'] / 100)) * (1 - ($obj['occupancy'] / 100)), 0);
                } else {
                    $pwrSaveLed = ($obj['quantity'] * $obj['pwr']) * (1 - ($obj['lux'] / 100)) * (1 - ($obj['occupancy'] / 100));
                }

                $pwrSave = (!$led || (($obj['legacyWatts'] * $obj['legacyQuantity']) == 0)) ? 0 : ((($pwrSaveLeg - $pwrSaveLed) / ($obj['legacyWatts'] * $obj['legacyQuantity'])) * 100);
                $kwHSave = (!$led || (($obj['legacyWatts'] * $obj['legacyQuantity']) == 0)) ? 0 : ((($pwrSaveLeg - $pwrSaveLed) / 1000) * $obj['hours'] * 52);

                $currentElecConsumption = round((($obj['legacyQuantity'] * $obj['hours'] * $obj['legacyWatts'] * 52) / 1000) * $project->getFuelTariff(), 2);
                $ledElecConsumption = round(((100 - $pwrSave) / 100) * $currentElecConsumption, 2);
                $elec_sav_ach = round($currentElecConsumption - $ledElecConsumption, 2);

                // calculate co2 savings
                $co2emmissionreduction = round((($elec_sav_ach / $project->getFuelTariff()) * $project->getCo2()) / 1000, 2);

                // calculate maintenance cost
                $legacyMaintenance = round($obj['legacyQuantity'] * $obj['legacyMcpu'], 2);

                // shift totals as per iteration
                $totals['elec_sav_ach'] += ($elec_sav_ach * $spaceQuantity);
                $totals['currentElecConsumption'] += ($currentElecConsumption * $spaceQuantity);
                $totals['ledElecConsumption'] += ($ledElecConsumption * $spaceQuantity);
                $totals['co2emmissionreduction'] += ($co2emmissionreduction * $spaceQuantity);
                $totals['legacyMaintenance'] += ($legacyMaintenance * $spaceQuantity);
                /*if (!empty($obj['eca'])) {
                    $totals['priceeca']+=$price;
                }/**/
                $totals['price_product'] += $price;
                $totals['productcost'] += ($obj['cpu'] * $obj['quantity'] * $spaceQuantity);
                $totals['kwhSave'] += ($kwHSave * $spaceQuantity);
            }

            if ($ecaCompatibile) {
                if (!empty($obj['eca'])) {
                    $totals['priceeca'] += $price;
                }
            }


            // shift totals as per iteration
            $totals['price'] += $price;
        }

        // adjust legacy maintenance if required
        if ($project->getMaintenance() > 0) {
            $totals['legacyMaintenance'] = $project->getMaintenance();
        }

        $csav = 0;
        $carbon = 0;

        // work out additional fee (if applicable)
        $totals['prelim'] = round($totals['price'] * $project->getFactorPrelim(), 2);
        $totals['overhead'] = round(($totals['price'] + $totals['prelim']) * $project->getFactorOverhead(), 2);
        $totals['management'] = round(($totals['price'] + $totals['prelim'] + $totals['overhead']) * $project->getFactorManagement(), 2);
        $totals['fee'] = round($totals['prelim'] + $totals['overhead'] + $totals['management'], 2);

        // total cost
        $total_cost = round($totals['price'] + $totals['fee'] + $totals['IBP'], 2);

        // cost of financing
        $financing_unsupported = false;
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


            $query = $qb2->getQuery();
            $result = $query->getResult(\Doctrine\ORM\AbstractQuery::HYDRATE_ARRAY);

            if (empty($result)) {
                $financing = false;
                $financing_unsupported = true;
            } else {
                $ff = array_shift($result);
                $finance_data['repayments'] = round($finance_data['amount'] * (float)$ff['factor'] * 4, 0);
                $finance_data['annualrate'] = round((((($finance_data['repayments'] * $project->getFinanceYears()->getFinanceYearsId()) - $finance_data['amount']) / $finance_data['amount']) / $project->getFinanceYears()->getFinanceYearsId()) * 100, 2);
            }
        }

        // calculate eca saving & carbon allowance
        $eca = $totals['priceeca'] * $project->getEca(); // new method based on individual light eca compatibility
        $callow = $project->getCarbon();

        $payback = $financing ? 0 : -($totals['price'] + $totals['fee'] + $totals['IBP']);
        $payback_eca = $financing ? $eca : -($totals['price'] + $totals['fee'] + $totals['IBP'] - $eca);

        $finance_avg_benefit = 0;
        $finance_avg_repay = 0;
        $finance_avg_netbenefit = 0;

        $payback_year = 0;
        $ledMaintenaceOn = (($project->getMaintenanceLed() > 0) && ($project->getMaintenanceLedYear() > 0));
        for ($i = 1; $i <= $years; $i++) {
            $legsp = ($totals['currentElecConsumption'] * pow(1 + $project->getEpi(), $i - 1));
            $ledsp = ($totals['ledElecConsumption'] * pow(1 + $project->getEpi(), $i - 1));
            $cam = ($totals['legacyMaintenance'] * pow(1 + $project->getRpi(), $i - 1));
            $led_maintenance = ($ledMaintenaceOn ? (($project->getMaintenanceLedYear() <= $i) ? round($project->getMaintenanceLed() * pow(1 + $project->getRpi(), $i - 1), 2) : 0) : 0);
            $carbon += $totals['co2emmissionreduction'];

            $cost_of_financing = ($financing) ? (($project->getFinanceYears()->getFinanceYearsId() >= $i) ? $finance_data['repayments'] : 0) : 0;
            $cash_benefit = round(($cam + ($legsp - $ledsp)) - $cost_of_financing - $led_maintenance, 2);

            $csav += round($cam + ($legsp - $ledsp) - $led_maintenance, 2);

            $payback += ($cam + ($legsp - $ledsp) - $led_maintenance);

            $payback_eca += ($cam + ($legsp - $ledsp) - $led_maintenance + ($callow * $totals['co2emmissionreduction']));

            if ($project->getFinanceYears()->getFinanceYearsId() >= $i) {
                $finance_avg_benefit += round($cam + ($legsp - $ledsp) - $led_maintenance, 2);
                $finance_avg_repay += $cost_of_financing;
                $finance_avg_netbenefit += $cash_benefit;
            }


            $payback -= $cost_of_financing;
            $payback_eca -= $cost_of_financing;

            $forecast[$i] = array(
                round($legsp, 2),
                round($ledsp, 2),
                round($legsp - $ledsp, 2), // electricity saving
                round($cam, 2),
                round($cam + ($legsp - $ledsp) - $led_maintenance, 2),
                round($csav, 2),
                round(($legsp - $ledsp) / 12, 2),
                round($totals['co2emmissionreduction'], 2),
                round($payback, 2),
                round($payback_eca, 2),
                round(($callow * $totals['co2emmissionreduction']), 2),
                $cost_of_financing,
                $cash_benefit,
                $led_maintenance,
            );

            if (($payback_eca > 0) && empty($payback_year)) {
                $payback_year = $i;
            }
        }

        $carballow = ($callow * $carbon);


        $figures = array(
            'saving' => round($csav, 2),
            'cost_maintenance' => round($totals['legacyMaintenance'], 2),
            'cost_install' => $totals['price_installation'],
            'cost_delivery' => round($totals['price_delivery'], 2),
            'cost_led' => round($totals['price_product'], 2), // TO DO
            'margin' => ($totals['price_product'] > 0) ? round((1 - ($totals['productcost'] / $totals['price_product'])) * 100, 2) : 0,
            'cost' => $total_cost,
            'costvat' => round(($total_cost * 1.2), 2),
            'costvateca' => round(($total_cost * 1.2) - $eca, 2),
            'vat' => round(($total_cost * 0.2), 2),
            'costeca' => round($total_cost - $eca, 2),
            'cost_prelim' => $totals['prelim'],
            'cost_overheads' => $totals['overhead'],
            'cost_management' => $totals['management'],// $total_fee,
            'cost_access' => $totals['price_access'],
            'cost_service' => $totals['price_service'],
            'cost_ibp' => $totals['IBP'],
            'profit' => round($payback, 2),
            'profiteca' => round($payback_eca, 2),
            'carbon' => round($carbon, 2),
            'eca' => round($eca, 2),
            'eca_eligible' => round($totals['priceeca'], 2),
            'eca_ineligible' => round($totals['price'] - $totals['priceeca'], 2),
            'carbonallowance' => $carballow,
            'finance_amount' => ($financing ? ($finance_data['repayments'] * $project->getFinanceYears()->getFinanceYearsId()) : 0),
            'finance_years' => ($financing ? $project->getFinanceYears()->getFinanceYearsId() : 0),
            'finance_annual_repayment' => ($financing ? $finance_data['repayments'] : 0),
            'finance_annual_rate' => ($financing ? $finance_data['annualrate'] : 0),
            'finance_avg_benefit' => ($financing ? round($finance_avg_benefit / $project->getFinanceYears()->getFinanceYearsId(), 2) : 0),
            'finance_avg_repay' => ($financing ? round($finance_avg_repay / $project->getFinanceYears()->getFinanceYearsId(), 2) : 0),
            'finance_avg_netbenefit' => ($financing ? round($finance_avg_netbenefit / $project->getFinanceYears()->getFinanceYearsId(), 2) : 0),
            'finance_netbenefit' => ($financing ? round($finance_avg_netbenefit, 2) : 0),
            'space_count' => count($spaces),
            'payback_year' => $payback_year,
            'kwhYear' => $totals['kwhSave'],
            'ecacompatible' => $ecaCompatibile,
        );

        if ($financing) {
            $figures['finance_exceeds'] = (($project->getClient()->getFund() < $total_cost) ? 1 : 0);
        }

        if ($financing_unsupported) {
            $figures['finance_unsupported'] = 1;
        }

        $figures['legacy_quantity'] = $totals['legacyQuantity'];
        $figures['led_quantity'] = $totals['ledQuantity'];
        /**/

        return array(
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
    function spaceBreakdown(Project $project, array $args = array())
    {
        $em = $this->getEntityManager();
        $qb = $em->createQueryBuilder();

        $breakdown = array();


        $qb
            ->select('s.label, s.cpu, s.ppu, s.ippu, s.quantity, s.hours, s.legacyWatts, s.legacyQuantity, s.legacyMcpu, s.lux, s.occupancy, s.locked, s.systemId, s.attributes, '
                . 'sp.spaceId, sp.name AS sName, sp.root, sp.quantity AS sQuantity,'
                . 'b.name AS bName, b.buildingId,'
                . 'ba.postcode, ba.line1, ba.line2, ba.line3, ba.line4, ba.line5, '
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

        $query = $qb->getQuery();
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
                $breakdown [$obj['buildingId']] = array(
                    'name' => $obj['bName'],
                    'address' => $obj['line1'] .
                        (empty($obj['line2']) ? '' : ', ' . $obj['line2']) .
                        (empty($obj['line3']) ? '' : ', ' . $obj['line3']) .
                        (empty($obj['line4']) ? '' : ', ' . $obj['line4']) .
                        (empty($obj['line5']) ? '' : ', ' . $obj['line5']),
                    'postcode' => $obj['postcode'],
                    'spaces' => array()
                );
            }

            if (!isset($breakdown[$obj['buildingId']] ['spaces'] [$obj['spaceId']])) {
                $breakdown [$obj['buildingId']] ['spaces'] [$obj['spaceId']] = array(
                    'name' => $obj['sName'],
                    'root' => !empty($obj['root']),
                    'quantity' => $obj['sQuantity'],
                    'products' => array()
                );
            }


            // calculate price
            $priceIncDiscount = round($obj['ppu'] * (1 - ($discount * $obj['mcd'])), 2);
            $price = round(($obj['quantity'] * $priceIncDiscount), 2);


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
                $pwrSaveLeg = ($obj['legacyWatts'] * $obj['legacyQuantity']);
                if ($obj['productType'] == 3) {
                    $attr = json_decode($obj['attributes']);
                    $ratio = (($attr->dLen * $attr->dUnits) / 1000) / $obj['quantity'];
                    $pwrSaveLed = round(($obj['quantity'] * $ratio * $obj['pwr']) * (1 - ($obj['lux'] / 100)) * (1 - ($obj['occupancy'] / 100)), 0);
                } else {
                    $pwrSaveLed = ($obj['quantity'] * $obj['pwr']) * (1 - ($obj['lux'] / 100)) * (1 - ($obj['occupancy'] / 100));
                }


                $pwrSave = (!$led || ($obj['legacyWatts'] == 0)) ? 0 : ((($pwrSaveLeg - $pwrSaveLed) / ($obj['legacyWatts'] * $obj['legacyQuantity'])) * 100);
                $kwHSave = (!$led || ($obj['legacyWatts'] == 0)) ? 0 : ((($pwrSaveLeg - $pwrSaveLed) / 1000) * $obj['hours'] * 52);

                $currentElecConsumption = round((($obj['legacyQuantity'] * $obj['hours'] * $obj['legacyWatts'] * 52) / 1000) * $project->getFuelTariff(), 2);
                $ledElecConsumption = round(((100 - $pwrSave) / 100) * $currentElecConsumption, 2);
                $elec_sav_ach = round($currentElecConsumption - $ledElecConsumption, 2);

                // calculate co2 savings
                $co2emmissionreduction = round((($elec_sav_ach / $project->getFuelTariff()) * $project->getCo2()) / 1000, 2);

                // calculate maintenance cost
                $legacyMaintenance = round($obj['legacyQuantity'] * $obj['legacyMcpu'], 2);

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
                    round($pwrSave, 2),
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

    function billitems(Project $project, array $args = array())
    {
        $em = $this->getEntityManager();
        //$qb = $em->createQueryBuilder();
        $discount = ($project->getMcd());

        $query = $em->createQuery('SELECT p.mcd, p.productId, p.model, p.description, p.eca, pt.service, pt.name AS productType, pt.typeId, pt.service, s.ppu, s.attributes, s.label, '
            . 'SUM(s.quantity * sp.quantity) AS quantity, '
            . 'SUM(s.ppu * s.quantity * sp.quantity) AS price, '
            . 'SUM(ROUND((s.ppu * (1 - (' . $discount . ' * p.mcd))),2) * s.quantity * sp.quantity) AS priceMCD, '
            . 'SUM(s.cpu * s.quantity * sp.quantity) AS cost '
            . 'FROM Space\Entity\System s '
            . 'JOIN s.space sp '
            . 'JOIN s.product p '
            . 'JOIN p.type pt '
            . 'WHERE sp.project=' . $project->getProjectId() . ' '
            . ((!empty($args['products'])) ? 'AND pt.service = 0 ' : '')
            . 'GROUP BY s.product, s.ppu');


        return $query->getResult(\Doctrine\ORM\AbstractQuery::HYDRATE_ARRAY);
    }

    /**
     * calculate trial breakdown set
     * @param \Project\Entity\Project $project
     * @param array $args
     * @return type
     */
    function trialBreakdown(Project $project, array $args = array())
    {
        $em = $this->getEntityManager();
        $qb = $em->createQueryBuilder();

        $breakdown = array();


        $qb
            ->select('s.label, s.quantity, s.hours, s.legacyWatts, s.legacyQuantity, s.legacyMcpu, s.lux, s.occupancy, s.systemId, '
                . 'sp.spaceId, sp.name AS sName, sp.root, sp.quantity AS sQuantity, '
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


        $query = $qb->getQuery();
        $result = $query->getResult(\Doctrine\ORM\AbstractQuery::HYDRATE_ARRAY);

        $discount = $project->getMcd();

        foreach ($result as $obj) {
            $led = ($obj['productType'] == 1);

            if (empty($obj['buildingId'])) {
                $obj['buildingId'] = 0;
            }

            if (!isset($breakdown[$obj['buildingId']])) {
                $breakdown [$obj['buildingId']] = array(
                    'name' => $obj['bName'],
                    'postcode' => $obj['postcode'],
                    'spaces' => array()
                );
            }

            if (!isset($breakdown[$obj['buildingId']] ['spaces'] [$obj['spaceId']])) {
                $breakdown [$obj['buildingId']] ['spaces'] [$obj['spaceId']] = array(
                    'name' => $obj['sName'],
                    'quantity' => $obj['sQuantity'],
                    'root' => !empty($obj['root']),
                    'products' => array()
                );
            }


            // calculate price
            $rrp = round($obj['ppu'], 2);
            $price = round(($obj['quantity'] * $rrp), 2);


            // calculate power savings (on a per fitting basis)
            $pwrSaveLeg = ($obj['legacyWatts']);
            $pwrSaveLed = ($obj['pwr']) * (1 - ($obj['lux'] / 100)) * (1 - ($obj['occupancy'] / 100));
            $pwrSave = (!$led || ($obj['legacyWatts'] == 0)) ? 0 : ((($obj['legacyWatts'] - $pwrSaveLed) / ($obj['legacyWatts'])) * 100);
            $kwHSave = (!$led || ($obj['legacyWatts'] == 0)) ? 0 : ((($obj['legacyWatts'] - $pwrSaveLed) / 1000) * $obj['hours'] * 52);


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
                round($pwrSave, 2),
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

    /**
     * get picklist
     * @param $attributes
     * @param array $boards
     * @param array $architectural
     * @param array $phosphor
     * @param array $aluminium
     * @param int $spaceMultipler
     */
    function getPickListItems($attributes, array &$boards, array &$architectural, array &$phosphor, array &$aluminium, $spaceMultipler = 1)
    {
        //echo '<pre>',print_r($attributes['dConf'], true), '</pre>';
        $multiplier = empty($attributes['dUnits']) ? 1 * $spaceMultipler : $attributes['dUnits'] * $spaceMultipler;

        foreach ($attributes['dConf'] as $confId => $aConfigs) {
            $size = count($aConfigs);
            $current = 0;
            foreach ($aConfigs as $aConfig => $aQty) {
                $current++;
                $rpLen = 0;
                $lastString = ($current == $size);
                if ($lastString) { // last item
                    $architectural['_ECT'][3] += (1 * $multiplier);
                    $architectural['_EC'][3] += (((2 * $aQty) - 1) * $multiplier);
                } else {
                    $architectural['_EC'][3] += ((2 * $aQty) * $multiplier);
                }

                $brdBd = explode('-', $aConfig);

                //$rpLen+=self::BOARDLEN_EC*2;
                $rpLen += self::BOARDLEN_GAP * (count($brdBd) - 1);
                $rpLen += self::BOARDLEN_ALUM * 2;

                foreach ($brdBd as $brd) {
                    $rpLen += constant('self::BOARDLEN_' . $brd);

                    if ($brd == 'A') {
                        $architectural['_CBL'][3] += ($aQty * $multiplier);
                        $architectural['_WG'][3] += ((2 * $aQty) * $multiplier);
                    } elseif ($brd == 'C') {
                        $architectural['_CBL'][3] += ($aQty * $multiplier);
                        if ($lastString) {
                            $architectural['_CBL'][3] -= (1 * $multiplier);
                        }
                    }
                    //$architectural['_'.$brd][3]+=$aQty;
                    $boards['_' . $brd][3] += ($aQty * $multiplier);
                }

                $aluLen = $rpLen;
                $rpLen--;
                if (empty($phosphor["{$rpLen}"][$aConfig])) {
                    $phosphor["{$rpLen}"][$aConfig][0] = 0;
                    $phosphor["{$rpLen}"][$aConfig][1] = 0;
                    $aluminium["{$aluLen}"][$aConfig] = 0;
                }


                $phosphor["{$rpLen}"][$aConfig][0] += (($lastString) ? (($aQty - 1) * $multiplier) : ($aQty * $multiplier));
                $phosphor["{$rpLen}"][$aConfig][1] += (($lastString) ? (1 * $multiplier) : 0);
                $aluminium["{$aluLen}"][$aConfig] += ($aQty * $multiplier);

            }
        }

    }

    /**
     * bulid sheet generation
     * @param type $attributes
     * @param type $model
     * @param array $build
     * @param array $buildConfig
     */
    function getBuildsheetItems($attributes, $model, array &$build, array &$buildConfig)
    {

        $multiplier = empty($attributes['dUnits']) ? 1 : $attributes['dUnits'];
        foreach ($attributes['dConf'] as $confId => $aConfigs) {
            $size = count($aConfigs);
            $current = 0;
            foreach ($aConfigs as $aConfig => $aQty) {
                $addConfig = empty($buildConfig[$aConfig]);

                $current++;
                $rpLen = 0;
                $lastString = ($current == $size);

                $brdBd = explode('-', $aConfig);

                if ($addConfig) {
                    $buildConfig[$aConfig] = array(
                        '_A' => 0,
                        '_B' => 0,
                        '_B1' => 0,
                        '_C' => 0,
                        '_CBL' => 0,
                        '_WG' => 0,
                        'LEN' => (self::BOARDLEN_ALUM * 2) + self::BOARDLEN_GAP * (count($brdBd) - 1) - 1 // here the -1 is for phosphor reduction
                    );

                    foreach ($brdBd as $brd) {
                        $buildConfig[$aConfig]['LEN'] += constant('self::BOARDLEN_' . $brd);
                        $buildConfig[$aConfig]['_' . $brd]++;
                        if ($brd == 'A') {
                            $buildConfig[$aConfig]['_CBL'] += 1;
                            $buildConfig[$aConfig]['_WG'] += 2;
                        } elseif ($brd == 'C') {
                            $buildConfig[$aConfig]['_CBL'] += 1;
                        }
                    }
                }

                $rpLen = $buildConfig[$aConfig]['LEN'];
                if (empty($build[$model]["{$rpLen}"][$aConfig])) {
                    $build[$model]["{$rpLen}"][$aConfig][0] = 0;
                    $build[$model]["{$rpLen}"][$aConfig][1] = 0;
                }


                $build[$model]["{$rpLen}"][$aConfig][0] += (($lastString) ? (($aQty - 1) * $multiplier) : ($aQty * $multiplier));
                $build[$model]["{$rpLen}"][$aConfig][1] += (($lastString) ? (1 * $multiplier) : 0);

            }
        }

        //echo '<pre>',print_r($build, true), '</pre>';die();
    }

    /**
     * statiglobal compare function
     * @param type $a
     * @param type $b
     * @return int
     */
    static function cmp($a, $b)
    {
        $aF = (float)$a;
        $bF = (float)$b;
        //echo print_r($a, true).'>'.print_r($b, true).'<br />';
        if ($aF == $bF) {
            return 0;
        }
        return ($aF < $bF) ? -1 : 1;
    }


    /**
     * add a board configuration
     * @param $configuration
     * @param $length
     * @param bool|false $flush
     */
    function addBoardConfiguration ($configuration, $length, $chainable, $flush = false) {
        $boardConfiguration = new BoardConfiguration();
        $boardConfiguration->setConfiguration($configuration);
        $boardConfiguration->setLength($length);
        $boardConfiguration->setChainable($chainable);

        $this->getEntityManager()->persist($boardConfiguration);

        if ($flush === true) {
            $this->getEntityManager()->flush();
        }
    }



    /**
     * generate set of board configs available
     * @param int $curLen
     * @param string $currConf
     * @param int $boardLen
     * @param string $boardName
     * @param int $maximum
     * @param int $boardGap
     * @param int $boardC
     * @param int $boardB1
     */
    function architecturalGenerate($curLen, $currConf, $boardLen, $boardName, $maximum, $boardGap, $boardC, $boardB1)
    {
        $len = ($curLen + $boardGap + $boardC);
        $conf = $currConf . '-C';
        if ($len < $maximum) {
            $this->addBoardConfiguration($conf, $len, true);
        }

        $len = ($curLen + $boardGap + $boardB1);
        $conf = $currConf . '-B1';
        if ($len < $maximum) {
            $this->addBoardConfiguration($conf, $len, true);
        }

        $len = ($curLen + $boardGap + $boardB1 + $boardGap + $boardC);
        $conf = $currConf . '-B1-C';
        if ($len < $maximum) {
            $this->addBoardConfiguration($conf, $len, true);
        }

        $len = ($curLen + $boardGap + $boardB1 + $boardGap + $boardB1);
        $conf = $currConf . '-B1-B1';
        if ($len < $maximum) {
            $this->addBoardConfiguration($conf, $len, true);
        }

        $len = ($curLen + $boardGap + $boardB1 + $boardGap + $boardB1 + $boardGap + $boardC);
        $conf = $currConf . '-B1-B1-C';
        if ($len < $maximum) {
            $this->addBoardConfiguration($conf, $len, true);
        }

        $len = $curLen + $boardGap + $boardLen;
        if ($len < $maximum) {
            $currConf = $currConf . '-' . $boardName;
            $this->addBoardConfiguration($currConf, $len, false, true);
            $this->architecturalGenerate($len, $currConf, $boardLen, $boardName, $maximum, $boardGap, $boardC, $boardB1);
        }

    }


    function truncateBoards () {
        $sql = 'DELETE FROM `Board_Configuration_Board_Chain`';
        $stmt = $this->getEntityManager()->getConnection()->prepare($sql);
        $stmt->execute();

        $sql = 'DELETE FROM `Board_Chain`';
        $stmt = $this->getEntityManager()->getConnection()->prepare($sql);
        $stmt->execute();

        $sql = 'DELETE FROM `Board_Configuration`';
        $stmt = $this->getEntityManager()->getConnection()->prepare($sql);
        $stmt->execute();

        $sql = 'ALTER TABLE `Board_Configuration` AUTO_INCREMENT = 1';
        $stmt = $this->getEntityManager()->getConnection()->prepare($sql);
        $stmt->execute();

        $sql = 'ALTER TABLE `Board_Chain` AUTO_INCREMENT = 1';
        $stmt = $this->getEntityManager()->getConnection()->prepare($sql);
        $stmt->execute();

        $sql = 'ALTER TABLE `Board_Configuration_Board_Chain` AUTO_INCREMENT = 1';
        $stmt = $this->getEntityManager()->getConnection()->prepare($sql);
        $stmt->execute();
    }


    /**
     * @param array $configurations
     * @param $depth
     */
    function addBoardChain (array $configurations, $depth) {
        $this->transactionCount++; //return;

        $boardChain = new BoardChain();
        $length = 0;
        foreach ($configurations as $boardId => $board) {
            $count = count($board);
            $boardConfiguration = $this->getEntityManager()->find('Product\Entity\BoardConfiguration', $boardId);
            $boardConfigurationBoardChain = new BoardConfigurationBoardChain();
            $boardConfigurationBoardChain->setBoardChain($boardChain);
            $boardConfigurationBoardChain->setBoardConfiguration($boardConfiguration);
            $boardConfigurationBoardChain->setCount($count);
            $length += ($boardConfiguration->getLength() * $count);
            $this->getEntityManager()->persist($boardConfigurationBoardChain);
            $boardConfigurationBoardChain = null;
        }

        $boardChain->setDepth($depth);
        $boardChain->setLength($length);
        $this->getEntityManager()->persist($boardChain);
        $boardChain = null;
    }

    public $transactionCount;

    function architecturalGenerateLengths($boards, $currentChain, $currentLength, $maximumUnitLength, $iteration)
    {
        if ($iteration > 4) {
            return;
        }

        foreach ($boards as $board) {
            // ignore boards that exceed maximum length
            if (($currentLength + $board['length']) > $maximumUnitLength) {
                continue;
            }

            $chain = $currentChain;

            $chain[$board['boardConfigurationId']][] = $board;

            $this->addBoardChain($chain, $iteration);
            if (($this->transactionCount % 500) === 0) {
                $this->getEntityManager()->flush();
                $this->getEntityManager()->clear();
            }


            $newLength = $currentLength + $board['length'];
            if (($board['chainable'] == true) && (($newLength + self::BOARDLEN_B1) < $maximumUnitLength)) {
                $this->architecturalGenerateLengths($boards, $chain, $newLength, $maximumUnitLength, $iteration + 1);
            }
        }

    }


    /**
     * Architectural layout generation
     * @return array
     */
    function resetArchitectural()
    {
        try {
            throw new \Exception('we are reset already');

            set_time_limit(1200); // 20 mins
            $this->truncateBoards();

            $data = array();

            $maximumPhosphorLength = 2000; // this is a moveable target- NEED TO CLARIFY
            $maximumUnitLength = 5000;  // this is a moveable target- NEED TO CLARIFY

            $boardConfigs = array(
                'A' => self::BOARDLEN_A,
                'B' => self::BOARDLEN_B,
                'B1' => self::BOARDLEN_B1,
                'C' => self::BOARDLEN_C,

                'GAP' => self::BOARDLEN_GAP,
                'EC' => self::BOARDLEN_EC,
                'ALUM' => self::BOARDLEN_ALUM,
            );

            $startBoardTypes = array(
                'A' => array($boardConfigs['A'], false),
                'B1' => array($boardConfigs['B1'], true),
            );

            // find maximum and configs array if not available
            foreach ($startBoardTypes as $type => $config) {
                $startLen = $boardConfigs['EC'] + $boardConfigs['ALUM'] + $config[0] + $boardConfigs['ALUM'] + $boardConfigs['EC'];  // this is the minimum length of any board
                $this->addBoardConfiguration($type, $startLen, $config[1]);
                $this->architecturalGenerate($startLen, $type, $boardConfigs['B'], 'B', $maximumPhosphorLength, $boardConfigs['GAP'], $boardConfigs['C'], $boardConfigs['B1']);
            }

            // we have created all the board configs so flush remaining
            $this->getEntityManager()->flush();


            // create all combinations under $maximumUnitLength
            $qb = $this->getEntityManager()->createQueryBuilder();
            $qb->select('bc')->from('Product\Entity\BoardConfiguration', 'bc');
            $query = $qb->getQuery();
            $boards = $query->getResult(\Doctrine\ORM\AbstractQuery::HYDRATE_ARRAY);
            $this->transactionCount = 0; // keeps an eye on transactions before flushing and clearing

            // generate and save to database
            $this->architecturalGenerateLengths($boards, array(), 0, $maximumPhosphorLength, 1);
            echo $this->transactionCount;
            $this->getEntityManager()->flush();
            $this->getEntityManager()->clear();

            return $data;
        } catch (\Exception $ex) {
            return array();
        }
    }


    /**
     * find the optimum board configuration
     * @param bool|false $upperLimit
     * @param bool|false $lowerLimit
     * @param bool|false $limit
     * @param bool|true $chainable
     * @return bool
     */
    function findOptimumBoard ($upperLimit = false, $lowerLimit = false, $limit = false, $chainable = null) {
        $queryBuilder = $this->getEntityManager()->createQueryBuilder();
        $queryBuilder
            ->select('bc')
            ->from('Product\Entity\BoardConfiguration', 'bc')
            ->addOrderBy('bc.length ', 'DESC');

        if ($chainable !== null) {
            $queryBuilder
                ->andWhere('bc.chainable = :chainable')
                ->setParameter('chainable', $chainable);
        }

        if ($upperLimit !== false) {
            $queryBuilder
                ->andWhere('bc.length <= :upperLimit')
                ->setParameter('upperLimit', $upperLimit);
        }

        if ($lowerLimit !== false) {
            $queryBuilder
                ->andWhere('bc.length >= :lowerLimit')
                ->setParameter('lowerLimit', $lowerLimit);
        }

        $query = $queryBuilder->getQuery();

        if ($limit !== false) {
            $query->setMaxResults(1);
        }

        $result = $query->getResult();

        if (empty($result)) {
            return false;
        } elseif ($limit === 1) {
            return $result[0];
        }

        return $result;
    }

    /**
     * Architectural layout generation
     * @param \Product\Entity\Product $product
     * @param type $length
     * @param type $mode
     * @param array $args
     * @return array
     */
    function findOptimumArchitectural(\Product\Entity\Product $product, $length, $maximumUnitLength, $maximumPhosphorLength, $mode, array $args = array())
    {
        try {
            $alternativeConfigs = !empty($args['alts']);
            $alternativeThreshold = $length - self::BOARDLEN_B1;

            $data = array('dLen' => 0, 'dUnits' => 1, 'dBill' => 0, 'dBillU' => 0, 'dBillTU' => 0, 'dCost' => 0, 'dConf' => 0);

            if (!empty($args['units'])) {
                if (preg_match('/^[\d]+$/', $args['units'])) {
                    $args['units'] = (int)$args['units'];
                    if ($args['units'] > 0) {
                        $data['dUnits'] = $args['units'];
                    }
                }
            }

            // ensure that phosphor is in valid range
            if ($maximumPhosphorLength > 2000) {
                $maximumPhosphorLength = 1800;
            }

            // largest that we can support per section
            if ($maximumUnitLength > 10000) {
                $maximumUnitLength = 10000;
            }

            // if the maximum unit length is greater than the length then there's no need to specify for greater values
            if ($maximumUnitLength > $length) {
                $maximumUnitLength = $length;
            }

            $fplRange = 50; // fewest phosphor lengths range
            $lengthRange = self::BOARDLEN_B1; // maximum allowable range for remainder selection

            if ($maximumUnitLength < self::BOARDLEN_B1) {
                throw new \Exception('maximum unit length is less than minimum start board size (' . self::BOARDLEN_B1 . 'mm)');
            }

            // find maximum and configs array if not available
            $maximumBoard = $this->findOptimumBoard($maximumPhosphorLength, 0, 1, true);

            // Note: in the past we iterated board types here - see beta site code for example
            $data['sLen'] = $length;
            $data['maxBoardPerRP'] = $maximumBoard->getLength();
            $data['maxBoardPerRPB'] = $maximumBoard->getConfiguration();
            $data['maximumUnitLength'] = $maximumUnitLength;

            // work out the maximum length
            $maximumCnt = floor($maximumUnitLength / $maximumBoard->getLength());
            $remainder = $maximumUnitLength - ($maximumCnt * $maximumBoard->getLength());

            $data['optimumLength'] = 0; // optimum length is the optimum length achievable

            $optimumConfig = ($maximumCnt == 0) ? array() : array($maximumBoard->getConfiguration() => $maximumCnt);
            $data['optimumLength'] = $maximumBoard->getLength() * $maximumCnt;

            $remainderBoard = $this->findOptimumBoard($remainder, 0, 1, true);

            if (!empty($remainderBoard)) {
                if (!empty($optimumConfig[$remainderBoard->getConfiguration()])) {
                    $optimumConfig[$remainderBoard->getConfiguration()]++;
                } else {
                    $optimumConfig[$remainderBoard->getConfiguration()] = 1;
                }

                $data['optimumLength'] += $remainderBoard->getLength();
            }

            $data['remotePhosphorMax'] = $maximumPhosphorLength;
            $data['optimumConfig'] = $optimumConfig;


            // calculate the number of optimum lengths in required length
            $setup = array();
            $fullLengths = floor($length / $data['optimumLength']);
            $data['dLen'] = $fullLengths * $data['optimumLength'];
            $remainder = $length - ($fullLengths * $data['optimumLength']);

            // can't have a remainder that is less than minimum config 
            if ($remainder < self::BOARDLEN_B1) { // note: was 'A'
                // do something!!!
            }

            for ($i = 0; $i < $fullLengths; $i++) {
                $setup[] = $optimumConfig;
            }

            $maximumCnt = floor($remainder / $maximumBoard->getLength());
            $optimumRemainderConfig = ($maximumCnt == 0) ? array() : array($maximumBoard->getConfiguration() => $maximumCnt);
            $remainder = $remainder - ($maximumCnt * $maximumBoard->getLength());
            $lastSectionLength = $maximumBoard->getLength() * $maximumCnt;


            $remainderBoard = $this->findOptimumBoard($remainder, 0, 1, true);

            if (!empty($remainderBoard)) {
                if (!empty($optimumRemainderConfig[$remainderBoard->getConfiguration()])) {
                    $optimumRemainderConfig[$remainderBoard->getConfiguration()]++;
                } else {
                    $optimumRemainderConfig[$remainderBoard->getConfiguration()] = 1;
                }
                $lastSectionLength += $remainderBoard->getLength();
            }

            $data['dLen'] += $lastSectionLength;
            $setup[] = $optimumRemainderConfig;


            $data['dBillU'] = ceil($data['dLen'] / 1000);
            $data['dBill'] = $data['dBillU'] * 1000;
            $data['dCost'] = $data['dBillU'] * $product->getPPU();

            $data['dBillTU'] = $data['dBillU'] * $data['dUnits'];
            $data['dBillT'] = $data['dBillTU'] * 1000;
            $data['dCostT'] = $data['dBillTU'] * $product->getPPU();
            $data['dConf'] = $setup;

            return $data;
        } catch (\Exception $ex) {
            return array();
        }
    }


    // factory involkable methods
    protected $em;


    /**
     * EntityManager setter
     * @param \Doctrine\ORM\EntityManager $em
     */
    function setEntityManager(\Doctrine\ORM\EntityManager $em)
    {
        $this->em = $em;
    }


    /**
     * EntityManager getter
     * @return type
     */
    public function getEntityManager()
    {
        return $this->em;
    }


}

