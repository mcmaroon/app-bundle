<?php

namespace App\AppBundle\Helper;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use App\AppBundle\Helper\AbstractAdminController;

/**
 * AbstractAdminTreeController.
 */
abstract class AbstractAdminTreeController extends AbstractAdminController {

    public function treeIndexAction() {

        $em = $this->getDoctrine()->getManager();
        $repository = $em->getRepository($this->getControllerBundleName() . ':' . $this->entityName);

        return $this->render($this->getViewPath() . ':tree.html.twig', array(
                    'classShortName' => strtolower($this->entityName),
                    'list' => $repository->getTreeList($this->getControllerBundleName() . ':' . $this->entityName),
                    'ajaxLoading' => 0,
                    'currentCategory' => null,
                    'currentCategoryParents' => null,
                    'sortable' => 'tree-list-sortable'
        ));
    }

    // ~

    public function treeSortAction(Request $request) {

        $em = $this->getDoctrine()->getManager();
        $repository = $em->getRepository($this->getControllerBundleName() . ':' . $this->entityName);

        $ids = $request->request->get('ids', array());

        $log = $this->container->get('app.log');

        $renderData = array('status' => false);

        $items = $repository->findByIds($ids);

        $updateItemsCount = 0;

        foreach ($items as $item) {
            if (isset($ids[$item->getId()])) {
                $item->setWeight((int) $ids[$item->getId()]);
                $em->persist($item);
                $updateItemsCount++;
                $renderData['status'] = true;
            }
        }

        $renderData['count'] = $updateItemsCount;

        $em->flush();

        return new JsonResponse($renderData);
    }

}
