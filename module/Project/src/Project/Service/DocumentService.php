<?php

namespace Project\Service;

use DoctrineModule\Stdlib\Hydrator\DoctrineObject as DoctrineHydrator;

class DocumentService 
{
    protected $location;
    protected $entityManager;
    
    /**
     * project
     * @var \Project\Entity\Project
     */
    protected $project;
    
    /**
     * client
     * @var \Client\Entity\Client 
     */
    protected $client;


    /**
     * user
     * @var \Application\Entity\User 
     */
    protected $user;


    public function __construct($location, \Doctrine\ORM\EntityManager $em) {
        $this->setLocation($location);
        $this->setEntityManager($em);
    }

    
    public function getLocation() {
        return $this->location;
    }

    public function setLocation($location) {
        $this->location = $location;
        return $this;
    }
    
    /**
     * get client
     * @return \Client\Entity\Client
     */
    public function getClient() {
        return $this->client;
    }

    /**
     * set client
     * @param \Client\Entity\Client $client
     * @return \Project\Service\DocumentService
     */
    public function setClient(\Client\Entity\Client $client) {
        $this->client = $client;
        return $this;
    }
    
    /**
     * check for client 
     * @return boolean
     */
    public function hasClient() {
        return ($this->client instanceof \Client\Entity\Client);
    }

        
    
    /**
     * get project
     * @return \Project\Entity\Project
     */
    public function getProject() {
        return $this->project;
    }
    

    /**
     * set project
     * @param \Project\Entity\Project $project
     * @return \Project\Service\DocumentService
     */
    public function setProject(\Project\Entity\Project $project) {
        $this->project = $project;
        $this->client = $project->getClient();
        return $this;
    }
    
    
    /**
     * check if project exists
     * @return boolean
     */
    public function hasProject() {
        return ($this->project instanceof \Project\Entity\Project);
    }

    /**
     * get user
     * @return \Application\Entity\User
     */
    public function getUser() {
        return $this->user;
    }
    
     /**
     * check if user exists
     * @return boolean
     */
    public function hasUser() {
        return ($this->user instanceof \Application\Entity\User);
    }

    /**
     * set user
     * @param \Application\Entity\User $user
     * @return \Project\Service\DocumentService
     */
    public function setUser(\Application\Entity\User $user) {
        $this->user = $user;
        return $this;
    }

        
    function getSaveLocation(array $config=array()) {
        $path = $this->synchronize();

        if (empty($path)) {
            throw new \Exception('Location not set');
        }
       
        if (!is_dir($path)) {
            throw new \Exception('Google Drive not found');
        }
        

        $path = rtrim($path, DIRECTORY_SEPARATOR).DIRECTORY_SEPARATOR;
        
        if (isset ($config['route'])) {
            $route = $config['route'];
            if (!is_array($route)) {
                $route = array($route);
            }

            foreach ($route as $dir) {
                $path.=$dir.DIRECTORY_SEPARATOR;
                if (!is_dir($path)) {
                    if (!mkdir($path)) {
                        throw new \Exception('project path could not be created');
                    }
                }
            }
        }
        
        return (rtrim($path, DIRECTORY_SEPARATOR).DIRECTORY_SEPARATOR);
    }

    public function saveDOMPdfDocument (\DOMPDF $dompdf, array $config=array(), $invoiceId=false) {
        if (empty($config['filename'])) {
            throw new \Exception('no filename found');
        }
        
        if (empty($config['category'])) {
            throw new \Exception('no category found');
        }
        $filename = $config['filename'];

        if (!preg_match('/[.]pdf$/i', $filename)) {
            $filename.='.pdf';
        }
        
        $dir = $this->getSaveLocation($config);
        file_put_contents($dir.$filename, $dompdf->output());
        
        try {
            $fileMd5 = md5_file($dir.$filename);
            $fileSize = filesize($dir.$filename);
        } catch (\Exception $ex) {
            $fileMd5=null;
            $fileSize=0;
        }
        
        $dlid = $this->logDocument($filename, $config['category'], $fileMd5, $fileSize, (empty($invoiceId)?false:array('invoiceId'=>$invoiceId)), 'application/pdf');
        
        
        return array (
            'file'=>$dir.$filename,
            'documentListId'=>$dlid
        );
    }
    
