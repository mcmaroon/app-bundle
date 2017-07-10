<?php

namespace App\AppBundle\Helper;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use App\AppBundle\Helper\AbstractControllerInterface;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\HttpFoundation\Cookie;
use App\AppBundle\Event\AppEntityEvent;

/**
 * AbstractController.
 */
abstract class AbstractController extends Controller implements AbstractControllerInterface {

    protected $entityName = null;

    function __construct() {
        $this->entityName = $this->getClassShortName();
    }

    public function getClassShortName() {
        $reflect = new \ReflectionClass($this);
        return str_replace('Controller', '', $reflect->getShortName());
    }

    // ~

    protected function getViewPath() {
        return $this->getControllerBundleName() . ':' . $this->entityName;
    }

    // ~

    private function getIndexCacheKey() {
        return strtolower($this->getControllerBundleName() . '-' . $this->entityName);
    }

    // ~

    public function indexAction(Request $request) {

        $translated = $this->get('translator');
        $em = $this->getDoctrine()->getManager();
        $em->getFilters()->disable('softdeleteable');

        $entityNamespace = $this->getControllerBundleName() . ':' . $this->entityName;

        $repository = $em->getRepository($entityNamespace);
        // ~

        $bulk = $request->query->get('bulk');

        if ($bulk !== null && isset($bulk['type']) && in_array($bulk['type'], array('active', 'unactive')) && isset($bulk['ids']) && is_array($bulk['ids']) && count($bulk['ids'])) {
            $bulkItems = $repository->findByIds($bulk['ids']);
            if (count($bulkItems)) {
                foreach ($bulkItems as $bulkItem) {
                    $bulkItem->setActive(($bulk['type'] === 'active') ? true : false);
                    $em->persist($bulkItem);
                }
                $em->flush();
                $this->addFlash('success', $translated->trans('global.messages.bulk.success'));
            }
        }

        if ($bulk !== null) {
            $request->query->remove('bulk');
            return $this->redirect($this->generateUrl($request->attributes->get('_route'), $request->query->all()));
        }

        // ~

        if ($request->query->has('reset')) {
            $request->getSession()->remove('filters-' . $this->getIndexCacheKey());
            $this->addFlash('success', $translated->trans('filters.resetmessage'));
            return $this->redirect($this->generateUrl(strtolower($this->entityName)));
        }


        $page = $request->query->getInt('page', 1);
        $limit = $request->query->getInt('limit', 50);
        $filters = $request->query->get('filters', $request->getSession()->get('filters-' . $this->getIndexCacheKey(), array()));
        $filterHeaders = array();
        $repository->setFilters($filters);

        if (count($repository->getFilters())) {
            $request->getSession()->set('filters-' . $this->getIndexCacheKey(), $repository->getFilters());
        }

        $cacheKey = $this->getIndexCacheKey() . '-' . implode($repository->getFilters(), '-') . '-' . $page . '-' . $limit;

        // ~

        try {
            $pool = $this->container->get('cache');
            $item = $pool->getItem($cacheKey);
            if (!$item->isHit()) {
                $result = $repository->getList();
                $paginator = $this->get('knp_paginator');
                $pagination = $paginator->paginate($result, $page, $limit, array('wrap-queries' => $repository->hasJoined()));
                $item->set($pagination)->setTags(['index']);
                $item->expiresAfter(60);
                $pool->save($item);
            } else {
                $pagination = $item->get();
            }
        } catch (\Exception $exc) {
            $result = $repository->getList();
            $paginator = $this->get('knp_paginator');
            $pagination = $paginator->paginate($result, $request->query->getInt('page', 1), $request->query->getInt('limit', 50), array('wrap-queries' => $repository->hasJoined()));
        }

        // ~

        return $this->render($this->getViewPath() . ':index.html.twig', array(
                    'classShortName' => strtolower($this->entityName),
                    'entityNamespace' => $entityNamespace,
                    'pagination' => $pagination,
                    'filters' => $repository->getFilters(),
                    'filterHeaders' => $filterHeaders,
        ));
    }

    // ~

    public function showAction($id) {
        $em = $this->getDoctrine()->getManager();
        $em->getFilters()->disable('softdeleteable');

        $entity = $em->getRepository($this->getControllerBundleName() . ':' . $this->entityName)->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find entity.');
        }

