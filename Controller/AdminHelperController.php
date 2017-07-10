<?php
namespace App\AppBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;

/**
 * AdminHelperController
 *
 */
class AdminHelperController extends Controller
{

    public function previewAction($entityNamespace, $id)
    {

        $renderData = array(
            'entityNamespace' => $entityNamespace,
            'id' => $id,
            'template' => ''
        );

        $em = $this->getDoctrine()->getManager();

        $entity = null;

        try {
            $repository = $em->getRepository($entityNamespace);
            $entity = $repository->find($id);
        } catch (\Exception $e) {
            $renderData['error'] = array(
                'code' => $e->getCode(),
                'message' => $e->getMessage()
            );
        }

        if ($entity) {

            $reflect = new \ReflectionClass($entity);

            $params = array(
                'entity' => $entity,
                'classShortName' => strtolower($reflect->getShortName())
            );

            try {
                $renderData['template'] = $this->renderView(str_replace(':', ':Admin', $entityNamespace) . ':preview.html.twig', $params);
            } catch (\Exception $exc) {
                $renderData['template'] = $this->renderView('AppAppBundle:Helper:preview.html.twig', $params);
            }
        }

        return new JsonResponse($renderData);
    }

    public function sortAction($entityNamespace, Request $request)
    {

        $em = $this->getDoctrine()->getManager();

        try {
            $repository = $em->getRepository($entityNamespace);
        } catch (\Exception $e) {
            $renderData['error'] = array(
                'code' => $e->getCode(),
                'message' => $e->getMessage()
            );
        }

        $ids = $request->request->get('ids', array());

        $renderData = array('status' => false);

        $updateItemsCount = 0;
        
        if ($repository) {
            $items = $repository->findByIds($ids);            

            foreach ($items as $item) {
                if (isset($ids[$item->getId()])) {
                    $item->setWeight((int) $ids[$item->getId()]);
                    $em->persist($item);
                    $updateItemsCount++;
                    $renderData['status'] = true;
                }
            }
        }

        $renderData['count'] = $updateItemsCount;

        $em->flush();

        return new JsonResponse($renderData);
    }
}