    public function findExtensionFromExt($ext, $type) {
        $em = $this->getEntityManager();
        $queryBuilder = $em->createQueryBuilder();
        $queryBuilder
            ->select('d')
            ->from('Project\Entity\DocumentExtension', 'd')
            ->where('d.extension=?1')
            ->setMaxResults(1)
            ->setParameter(1, $ext);
        $query  = $queryBuilder->getQuery();
        try {
            $extObjs = $query->getResult();
            if (empty($extObjs)) {
                throw new \Exception('extension not found');
            }
            
            $extObj = array_shift($extObjs);
            
        } catch (\Exception $e) {
            $extObj = new \Project\Entity\DocumentExtension();
            $extObj
                ->setExtension($ext)
                ->setHeader($type);
            $em->persist($extObj);
            $em->flush();
        }
        
        return $extObj;
    }
    
    public function saveUploadedFile ($file, array $config=array()) {
        if (empty($config['category'])) {
            throw new \Exception('no category found');
        }
        
        $dir = $this->getSaveLocation($config);
        $filename =  !empty($config['filename'])?$config['filename']:$file['name'];  //5
        $filenameOrig =  $filename;  //5
        
        $tempFile = $file['tmp_name'];          //3             
                
        $iteration = 1;
        $maxIteration = 100;
        while (file_exists($dir.$filename)) {
            if ($iteration>$maxIteration) {
                throw new \Exception('could not create file due to duplicate names');
            }
            $filename = preg_replace('/([.][^.]+)$/', '.'.$iteration.'$1', $filenameOrig);
            $iteration++;
        }

        if (!move_uploaded_file($tempFile,$dir.$filename)) {
            throw new \Exception('could not move file');
        } //6/**/
                
        
        try {
            $fileMd5 = md5_file($dir.$filename);
            $fileSize = filesize($dir.$filename);
        } catch (\Exception $ex) {
            $fileMd5=null;
            $fileSize=0;
        }
        
        $this->logDocument($filename, $config['category'], $fileMd5, $fileSize, false, $file['type'], empty($config['subid'])?false:$config['subid']);
        
        
        return array (
            'file'=>$dir.$filename,
        );
    }
    
    
    public function saveUploadedRawFile ($file, array $route=array()) {
        $dir = realpath($this->getSaveLocation().implode(DIRECTORY_SEPARATOR, $route)).DIRECTORY_SEPARATOR;

        if (!file_exists($dir)) {
            throw new \Exception('Directory not found');
        }
        
        $filename =  $file['name'];  //5
        $filenameOrig =  $file['name'];  //
        $tempFile = $file['tmp_name'];          //3             
                
        $iteration = 1;
        $maxIteration = 10;
        while (file_exists($dir.$filename)) {
            if ($iteration>$maxIteration) {
                throw new \Exception('could not create file due to duplicate names');
            }
            $filename = preg_replace('/([.][^.]+)$/', '.'.$iteration.'$1', $filenameOrig);
            $iteration++;
        }

        
        if (!move_uploaded_file($tempFile,$dir.$filename)) {
            throw new \Exception('could not move file');
        } //6/**/
                
        
        return array (
            'dir'=>$dir,
            'file'=>$filename,
        );
    }
    
    
    function logDocument($filename, $category, $hash, $size, $config=false, $type='application/octet-stream', $subid=false) {
        // example chksum = '3c167ffb798d9b313abd8a3f4cb30ecb';
        $em = $this->getEntityManager();
        $document = new \Project\Entity\DocumentList();
        
        
        $ext = preg_replace('/^[\s\S]+[.]([^.]+)$/','$1',$filename);
        $extObj = $this->findExtensionFromExt($ext, $type);
        
        $document->setExtension($extObj);
        
        $data = array (
            'filename'=>$filename,
            'hash'=>$hash,
            'size'=>$size,
        );
        
        if (!empty($config)) {
            if (is_array ($config)) {
                $config = json_encode($config);
            }
            
            $data['config'] = $config;
        }
        
        if (!empty($subid)) {
            $document->setSubid($subid);
        }
        
        if ($category instanceof \Project\Entity\DocumentCategory) {
            $document->setCategory($category);
        } else {
            $data['category'] = $category;
        }
        
        if ($this->hasUser()) {
            $document->setUser($this->getUser());
        }
        
        if ($this->hasProject()) {
            $document->setProject($this->getProject());
        }
        
        $hydrator = new DoctrineHydrator($em,'Project\Entity\DocumentList');
        $hydrator->hydrate(
            $data,
            $document
        );
        
        $em->persist($document);
        $em->flush();
        
        return $document->getDocumentListId();
    }

