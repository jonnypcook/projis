<?php
namespace Resource\Controller;

use Application\Controller\AuthController;
use Zend\View\Model\JsonModel;

class ActivityController extends AuthController
{
    public function indexAction()
    {
        $this->setCaption('Resource Activity');

        $jid = $this->params()->fromRoute('jid', '');


        $page = $this->params()->fromRoute('page', 1);
        # move to service
        $limit  = 10;
        $offset = ($page == 0) ? 0 : ($page - 1) * $limit;

        $resources = '';
        $base_url  = '';
        if ( !empty($jid) )
        {
            //$resources = $this->getEntityManager()->getRepository('Resource\Entity\ResourceActivity')->findBy([], array('resourceActivityId' => 'desc'));
            $resources = $this->getEntityManager()->getRepository('Resource\Entity\ResourceActivity')->findByPaginate($offset, $limit, array('jid' => $jid));
            $base_url  = $this->url()->fromRoute('resource_activities', Array('action' => 'index', 'jid' => $jid));

        }
        else
        {
            //$resources = $this->getEntityManager()->getRepository('Resource\Entity\ResourceActivity')->findBy(['project' => $jid], array('resourceActivityId' => 'desc'));
            $resources = $this->getEntityManager()->getRepository('Resource\Entity\ResourceActivity')->findByPaginate($offset, $limit);
            $base_url  = $this->url()->fromRoute('resource_activities', Array('action' => 'index'));
        }
        $total_rows = $resources->count();
        $data       = array();

        foreach ( $resources as $resource )
        {
            $project = $this->getEntityManager()->getRepository('Project\Entity\Project')->findByProjectId($resource->getProject());

            $data['activities'][$resource->getResourceActivityId()] = Array(
                'project_ref'    => $project->getProjectId() . ' - ' . $project->getName() . ' - ' . $project->getClient()->getName(),
                'reference'      => $resource->getReference(),
                'details'        => $resource->getDetails(),
                'resource'       => $resource->getResource()->getName(),
                'cost_code'      => $resource->getCostCode()->getName(),
                'quantity'       => number_format($resource->getQuantity(), 2),
                'rate'           => number_format($resource->getRate(), 2),
                'total'          => number_format($resource->getQuantity() * $resource->getRate(), 2),
                'id'             => $resource->getResourceActivityId(),
                'date'           => $resource->getDate()->format('d/m/Y'),
                'reference_type' => $resource->getReferenceType(),
                'project'        => $project,
                'color'          => !empty($resource->getStatus()) ? $resource->getStatus()->getColor() : ''
            );
        }
        $em                   = $this->getEntityManager();
        $formResourceActivity = new \Resource\Form\ResourceActivityForm($em);

        $url = '/resource-activities/add';
        $formResourceActivity->setAttribute('action', $url)
            ->setAttribute('class', 'form-horizontal');

        $resources         = $this->getEntityManager()->getRepository('\Resource\Entity\Resource')->findResources();
        $data['resources'] = Array();
        if ( !empty($resources) )
        {
            foreach ( $resources as $resource )
            {
                $data['resources'][$resource->getResourceId()] = $resource->getName();
            }
        }

        $costCodes         = $this->getEntityManager()->getRepository('\Resource\Entity\CostCode')->findCostCodes();
        $data['costcodes'] = Array();
        if ( !empty($costCodes) )
        {
            foreach ( $costCodes as $cost )
            {
                $data['costcodes'][$cost->getCostCodeId()] = $cost->getName();
            }
        }

        // Get Status listing to populate radio buttons
        $status = $this->getEntityManager()->getRepository('\Resource\Entity\Status')->findBy([], array('name' => 'asc'));

        $this->getView()
            ->setVariable('data', $data)
            ->setVariable('resource_form', $formResourceActivity)
            ->setVariable('status', $status)
            ->setVariable('page', $page)
            ->setVariable('total_rows', $total_rows)
            ->setVariable('base_path', $base_url);


        return $this->getView();
    }