        return $this->render($this->getViewPath() . ':show.html.twig', array(
                    'classShortName' => strtolower($this->entityName),
                    'entity' => $entity
        ));
    }

    // ~
    
    public function editActionVars($entity){
        
    }

    public function editAction($id) {
        $em = $this->getDoctrine()->getManager();
        $em->getFilters()->disable('softdeleteable');

        $entity = $em->getRepository($this->getControllerBundleName() . ':' . $this->entityName)->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find entity.');
        }

        $form = $this->createEditForm($entity);

        return $this->render($this->getViewPath() . ':edit.html.twig', array(
                    'classShortName' => strtolower($this->entityName),
                    'classShortNameSpace' => $this->entityName,
                    'entity' => $entity,
                    'vars' => $this->editActionVars($entity),
                    'form' => $form->createView()
        ));
    }

    // ~
    
    public function updateActionVars(Request $request, $id) {
        
    }
    
    // ~

    public function updateAction(Request $request, $id) {
        $translated = $this->get('translator');
        $em = $this->getDoctrine()->getManager();
        $em->getFilters()->disable('softdeleteable');

        $entity = $em->getRepository($this->getControllerBundleName() . ':' . $this->entityName)->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find entity.');
        }

        $form = $this->createEditForm($entity);
        $form->handleRequest($request);

        if ($form->isValid()) {            
            try {
                $em->flush();
                
                $appEntityEvent = new AppEntityEvent($entity);
                $this->get('event_dispatcher')->dispatch(AppEntityEvent::EVENT_UPDATE, $appEntityEvent);
                
                $this->updateActionVars($request, $id);
                
                $this->addFlash('success', $translated->trans('global.messages.update.success'));
            } catch (\Exception $e) {                
                $this->addFlash('danger', $translated->trans('global.messages.update.error'));               
            }                                    
            if ($form->get('submitAndStay')->isClicked() || is_string($request->get('submitAndStay'))) {
                return $this->redirect($this->generateUrl(strtolower($this->entityName) . '_edit', array('id' => $id)));
            }

            return $this->redirect($this->generateUrl(strtolower($this->entityName)));
        }

        return $this->render($this->getViewPath() . ':edit.html.twig', array(
                    'entity' => $entity,
                    'form' => $form->createView()
        ));
    }

    // ~

    public function createAction(Request $request) {
        $translated = $this->get('translator');

        $entity = $this->getControllerEntity();
        $form = $this->createCreateForm($entity);
        $form->handleRequest($request);

        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($entity);
            $em->flush();
            
            $appEntityEvent = new AppEntityEvent($entity);
            $this->get('event_dispatcher')->dispatch(AppEntityEvent::EVENT_CREATE, $appEntityEvent);

            $this->addFlash('success', $translated->trans('global.messages.create.success'));

            return $this->redirect($this->generateUrl(strtolower($this->entityName) . '_edit', array('id' => $entity->getId())));
        }

        return $this->render($this->getViewPath() . ':edit.html.twig', array(
                    'entity' => $entity,
                    'form' => $form->createView(),
        ));
    }

    // ~

    protected function createCreateForm($entity) {

        $form = $this->createForm($this->getControllerFormType(), $entity, array(
            'action' => $this->generateUrl(strtolower($this->entityName) . '_create'),
            'method' => 'POST',
        ));

        $form->add('submit', SubmitType::class, array('label' => 'global.create'));

        return $form;
    }

    // ~

    public function newAction() {

        $entity = $this->getControllerEntity();
        $form = $this->createCreateForm($entity);

        return $this->render($this->getViewPath() . ':edit.html.twig', array(
                    'classShortName' => strtolower($this->entityName),
                    'entity' => $entity,
                    'form' => $form->createView(),
        ));
    }

    // ~

    protected function createEditForm($entity) {

        $form = $this->createForm($this->getControllerFormType(), $entity, array(
            'action' => $this->generateUrl(strtolower($this->entityName) . '_update', array('id' => $entity->getId())),
            'method' => 'PUT',
        ));

        $form->add('submit', SubmitType::class, array('label' => 'global.submit'));
        $form->add('submitAndStay', SubmitType::class, array('label' => 'global.submitAndStay'));

        return $form;
    }

    // ~

    protected function deleteActionForceLock($entity) {
        return false;
    }

    // ~
    
    protected function deleteActionBeforeFlush($entity){
        
    }
    
    // ~

    public function deleteAction($id, Request $request) {
        $translated = $this->get('translator');
        $em = $this->getDoctrine()->getManager();
        $em->getFilters()->disable('softdeleteable');
        $entity = $em->getRepository($this->getControllerBundleName() . ':' . $this->entityName)->find($id);

        if (!$entity) {
            $this->addFlash('danger', $translated->trans('global.messages.remove.error'));
            return $this->redirect($this->generateUrl(strtolower($this->entityName)));
        }

        if ($this->deleteActionForceLock($entity)) {
            $this->addFlash('danger', $translated->trans('global.messages.remove.forcelock'));
            return $this->redirect($this->generateUrl(strtolower($this->entityName)));
        }

        if (method_exists($entity, 'isDeleted') && $entity->isDeleted()) {
            $this->addFlash('warning', $translated->trans('global.messages.remove.issoftdeleted'));
        } else {
            try {
                $this->deleteActionBeforeFlush($entity);
                $em->remove($entity);
                $em->flush();                                                
                $this->addFlash('success', $translated->trans('global.messages.remove.success'));
            } catch (\Doctrine\DBAL\DBALException $e) {
                if ($e->getPrevious()->getCode() === '23000') {
                    $this->addFlash('warning', $translated->trans('global.messages.remove.foreignkey'));
                }
            }
        }

        if ($redirectUrl = $request->get('redirectUrl')) {
            return new RedirectResponse($redirectUrl);
        }

        if ($request->headers->get('referer') !== null) {
            //return new RedirectResponse($request->headers->get('referer'));
        }

        return $this->redirect($this->generateUrl(strtolower($this->entityName)));
    }

}