    // factory involkable methods
    function setEntityManager(\Doctrine\ORM\EntityManager $em) {
        $this->em = $em;
    }
    
    public function getEntityManager() {
        return $this->em;
    }

    public function synchronize() {
        try {
            $path = $this->getLocation();
            if (empty($path)) {
                throw new \Exception('Location not set');
            }

            if (!is_dir($path)) {
                throw new \Exception('Google Drive not found');
            }

            $path = rtrim($path, DIRECTORY_SEPARATOR).DIRECTORY_SEPARATOR;

            // make unix safe
            if ($this->hasClient()) {
                $path.= trim(preg_replace('/[_]+/', '_',str_replace(array('"', "'", "&", "/", "\\", "?", "#"), '_', trim($this->getClient()->getName()))),'_');
                if (!is_dir($path)) {
                    if (!mkdir($path)) {
                        throw new \Exception('client path could not be created');
                    }
                }

                if ($this->hasProject()) {
                    $pid = str_pad($this->getProject()->getClient()->getClientId(), 5, '0', STR_PAD_LEFT).'-'.str_pad($this->getProject()->getProjectId(), 5, '0', STR_PAD_LEFT);
                    $path.=DIRECTORY_SEPARATOR.trim(preg_replace('/[_]+/', '_',str_replace(array('"', "'", "&", "/", "\\", "?", "#"), '_', trim('['.$pid.'] '.$this->getProject()->getName()))),'_');

                    if (!is_dir($path)) {
                        if (!mkdir($path)) {
                            throw new \Exception('project path could not be created');
                        }
                    }
                    
                    $dirs = array(
                        '3rd party suppliers', // ok
                        'design'=>array (
                            'AGi32',
                            'DIAlux',
                            'Drawings',
                            'Relux'
                        ),// ok (called design on template not "8point3 design"
                        'accounts',// ok
                        'proposals',// ok
                        'contract documents',// ok
                        'client enquiry information'=>array(
                            'email correspondence',
                            'original drawings'
                        ),// ok
                        'health and safety',// ok
                        'project management'=>array(
                            'barcode data',
                            'O&M'
                        ),// ok
                        'OLD',// ok
                        'images'=>array(
                            'spaces',
                            'buildings',
                            'general',
                        ),
                    );
                    
                    foreach ($dirs as $i=>$dir) {
                        if (is_array($dir)) {
                            $tmpPath = $path.DIRECTORY_SEPARATOR.$i;
                            if (!is_dir($tmpPath)) {
                                if (!mkdir($tmpPath)) {
                                    throw new \Exception('project path could not be created');
                                } 
                            } 
                            
                            foreach ($dir as $subdir) {
                                $tmpPath = $path.DIRECTORY_SEPARATOR.$i.DIRECTORY_SEPARATOR.$subdir;
                                if (!is_dir($tmpPath)) {
                                    if (!mkdir($tmpPath)) {
                                        throw new \Exception('project path could not be created');
                                    } 
                                } 
                            }/**/
                        } else {
                            $tmpPath = $path.DIRECTORY_SEPARATOR.$dir;
                            if (!is_dir($tmpPath)) {
                                if (!mkdir($tmpPath)) {
                                    throw new \Exception('project path could not be created');
                                } 
                            } 
                        }
                    }
                } 
            } 
            
            return $path;
        } catch (\Exception $e) {
            // need to add alert to email/task ... or something
            return false;
        }
        
    }/**/
    
}