    public function addAction()
    {
        try
        {
            if ( !($this->getRequest()->isXmlHttpRequest()) )
            {
                throw new \Exception('Access denied');
            }

            $post = $this->getRequest()->getPost();

            $em   = $this->getEntityManager();
            $form = new \Resource\Form\ResourceActivityForm($this->getEntityManager());
            $ra   = new \Resource\Entity\ResourceActivity();

            $form->setInputFilter($ra->getInputFilter());
            $form->setData($post);

            // Validating Date
            $expectedDate = $this->params()->fromPost('expected_date', null);
            if ( empty(trim($expectedDate)) )
            {
                return new JsonModel(array('err' => true, 'info' => 'Date cannot be empty.'));
            }

            // Validating start date
            $startDate = $this->params()->fromPost('start_date', null);
            if ( empty(trim($startDate)) )
            {
                return new JsonModel(array('err' => true, 'info' => 'Start date cannot be empty'));
            }

            // Validating end date
            $endDate = $this->params()->fromPost('end_date', null);
            if ( empty(trim($endDate)) )
            {
                return new JsonModel(array('err' => true, 'info' => 'End date cannot be empty'));
            }


            $dateArr = explode('/', $expectedDate);
            $data    = array('err' => false, 'info' => '');
            $dt      = new \DateTime(date('m/d/Y', mktime(1, 1, 1, $dateArr[1], $dateArr[0], $dateArr[2])));
            $ra->setDate($dt);

            $arr     = explode('/', $startDate);
            $startDt = new \DateTime(date('m/d/Y', mktime(1, 1, 1, $arr[1], $arr[0], $arr[2])));

            $arr   = explode('/', $endDate);
            $endDt = new \DateTime(date('m/d/Y', mktime(1, 1, 1, $arr[1], $arr[0], $arr[2])));

            if ( $form->isValid() )
            {
                $ra->setStartDate($startDt);
                $ra->setEndDate($endDt);
                $resource = $this->getEntityManager()->find('Resource\Entity\Resource', $post['resource']);
                $ra->setResource($resource);

                $costCode = $this->getEntityManager()->find('Resource\Entity\CostCode', $post['costCode']);
                $ra->setCostCode($costCode);

                $status = $this->getEntityManager()->find('Resource\Entity\Status', $post['status']);
                $ra->setStatus($status);

                $ra->setReferenceType('resource');

                $ra->exchangeArray($form->getData());
                $this->getEntityManager()->persist($ra);
                $this->getEntityManager()->flush();

                // Redirect to list of albums
                $this->flashMessenger()->addSuccessMessage('Resource activity has been added successfully!');

                $data = array('err' => false, 'info' => 'added');
                //return $this->redirect()->toRoute( 'resource_activities' );

            }
            else
            {
                $data = array('err' => true, 'info' => $form->getMessages());
            }

        }
        catch ( \Exception $ex )
        {
            $data = array('err' => true, 'info' => array('ex' => $ex->getMessage()));
        }

        return new JsonModel(empty($data) ? array('err' => true) : $data);/**/
    }

    public function getClientIdAction()
    {
        if ( !$this->request->isXmlHttpRequest() )
        {
            throw new \Exception('Access denied');
        }

        $post = $this->getRequest()->getPost();

        //$project      = $this->getEntityManager()->getRepository( 'Project\Entity\Project' )->findJobs( $post['project_id'] );
        $project      = $this->getEntityManager()->getRepository('Project\Entity\Project')->findJobs($post['project_id']);
        $return_value = Array('error' => 1);
        if ( !empty($project) )
        {
            $client_id    = $project->getClient()->getClientId();
            $return_value = Array(
                'error'        => 0,
                'client_id'    => $client_id,
                'project_id'   => $project->getProjectId(),
                'project_ref'  => $project->getProjectId() . ' - ' . $project->getName() . ' - ' . $project->getClient()->getName(),
                'project_name' => $project->getName() . ' - ' . $project->getClient()->getName()
            );
        }

        return new JsonModel($return_value);
    }

