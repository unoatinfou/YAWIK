<?php
/**
 * Cross Applicant Management
 *
 * @filesource
 * @copyright (c) 2013 Cross Solution (http://cross-solution.de)
 * @license   GPLv3
 */

/** ActionController of Jobs */
namespace Jobs\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use Zend\Session\Container as Session;

/**
 * Main Action Controller for the application.
 * Responsible for displaying the home site.  
 *
 */
class IndexController extends AbstractActionController
{
    
    
    /**
     * List jobs
     */
    public function indexAction()
    { 
        
        $params = $this->getRequest()->getQuery();
        $jsonFormat = 'json' == $params->get('format');
        $repository = $this->getServiceLocator()->get('repositories')->get('Jobs/Job');
        $isRecruiter = $this->acl()->isRole('recruiter');
//         $jobs= $repository->fetch();
//         foreach ($jobs as $job) {
//             $repository->save($job);
//         }
//         exit;
        
        if (!$jsonFormat && !$this->getRequest()->isXmlHttpRequest()) {
            $session = new Session('Jobs\Index');
            $sessionKey = $this->auth()->isLoggedIn() ? 'userParams' : 'guestParams';
            $sessionParams = $session[$sessionKey];
            if ($sessionParams) {
                foreach ($sessionParams as $key => $value) {
                    $params->set($key, $params->get($key, $value));
                }
            } else if ($isRecruiter) {
                $params->set('by', 'me');
            }
            $session[$sessionKey] = $params->toArray();
            $filterForm = $this->getServiceLocator()->get('forms')->get('Jobs/ListFilter', $isRecruiter);
            $filterForm->bind($params);
            //$filterForm->setData(array('params' => $params->toArray()));
            //$filterForm->setData()
        }
        
        $repository = $this->getServiceLocator()->get('repositories')->get('Jobs/Job');
        
        $paginator = $this->paginator('Jobs/Job');
        
//         $jsonFormat = 'json' == $this->params()->fromQuery('format');
        
//         if ($jsonFormat) {
//             $viewModel = new JsonModel();
//             //$items = iterator_to_array($paginator);
            
//             $viewModel->setVariables(array(
//                 'items' => $this->getServiceLocator()->get('builders')->get('JsonApplication')
//                                 ->unbuildCollection($paginator->getCurrentItems()),
//                 'count' => $paginator->getTotalItemCount()
//             ));
//             return $viewModel;
            
//         } 
        
        $return = array(
            'by' => $params->get('by', 'all'),
            'jobs' => $paginator,
        );
        if (isset($filterForm)) {
            $return['filterForm'] = $filterForm;
        }
        return $return;
        
    
     }
     
     public function dashboardAction()
     {
         $services = $this->getServiceLocator();
         $params = $this->getRequest()->getQuery();
         $isRecruiter = $this->acl()->isRole('recruiter');
         if ($isRecruiter) {
             $params->set('by', 'me');
         }
         $myJobs = $services->get('repositories')->get('Jobs/Job');
         
         $paginator = $this->paginator('Jobs/Job');

         #$paginator->setCurrentPageNumber($this->params()->fromQuery('page', 1))
         #->setItemCountPerPage($params->get('count', 10));
         
         return array(
             'script' => 'jobs/index/dashboard',
             'type' => $this->params('type'),
             'myJobs' => $myJobs,
             'jobs' => $paginator
         );
     }
    
}