    public function addProductAction()
    {
        try
        {
            if ( !($this->request->isXmlHttpRequest()) )
            {
                throw new \Exception('Access denied');
            }

            $post = $this->getRequest()->getPost();


            $data = array('err' => false, 'info' => '');

            $productDate = $this->params()->fromPost('productDate', null);

            $em = $this->getEntityManager();

            $productActivity = new \Resource\Entity\ResourceActivity();

            $dateArr = explode('/', $productDate);

            // Validating start date
            $startDate = $this->params()->fromPost('productStartDate', null);
            if ( empty(trim($startDate)) )
            {
                return new JsonModel(array('err' => true, 'info' => 'Start date cannot be empty'));
            }

            // Validating end date
            $endDate = $this->params()->fromPost('productEndDate', null);
            if ( empty(trim($endDate)) )
            {
                return new JsonModel(array('err' => true, 'info' => 'End date cannot be empty'));
            }

            $arr     = explode('/', $startDate);
            $startDt = new \DateTime(date('m/d/Y', mktime(1, 1, 1, $arr[1], $arr[0], $arr[2])));

            $arr   = explode('/', $endDate);
            $endDt = new \DateTime(date('m/d/Y', mktime(1, 1, 1, $arr[1], $arr[0], $arr[2])));

            if ( !empty($productDate) )
            {
                $dt = new \DateTime(date('m/d/Y', mktime(1, 1, 1, $dateArr[1], $dateArr[0], $dateArr[2])));
                $productActivity->setDate($dt);
                $productActivity->setStartDate($startDt);
                $productActivity->setEndDate($endDt);

                //$form->setData( $post );
                $resource = $this->getEntityManager()->find('Resource\Entity\Resource', 14);
                $costCode = $this->getEntityManager()->find('Resource\Entity\CostCode', 6);
                $product  = $this->getEntityManager()->getRepository('Product\Entity\Product')->find($post['productReference']);


                $status = $this->getEntityManager()->find('Resource\Entity\Status', $post['productStatus']);
                $productActivity->setStatus($status);

                $productActivity->setCostCode($costCode);
                $productActivity->setResource($resource);
                $productActivity->setDetails($product->getModel());
                $productActivity->setProject($post['productProject']);
                $productActivity->setQuantity($post['productQuantity']);
                $productActivity->setRate($post['productRate']);
                $productActivity->setReference($post['productReference']);
                $productActivity->setReferenceType('product');
                $em->persist($productActivity);
                $em->flush();

                return new JsonModel(Array('err' => false, 'info' => 'Product activity saved successfully.'));
            }
            else
            {
                $data = array('err' => true, 'info' => 'Invalid date');
            }
        }
        catch ( \Exception $ex )
        {
            $data = Array('err' => false, 'info' => array('ex' => $ex->getMessage()));
        }

        return new JsonModel($data);
    }

    public function addInvoiceAction()
    {
        try
        {
            if ( !($this->request->isXmlHttpRequest()) )
            {
                throw new \Exception('Access denied');
            }

            $post = $this->getRequest()->getPost();


            $data = array('err' => false, 'info' => '');

            $invoiceDate = $this->params()->fromPost('invoiceDate', null);

            $em = $this->getEntityManager();

            $invoiceActivity = new \Resource\Entity\ResourceActivity();

            $dateArr = explode('/', $invoiceDate);
            $data    = array('err' => false, 'info' => '');
            if ( !empty($invoiceDate) )
            {
                $dt = new \DateTime(date('m/d/Y', mktime(1, 1, 1, $dateArr[1], $dateArr[0], $dateArr[2])));
                $invoiceActivity->setDate($dt);

                //$form->setData( $post );

                $resource = $this->getEntityManager()->find('Resource\Entity\Resource', 15);
                $costCode = $this->getEntityManager()->find('Resource\Entity\CostCode', 7);

                $invoiceActivity->setCostCode($costCode);
                $invoiceActivity->setResource($resource);
                $invoiceActivity->setDetails($post['invoiceDetails']);
                $invoiceActivity->setProject($post['invoiceProject']);
                $invoiceActivity->setQuantity(1);
                $invoiceActivity->setRate($post['invoiceRate']);
                $invoiceActivity->setReference($post['invoiceReference']);
                $invoiceActivity->setReferenceType('invoice');

                $em->persist($invoiceActivity);
                $em->flush();

                return new JsonModel(Array('err' => false, 'info' => 'Invoice activity updated successfully.'));
            }
            else
            {
                $data = array('err' => true, 'info' => 'Invalid date');
            }
        }
        catch ( \Exception $ex )
        {
            $data = Array('err' => false, 'info' => array('ex' => $ex->getMessage()));
        }

        return new JsonModel($data);
    }

    public function getProductInfoAction()
    {
        if ( !$this->request->isXmlHttpRequest() )
        {
            throw new \Exception('Access denied');
        }

        $post = $this->getRequest()->getPost();

        $product      = $this->getEntityManager()->getRepository('Product\Entity\Product')->find($post['product_id']);
        $return_value = Array('error' => 1);
        if ( !empty($product) )
        {
            //$client_id    = $product->getClient()->getClientId();
            $return_value = Array(
                'error'       => 0,
                'project_id'  => $product->getProductId(),
                'project_ref' => $product->getProductId() . ' - ' . $product->getDescription(),
                'description' => $product->getDescription(),
                'cpu'         => $product->getCpu(),
                'ppu'         => $product->getPpu(),
                'model'       => $product->getModel()
            );
        }

        return new JsonModel($return_value);
    }

    /**
     * Process ajax request for Work in progress report filter
     *
     * @return JsonModel
     * @throws \Exception
     */
    public function wiplistAction()
    {
        if ( !($this->request->isXmlHttpRequest()) )
        {
            throw new \Exception('Access denied');
        }

        $post = $this->getRequest()->getPost();

        $date = explode('-', $post['wip_end_date']);

        $end_date = $date[2] . '-' . $date[1] . '-' . $date[0];

        $em = $this->getEntityManager()->getRepository('Resource\Entity\ResourceActivity');

        $resources = $em->getWipList($end_date);
        $data      = array();

        foreach ( $resources as $resource )
        {
            $project = $this->getEntityManager()->getRepository('Project\Entity\Project')->findByProjectId($resource->getProject());

            $data['activities'][] = Array(
                'project_ref'    => $project->getProjectId() . ' - ' . $project->getName() . ' - ' . $project->getClient()->getName(),
                'client_id'      => $project->getClient()->getClientId(),
                'project_id'     => $project->getProjectId(),
                'reference'      => $resource->getReference(),
                'details'        => $resource->getDetails(),
                'resource'       => $resource->getResource()->getName(),
                'cost_code'      => $resource->getCostCode()->getName(),
                'quantity'       => number_format($resource->getQuantity(), 2),
                'rate'           => number_format($resource->getRate(), 2),
                'total'          => number_format($resource->getQuantity() * $resource->getRate(), 2),
                'id'             => $resource->getResourceActivityId(),
                'date'           => $resource->getDate()->format('d/m/Y'),
                'reference_type' => $resource->getReferenceType(),
                'project'        => $project,
                'color'          => !empty($resource->getStatus()) ? $resource->getStatus()->getColor() : ''
            );
        }

        return new JsonModel($data);
    }

    public function activitySalesAction()
    {
        $this->setCaption('Sales Activity');
        $request        = $this->getRequest();
        $em             = $this->getEntityManager()->getRepository('Resource\Entity\ResourceActivity');
        $start_date     = '';
        $end_date       = '';
        $resources      = [];
        $filter_types   = '';
        $posted         = false;
        $resources      = [];
        $other_projects = [];
        if ( $request->isPost() )
        {
            $posted       = true;
            $post         = $request->getPost();
            $filter_types = $post['filter_types'];
            $client       = isset($post['client']) ? $post['client'] : '';
            $owner        = isset($post['owner']) ? $post['owner'] : '';

            if ( isset($post['form_name']) && !empty($post['form_name']) && $post['form_name'] === 'sub_filters' )
            {
                if ( strtolower($filter_types) == 'wip' )
                {
                    $end_date = $post['end_date'];
                }

                if ( strtolower($filter_types) == 'cos' )
                {
                    $start_date = $post['start_date'];
                    $end_date   = $post['end_date'];
                }
            }
            else
            {
                if ( strtolower($post['filter_types']) == 'wip' )
                {
                    $end_date = $post['wip_end_date'];
                }
                elseif ( strtolower($post['filter_types']) == 'cos' )
                {
                    $start_date = $post['cos_start_date'];
                    $end_date   = $post['cos_end_date'];
                }
            }

            $resources = $em->getResourcesWithoutInvoice($start_date, $end_date, $filter_types, $client, $owner);

            // Get all jobs having no activity
            $other_projects = $this->getEntityManager()->getRepository('Project\Entity\Project')->findJobsWithoutActivities($filter_types, $start_date, $end_date, $owner, $client);
        }
        else
        {
            //$resources = $em->getResourcesWithoutInvoice();
        }

        $grand_totals = $resources['grand_totals'];
        unset($resources['grand_totals']);

        $users = [];
        // create an array for sub filters drop down.
        if ( isset($resources['users']) && !empty($resources['users']) )
        {
            foreach ( $resources['users'] as $user )
            {
                $project                                                       = $this->getEntityManager()->getRepository('Project\Entity\Project')->findByProjectId($user->getProject());
                $users['client'][$project->getClient()->getClientId()]         = $project->getClient()->getName();
                $users['owner'][$project->getClient()->getUser()->getUserId()] = $project->getClient()->getUser()->getName();
            }

        }

        unset($resources['users']);

        $project_users = [];
        if ( !empty($resources) && $posted )
        {
            foreach ( $resources as $resource )
            {
                $project_users['clients'][$resource['project']->getClient()->getClientId()]         = $resource['project']->getClient()->getName();
                $project_users['owners'][$resource['project']->getClient()->getUser()->getUserId()] = $resource['project']->getClient()->getUser()->getName();
            }
        }

        // Get Status listing to populate radio buttons
        $status = $this->getEntityManager()->getRepository('\Resource\Entity\Status')->findBy([], array('name' => 'asc'));

        $resources_activities = $this->getEntityManager()->getRepository('\Resource\Entity\Resource')->findResources();
        $data['resources']    = Array();
        if ( !empty($resources_activities) )
        {
            foreach ( $resources_activities as $resource )
            {
                $data['resources'][$resource->getResourceId()] = $resource->getName();
            }
        }

        $costCodes         = $this->getEntityManager()->getRepository('\Resource\Entity\CostCode')->findCostCodes();
        $data['costcodes'] = Array();
        if ( !empty($costCodes) )
        {
            foreach ( $costCodes as $cost )
            {
                $data['costcodes'][$cost->getCostCodeId()] = $cost->getName();
            }
        }

        $this->getView()
            ->setVariable('resources', $resources)
            ->setVariable('start_date', $start_date)
            ->setVariable('end_date', $end_date)
            ->setVariable('filter_type', $filter_types)
            ->setVariable('grand_totals', $grand_totals)
            ->setVariable('users', $users)
            ->setVariable('status', $status)
            ->setVariable('posted', $posted)
            ->setVariable('other_projects', $other_projects)
            ->setVariable('project_users', $project_users)
            ->setVariable('data', $data);

        return $this->getView();
    }

    /**
     * process ajax request for Cost of sales report filter
     *
     * @return JsonModel
     * @throws \Exception
     */
    public function coslistAction()
    {
        if ( !($this->request->isXmlHttpRequest()) )
        {
            throw new \Exception('Direct access to url is probimited');
        }


        $post = $this->getRequest()->getPost();

        $start_arr  = explode('-', $post['cos_start_date']);
        $start_date = $start_arr[2] . '-' . $start_arr[1] . '-' . $start_arr[0];

        $end_arr  = explode('-', $post['cos_end_date']);
        $end_date = $end_arr[2] . '-' . $end_arr[1] . '-' . $end_arr[0];
        $em       = $this->getEntityManager()->getRepository('Resource\Entity\ResourceActivity');

        $resources = $em->getCosList($start_date, $end_date);

        $data = array();

        foreach ( $resources as $resource )
        {
            $project = $this->getEntityManager()->getRepository('Project\Entity\Project')->findByProjectId($resource->getProject());

            $data['activities'][] = Array(
                'project_ref'    => $project->getProjectId() . ' - ' . $project->getName() . ' - ' . $project->getClient()->getName(),
                'client_id'      => $project->getClient()->getClientId(),
                'project_id'     => $project->getProjectId(),
                'reference'      => $resource->getReference(),
                'details'        => $resource->getDetails(),
                'resource'       => $resource->getResource()->getName(),
                'cost_code'      => $resource->getCostCode()->getName(),
                'quantity'       => number_format($resource->getQuantity(), 2),
                'rate'           => number_format($resource->getRate(), 2),
                'total'          => number_format($resource->getQuantity() * $resource->getRate(), 2),
                'id'             => $resource->getResourceActivityId(),
                'date'           => $resource->getDate()->format('d/m/Y'),
                'reference_type' => $resource->getReferenceType(),
                'project'        => $project,
                'color'          => !empty($resource->getStatus()) ? $resource->getStatus()->getColor() : ''
            );
        }

        return new JsonModel($data);
    }

    public function invoicesAction()
    {
        if ( !$this->request->isXmlHttpRequest() )
        {
            throw new \Exception('Direct access to url is not allowed');
        }

        $post = $this->getRequest()->getPost();

        $start_arr = explode('-', $post['start_date']);
        $end_arr   = explode('-', $post['end_date']);

        $start_date = $start_arr[2] . '-' . $start_arr[1] . '-' . $start_arr[0];
        $end_date   = $end_arr[2] . '-' . $end_arr[1] . '-' . $end_arr[0];

        $repository = $this->getEntityManager()->getRepository('Resource\Entity\ResourceActivity');
        $resources  = $repository->getInvoicesList($start_date, $end_date);

        $data = array();

        foreach ( $resources as $resource )
        {
            $project = $this->getEntityManager()->getRepository('Project\Entity\Project')->findByProjectId($resource->getProject());

            $data['activities'][] = Array(
                'project_ref'    => $project->getProjectId() . ' - ' . $project->getName() . ' - ' . $project->getClient()->getName(),
                'client_id'      => $project->getClient()->getClientId(),
                'project_id'     => $project->getProjectId(),
                'reference'      => $resource->getReference(),
                'details'        => $resource->getDetails(),
                'resource'       => $resource->getResource()->getName(),
                'cost_code'      => $resource->getCostCode()->getName(),
                'quantity'       => number_format($resource->getQuantity(), 2),
                'rate'           => number_format($resource->getRate(), 2),
                'total'          => number_format($resource->getQuantity() * $resource->getRate(), 2),
                'id'             => $resource->getResourceActivityId(),
                'date'           => $resource->getDate()->format('d/m/Y'),
                'reference_type' => $resource->getReferenceType(),
                'project'        => $project,
                'color'          => !empty($resource->getStatus()) ? $resource->getStatus()->getColor() : ''
            );
        }

        return new JsonModel($data);

    }

    public function downloadActivitiesAction()
    {
        //$resources = $this->getEntityManager()->getRepository('Resource\Entity\ResourceActivity')->findBy([], ['date' => 'desc']);
        $resources = $this->getEntityManager()->getRepository('Resource\Entity\ResourceActivity')->findAll();
        $data[]    = array(
            '"Date"',
            '"Project Ref"',
            '"Reference"',
            '"Details"',
            '"Resource Type"',
            '"Resource"',
            '"Cost Code"',
            '"Quantity"',
            '"Rate"',
            '"Total Cost"'
        );
        if ( !empty($resources) )
        {
            foreach ( $resources as $resource )
            {
                $project = $this->getEntityManager()->getRepository('Project\Entity\Project')->findByProjectId($resource->getProject());
                $data[]  = Array(
                    '"' . $resource->getDate()->format('d-M-Y') . '"',
                    '"' . $project->getProjectId() . ' - ' . $project->getName() . ' - ' . $project->getClient()->getName() . '"',
                    '"' . $resource->getReference() . '"',
                    '"' . $resource->getDetails() . '"',
                    '"' . $resource->getReferenceType() . '"',
                    '"' . $resource->getResource()->getName() . '"',
                    '"' . $resource->getCostCode()->getName() . '"',
                    '"' . number_format($resource->getQuantity(), 2) . '"',
                    '"' . number_format($resource->getRate(), 2) . '"',
                    '"' . number_format($resource->getQuantity() * $resource->getRate(), 2) . '"',
                );
            }
        }

        $filename = 'Resource Activities - ' . date('d-M-Y H:i:s') . '.csv';

        $response = $this->prepareCSVResponse($data, $filename);

        return $response;
    }
}